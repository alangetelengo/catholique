<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Traits\LogsErrors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class RevenueCategoryController extends Controller
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

        $query = RevenueCategory::query()->with('paroisse')->orderBy('ordre')->orderBy('nom');

        if ($user->hasRole('super_admin')) {
            if ($paroisseId) {
                $query->where('paroisse_id', $paroisseId);
            }
        } else {
            $query->where('paroisse_id', $user->paroisse_id);
        }

        $categories = $query->paginate(20)->withQueryString();
        $paroisses = $user->hasRole('super_admin') ? Paroisse::orderBy('nom')->get() : collect();

        return view('revenue-categories.index', [
            'categories' => $categories,
            'paroisses' => $paroisses,
            'paroisseId' => $paroisseId,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        return view('revenue-categories.create', [
            'category' => new RevenueCategory(['actif' => true, 'ordre' => 0]),
            'paroisses' => $paroisses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'paroisse_id' => ['required', 'exists:paroisses,id'],
            'code' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! $user->hasRole('super_admin')) {
            $validated['paroisse_id'] = $user->paroisse_id;
        }

        $validated['code'] = \Illuminate\Support\Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $exists = RevenueCategory::where('paroisse_id', $validated['paroisse_id'])
            ->where('code', $validated['code'])->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette paroisse.'])->withInput();
        }

        try {
            RevenueCategory::create($validated);
            FlashAlert::success('Catégorie de recette créée avec succès.');
            return redirect()->route('revenue-categories.index', ['paroisse_id' => $validated['paroisse_id']]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création catégorie recette');
            FlashAlert::error('Une erreur est survenue.');
            return back()->withInput();
        }
    }

    public function edit(Request $request, RevenueCategory $revenue_category): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $revenue_category->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('revenue-categories.index');
        }

        return view('revenue-categories.edit', [
            'category' => $revenue_category,
        ]);
    }

    public function update(Request $request, RevenueCategory $revenue_category): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $revenue_category->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('revenue-categories.index');
        }

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'actif' => ['boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        try {
            $revenue_category->update($validated);
            FlashAlert::success('Catégorie mise à jour.');
            return redirect()->route('revenue-categories.index', ['paroisse_id' => $revenue_category->paroisse_id]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur mise à jour catégorie recette');
            FlashAlert::error('Une erreur est survenue.');
            return back()->withInput();
        }
    }

    public function destroy(Request $request, RevenueCategory $revenue_category): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $revenue_category->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('revenue-categories.index');
        }

        if ($revenue_category->types()->exists() || $revenue_category->revenues()->exists()) {
            FlashAlert::error('Impossible de supprimer : des types ou recettes utilisent cette catégorie.');
            return back();
        }

        try {
            $paroisseId = $revenue_category->paroisse_id;
            $revenue_category->delete();
            FlashAlert::success('Catégorie supprimée.');
            return redirect()->route('revenue-categories.index', ['paroisse_id' => $paroisseId]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur suppression catégorie recette');
            FlashAlert::error('Une erreur est survenue.');
            return back();
        }
    }
}
