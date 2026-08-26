<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use App\Services\CaisseService;
use App\Services\FinancialStatisticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialStatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_totals_and_daily_chart_apply_validated_and_popote_allocation_rules(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Pierre',
            'code_paroisse' => 'SP-001',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire_sp001',
            'nom' => 'Quête ordinaire',
            'actif' => true,
            'ordre' => 1,
        ]);

        $queteType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'quete_type_sp001',
            'nom' => 'Quête type',
            'actif' => true,
            'ordre' => 1,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $queteType->id,
            'montant' => 10000,
            'date_recette' => '2026-05-10',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $queteType->id,
            'montant' => 5000,
            'date_recette' => '2026-05-10',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $queteType->id,
            'montant' => 9000,
            'date_recette' => '2026-05-10',
            'statut' => 'en_attente',
            'created_by' => $user->id,
        ]);

        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $transport = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'transport')->firstOrFail();
        $serviceCaisse = app(CaisseService::class);
        $serviceCaisse->creditDirect($popote, 100000, '2026-05-01', 'Crédit popote', null, $user->id);
        $serviceCaisse->creditDirect($transport, 100000, '2026-05-01', 'Crédit transport', null, $user->id);

        $expenseType = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();

        $expenseValidated = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'montant' => 7000,
            'date_depense' => '2026-05-10',
            'jour_semaine' => 'samedi',
            'libelle' => 'Courses popote',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expenseValidated->fundingSources()->createMany([
            [
                'caisse_id' => $popote->id,
                'montant_alloue' => 4000,
                'ordre' => 1,
            ],
            [
                'caisse_id' => $transport->id,
                'montant_alloue' => 3000,
                'ordre' => 2,
            ],
        ]);

        $expenseOtherValidated = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'montant' => 2000,
            'date_depense' => '2026-05-10',
            'jour_semaine' => 'samedi',
            'libelle' => 'Achat matériel',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expenseOtherValidated->fundingSources()->create([
            'caisse_id' => $transport->id,
            'montant_alloue' => 2000,
            'ordre' => 1,
        ]);

        $expensePending = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'montant' => 6000,
            'date_depense' => '2026-05-10',
            'jour_semaine' => 'samedi',
            'libelle' => 'Dépense en attente',
            'statut' => 'en_attente',
            'created_by' => $user->id,
        ]);

        $expensePending->fundingSources()->create([
            'caisse_id' => $popote->id,
            'montant_alloue' => 6000,
            'ordre' => 1,
        ]);

        $service = app(FinancialStatisticsService::class);
        $from = Carbon::parse('2026-05-10')->startOfDay();
        $to = Carbon::parse('2026-05-10')->endOfDay();

        $totals = $service->quickFinancialTotals($from, $to, $paroisse->id);

        $this->assertSame(15000.0, $totals['total_revenues']);
        $this->assertSame(9000.0, $totals['total_expenses_all']);
        $this->assertSame(4000.0, $totals['total_expenses_popote']);
        $this->assertSame(11000.0, $totals['solde']);

        $chart = $service->chartSeriesForDashboard($from, $to, $paroisse->id);
        $this->assertSame('day', $chart['granularity']);
        $this->assertSame(['2026-05-10'], $chart['labels']);
        $this->assertSame([15000.0], $chart['revenues']);
        $this->assertSame([4000.0], $chart['popote']);
    }
}
