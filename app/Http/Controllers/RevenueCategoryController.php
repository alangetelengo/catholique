<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RevenueCategoryController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('application-configuration.index', array_merge(
            $request->query(),
            ['tab' => 'revenue-categories']
        ));
    }

    public function create(Request $request): View
    {
        $category = new RevenueCategory([
            'actif' => true,
            'ordre' => 0,
            'paroisse_id' => $request->user()?->paroisse_id,
        ]);
        $paroisses = $this->paroissesForForm($request);

        return view('revenue-categories.create', compact('category', 'paroisses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isSuper = $request->user()?->hasRole('super_admin');
        $userParoisseId = (int) ($request->user()?->paroisse_id ?? 0);

        $rules = [
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('revenue_categories', 'code')->where(function ($q) use ($request, $isSuper, $userParoisseId): void {
                    $pid = $isSuper ? (int) $request->input('paroisse_id') : $userParoisseId;
                    $q->where('paroisse_id', $pid);
                }),
            ],
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
                'paroisse_id' => 'Aucune paroisse valide. Associez une paroisse à votre compte ou choisissez une paroisse.',
            ])->withInput();
        }

        $validated['paroisse_id'] = $paroisseId;
        $validated['code'] = Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        RevenueCategory::create($validated);

        return redirect()->route('revenue-categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Request $request, RevenueCategory $revenueCategory): View
    {
        $category = $revenueCategory;
        $paroisses = $this->paroissesForForm($request);

        return view('revenue-categories.edit', compact('category', 'paroisses'));
    }

    public function update(Request $request, RevenueCategory $revenueCategory): RedirectResponse
    {
        $isSuper = $request->user()?->hasRole('super_admin');
        if (! $isSuper && (int) ($revenueCategory->paroisse_id ?? 0) !== (int) ($request->user()?->paroisse_id ?? 0)) {
            abort(403);
        }

        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ];

        if ($isSuper) {
            $rules['paroisse_id'] = ['required', 'integer', 'exists:paroisses,id'];
        }

        $validated = $request->validate($rules);

        $paroisseId = $isSuper ? (int) $validated['paroisse_id'] : (int) ($revenueCategory->paroisse_id ?? 0);
        if ($isSuper) {
            $validated['paroisse_id'] = $paroisseId;
        }

        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $previousParoisseId = (int) ($revenueCategory->paroisse_id ?? 0);
        if ($previousParoisseId !== $paroisseId) {
            RevenueType::query()
                ->where('revenue_category_id', $revenueCategory->id)
                ->update(['paroisse_id' => $paroisseId]);
        }

        if ($isSuper) {
            $revenueCategory->update($validated);
        } else {
            $revenueCategory->update([
                'nom' => $validated['nom'],
                'description' => $validated['description'] ?? null,
                'ordre' => $validated['ordre'],
                'actif' => $validated['actif'],
            ]);
        }

        return redirect()->route('revenue-categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(RevenueCategory $revenueCategory): RedirectResponse
    {
        if ($revenueCategory->types()->exists() || $revenueCategory->revenues()->exists()) {
            return back()->with('error', 'Suppression impossible: cette catégorie est déjà utilisée.');
        }

        $revenueCategory->delete();

        return redirect()->route('revenue-categories.index')->with('success', 'Catégorie supprimée.');
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
}
