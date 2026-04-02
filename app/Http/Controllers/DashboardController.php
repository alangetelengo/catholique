<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Services\DashboardService;
use App\Traits\LogsErrors;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Tableau de bord : indicateurs financiers, graphique, raccourcis et dernières écritures.
 */
class DashboardController extends Controller
{
    use LogsErrors;

    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        try {
            $period = $this->dashboardService->resolvePeriod($request);
            $data = $this->dashboardService->build($request, $period);

            $paroisses = $request->user()?->hasRole('super_admin')
                ? Paroisse::query()->orderBy('nom')->get()
                : collect();

            $this->logInfo('Consultation tableau de bord', [
                'preset' => $period['preset'],
                'date_from' => $period['date_from'],
                'date_to' => $period['date_to'],
                'paroisse_id' => $period['paroisse_id'],
            ]);

            return view('dashboard.index', [
                'period' => $period,
                'data' => $data,
                'paroisses' => $paroisses,
            ]);
        } catch (Throwable $e) {
            $this->logError($e, 'Erreur tableau de bord');

            throw $e;
        }
    }
}
