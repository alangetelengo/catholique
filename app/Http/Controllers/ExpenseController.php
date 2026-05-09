<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ExpenseController extends Controller
{
    use LogsErrors;

    public function index(Request $request): View
    {
        try {
            $expenses = $this->expensesIndexFilteredQuery($request)
                ->with(['createdBy', 'revenueCategory', 'revenueType'])
                ->orderByDesc('date_depense')
                ->orderByDesc('id')
                ->paginate(PaginationPerPage::resolve($request))
                ->withQueryString();

            $totalMontantDepenses = (float) $this->expensesIndexFilteredQuery($request)->sum('montant');
            $montantDerniereDepense = $this->expensesIndexFilteredQuery($request)
                ->orderByDesc('date_depense')
                ->orderByDesc('id')
                ->value('montant');
            $montantDerniereDepense = $montantDerniereDepense !== null ? (float) $montantDerniereDepense : null;

            return view('expenses.index', compact(
                'expenses',
                'totalMontantDepenses',
                'montantDerniereDepense',
            ));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des dépenses');
            throw $e;
        }
    }

    public function create(Request $request): View
    {
        $userParoisseId = $request->user()?->paroisse_id;

        // Récupérer les catégories de recettes (sources des fonds)
        $revenueCategories = RevenueCategory::where('paroisse_id', $userParoisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        // Récupérer tous les types de recettes
        $revenueTypes = RevenueType::where('paroisse_id', $userParoisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        $expense = new Expense([
            'date_depense' => now()->toDateString(),
            'methode_paiement' => 'especes',
        ]);

        return view('expenses.create', compact('expense', 'revenueCategories', 'revenueTypes'));
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

    public function edit(Request $request, Expense $expense): View
    {
        $userParoisseId = $request->user()?->paroisse_id;

        // Récupérer les catégories de recettes (sources des fonds)
        $revenueCategories = RevenueCategory::where('paroisse_id', $userParoisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        // Récupérer tous les types de recettes
        $revenueTypes = RevenueType::where('paroisse_id', $userParoisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        return view('expenses.edit', compact('expense', 'revenueCategories', 'revenueTypes'));
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

    private function expensesIndexFilteredQuery(Request $request): Builder
    {
        $query = Expense::query();

        // Filtrer par catégorie de recette (source des fonds)
        if ($request->filled('revenue_category_id')) {
            $query->where('revenue_category_id', $request->integer('revenue_category_id'));
        }

        // Filtrer par type de recette (source précise des fonds)
        if ($request->filled('revenue_type_id')) {
            $query->where('revenue_type_id', $request->integer('revenue_type_id'));
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

        return $query;
    }

    private function validateExpense(Request $request): array
    {
        $validated = $request->validate([
            'revenue_category_id' => ['required', 'integer', 'exists:revenue_categories,id'],
            'revenue_type_id' => ['required', 'integer', 'exists:revenue_types,id'],
            'date_depense' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:0'],
            'libelle' => ['required', 'string', 'max:500'],
            'facture_reference' => ['nullable', 'string', 'max:255'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
            'methode_paiement' => ['required', 'in:especes,cheque,virement,carte,mobile_money'],
            'notes' => ['nullable', 'string'],
            'piece_facture' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'piece_recu' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'piece_autre' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Calculer automatiquement le jour de la semaine
        $validated['jour_semaine'] = $this->weekdayFromDate($validated['date_depense']);

        // Gérer l'upload des documents justificatifs
        if ($request->hasFile('piece_facture')) {
            $validated['piece_facture_path'] = $request->file('piece_facture')
                ->store('expenses/factures', 'public');
        }

        if ($request->hasFile('piece_recu')) {
            $validated['piece_recu_path'] = $request->file('piece_recu')
                ->store('expenses/recus', 'public');
        }

        if ($request->hasFile('piece_autre')) {
            $validated['piece_autre_path'] = $request->file('piece_autre')
                ->store('expenses/autres', 'public');
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
