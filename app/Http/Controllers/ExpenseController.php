<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\RevenueCategory;
use App\Services\BudgetService;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ExpenseController extends Controller
{
    use LogsErrors;

    public function __construct(
        protected BudgetService $budgetService
    ) {}

    public function index(Request $request): View
    {
        try {
            $expenses = $this->expensesIndexFilteredQuery($request)
                ->with(['createdBy', 'revenueCategory', 'fundingSources.revenueType', 'fundingSources.revenue'])
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

        // Récupérer les types de recettes avec solde disponible > 0
        $revenueTypes = $this->budgetService->getSourcesAvecSolde($userParoisseId);
        $subventionEnvelopes = $this->budgetService->getSubventionEnvelopesForExpenseForm((int) $userParoisseId);

        $expense = new Expense([
            'date_depense' => now()->toDateString(),
            'methode_paiement' => 'especes',
        ]);

        return view('expenses.create', compact('expense', 'revenueCategories', 'revenueTypes', 'subventionEnvelopes'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateExpense($request);
            $validated['created_by'] = $request->user()?->id;

            $fundingSources = $request->input('funding_sources', []);

            DB::transaction(function () use ($validated, $fundingSources, &$expense) {
                // Créer la dépense
                $expense = Expense::create($validated);

                // Créer les sources de financement
                if (! empty($fundingSources)) {
                    foreach ($fundingSources as $index => $source) {
                        if (! empty($source['revenue_type_id']) && ! empty($source['montant_alloue'])) {
                            $expense->fundingSources()->create([
                                'revenue_type_id' => $source['revenue_type_id'],
                                'revenue_id' => ! empty($source['revenue_id']) ? (int) $source['revenue_id'] : null,
                                'montant_alloue' => $source['montant_alloue'],
                                'ordre' => $index + 1,
                            ]);
                        }
                    }
                }
            });

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

        // Récupérer les types de recettes avec solde disponible > 0 (en excluant la dépense actuelle)
        $revenueTypes = $this->budgetService->getSourcesAvecSolde($userParoisseId, $expense);
        $subventionEnvelopes = $this->budgetService->getSubventionEnvelopesForExpenseForm((int) $userParoisseId, $expense);

        // Charger les sources de financement existantes
        $expense->load('fundingSources.revenueType', 'fundingSources.revenue');

        return view('expenses.edit', compact('expense', 'revenueCategories', 'revenueTypes', 'subventionEnvelopes'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        try {
            $validated = $this->validateExpense($request, $expense);
            if (empty($expense->created_by) && $request->user()?->id) {
                $validated['created_by'] = $request->user()?->id;
            }

            $fundingSources = $request->input('funding_sources', []);

            DB::transaction(function () use ($expense, $validated, $fundingSources) {
                // Mettre à jour la dépense
                $expense->update($validated);

                // Supprimer les anciennes sources
                $expense->fundingSources()->delete();

                // Créer les nouvelles sources de financement
                if (! empty($fundingSources)) {
                    foreach ($fundingSources as $index => $source) {
                        if (! empty($source['revenue_type_id']) && ! empty($source['montant_alloue'])) {
                            $expense->fundingSources()->create([
                                'revenue_type_id' => $source['revenue_type_id'],
                                'revenue_id' => ! empty($source['revenue_id']) ? (int) $source['revenue_id'] : null,
                                'montant_alloue' => $source['montant_alloue'],
                                'ordre' => $index + 1,
                            ]);
                        }
                    }
                }
            });

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

    private function validateExpense(Request $request, ?Expense $expense = null): array
    {
        $validated = $request->validate([
            'revenue_category_id' => ['required', 'integer', 'exists:revenue_categories,id'],
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
            'funding_sources' => ['required', 'array', 'min:1'],
            'funding_sources.*.revenue_type_id' => ['required', 'integer', 'exists:revenue_types,id'],
            'funding_sources.*.revenue_id' => ['nullable', 'integer', 'exists:revenues,id'],
            'funding_sources.*.montant_alloue' => ['required', 'numeric', 'min:0'],
        ]);

        // Valider les sources de financement avec le BudgetService
        $fundingSources = $request->input('funding_sources', []);
        $validation = $this->budgetService->validateFundingSources($fundingSources, $expense);

        if (! $validation['valid']) {
            throw ValidationException::withMessages([
                'funding_sources' => $validation['errors'],
            ]);
        }

        // Vérifier que le total des sources = montant de la dépense
        $montantDepense = (float) $validated['montant'];
        $totalAlloue = $validation['total_alloue'];

        if (abs($montantDepense - $totalAlloue) > 0.01) {
            throw ValidationException::withMessages([
                'funding_sources' => [
                    sprintf(
                        'Le total des sources (%s FCFA) doit être égal au montant de la dépense (%s FCFA)',
                        number_format($totalAlloue, 0, ',', ' '),
                        number_format($montantDepense, 0, ',', ' ')
                    ),
                ],
            ]);
        }

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
