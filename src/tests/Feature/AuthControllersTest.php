<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Middleware\JwtMiddleware;

class AuthControllersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_register_user(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['message' => 'Usuário registrado com sucesso']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_login_success(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret123'),
        ]);

        JWTAuth::shouldReceive('attempt')->once()->andReturn('token123');
        JWTAuth::shouldReceive('factory')->once()->andReturn(new class {
            public function getTTL(): int
            {
                return 60;
            }
        });

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'expires_in']);
    }

    public function test_login_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'invalid@example.com',
            'password' => Hash::make('secret123'),
        ]);

        JWTAuth::shouldReceive('attempt')->once()->andReturn(false);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment(['error' => 'Credenciais inválidas']);
    }

    public function test_logout(): void
    {
        JWTAuth::shouldReceive('getToken')->once()->andReturn('token123');
        JWTAuth::shouldReceive('invalidate')->once();

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Usuário deslogado com sucesso']);
    }

    public function test_auth_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonStructure(['user']);
    }
}
