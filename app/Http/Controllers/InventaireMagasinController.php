<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Models\InventaireMagasin;
use App\Models\Paroisse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventaireMagasinController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = InventaireMagasin::query()->with('paroisse');

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
                    ->orWhereRaw('LOWER(notes) LIKE ?', ["%{$q}%"]);
            });
        }

        $items = $query->orderBy('nom')->paginate(15)->withQueryString();
        $paroisses = $user->hasRole('super_admin') ? Paroisse::orderBy('nom')->get() : collect();

        return view('inventaire-magasin.index', [
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

        return view('inventaire-magasin.create', [
            'item' => new InventaireMagasin(),
            'paroisses' => $paroisses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'unite' => ['nullable', 'string', 'max:50'],
            'quantite' => ['required', 'numeric', 'min:0'],
            'quantite_min_alerte' => ['nullable', 'numeric', 'min:0'],
            'date_peremption' => ['nullable', 'date'],
            'emplacement' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'paroisse_id' => ['nullable', 'exists:paroisses,id'],
        ]);
        if (! $user->hasRole('super_admin')) {
            $validated['paroisse_id'] = $user->paroisse_id;
        }
        InventaireMagasin::create($validated);
        FlashAlert::success('Article ajouté à l\'inventaire des produits alimentaires.');

        return redirect()->route('inventaire-magasin.index');
    }

    public function edit(Request $request, InventaireMagasin $inventaire_magasin): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $inventaire_magasin->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('inventaire-magasin.index');
        }
        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        return view('inventaire-magasin.edit', [
            'item' => $inventaire_magasin,
            'paroisses' => $paroisses,
        ]);
    }

    public function update(Request $request, InventaireMagasin $inventaire_magasin): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $inventaire_magasin->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('inventaire-magasin.index');
        }
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'unite' => ['nullable', 'string', 'max:50'],
            'quantite' => ['required', 'numeric', 'min:0'],
            'quantite_min_alerte' => ['nullable', 'numeric', 'min:0'],
            'date_peremption' => ['nullable', 'date'],
            'emplacement' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'paroisse_id' => ['nullable', 'exists:paroisses,id'],
        ]);
        if (! $user->hasRole('super_admin')) {
            $validated['paroisse_id'] = $user->paroisse_id;
        }
        $inventaire_magasin->update($validated);
        FlashAlert::success('Article mis à jour.');

        return redirect()->route('inventaire-magasin.index');
    }

    public function destroy(Request $request, InventaireMagasin $inventaire_magasin): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole('super_admin') && $inventaire_magasin->paroisse_id !== $user->paroisse_id) {
            FlashAlert::error('Accès non autorisé.');
            return redirect()->route('inventaire-magasin.index');
        }
        $inventaire_magasin->delete();
        FlashAlert::success('Article supprimé.');

        return redirect()->route('inventaire-magasin.index');
    }
}
