<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Paroisse;
use App\Traits\LogsErrors;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function index(Request $request): View
    {
        try {
            $query = Paroisse::query()
                ->with('curé')
                ->orderBy('nom');

            if (! $request->user()?->hasRole('super_admin')) {
                if ($request->user()?->paroisse_id) {
                    $query->where('id', $request->user()->paroisse_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                if ($request->filled('q')) {
                    $s = '%'.addcslashes(mb_strtolower($request->string('q')->value()), '%_\\').'%';
                    $query->where(function ($q) use ($s): void {
                        $q->whereRaw('LOWER(nom) LIKE ?', [$s])
                            ->orWhereRaw('LOWER(COALESCE(ville, "")) LIKE ?', [$s])
                            ->orWhereRaw('LOWER(COALESCE(code_paroisse, "")) LIKE ?', [$s]);
                    });
                }
                if ($request->filled('actif')) {
                    $query->where('actif', $request->boolean('actif'));
                }
            }

            $paroisses = $query->paginate(15)->withQueryString();
            $canManage = $request->user()?->hasRole('super_admin') ?? false;

            return view('paroisses.index', compact('paroisses', 'canManage'));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des paroisses');

            throw $e;
        }
    }

    public function create(Request $request): View
    {
        $this->ensureSuperAdmin($request);
        $members = $this->membersForSelect();
        $paroisse = new Paroisse([
            'pays' => 'République du Congo',
            'actif' => true,
        ]);

        return view('paroisses.create', compact('paroisse', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $this->ensureSuperAdmin($request);
            $validated = $this->validatedParoisse($request, null);
            $paroisse = Paroisse::create($validated);

            $this->logInfo('Paroisse créée', ['paroisse_id' => $paroisse->id, 'nom' => $paroisse->nom]);

            return redirect()->route('paroisses.index')->with('success', 'Paroisse créée avec succès.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création paroisse', ['data' => $request->all()]);

            throw $e;
        }
    }

    public function edit(Request $request, Paroisse $paroisse): View
    {
        $this->ensureSuperAdmin($request);
        $members = $this->membersForSelect();

        return view('paroisses.edit', compact('paroisse', 'members'));
    }

    public function update(Request $request, Paroisse $paroisse): RedirectResponse
    {
        try {
            $this->ensureSuperAdmin($request);
            $validated = $this->validatedParoisse($request, $paroisse);
            $paroisse->update($validated);

            $this->logInfo('Paroisse mise à jour', ['paroisse_id' => $paroisse->id]);

            return redirect()->route('paroisses.index')->with('success', 'Paroisse mise à jour.');
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

            return redirect()->route('paroisses.index')->with('success', 'Paroisse désactivée. Elle reste en base mais n’apparaît plus comme active.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur désactivation paroisse', ['paroisse_id' => $paroisse->id]);

            throw $e;
        }
    }

    /**
     * @return Collection<int, Member>
     */
    private function membersForSelect()
    {
        return Member::query()
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
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
            'curé_id' => ['nullable', 'exists:members,id'],
            'diocèse' => ['nullable', 'string', 'max:255'],
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
