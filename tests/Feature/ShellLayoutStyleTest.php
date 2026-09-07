<?php

namespace Tests\Feature;

use App\Models\Paroisse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShellLayoutStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_green_ged_accent(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('#00b464', false);
        $response->assertSee('rgba(0,180,100', false);
        $response->assertDontSee('#d4a84b', false);
        $response->assertDontSee('#4a3570', false);
    }

    public function test_authenticated_shell_uses_cosud_green_layout(): void
    {
        $paroisse = Paroisse::query()->create([
            'nom' => 'Saint Esprit',
            'code_paroisse' => 'SE-SHELL',
        ]);
        $user = User::factory()->create(['paroisse_id' => $paroisse->id]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="navHeader"', false);
        $response->assertSee('id="mainHeader"', false);
        $response->assertSee('class="sidebar', false);
        $response->assertSee('from-[#00b464]', false);
        $response->assertSee('to-[#00ff88]', false);
        $response->assertDontSee('from-church-gold', false);
        $response->assertDontSee('#8b6cb8', false);
    }
}
