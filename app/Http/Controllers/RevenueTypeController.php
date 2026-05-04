<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RevenueTypeController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('application-configuration.index', array_merge(
            $request->query(),
            ['tab' => 'revenue-types']
        ));
    }

    public function create(Request $request): View
    {
        $type = new RevenueType([
            'actif' => true,
            'ordre' => 0,
            'paroisse_id' => $request->user()?->paroisse_id,
        ]);
        $categories = $this->categoriesForTypeForm($request);
        $paroisses = $this->paroissesForForm($request);

        return view('revenue-types.create', compact('type', 'categories', 'paroisses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isSuper = $request->user()?->hasRole('super_admin');
        $userParoisseId = (int) ($request->user()?->paroisse_id ?? 0);

        $rules = [
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'code' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ];

        if ($isSuper) {
            $rules['paroisse_id'] = ['required', 'integer', 'exists:paroisses,id'];
        }

        $validated = $request->validate($rules);

        $paroisseId = $isSuper ? (int) $validated['paroisse_id'] : $userParoisseId;
        if ($paroisseId <= 0) {
            return back()->withErrors([
                'paroisse_id' => 'Aucune paroisse valide pour ce type de recette.',
            ])->withInput();
        }

        $category = RevenueCategory::query()->findOrFail((int) $validated['revenue_category_id']);
        if ((int) ($category->paroisse_id ?? 0) !== $paroisseId) {
            throw ValidationException::withMessages([
                'revenue_category_id' => 'La catégorie choisie n\'appartient pas à la paroisse sélectionnée.',
            ]);
        }

        $validated['code'] = Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);
        $validated['paroisse_id'] = $paroisseId;

        $exists = RevenueType::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette paroisse.'])->withInput();
        }

        RevenueType::create($validated);

        return redirect()->route('revenue-types.index')->with('success', 'Type de recette créé.');
    }

    public function edit(Request $request, RevenueType $revenueType): View
    {
        if (! $request->user()?->hasRole('super_admin') && (int) $revenueType->paroisse_id !== (int) ($request->user()?->paroisse_id ?? 0)) {
            abort(403);
        }

        $type = $revenueType;
        $categories = $this->categoriesForTypeForm($request);
        $paroisses = $this->paroissesForForm($request);

        return view('revenue-types.edit', compact('type', 'categories', 'paroisses'));
    }

    public function update(Request $request, RevenueType $revenueType): RedirectResponse
    {
        if (! $request->user()?->hasRole('super_admin') && (int) $revenueType->paroisse_id !== (int) ($request->user()?->paroisse_id ?? 0)) {
            abort(403);
        }

        $isSuper = $request->user()?->hasRole('super_admin');
        $userParoisseId = (int) ($request->user()?->paroisse_id ?? 0);

        $rules = [
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'code' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ];

        if ($isSuper) {
            $rules['paroisse_id'] = ['required', 'integer', 'exists:paroisses,id'];
        }

        $validated = $request->validate($rules);

        $paroisseId = $isSuper ? (int) $validated['paroisse_id'] : $userParoisseId;
        if ($paroisseId <= 0) {
            return back()->withErrors([
                'paroisse_id' => 'Aucune paroisse valide pour ce type de recette.',
            ])->withInput();
        }

        $category = RevenueCategory::query()->findOrFail((int) $validated['revenue_category_id']);
        if ((int) ($category->paroisse_id ?? 0) !== $paroisseId) {
            throw ValidationException::withMessages([
                'revenue_category_id' => 'La catégorie choisie n\'appartient pas à la paroisse sélectionnée.',
            ]);
        }

        $validated['code'] = Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);
        $validated['paroisse_id'] = $paroisseId;

        $exists = RevenueType::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', $validated['code'])
            ->where('id', '!=', $revenueType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette paroisse.'])->withInput();
        }

        $revenueType->update($validated);

        return redirect()->route('revenue-types.index')->with('success', 'Type de recette mis à jour.');
    }

    public function destroy(Request $request, RevenueType $revenueType): RedirectResponse
    {
        if (! $request->user()?->hasRole('super_admin') && (int) $revenueType->paroisse_id !== (int) ($request->user()?->paroisse_id ?? 0)) {
            abort(403);
        }

        if ($revenueType->revenues()->exists()) {
            return back()->with('error', 'Suppression impossible: ce type est déjà utilisé.');
        }

        $revenueType->delete();

        return redirect()->route('revenue-types.index')->with('success', 'Type de recette supprimé.');
    }

    /**
     * @return EloquentCollection<int, Paroisse>
     */
    private function paroissesForForm(Request $request): EloquentCollection
    {
        if ($request->user()?->hasRole('super_admin')) {
            return Paroisse::query()->orderBy('nom')->get();
        }

        if ($request->user()?->paroisse_id) {
            return Paroisse::query()->whereKey($request->user()->paroisse_id)->get();
        }

        return Paroisse::query()->whereRaw('1 = 0')->get();
    }

    /**
     * @return EloquentCollection<int, RevenueCategory>
     */
    private function categoriesForTypeForm(Request $request): EloquentCollection
    {
        $query = RevenueCategory::query()->with('paroisse')->orderBy('ordre')->orderBy('nom');

        if (! $request->user()?->hasRole('super_admin')) {
            if ($request->user()?->paroisse_id) {
                $query->where('paroisse_id', $request->user()->paroisse_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->get();
    }
}
