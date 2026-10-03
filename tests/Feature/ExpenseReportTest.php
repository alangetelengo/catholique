<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\FinancialReport;
use App\Models\Paroisse;
use App\Models\User;
use App\Services\CaisseService;
use App\Services\ExpenseReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ExpenseReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_report_page_is_accessible(): void
    {
        [$user] = $this->userWithFinancialReportPermissions();

        $response = $this->actingAs($user)->get(route('financial-reports.expenses'));

        $response->assertOk();
        $response->assertSee('Rapport dépenses', false);
        $response->assertSee('Synthèse', false);
        $response->assertSee('Capital', false);
        $response->assertDontSee('Capital reçu → dépenses', false);
    }

    public function test_expense_report_calculate_returns_summary_json(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $expenseType = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();

        app(CaisseService::class)->creditDirect($popote, 500000, '2026-06-01', 'Crédit popote', null, $user->id);

        Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-06-15',
            'montant' => 150000,
            'libelle' => 'Courses popote',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ])->fundingSources()->create([
            'caisse_id' => $popote->id,
            'montant_alloue' => 150000,
        ]);

        $response = $this->actingAs($user)->postJson(route('financial-reports.expenses.calculate'), [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['html', 'detail_html', 'pdf_url', 'period_label', 'total_general']);
        $response->assertJsonPath('total_general', 150000);
        $this->assertStringContainsString('financial-reports/expenses/print', $response->json('pdf_url'));
    }

    public function test_expense_report_can_be_stored(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        Permission::findOrCreate('generate_financial_reports');
        $user->givePermissionTo('generate_financial_reports');

        $response = $this->actingAs($user)->post(route('financial-reports.expenses.store'), [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_reports', [
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'depenses',
        ]);
    }

    public function test_hub_route_redirects_to_history(): void
    {
        [$user] = $this->userWithFinancialReportPermissions();

        $response = $this->actingAs($user)->get('/financial-reports');

        $response->assertRedirect('/financial-reports/list');
    }

    public function test_legacy_expenses_by_category_redirects_to_unified_report(): void
    {
        [$user] = $this->userWithFinancialReportPermissions();

        $this->actingAs($user)
            ->get('/financial-reports/expenses-by-category')
            ->assertRedirect('/financial-reports/expenses');
    }

    public function test_capital_usage_route_redirects_to_unified_expenses_tab(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();

        $this->actingAs($user)
            ->get(route('financial-reports.capital-usage', [
                'date_debut' => '2026-06-01',
                'date_fin' => '2026-06-30',
            ]))
            ->assertRedirect(route('financial-reports.expenses', [
                'tab' => 'capital',
                'date_debut' => '2026-06-01',
                'date_fin' => '2026-06-30',
            ]));
    }

    public function test_capital_tab_shows_report_for_paroisse(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();

        $response = $this->actingAs($user)->get(route('financial-reports.expenses', [
            'tab' => 'capital',
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Capital Banque reçu', false);
        $response->assertDontSee('Sélectionnez une paroisse et une période', false);
    }

    public function test_capital_usage_print_shows_pdf_viewer(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();

        $response = $this->actingAs($user)->get(route('financial-reports.capital-usage.print', [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Aperçu PDF', false);
        $response->assertSee('Capital reçu → dépenses', false);
        $response->assertSee('Document PDF', false);
    }

    public function test_expense_report_print_shows_pdf_viewer(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();

        $response = $this->actingAs($user)->get(route('financial-reports.expenses.print', [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertSee('Aperçu PDF', false);
        $response->assertSee('Rapport dépenses', false);
    }

    public function test_popote_report_can_be_stored(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        Permission::findOrCreate('generate_financial_reports');
        $user->givePermissionTo('generate_financial_reports');

        $response = $this->actingAs($user)->post(route('financial-reports.expenses.store-popote'), [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_reports', [
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'popote_subvention',
        ]);
    }

    public function test_expense_report_pdf_downloads(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        Permission::findOrCreate('generate_financial_reports');
        $user->givePermissionTo('generate_financial_reports');

        $response = $this->actingAs($user)->get(route('financial-reports.expenses.pdf', [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_popote_monthly_summary_respects_selected_date_range(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service = app(CaisseService::class);

        $service->creditDirect($popote, 100000, '2026-01-05', 'Janvier hors plage', null, $user->id);
        $service->creditDirect($popote, 150000, '2026-06-10', 'Juin', null, $user->id);
        $service->creditDirect($popote, 200000, '2026-07-10', 'Juillet', null, $user->id);

        $report = app(ExpenseReportService::class)->calculatePopoteReport(
            $paroisse->id,
            Carbon::parse('2026-06-01')->startOfDay(),
            Carbon::parse('2026-07-31')->endOfDay()
        );

        $months = collect($report['monthly_summary'])->pluck('mois_subvention')->all();

        $this->assertSame(['2026-06', '2026-07'], $months);
        $this->assertSame(350000.0, $report['subvention_recue']);
    }

    public function test_popote_monthly_summary_includes_months_across_years(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service = app(CaisseService::class);

        $service->creditDirect($popote, 80000, '2026-12-15', 'Décembre', null, $user->id);
        $service->creditDirect($popote, 90000, '2027-01-10', 'Janvier', null, $user->id);

        $report = app(ExpenseReportService::class)->calculatePopoteReport(
            $paroisse->id,
            Carbon::parse('2026-12-01')->startOfDay(),
            Carbon::parse('2027-01-31')->endOfDay()
        );

        $months = collect($report['monthly_summary'])->pluck('mois_subvention')->all();

        $this->assertSame(['2026-12', '2027-01'], $months);
    }

    public function test_saved_depenses_report_show_redirects_to_unified_expenses_with_calculated(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();

        $report = FinancialReport::query()->create([
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'depenses',
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
            'total_recettes' => 0,
            'total_depenses' => 50000,
            'solde' => -50000,
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('financial-reports.show', $report))
            ->assertRedirect(route('financial-reports.expenses', [
                'tab' => 'synthese',
                'paroisse_id' => $paroisse->id,
                'date_debut' => '2026-06-01',
                'date_fin' => '2026-06-30',
                'calculated' => 1,
            ]));

        $this->actingAs($user)
            ->get(route('financial-reports.expenses', [
                'tab' => 'synthese',
                'paroisse_id' => $paroisse->id,
                'date_debut' => '2026-06-01',
                'date_fin' => '2026-06-30',
                'calculated' => 1,
            ]))
            ->assertOk()
            ->assertDontSee('Paramétrez le rapport', false);
    }

    public function test_saved_depenses_report_pdf_uses_expenses_template(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        Permission::findOrCreate('generate_financial_reports');
        $user->givePermissionTo('generate_financial_reports');

        $report = FinancialReport::query()->create([
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'depenses',
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
            'total_recettes' => 0,
            'total_depenses' => 0,
            'solde' => 0,
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('financial-reports.download-pdf', $report));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_summary_keeps_real_caisse_balance_when_filtered_by_expense_type(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $liturgie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'liturgie')->firstOrFail();
        $typePopote = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();
        $typeCarburant = ExpenseType::query()->where('code', 'carburant')->firstOrFail();

        $caisseService = app(CaisseService::class);
        $caisseService->creditDirect($popote, 700000, '2026-08-01', 'Crédit popote', null, $user->id);
        $caisseService->creditDirect($liturgie, 50000, '2026-08-01', 'Crédit liturgie', null, $user->id);

        $createExpense = function (ExpenseType $type, Caisse $caisse, float $montant) use ($paroisse, $user): void {
            Expense::query()->create([
                'paroisse_id' => $paroisse->id,
                'expense_type_id' => $type->id,
                'date_depense' => '2026-08-15',
                'montant' => $montant,
                'libelle' => 'Dépense '.$type->code,
                'statut' => 'valide',
                'methode_paiement' => 'especes',
                'created_by' => $user->id,
            ])->fundingSources()->create([
                'caisse_id' => $caisse->id,
                'montant_alloue' => $montant,
            ]);
        };

        $createExpense($typePopote, $popote, 100000);
        $createExpense($typeCarburant, $popote, 30000);
        $createExpense($typePopote, $liturgie, 7000);

        $service = app(ExpenseReportService::class);
        $debut = Carbon::parse('2026-08-01')->startOfDay();
        $fin = Carbon::parse('2026-08-31')->endOfDay();

        $global = $service->calculateSummaryReport($paroisse->id, $debut, $fin);
        $this->assertSame(107000.0, $global['by_expense_type'][$typePopote->id]['montant']);
        $popoteGlobal = collect($global['caisse_summary'])->firstWhere('caisse_id', $popote->id);
        $this->assertSame(130000.0, $popoteGlobal['depenses']);
        $this->assertSame(570000.0, $popoteGlobal['solde']);

        $parCaisse = $service->calculateSummaryReport($paroisse->id, $debut, $fin, $popote->id);
        $this->assertSame(100000.0, $parCaisse['by_expense_type'][$typePopote->id]['montant']);
        $this->assertSame(30000.0, $parCaisse['by_expense_type'][$typeCarburant->id]['montant']);

        $filtre = $service->calculateSummaryReport($paroisse->id, $debut, $fin, $popote->id, $typePopote->id);
        $popoteFiltre = collect($filtre['caisse_summary'])->firstWhere('caisse_id', $popote->id);
        $this->assertTrue($filtre['is_type_filtered']);
        $this->assertSame(100000.0, $filtre['total_general']);
        $this->assertSame(100000.0, $popoteFiltre['depenses']);
        $this->assertSame(130000.0, $popoteFiltre['depenses_caisse']);
        $this->assertSame(570000.0, $popoteFiltre['solde']);

        $response = $this->actingAs($user)->postJson(route('financial-reports.expenses.calculate'), [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-08-01',
            'date_fin' => '2026-08-31',
            'caisse_id' => $popote->id,
            'expense_type_id' => $typePopote->id,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('Solde réel caisse', $response->json('html'));
        $this->assertStringContainsString('Dépensé (tous types)', $response->json('html'));
    }

    public function test_legacy_total_report_show_lists_expenses_by_caisse(): void
    {
        [$user, $paroisse] = $this->userWithFinancialReportPermissions();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $expenseType = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();

        app(CaisseService::class)->creditDirect($popote, 500000, '2026-06-01', 'Crédit popote', null, $user->id);

        Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-06-15',
            'montant' => 123000,
            'libelle' => 'Courses popote',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ])->fundingSources()->create([
            'caisse_id' => $popote->id,
            'montant_alloue' => 123000,
        ]);

        $report = FinancialReport::query()->create([
            'paroisse_id' => $paroisse->id,
            'periode_type' => 'total',
            'date_debut' => '2026-06-01',
            'date_fin' => '2026-06-30',
            'total_recettes' => 0,
            'total_depenses' => 123000,
            'solde' => -123000,
            'details_recettes' => [],
            'details_depenses' => [],
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('financial-reports.show', $report))
            ->assertOk()
            ->assertSee($popote->nom, false)
            ->assertDontSee('Aucune dépense enregistrée', false);
    }

    /**
     * @return array{0: User, 1: Paroisse}
     */
    private function userWithFinancialReportPermissions(): array
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-REPORT',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        Permission::findOrCreate('view_financial_reports');
        $user->givePermissionTo('view_financial_reports');

        return [$user, $paroisse];
    }
}
