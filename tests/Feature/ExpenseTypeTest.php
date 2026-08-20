<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\Revenue;
use App\Models\RevenueCategory;
use App\Models\RevenueType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_types_are_seeded(): void
    {
        $this->assertDatabaseHas('expense_types', ['code' => 'alimentation_popote']);
        $this->assertDatabaseHas('expense_types', ['code' => 'salaires']);
        $this->assertDatabaseHas('expense_types', ['code' => 'autre']);
        $this->assertSame(6, ExpenseType::query()->count());
    }

    public function test_expense_requires_expense_type(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-TYPE',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $type = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'messe_semaine',
            'nom' => 'Messe Semaine',
            'actif' => true,
            'ordre' => 1,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $type->id,
            'montant' => 100000,
            'date_recette' => '2026-08-01',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'date_depense' => '2026-08-10',
            'montant' => 10000,
            'libelle' => 'Achat test',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                [
                    'revenue_type_id' => $type->id,
                    'montant_alloue' => 10000,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('expense_type_id');
    }

    public function test_expense_can_be_created_with_expense_type(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-TYPE2',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $type = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'messe_semaine',
            'nom' => 'Messe Semaine',
            'actif' => true,
            'ordre' => 1,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $type->id,
            'montant' => 100000,
            'date_recette' => '2026-08-01',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $expenseType = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();

        $this->actingAs($user);

        $response = $this->post(route('expenses.store'), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-10',
            'montant' => 10000,
            'libelle' => 'Achat riz popote',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                [
                    'revenue_type_id' => $type->id,
                    'montant_alloue' => 10000,
                ],
            ],
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'libelle' => 'Achat riz popote',
            'expense_type_id' => $expenseType->id,
        ]);
    }

    public function test_expense_edit_keeps_inactive_expense_type(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-TYPE3',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenueType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'messe_semaine',
            'nom' => 'Messe Semaine',
            'actif' => true,
            'ordre' => 1,
        ]);

        Revenue::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'revenue_type_id' => $revenueType->id,
            'montant' => 100000,
            'date_recette' => '2026-08-01',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $expenseType = ExpenseType::query()->where('code', 'salaires')->firstOrFail();
        $expenseType->update(['actif' => false]);

        $expense = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'montant' => 5000,
            'date_depense' => '2026-08-05',
            'libelle' => 'Salaire août',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $expense->fundingSources()->create([
            'revenue_type_id' => $revenueType->id,
            'montant_alloue' => 5000,
            'ordre' => 1,
        ]);

        $this->actingAs($user);

        $editResponse = $this->get(route('expenses.edit', $expense));
        $editResponse->assertOk();
        $editResponse->assertSee('Salaires', false);
        $editResponse->assertSee('(inactif)', false);

        $updateResponse = $this->put(route('expenses.update', $expense), [
            'revenue_category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-05',
            'montant' => 5000,
            'libelle' => 'Salaire août (corrigé)',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                [
                    'revenue_type_id' => $revenueType->id,
                    'montant_alloue' => 5000,
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'libelle' => 'Salaire août (corrigé)',
            'expense_type_id' => $expenseType->id,
        ]);
    }

    public function test_sync_requires_expense_type_id(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-SYNC',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenueType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'messe_semaine',
            'nom' => 'Messe Semaine',
            'actif' => true,
            'ordre' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->postJson(route('api.sync'), [
            'expenses' => [
                [
                    'action' => 'create',
                    'data' => [
                        'paroisse_id' => $paroisse->id,
                        'revenue_category_id' => $category->id,
                        'revenue_type_id' => $revenueType->id,
                        'date_depense' => '2026-08-10',
                        'montant' => 1000,
                        'libelle' => 'Sync sans type',
                        'methode_paiement' => 'especes',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expenses.0.data.expense_type_id']);
    }

    public function test_sync_creates_expense_with_expense_type(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Paul',
            'code_paroisse' => 'SP-SYNC2',
        ]);

        $user = User::factory()->create([
            'paroisse_id' => $paroisse->id,
        ]);

        $category = RevenueCategory::query()->create([
            'paroisse_id' => $paroisse->id,
            'code' => 'quete_ordinaire',
            'nom' => 'Quête',
            'actif' => true,
            'ordre' => 1,
        ]);

        $revenueType = RevenueType::query()->create([
            'paroisse_id' => $paroisse->id,
            'revenue_category_id' => $category->id,
            'code' => 'messe_semaine',
            'nom' => 'Messe Semaine',
            'actif' => true,
            'ordre' => 1,
        ]);

        $expenseType = ExpenseType::query()->where('code', 'carburant')->firstOrFail();

        $this->actingAs($user);

        $response = $this->postJson(route('api.sync'), [
            'expenses' => [
                [
                    'action' => 'create',
                    'data' => [
                        'paroisse_id' => $paroisse->id,
                        'revenue_category_id' => $category->id,
                        'revenue_type_id' => $revenueType->id,
                        'expense_type_id' => $expenseType->id,
                        'date_depense' => '2026-08-10',
                        'montant' => 2500,
                        'libelle' => 'Essence sync',
                        'methode_paiement' => 'especes',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('expenses', [
            'libelle' => 'Essence sync',
            'expense_type_id' => $expenseType->id,
        ]);
    }
}
