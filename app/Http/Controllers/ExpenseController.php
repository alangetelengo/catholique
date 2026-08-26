<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Services\CaisseService;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ExpenseController extends Controller
{
    use LogsErrors;

    public function __construct(
        protected CaisseService $caisseService
    ) {}

    public function index(Request $request): View
    {
        try {
            $userParoisseId = (int) $request->user()?->paroisse_id;

            $expenses = $this->expensesIndexFilteredQuery($request)
                ->with(['createdBy', 'expenseType', 'fundingSources.caisse'])
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
            $expenseTypes = ExpenseType::query()->where('actif', true)->orderBy('ordre')->orderBy('nom')->get();
            $caisses = $this->caisseService->getCaissesAvecSolde($userParoisseId, onlyOperatives: true);

            return view('expenses.index', compact(
                'expenses',
                'totalMontantDepenses',
                'montantDerniereDepense',
                'expenseTypes',
                'caisses',
            ));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des dépenses');
            throw $e;
        }
    }

    public function create(Request $request): View
    {
        $userParoisseId = (int) $request->user()?->paroisse_id;

        $caisses = $this->caisseService->getCaissesAvecSolde($userParoisseId, onlyOperatives: true, onlyWithSolde: true);
        $expenseTypes = ExpenseType::query()->where('actif', true)->orderBy('ordre')->orderBy('nom')->get();

        $expense = new Expense([
            'date_depense' => now()->toDateString(),
            'methode_paiement' => 'especes',
        ]);

        return view('expenses.create', compact('expense', 'caisses', 'expenseTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validateExpense($request);
            $validated['created_by'] = $request->user()?->id;
            $validated['paroisse_id'] = $request->user()?->paroisse_id;

            $fundingSources = $request->input('funding_sources', []);

            DB::transaction(function () use ($validated, $fundingSources, &$expense) {
                $expense = Expense::create($validated);

                if (! empty($fundingSources)) {
                    foreach ($fundingSources as $index => $source) {
                        if (! empty($source['caisse_id']) && ! empty($source['montant_alloue'])) {
                            $expense->fundingSources()->create([
                                'caisse_id' => $source['caisse_id'],
                                'montant_alloue' => $source['montant_alloue'],
                                'ordre' => $index + 1,
                            ]);
                        }
                    }
                }

                $expense->load('fundingSources');
                $this->caisseService->syncDepenseMouvements($expense, $fundingSources);
            });

            $this->logInfo('Dépense créée', ['expense_id' => $expense->id, 'montant' => $expense->montant]);

            return redirect()->route('expenses.index')->with('success', 'Dépense enregistrée avec succès.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la création de la dépense', ['data' => $request->all()]);
            throw $e;
        }
    }

    public function show(Expense $expense): RedirectResponse
    {
        return redirect()->route('expenses.edit', $expense);
    }

    public function edit(Request $request, Expense $expense): View
    {
        $userParoisseId = (int) $request->user()?->paroisse_id;

        $caisses = $this->caisseService->getCaissesAvecSolde($userParoisseId, $expense, onlyOperatives: true);
        $usedCaisseIds = $expense->fundingSources()->whereNotNull('caisse_id')->pluck('caisse_id');
        $caisses = $caisses->filter(function ($caisse) use ($usedCaisseIds) {
            return round((float) ($caisse->solde_disponible ?? 0), 2) > 0
                || $usedCaisseIds->contains($caisse->id);
        })->values();

        $expenseTypes = ExpenseType::query()
            ->where(function ($query) use ($expense): void {
                $query->where('actif', true);
                if ($expense->expense_type_id) {
                    $query->orWhere('id', $expense->expense_type_id);
                }
            })
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        $expense->load('fundingSources.caisse', 'expenseType');

        return view('expenses.edit', compact('expense', 'caisses', 'expenseTypes'));
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
                $expense->update($validated);
                $expense->fundingSources()->delete();

                if (! empty($fundingSources)) {
                    foreach ($fundingSources as $index => $source) {
                        if (! empty($source['caisse_id']) && ! empty($source['montant_alloue'])) {
                            $expense->fundingSources()->create([
                                'caisse_id' => $source['caisse_id'],
                                'montant_alloue' => $source['montant_alloue'],
                                'ordre' => $index + 1,
                            ]);
                        }
                    }
                }

                $expense->load('fundingSources');
                $this->caisseService->syncDepenseMouvements($expense, $fundingSources);
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
            DB::transaction(function () use ($expense): void {
                $this->caisseService->removeDepenseMouvements($expense);
                $expense->delete();
            });
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

        if ($request->filled('expense_type_id')) {
            $query->where('expense_type_id', $request->integer('expense_type_id'));
        }

        if ($request->filled('caisse_id')) {
            $caisseId = $request->integer('caisse_id');
            $query->whereHas('fundingSources', function (Builder $builder) use ($caisseId): void {
                $builder->where('caisse_id', $caisseId);
            });
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
            'expense_type_id' => [
                'required',
                'integer',
                Rule::exists('expense_types', 'id')->where(function ($query) use ($expense): void {
                    $query->where('actif', true);
                    if ($expense?->expense_type_id) {
                        $query->orWhere('id', $expense->expense_type_id);
                    }
                }),
            ],
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
            'funding_sources.*.caisse_id' => ['required', 'integer', 'exists:caisses,id'],
            'funding_sources.*.montant_alloue' => ['required', 'numeric', 'min:0.01'],
        ]);

        // Financement via caisses uniquement — plus de catégorie de reporting.
        $validated['revenue_category_id'] = null;
        $validated['revenue_type_id'] = null;

        $fundingSources = $request->input('funding_sources', []);
        $paroisseId = $expense?->paroisse_id
            ? (int) $expense->paroisse_id
            : (int) $request->user()?->paroisse_id;
        $validation = $this->caisseService->validateFundingSources($fundingSources, $expense, $paroisseId);

        if (! $validation['valid']) {
            throw ValidationException::withMessages([
                'funding_sources' => $validation['errors'],
            ]);
        }

        $montantDepense = (float) $validated['montant'];
        $totalAlloue = $validation['total_alloue'];

        if (abs($montantDepense - $totalAlloue) > 0.01) {
            throw ValidationException::withMessages([
                'funding_sources' => [
                    sprintf(
                        'Le total des caisses (%s FCFA) doit être égal au montant de la dépense (%s FCFA)',
                        number_format($totalAlloue, 0, ',', ' '),
                        number_format($montantDepense, 0, ',', ' ')
                    ),
                ],
            ]);
        }

        $validated['jour_semaine'] = $this->weekdayFromDate($validated['date_depense']);

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

        $validated['statut'] = $validated['statut'] ?? 'valide';

        return $validated;
    }

    private function weekdayFromDate(string $date): string
    {
        $map = [
            0 => 'dimanche',
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
        ];

        return $map[Carbon::parse($date)->dayOfWeek] ?? 'lundi';
    }
}
