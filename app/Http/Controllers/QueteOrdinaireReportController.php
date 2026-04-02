<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Anciennes URLs /reports/quete-ordinaire* : redirection 301 vers les routes financières canoniques.
 */
class QueteOrdinaireReportController extends Controller implements HasMiddleware
{
    /**
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_financial_reports', only: [
                'legacyRedirectIndex',
                'legacyRedirectPrint',
            ]),
            new Middleware('permission:generate_financial_reports', only: [
                'legacyRedirectPdf',
            ]),
        ];
    }

    public function legacyRedirectIndex(Request $request): RedirectResponse
    {
        return redirect()->route('financial-reports.revenues-weekly', $this->buildWeeklyQuery($request), 301);
    }

    public function legacyRedirectPrint(Request $request): RedirectResponse
    {
        return redirect()->route('financial-reports.revenues-weekly-print', $this->buildWeeklyQuery($request), 301);
    }

    public function legacyRedirectPdf(Request $request): RedirectResponse
    {
        return redirect()->route('financial-reports.revenues-weekly-pdf', $this->buildWeeklyQuery($request), 301);
    }

    /**
     * @return array<string, int|string>
     */
    private function buildWeeklyQuery(Request $request): array
    {
        if ($request->filled('period_type')) {
            $q = array_filter([
                'paroisse_id' => $request->input('paroisse_id'),
                'period_type' => $request->input('period_type'),
                'week_start' => $request->input('week_start'),
                'month' => $request->input('month'),
                'year' => $request->input('year'),
            ], static fn ($v) => $v !== null && $v !== '');

            return $q;
        }

        $periode = $request->input('periode', 'semaine');
        if (! in_array($periode, ['semaine', 'mois'], true)) {
            $periode = 'semaine';
        }
        $periodType = $periode === 'mois' ? 'month' : 'week';

        $out = array_filter([
            'paroisse_id' => $request->input('paroisse_id'),
            'period_type' => $periodType,
        ], static fn ($v) => $v !== null && $v !== '');

        if ($periodType === 'week') {
            $out['week_start'] = $request->input('week_start', now()->startOfWeek()->format('Y-m-d'));
        } else {
            $out['month'] = (int) $request->input('report_month', $request->input('month', now()->month));
            $out['year'] = (int) $request->input('report_year', $request->input('year', now()->year));
        }

        return $out;
    }
}
