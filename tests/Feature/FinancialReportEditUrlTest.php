<?php

namespace Tests\Feature;

use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportEditUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_revenue_report_with_report_target_links_to_revenue_reports_edit(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-EDIT-URL',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $report = FinancialReport::query()->create([
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'total',
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
            'total_recettes' => 100000,
            'total_depenses' => 0,
            'solde' => 100000,
            'details_recettes' => [
                'report_target' => 'global',
                'period_kind' => 'mensuel',
                'revenues' => [],
            ],
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $this->assertSame(
            route('revenue-reports.edit', $report),
            $report->editUrlForList()
        );
    }

    public function test_legacy_total_snapshot_without_report_target_has_no_edit_url(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Sainte Marie',
            'code_paroisse' => 'SM-EDIT-URL',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $report = FinancialReport::query()->create([
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'total',
            'date_debut' => '2026-01-01',
            'date_fin' => '2026-01-31',
            'total_recettes' => 50000,
            'total_depenses' => 20000,
            'solde' => 30000,
            'details_recettes' => [
                ['code' => 'offrandes', 'nom' => 'Offrandes', 'montant' => 50000],
            ],
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $this->assertNull($report->editUrlForList());
    }
}
