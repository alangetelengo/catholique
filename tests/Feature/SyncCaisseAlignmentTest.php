<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\CaisseMouvement;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use App\Services\CaisseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncCaisseAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_banque_revenue_credits_tresorerie(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-SYNC-BANQUE',
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

        $this->actingAs($user);

        $response = $this->postJson(route('api.sync'), [
            'revenues' => [[
                'action' => 'create',
                'data' => [
                    'revenue_category_id' => $banque->id,
                    'revenue_type_id' => $type->id,
                    'date_recette' => '2026-08-10',
                    'mois_capital' => '08',
                    'montant' => 250000,
                    'methode_paiement' => 'virement',
                ],
            ]],
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $tresorerie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', Caisse::CODE_TRESORERIE)->firstOrFail();
        $this->assertSame(250000.0, app(CaisseService::class)->getSolde($tresorerie));
        $this->assertDatabaseHas('caisse_mouvements', [
            'caisse_id' => $tresorerie->id,
            'type' => CaisseMouvement::TYPE_CREDIT_RECETTE,
            'montant' => 250000,
        ]);
    }

    public function test_sync_expense_debits_caisse_with_funding_sources(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-SYNC-EXP',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);
        $liturgie = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'liturgie')->firstOrFail();
        $service = app(CaisseService::class);
        $service->creditDirect($liturgie, 80000, '2026-08-01', 'Crédit sync', null, $user->id);

        $expenseType = ExpenseType::query()->where('code', 'liturgie')->first()
            ?? ExpenseType::query()->where('code', 'autre')->firstOrFail();

        $this->actingAs($user);

        $response = $this->postJson(route('api.sync'), [
            'expenses' => [[
                'action' => 'create',
                'data' => [
                    'expense_type_id' => $expenseType->id,
                    'date_depense' => '2026-08-12',
                    'mois_capital' => '08',
                    'annee_capital' => 2026,
                    'montant' => 25000,
                    'libelle' => 'Dépense sync',
                    'methode_paiement' => 'especes',
                    'funding_sources' => [
                        ['caisse_id' => $liturgie->id, 'montant_alloue' => 25000],
                    ],
                ],
            ]],
        ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSame(55000.0, $service->getSolde($liturgie->fresh()));
        $this->assertDatabaseHas('caisse_mouvements', [
            'caisse_id' => $liturgie->id,
            'type' => CaisseMouvement::TYPE_DEPENSE,
            'montant' => 25000,
            'mois_capital' => '08',
            'annee_capital' => 2026,
        ]);
    }
}
