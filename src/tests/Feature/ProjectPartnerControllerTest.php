<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Project;
use App\Models\ProjectPartner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\JwtMiddleware;

class ProjectPartnerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_sync_project_partners(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project A',
        ]);
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner A',
            'email' => 'partnera@example.com',
        ]);

        $payload = [
            'partners' => [
                [
                    'partner_id' => $partner->id,
                    'share_percent' => 50,
                    'role' => 'Design',
                ],
            ],
            'mode' => 'sync',
            'detach_missing' => true,
        ];

        $response = $this->putJson("/api/projects/{$project->id}/partners", $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['project', 'partners']);
    }

    public function test_list_project_partners(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project B',
        ]);
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner B',
            'email' => 'partnerb@example.com',
        ]);

        ProjectPartner::create([
            'project_id' => $project->id,
            'partner_id' => $partner->id,
            'share_percent' => 100,
        ]);

        $response = $this->getJson("/api/projects/{$project->id}/partners");

        $response->assertStatus(200)
            ->assertJsonStructure(['project', 'partners']);
    }
}
