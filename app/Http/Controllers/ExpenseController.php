<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ExpenseController extends Controller
{
    use LogsErrors;

    public function index(Request $request): View
    {
        try {
            $query = Expense::query()
                ->with('createdBy')
                ->orderByDesc('date_depense')
                ->orderByDesc('id');

            if ($request->filled('categorie_charge')) {
                $query->where('categorie_charge', $request->string('categorie_charge')->value());
            }

            if ($request->filled('type_charge')) {
                $query->where('type_charge', $request->string('type_charge')->value());
            }

            if ($request->filled('date_from')) {
                $query->whereDate('date_depense', '>=', $request->date('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('date_depense', '<=', $request->date('date_to'));
            }

            if ($request->filled('q')) {
                $search = mb_strtolower($request->string('q')->value());
                $query->where(function ($builder) use ($search): void {
                    $builder
                        ->whereRaw('LOWER(notes) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(fournisseur) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(facture_reference) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(libelle) LIKE ?', ["%{$search}%"]);
                });
            }

            $expenses = $query->paginate(20)->withQueryString();

            return view('expenses.index', compact('expenses'));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des dépenses');
            throw $e;
        }
    }

    public function create(): View
    {
        $expense = new Expense([
            'date_depense' => now()->toDateString(),
            'categorie_charge' => 'charge_fixe',
            'type_charge' => 'autre',
            'methode_paiement' => 'especes',
        ]);

        return view('expenses.create', compact('expense'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateExpense($request);
            $validated['created_by'] = $request->user()?->id;

            $expense = Expense::create($validated);
            $this->logInfo('Dépense créée', ['expense_id' => $expense->id, 'montant' => $expense->montant]);

            return redirect()->route('expenses.index')->with('success', 'Dépense enregistrée avec succès.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création de la dépense', ['data' => $request->all()]);
            throw $e;
        }
    }

    /**
     * Pas de fiche détail : les liens GET /expenses/{id} (favoris, anciennes URL) redirigent vers l’édition.
     */
    public function show(Expense $expense): RedirectResponse
    {
        return redirect()->route('expenses.edit', $expense);
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        try {
            $validated = $this->validateExpense($request);
            if (empty($expense->created_by) && $request->user()?->id) {
                $validated['created_by'] = $request->user()?->id;
            }

            $expense->update($validated);
            $this->logInfo('Dépense mise à jour', ['expense_id' => $expense->id, 'montant' => $expense->montant]);

            return redirect()->route('expenses.index')->with('success', 'Dépense mise à jour.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la mise à jour de la dépense', ['expense_id' => $expense->id]);
            throw $e;
        }
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        try {
            $expense->delete();
            $this->logInfo('Dépense supprimée logiquement', ['expense_id' => $expense->id]);

            return redirect()->route('expenses.index')->with('success', 'Dépense supprimée.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la suppression de la dépense', ['expense_id' => $expense->id]);
            throw $e;
        }
    }

    private function validateExpense(Request $request): array
    {
        $validated = $request->validate([
            'categorie_charge' => ['required', 'in:charge_fixe,charge_variable,charge_exceptionnelle,alimentation_popote'],
            'type_charge' => ['nullable', 'in:carburant,hosties,internet,maintenance_materiel,gaz,eau,electricite,gardiennage,salaire_ouvrier,autre,alimentation'],
            'date_depense' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0'],
            'jour_semaine' => ['nullable', 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche'],
            'libelle' => ['nullable', 'string', 'max:500'],
            'facture_reference' => ['nullable', 'string', 'max:255'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['categorie_charge'] === 'alimentation_popote') {
            if (empty($validated['libelle'])) {
                throw ValidationException::withMessages([
                    'libelle' => 'Le libellé est obligatoire pour une dépense alimentation popote.',
                ]);
            }
            $validated['type_charge'] = 'alimentation';
            $validated['jour_semaine'] = $this->weekdayFromDate($validated['date_depense']);
        } else {
            if (empty($validated['type_charge']) || $validated['type_charge'] === 'alimentation') {
                throw ValidationException::withMessages([
                    'type_charge' => 'Le type de charge est obligatoire.',
                ]);
            }
            $validated['jour_semaine'] = null;
            $validated['libelle'] = null;
        }

        return $validated;
    }

    private function weekdayFromDate(string $date): string
    {
        $weekdayMap = [
            0 => 'dimanche',
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
        ];

        return $weekdayMap[Carbon::parse($date)->dayOfWeek] ?? 'lundi';
    }
}
