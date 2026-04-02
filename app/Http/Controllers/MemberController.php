<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\Paroisse;
use App\Traits\LogsErrors;
use Exception;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller implements HasMiddleware
{
    use LogsErrors;

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_members', only: ['index', 'show']),
            new Middleware('permission:create_members', only: ['create', 'store']),
            new Middleware('permission:edit_members', only: ['edit', 'update']),
            new Middleware('permission:delete_members', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $query = Member::query()->with('paroisse');

            // Filtre par paroisse (restriction pour non super_admin)
            if (Auth::check() && ! Auth::user()->hasRole('super_admin')) {
                if (Auth::user()->paroisse_id) {
                    $query->where('paroisse_id', Auth::user()->paroisse_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif (request('paroisse_id')) {
                $query->where('paroisse_id', request('paroisse_id'));
            }

            // Filtre statut
            if ($statut = request('statut')) {
                $query->where('statut', $statut);
            }

            // Filtre sexe
            if ($sexe = request('sexe')) {
                $query->where('sexe', $sexe);
            }

            // La recherche texte se fait côté client uniquement (sur la liste déjà chargée)
            // pour ne pas être liée aux filtres qui interrogent la base de données.

            $members = $query->latest()->paginate(15)->withQueryString();

            $paroisses = Auth::check() && Auth::user()->hasRole('super_admin')
                ? Paroisse::query()->orderBy('nom')->get()
                : collect();

            return view('members.index', compact('members', 'paroisses'));
        } catch (Exception $e) {
            $this->logError('Erreur lors de la récupération des membres', $e);
            FlashAlert::error('Une erreur est survenue lors de la récupération des membres.');

            return redirect()->route('home');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $paroisses = Auth::check() && Auth::user()->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : collect();

        return view('members.create', compact('paroisses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        try {
            $validated = $request->validated();

            if (! Auth::user()->hasRole('super_admin')) {
                $validated['paroisse_id'] = Auth::user()->paroisse_id;
            }

            $member = Member::create($validated);

            $this->logInfo('Membre créé', ['member_id' => $member->id]);
            FlashAlert::success('Le membre a été créé avec succès.');

            return redirect()->route('members.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la création du membre', $e, ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la création du membre.');

            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $this->ensureMemberAccessible($member);

        $member->load(['paroisse', 'groupes', 'evenements']);

        return view('members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        $this->ensureMemberAccessible($member);

        $paroisses = Auth::check() && Auth::user()->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : collect();

        return view('members.edit', compact('member', 'paroisses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        try {
            $this->ensureMemberAccessible($member);

            $validated = $request->validated();

            if (! Auth::user()->hasRole('super_admin')) {
                unset($validated['paroisse_id']);
            }

            $member->update($validated);

            $this->logInfo('Membre mis à jour', ['member_id' => $member->id]);
            FlashAlert::success('Le membre a été mis à jour avec succès.');

            return redirect()->route('members.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la mise à jour du membre', $e, [
                'member_id' => $member->id,
                'data' => $request->all(),
            ]);
            FlashAlert::error('Une erreur est survenue lors de la mise à jour du membre.');

            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        try {
            $this->ensureMemberAccessible($member);

            $member->delete();

            $this->logInfo('Membre supprimé', ['member_id' => $member->id]);
            FlashAlert::success('Le membre a été supprimé avec succès.');

            return redirect()->route('members.index');
        } catch (Exception $e) {
            $this->logError('Erreur lors de la suppression du membre', $e, ['member_id' => $member->id]);
            FlashAlert::error('Une erreur est survenue lors de la suppression du membre.');

            return back();
        }
    }

    private function ensureMemberAccessible(Member $member): void
    {
        if (! Auth::check()) {
            abort(403);
        }

        if (Auth::user()->hasRole('super_admin')) {
            return;
        }

        if (Auth::user()->paroisse_id && $member->paroisse_id === Auth::user()->paroisse_id) {
            return;
        }

        abort(404);
    }
}
