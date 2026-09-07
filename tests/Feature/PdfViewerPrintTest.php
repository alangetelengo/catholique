<?php

namespace Tests\Feature;

use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PdfViewerPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_saved_report_print_opens_pdf_viewer(): void
    {
        [$user, $paroisse] = $this->userWithViewPermission();

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
                'month' => 6,
                'year' => 2026,
                'revenues' => [],
            ],
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('financial-reports.print', $report));

        $response->assertOk();
        $response->assertSee('Aperçu PDF', false);
        $response->assertSee('Document PDF', false);
        $response->assertSee('Télécharger', false);
        $response->assertSee('data:application/pdf;base64,', false);
        $response->assertSee('<iframe', false);
    }

    public function test_revenue_report_print_opens_pdf_viewer(): void
    {
        [$user, $paroisse] = $this->userWithViewPermission();

        $report = FinancialReport::query()->create([
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'total',
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
            'total_recettes' => 50000,
            'total_depenses' => 0,
            'solde' => 50000,
            'details_recettes' => [
                'report_target' => 'global',
                'period_kind' => 'mensuel',
                'month' => 6,
                'year' => 2026,
                'revenues' => [],
            ],
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('revenue-reports.print', $report));

        $response->assertOk();
        $response->assertSee('Aperçu PDF', false);
        $response->assertSee('Rapport de recettes', false);
        $response->assertSee('Télécharger', false);
        $response->assertSee('<iframe', false);
    }

    public function test_revenues_by_category_pdf_route_opens_viewer(): void
    {
        [$user, $paroisse] = $this->userWithViewPermission();

        $response = $this->actingAs($user)->get(route('financial-reports.revenues-by-category.pdf', [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Aperçu PDF', false);
        $response->assertSee('Document PDF', false);
        $response->assertSee('Télécharger', false);
    }

    public function test_financial_statistics_pdf_opens_viewer(): void
    {
        [$user] = $this->userWithViewPermission();
        Permission::findOrCreate('view_financial_statistics');
        $user->givePermissionTo('view_financial_statistics');

        $response = $this->actingAs($user)->get(route('financial-statistics.pdf', [
            'date_from' => '2026-01-01',
            'date_to' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Aperçu PDF', false);
        $response->assertSee('Statistiques financières', false);
        $response->assertSee('Télécharger', false);
    }

    /**
     * @return array{0: User, 1: Paroisse}
     */
    private function userWithViewPermission(): array
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-PDF',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        Permission::findOrCreate('view_financial_reports');
        $user->givePermissionTo('view_financial_reports');

        return [$user, $paroisse];
    }
}
