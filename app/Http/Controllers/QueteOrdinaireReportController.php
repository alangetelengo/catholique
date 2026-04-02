<?php

namespace App\Http\Controllers;

use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Traits\LogsErrors;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

class QueteOrdinaireReportController extends Controller
{
    use LogsErrors;

    public function index(Request $request): View
    {
        $user = $request->user();
        $paroisses = $user?->hasRole('super_admin')
            ? Paroisse::query()->orderBy('nom')->get()
            : Paroisse::query()->whereKey($user?->paroisse_id)->get();

        $selectedParoisseId = (int) ($request->integer('paroisse_id') ?: $user?->paroisse_id ?: Paroisse::query()->value('id'));
        $periode = $request->input('periode', 'semaine');
        if (! in_array($periode, ['semaine', 'mois'], true)) {
            $periode = 'semaine';
        }

        if ($periode === 'mois') {
            $reportMonth = (int) $request->input('report_month', now()->month);
            $reportYear = (int) $request->input('report_year', now()->year);
            $reportMonth = max(1, min(12, $reportMonth));
            $reportYear = max(2000, min(2100, $reportYear));
            $dateDebut = Carbon::createFromDate($reportYear, $reportMonth, 1)->startOfDay();
            $dateFin = $dateDebut->copy()->endOfMonth();
            $weekStart = $dateDebut->copy()->startOfWeek()->format('Y-m-d');
        } else {
            $weekStart = $request->input('week_start', now()->startOfWeek()->format('Y-m-d'));
            $dateDebut = Carbon::parse($weekStart)->startOfWeek();
            $dateFin = $dateDebut->copy()->endOfWeek();
            $reportMonth = (int) $dateDebut->month;
            $reportYear = (int) $dateDebut->year;
        }

        $report = $this->calculate($selectedParoisseId, $dateDebut, $dateFin);

        return view('financial-reports.revenues-weekly', [
            'paroisses' => $paroisses,
            'selectedParoisseId' => $selectedParoisseId,
            'selectedPeriode' => $periode,
            'selectedWeekStart' => $weekStart,
            'selectedReportMonth' => $reportMonth,
            'selectedReportYear' => $reportYear,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'report' => $report,
        ]);
    }

    public function print(Request $request): View
    {
        $payload = $this->validatedPayload($request);
        [$dateDebut, $dateFin] = $this->datesFromPayload($payload);
        $report = $this->calculate($payload['paroisse_id'], $dateDebut, $dateFin);
        $paroisse = Paroisse::query()->find($payload['paroisse_id']);

        return view('financial-reports.revenues-weekly-print', compact('report', 'paroisse', 'dateDebut', 'dateFin'));
    }

    public function exportPdf(Request $request): Response
    {
        $payload = $this->validatedPayload($request);
        [$dateDebut, $dateFin] = $this->datesFromPayload($payload);
        $report = $this->calculate($payload['paroisse_id'], $dateDebut, $dateFin);
        $paroisse = Paroisse::query()->find($payload['paroisse_id']);

        // On utilise le conteneur dompdf.wrapper pour éviter la dépendance directe à une Facade.
        $pdf = app('dompdf.wrapper')->loadView('financial-reports.revenues-weekly-pdf', compact('report', 'paroisse', 'dateDebut', 'dateFin'))
            ->setPaper('a4', 'landscape');

        $suffix = $payload['periode'] === 'mois'
            ? $dateDebut->format('Y-m')
            : $dateDebut->format('Y-m-d');

        return $pdf->download('rapport-revenus-quete-ordinaire-' . $suffix . '.pdf');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: Carbon, 1: Carbon}
     */
    private function datesFromPayload(array $payload): array
    {
        if ($payload['periode'] === 'mois') {
            $dateDebut = Carbon::createFromDate((int) $payload['report_year'], (int) $payload['report_month'], 1)->startOfDay();

            return [$dateDebut, $dateDebut->copy()->endOfMonth()];
        }

        $dateDebut = Carbon::parse($payload['week_start'])->startOfWeek();

        return [$dateDebut, $dateDebut->copy()->endOfWeek()];
    }

    private function validatedPayload(Request $request): array
    {
        $periode = $request->input('periode', 'semaine');
        if (! in_array($periode, ['semaine', 'mois'], true)) {
            $periode = 'semaine';
        }

        $base = $request->validate([
            'paroisse_id' => ['required', 'integer', 'exists:paroisses,id'],
            'periode' => ['sometimes', 'in:semaine,mois'],
        ]);

        $base['periode'] = $periode;

        if ($periode === 'mois') {
            $extra = $request->validate([
                'report_month' => ['required', 'integer', 'between:1,12'],
                'report_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            ]);
        } else {
            $extra = $request->validate([
                'week_start' => ['required', 'date'],
            ]);
        }

        return array_merge($base, $extra);
    }

    private function calculate(int $paroisseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        $queteCategory = RevenueCategory::query()
            ->where('paroisse_id', $paroisseId)
            ->where('code', 'quete_ordinaire')
            ->first();

        $revenues = Revenue::query()
            ->with('type')
            ->where('paroisse_id', $paroisseId)
            ->where('statut', 'valide')
            ->when($queteCategory, fn ($q) => $q->where('revenue_category_id', $queteCategory->id))
            ->whereDate('date_recette', '>=', $dateDebut)
            ->whereDate('date_recette', '<=', $dateFin)
            ->orderBy('date_recette')
            ->orderBy('id')
            ->get();

        $revenuesSemaine = $revenues->filter(fn ($r) => $r->jour_semaine !== 'dimanche');
        $revenuesDimanche = $revenues->filter(fn ($r) => $r->jour_semaine === 'dimanche');

        $totalSemaine = (float) $revenuesSemaine->sum('montant');
        $totalDimanche = (float) $revenuesDimanche->sum('montant');
        $totalGeneral = $totalSemaine + $totalDimanche;

        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
        $short = ['lundi' => 'Lun', 'mardi' => 'Mar', 'mercredi' => 'Mer', 'jeudi' => 'Jeu', 'vendredi' => 'Ven', 'samedi' => 'Sam', 'dimanche' => 'Dim'];

        $detailsSemaine = [];
        foreach ($jours as $jour) {
            $items = $revenuesSemaine->where('jour_semaine', $jour);
            $detailsSemaine[] = [
                'jour' => $short[$jour],
                'montant' => (float) $items->sum('montant'),
                'nb' => $items->count(),
            ];
        }

        $detailsDimanche = [
            ['jour' => 'Dimanche', 'montant' => $totalDimanche, 'nb' => $revenuesDimanche->count()],
        ];

        return [
            'total_semaine' => $totalSemaine,
            'total_dimanche' => $totalDimanche,
            'total_general' => $totalGeneral,
            'details_semaine' => $detailsSemaine,
            'details_dimanche' => $detailsDimanche,
            'revenues_all' => $revenues,
            'jours_short' => $short,
        ];
    }
}

