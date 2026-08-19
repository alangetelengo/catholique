<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use App\Services\BudgetService;
use App\Support\SubventionMensuelle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopoteSubventionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{paroisse: Paroisse, user: User, category: RevenueCategory, popoteType: RevenueType, carburantType: RevenueType}
     */
    private function createSubventionContext(): array
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-TEST',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => SubventionMensuelle::CATEGORY_CODE,
            'nom' => 'Subvention',
            'actif' => true,
            'ordre' => 1,
        ]);

        $popoteType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => SubventionMensuelle::POPOTE_TYPE_CODE,
            'nom' => 'Subvention Popote',
            'actif' => true,
            'ordre' => 1,
        ]);

        $carburantType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'subvention_carburant',
            'nom' => 'Subvention Carburant',
            'actif' => true,
            'ordre' => 2,
        ]);

        return compact('paroisse', 'user', 'category', 'popoteType', 'carburantType');
    }

    public function test_monthly_popote_envelope_tracks_balance_per_month(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'popoteType' => $popoteType] = $this->createSubventionContext();

        $juin = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-06',
            'montant' => 700000,
            'date_recette' => '2026-06-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $mai = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-05',
            'montant' => 650000,
            'date_recette' => '2026-06-10',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $service = app(BudgetService::class);

        $this->assertSame(700000.0, $service->getSoldeDisponibleForRevenue($juin));
        $this->assertSame(650000.0, $service->getSoldeDisponibleForRevenue($mai));

        $expense = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'montant' => 200000,
            'date_depense' => '2026-06-12',
            'jour_semaine' => 'vendredi',
            'libelle' => 'Courses popote juin',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expense->fundingSources()->create([
            'revenue_type_id' => $popoteType->id,
            'revenue_id' => $juin->id,
            'montant_alloue' => 200000,
            'ordre' => 1,
        ]);

        $this->assertSame(500000.0, $service->getSoldeDisponibleForRevenue($juin->fresh()));
        $this->assertSame(650000.0, $service->getSoldeDisponibleForRevenue($mai->fresh()));
    }

    public function test_carburant_subvention_requires_monthly_envelope_for_expense(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'carburantType' => $carburantType] = $this->createSubventionContext();

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $carburantType->id,
            'mois_subvention' => '2026-06',
            'montant' => 300000,
            'date_recette' => '2026-06-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $service = app(BudgetService::class);

        $validation = $service->validateFundingSources([
            [
                'revenue_type_id' => $carburantType->id,
                'montant_alloue' => 50000,
            ],
        ]);

        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('Subvention Carburant', $validation['errors'][0]);
    }

    public function test_subvention_funding_requires_monthly_envelope(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'popoteType' => $popoteType] = $this->createSubventionContext();

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-06',
            'montant' => 700000,
            'date_recette' => '2026-06-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $service = app(BudgetService::class);

        $validation = $service->validateFundingSources([
            [
                'revenue_type_id' => $popoteType->id,
                'montant_alloue' => 100000,
            ],
        ]);

        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('Subvention Popote', $validation['errors'][0]);
    }

    public function test_multiple_monthly_subvention_revenues_are_allowed(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'carburantType' => $carburantType] = $this->createSubventionContext();

        $this->actingAs($user);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $carburantType->id,
            'mois_subvention' => '2026-06',
            'montant' => 300000,
            'date_recette' => '2026-06-05',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $response = $this->post(route('revenues.store'), [
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $carburantType->id,
            'date_recette' => '2026-06-15',
            'mois_subvention' => '2026-06',
            'montant' => 300000,
            'methode_paiement' => 'virement',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, Revenue::query()->where('revenue_type_id', $carburantType->id)->where('mois_subvention', '2026-06')->count());
    }

    public function test_same_month_popote_receipts_are_grouped_into_one_expense_envelope(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'popoteType' => $popoteType] = $this->createSubventionContext();

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-08',
            'montant' => 500000,
            'date_recette' => '2026-08-01',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-08',
            'montant' => 500000,
            'date_recette' => '2026-08-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $service = app(BudgetService::class);
        $envelopes = $service->getSubventionEnvelopesForExpenseForm($paroisse->id);

        $this->assertCount(1, $envelopes);
        $this->assertSame('2026-08', $envelopes->first()->mois_subvention);
        $this->assertSame(1000000.0, $envelopes->first()->solde_disponible);
        $this->assertSame(1000000.0, $envelopes->first()->envelope_montant_recu);
    }

    public function test_exhausted_subvention_envelope_is_hidden_from_expense_form(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'popoteType' => $popoteType] = $this->createSubventionContext();

        $fevrier = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-02',
            'montant' => 394450,
            'date_recette' => '2026-02-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-03',
            'montant' => 394450,
            'date_recette' => '2026-03-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expense = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'montant' => 394450,
            'date_depense' => '2026-02-15',
            'jour_semaine' => 'vendredi',
            'libelle' => 'Salaires février',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expense->fundingSources()->create([
            'revenue_type_id' => $popoteType->id,
            'revenue_id' => $fevrier->id,
            'montant_alloue' => 394450,
            'ordre' => 1,
        ]);

        $service = app(BudgetService::class);
        $envelopes = $service->getSubventionEnvelopesForExpenseForm($paroisse->id);

        $this->assertFalse($envelopes->contains('id', $fevrier->id));
        $this->assertTrue($envelopes->contains('mois_subvention', '2026-03'));
        $this->assertSame(394450.0, $envelopes->firstWhere('mois_subvention', '2026-03')->solde_disponible);
    }

    public function test_orphan_subvention_allocation_reduces_oldest_envelope_balance_fifo(): void
    {
        ['paroisse' => $paroisse, 'user' => $user, 'category' => $category, 'popoteType' => $popoteType] = $this->createSubventionContext();

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-02',
            'montant' => 394450,
            'date_recette' => '2026-02-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $popoteType->id,
            'mois_subvention' => '2026-03',
            'montant' => 394450,
            'date_recette' => '2026-03-05',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expense = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'montant' => 394450,
            'date_depense' => '2026-02-15',
            'jour_semaine' => 'vendredi',
            'libelle' => 'Ancienne dépense sans mois',
            'statut' => 'valide',
            'created_by' => $user->id,
        ]);

        $expense->fundingSources()->create([
            'revenue_type_id' => $popoteType->id,
            'revenue_id' => null,
            'montant_alloue' => 394450,
            'ordre' => 1,
        ]);

        $service = app(BudgetService::class);
        $envelopes = $service->getSubventionEnvelopesForExpenseForm($paroisse->id);

        $this->assertFalse($envelopes->contains('mois_subvention', '2026-02'));
        $this->assertTrue($envelopes->contains('mois_subvention', '2026-03'));
    }
}
