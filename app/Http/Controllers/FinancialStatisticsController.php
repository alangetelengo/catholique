<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Services\FinancialStatisticsService;
use App\Traits\LogsErrors;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Statistiques financières : KPI, graphiques, export PDF / Excel.
 * Solde = recettes − dépenses popote uniquement (voir FinancialStatisticsService).
 */
class FinancialStatisticsController extends Controller
{
    use LogsErrors;

    public function __construct(
        private readonly FinancialStatisticsService $statisticsService
    ) {}

    public function index(Request $request): View
    {
        try {
            $filters = $this->statisticsService->resolveFilters($request);
            $snapshot = $this->statisticsService->buildSnapshot($filters);
            $snapshot['chart']['pie_revenues'] = [
                'labels' => $snapshot['current']['revenue_by_category']->pluck('label')->values()->all(),
                'values' => $snapshot['current']['revenue_by_category']->pluck('total')->values()->all(),
            ];
            $snapshot['chart']['pie_expenses'] = [
                'labels' => $snapshot['current']['expense_by_category']->pluck('label')->values()->all(),
                'values' => $snapshot['current']['expense_by_category']->pluck('total')->values()->all(),
            ];
            $paroisses = $request->user()?->hasRole('super_admin')
                ? Paroisse::query()->orderBy('nom')->get()
                : collect();

            $this->logInfo('Consultation statistiques financières', [
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'paroisse_id' => $filters['paroisse_id'],
            ]);

            return view('financial-statistics.index', compact('snapshot', 'filters', 'paroisses'));
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur statistiques financières (index)');

            throw $e;
        }
    }

    public function exportPdf(Request $request): Response
    {
        try {
            $filters = $this->statisticsService->resolveFilters($request);
            $snapshot = $this->statisticsService->buildSnapshot($filters);

            $pdf = Pdf::loadView('financial-statistics.pdf', compact('snapshot', 'filters'))
                ->setPaper('a4', 'portrait');

            $filename = 'statistiques-financieres-'.now()->format('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur export PDF statistiques financières');

            throw $e;
        }
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        try {
            $filters = $this->statisticsService->resolveFilters($request);
            $snapshot = $this->statisticsService->buildSnapshot($filters);

            $filename = 'statistiques-financieres-'.now()->format('Y-m-d').'.xlsx';

            return response()->streamDownload(function () use ($snapshot, $filters): void {
                $writer = new Writer;
                $writer->openToFile('php://output');

                // --- Feuille Synthèse ---
                $writer->getCurrentSheet()->setName('Synthèse');
                $writer->addRow(Row::fromValues(['Statistiques financières']));
                $writer->addRow(Row::fromValues(['Période', $snapshot['period_label']]));
                $writer->addRow(Row::fromValues(['Paroisse', $snapshot['paroisse']?->nom ?? 'Toutes']));
                $writer->addRow(Row::fromValues([]));
                $writer->addRow(Row::fromValues(['Règle solde', 'Recettes − dépenses alimentation popote uniquement']));
                $writer->addRow(Row::fromValues([]));
                $c = $snapshot['current'];
                $writer->addRow(Row::fromValues(['Total recettes', $c['total_revenues']]));
                $writer->addRow(Row::fromValues(['Dépenses popote (déductibles)', $c['total_expenses_popote']]));
                $writer->addRow(Row::fromValues(['Solde', $c['solde']]));
                $writer->addRow(Row::fromValues(['Total toutes dépenses (info)', $c['total_expenses_all']]));
                $writer->addRow(Row::fromValues([]));
                $f = $snapshot['forecast'];
                $writer->addRow(Row::fromValues(['Jours dans la période', $f['days_in_period']]));
                $writer->addRow(Row::fromValues(['Moyenne journalière recettes', $f['daily_avg_revenue']]));
                $writer->addRow(Row::fromValues(['Projection 365 jours (indicatif)', $f['projected_annual_revenue']]));

                if ($filters['compare_previous_year'] && $snapshot['previous']) {
                    $writer->addRow(Row::fromValues([]));
                    $writer->addRow(Row::fromValues(['— Période N−1 (mêmes dates, année précédente) —']));
                    $p = $snapshot['previous'];
                    $writer->addRow(Row::fromValues(['Total recettes N−1', $p['total_revenues']]));
                    $writer->addRow(Row::fromValues(['Dépenses popote N−1', $p['total_expenses_popote']]));
                    $writer->addRow(Row::fromValues(['Solde N−1', $p['solde']]));
                }

                // --- Recettes par catégorie ---
                $sh = $writer->addNewSheetAndMakeItCurrent();
                $sh->setName('Recettes par cat.');
                $writer->addRow(Row::fromValues(['Catégorie', 'Montant']));
                foreach ($snapshot['current']['revenue_by_category'] as $row) {
                    $writer->addRow(Row::fromValues([$row['label'], $row['total']]));
                }

                // --- Dépenses par catégorie ---
                $sh2 = $writer->addNewSheetAndMakeItCurrent();
                $sh2->setName('Depenses par cat.');
                $writer->addRow(Row::fromValues(['Catégorie', 'Montant', 'Déductible solde']));
                foreach ($snapshot['current']['expense_by_category'] as $row) {
                    $writer->addRow(Row::fromValues([
                        $row['label'],
                        $row['total'],
                        $row['deductible'] ? 'oui' : 'non',
                    ]));
                }

                // --- Série mensuelle ---
                $sh3 = $writer->addNewSheetAndMakeItCurrent();
                $sh3->setName('Mensuel');
                $writer->addRow(Row::fromValues(['Mois', 'Recettes', 'Popote déductible', 'Autres dépenses (info)']));
                foreach (array_keys($snapshot['current']['monthly_revenues']) as $ym) {
                    $writer->addRow(Row::fromValues([
                        $ym,
                        $snapshot['current']['monthly_revenues'][$ym] ?? 0,
                        $snapshot['current']['monthly_popote'][$ym] ?? 0,
                        $snapshot['current']['monthly_other_expenses'][$ym] ?? 0,
                    ]));
                }

                $writer->close();
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur export Excel statistiques financières');

            throw $e;
        }
    }
}
