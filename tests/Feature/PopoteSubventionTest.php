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

    public function test_duplicate_monthly_subvention_revenue_is_rejected(): void
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

        $response->assertSessionHasErrors('mois_subvention');
        $this->assertSame(1, Revenue::query()->where('revenue_type_id', $carburantType->id)->where('mois_subvention', '2026-06')->count());
    }
}
