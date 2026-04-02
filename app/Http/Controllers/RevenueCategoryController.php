<?php

namespace App\Http\Controllers;

use App\Models\RevenueCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function create(): View
    {
        $category = new RevenueCategory([
            'actif' => true,
            'ordre' => 0,
        ]);

        return view('revenue-categories.create', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:revenue_categories,code'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        RevenueCategory::create($validated);

        return redirect()->route('revenue-categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(RevenueCategory $revenueCategory): View
    {
        $category = $revenueCategory;

        return view('revenue-categories.edit', compact('category'));
    }

    public function update(Request $request, RevenueCategory $revenueCategory): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $revenueCategory->update($validated);

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
}
