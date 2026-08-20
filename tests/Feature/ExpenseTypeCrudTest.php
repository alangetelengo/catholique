<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Paroisse;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTypeCrudTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsSuperAdmin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_create_expense_type(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(route('expense-types.store'), [
            'code' => 'formation',
            'nom' => 'Formation',
            'description' => 'Frais de formation',
            'ordre' => 10,
            'actif' => 1,
        ]);

        $response->assertRedirect(route('expense-types.index'));
        $this->assertDatabaseHas('expense_types', [
            'code' => 'formation',
            'nom' => 'Formation',
            'actif' => 1,
        ]);
    }

    public function test_non_admin_cannot_create_expense_type(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('expense-types.store'), [
            'code' => 'formation',
            'nom' => 'Formation',
            'ordre' => 10,
            'actif' => 1,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('expense_types', ['code' => 'formation']);
    }

    public function test_admin_can_update_expense_type(): void
    {
        $this->actingAsSuperAdmin();
        $type = ExpenseType::query()->where('code', 'autre')->firstOrFail();

        $response = $this->put(route('expense-types.update', $type), [
            'code' => 'autre',
            'nom' => 'Autre (mis à jour)',
            'description' => 'Divers',
            'ordre' => 99,
            'actif' => 1,
        ]);

        $response->assertRedirect(route('expense-types.index'));
        $this->assertDatabaseHas('expense_types', [
            'id' => $type->id,
            'nom' => 'Autre (mis à jour)',
        ]);
    }

    public function test_cannot_delete_expense_type_in_use(): void
    {
        $user = $this->actingAsSuperAdmin();
        $user->update([
            'paroisse_id' => Paroisse::query()->create([
                'nom' => 'Test',
                'code_paroisse' => 'T1',
            ])->id,
        ]);

        $type = ExpenseType::query()->where('code', 'salaires')->firstOrFail();

        Expense::query()->create([
            'paroisse_id' => $user->paroisse_id,
            'expense_type_id' => $type->id,
            'montant' => 1000,
            'date_depense' => '2026-08-01',
            'libelle' => 'Test',
            'statut' => 'valide',
            'methode_paiement' => 'especes',
            'created_by' => $user->id,
        ]);

        $response = $this->from(route('expense-types.index'))
            ->delete(route('expense-types.destroy', $type));

        $response->assertRedirect(route('expense-types.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('expense_types', ['id' => $type->id]);
    }

    public function test_configuration_panel_lists_expense_types(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->get(route('application-configuration.index', ['tab' => 'expense-types']));

        $response->assertOk();
        $response->assertSee('Types dépenses', false);
        $response->assertSee('Alimentation / popote', false);
        $response->assertSee('Nouveau type', false);
    }

    public function test_non_admin_sees_expense_types_without_actions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('application-configuration.index', ['tab' => 'expense-types']));

        $response->assertOk();
        $response->assertSee('Alimentation / popote', false);
        $response->assertDontSee('Nouveau type', false);
    }
}
