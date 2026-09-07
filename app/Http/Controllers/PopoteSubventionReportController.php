<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\Expense;
use App\Models\ExpenseFundingSource;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Support\PaginationPerPage;
use App\Support\SubventionMensuelle;
use App\Traits\LogsErrors;
use Barryvdh\DomPDF\Facade\Pdf;
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

            return redirect()->route('popote-reports.show', $report)->with('success', 'Rapport Caisse Popote enregistré.');
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

    public function print(FinancialReport $popoteReport): View|RedirectResponse
    {
        try {
            $this->authorizeAccess($popoteReport, request()->user()?->paroisse_id, (bool) request()->user()?->hasRole('super_admin'));

            $detailsRecettes = (array) ($popoteReport->details_recettes ?? []);
            $detailsDepenses = (array) ($popoteReport->details_depenses ?? []);
            $paroisse = $popoteReport->paroisse;

            $pdf = Pdf::loadView('popote-reports.pdf', [
                'report' => $popoteReport,
                'detailsRecettes' => $detailsRecettes,
                'detailsDepenses' => $detailsDepenses,
                'rowsRecettes' => collect($detailsRecettes['revenues'] ?? []),
                'rowsDepenses' => collect($detailsDepenses['expenses'] ?? []),
                'paroisse' => $paroisse,
            ])->setPaper('a4', 'portrait');

            $periodeLabel = optional($popoteReport->date_debut)->format('d/m/Y').' au '.optional($popoteReport->date_fin)->format('d/m/Y');

            return view('financial-reports.viewer-pdf', [
                'content' => $pdf->output(),
                'titre' => 'Rapport Caisse Popote',
                'sousTitre' => ($paroisse?->nom ?? 'Paroisse').' — '.$periodeLabel,
                'downloadName' => 'rapport-subvention-popote-'.optional($popoteReport->date_debut)->format('Y-m-d').'.pdf',
                'retourUrl' => route('popote-reports.show', $popoteReport),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur aperçu PDF rapport popote', ['report_id' => $popoteReport->id]);

            return redirect()->route('popote-reports.show', $popoteReport)
                ->with('error', 'Impossible de générer l\'aperçu PDF.');
        }
    }

    public function exportPdf(FinancialReport $popoteReport): View|RedirectResponse
    {
        return $this->print($popoteReport);
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
        $paroisseId = (int) $validated['paroisse_id'];

        $popoteCaisse = Caisse::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', 'alimentation_popote')
            ->first();

        $credits = collect();
        $depensesAlimentation = collect();
        $subventionRecue = 0.0;
        $totalDepensesAlimentation = 0.0;

        if ($popoteCaisse) {
            $credits = CaisseMouvement::query()
                ->where('caisse_id', $popoteCaisse->id)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->whereDate('date_mouvement', '>=', $dateDebut)
                ->whereDate('date_mouvement', '<=', $dateFin)
                ->orderBy('date_mouvement')
                ->orderBy('id')
                ->get();

            $subventionRecue = (float) $credits->sum('montant');

            $depensesAlimentation = Expense::query()
                ->where('paroisse_id', $paroisseId)
                ->where('statut', 'valide')
                ->whereDate('date_depense', '>=', $dateDebut)
                ->whereDate('date_depense', '<=', $dateFin)
                ->whereHas('fundingSources', fn ($q) => $q->where('caisse_id', $popoteCaisse->id))
                ->with(['fundingSources' => fn ($q) => $q->where('caisse_id', $popoteCaisse->id)])
                ->orderBy('date_depense')
                ->orderBy('id')
                ->get();

            $totalDepensesAlimentation = (float) ExpenseFundingSource::query()
                ->where('caisse_id', $popoteCaisse->id)
                ->whereHas('expense', function ($q) use ($paroisseId, $dateDebut, $dateFin): void {
                    $q->where('paroisse_id', $paroisseId)
                        ->where('statut', 'valide')
                        ->whereDate('date_depense', '>=', $dateDebut)
                        ->whereDate('date_depense', '<=', $dateFin);
                })
                ->sum('montant_alloue');
        }

        $solde = $subventionRecue - $totalDepensesAlimentation;
        $monthlySummary = $this->buildMonthlySummary(
            $paroisseId,
            (int) $validated['year'],
            $isMonthly ? (int) $validated['month'] : null,
            $popoteCaisse?->id
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
                'revenues' => $credits->map(fn (CaisseMouvement $m) => [
                    'id' => $m->id,
                    'mois_subvention' => optional($m->date_mouvement)->format('Y-m'),
                    'mois_label' => SubventionMensuelle::formatMoisLabel(optional($m->date_mouvement)->format('Y-m')),
                    'date' => optional($m->date_mouvement)->format('Y-m-d'),
                    'reference' => $m->type,
                    'montant' => (float) $m->montant,
                    'libelle' => $m->libelle,
                ])->values()->all(),
            ],
            'details_depenses' => [
                'period_kind' => $validated['period_kind'],
                'month' => $isMonthly ? (int) $validated['month'] : null,
                'year' => (int) $validated['year'],
                'expenses' => $depensesAlimentation->map(function (Expense $e) {
                    $montantPopote = (float) $e->fundingSources->sum('montant_alloue');

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
    private function buildMonthlySummary(int $paroisseId, int $year, ?int $onlyMonth, ?int $popoteCaisseId): array
    {
        if ($popoteCaisseId === null) {
            return [];
        }

        $months = $onlyMonth !== null ? [$onlyMonth] : range(1, 12);
        $summary = [];

        foreach ($months as $month) {
            $moisKey = SubventionMensuelle::moisSubventionFromParts($year, $month);
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $subventionRecue = (float) CaisseMouvement::query()
                ->where('caisse_id', $popoteCaisseId)
                ->where('sens', CaisseMouvement::SENS_CREDIT)
                ->whereDate('date_mouvement', '>=', $start)
                ->whereDate('date_mouvement', '<=', $end)
                ->sum('montant');

            $depenses = (float) ExpenseFundingSource::query()
                ->where('caisse_id', $popoteCaisseId)
                ->whereHas('expense', function ($q) use ($paroisseId, $start, $end): void {
                    $q->where('paroisse_id', $paroisseId)
                        ->where('statut', 'valide')
                        ->whereDate('date_depense', '>=', $start)
                        ->whereDate('date_depense', '<=', $end);
                })
                ->sum('montant_alloue');

            if ($subventionRecue === 0.0 && $depenses === 0.0) {
                continue;
            }

            $summary[] = [
                'mois_subvention' => $moisKey,
                'mois_label' => SubventionMensuelle::formatMoisLabel($moisKey),
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
