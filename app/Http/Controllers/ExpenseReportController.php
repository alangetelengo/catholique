<?php

namespace App\Http\Controllers;

use App\Helpers\FlashAlert;
use App\Helpers\ParoisseConfig;
use App\Models\Caisse;
use App\Models\ExpenseType;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\User;
use App\Services\ExpenseReportService;
use App\Support\FinancialReportSignatories;
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
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ExpenseReportController extends Controller implements HasMiddleware
{
    use LogsErrors;

    public function __construct(
        private readonly ExpenseReportService $expenseReportService
    ) {}

    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_financial_reports', only: [
                'index', 'calculate', 'capitalUsage', 'capitalUsagePrint', 'printPdf',
            ]),
            new Middleware('permission:generate_financial_reports', only: [
                'store', 'storePopote', 'downloadPdf',
            ]),
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $paroisses = $user->hasRole('super_admin')
            ? Paroisse::orderBy('nom')->get()
            : Paroisse::whereKey($user->paroisse_id)->get();

        $selectedParoisseId = $user->hasRole('super_admin')
            ? ($request->integer('paroisse_id') ?: null)
            : (int) $user->paroisse_id;

        $activeTab = $this->resolveActiveTab($request);

        $now = now();
        $dateDebut = $request->input('date_debut', $now->copy()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', $now->copy()->endOfMonth()->format('Y-m-d'));

        $caisses = $this->loadCaisses($user, $selectedParoisseId);
        $expenseTypes = ExpenseType::query()
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get();

        $summaryReport = null;
        $summaryPrintUrl = null;
        $capitalReport = null;
        $capitalPrintUrl = null;
        $selectedCaisseId = $request->integer('caisse_id') ?: null;
        $selectedExpenseTypeId = $request->integer('expense_type_id') ?: null;

        if ($selectedParoisseId) {
            $debut = Carbon::parse($dateDebut)->startOfDay();
            $fin = Carbon::parse($dateFin)->endOfDay();

            if ($activeTab === 'capital') {
                $capitalReport = $this->expenseReportService->calculateCapitalUsageReport(
                    (int) $selectedParoisseId,
                    $debut,
                    $fin
                );
                $capitalPrintUrl = route('financial-reports.capital-usage.print', [
                    'paroisse_id' => (int) $selectedParoisseId,
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                ]);
            } elseif ($request->boolean('calculated')) {
                $summaryReport = $this->expenseReportService->calculateSummaryReport(
                    (int) $selectedParoisseId,
                    $debut,
                    $fin,
                    $selectedCaisseId,
                    $selectedExpenseTypeId
                );
                $summaryPrintUrl = route('financial-reports.expenses.print', array_filter([
                    'paroisse_id' => (int) $selectedParoisseId,
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                    'caisse_id' => $selectedCaisseId,
                    'expense_type_id' => $selectedExpenseTypeId,
                ]));
            }
        }

        return view('financial-reports.expenses', [
            'paroisses' => $paroisses,
            'selectedParoisseId' => $selectedParoisseId,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'caisses' => $caisses,
            'expenseTypes' => $expenseTypes,
            'summaryReport' => $summaryReport,
            'summaryPrintUrl' => $summaryPrintUrl,
            'capitalReport' => $capitalReport,
            'capitalPrintUrl' => $capitalPrintUrl,
            'selectedCaisseId' => $selectedCaisseId,
            'selectedExpenseTypeId' => $selectedExpenseTypeId,
            'activeTab' => $activeTab,
        ]);
    }

    public function capitalUsage(Request $request): RedirectResponse
    {
        return redirect()->route('financial-reports.expenses', array_filter([
            'tab' => 'capital',
            'paroisse_id' => $request->integer('paroisse_id') ?: null,
            'date_debut' => $request->input('date_debut'),
            'date_fin' => $request->input('date_fin'),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function capitalUsagePrint(Request $request): View|RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatePeriodFilters($request);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez consulter que les rapports de votre paroisse.');

                return redirect()->route('financial-reports.expenses', ['tab' => 'capital']);
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $paroisseId = (int) $validated['paroisse_id'];

            $report = $this->expenseReportService->calculateCapitalUsageReport($paroisseId, $dateDebut, $dateFin);
            $paroisse = Paroisse::findOrFail($paroisseId);

            $pdf = Pdf::loadView('financial-reports.capital-usage-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $this->headerConfig($paroisseId),
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'signataires' => FinancialReportSignatories::defaultPdfBlocks(),
            ])
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true)
                ->setOption('isHtml5ParserEnabled', true);

            $periodeLabel = $dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y');

            return view('financial-reports.viewer-pdf', [
                'content' => $pdf->output(),
                'titre' => 'Capital reçu → dépenses',
                'sousTitre' => $periodeLabel,
                'downloadName' => 'capital-recu-depenses-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'.pdf',
                'retourUrl' => route('financial-reports.expenses', [
                    'tab' => 'capital',
                    'paroisse_id' => $paroisseId,
                    'date_debut' => $validated['date_debut'],
                    'date_fin' => $validated['date_fin'],
                ]),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur aperçu PDF capital', $request->all());
            FlashAlert::error('Une erreur est survenue lors de la génération de l\'aperçu PDF.');

            return redirect()->back();
        }
    }

    public function printPdf(Request $request): View|RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatePeriodFilters($request);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez consulter que les rapports de votre paroisse.');

                return redirect()->route('financial-reports.expenses');
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $caisseId = $validated['caisse_id'] ?? null;
            $expenseTypeId = $validated['expense_type_id'] ?? null;
            $paroisseId = (int) $validated['paroisse_id'];

            $report = $this->expenseReportService->calculateSummaryReport(
                $paroisseId,
                $dateDebut,
                $dateFin,
                $caisseId,
                $expenseTypeId
            );

            $paroisse = Paroisse::findOrFail($paroisseId);

            $pdf = Pdf::loadView('financial-reports.expenses-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $this->headerConfig($paroisseId),
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'selectedCaisseId' => $caisseId,
                'selectedExpenseTypeId' => $expenseTypeId,
                'signataires' => FinancialReportSignatories::defaultPdfBlocks(),
            ])
                ->setPaper('a4', 'portrait')
                ->setOption('isRemoteEnabled', true)
                ->setOption('isHtml5ParserEnabled', true);

            $periodeLabel = $dateDebut->format('d/m/Y').' au '.$dateFin->format('d/m/Y');

            return view('financial-reports.viewer-pdf', [
                'content' => $pdf->output(),
                'titre' => 'Rapport dépenses',
                'sousTitre' => $periodeLabel,
                'downloadName' => 'rapport-depenses-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'.pdf',
                'retourUrl' => route('financial-reports.expenses', array_filter([
                    'tab' => 'synthese',
                    'paroisse_id' => $paroisseId,
                    'date_debut' => $validated['date_debut'],
                    'date_fin' => $validated['date_fin'],
                    'calculated' => 1,
                    'caisse_id' => $caisseId,
                    'expense_type_id' => $expenseTypeId,
                ], fn ($value) => $value !== null && $value !== '')),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur aperçu PDF rapport dépenses', $request->all());
            FlashAlert::error('Une erreur est survenue lors de la génération de l\'aperçu PDF.');

            return redirect()->back();
        }
    }

    public function calculate(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatePeriodFilters($request);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                return response()->json(['message' => 'Vous ne pouvez consulter que les rapports de votre paroisse.'], 403);
            }

            $caisseId = $validated['caisse_id'] ?? null;
            $expenseTypeId = $validated['expense_type_id'] ?? null;
            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();

            $report = $this->expenseReportService->calculateSummaryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $caisseId,
                $expenseTypeId
            );

            $html = view('financial-reports.partials.expenses-report-summary', [
                'report' => $report,
                'dateDebut' => $validated['date_debut'],
                'dateFin' => $validated['date_fin'],
                'selectedCaisseId' => $caisseId,
                'selectedExpenseTypeId' => $expenseTypeId,
            ])->render();

            $detailHtml = view('financial-reports.partials.expenses-report-detail', [
                'report' => $report,
            ])->render();

            $pdfUrl = route('financial-reports.expenses.print', array_filter([
                'paroisse_id' => (int) $validated['paroisse_id'],
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'caisse_id' => $caisseId,
                'expense_type_id' => $expenseTypeId,
            ]));

            return response()->json([
                'html' => $html,
                'detail_html' => $detailHtml,
                'pdf_url' => $pdfUrl,
                'period_label' => $dateDebut->format('d/m/Y').' - '.$dateFin->format('d/m/Y'),
                'total_general' => $report['total_general'],
                'expense_count' => $report['expenses']->count(),
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur calcul rapport dépenses', $request->all());

            return response()->json([
                'message' => 'Une erreur est survenue lors du calcul du rapport.',
            ], 500);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatePeriodFilters($request);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez enregistrer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $caisseId = $validated['caisse_id'] ?? null;
            $expenseTypeId = $validated['expense_type_id'] ?? null;

            $report = $this->expenseReportService->calculateSummaryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $caisseId,
                $expenseTypeId
            );

            $detailsDepenses = [
                'caisse_id' => $caisseId,
                'expense_type_id' => $expenseTypeId,
                'caisse_summary' => $report['caisse_summary'],
                'by_expense_type' => $report['by_expense_type'],
                'expenses' => $report['expenses']->map(fn ($expense) => [
                    'id' => $expense->id,
                    'date' => $expense->date_depense?->format('Y-m-d'),
                    'libelle' => $expense->libelle,
                    'montant' => (float) $expense->montant,
                    'expense_type' => $expense->expenseType?->nom,
                ])->values()->all(),
            ];

            $financialReport = FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => 'depenses',
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'total_recettes' => 0,
                'total_depenses' => $report['total_general'],
                'solde' => -$report['total_general'],
                'details_recettes' => null,
                'details_depenses' => $detailsDepenses,
                'created_by' => $user->id,
            ]);

            FlashAlert::success('Rapport dépenses enregistré avec succès.');

            return redirect()->route('financial-reports.show', $financialReport);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur enregistrement rapport dépenses', $request->all());
            FlashAlert::error('Une erreur est survenue lors de l\'enregistrement du rapport.');

            return redirect()->back()->withInput();
        }
    }

    public function storePopote(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatePeriodFilters($request);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez enregistrer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $data = $this->expenseReportService->calculatePopoteReport((int) $validated['paroisse_id'], $dateDebut, $dateFin);

            $financialReport = FinancialReport::create([
                'paroisse_id' => $validated['paroisse_id'],
                'periode_type' => 'popote_subvention',
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'total_recettes' => $data['subvention_recue'],
                'total_depenses' => $data['total_depenses_alimentation'],
                'solde' => $data['solde'],
                'details_recettes' => [
                    'period_kind' => 'custom',
                    'monthly_summary' => $data['monthly_summary'],
                    'revenues' => $data['credits']->map(fn ($m) => [
                        'id' => $m->id,
                        'date' => $m->date_mouvement?->format('Y-m-d'),
                        'montant' => (float) $m->montant,
                        'libelle' => $m->libelle,
                    ])->values()->all(),
                ],
                'details_depenses' => [
                    'expenses' => $data['depenses']->map(fn ($e) => [
                        'id' => $e->id,
                        'date' => $e->date_depense?->format('Y-m-d'),
                        'libelle' => $e->libelle,
                        'montant' => (float) $e->fundingSources->sum('montant_alloue'),
                    ])->values()->all(),
                ],
                'created_by' => $user->id,
            ]);

            FlashAlert::success('Rapport Popote enregistré avec succès.');

            return redirect()->route('popote-reports.show', $financialReport);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur enregistrement rapport Popote', $request->all());
            FlashAlert::error('Une erreur est survenue lors de l\'enregistrement du rapport Popote.');

            return redirect()->back()->withInput();
        }
    }

    public function downloadPdf(Request $request): Response|RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $this->validatePeriodFilters($request);

            if (! $user->hasRole('super_admin') && (int) $validated['paroisse_id'] !== (int) $user->paroisse_id) {
                FlashAlert::error('Vous ne pouvez générer des rapports que pour votre paroisse.');

                return redirect()->back();
            }

            $dateDebut = Carbon::parse($validated['date_debut'])->startOfDay();
            $dateFin = Carbon::parse($validated['date_fin'])->endOfDay();
            $caisseId = $validated['caisse_id'] ?? null;
            $expenseTypeId = $validated['expense_type_id'] ?? null;

            $report = $this->expenseReportService->calculateSummaryReport(
                (int) $validated['paroisse_id'],
                $dateDebut,
                $dateFin,
                $caisseId,
                $expenseTypeId
            );

            $paroisse = Paroisse::find($validated['paroisse_id']);

            $pdf = Pdf::loadView('financial-reports.expenses-pdf', [
                'report' => $report,
                'paroisse' => $paroisse,
                'headerConfig' => $this->headerConfig((int) $validated['paroisse_id']),
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
                'selectedCaisseId' => $caisseId,
                'selectedExpenseTypeId' => $expenseTypeId,
                'signataires' => FinancialReportSignatories::defaultPdfBlocks(),
            ])->setPaper('a4', 'portrait');

            $filename = 'rapport-depenses-'.Str::slug($paroisse->nom).'-'.$dateDebut->format('Y-m-d').'-'.$dateFin->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur génération PDF rapport dépenses', $request->all());
            FlashAlert::error('Une erreur est survenue lors de la génération du PDF.');

            return redirect()->back();
        }
    }

    private function resolveActiveTab(Request $request): string
    {
        $tab = $request->input('tab', 'synthese');

        return in_array($tab, ['synthese', 'detail', 'capital'], true) ? $tab : 'synthese';
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePeriodFilters(Request $request): array
    {
        return $request->validate([
            'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'caisse_id' => ['nullable', 'integer', 'exists:caisses,id'],
            'expense_type_id' => ['nullable', 'integer', 'exists:expense_types,id'],
        ]);
    }

    /**
     * @return Collection<int, Caisse>
     */
    private function loadCaisses(User $user, ?int $selectedParoisseId)
    {
        $caissesQuery = Caisse::query()
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('nom');

        if ($selectedParoisseId) {
            $caissesQuery->where('paroisse_id', $selectedParoisseId);
        } elseif ($user->hasRole('super_admin')) {
            $caissesQuery->with('paroisse:id,nom');
        } else {
            $caissesQuery->whereRaw('1 = 0');
        }

        return $caissesQuery->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function headerConfig(int $paroisseId): array
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
}
