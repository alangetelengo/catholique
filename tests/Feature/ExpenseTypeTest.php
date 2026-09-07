<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\RevenueCategory;
use App\Models\User;
use App\Services\CaisseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTypeTest extends TestCase
{
    use RefreshDatabase;

    private function envelopeFromDate(string $date): array
    {
        $parsed = Carbon::parse($date);

        return [
            'mois_capital' => $parsed->format('m'),
            'annee_capital' => (int) $parsed->format('Y'),
        ];
    }

    private function creditCaisse(Paroisse $paroisse, string $code, float $montant, User $user, string $date = '2026-08-01'): Caisse
    {
        $caisse = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', $code)->firstOrFail();
        app(CaisseService::class)->creditDirect($caisse, $montant, $date, 'Crédit test', null, $user->id);

        return $caisse;
    }

    public function test_expense_types_are_seeded(): void
    {
        $this->assertDatabaseHas('expense_types', ['code' => 'alimentation_popote']);
        $this->assertDatabaseHas('expense_types', ['code' => 'salaires']);
        $this->assertDatabaseHas('expense_types', ['code' => 'autre']);
        $this->assertDatabaseHas('expense_types', ['code' => 'liturgie']);
        $this->assertDatabaseHas('expense_types', ['code' => 'transport']);
        $this->assertTrue(ExpenseType::query()->count() >= 6);
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

        $caisse = $this->creditCaisse($paroisse, 'alimentation_popote', 100000, $user);

        $this->actingAs($user);

        $response = $this->post(route('expenses.store'), [
            'date_depense' => '2026-08-10',
            ...$this->envelopeFromDate('2026-08-10'),
            'montant' => 10000,
            'libelle' => 'Achat test',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                [
                    'caisse_id' => $caisse->id,
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

        $caisse = $this->creditCaisse($paroisse, 'alimentation_popote', 100000, $user, '2026-08-10');
        $expenseType = ExpenseType::query()->where('code', 'alimentation_popote')->firstOrFail();

        $this->actingAs($user);

        $response = $this->post(route('expenses.store'), [
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-10',
            ...$this->envelopeFromDate('2026-08-10'),
            'montant' => 10000,
            'libelle' => 'Achat riz popote',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                [
                    'caisse_id' => $caisse->id,
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

        $caisse = $this->creditCaisse($paroisse, 'salaires', 100000, $user, '2026-08-05');

        $expenseType = ExpenseType::query()->where('code', 'salaires')->firstOrFail();
        $expenseType->update(['actif' => false]);

        $expense = Expense::query()->create([
            'paroisse_id' => $paroisse->id,
            'expense_type_id' => $expenseType->id,
            'montant' => 5000,
            'date_depense' => '2026-08-05',
            'mois_capital' => '08',
            'annee_capital' => 2026,
            'libelle' => 'Salaire août',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $expense->fundingSources()->create([
            'caisse_id' => $caisse->id,
            'montant_alloue' => 5000,
            'ordre' => 1,
        ]);

        app(CaisseService::class)->syncDepenseMouvements($expense->fresh('fundingSources'), []);

        $this->actingAs($user);

        $editResponse = $this->get(route('expenses.edit', $expense));
        $editResponse->assertOk();
        $editResponse->assertSee('Salaires', false);
        $editResponse->assertSee('(inactif)', false);

        $updateResponse = $this->put(route('expenses.update', $expense), [
            'expense_type_id' => $expenseType->id,
            'date_depense' => '2026-08-05',
            ...$this->envelopeFromDate('2026-08-05'),
            'montant' => 5000,
            'libelle' => 'Salaire août (corrigé)',
            'methode_paiement' => 'especes',
            'funding_sources' => [
                [
                    'caisse_id' => $caisse->id,
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

        $this->actingAs($user);

        $response = $this->postJson(route('api.sync'), [
            'expenses' => [
                [
                    'action' => 'create',
                    'data' => [
                        'paroisse_id' => $paroisse->id,
                        'revenue_category_id' => $category->id,
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

        $transport = Caisse::query()->where('paroisse_id', $paroisse->id)->where('code', 'transport')->firstOrFail();
        app(CaisseService::class)->creditDirect($transport, 50000, '2026-08-10', 'Crédit transport', null, $user->id);

        $expenseType = ExpenseType::query()->where('code', 'transport')->firstOrFail();

        $this->actingAs($user);

        $response = $this->postJson(route('api.sync'), [
            'expenses' => [
                [
                    'action' => 'create',
                    'data' => [
                        'paroisse_id' => $paroisse->id,
                        'expense_type_id' => $expenseType->id,
                        'date_depense' => '2026-08-10',
                        'mois_capital' => '08',
                        'annee_capital' => 2026,
                        'montant' => 2500,
                        'libelle' => 'Essence sync',
                        'methode_paiement' => 'especes',
                        'funding_sources' => [
                            [
                                'caisse_id' => $transport->id,
                                'montant_alloue' => 2500,
                            ],
                        ],
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
