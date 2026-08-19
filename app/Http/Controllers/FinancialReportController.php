<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Helpers\ParoisseConfig;
use App\Models\Expense;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Support\FinancialReportSignatories;
use App\Support\PaginationPerPage;
use App\Support\SubventionMensuelle;
use App\Traits\LogsErrors;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
     * Recettes prises en compte dans le rapport hub mensuel (hors Procure).
     *
     * @var list<string>
     */
    private const REVENUE_CATEGORY_CODES_HUB = [
        'quete_ordinaire',
        'quete_extraordinaire',
        'location',
        'popote_subvention',
    ];

    /**
     * @return list<string>
     */
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_financial_reports', only: [
                'index', 'list', 'show', 'statistics', 'revenuesWeekly', 'revenuesWeeklyPrint',
                'revenuesByCategory',
                'revenueCategoriesForParoisse', 'revenueTypesForCategory', 'revenuesByCategoryCalculate',
                'expensesByCategory', 'expensesByCategoryCalculate',
            ]),
            new Middleware('permission:generate_financial_reports', only: [
                'store', 'destroy', 'downloadPdf', 'downloadRevenuesWeeklyPdf',
                'storeRevenuesByCategory', 'downloadRevenuesByCategoryPdf', 'downloadExpensesByCategoryPdf',
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

    public function destroy(Request $request, FinancialReport $financialReport): RedirectResponse
    {
        try {
            $user = $request->user();

            if (! $user->hasRole('super_admin') && (int) $financialReport->paroisse_id !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous n\'avez pas accès à ce rapport.');

                return redirect()->route('financial-reports.list');
            }

            $financialReport->delete();
            $this->logInfo('Rapport financier supprimé (soft delete)', ['report_id' => $financialReport->id]);
            FlashAlert::success('Le rapport enregistré a été supprimé.');

            return redirect()->route('financial-reports.list');
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur suppression rapport financier', ['report_id' => $financialReport->id]);
            FlashAlert::error('La suppression du rapport a échoué.');

            return redirect()->route('financial-reports.list');
        }
    }

    /**
     * Calcule un rapport financier pour une paroisse et une période donnée.
     */
    private function calculateReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $revenues = Revenue::query()
            ->with(['category', 'type'])
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->whereHas('category', function ($q) use ($paroisseId): void {
                $q->where('paroisse_id', $paroisseId)
                    ->whereIn('code', self::REVENUE_CATEGORY_CODES_HUB);
            })
            ->orderBy('date_recette')
            ->get();

        $totalRecettes = (float) $revenues->sum('montant');

        $expenses = Expense::query()
            ->with(['revenueCategory', 'fundingSources.revenueType'])
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin)
            ->orderBy('date_depense')
            ->get();

        $totalDepenses = (float) $expenses->sum('montant');

        // Regroupement des dépenses par catégorie de revenu source
        $detailsDepenses = [];
        foreach ($expenses->groupBy('revenue_category_id') as $categoryId => $categoryExpenses) {
            $category = RevenueCategory::find($categoryId);
            if ($category) {
                $detailsDepenses[$category->code] = (float) $categoryExpenses->sum('montant');
            }
        }

        $detailsRecettes = [];
        foreach (self::REVENUE_CATEGORY_CODES_HUB as $code) {
            $cat = RevenueCategory::query()
                ->where('paroisse_id', $paroisseId)
                ->where('code', $code)
                ->first();
            if (! $cat) {
                continue;
            }
            $subset = $revenues->where('revenue_category_id', $cat->id);
            $detailsRecettes[] = [
                'code' => $code,
                'nom' => $cat->nom,
                'montant' => (float) $subset->sum('montant'),
                'count' => $subset->count(),
            ];
        }

        return [
            'total_recettes' => $totalRecettes,
            'total_depenses' => $totalDepenses,
            'solde' => $totalRecettes - $totalDepenses,
            'details_recettes' => $detailsRecettes,
            'details_depenses' => $detailsDepenses,
            'revenues' => $revenues,
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

                $totalRecettes = (float) Revenue::query()
                    ->where('paroisse_id', $selectedParoisseId)
                    ->where('statut', 'valide')
                    ->whereDate('date_recette', '>=', $dateDebut)
                    ->whereDate('date_recette', '<=', $dateFin)
                    ->whereHas('category', function ($q) use ($selectedParoisseId): void {
                        $q->where('paroisse_id', $selectedParoisseId)
                            ->whereIn('code', self::REVENUE_CATEGORY_CODES_HUB);
                    })
                    ->sum('montant');

                $totalDepenses = (float) Expense::query()
                    ->where('paroisse_id', $selectedParoisseId)
                    ->where('statut', 'valide')
                    ->whereDate('date_depense', '>=', $dateDebut)
                    ->whereDate('date_depense', '<=', $dateFin)
                    ->sum('montant');

                $stats = [
                    'total_recettes' => $totalRecettes,
                    'total_depenses' => $totalDepenses,
                    'solde' => $totalRecettes - $totalDepenses,
                ];

                $moisNoms = [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
                ];
                for ($m = 1; $m <= 12; $m++) {
                    $debutMois = Carbon::create($selectedYear, $m, 1)->startOfMonth();
                    $finMois = $debutMois->copy()->endOfMonth();
                    $recettesMois = (float) Revenue::query()
                        ->where('paroisse_id', $selectedParoisseId)
                        ->where('statut', 'valide')
                        ->whereDate('date_recette', '>=', $debutMois)
                        ->whereDate('date_recette', '<=', $finMois)
                        ->whereHas('category', function ($q) use ($selectedParoisseId): void {
                            $q->where('paroisse_id', $selectedParoisseId)
                                ->whereIn('code', self::REVENUE_CATEGORY_CODES_HUB);
                        })
                        ->sum('montant');
                    $depensesMois = (float) Expense::query()
                        ->where('paroisse_id', $selectedParoisseId)
                        ->where('statut', 'valide')
                        ->whereDate('date_depense', '>=', $debutMois)
                        ->whereDate('date_depense', '<=', $finMois)
                        ->sum('montant');
                    $byMonth[$m] = [
                        'nom' => $moisNoms[$m],
                        'recettes' => $recettesMois,
                        'depenses' => $depensesMois,
                        'solde' => $recettesMois - $depensesMois,
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
                $typeId = isset($details['revenue_type_id']) ? (int) $details['revenue_type_id'] : null;
                $report = $this->calculateRevenuesByCategoryReport($financialReport->paroisse_id, $dateDebut, $dateFin, $categoryId, $typeId);

                $layout = $this->revenuesByCategoryReportLayout($typeId, $categoryId);

                return view('financial-reports.show-revenues-by-category', [
                    'financialReport' => $financialReport,
                    'report' => $report,
                    'selectedCategoryId' => $categoryId,
                    'selectedTypeId' => $typeId,
                    ...$layout,
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
                $typeId = isset($details['revenue_type_id']) ? (int) $details['revenue_type_id'] : null;
                $report = $this->calculateRevenuesByCategoryReport($financialReport->paroisse_id, $dateDebut, $dateFin, $categoryId, $typeId);
                $paroisse = $financialReport->paroisse;
                $headerConfig = $this->getHeaderConfig($financialReport->paroisse_id);

                $pdfCategoryNom = $categoryId ? RevenueCategory::query()->whereKey($categoryId)->value('nom') : null;
                $pdfTypeNom = $typeId ? RevenueType::query()->whereKey($typeId)->value('nom') : null;
                $layout = $this->revenuesByCategoryReportLayout($typeId, $categoryId);

                $pdf = Pdf::loadView('financial-reports.revenues-by-category-pdf', [
                    'report' => $report,
                    'paroisse' => $paroisse,
                    'headerConfig' => $headerConfig,
                    'dateDebut' => $dateDebut,
                    'dateFin' => $dateFin,
                    'selectedCategoryId' => $categoryId,
                    'selectedTypeId' => $typeId,
                    'pdfCategoryNom' => $pdfCategoryNom,
                    'pdfTypeNom' => $pdfTypeNom,
                    ...$layout,
                ])->setPaper('a4', 'portrait');

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
                'signataires' => FinancialReportSignatories::defaultPdfBlocks(),
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
        $revenues = Revenue::query()
            ->with('type')
            ->where('paroisse_id', $paroisseId)
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->where('statut', 'valide')
            ->whereHas('category', function ($q) use ($paroisseId): void {
                $q->where('paroisse_id', $paroisseId)
                    ->where('code', 'quete_ordinaire');
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
     * Rapport des charges fixes (mensuel / annuel) — pour la hiérarchie.
     * Les charges fixes ne sont déduites d'aucune recette ; ce rapport liste les dépenses enregistrées.
     */
    /**
     * Rapport par catégories de recettes — page initiale (sans requête GET longue) ; calcul via AJAX.
     */
    public function revenuesByCategory(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $user->hasRole('super_admin')
                ? null
                : (int) $user->paroisse_id;

            $now = now();
            $dateDebut = $now->copy()->startOfMonth()->format('Y-m-d');
            $dateFin = $now->copy()->endOfMonth()->format('Y-m-d');

            $categories = collect();
            $types = collect();

            if ($selectedParoisseId) {
                $categories = RevenueCategory::query()
                    ->where('paroisse_id', $selectedParoisseId)
                    ->where('actif', true)
                    ->orderBy('ordre')
                    ->orderBy('nom')
                    ->get();
            }

            return view('financial-reports.revenues-by-category', [
                'paroisses' => $paroisses,
                'categories' => $categories,
                'types' => $types,
                'selectedParoisseId' => $selectedParoisseId,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'selectedCategoryId' => null,
                'selectedTypeId' => null,
                'report' => null,
                'ajaxRoutes' => [
                    'categories' => route('financial-reports.revenues-by-category.revenue-categories'),
                    'types' => route('financial-reports.revenues-by-category.revenue-types'),
                    'calculate' => route('financial-reports.revenues-by-category.calculate'),
                ],
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur rapport par catégories de recettes');
            FlashAlert::error('Une erreur est survenue lors du chargement du rapport.');

            return view('financial-reports.revenues-by-category', [
                'paroisses' => collect(),
                'categories' => collect(),
                'types' => collect(),
                'selectedParoisseId' => null,
                'dateDebut' => now()->startOfMonth()->format('Y-m-d'),
                'dateFin' => now()->endOfMonth()->format('Y-m-d'),
                'selectedCategoryId' => null,
                'selectedTypeId' => null,
                'report' => null,
                'ajaxRoutes' => [
                    'categories' => route('financial-reports.revenues-by-category.revenue-categories'),
                    'types' => route('financial-reports.revenues-by-category.revenue-types'),
                    'calculate' => route('financial-reports.revenues-by-category.calculate'),
                ],
            ]);
        }
    }

    public function revenueCategoriesForParoisse(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
        ]);
        $paroisseId = (int) $validated['paroisse_id'];

        if (! $user->hasRole('super_admin') && (int) $user->paroisse_id !== $paroisseId) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $categories = RevenueCategory::query()
            ->where('paroisse_id', $paroisseId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return response()->json(['categories' => $categories]);
    }

    public function revenueTypesForCategory(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
            'revenue_category_id' => ['required', 'integer', 'exists:revenue_categories,id'],
        ]);
        $paroisseId = (int) $validated['paroisse_id'];
        $categoryId = (int) $validated['revenue_category_id'];

        if (! $user->hasRole('super_admin') && (int) $user->paroisse_id !== $paroisseId) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $categoryOk = RevenueCategory::query()
            ->whereKey($categoryId)
            ->where('paroisse_id', $paroisseId)
            ->exists();

        if (! $categoryOk) {
            return response()->json(['message' => 'Catégorie invalide pour cette paroisse.'], 422);
        }

        $types = RevenueType::query()
            ->where('paroisse_id', $paroisseId)
            ->where('revenue_category_id', $categoryId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return response()->json(['types' => $types]);
    }

    public function revenuesByCategoryCalculate(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'revenue_category_id' => ['nullable', 'exists:revenue_categories,id', 'required_with:revenue_type_id'],
                'revenue_type_id' => ['nullable', 'integer', 'exists:revenue_types,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                return response()->json(['message' => 'Vous ne pouvez consulter que les rapports de votre paroisse.'], 403);
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $categoryId = isset($validated['revenue_category_id']) ? (int) $validated['revenue_category_id'] : null;
            $requestedTypeId = isset($validated['revenue_type_id']) ? (int) $validated['revenue_type_id'] : null;
            $typeId = $this->resolveRevenueTypeIdForReport($requestedTypeId, (int) $validated['paroisse_id'], $categoryId);

            if ($requestedTypeId !== null && $typeId === null) {
                return response()->json([
                    'message' => 'Le type de recette est invalide ou ne correspond pas à la catégorie.',
                ], 422);
            }

            $report = $this->calculateRevenuesByCategoryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $categoryId,
                $typeId
            );

            $layout = $this->revenuesByCategoryReportLayout($typeId, $categoryId);

            $html = view('financial-reports.partials.revenues-by-category-report-body', [
                'report' => $report,
                'dateDebut' => $validated['date_debut'],
                'dateFin' => $validated['date_fin'],
                'selectedCategoryId' => $categoryId,
                'selectedTypeId' => $typeId,
                'selectedParoisseId' => (int) $validated['paroisse_id'],
                ...$layout,
            ])->render();

            $pdfUrl = route('financial-reports.revenues-by-category.pdf', array_filter([
                'paroisse_id' => (int) $validated['paroisse_id'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'revenue_category_id' => $categoryId,
                'revenue_type_id' => $typeId,
            ], fn ($v) => $v !== null && $v !== ''));

            return response()->json([
                'html' => $html,
                'pdf_url' => $pdfUrl,
                'period_label' => $dateDebut->format('d/m/Y').' → '.$dateFin->format('d/m/Y'),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur calcul AJAX rapport recettes par catégorie', ['data' => $request->all()]);

            return response()->json(['message' => 'Une erreur est survenue lors du calcul du rapport.'], 500);
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
                'revenue_category_id' => ['nullable', 'exists:revenue_categories,id', 'required_with:revenue_type_id'],
                'revenue_type_id' => ['nullable', 'integer', 'exists:revenue_types,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->route('financial-reports.revenues-by-category');
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $categoryId = isset($validated['revenue_category_id']) ? (int) $validated['revenue_category_id'] : null;
            $requestedTypeId = isset($validated['revenue_type_id']) ? (int) $validated['revenue_type_id'] : null;
            $typeId = $this->resolveRevenueTypeIdForReport($requestedTypeId, (int) $validated['paroisse_id'], $categoryId);

            if ($requestedTypeId !== null && $typeId === null) {
                FlashAlert::error('Le type de recette est invalide ou ne correspond pas à la catégorie (une catégorie est obligatoire pour filtrer par type).');

                return redirect()->back()->withInput();
            }

            $report = $this->calculateRevenuesByCategoryReport((int) $validated['paroisse_id'], $dateDebut, $dateFin, $categoryId, $typeId);

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
                    'revenue_type_id' => $typeId,
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
                'revenue_category_id' => ['nullable', 'exists:revenue_categories,id', 'required_with:revenue_type_id'],
                'revenue_type_id' => ['nullable', 'integer', 'exists:revenue_types,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $selectedCategoryId = isset($validated['revenue_category_id']) ? (int) $validated['revenue_category_id'] : null;
            $requestedTypeId = isset($validated['revenue_type_id']) ? (int) $validated['revenue_type_id'] : null;
            $selectedTypeId = $this->resolveRevenueTypeIdForReport($requestedTypeId, (int) $validated['paroisse_id'], $selectedCategoryId);

            if ($requestedTypeId !== null && $selectedTypeId === null) {
                FlashAlert::error('Le type de recette est invalide ou ne correspond pas à la catégorie (une catégorie est obligatoire pour filtrer par type).');

                return redirect()->back();
            }

            $report = $this->calculateRevenuesByCategoryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $selectedCategoryId,
                $selectedTypeId
            );

            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            $pdfCategoryNom = $selectedCategoryId
                ? RevenueCategory::query()->whereKey($selectedCategoryId)->value('nom')
                : null;
            $pdfTypeNom = $selectedTypeId
                ? RevenueType::query()->whereKey($selectedTypeId)->value('nom')
                : null;
            $layout = $this->revenuesByCategoryReportLayout($selectedTypeId, $selectedCategoryId);

            $pdf = Pdf::loadView('financial-reports.revenues-by-category-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'selectedCategoryId' => $selectedCategoryId,
                'selectedTypeId' => $selectedTypeId,
                'pdfCategoryNom' => $pdfCategoryNom,
                'pdfTypeNom' => $pdfTypeNom,
                ...$layout,
            ])->setPaper('a4', 'portrait');

            $filename = 'rapport-recettes-par-categorie-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'-'.$dateFin->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur génération PDF rapport recettes par catégorie', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->back();
        }
    }

    private function resolveRevenueTypeIdForReport(?int $typeId, int $paroisseId, ?int $categoryId): ?int
    {
        if ($typeId === null) {
            return null;
        }

        if ($categoryId === null) {
            return null;
        }

        $type = RevenueType::query()
            ->whereKey($typeId)
            ->where('paroisse_id', $paroisseId)
            ->first();

        if (! $type) {
            return null;
        }

        if ($categoryId !== null && (int) $type->revenue_category_id !== $categoryId) {
            return null;
        }

        return (int) $type->id;
    }

    /**
     * Affichage semaine / dimanche du rapport selon le code du type de recette filtré.
     *
     * @return array{showRptSemaine: bool, showRptDimanche: bool, rptTotalSubtitle: string, showWeeklyBreakdown: bool}
     */
    private function revenuesByCategoryReportLayout(?int $typeId, ?int $categoryId = null): array
    {
        $showWeeklyBreakdown = $this->shouldShowWeeklyRevenueBreakdown($categoryId, $typeId);

        if ($typeId === null) {
            return [
                'showRptSemaine' => true,
                'showRptDimanche' => true,
                'rptTotalSubtitle' => 'Semaine + dimanche',
                'showWeeklyBreakdown' => $showWeeklyBreakdown,
            ];
        }

        $code = RevenueType::query()->whereKey($typeId)->value('code');

        return array_merge(match ($code) {
            'messe_dimanche' => [
                'showRptSemaine' => false,
                'showRptDimanche' => true,
                'rptTotalSubtitle' => 'Messes du dimanche (période)',
            ],
            'messe_semaine' => [
                'showRptSemaine' => true,
                'showRptDimanche' => false,
                'rptTotalSubtitle' => 'Messes de semaine (période)',
            ],
            default => [
                'showRptSemaine' => true,
                'showRptDimanche' => true,
                'rptTotalSubtitle' => 'Semaine + dimanche',
            ],
        }, ['showWeeklyBreakdown' => $showWeeklyBreakdown]);
    }

    private function shouldShowWeeklyRevenueBreakdown(?int $categoryId, ?int $typeId): bool
    {
        if ($typeId !== null) {
            $code = RevenueType::query()->whereKey($typeId)->value('code');

            return in_array($code, ['messe_dimanche', 'messe_semaine'], true);
        }

        if ($categoryId !== null) {
            return RevenueCategory::query()->whereKey($categoryId)->value('code') === 'quete_ordinaire';
        }

        return false;
    }

    /**
     * @return array{
     *     revenues: Collection<int, Revenue>,
     *     by_category: array<int|string, array{nom: string, code: string, montant: float, count: int, revenues: Collection<int, Revenue>}>,
     *     by_type: array<string, array{id: int, nom: string, mois_subvention: string|null, mois_label: string|null, montant: float, count: int}>,
     *     subvention_envelopes: list<array{key: string, type_id: int, type_nom: string, mois_subvention: string, mois_label: string, label: string, montant: float, count: int}>,
     *     total_general: float,
     *     date_debut: Carbon,
     *     date_fin: Carbon,
     *     weekly: array<string, mixed>
     * }
     */
    private function calculateRevenuesByCategoryReport(int $paroisseId, Carbon $dateDebut, Carbon $dateFin, ?int $categoryId = null, ?int $typeId = null): array
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

        if ($typeId) {
            $query->where('revenue_type_id', $typeId);
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

        $byType = [];
        $subventionEnvelopeBuckets = [];

        foreach ($revenues as $revenue) {
            $type = $revenue->type;
            if (! $type) {
                continue;
            }

            if ($typeId && (int) $type->id !== (int) $typeId) {
                continue;
            }

            $moisSubvention = $revenue->mois_subvention;
            $isSubvention = SubventionMensuelle::isSubventionType($type);
            $groupKey = $isSubvention && $moisSubvention
                ? $type->id.'-'.$moisSubvention
                : (string) $type->id;
            $groupLabel = $isSubvention && $moisSubvention
                ? SubventionMensuelle::envelopeLabel($type, $moisSubvention)
                : $type->nom;

            if (! isset($byType[$groupKey])) {
                $byType[$groupKey] = [
                    'id' => (int) $type->id,
                    'nom' => $groupLabel,
                    'mois_subvention' => $moisSubvention,
                    'mois_label' => $moisSubvention ? SubventionMensuelle::formatMoisLabel($moisSubvention) : null,
                    'montant' => 0.0,
                    'count' => 0,
                ];
            }

            $montant = (float) $revenue->montant;
            $byType[$groupKey]['montant'] += $montant;
            $byType[$groupKey]['count']++;

            if ($isSubvention && $moisSubvention) {
                if (! isset($subventionEnvelopeBuckets[$groupKey])) {
                    $subventionEnvelopeBuckets[$groupKey] = [
                        'key' => $groupKey,
                        'type_id' => (int) $type->id,
                        'type_nom' => $type->nom,
                        'mois_subvention' => $moisSubvention,
                        'mois_label' => SubventionMensuelle::formatMoisLabel($moisSubvention),
                        'label' => $groupLabel,
                        'montant' => 0.0,
                        'count' => 0,
                    ];
                }

                $subventionEnvelopeBuckets[$groupKey]['montant'] += $montant;
                $subventionEnvelopeBuckets[$groupKey]['count']++;
            }
        }

        $subventionEnvelopes = array_values($subventionEnvelopeBuckets);
        usort($subventionEnvelopes, function (array $a, array $b): int {
            $cmp = strcmp($a['mois_subvention'], $b['mois_subvention']);

            return $cmp !== 0 ? $cmp : strcmp($a['type_nom'], $b['type_nom']);
        });

        $totalGeneral = (float) $revenues->sum('montant');
        $weekly = $this->splitRevenuesSemaineDimanche($revenues);
        $weekly['revenues_all'] = $revenues;

        return [
            'revenues' => $revenues,
            'by_category' => $byCategory,
            'by_type' => $byType,
            'subvention_envelopes' => $subventionEnvelopes,
            'total_general' => $totalGeneral,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'weekly' => $weekly,
        ];
    }

    /**
     * @return list<string>
     */
    /**
     * Rapport par catégories de dépenses — page initiale ; calcul via AJAX.
     */
    public function expensesByCategory(Request $request): View
    {
        try {
            $user = $request->user();

            $paroisses = $user->hasRole('super_admin')
                ? Paroisse::orderBy('nom')->get()
                : Paroisse::whereKey($user->paroisse_id)->get();

            $selectedParoisseId = $user->hasRole('super_admin')
                ? null
                : (int) $user->paroisse_id;

            $now = now();
            $dateDebut = $now->copy()->startOfMonth()->format('Y-m-d');
            $dateFin = $now->copy()->endOfMonth()->format('Y-m-d');

            // Récupérer les catégories de recettes (sources de fonds pour dépenses)
            $revenueCategories = RevenueCategory::where('actif', 1)
                ->orderBy('ordre')
                ->orderBy('nom')
                ->get();

            // Récupérer les types de recettes
            $revenueTypes = RevenueType::where('actif', 1)
                ->orderBy('ordre')
                ->orderBy('nom')
                ->get();

            return view('financial-reports.expenses-by-category', [
                'paroisses' => $paroisses,
                'selectedParoisseId' => $selectedParoisseId,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'revenueCategories' => $revenueCategories,
                'revenueTypes' => $revenueTypes,
                'ajaxCalculateRoute' => route('financial-reports.expenses-by-category.calculate'),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur rapport par catégories de dépenses');
            FlashAlert::error('Une erreur est survenue lors du chargement du rapport.');

            return view('financial-reports.expenses-by-category', [
                'paroisses' => collect(),
                'selectedParoisseId' => null,
                'dateDebut' => now()->startOfMonth()->format('Y-m-d'),
                'dateFin' => now()->endOfMonth()->format('Y-m-d'),
                'revenueCategories' => collect(),
                'revenueTypes' => collect(),
                'ajaxCalculateRoute' => route('financial-reports.expenses-by-category.calculate'),
            ]);
        }
    }

    public function expensesByCategoryCalculate(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'revenue_category_id' => ['nullable', 'integer', 'exists:revenue_categories,id'],
                'revenue_type_id' => ['nullable', 'integer', 'exists:revenue_types,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                return response()->json(['message' => 'Vous ne pouvez consulter que les rapports de votre paroisse.'], 403);
            }

            $revenueCategoryId = $validated['revenue_category_id'] ?? null;
            $revenueTypeId = $validated['revenue_type_id'] ?? null;

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();

            $report = $this->calculateExpensesByCategoryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $revenueCategoryId,
                $revenueTypeId
            );

            $html = view('financial-reports.partials.expenses-by-category-report-body', [
                'report' => $report,
                'dateDebut' => $validated['date_debut'],
                'dateFin' => $validated['date_fin'],
                'selectedRevenueCategoryId' => $revenueCategoryId,
                'selectedRevenueTypeId' => $revenueTypeId,
            ])->render();

            $pdfUrl = route('financial-reports.expenses-by-category.pdf', array_filter([
                'paroisse_id' => (int) $validated['paroisse_id'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'revenue_category_id' => $revenueCategoryId,
                'revenue_type_id' => $revenueTypeId,
            ]));

            return response()->json([
                'html' => $html,
                'pdf_url' => $pdfUrl,
                'period_label' => $dateDebut->format('d/m/Y').' - '.$dateFin->format('d/m/Y'),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur calcul rapport par catégories de dépenses', $request->all());

            return response()->json([
                'message' => 'Une erreur est survenue lors du calcul du rapport. '.$e->getMessage(),
            ], 500);
        }
    }

    public function downloadExpensesByCategoryPdf(Request $request): Response|RedirectResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'paroisse_id' => ['required', 'exists:paroisses,id'],
                'date_debut' => ['required', 'date'],
                'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
                'revenue_category_id' => ['nullable', 'integer', 'exists:revenue_categories,id'],
                'revenue_type_id' => ['nullable', 'integer', 'exists:revenue_types,id'],
            ]);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $revenueCategoryId = $validated['revenue_category_id'] ?? null;
            $revenueTypeId = $validated['revenue_type_id'] ?? null;

            $report = $this->calculateExpensesByCategoryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $revenueCategoryId,
                $revenueTypeId
            );

            $paroisse = Paroisse::find($validated['paroisse_id']);
            $headerConfig = $this->getHeaderConfig($validated['paroisse_id']);

            $pdf = Pdf::loadView('financial-reports.expenses-by-category-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $headerConfig,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'selectedRevenueCategoryId' => $revenueCategoryId,
                'selectedRevenueTypeId' => $revenueTypeId,
                'signataires' => FinancialReportSignatories::defaultPdfBlocks(),
            ])->setPaper('a4', 'portrait');

            $filename = 'rapport-depenses-par-categorie-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'-'.$dateFin->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur génération PDF rapport dépenses par catégorie', ['data' => $request->all()]);
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->back();
        }
    }

    /**
     * @return array{
     *     expenses: Collection<int, Expense>,
     *     by_category: array<string, array{id: int, nom: string, montant: float, count: int}>,
     *     by_type: array<string, array{id: int, nom: string, montant: float, count: int, mois_subvention: string|null, mois_label: string|null}>,
     *     subvention_envelopes: list<array{key: string, type_id: int, type_nom: string, mois_subvention: string|null, mois_label: string|null, label: string, subvention_recue: float|null, depenses: float, solde: float|null, count: int}>,
     *     total_general: float,
     *     date_debut: Carbon,
     *     date_fin: Carbon
     * }
     */
    private function calculateExpensesByCategoryReport(
        int $paroisseId,
        Carbon $dateDebut,
        Carbon $dateFin,
        ?int $revenueCategoryId = null,
        ?int $revenueTypeId = null
    ): array {
        $query = Expense::query()
            ->with(['revenueCategory', 'fundingSources.revenueType', 'fundingSources.revenue'])
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->whereDate('date_depense', '>=', $dateDebut)
            ->whereDate('date_depense', '<=', $dateFin);

        if ($revenueCategoryId) {
            $query->where('revenue_category_id', $revenueCategoryId);
        }

        if ($revenueTypeId) {
            $query->whereHas('fundingSources', function ($fundingQuery) use ($revenueTypeId): void {
                $fundingQuery->where('revenue_type_id', $revenueTypeId);
            });
        }

        $expenses = $query->orderBy('date_depense')->orderBy('id')->get();

        $byCategory = [];
        foreach ($expenses->groupBy('revenue_category_id') as $categoryId => $items) {
            $category = RevenueCategory::find($categoryId);
            if ($category) {
                $byCategory[$categoryId] = [
                    'id' => $categoryId,
                    'nom' => $category->nom,
                    'montant' => (float) $items->sum('montant'),
                    'count' => $items->count(),
                ];
            }
        }

        $byType = [];
        $subventionEnvelopeBuckets = [];

        foreach ($expenses as $expense) {
            foreach ($expense->fundingSources as $fundingSource) {
                $type = $fundingSource->revenueType;
                if (! $type) {
                    continue;
                }

                if ($revenueTypeId && (int) $type->id !== (int) $revenueTypeId) {
                    continue;
                }

                $moisSubvention = $fundingSource->revenue?->mois_subvention;
                $isSubvention = SubventionMensuelle::isSubventionType($type);
                $groupKey = $isSubvention && $moisSubvention
                    ? $type->id.'-'.$moisSubvention
                    : (string) $type->id;
                $groupLabel = $isSubvention && $moisSubvention
                    ? SubventionMensuelle::envelopeLabel($type, $moisSubvention)
                    : $type->nom;

                if (! isset($byType[$groupKey])) {
                    $byType[$groupKey] = [
                        'id' => (int) $type->id,
                        'nom' => $groupLabel,
                        'mois_subvention' => $moisSubvention,
                        'mois_label' => $moisSubvention ? SubventionMensuelle::formatMoisLabel($moisSubvention) : null,
                        'montant' => 0.0,
                        'count' => 0,
                    ];
                }

                $allocated = (float) $fundingSource->montant_alloue;
                $byType[$groupKey]['montant'] += $allocated;
                $byType[$groupKey]['count']++;

                if ($isSubvention && $moisSubvention) {
                    if (! isset($subventionEnvelopeBuckets[$groupKey])) {
                        $subventionEnvelopeBuckets[$groupKey] = [
                            'key' => $groupKey,
                            'type_id' => (int) $type->id,
                            'type_nom' => $type->nom,
                            'mois_subvention' => $moisSubvention,
                            'mois_label' => SubventionMensuelle::formatMoisLabel($moisSubvention),
                            'label' => $groupLabel,
                            'depenses' => 0.0,
                            'count' => 0,
                        ];
                    }

                    $subventionEnvelopeBuckets[$groupKey]['depenses'] += $allocated;
                    $subventionEnvelopeBuckets[$groupKey]['count']++;
                }
            }
        }

        $subventionEnvelopes = $this->finalizeSubventionEnvelopeSummary($paroisseId, $subventionEnvelopeBuckets);

        return [
            'expenses' => $expenses,
            'by_category' => $byCategory,
            'by_type' => $byType,
            'subvention_envelopes' => $subventionEnvelopes,
            'total_general' => (float) $expenses->sum('montant'),
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ];
    }

    /**
     * @param  array<string, array{key: string, type_id: int, type_nom: string, mois_subvention: string, mois_label: string, label: string, depenses: float, count: int}>  $buckets
     * @return list<array{key: string, type_id: int, type_nom: string, mois_subvention: string, mois_label: string, label: string, subvention_recue: float, depenses: float, solde: float, count: int}>
     */
    private function finalizeSubventionEnvelopeSummary(int $paroisseId, array $buckets): array
    {
        $summary = [];

        foreach ($buckets as $bucket) {
            $subventionRecue = (float) Revenue::query()
                ->where('paroisse_id', $paroisseId)
                ->where('revenue_type_id', $bucket['type_id'])
                ->where('mois_subvention', $bucket['mois_subvention'])
                ->where('statut', 'valide')
                ->sum('montant');
            $depenses = (float) $bucket['depenses'];

            $summary[] = [
                ...$bucket,
                'subvention_recue' => $subventionRecue,
                'solde' => $subventionRecue - $depenses,
            ];
        }

        usort($summary, function (array $a, array $b): int {
            $cmp = strcmp($a['mois_subvention'], $b['mois_subvention']);

            return $cmp !== 0 ? $cmp : strcmp($a['type_nom'], $b['type_nom']);
        });

        return $summary;
    }
}
