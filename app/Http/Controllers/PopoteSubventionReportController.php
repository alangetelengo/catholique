<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueType;
use App\Support\PaginationPerPage;
use App\Support\SubventionMensuelle;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PopoteSubventionReportController extends Controller
{
    use LogsErrors;

    public function index(Request $request): View
    {
        $user = $request->user();

        // Liste des rapports popote, filtrée par paroisse si l'utilisateur n'est pas super admin.
        $query = FinancialReport::query()
            ->with(['paroisse', 'createdBy'])
            ->where('periode_type', 'popote_subvention')
            ->orderByDesc('created_at');

        if (! $user?->hasRole('super_admin')) {
            $query->where('paroisse_id', $user?->paroisse_id);
        } elseif ($request->filled('paroisse_id')) {
            $query->where('paroisse_id', (int) $request->integer('paroisse_id'));
        }

        if ($request->filled('year')) {
            $query->whereYear('date_debut', (int) $request->integer('year'));
        }

        $reports = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $paroisses = $user?->hasRole('super_admin') ? Paroisse::query()->orderBy('nom')->get() : collect();

        return view('popote-reports.index', compact('reports', 'paroisses'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $selectedParoisseId = (int) ($user?->paroisse_id ?: Paroisse::query()->value('id'));
        $paroisses = $user?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($selectedParoisseId)->get();

        return view('popote-reports.create', compact('paroisses', 'selectedParoisseId'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $this->validatePayload($request);
            $data = $this->buildData($validated);

            $report = FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => 'popote_subvention',
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                // Recettes = subvention reçue; Dépenses = alimentation popote.
                'total_recettes' => $data['subvention_recue'],
                'total_depenses' => $data['total_depenses_alimentation'],
                // Solde popote = subvention reçue - dépenses alimentation.
                'solde' => $data['solde'],
                'details_recettes' => $data['details_recettes'],
                'details_depenses' => $data['details_depenses'],
                'created_by' => $request->user()?->id,
            ]);

            $this->logInfo('Rapport subvention popote créé', ['report_id' => $report->id]);

            return redirect()->route('popote-reports.show', $report)->with('success', 'Rapport Subvention Popote enregistré.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur création rapport Subvention Popote', ['data' => $request->all()]);

            return back()->withInput()->with('error', 'Impossible de créer le rapport Subvention Popote.');
        }
    }

    public function show(FinancialReport $popoteReport): View
    {
        $this->authorizeAccess($popoteReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));

        $detailsRecettes = (array) ($popoteReport->details_recettes ?? []);
        $detailsDepenses = (array) ($popoteReport->details_depenses ?? []);
        $rowsRecettes = collect($detailsRecettes['revenues'] ?? []);
        $rowsDepenses = collect($detailsDepenses['expenses'] ?? []);

        return view('popote-reports.show', [
            'report' => $popoteReport,
            'detailsRecettes' => $detailsRecettes,
            'detailsDepenses' => $detailsDepenses,
            'rowsRecettes' => $rowsRecettes,
            'rowsDepenses' => $rowsDepenses,
        ]);
    }

    public function edit(FinancialReport $popoteReport, Request $request): View
    {
        $this->authorizeAccess($popoteReport, $request->user()?->paroisse_id, (bool) $request->user()?->hasRole('super_admin'));

        $paroisses = $request->user()?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($popoteReport->paroisse_id)->get();

        $detailsRecettes = (array) ($popoteReport->details_recettes ?? []);

        return view('popote-reports.edit', compact('popoteReport', 'paroisses', 'detailsRecettes'));
    }

    public function update(Request $request, FinancialReport $popoteReport): RedirectResponse
    {
        try {
            $this->authorizeAccess($popoteReport, $request->user()?->paroisse_id, (bool) $request->user()?->hasRole('super_admin'));
            $validated = $this->validatePayload($request);
            $data = $this->buildData($validated);

            $popoteReport->update([
                'paroisse_id' => $validated['paroisse_id'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'total_recettes' => $data['subvention_recue'],
                'total_depenses' => $data['total_depenses_alimentation'],
                'solde' => $data['solde'],
                'details_recettes' => $data['details_recettes'],
                'details_depenses' => $data['details_depenses'],
            ]);

            $this->logInfo('Rapport subvention popote mis à jour', ['report_id' => $popoteReport->id]);

            return redirect()->route('popote-reports.show', $popoteReport)->with('success', 'Rapport mis à jour.');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur MAJ rapport Subvention Popote', ['report_id' => $popoteReport->id]);

            return back()->withInput()->with('error', 'Mise à jour impossible.');
        }
    }

    public function destroy(FinancialReport $popoteReport): RedirectResponse
    {
        $this->authorizeAccess($popoteReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));
        $popoteReport->delete();
        $this->logInfo('Rapport subvention popote supprimé logiquement', ['report_id' => $popoteReport->id]);

        return redirect()->route('popote-reports.index')->with('success', 'Rapport supprimé.');
    }

    public function print(FinancialReport $popoteReport): View
    {
        $this->authorizeAccess($popoteReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));

        $detailsRecettes = (array) ($popoteReport->details_recettes ?? []);
        $detailsDepenses = (array) ($popoteReport->details_depenses ?? []);

        return view('popote-reports.print', [
            'report' => $popoteReport,
            'detailsRecettes' => $detailsRecettes,
            'detailsDepenses' => $detailsDepenses,
            'rowsRecettes' => collect($detailsRecettes['revenues'] ?? []),
            'rowsDepenses' => collect($detailsDepenses['expenses'] ?? []),
            'paroisse' => $popoteReport->paroisse,
        ]);
    }

    public function exportPdf(FinancialReport $popoteReport)
    {
        $this->authorizeAccess($popoteReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));

        $detailsRecettes = (array) ($popoteReport->details_recettes ?? []);
        $detailsDepenses = (array) ($popoteReport->details_depenses ?? []);

        // Utilisation du wrapper dompdf via container pour éviter l'erreur de facade introuvable.
        $pdf = app('dompdf.wrapper')->loadView('popote-reports.pdf', [
            'report' => $popoteReport,
            'detailsRecettes' => $detailsRecettes,
            'detailsDepenses' => $detailsDepenses,
            'rowsRecettes' => collect($detailsRecettes['revenues'] ?? []),
            'rowsDepenses' => collect($detailsDepenses['expenses'] ?? []),
            'paroisse' => $popoteReport->paroisse,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('rapport-subvention-popote-'.optional($popoteReport->date_debut)->format('Y-m-d').'.pdf');
    }

    private function validatePayload(Request $request): array
    {
        $user = $request->user();
        $validated = $request->validate([
            'paroisse_id' => ['required', 'integer', Rule::exists('paroisses', 'id')],
            'period_kind' => ['required', Rule::in(['mensuel', 'annuel'])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        if (! $user?->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user?->paroisse_id) {
            abort(403);
        }

        return $validated;
    }

    private function buildData(array $validated): array
    {
        $isMonthly = $validated['period_kind'] === 'mensuel';
        $dateDebut = $isMonthly
            ? Carbon::create((int) $validated['year'], (int) $validated['month'], 1)->startOfMonth()
            : Carbon::create((int) $validated['year'], 1, 1)->startOfYear();
        $dateFin = $isMonthly ? $dateDebut->copy()->endOfMonth() : $dateDebut->copy()->endOfYear();

        $popoteType = RevenueType::query()
            ->where('paroisse_id', (int) $validated['paroisse_id'])
            ->where('code', SubventionMensuelle::POPOTE_TYPE_CODE)
            ->first();

        $subventionRevenues = Revenue::query()
            ->where('paroisse_id', (int) $validated['paroisse_id'])
            ->where('statut', 'valide')
            ->when($popoteType, fn ($q) => $q->where('revenue_type_id', $popoteType->id))
            ->whereNotNull('mois_subvention')
            ->when(
                $isMonthly,
                fn ($q) => $q->where('mois_subvention', $dateDebut->format('Y-m')),
                fn ($q) => $q->where('mois_subvention', 'like', (int) $validated['year'].'-%')
            )
            ->orderBy('mois_subvention')
            ->orderBy('date_recette')
            ->orderBy('id')
            ->get();

        $revenueIds = $subventionRevenues->pluck('id');

        $depensesAlimentation = Expense::query()
            ->where('paroisse_id', (int) $validated['paroisse_id'])
            ->where('statut', 'valide')
            ->when($revenueIds->isNotEmpty(), function ($q) use ($revenueIds): void {
                $q->whereHas('fundingSources', fn ($builder) => $builder->whereIn('revenue_id', $revenueIds));
            }, fn ($q) => $q->whereRaw('0 = 1'))
            ->with(['fundingSources' => fn ($q) => $q->whereIn('revenue_id', $revenueIds)])
            ->orderBy('date_depense')
            ->orderBy('id')
            ->get();

        $subventionRecue = (float) $subventionRevenues->sum('montant');
        $totalDepensesAlimentation = (float) ExpenseFundingSource::query()
            ->whereIn('revenue_id', $revenueIds)
            ->whereHas('expense', fn ($q) => $q->where('statut', 'valide'))
            ->sum('montant_alloue');
        $solde = $subventionRecue - $totalDepensesAlimentation;

        $monthlySummary = $this->buildMonthlySummary(
            (int) $validated['paroisse_id'],
            (int) $validated['year'],
            $isMonthly ? (int) $validated['month'] : null,
            $popoteType?->id
        );

        return [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'subvention_recue' => $subventionRecue,
            'total_depenses_alimentation' => $totalDepensesAlimentation,
            'solde' => $solde,
            'details_recettes' => [
                'period_kind' => $validated['period_kind'],
                'month' => $isMonthly ? (int) $validated['month'] : null,
                'year' => (int) $validated['year'],
                'monthly_summary' => $monthlySummary,
                'revenues' => $subventionRevenues->map(fn ($r) => [
                    'id' => $r->id,
                    'mois_subvention' => $r->mois_subvention,
                    'mois_label' => SubventionMensuelle::formatMoisLabel($r->mois_subvention),
                    'date' => optional($r->date_recette)->format('Y-m-d'),
                    'reference' => $r->reference_paiement,
                    'montant' => (float) $r->montant,
                ])->values()->all(),
            ],
            'details_depenses' => [
                'period_kind' => $validated['period_kind'],
                'month' => $isMonthly ? (int) $validated['month'] : null,
                'year' => (int) $validated['year'],
                'expenses' => $depensesAlimentation->map(function ($e) use ($revenueIds) {
                    $montantPopote = (float) $e->fundingSources
                        ->whereIn('revenue_id', $revenueIds->all())
                        ->sum('montant_alloue');

                    return [
                        'id' => $e->id,
                        'date' => optional($e->date_depense)->format('Y-m-d'),
                        'libelle' => $e->libelle,
                        'reference' => $e->facture_reference,
                        'fournisseur' => $e->fournisseur,
                        'montant' => $montantPopote > 0 ? $montantPopote : (float) $e->montant,
                    ];
                })->values()->all(),
            ],
        ];
    }

    /**
     * @return list<array{mois_subvention: string, mois_label: string, subvention_recue: float, depenses: float, solde: float}>
     */
    private function buildMonthlySummary(int $paroisseId, int $year, ?int $onlyMonth, ?int $popoteTypeId): array
    {
        if ($popoteTypeId === null) {
            return [];
        }

        $months = $onlyMonth !== null ? [$onlyMonth] : range(1, 12);
        $summary = [];

        foreach ($months as $month) {
            $moisSubvention = SubventionMensuelle::moisSubventionFromParts($year, $month);

            $revenues = Revenue::query()
                ->where('paroisse_id', $paroisseId)
                ->where('revenue_type_id', $popoteTypeId)
                ->where('statut', 'valide')
                ->where('mois_subvention', $moisSubvention)
                ->get();

            $subventionRecue = (float) $revenues->sum('montant');
            $depenses = $revenues->isNotEmpty()
                ? (float) ExpenseFundingSource::query()
                    ->whereIn('revenue_id', $revenues->pluck('id'))
                    ->whereHas('expense', fn ($q) => $q->where('statut', 'valide'))
                    ->sum('montant_alloue')
                : 0.0;

            if ($subventionRecue === 0.0 && $depenses === 0.0) {
                continue;
            }

            $summary[] = [
                'mois_subvention' => $moisSubvention,
                'mois_label' => SubventionMensuelle::formatMoisLabel($moisSubvention),
                'subvention_recue' => $subventionRecue,
                'depenses' => $depenses,
                'solde' => $subventionRecue - $depenses,
            ];
        }

        return $summary;
    }

    private function authorizeAccess(FinancialReport $report, ?int $userParoisseId, bool $isSuperAdmin): void
    {
        if ($isSuperAdmin) {
            return;
        }
        if ((int) $report->paroisse_id !== (int) $userParoisseId) {
            abort(403);
        }
    }
}
