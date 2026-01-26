<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\JwtMiddleware;

class ReportProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_allocate_report_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project A',
        ]);
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-06',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        $payload = [
            'allocations' => [
                [
                    'project_id' => $project->id,
                    'project_net_amount' => 500,
                    'currency' => 'USD',
                    'units_sold' => 10,
                ],
            ],
            'overwrite' => true,
        ];

        $response = $this->postJson("/api/reports/{$report->id}/allocate", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['report_id' => $report->id]);
    }
}
