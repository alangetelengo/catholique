<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use App\Services\CaisseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CaisseBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_banque_revenue_credits_tresorerie_only(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CAISSE',
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

        $response = $this->post(route('revenues.store'), [
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'date_recette' => '2026-08-26',
            'mois_capital' => '08',
            'montant' => 500000,
            'methode_paiement' => 'virement',
        ]);

        $response->assertRedirect(route('revenues.index'));

        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $service = app(CaisseService::class);

        $this->assertSame(500000.0, $service->getSolde($tresorerie));
        $this->assertDatabaseHas('caisse_mouvements', [
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
            'montant' => 500000,
        ]);
    }

    public function test_credit_direct_and_multi_caisse_expense(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CAISSE2',
        ]);

        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $liturgie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'liturgie')->firstOrFail();
        $transport = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'transport')->firstOrFail();
        $expenseType = ExpenseType::query()->where('code', 'liturgie')->first()
            ?? ExpenseType::query()->where('code', 'autre')->firstOrFail();

        $service = app(CaisseService::class);
        $service->creditDirect($liturgie, 80000, '2026-08-01', 'Hosties diocèse', null, $user->id);
        $service->creditDirect($transport, 50000, '2026-08-01', 'Carburant diocèse', null, $user->id);

        $this->actingAs($user);

        $response = $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-10',
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'montant' => 90000,
            'libelle' => 'Achats mixtes',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $liturgie->id, 'montant_alloue' => 60000],
                ['caisse_id' => $transport->id, 'montant_alloue' => 30000],
            ],
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertSame(20000.0, $service->getSolde($liturgie->fresh()));
        $this->assertSame(20000.0, $service->getSolde($transport->fresh()));
    }

    public function test_cannot_spend_from_tresorerie_directly(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CAISSE3',
        ]);

        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'banque',
            'nom' => 'BANQUE',
            'actif' => true,
            'ordre' => 0,
        ]);
        $type = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'revenu_principal',
            'nom' => 'REVENU PRINCIPAL',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $type->id,
            'montant' => 100000,
            'date_recette' => '2026-08-01',
            'mois_capital' => '08',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        app(CaisseService::class)->syncCreditFromBanqueRevenue($revenue);

        $expenseType = ExpenseType::query()->where('code', 'autre')->firstOrFail();
        $this->actingAs($user);

        $response = $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-10',
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'montant' => 10000,
            'libelle' => 'Interdit',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $tresorerie->id, 'montant_alloue' => 10000],
            ],
        ]);

        $response->assertSessionHasErrors('funding_sources');
    }

    public function test_virement_from_tresorerie_to_caisse(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CAISSE4',
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

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'montant' => 200000,
            'date_recette' => '2026-08-01',
            'mois_capital' => '08',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $service = app(CaisseService::class);
        $service->syncCreditFromBanqueRevenue($revenue);

        $charges = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'charges')->firstOrFail();
        $this->actingAs($user);

        $response = $this->post(route('caisses.virement.store'), [
            'mode' => 'tresorerie',
            'caisse_id' => $charges->id,
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'montant' => 75000,
            'date_mouvement' => '2026-08-05',
            'libelle' => 'Alim charges',
        ]);

        $response->assertRedirect(route('caisses.show', $charges));
        $this->assertSame(75000.0, $service->getSolde($charges->fresh()));
        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $this->assertSame(125000.0, $service->getSolde($tresorerie));
    }

    public function test_expense_create_page_uses_caisses_copy(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CREATE-UI',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $this->actingAs($user);

        $response = $this->get(route('expenses.create'));
        $response->assertOk();
        $response->assertSee('Caisses de financement', false);
        $response->assertDontSee('Catégorie (reporting)', false);
        $response->assertDontSee('Toutes sources', false);
    }

    public function test_expenses_index_can_filter_by_caisse(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-FILTER-CAISSE',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $liturgie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'liturgie')->firstOrFail();
        $transport = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'transport')->firstOrFail();
        $service = app(CaisseService::class);
        $service->creditDirect($liturgie, 50000, '2026-08-01', 'Crédit liturgie', null, $user->id);
        $service->creditDirect($transport, 50000, '2026-08-01', 'Crédit transport', null, $user->id);

        $expenseType = ExpenseType::query()->where('code', 'liturgie')->firstOrFail();
        $this->actingAs($user);

        $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-10',
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'montant' => 10000,
            'libelle' => 'Dépense liturgie filtrable',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $liturgie->id, 'montant_alloue' => 10000],
            ],
        ])->assertRedirect(route('expenses.index'));

        $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => ExpenseType::query()->where('code', 'transport')->firstOrFail()->id,
            'date_depense' => '2026-08-11',
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'montant' => 15000,
            'libelle' => 'Dépense transport filtrable',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $transport->id, 'montant_alloue' => 15000],
            ],
        ])->assertRedirect(route('expenses.index'));

        $filtered = $this->get(route('expenses.index', ['caisse_id' => $liturgie->id]));
        $filtered->assertOk();
        $filtered->assertSee('Dépense liturgie filtrable', false);
        $filtered->assertDontSee('Dépense transport filtrable', false);
        $filtered->assertSee('Toutes caisses', false);
        $filtered->assertDontSee('Toutes sources', false);
    }

    public function test_align_command_reclassifies_subvention_popote_as_banque_capital(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-ALIGN-SUB',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $subvention = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'subvention',
            'nom' => 'Subvention',
            'actif' => true,
            'ordre' => 4,
        ]);
        $popoteType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $subvention->id,
            'code' => 'subvention_popote',
            'nom' => 'Subvention Popote',
            'actif' => true,
            'ordre' => 9,
        ]);

        $banque = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'banque',
            'nom' => 'BANQUE',
            'actif' => true,
            'ordre' => 0,
        ]);
        RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'code' => 'revenu_principal',
            'nom' => 'Revenu principal',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $subvention->id,
            'revenue_type_id' => $popoteType->id,
            'montant' => 500000,
            'date_recette' => '2026-08-16',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $this->artisan('paroisse:align-legacy-finances-to-caisses')
            ->assertSuccessful();

        $revenue->refresh();
        $this->assertSame((int) $banque->id, (int) $revenue->revenue_category_id);
        $this->assertSame('08', $revenue->mois_capital);

        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service = app(CaisseService::class);

        $this->assertSame(0.0, $service->getSolde($tresorerie));
        $this->assertSame(500000.0, $service->getSolde($popote));
        $this->assertDatabaseHas('caisse_mouvements', [
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
            'revenue_id' => $revenue->id,
            'montant' => 500000,
        ]);
        $this->assertDatabaseHas('caisse_mouvements', [
            'caisse_id' => $popote->id,
            'type' => CaisseMouvement::TYPE_VIREMENT,
            'sens' => CaisseMouvement::SENS_CREDIT,
            'montant' => 500000,
        ]);
        $this->assertDatabaseMissing('caisse_mouvements', [
            'type' => CaisseMouvement::TYPE_CREDIT_DIRECT,
            'libelle' => 'Crédit legacy#'.$revenue->id.' — Subvention Popote (08/2026)',
        ]);
    }

    public function test_capital_usage_report_page_shows_banque_flow(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CAPITAL-RPT',
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
            'nom' => 'Revenu principal',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'montant' => 200000,
            'date_recette' => '2026-08-10',
            'mois_capital' => '08',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $service = app(CaisseService::class);
        $service->syncCreditFromBanqueRevenue($revenue);
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service->virementTresorerieVersCaisse($popote, 80000, '2026-08-12', 'Alim popote', '08', 2026, null, $user->id);

        Permission::findOrCreate('view_financial_reports');
        $user->givePermissionTo('view_financial_reports');

        $this->actingAs($user);
        $response = $this->get(route('financial-reports.capital-usage', [
            'paroisse_id' => $paroisse->id,
            'date_debut' => '2026-08-01',
            'date_fin' => '2026-08-31',
        ]));

        $response->assertOk();
        $response->assertSee('Capital reçu → dépenses', false);
        $response->assertSee('Capital Banque reçu', false);
        $response->assertSee('Caisse alimentation', false);
    }

    public function test_can_update_and_delete_virement_when_no_expense(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-EDIT-VIR',
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
            'nom' => 'Revenu principal',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'montant' => 500000,
            'date_recette' => '2026-08-01',
            'mois_capital' => '08',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $service = app(CaisseService::class);
        $service->syncCreditFromBanqueRevenue($revenue);
        $salaires = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'salaires')->firstOrFail();
        $pair = $service->virementTresorerieVersCaisse($salaires, 150000, '2026-08-05', 'Alim salaires', '08', 2026, null, $user->id);
        $credit = $pair['credit'];

        $this->actingAs($user);

        $this->put(route('caisses.mouvements.update', $credit), [
            'montant' => 100000,
            'date_mouvement' => '2026-08-05',
            'libelle' => 'Alim salaires corrigée',
            'mois_capital' => '08',
            'annee_capital' => 2026,
        ])->assertRedirect(route('caisses.show', $salaires));

        $this->assertSame(100000.0, $service->getSolde($salaires->fresh()));
        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $this->assertSame(400000.0, $service->getSolde($tresorerie));

        $credit->refresh();
        $this->delete(route('caisses.mouvements.destroy', $credit))
            ->assertRedirect(route('caisses.show', $salaires));

        $this->assertSame(0.0, $service->getSolde($salaires->fresh()));
        $this->assertSame(500000.0, $service->getSolde($tresorerie->fresh()));
    }

    public function test_cannot_delete_virement_when_expense_exists(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-BLOCK-VIR',
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
            'nom' => 'Revenu principal',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'montant' => 200000,
            'date_recette' => '2026-08-01',
            'mois_capital' => '08',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $service = app(CaisseService::class);
        $service->syncCreditFromBanqueRevenue($revenue);
        $salaires = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'salaires')->firstOrFail();
        $pair = $service->virementTresorerieVersCaisse($salaires, 100000, '2026-08-05', 'Alim salaires', '08', 2026, null, $user->id);

        $expenseType = ExpenseType::query()->where('code', 'salaires')->first()
            ?? ExpenseType::query()->where('code', 'autre')->firstOrFail();

        $this->actingAs($user);
        $this->post(route('expenses.store'), [
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-10',
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'montant' => 20000,
            'libelle' => 'Acompte salaire',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $salaires->id, 'montant_alloue' => 20000],
            ],
        ])->assertRedirect(route('expenses.index'));

        $this->delete(route('caisses.mouvements.destroy', $pair['credit']))
            ->assertSessionHasErrors('mouvement');

        $this->assertSame(80000.0, $service->getSolde($salaires->fresh()));
    }

    public function test_cannot_move_credit_direct_to_envelope_with_expenses(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-MOVE-CREDIT',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $liturgie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'liturgie')->firstOrFail();
        $service = app(CaisseService::class);

        $creditAout = $service->creditDirect($liturgie, 50000, '2026-08-05', 'Crédit août', null, $user->id);
        $service->creditDirect($liturgie, 30000, '2026-09-05', 'Crédit septembre', null, $user->id);

        $expenseType = ExpenseType::query()->where('code', 'liturgie')->first()
            ?? ExpenseType::query()->where('code', 'autre')->firstOrFail();

        $this->actingAs($user);
        $this->post(route('expenses.store'), [
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-09-10',
            'mois_capital' => '09',
            'annee_capital' => 2026,
            'montant' => 10000,
            'libelle' => 'Dépense septembre',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                ['caisse_id' => $liturgie->id, 'montant_alloue' => 10000],
            ],
        ])->assertRedirect(route('expenses.index'));

        $this->put(route('caisses.mouvements.update', $creditAout), [
            'montant' => 50000,
            'date_mouvement' => '2026-09-15',
            'libelle' => 'Crédit déplacé en septembre',
        ])->assertSessionHasErrors('montant');

        $creditAout->refresh();
        $this->assertSame('08', $creditAout->mois_capital);
        $this->assertSame(2026, (int) $creditAout->annee_capital);
    }

    public function test_monthly_envelopes_are_independent(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-ENVELOPES',
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
            'nom' => 'Revenu principal',
            'actif' => true,
            'ordre' => 1,
        ]);

        $service = app(CaisseService::class);

        foreach (['07' => 1103000, '08' => 500000] as $mois => $montant) {
            $revenue = Revenue::query()->create([
                'paroisse_id' => $paroisse->id,
                'revenue_category_id' => $banque->id,
                'revenue_type_id' => $type->id,
                'montant' => $montant,
                'date_recette' => "2026-{$mois}-15",
                'mois_capital' => $mois,
                'statut' => 'valide',
                'methode_paiement' => 'virement',
                'created_by' => $user->id,
            ]);
            $service->syncCreditFromBanqueRevenue($revenue);
        }

        $envelopes = $service->getEnvelopesCapital((int) $paroisse->id);

        $this->assertCount(2, $envelopes);
        $juillet = $envelopes->first(fn (array $row): bool => $row['mois_capital'] === '07');
        $aout = $envelopes->first(fn (array $row): bool => $row['mois_capital'] === '08');

        $this->assertNotNull($juillet);
        $this->assertNotNull($aout);
        $this->assertSame(1103000.0, $juillet['disponible']);
        $this->assertSame(500000.0, $aout['disponible']);

        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();
        $service->virementTresorerieVersCaisse($popote, 200000, '2026-08-16', 'Alim août', '08', 2026);

        $envelopesAfter = $service->getEnvelopesCapital((int) $paroisse->id);
        $juilletAfter = $envelopesAfter->first(fn (array $row): bool => $row['mois_capital'] === '07');
        $aoutAfter = $envelopesAfter->first(fn (array $row): bool => $row['mois_capital'] === '08');

        $this->assertSame(1103000.0, $juilletAfter['disponible']);
        $this->assertSame(300000.0, $aoutAfter['disponible']);
    }

    public function test_legacy_virement_without_envelope_is_counted_in_alloue(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-LEGACY-VIR',
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
            'nom' => 'Revenu principal',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenue = Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $banque->id,
            'revenue_type_id' => $type->id,
            'montant' => 500000,
            'date_recette' => '2026-08-01',
            'mois_capital' => '08',
            'statut' => 'valide',
            'methode_paiement' => 'virement',
            'created_by' => $user->id,
        ]);

        $service = app(CaisseService::class);
        $service->syncCreditFromBanqueRevenue($revenue);

        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $popote = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'alimentation_popote')->firstOrFail();

        CaisseMouvement::query()->create([
            'paroisse_id' => $paroisse->id,
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_VIREMENT,
            'sens' => CaisseMouvement::SENS_DEBIT,
            'montant' => 120000,
            'date_mouvement' => '2026-08-10',
            'libelle' => 'Virement legacy sans enveloppe',
            'contrepartie_caisse_id' => $popote->id,
        ]);

        $envelope = $service->getEnvelopesCapital((int) $paroisse->id)
            ->first(fn (array $row): bool => $row['mois_capital'] === '08');

        $this->assertNotNull($envelope);
        $this->assertSame(120000.0, $envelope['alloue']);
        $this->assertSame(380000.0, $envelope['disponible']);
    }

    public function test_envelopes_depense_includes_month_with_credit_direct_only(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-CREDIT-ENV',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $liturgie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'liturgie')->firstOrFail();
        $service = app(CaisseService::class);
        $service->creditDirect($liturgie, 45000, '2026-09-05', 'Don diocèse', null, $user->id);

        $envelopes = $service->getEnvelopesDepense((int) $paroisse->id);

        $septembre = $envelopes->first(fn (array $row): bool => $row['mois_capital'] === '09' && $row['annee_capital'] === 2026);

        $this->assertNotNull($septembre);
        $this->assertTrue($septembre['has_caisse_solde']);
        $this->assertSame(0.0, $septembre['disponible']);
    }
}
