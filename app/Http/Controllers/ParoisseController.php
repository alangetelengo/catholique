<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Paroisse;
use App\Traits\LogsErrors;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * CRUD paroisses. Liste consultable par tous (filtrée pour les non super-admin).
 * Création, modification et désactivation : super-admin uniquement.
 */
class ParoisseController extends Controller
{
    use LogsErrors;

    private function ensureSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403, 'Action réservée aux administrateurs.');
    }

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('application-configuration.index', array_merge(
            $request->query(),
            ['tab' => 'paroisses']
        ));
    }

    public function create(Request $request): View
    {
        $this->ensureSuperAdmin($request);
        $members = $this->membersForSelect(null);
        $paroisse = new Paroisse([
            'pays' => 'République du Congo',
            'actif' => true,
        ]);
        $canQuickCreateCure = $request->user()?->can('create_members')
            || $request->user()?->hasRole('super_admin');

        return view('paroisses.create', compact('paroisse', 'members', 'canQuickCreateCure'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->ensureSuperAdmin($request);
            $validated = $this->validatedParoisse($request, null);
            $paroisse = Paroisse::create($validated);
            $this->attachCureToParoisse($paroisse, $validated['cure_id'] ?? null);

            $this->logInfo('Paroisse créée', ['paroisse_id' => $paroisse->id, 'nom' => $paroisse->nom]);

            return redirect()
                ->route('application-configuration.index', ['tab' => 'paroisses'])
                ->with('success', 'Paroisse créée avec succès.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création paroisse', ['data' => $request->all()]);

            throw $e;
        }
    }

    public function edit(Request $request, Paroisse $paroisse): View
    {
        $this->ensureSuperAdmin($request);
        $members = $this->membersForSelect($paroisse);
        $canQuickCreateCure = $request->user()?->can('create_members')
            || $request->user()?->hasRole('super_admin');

        return view('paroisses.edit', compact('paroisse', 'members', 'canQuickCreateCure'));
    }

    public function update(Request $request, Paroisse $paroisse): RedirectResponse
    {
        try {
            $this->ensureSuperAdmin($request);
            $validated = $this->validatedParoisse($request, $paroisse);
            $paroisse->update($validated);
            $this->attachCureToParoisse($paroisse, $validated['cure_id'] ?? null);

            $this->logInfo('Paroisse mise à jour', ['paroisse_id' => $paroisse->id]);

            return redirect()
                ->route('application-configuration.index', ['tab' => 'paroisses'])
                ->with('success', 'Paroisse mise à jour.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur mise à jour paroisse', ['paroisse_id' => $paroisse->id, 'data' => $request->all()]);

            throw $e;
        }
    }

    /**
     * Désactive la paroisse (pas de suppression physique : données financières liées).
     */
    public function destroy(Request $request, Paroisse $paroisse): RedirectResponse
    {
        try {
            $this->ensureSuperAdmin($request);
            $paroisse->update(['actif' => false]);

            $this->logInfo('Paroisse désactivée', ['paroisse_id' => $paroisse->id, 'nom' => $paroisse->nom]);

            return redirect()
                ->route('application-configuration.index', ['tab' => 'paroisses'])
                ->with('success', 'Paroisse désactivée. Elle reste en base mais n’apparaît plus comme active.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur désactivation paroisse', ['paroisse_id' => $paroisse->id]);

            throw $e;
        }
    }

    /**
     * Membres proposés comme curé : paroisse courante en édition ; liste vide en création
     * (utiliser « + Nouveau curé »), plus le curé déjà rattaché s’il existe.
     *
     * @return Collection<int, Member>
     */
    private function membersForSelect(?Paroisse $paroisse): Collection
    {
        if ($paroisse === null || ! $paroisse->exists) {
            return new Collection;
        }

        $query = Member::query()
            ->where('statut', 'actif')
            ->where('paroisse_id', $paroisse->id)
            ->orderBy('nom')
            ->orderBy('prenom');

        $members = $query->get();

        if ($paroisse->cure_id) {
            $current = Member::query()->find($paroisse->cure_id);
            if ($current && ! $members->contains('id', $current->id)) {
                $members->prepend($current);
            }
        }

        return $members;
    }

    private function attachCureToParoisse(Paroisse $paroisse, mixed $cureId): void
    {
        if ($cureId === null || $cureId === '') {
            return;
        }

        Member::query()
            ->whereKey((int) $cureId)
            ->where(function ($q) use ($paroisse): void {
                $q->whereNull('paroisse_id')
                    ->orWhere('paroisse_id', $paroisse->id);
            })
            ->update(['paroisse_id' => $paroisse->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedParoisse(Request $request, ?Paroisse $paroisse): array
    {
        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:120'],
            'pays' => ['nullable', 'string', 'max:120'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'code_paroisse' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('paroisses', 'code_paroisse')->ignore($paroisse?->id),
            ],
            'cure_id' => ['nullable', 'exists:members,id'],
            'diocese' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];

        $validated = $request->validate($rules);
        $validated['actif'] = $request->boolean('actif');

        if (empty($validated['code_paroisse'])) {
            $validated['code_paroisse'] = null;
        }

        return $validated;
    }
}
