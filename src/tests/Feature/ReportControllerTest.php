<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\JwtMiddleware;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_list_reports(): void
    {
        $user = User::factory()->create();
        Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-01',
            'currency' => 'USD',
            'gross_amount' => 100,
        ]);

        $response = $this->getJson('/api/reports');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'current_page']);
    }

    public function test_create_report(): void
    {
        $user = User::factory()->create();

        $payload = [
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-02',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ];

        $response = $this->postJson('/api/reports', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['platform' => 'steam']);
    }

    public function test_show_report(): void
    {
        $user = User::factory()->create();
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-03',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        $response = $this->getJson("/api/reports/{$report->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $report->id]);
    }

    public function test_update_report(): void
    {
        $user = User::factory()->create();
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-04',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        $payload = [
            'title' => 'Updated Report',
        ];

        $response = $this->putJson("/api/reports/{$report->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Report']);
    }

    public function test_delete_report(): void
    {
        $user = User::factory()->create();
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-05',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        $response = $this->deleteJson("/api/reports/{$report->id}");

        $response->assertStatus(204);
    }
}
