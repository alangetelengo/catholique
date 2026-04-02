<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Group;
use App\Models\Member;
use App\Models\Paroisse;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class GroupController extends Controller implements HasMiddleware
{
    use LogsErrors;

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_groups', only: ['index', 'show']),
            new Middleware('permission:create_groups', only: ['create', 'store']),
            new Middleware('permission:edit_groups', only: ['edit', 'update']),
            new Middleware('permission:delete_groups', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        try {
            $user = $request->user();

            $query = Group::query()
                ->with(['paroisse', 'responsable']);

            // Restriction par paroisse si pas super admin
            if (! $user->hasRole('super_admin')) {
                $query->where('paroisse_id', $user->paroisse_id);
            }

            // Filtres
            if ($request->filled('type')) {
                $query->where('type', $request->string('type')->value());
            }

            if ($request->filled('paroisse_id')) {
                $query->where('paroisse_id', $request->integer('paroisse_id'));
            }

            if ($request->filled('q')) {
                $q = $request->string('q')->lower()->value();
                $query->where(function ($sub) use ($q): void {
                    $sub->whereRaw('LOWER(nom) LIKE ?', ["%{$q}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$q}%"]);
                });
            }

            $groups = $query->orderBy('nom')->paginate(15)->withQueryString();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : collect();

            return view('groups.index', [
                'groups' => $groups,
                'paroisses' => $paroisses,
            ]);
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement de la liste des groupes');
            FlashAlert::error('Une erreur est survenue lors du chargement de la liste des groupes.');

            return view('groups.index', [
                'groups' => collect(),
                'paroisses' => collect(),
            ]);
        }
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        $responsables = Member::query()
            ->when(! $user->hasRole('super_admin'), function ($q) use ($user): void {
                $q->where('paroisse_id', $user->paroisse_id);
            })
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        return view('groups.create', [
            'group' => new Group(),
            'paroisses' => $paroisses,
            'responsables' => $responsables,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'nom' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:chorale,catéchisme,mouvement,autre'],
                'responsable_id' => ['nullable', 'exists:members,id'],
                'paroisse_id' => ['nullable', 'exists:paroisses,id'],
                'description' => ['nullable', 'string'],
            ]);

            // Normalisation simple
            $validated['nom'] = mb_strtoupper($validated['nom']);

            if (! $user->hasRole('super_admin')) {
                $validated['paroisse_id'] = $user->paroisse_id;
            }

            Group::create($validated);

            FlashAlert::success('Groupe créé avec succès.');

            return redirect()->route('groups.index');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la création du groupe');
            FlashAlert::error('Une erreur est survenue lors de la création du groupe.');

            return redirect()->back()->withInput();
        }
    }

    public function edit(Request $request, Group $group): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('super_admin') && $group->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Vous ne pouvez modifier que les groupes de votre paroisse.');

            return redirect()->route('groups.index');
        }

        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        $responsables = Member::query()
            ->when(! $user->hasRole('super_admin'), function ($q) use ($user): void {
                $q->where('paroisse_id', $user->paroisse_id);
            })
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get();

        return view('groups.edit', [
            'group' => $group,
            'paroisses' => $paroisses,
            'responsables' => $responsables,
        ]);
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user->hasRole('super_admin') && $group->paroisse_id !== $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez modifier que les groupes de votre paroisse.');

                return redirect()->route('groups.index');
            }

            $validated = $request->validate([
                'nom' => ['required', 'string', 'max:255'],
                'type' => ['required', 'in:chorale,catéchisme,mouvement,autre'],
                'responsable_id' => ['nullable', 'exists:members,id'],
                'paroisse_id' => ['nullable', 'exists:paroisses,id'],
                'description' => ['nullable', 'string'],
            ]);

            $validated['nom'] = mb_strtoupper($validated['nom']);

            if (! $user->hasRole('super_admin')) {
                $validated['paroisse_id'] = $user->paroisse_id;
            }

            $group->update($validated);

            FlashAlert::success('Groupe mis à jour avec succès.');

            return redirect()->route('groups.index');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour du groupe', ['group_id' => $group->id]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour du groupe.');

            return redirect()->back()->withInput();
        }
    }

    public function destroy(Request $request, Group $group): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user->hasRole('super_admin') && $group->paroisse_id !== $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez supprimer que les groupes de votre paroisse.');

                return redirect()->route('groups.index');
            }

            $group->delete();

            FlashAlert::success('Groupe supprimé avec succès.');
        } catch (\Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression du groupe', ['group_id' => $group->id]);
            FlashAlert::error('Une erreur est survenue lors de la suppression du groupe.');
        }

        return redirect()->route('groups.index');
    }
}

