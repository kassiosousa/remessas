<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\JwtMiddleware;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_list_projects(): void
    {
        $user = User::factory()->create();
        Project::create([
            'created_by' => $user->id,
            'title' => 'Project A',
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'current_page']);
    }

    public function test_create_project(): void
    {
        $user = User::factory()->create();

        $payload = [
            'created_by' => $user->id,
            'title' => 'Project B',
            'description' => 'Desc',
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Project B']);
    }

    public function test_show_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project C',
        ]);

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $project->id]);
    }

    public function test_update_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project D',
        ]);

        $payload = [
            'created_by' => $user->id,
            'title' => 'Project D2',
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Project D2']);
    }

    public function test_delete_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project E',
        ]);

        $response = $this->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(204);
    }
}
