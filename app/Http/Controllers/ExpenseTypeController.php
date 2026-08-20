<?php

namespace App\Http\Controllers;

use App\Models\ExpenseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExpenseTypeController extends Controller
{
    private function ensureSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('super_admin'), 403, 'Action réservée aux administrateurs.');
    }

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('application-configuration.index', array_merge(
            $request->query(),
            ['tab' => 'expense-types']
        ));
    }

    public function create(Request $request): View
    {
        $this->ensureSuperAdmin($request);

        $type = new ExpenseType([
            'actif' => true,
            'ordre' => 0,
        ]);

        return view('expense-types.create', compact('type'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:expense_types,code'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $exists = ExpenseType::query()->where('code', $validated['code'])->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà.'])->withInput();
        }

        ExpenseType::query()->create($validated);

        return redirect()->route('expense-types.index')->with('success', 'Type de dépense créé.');
    }

    public function edit(Request $request, ExpenseType $expenseType): View
    {
        $this->ensureSuperAdmin($request);

        $type = $expenseType;

        return view('expense-types.edit', compact('type'));
    }

    public function update(Request $request, ExpenseType $expenseType): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:expense_types,code,'.$expenseType->id],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'actif' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        $validated['actif'] = $request->boolean('actif');
        $validated['ordre'] = (int) ($validated['ordre'] ?? 0);

        $exists = ExpenseType::query()
            ->where('code', $validated['code'])
            ->where('id', '!=', $expenseType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà.'])->withInput();
        }

        $expenseType->update($validated);

        return redirect()->route('expense-types.index')->with('success', 'Type de dépense mis à jour.');
    }

    public function destroy(Request $request, ExpenseType $expenseType): RedirectResponse
    {
        $this->ensureSuperAdmin($request);

        if ($expenseType->expenses()->exists()) {
            return back()->with('error', 'Suppression impossible : ce type est déjà utilisé par des dépenses.');
        }

        $expenseType->delete();

        return redirect()->route('expense-types.index')->with('success', 'Type de dépense supprimé.');
    }
}
