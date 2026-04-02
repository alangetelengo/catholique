<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Paroisse;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * CRUD inventaires par paroisse.
 * Journalisation métier : canal « paroisse » → storage/logs/paroisse.log (trait LogsErrors).
 */
class InventoryController extends Controller
{
    use LogsErrors;

    /**
     * Liste filtrée : super-admin peut filtrer par paroisse ; les autres voient leur paroisse uniquement.
     */
    public function index(Request $request): View
    {
        try {
            $user = $request->user();
            $query = Inventory::query()
                ->with(['paroisse', 'createdBy'])
                ->orderByDesc('updated_at')
                ->orderByDesc('id');

            if (! $user?->hasRole('super_admin')) {
                $query->where('paroisse_id', $user?->paroisse_id);
            } elseif ($request->filled('paroisse_id')) {
                $query->where('paroisse_id', (int) $request->integer('paroisse_id'));
            }

            if ($request->filled('categorie')) {
                $query->where('categorie', $request->string('categorie')->value());
            }

            if ($request->filled('etat')) {
                $query->where('etat', $request->string('etat')->value());
            }

            if ($request->filled('q')) {
                $search = mb_strtolower($request->string('q')->value());
                $query->where(function ($b) use ($search): void {
                    $b->whereRaw('LOWER(designation) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(reference_inventaire, "")) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(emplacement, "")) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(COALESCE(notes, "")) LIKE ?', ["%{$search}%"]);
                });
            }

            $inventories = $query->paginate(20)->withQueryString();
            $paroisses = $user?->hasRole('super_admin')
                ? Paroisse::query()->orderBy('nom')->get()
                : collect();

            return view('inventories.index', compact('inventories', 'paroisses'));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des inventaires');

            throw $e;
        }
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $paroisses = $user?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : collect();

        $inventory = new Inventory([
            'categorie' => 'autre',
            'quantite' => 1,
            'unite' => 'unité',
            'etat' => 'bon',
            'paroisse_id' => $user?->hasRole('super_admin')
                ? Paroisse::query()->value('id')
                : $user?->paroisse_id,
        ]);

        return view('inventories.create', compact('inventory', 'paroisses'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatedInventory($request, null, $user);
            $validated['created_by'] = $user?->id;

            $row = Inventory::create($validated);
            $this->logInfo('Inventaire créé', [
                'inventory_id' => $row->id,
                'paroisse_id' => $row->paroisse_id,
                'designation' => $row->designation,
            ]);

            return redirect()->route('inventories.index')->with('success', 'Article d’inventaire enregistré.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création d’un inventaire', ['data' => $request->all()]);

            throw $e;
        }
    }

    public function edit(Request $request, Inventory $inventory): View
    {
        $this->authorizeInventory($request->user(), $inventory);
        $paroisses = $request->user()?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : collect();

        return view('inventories.edit', compact('inventory', 'paroisses'));
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        try {
            $this->authorizeInventory($request->user(), $inventory);
            $validated = $this->validatedInventory($request, $inventory, $request->user());
            $inventory->update($validated);

            $this->logInfo('Inventaire mis à jour', [
                'inventory_id' => $inventory->id,
                'paroisse_id' => $inventory->paroisse_id,
            ]);

            return redirect()->route('inventories.index')->with('success', 'Inventaire mis à jour.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour d’un inventaire', ['inventory_id' => $inventory->id]);

            throw $e;
        }
    }

    public function destroy(Request $request, Inventory $inventory): RedirectResponse
    {
        try {
            $this->authorizeInventory($request->user(), $inventory);
            $inventory->delete();

            $this->logInfo('Inventaire supprimé (soft delete)', [
                'inventory_id' => $inventory->id,
                'paroisse_id' => $inventory->paroisse_id,
            ]);

            return redirect()->route('inventories.index')->with('success', 'Article retiré de l’inventaire.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression d’un inventaire', ['inventory_id' => $inventory->id]);

            throw $e;
        }
    }

    /**
     * Accès : super-admin sur toutes les paroisses ; sinon uniquement la paroisse de l’utilisateur.
     */
    private function authorizeInventory(?\App\Models\User $user, Inventory $inventory): void
    {
        if ($user?->hasRole('super_admin')) {
            return;
        }

        if ((int) $inventory->paroisse_id !== (int) ($user?->paroisse_id)) {
            abort(403);
        }
    }

    /**
     * Règles de validation + paroisse_id imposée pour les non super-admins.
     *
     * @return array<string, mixed>
     */
    private function validatedInventory(Request $request, ?Inventory $existing, ?\App\Models\User $user): array
    {
        $paroisseId = $this->resolveParoisseIdForValidation($request, $existing, $user);

        $validated = $request->validate([
            'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
            'designation' => ['required', 'string', 'max:500'],
            'categorie' => ['required', 'in:mobilier_liturgique,mobilier,materiel_technique,consommable,autre'],
            'reference_inventaire' => [
                'nullable',
                'string',
                'max:128',
                Rule::unique('inventories', 'reference_inventaire')
                    ->where('paroisse_id', $paroisseId)
                    ->ignore($existing?->id),
            ],
            'quantite' => ['required', 'numeric', 'min:0'],
            'unite' => ['required', 'string', 'max:64'],
            'emplacement' => ['nullable', 'string', 'max:255'],
            'etat' => ['required', 'in:bon,usage,a_reparer,hors_service'],
            'date_acquisition' => ['nullable', 'date'],
            'valeur_estimee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! $user?->hasRole('super_admin')) {
            $validated['paroisse_id'] = (int) $user->paroisse_id;
        }

        $ref = $validated['reference_inventaire'] ?? null;
        $validated['reference_inventaire'] = ($ref !== null && $ref !== '') ? $ref : null;

        return $validated;
    }

    private function resolveParoisseIdForValidation(Request $request, ?Inventory $existing, ?\App\Models\User $user): int
    {
        if ($user?->hasRole('super_admin')) {
            return (int) $request->integer('paroisse_id', $existing?->paroisse_id ?? 0);
        }

        return (int) $user->paroisse_id;
    }
}
