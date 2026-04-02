<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Helpers\ParoisseConfig;
use App\Models\Expense;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Support\PaginationPerPage;
use App\Traits\LogsErrors;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class FinancialReportController extends Controller implements HasMiddleware
{
    use LogsErrors;

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_financial_reports', only: [
                'index', 'list', 'show', 'statistics', 'revenuesWeekly', 'revenuesWeeklyPrint',
                'popoteReport', 'popotePrint', 'chargesFixesReport', 'revenuesByCategory',
            ]),
            new Middleware('permission:generate_financial_reports', only: [
                'store', 'downloadPdf', 'downloadRevenuesWeeklyPdf', 'downloadPopotePdf',
                'storeRevenuesByCategory', 'downloadRevenuesByCategoryPdf',
            ]),
        ];
    }

    public function index(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $request->integer('paroisse_id', $user->hasRole('super_admin') ? null : $user->paroisse_id);
            $selectedMonth = $request->integer('month', now()->month);
            $selectedYear = $request->integer('year', now()->year);

            $report = null;
            if ($selectedParoisseId) {
                $dateDebut = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
                $dateFin = $dateDebut->copy()->endOfMonth();

                $report = $this->calculateReport($selectedParoisseId, $dateDebut, $dateFin);
            }

            return view('financial-reports.index', [
                'paroisses' => $paroisses,
                'selectedParoisseId' => $selectedParoisseId,
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'report' => $report,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des rapports financiers');
            FlashAlert::error('Une erreur est survenue lors du chargement des rapports.');

            return view('financial-reports.index', [
                'paroisses' => collect(),
                'selectedParoisseId' => null,
                'selectedMonth' => now()->month,
                'selectedYear' => now()->year,
                'report' => null,
            ]);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'month' => ['required', 'integer', 'min:1', 'max:12'],
                'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                Log::channel('paroisse')->warning('Rapport financier refusé : paroisse non autorisée', [
                    'user_id' => $user->id,
                    'user_paroisse_id' => $user->paroisse_id,
                    'request_paroisse_id' => $validated['paroisse_id'],
                    'url' => $request->fullUrl(),
                ]);
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
            $dateFin = $dateDebut->copy()->endOfMonth();

            $report = $this->calculateReport($validated['paroisse_id'], $dateDebut, $dateFin);

            $financialReport = FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => 'total',
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'total_recettes' => $report['total_recettes'],
                'total_depenses' => $report['total_depenses'],
                'solde' => $report['solde'],
                'details_recettes' => $report['details_recettes'],
                'details_depenses' => $report['details_depenses'],
                'created_by' => $user->id,
            ]);

            FlashAlert::success('Rapport financier enregistré avec succès.');

            return redirect()->route('financial-reports.show', $financialReport);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de l\'enregistrement du rapport financier', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de l\'enregistrement du rapport.');

            return redirect()->back()->withInput();
        }
    }

    /**
     * Calcule un rapport financier pour une paroisse et une période donnée.
     */
    private function calculateReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        // Recettes popote/subvention uniquement (catégorie de la paroisse)
        $popoteCategory = RevenueCategory::where('paroisse_id', $paroisseId)->where('code', 'popote_subvention')->first();
        $popoteRevenues = Revenue::query()
            ->with('type')
            ->where('paroisse_id', $paroisseId)
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->when($popoteCategory, function ($q) use ($popoteCategory): void {
                $q->where('revenue_category_id', $popoteCategory->id);
            })
            ->get();

        $totalRecettes = $popoteRevenues->sum('montant');

        // Dépenses (toutes catégories)
        $expenses = Expense::query()
            ->where('paroisse_id', $paroisseId)
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->get();

        $totalDepenses = $expenses->sum('montant');

        // Détails par catégories de dépenses (alimentation_popote = dépenses popote / subvention)
        $detailsDepenses = [
            'charge_fixe' => $expenses->where('categorie_charge', 'charge_fixe')->sum('montant'),
            'charge_variable' => $expenses->where('categorie_charge', 'charge_variable')->sum('montant'),
            'charge_exceptionnelle' => $expenses->where('categorie_charge', 'charge_exceptionnelle')->sum('montant'),
            'alimentation_popote' => $expenses->where('categorie_charge', 'alimentation_popote')->sum('montant'),
        ];

        // Détails des recettes popote/subvention par type
        $detailsRecettes = [];
        if ($popoteCategory) {
            foreach ($popoteRevenues->groupBy('revenue_type_id') as $typeId => $revenues) {
                $type = $revenues->first()->type;
                if ($type) {
                    $detailsRecettes[$type->code] = [
                        'nom' => $type->nom,
                        'montant' => $revenues->sum('montant'),
                        'count' => $revenues->count(),
                    ];
                }
            }
        }

        return [
            'total_recettes' => $totalRecettes,
            'total_depenses' => $totalDepenses,
            'solde' => $totalRecettes - $totalDepenses,
            'details_recettes' => $detailsRecettes,
            'details_depenses' => $detailsDepenses,
            'revenues' => $popoteRevenues,
            'expenses' => $expenses,
        ];
    }

    public function list(Request $request): View
    {
        try {
            $user = $request->user();

            $query = FinancialReport::with(['paroisse', 'createdBy'])
                ->orderBy('date_debut', 'desc')
                ->orderBy('created_at', 'desc');

            if (! $user->hasRole('super_admin')) {
                $query->where('paroisse_id', $user->paroisse_id);
            }

            // Filtres
            if ($request->filled('paroisse_id')) {
                $query->where('paroisse_id', $request->integer('paroisse_id'));
            }

            if ($request->filled('year')) {
                $query->whereYear('date_debut', $request->integer('year'));
            }

            $reports = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : collect();

            return view('financial-reports.list', [
                'reports' => $reports,
                'paroisses' => $paroisses,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement de la liste des rapports');
            FlashAlert::error('Une erreur est survenue lors du chargement des rapports.');

            return view('financial-reports.list', [
                'reports' => collect(),
                'paroisses' => collect(),
            ]);
        }
    }

    /**
     * Statistiques financières : totaux annuels et répartition par mois.
     */
    public function statistics(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $request->integer('paroisse_id', $user->hasRole('super_admin') ? null : $user->paroisse_id);
            $selectedYear = $request->integer('year', now()->year);

            $stats = null;
            $byMonth = [];

            if ($selectedParoisseId) {
                $dateDebut = Carbon::create($selectedYear, 1, 1)->startOfDay();
                $dateFin = Carbon::create($selectedYear, 12, 31)->endOfDay();

                $totalRecettes = Revenue::query()
                    ->where('paroisse_id', $selectedParoisseId)
                    ->whereDate('date_recette', '>=', $dateDebut)
                    ->whereDate('date_recette', '<=', $dateFin)
                    ->sum('montant');

                // Seules les dépenses Popote/Alimentation sont déduites des recettes.
                // Les autres (charges fixes, variables, exceptionnelles) sont pour rapports hiérarchie.
                $depensesPopote = Expense::query()
                    ->where('paroisse_id', $selectedParoisseId)
                    ->where('categorie_charge', 'alimentation_popote')
                    ->whereDate('date_depense', '>=', $dateDebut)
                    ->whereDate('date_depense', '<=', $dateFin)
                    ->sum('montant');

                $depensesAutres = Expense::query()
                    ->where('paroisse_id', $selectedParoisseId)
                    ->whereIn('categorie_charge', ['charge_fixe', 'charge_variable', 'charge_exceptionnelle'])
                    ->whereDate('date_depense', '>=', $dateDebut)
                    ->whereDate('date_depense', '<=', $dateFin)
                    ->sum('montant');

                $totalDepenses = $depensesPopote + $depensesAutres;

                $stats = [
                    'total_recettes' => $totalRecettes,
                    'total_depenses' => $totalDepenses,
                    'depenses_popote' => $depensesPopote,
                    'depenses_autres' => $depensesAutres,
                    'solde' => $totalRecettes - $depensesPopote,
                ];

                $moisNoms = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                ];
                for ($m = 1; $m <= 12; $m++) {
                    $debutMois = Carbon::create($selectedYear, $m, 1)->startOfMonth();
                    $finMois = $debutMois->copy()->endOfMonth();
                    $recettesMois = Revenue::query()
                        ->where('paroisse_id', $selectedParoisseId)
                        ->whereDate('date_recette', '>=', $debutMois)
                        ->whereDate('date_recette', '<=', $finMois)
                        ->sum('montant');
                    $depensesPopoteMois = Expense::query()
                        ->where('paroisse_id', $selectedParoisseId)
                        ->where('categorie_charge', 'alimentation_popote')
                        ->whereDate('date_depense', '>=', $debutMois)
                        ->whereDate('date_depense', '<=', $finMois)
                        ->sum('montant');
                    $depensesAutresMois = Expense::query()
                        ->where('paroisse_id', $selectedParoisseId)
                        ->whereIn('categorie_charge', ['charge_fixe', 'charge_variable', 'charge_exceptionnelle'])
                        ->whereDate('date_depense', '>=', $debutMois)
                        ->whereDate('date_depense', '<=', $finMois)
                        ->sum('montant');
                    $byMonth[$m] = [
                        'nom' => $moisNoms[$m],
                        'recettes' => $recettesMois,
                        'depenses_popote' => $depensesPopoteMois,
                        'depenses_autres' => $depensesAutresMois,
                        'depenses' => $depensesPopoteMois + $depensesAutresMois,
                        'solde' => $recettesMois - $depensesPopoteMois,
                    ];
                }
            }

            return view('financial-reports.statistics', [
                'paroisses' => $paroisses,
                'selectedParoisseId' => $selectedParoisseId,
                'selectedYear' => $selectedYear,
                'stats' => $stats,
                'byMonth' => $byMonth,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement des statistiques financières');
            FlashAlert::error('Une erreur est survenue lors du chargement des statistiques.');

            return view('financial-reports.statistics', [
                'paroisses' => collect(),
                'selectedParoisseId' => null,
                'selectedYear' => now()->year,
                'stats' => null,
                'byMonth' => [],
            ]);
        }
    }

    public function show(FinancialReport $financialReport): View|RedirectResponse
    {
        try {
            $user = request()->user();

            // Vérifier l'accès
            if (! $user->hasRole('super_admin') && $financialReport->paroisse_id !== $user->paroisse_id) {
                FlashAlert::error('Vous n\'avez pas accès à ce rapport.');

                return redirect()->route('financial-reports.list');
            }

            $dateDebut = $financialReport->date_debut;
            $dateFin = $financialReport->date_fin;

            if ($financialReport->periode_type === 'revenues_by_category') {
                $details = $financialReport->details_recettes ?? [];
                $categoryId = $details['revenue_category_id'] ?? null;
                $report = $this->calculateRevenuesByCategoryReport($financialReport->paroisse_id, $dateDebut, $dateFin, $categoryId);

                return view('financial-reports.show-revenues-by-category', [
                    'financialReport' => $financialReport,
                    'report' => $report,
                ]);
            }

            $report = $this->calculateReport($financialReport->paroisse_id, $dateDebut, $dateFin);

            return view('financial-reports.show', [
                'financialReport' => $financialReport,
                'report' => $report,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de l\'affichage du rapport', ['report_id' => $financialReport->id]);
            FlashAlert::error('Une erreur est survenue lors de l\'affichage du rapport.');

            return redirect()->route('financial-reports.list');
        }
    }

    public function downloadPdf(FinancialReport $financialReport): Response|RedirectResponse
    {
        try {
            $user = request()->user();

            // Vérifier l'accès
            if (! $user->hasRole('super_admin') && $financialReport->paroisse_id !== $user->paroisse_id) {
                FlashAlert::error('Vous n\'avez pas accès à ce rapport.');

                return redirect()->route('financial-reports.list');
            }

            $dateDebut = $financialReport->date_debut;
            $dateFin = $financialReport->date_fin;

            if ($financialReport->periode_type === 'revenues_by_category') {
                $details = $financialReport->details_recettes ?? [];
                $categoryId = $details['revenue_category_id'] ?? null;
                $report = $this->calculateRevenuesByCategoryReport($financialReport->paroisse_id, $dateDebut, $dateFin, $categoryId);
                $paroisse = $financialReport->paroisse;
                $headerConfig = $this->getHeaderConfig($financialReport->paroisse_id);

                $pdf = Pdf::loadView('financial-reports.revenues-by-category-pdf', [
                    'report' => $report,
                    'paroisse' => $paroisse,
                    'headerConfig' => $headerConfig,
                    'dateDebut' => $dateDebut,
                    'dateFin' => $dateFin,
                    'selectedCategoryId' => $categoryId,
                ])->setPaper('a4', 'landscape');

                $filename = 'rapport-recettes-par-categorie-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'-'.$dateFin->format('Y-m-d').'.pdf';

                return $pdf->download($filename);
            }

            $report = $this->calculateReport($financialReport->paroisse_id, $dateDebut, $dateFin);

            $paroisse = $financialReport->paroisse;
            $headerConfig = $this->getHeaderConfig($financialReport->paroisse_id);

            // Générer le PDF
            $pdf = Pdf::loadView('financial-reports.pdf', [
                'financialReport' => $financialReport,
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
            ])->setPaper('a4', 'portrait');

            $filename = 'rapport-financier-'.$financialReport->paroisse->nom.'-'.$dateDebut->format('Y-m').'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la génération du PDF', ['report_id' => $financialReport->id]);
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->route('financial-reports.show', $financialReport);
        }
    }

    /**
     * Récupère la configuration de l'en-tête pour le PDF
     */
    public function getHeaderConfig(?int $paroisseId): array
    {
        return [
            'logo_path' => ParoisseConfig::get($paroisseId, 'pdf_header_logo', null),
            'logo_width' => ParoisseConfig::get($paroisseId, 'pdf_header_logo_width', '80'),
            'show_logo' => ParoisseConfig::get($paroisseId, 'pdf_header_show_logo', true),
            'title' => ParoisseConfig::get($paroisseId, 'pdf_header_title', null),
            'subtitle' => ParoisseConfig::get($paroisseId, 'pdf_header_subtitle', null),
            'address' => ParoisseConfig::get($paroisseId, 'pdf_header_address', null),
            'phone' => ParoisseConfig::get($paroisseId, 'pdf_header_phone', null),
            'email' => ParoisseConfig::get($paroisseId, 'pdf_header_email', null),
            'custom_text' => ParoisseConfig::get($paroisseId, 'pdf_header_custom_text', null),
            'header_bg_color' => ParoisseConfig::get($paroisseId, 'pdf_header_bg_color', '#003366'),
            'header_text_color' => ParoisseConfig::get($paroisseId, 'pdf_header_text_color', '#FFFFFF'),
        ];
    }

    public function revenuesWeekly(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $request->integer('paroisse_id', $user->hasRole('super_admin') ? null : $user->paroisse_id);
            $selectedWeekStart = $request->input('week_start', now()->startOfWeek()->format('Y-m-d'));
            $selectedMonth = $request->integer('month', now()->month);
            $selectedYear = $request->integer('year', now()->year);
            $periodType = $request->input('period_type', 'week'); // 'week' ou 'month'

            $report = null;
            if ($selectedParoisseId) {
                if ($periodType === 'week') {
                    // Utiliser la date de début de semaine (lundi)
                    $dateDebut = Carbon::parse($selectedWeekStart)->startOfWeek();
                    $dateFin = $dateDebut->copy()->endOfWeek(); // Dimanche
                } else {
                    // Mois
                    $dateDebut = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
                    $dateFin = $dateDebut->copy()->endOfMonth();
                }

                $report = $this->calculateRevenuesWeeklyReport($selectedParoisseId, $dateDebut, $dateFin);
            }

            return view('financial-reports.revenues-weekly', [
                'paroisses' => $paroisses,
                'selectedParoisseId' => $selectedParoisseId,
                'selectedWeekStart' => $selectedWeekStart,
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'periodType' => $periodType,
                'report' => $report,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors du chargement du rapport des revenus hebdomadaire');
            FlashAlert::error('Une erreur est survenue lors du chargement du rapport.');

            return view('financial-reports.revenues-weekly', [
                'paroisses' => collect(),
                'selectedParoisseId' => null,
                'selectedWeekStart' => now()->startOfWeek()->format('Y-m-d'),
                'selectedMonth' => now()->month,
                'selectedYear' => now()->year,
                'periodType' => 'week',
                'report' => null,
            ]);
        }
    }

    /**
     * Vue imprimable du rapport des revenus (Quête ordinaire) — une page, bouton Imprimer.
     */
    public function revenuesWeeklyPrint(Request $request): View|RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'period_type' => ['required', 'in:week,month'],
                'week_start' => ['required_if:period_type,week', 'date'],
                'month' => ['required_if:period_type,month', 'integer', 'min:1', 'max:12'],
                'year' => ['required_if:period_type,month', 'integer', 'min:2000', 'max:2100'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->route('financial-reports.revenues-weekly');
            }

            if ($validated['period_type'] === 'week') {
                $dateDebut = Carbon::parse($validated['week_start'])->startOfWeek();
                $dateFin = $dateDebut->copy()->endOfWeek();
            } else {
                $dateDebut = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
                $dateFin = $dateDebut->copy()->endOfMonth();
            }

            $report = $this->calculateRevenuesWeeklyReport($validated['paroisse_id'], $dateDebut, $dateFin);
            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            return view('financial-reports.revenues-weekly-print', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'periodType' => $validated['period_type'],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de l\'affichage du rapport imprimable');
            FlashAlert::error('Une erreur est survenue.');

            return redirect()->route('financial-reports.revenues-weekly');
        }
    }

    public function downloadRevenuesWeeklyPdf(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'period_type' => ['required', 'in:week,month'],
                'week_start' => ['required_if:period_type,week', 'date'],
                'month' => ['required_if:period_type,month', 'integer', 'min:1', 'max:12'],
                'year' => ['required_if:period_type,month', 'integer', 'min:2000', 'max:2100'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                Log::channel('paroisse')->warning('Rapport revenus (Quête ordinaire) refusé : paroisse non autorisée', [
                    'user_id' => $user->id,
                    'user_paroisse_id' => $user->paroisse_id,
                    'request_paroisse_id' => $validated['paroisse_id'],
                    'url' => $request->fullUrl(),
                ]);
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            if ($validated['period_type'] === 'week') {
                // Utiliser la date de début de semaine (lundi)
                $dateDebut = Carbon::parse($validated['week_start'])->startOfWeek();
                $dateFin = $dateDebut->copy()->endOfWeek();
            } else {
                $dateDebut = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
                $dateFin = $dateDebut->copy()->endOfMonth();
            }

            $report = $this->calculateRevenuesWeeklyReport($validated['paroisse_id'], $dateDebut, $dateFin);
            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            $pdf = Pdf::loadView('financial-reports.revenues-weekly-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'periodType' => $validated['period_type'],
            ])->setPaper('a4', 'portrait');

            $periodLabel = $validated['period_type'] === 'week'
                ? 'semaine-'.$dateDebut->format('Y-m-d')
                : $dateDebut->format('Y-m');
            $filename = 'rapport-revenus-'.$paroisse->nom.'-'.$periodLabel.'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur lors de la génération du PDF des revenus', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->back();
        }
    }

    /**
     * Calcule un rapport des revenus hebdomadaire (semaine/dimanche/total)
     */
    public function calculateRevenuesWeeklyReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $queteCategory = RevenueCategory::where('paroisse_id', $paroisseId)->where('code', 'quete_ordinaire')->first();

        $revenues = Revenue::query()
            ->with('type')
            ->where('paroisse_id', $paroisseId)
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->when($queteCategory, function ($q) use ($queteCategory): void {
                $q->where('revenue_category_id', $queteCategory->id);
            })
            ->get();

        $split = $this->splitRevenuesSemaineDimanche($revenues);

        return array_merge($split, [
            'revenues_all' => $revenues,
        ]);
    }

    /**
     * Découpe des recettes : lundi–samedi vs dimanche (période messe ou jour de la semaine).
     *
     * @return array<string, mixed>
     */
    private function splitRevenuesSemaineDimanche(Collection $revenues): array
    {
        $revenuesSemaine = $revenues->filter(function (Revenue $revenue): bool {
            return $revenue->periode_messe === 'semaine'
                || ($revenue->jour_semaine && in_array($revenue->jour_semaine, ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'], true));
        });

        $revenuesDimanche = $revenues->filter(function (Revenue $revenue): bool {
            return $revenue->periode_messe === 'dimanche'
                || $revenue->jour_semaine === 'dimanche';
        });

        $totalSemaine = $revenuesSemaine->sum('montant');
        $totalDimanche = $revenuesDimanche->sum('montant');
        $totalGeneral = $totalSemaine + $totalDimanche;

        $detailsSemaine = [];
        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        foreach ($jours as $jour) {
            $revenusJour = $revenuesSemaine->filter(fn (Revenue $r): bool => $r->jour_semaine === $jour);
            $detailsSemaine[$jour] = [
                'montant' => $revenusJour->sum('montant'),
                'count' => $revenusJour->count(),
                'revenues' => $revenusJour,
            ];
        }

        $detailsDimanche = [
            'montant' => $totalDimanche,
            'count' => $revenuesDimanche->count(),
            'revenues' => $revenuesDimanche,
        ];

        return [
            'total_semaine' => $totalSemaine,
            'total_dimanche' => $totalDimanche,
            'total_general' => $totalGeneral,
            'details_semaine' => $detailsSemaine,
            'details_dimanche' => $detailsDimanche,
            'revenues_semaine' => $revenuesSemaine,
            'revenues_dimanche' => $revenuesDimanche,
        ];
    }

    /**
     * Rapport Subvention Popote vs Dépenses alimentation (mensuel / annuel).
     * La subvention popote est réservée aux dépenses d'alimentation.
     */
    public function popoteReport(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $request->integer('paroisse_id', $user->hasRole('super_admin') ? null : $user->paroisse_id);
            $periodType = $request->input('period_type', 'month'); // month | year
            $selectedMonth = $request->integer('month', now()->month);
            $selectedYear = $request->integer('year', now()->year);

            $report = null;
            if ($selectedParoisseId) {
                if ($periodType === 'year') {
                    $dateDebut = Carbon::create($selectedYear, 1, 1)->startOfMonth();
                    $dateFin = Carbon::create($selectedYear, 12, 31)->endOfDay();
                } else {
                    $dateDebut = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
                    $dateFin = $dateDebut->copy()->endOfMonth();
                }
                $report = $this->calculatePopoteReport($selectedParoisseId, $dateDebut, $dateFin);
            }

            return view('financial-reports.popote-report', [
                'paroisses' => $paroisses,
                'selectedParoisseId' => $selectedParoisseId,
                'periodType' => $periodType,
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'report' => $report,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur rapport Popote');
            FlashAlert::error('Une erreur est survenue.');

            return view('financial-reports.popote-report', [
                'paroisses' => collect(),
                'selectedParoisseId' => null,
                'periodType' => 'month',
                'selectedMonth' => now()->month,
                'selectedYear' => now()->year,
                'report' => null,
            ]);
        }
    }

    /**
     * Vue imprimable du rapport Subvention Popote.
     */
    public function popotePrint(Request $request): View|RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'period_type' => ['required', 'in:month,year'],
                'month' => ['required_if:period_type,month', 'integer', 'min:1', 'max:12'],
                'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->route('financial-reports.popote');
            }

            if ($validated['period_type'] === 'year') {
                $dateDebut = Carbon::create($validated['year'], 1, 1)->startOfMonth();
                $dateFin = Carbon::create($validated['year'], 12, 31)->endOfDay();
            } else {
                $dateDebut = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
                $dateFin = $dateDebut->copy()->endOfMonth();
            }

            $report = $this->calculatePopoteReport($validated['paroisse_id'], $dateDebut, $dateFin);
            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            return view('financial-reports.popote-print', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'periodType' => $validated['period_type'],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur affichage rapport Popote imprimable');
            FlashAlert::error('Une erreur est survenue.');

            return redirect()->route('financial-reports.popote');
        }
    }

    public function downloadPopotePdf(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'period_type' => ['required', 'in:month,year'],
                'month' => ['required_if:period_type,month', 'integer', 'min:1', 'max:12'],
                'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            if ($validated['period_type'] === 'year') {
                $dateDebut = Carbon::create($validated['year'], 1, 1)->startOfMonth();
                $dateFin = Carbon::create($validated['year'], 12, 31)->endOfDay();
            } else {
                $dateDebut = Carbon::create($validated['year'], $validated['month'], 1)->startOfMonth();
                $dateFin = $dateDebut->copy()->endOfMonth();
            }

            $report = $this->calculatePopoteReport($validated['paroisse_id'], $dateDebut, $dateFin);
            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            $pdf = Pdf::loadView('financial-reports.popote-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'periodType' => $validated['period_type'],
            ])->setPaper('a4', 'portrait');

            $periodLabel = $validated['period_type'] === 'year'
                ? $dateDebut->format('Y')
                : $dateDebut->format('Y-m');
            $filename = 'rapport-popote-'.Str::slug($paroisse->nom).'-'.$periodLabel.'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur génération PDF Popote', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->back();
        }
    }

    private function calculatePopoteReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $popoteCategory = RevenueCategory::where('paroisse_id', $paroisseId)->where('code', 'popote_subvention')->first();

        $subventionRecue = Revenue::query()
            ->where('paroisse_id', $paroisseId)
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->when($popoteCategory, fn ($q) => $q->where('revenue_category_id', $popoteCategory->id))
            ->sum('montant');

        $depensesAlimentation = Expense::query()
            ->where('paroisse_id', $paroisseId)
            ->where('categorie_charge', 'alimentation_popote')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->orderBy('date_depense')
            ->get();

        $totalDepensesAlimentation = $depensesAlimentation->sum('montant');
        $solde = $subventionRecue - $totalDepensesAlimentation;

        return [
            'subvention_recue' => $subventionRecue,
            'depenses_alimentation' => $depensesAlimentation,
            'total_depenses_alimentation' => $totalDepensesAlimentation,
            'solde' => $solde,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ];
    }

    /**
     * Rapport des charges fixes (mensuel / annuel) — pour la hiérarchie.
     * Les charges fixes ne sont déduites d'aucune recette ; ce rapport liste les dépenses enregistrées.
     */
    public function chargesFixesReport(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $request->integer('paroisse_id', $user->hasRole('super_admin') ? null : $user->paroisse_id);
            $periodType = $request->input('period_type', 'month');
            $selectedMonth = $request->integer('month', now()->month);
            $selectedYear = $request->integer('year', now()->year);

            $report = null;
            if ($selectedParoisseId) {
                if ($periodType === 'year') {
                    $dateDebut = Carbon::create($selectedYear, 1, 1)->startOfMonth();
                    $dateFin = Carbon::create($selectedYear, 12, 31)->endOfDay();
                } else {
                    $dateDebut = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
                    $dateFin = $dateDebut->copy()->endOfMonth();
                }
                $report = $this->calculateChargesFixesReport($selectedParoisseId, $dateDebut, $dateFin);
            }

            return view('financial-reports.charges-fixes-report', [
                'paroisses' => $paroisses,
                'selectedParoisseId' => $selectedParoisseId,
                'periodType' => $periodType,
                'selectedMonth' => $selectedMonth,
                'selectedYear' => $selectedYear,
                'report' => $report,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur rapport charges fixes');
            FlashAlert::error('Une erreur est survenue.');

            return view('financial-reports.charges-fixes-report', [
                'paroisses' => collect(),
                'selectedParoisseId' => null,
                'periodType' => 'month',
                'selectedMonth' => now()->month,
                'selectedYear' => now()->year,
                'report' => null,
            ]);
        }
    }

    /**
     * Rapport par catégories de recettes — Filtres : paroisse, période, catégorie.
     */
    public function revenuesByCategory(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $request->integer('paroisse_id', $user->hasRole('super_admin') ? null : $user->paroisse_id);
            $dateDebut = $request->filled('date_debut') ? Carbon::parse($request->date_debut)->startOfDay() : null;
            $dateFin = $request->filled('date_fin') ? Carbon::parse($request->date_fin)->endOfDay() : null;
            $selectedCategoryId = $request->filled('revenue_category_id') ? (int) $request->revenue_category_id : null;

            $categories = collect();
            $report = null;
            if ($selectedParoisseId) {
                $categories = RevenueCategory::with('types')
                    ->where('paroisse_id', $selectedParoisseId)
                    ->where('actif', true)
                    ->orderBy('ordre')
                    ->orderBy('nom')
                    ->get();

                if ($dateDebut && $dateFin) {
                    $report = $this->calculateRevenuesByCategoryReport($selectedParoisseId, $dateDebut, $dateFin, $selectedCategoryId);
                }
            }

            return view('financial-reports.revenues-by-category', [
                'paroisses' => $paroisses,
                'categories' => $categories,
                'selectedParoisseId' => $selectedParoisseId,
                'dateDebut' => $dateDebut?->format('Y-m-d'),
                'dateFin' => $dateFin?->format('Y-m-d'),
                'selectedCategoryId' => $selectedCategoryId,
                'report' => $report,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur rapport par catégories de recettes');
            FlashAlert::error('Une erreur est survenue lors du chargement du rapport.');

            return view('financial-reports.revenues-by-category', [
                'paroisses' => collect(),
                'categories' => collect(),
                'selectedParoisseId' => null,
                'dateDebut' => null,
                'dateFin' => null,
                'selectedCategoryId' => null,
                'report' => null,
            ]);
        }
    }

    public function storeRevenuesByCategory(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'revenue_category_id' => ['nullable', 'exists:revenue_categories,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->route('financial-reports.revenues-by-category');
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $categoryId = $validated['revenue_category_id'] ?? null;

            $report = $this->calculateRevenuesByCategoryReport((int) $validated['paroisse_id'], $dateDebut, $dateFin, $categoryId);

            FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => 'revenues_by_category',
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'total_recettes' => $report['total_general'],
                'total_depenses' => 0,
                'solde' => $report['total_general'],
                'details_recettes' => [
                    'revenue_category_id' => $categoryId,
                    'by_category' => $report['by_category'],
                    'revenues' => $report['revenues']->map(fn ($r) => [
                        'id' => $r->id,
                        'date' => $r->date_recette?->format('Y-m-d'),
                        'category' => $r->category?->nom,
                        'type' => $r->type?->nom,
                        'montant' => (float) $r->montant,
                    ])->toArray(),
                ],
                'details_depenses' => [],
                'created_by' => $user->id,
            ]);

            FlashAlert::success('Rapport par catégories de recettes enregistré avec succès.');

            return redirect()->route('financial-reports.list');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur enregistrement rapport par catégories', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de l\'enregistrement du rapport.');

            return redirect()->back()->withInput();
        }
    }

    public function downloadRevenuesByCategoryPdf(Request $request): Response|RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'revenue_category_id' => ['nullable', 'exists:revenue_categories,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $report = $this->calculateRevenuesByCategoryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $validated['revenue_category_id'] ?? null
            );

            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            $selectedCategoryId = isset($validated['revenue_category_id'])
                ? (int) $validated['revenue_category_id']
                : null;
            $pdfCategoryNom = $selectedCategoryId
                ? RevenueCategory::query()->whereKey($selectedCategoryId)->value('nom')
                : null;

            $pdf = Pdf::loadView('financial-reports.revenues-by-category-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'selectedCategoryId' => $selectedCategoryId,
                'pdfCategoryNom' => $pdfCategoryNom,
            ])->setPaper('a4', 'portrait');

            $filename = 'rapport-recettes-par-categorie-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'-'.$dateFin->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur génération PDF rapport recettes par catégorie', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->back();
        }
    }

    private function calculateRevenuesByCategoryReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin, ?int $categoryId = null): array
    {
        $query = Revenue::query()
            ->with(['category', 'type'])
            ->where('paroisse_id', $paroisseId)
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->where('statut', 'valide');

        if ($categoryId) {
            $query->where('revenue_category_id', $categoryId);
        }

        $revenues = $query->orderBy('date_recette')->orderBy('id')->get();

        $byCategory = [];
        foreach ($revenues->groupBy('revenue_category_id') as $catId => $items) {
            $cat = $items->first()->category;
            $byCategory[$catId] = [
                'nom' => $cat?->nom ?? 'Sans catégorie',
                'code' => $cat?->code ?? '',
                'montant' => $items->sum('montant'),
                'count' => $items->count(),
                'revenues' => $items,
            ];
        }

        $totalGeneral = $revenues->sum('montant');
        $weekly = $this->splitRevenuesSemaineDimanche($revenues);
        $weekly['revenues_all'] = $revenues;

        return [
            'revenues' => $revenues,
            'by_category' => $byCategory,
            'total_general' => $totalGeneral,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'weekly' => $weekly,
        ];
    }

    private function calculateChargesFixesReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $expenses = Expense::query()
            ->where('paroisse_id', $paroisseId)
            ->where('categorie_charge', 'charge_fixe')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->orderBy('date_depense')
            ->get();

        $typeLabels = [
            'carburant' => 'Carburant',
            'hosties' => 'Hosties',
            'internet' => 'Internet',
            'maintenance_materiel' => 'Maintenance matériel',
            'gaz' => 'Gaz',
            'eau' => 'Eau',
            'electricite' => 'Électricité',
            'gardiennage' => 'Gardiennage',
            'salaire_ouvrier' => 'Salaire ouvrier',
            'autre' => 'Autre',
        ];

        return [
            'expenses' => $expenses,
            'total' => $expenses->sum('montant'),
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'type_labels' => $typeLabels,
        ];
    }
}
