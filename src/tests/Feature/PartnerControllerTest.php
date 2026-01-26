<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\JwtMiddleware;

class PartnerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_list_partners(): void
    {
        $user = User::factory()->create();
        Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner A',
            'email' => 'a@example.com',
        ]);

        $response = $this->getJson('/api/partners');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'current_page']);
    }

    public function test_create_partner(): void
    {
        $user = User::factory()->create();

        $payload = [
            'created_by' => $user->id,
            'name' => 'Partner B',
            'email' => 'b@example.com',
        ];

        $response = $this->postJson('/api/partners', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Partner B']);
    }

    public function test_show_partner(): void
    {
        $user = User::factory()->create();
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner C',
            'email' => 'c@example.com',
        ]);

        $response = $this->getJson("/api/partners/{$partner->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $partner->id]);
    }

    public function test_update_partner(): void
    {
        $user = User::factory()->create();
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner D',
            'email' => 'd@example.com',
        ]);

        $payload = [
            'created_by' => $user->id,
            'name' => 'Partner D2',
            'email' => 'd2@example.com',
        ];

        $response = $this->putJson("/api/partners/{$partner->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Partner D2']);
    }

    public function test_delete_partner(): void
    {
        $user = User::factory()->create();
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner E',
            'email' => 'e@example.com',
        ]);

        $response = $this->deleteJson("/api/partners/{$partner->id}");

        $response->assertStatus(204);
    }
}
