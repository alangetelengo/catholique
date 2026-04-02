<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class RevenueTypeController extends Controller
{
    use LogsErrors;

    public function __construct()
    {
        $this->middleware('permission:view_revenues')->only(['index', 'show']);
        $this->middleware('permission:create_revenues')->only(['create', 'store']);
        $this->middleware('permission:edit_revenues')->only(['edit', 'update']);
        $this->middleware('permission:delete_revenues')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $paroisseId = $request->filled('paroisse_id')
            ? $request->integer('paroisse_id')
            : ($user->hasRole('super_admin') ? null : $user->paroisse_id);

        $query = RevenueType::query()->with(['paroisse', 'category'])
            ->orderBy('ordre')->orderBy('nom');

        if ($user->hasRole('super_admin')) {
            if ($paroisseId) {
                $query->where('paroisse_id', $paroisseId);
            }
        } else {
            $query->where('paroisse_id', $user->paroisse_id);
        }

        if ($request->filled('revenue_category_id')) {
            $query->where('revenue_category_id', $request->integer('revenue_category_id'));
        }

        $types = $query->get();
        $paroisses = $user->hasRole('super_admin') ? Paroisse::orderBy('nom')->get() : collect();
        $categories = $paroisseId
            ? RevenueCategory::where('paroisse_id', $paroisseId)->orderBy('ordre')->get()
            : collect();

        return view('revenue-types.index', [
            'types' => $types,
            'paroisses' => $paroisses,
            'categories' => $categories,
            'paroisseId' => $paroisseId,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $paroisseId = $request->filled('paroisse_id')
            ? $request->integer('paroisse_id')
            : ($user->hasRole('super_admin') ? null : $user->paroisse_id);

        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        $categories = $paroisseId
            ? RevenueCategory::where('paroisse_id', $paroisseId)->orderBy('ordre')->get()
            : collect();

        return view('revenue-types.create', [
            'type' => new RevenueType(['actif' => true, 'ordre' => 0]),
            'paroisses' => $paroisses,
            'categories' => $categories,
            'paroisseId' => $paroisseId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'paroisse_id' => ['required', 'exists:paroisses,id'],
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'code' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! $user->hasRole('super_admin')) {
            $validated['paroisse_id'] = $user->paroisse_id;
        }

        $category = RevenueCategory::find($validated['revenue_category_id']);
        if ($category && $category->paroisse_id != $validated['paroisse_id']) {
            return back()->withErrors(['revenue_category_id' => 'La catégorie doit appartenir à la même paroisse.'])->withInput();
        }

        $validated['paroisse_id'] = $category ? $category->paroisse_id : $validated['paroisse_id'];
        $validated['code'] = \Illuminate\Support\Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $exists = RevenueType::where('paroisse_id', $validated['paroisse_id'])
            ->where('code', $validated['code'])->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette paroisse.'])->withInput();
        }

        try {
            RevenueType::create($validated);
            FlashAlert::success('Type de recette créé avec succès.');
            return redirect()->route('revenue-types.index', ['paroisse_id' => $validated['paroisse_id']]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création type recette');
            FlashAlert::error('Une erreur est survenue.');
            return back()->withInput();
        }
    }

    public function edit(Request $request, RevenueType $revenue_type): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $revenue_type->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('revenue-types.index');
        }

        $categories = RevenueCategory::where('paroisse_id', $revenue_type->paroisse_id)->orderBy('ordre')->get();

        return view('revenue-types.edit', [
            'type' => $revenue_type,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, RevenueType $revenue_type): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $revenue_type->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('revenue-types.index');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = RevenueCategory::find($validated['revenue_category_id']);
        if ($category && $category->paroisse_id != $revenue_type->paroisse_id) {
            return back()->withErrors(['revenue_category_id' => 'La catégorie doit appartenir à la même paroisse.'])->withInput();
        }

        $validated['code'] = \Illuminate\Support\Str::slug($validated['code']);
        $exists = RevenueType::where('paroisse_id', $revenue_type->paroisse_id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $revenue_type->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette paroisse.'])->withInput();
        }

        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        try {
            $revenue_type->update($validated);
            FlashAlert::success('Type mis à jour.');
            return redirect()->route('revenue-types.index', ['paroisse_id' => $revenue_type->paroisse_id]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur mise à jour type recette');
            FlashAlert::error('Une erreur est survenue.');
            return back()->withInput();
        }
    }

    public function destroy(Request $request, RevenueType $revenue_type): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $revenue_type->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('revenue-types.index');
        }

        if ($revenue_type->revenues()->exists()) {
            FlashAlert::error('Impossible de supprimer : des recettes utilisent ce type.');
            return back();
        }

        try {
            $paroisseId = $revenue_type->paroisse_id;
            $revenue_type->delete();
            FlashAlert::success('Type supprimé.');
            return redirect()->route('revenue-types.index', ['paroisse_id' => $paroisseId]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur suppression type recette');
            FlashAlert::error('Une erreur est survenue.');
            return back();
        }
    }
}
