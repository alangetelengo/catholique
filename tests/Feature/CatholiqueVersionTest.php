<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatholiqueVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_catholique_version_config_has_semantic_default(): void
    {
        $this->assertSame('1.0.0', config('catholique.version'));
        $this->assertSame('2026-09-07', config('catholique.released_at'));
    }

    public function test_catholique_version_command_displays_version(): void
    {
        $name = config('app.name', 'Catholique');

        $this->artisan('catholique:version')
            ->expectsOutputToContain("{$name} 1.0.0")
            ->assertSuccessful();
    }

    public function test_login_page_displays_version(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('v1.0.0', false);
    }

    public function test_footer_displays_version_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('v'.config('catholique.version'), false);
    }
}
