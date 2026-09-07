<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParoisseCureQuickCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_quick_create_cure_without_paroisse(): void
    {
        $user = $this->makeSuperAdmin();

        $response = $this->actingAs($user)->postJson(route('members.quick-store'), [
            'prenom' => 'Jean',
            'nom' => 'Mbemba',
            'sexe' => 'M',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('prenom', 'Jean');
        $response->assertJsonPath('nom', 'MBEMBA');
        $this->assertDatabaseHas('members', [
            'id' => $response->json('id'),
            'nom' => 'MBEMBA',
            'statut' => 'actif',
            'paroisse_id' => null,
        ]);
    }

    public function test_quick_create_cure_allows_empty_prenom(): void
    {
        $user = $this->makeSuperAdmin();

        $response = $this->actingAs($user)->postJson(route('members.quick-store'), [
            'nom' => 'Okoko',
            'sexe' => 'M',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('prenom', '');
        $response->assertJsonPath('nom', 'OKOKO');
        $response->assertJsonPath('label', 'OKOKO');
        $this->assertDatabaseHas('members', [
            'id' => $response->json('id'),
            'prenom' => '',
            'nom' => 'OKOKO',
        ]);
    }

    public function test_creating_paroisse_attaches_cure_to_paroisse(): void
    {
        $user = $this->makeSuperAdmin();

        $member = Member::query()->create([
            'prenom' => 'Paul',
            'nom' => 'NGOMA',
            'sexe' => 'M',
            'statut' => 'actif',
            'paroisse_id' => null,
        ]);

        $response = $this->actingAs($user)->post(route('paroisses.store'), [
            'nom' => 'Sainte Famille',
            'code_paroisse' => 'SF-CURE',
            'pays' => 'République du Congo',
            'cure_id' => $member->id,
            'actif' => '1',
        ]);

        $response->assertRedirect(route('application-configuration.index', ['tab' => 'paroisses']));

        $paroisse = Paroisse::query()->where('code_paroisse', 'SF-CURE')->firstOrFail();
        $this->assertSame((int) $member->id, (int) $paroisse->cure_id);
        $this->assertSame((int) $paroisse->id, (int) $member->fresh()->paroisse_id);
    }

    public function test_create_paroisse_page_shows_quick_cure_button(): void
    {
        $user = $this->makeSuperAdmin();

        $response = $this->actingAs($user)->get(route('paroisses.create'));

        $response->assertOk();
        $response->assertSee('Nouvelle paroisse', false);
        $response->assertSee('Nouveau curé', false);
        $response->assertSee(route('members.quick-store'), false);
        $response->assertSee('m-auto max-w-lg', false);
        $response->assertDontSee('Créer une paroisse : identifiants', false);
    }

    private function makeSuperAdmin(): User
    {
        Role::findOrCreate('super_admin', 'web');
        Permission::findOrCreate('create_members');
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $user->givePermissionTo('create_members');

        return $user;
    }
}
