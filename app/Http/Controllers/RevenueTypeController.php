<?php

namespace App\Http\Controllers;

use App\Models\RevenueCategory;
use App\Models\RevenueType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    public function create(): View
    {
        $type = new RevenueType([
            'actif' => true,
            'ordre' => 0,
        ]);
        $categories = RevenueCategory::query()->orderBy('ordre')->orderBy('nom')->get();

        return view('revenue-types.create', compact('type', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'code' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $exists = RevenueType::query()
            ->where('revenue_category_id', $validated['revenue_category_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette catégorie.'])->withInput();
        }

        RevenueType::create($validated);

        return redirect()->route('revenue-types.index')->with('success', 'Type de recette créé.');
    }

    public function edit(RevenueType $revenueType): View
    {
        $type = $revenueType;
        $categories = RevenueCategory::query()->orderBy('ordre')->orderBy('nom')->get();

        return view('revenue-types.edit', compact('type', 'categories'));
    }

    public function update(Request $request, RevenueType $revenueType): RedirectResponse
    {
        $validated = $request->validate([
            'revenue_category_id' => ['required', 'exists:revenue_categories,id'],
            'code' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code']);
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $exists = RevenueType::query()
            ->where('revenue_category_id', $validated['revenue_category_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $revenueType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà pour cette catégorie.'])->withInput();
        }

        $revenueType->update($validated);

        return redirect()->route('revenue-types.index')->with('success', 'Type de recette mis à jour.');
    }

    public function destroy(RevenueType $revenueType): RedirectResponse
    {
        if ($revenueType->revenues()->exists()) {
            return back()->with('error', 'Suppression impossible: ce type est déjà utilisé.');
        }

        $revenueType->delete();

        return redirect()->route('revenue-types.index')->with('success', 'Type de recette supprimé.');
    }
}
