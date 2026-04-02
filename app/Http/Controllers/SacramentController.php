<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Member;
use App\Models\Paroisse;
use App\Models\Sacrament;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SacramentController extends Controller
{
    use LogsErrors;

    public const TYPE_PERMISSIONS = [
        'bapteme' => ['view' => 'view_baptisms', 'create' => 'create_baptisms', 'edit' => 'edit_baptisms', 'delete' => 'delete_baptisms'],
        'confirmation' => ['view' => 'view_confirmations', 'create' => 'create_confirmations', 'edit' => 'edit_confirmations', 'delete' => 'delete_confirmations'],
        'communion' => ['view' => 'view_communions', 'create' => 'create_communions', 'edit' => 'edit_communions', 'delete' => 'delete_communions'],
        'mariage' => ['view' => 'view_marriages', 'create' => 'create_marriages', 'edit' => 'edit_marriages', 'delete' => 'delete_marriages'],
        'obseques' => ['view' => 'view_funerals', 'create' => 'create_funerals', 'edit' => 'edit_funerals', 'delete' => 'delete_funerals'],
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $type = $request->string('type')->value();
        if (! $type || ! array_key_exists($type, Sacrament::TYPES)) {
            $type = 'bapteme';
            if (! $request->user()->can(self::TYPE_PERMISSIONS['bapteme']['view'])) {
                foreach (array_keys(Sacrament::TYPES) as $t) {
                    if ($request->user()->can(self::TYPE_PERMISSIONS[$t]['view'])) {
                        $type = $t;
                        break;
                    }
                }
            }

            return redirect()->route('sacraments.index', ['type' => $type]);
        }

        if (! $request->user()->can(self::TYPE_PERMISSIONS[$type]['view'])) {
            abort(403);
        }

        $query = Sacrament::query()->with(['paroisse', 'celebrant', 'beneficiary'])
            ->where('type', $type)
            ->orderByDesc('date_celebration');

        if (! $request->user()->hasRole('super_admin')) {
            if ($request->user()->paroisse_id) {
                $query->where('paroisse_id', $request->user()->paroisse_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        if ($request->user()->hasRole('super_admin') && $request->filled('paroisse_id')) {
            $query->where('paroisse_id', $request->integer('paroisse_id'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date_celebration', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date_celebration', '<=', $request->date('date_to'));
        }

        $sacraments = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $paroisses = $request->user()->hasRole('super_admin') ? Paroisse::orderBy('nom')->get() : collect();

        return view('sacraments.index', [
            'sacraments' => $sacraments,
            'type' => $type,
            'paroisses' => $paroisses,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $type = $request->string('type', 'bapteme')->value();
        if (! array_key_exists($type, Sacrament::TYPES)) {
            $type = 'bapteme';
        }
        if (! $request->user()->can(self::TYPE_PERMISSIONS[$type]['create'])) {
            abort(403);
        }

        $paroisses = $request->user()->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($request->user()->paroisse_id)->get();
        $paroisseId = $request->user()->hasRole('super_admin') && $request->filled('paroisse_id')
            ? $request->integer('paroisse_id')
            : $request->user()->paroisse_id;
        $celebrants = $this->getCelebrantsQuery($request)->get();
        $members = $paroisseId ? Member::where('paroisse_id', $paroisseId)->orderBy('prenom')->orderBy('nom')->get() : collect();

        return view('sacraments.create', [
            'sacrament' => new Sacrament(['type' => $type, 'date_celebration' => now()]),
            'type' => $type,
            'paroisses' => $paroisses,
            'paroisseId' => $paroisseId,
            'celebrants' => $celebrants,
            'members' => $members,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->string('type', 'bapteme')->value();
        if (! array_key_exists($type, Sacrament::TYPES)) {
            FlashAlert::error('Type de sacrement invalide.');

            return redirect()->route('sacraments.index', ['type' => 'bapteme']);
        }
        $this->authorize(self::TYPE_PERMISSIONS[$type]['create']);

        $paroisseRule = $request->user()->hasRole('super_admin')
            ? ['required', 'exists:paroisses,id']
            : ['nullable', 'exists:paroisses,id'];

        $validated = $request->validate([
            'paroisse_id' => $paroisseRule,
            'date_celebration' => ['required', 'date'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'celebrant_id' => ['nullable', 'exists:members,id'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'beneficiary_id' => ['nullable', 'exists:members,id'],
            'notes' => ['nullable', 'string'],
        ]);
        $validated['type'] = $type;
        if (! $request->user()->hasRole('super_admin')) {
            $validated['paroisse_id'] = $request->user()->paroisse_id;
        }

        if (empty($validated['paroisse_id'])) {
            FlashAlert::error('Une paroisse est requise. Associez une paroisse à l’utilisateur ou sélectionnez-en une.');

            return back()->withInput();
        }

        try {
            Sacrament::create($validated);
            FlashAlert::success('Le sacrement a été enregistré avec succès.');

            return redirect()->route('sacraments.index', ['type' => $type]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création sacrement');
            FlashAlert::error('Une erreur est survenue.');

            return back()->withInput();
        }
    }

    public function show(Request $request, Sacrament $sacrament): View
    {
        if (! $request->user()->can(self::TYPE_PERMISSIONS[$sacrament->type]['view'])) {
            abort(403);
        }
        $this->ensureAccess($sacrament);
        $sacrament->load(['paroisse', 'celebrant', 'beneficiary']);

        return view('sacraments.show', compact('sacrament'));
    }

    public function edit(Request $request, Sacrament $sacrament): View
    {
        if (! $request->user()->can(self::TYPE_PERMISSIONS[$sacrament->type]['edit'])) {
            abort(403);
        }
        $this->ensureAccess($sacrament);

        $paroisses = $request->user()->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($sacrament->paroisse_id)->get();
        $celebrants = $this->getCelebrantsQuery($request)->get();
        $members = $sacrament->paroisse_id ? Member::where('paroisse_id', $sacrament->paroisse_id)->orderBy('prenom')->orderBy('nom')->get() : collect();

        return view('sacraments.edit', [
            'sacrament' => $sacrament,
            'type' => $sacrament->type,
            'paroisses' => $paroisses,
            'celebrants' => $celebrants,
            'members' => $members,
        ]);
    }

    public function update(Request $request, Sacrament $sacrament): RedirectResponse
    {
        if (! $request->user()->can(self::TYPE_PERMISSIONS[$sacrament->type]['edit'])) {
            abort(403);
        }
        $this->ensureAccess($sacrament);

        $validated = $request->validate([
            'paroisse_id' => ['nullable', 'exists:paroisses,id'],
            'date_celebration' => ['required', 'date'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'celebrant_id' => ['nullable', 'exists:members,id'],
            'beneficiary_name' => ['nullable', 'string', 'max:255'],
            'beneficiary_id' => ['nullable', 'exists:members,id'],
            'notes' => ['nullable', 'string'],
        ]);
        if (! $request->user()->hasRole('super_admin')) {
            unset($validated['paroisse_id']);
        }

        try {
            $sacrament->update($validated);
            FlashAlert::success('Le sacrement a été mis à jour.');

            return redirect()->route('sacraments.index', ['type' => $sacrament->type]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur mise à jour sacrement');
            FlashAlert::error('Une erreur est survenue.');

            return back()->withInput();
        }
    }

    public function destroy(Request $request, Sacrament $sacrament): RedirectResponse
    {
        if (! $request->user()->can(self::TYPE_PERMISSIONS[$sacrament->type]['delete'])) {
            abort(403);
        }
        $this->ensureAccess($sacrament);
        $type = $sacrament->type;
        try {
            $sacrament->delete();
            FlashAlert::success('Le sacrement a été supprimé.');

            return redirect()->route('sacraments.index', ['type' => $type]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur suppression sacrement');
            FlashAlert::error('Une erreur est survenue.');

            return back();
        }
    }

    private function ensureAccess(Sacrament $sacrament): void
    {
        if ($sacrament->paroisse_id && auth()->user()->paroisse_id && (int) $sacrament->paroisse_id !== (int) auth()->user()->paroisse_id && ! auth()->user()->hasRole('super_admin')) {
            abort(404);
        }
    }

    private function getCelebrantsQuery(Request $request)
    {
        return Member::query()
            ->when(! $request->user()->hasRole('super_admin'), function ($q) use ($request): void {
                if ($request->user()->paroisse_id) {
                    $q->where('paroisse_id', $request->user()->paroisse_id);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->where(function ($q) {
                $q->where('notes', 'like', '%curé%')
                    ->orWhere('notes', 'like', '%abbé%')
                    ->orWhere('notes', 'like', '%père%')
                    ->orWhere('notes', 'like', '%pere%');
            })
            ->orderBy('prenom')
            ->orderBy('nom');
    }
}
