<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\InventairePatrimoine;
use App\Models\Paroisse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventairePatrimoineController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = InventairePatrimoine::query()->with('paroisse');

        if (! $user->hasRole('super_admin')) {
            $query->where('paroisse_id', $user->paroisse_id);
        }
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->string('categorie')->value());
        }
        if ($request->filled('q')) {
            $q = $request->string('q')->lower()->value();
            $query->where(function ($sub) use ($q): void {
                $sub->whereRaw('LOWER(nom) LIKE ?', ["%{$q}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$q}%"])
                    ->orWhereRaw('LOWER(reference) LIKE ?', ["%{$q}%"]);
            });
        }

        $items = $query->orderBy('nom')->paginate(15)->withQueryString();
        $paroisses = $user->hasRole('super_admin') ? Paroisse::orderBy('nom')->get() : collect();

        return view('inventaire-patrimoine.index', [
            'items' => $items,
            'paroisses' => $paroisses,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        return view('inventaire-patrimoine.create', [
            'item' => new InventairePatrimoine(),
            'paroisses' => $paroisses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'valeur_estimee' => ['nullable', 'numeric', 'min:0'],
            'date_acquisition' => ['nullable', 'date'],
            'etat' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'paroisse_id' => ['nullable', 'exists:paroisses,id'],
        ]);
        if (! $user->hasRole('super_admin')) {
            $validated['paroisse_id'] = $user->paroisse_id;
        }
        InventairePatrimoine::create($validated);
        FlashAlert::success('Bien ajouté à l\'inventaire patrimoine.');

        return redirect()->route('inventaire-patrimoine.index');
    }

    public function edit(Request $request, InventairePatrimoine $inventaire_patrimoine): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $inventaire_patrimoine->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('inventaire-patrimoine.index');
        }
        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        return view('inventaire-patrimoine.edit', [
            'item' => $inventaire_patrimoine,
            'paroisses' => $paroisses,
        ]);
    }

    public function update(Request $request, InventairePatrimoine $inventaire_patrimoine): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $inventaire_patrimoine->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('inventaire-patrimoine.index');
        }
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'valeur_estimee' => ['nullable', 'numeric', 'min:0'],
            'date_acquisition' => ['nullable', 'date'],
            'etat' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
            'paroisse_id' => ['nullable', 'exists:paroisses,id'],
        ]);
        if (! $user->hasRole('super_admin')) {
            $validated['paroisse_id'] = $user->paroisse_id;
        }
        $inventaire_patrimoine->update($validated);
        FlashAlert::success('Bien mis à jour.');

        return redirect()->route('inventaire-patrimoine.index');
    }

    public function destroy(Request $request, InventairePatrimoine $inventaire_patrimoine): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $inventaire_patrimoine->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('inventaire-patrimoine.index');
        }
        $inventaire_patrimoine->delete();
        FlashAlert::success('Bien supprimé.');

        return redirect()->route('inventaire-patrimoine.index');
    }
}
