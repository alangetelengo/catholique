<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use App\Services\CaisseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopoteSubventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_direct_popote_caisse_tracks_balance(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-POPOTE',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service = app(CaisseService::class);

        $service->creditDirect($popote, 700000, '2026-06-05', 'Crédit popote juin', null, $user->id);
        $this->assertSame(700000.0, $service->getSolde($popote));

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);
        $expenseType = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();

        $this->actingAs($user);
        $response = $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-06-12',
            'montant' => 200000,
            'libelle' => 'Courses popote juin',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $popote->id, 'montant_alloue' => 200000],
            ],
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertSame(500000.0, $service->getSolde($popote->fresh()));
    }

    public function test_duplicate_caisse_lines_are_aggregated_against_solde(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-AGG',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service = app(CaisseService::class);
        $service->creditDirect($popote, 100000, '2026-06-01', 'Crédit', null, $user->id);

        $validation = $service->validateFundingSources([
            ['caisse_id' => $popote->id, 'montant_alloue' => 70000],
            ['caisse_id' => $popote->id, 'montant_alloue' => 50000],
        ], null, $paroisse->id);

        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('100 000', $validation['errors'][0]);
    }

    public function test_foreign_paroisse_caisse_is_rejected(): void
    {
        $paroisseA = Paroisse::query()->create(['nom' => 'A', 'code_paroisse' => 'PA']);
        $paroisseB = Paroisse::query()->create(['nom' => 'B', 'code_paroisse' => 'PB']);
        $user = User::factory()->create(['paroisse_id' => $paroisseA->id]);

        $caisseB = Caisse::query()->where('paroisse_id', $paroisseB->id)->where('code', 'liturgie')->firstOrFail();
        app(CaisseService::class)->creditDirect($caisseB, 50000, '2026-06-01', 'Crédit B', null, $user->id);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisseA->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);
        $expenseType = ExpenseType::query()->where('code', 'autre')->firstOrFail();

        $this->actingAs($user);
        $response = $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-06-12',
            'montant' => 10000,
            'libelle' => 'Dépense illicite',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $caisseB->id, 'montant_alloue' => 10000],
            ],
        ]);

        $response->assertSessionHasErrors('funding_sources');
    }

    public function test_subvention_category_is_rejected_on_revenue_store(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-SUB',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'subvention',
            'nom' => 'Subvention',
            'actif' => false,
            'ordre' => 4,
        ]);
        $type = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'subvention_carburant',
            'nom' => 'Subvention Carburant',
            'actif' => false,
            'ordre' => 1,
        ]);

        $this->actingAs($user);
        $response = $this->post(route('revenues.store'), [
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $type->id,
            'date_recette' => '2026-06-15',
            'montant' => 300000,
            'methode_paiement' => 'virement',
        ]);

        $response->assertSessionHasErrors('revenue_category_id');
    }

    public function test_multiple_credit_directs_on_same_caisse_are_allowed(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-MULTI',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $transport = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'transport')->firstOrFail();
        $service = app(CaisseService::class);

        $this->actingAs($user);
        $this->post(route('caisses.credit.store'), [
            'caisse_id' => $transport->id,
            'montant' => 300000,
            'date_mouvement' => '2026-06-05',
            'libelle' => 'Crédit 1',
        ])->assertRedirect();

        $this->post(route('caisses.credit.store'), [
            'caisse_id' => $transport->id,
            'montant' => 300000,
            'date_mouvement' => '2026-06-15',
            'libelle' => 'Crédit 2',
        ])->assertRedirect();

        $this->assertSame(600000.0, $service->getSolde($transport->fresh()));
    }

    public function test_backfill_creates_missing_banque_treasury_credits(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-BACKFILL',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $banque = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'banque',
            'nom' => 'BANQUE',
            'actif' => true,
            'ordre' => 0,
        ]);
        $type = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'code' => 'revenu_principal',
            'nom' => 'REVENU PRINCIPAL',
            'actif' => true,
            'ordre' => 1,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'montant' => 150000,
            'date_recette' => '2026-05-01',
            'mois_capital' => '05',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $service = app(CaisseService::class);
        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $this->assertSame(0.0, $service->getSolde($tresorerie));

        $created = $service->backfillBanqueCredits($paroisse->id);
        $this->assertSame(1, $created);
        $this->assertSame(150000.0, $service->getSolde($tresorerie->fresh()));
        $this->assertSame(0, $service->backfillBanqueCredits($paroisse->id));
    }

    public function test_banque_revenue_requires_mois_capital_and_allows_multiple_same_month(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-CAPITAL',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $banque = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'banque',
            'nom' => 'BANQUE',
            'actif' => true,
            'ordre' => 0,
        ]);
        $type = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'code' => 'revenu_principal',
            'nom' => 'REVENU PRINCIPAL',
            'actif' => true,
            'ordre' => 1,
        ]);

        $this->actingAs($user);

        $this->post(route('revenues.store'), [
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'date_recette' => '2026-08-10',
            'montant' => 500000,
            'methode_paiement' => 'virement',
        ])->assertSessionHasErrors('mois_capital');

        $this->post(route('revenues.store'), [
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'date_recette' => '2026-08-10',
            'mois_capital' => '08',
            'montant' => 500000,
            'methode_paiement' => 'virement',
        ])->assertRedirect(route('revenues.index'));

        $this->post(route('revenues.store'), [
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'date_recette' => '2026-08-20',
            'mois_capital' => '08',
            'montant' => 250000,
            'methode_paiement' => 'virement',
        ])->assertRedirect(route('revenues.index'));

        $this->assertSame(2, Revenue::query()
            ->where('revenue_category_id', $banque->id)
            ->where('mois_capital', '08')
            ->count());

        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $this->assertSame(750000.0, app(CaisseService::class)->getSolde($tresorerie));
    }
}
