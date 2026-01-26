<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Payout;
use App\Models\Project;
use App\Models\ProjectPartner;
use App\Models\Report;
use App\Models\ReportProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Http\Middleware\JwtMiddleware;

class PayoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(JwtMiddleware::class);
    }

    public function test_list_payouts(): void
    {
        $user = User::factory()->create();
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner A',
            'email' => 'a@example.com',
        ]);
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project A',
        ]);
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-07',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        Payout::create([
            'report_id' => $report->id,
            'project_id' => $project->id,
            'partner_id' => $partner->id,
            'currency' => 'USD',
            'amount' => 100,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/payouts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'current_page']);
    }

    public function test_generate_payouts_from_report(): void
    {
        $user = User::factory()->create();
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner B',
            'email' => 'b@example.com',
        ]);
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project B',
        ]);
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-08',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        ProjectPartner::create([
            'project_id' => $project->id,
            'partner_id' => $partner->id,
            'share_percent' => 50,
        ]);

        ReportProject::create([
            'project_id' => $project->id,
            'report_id' => $report->id,
            'project_net_amount' => 500,
            'currency' => 'USD',
        ]);

        $response = $this->postJson("/api/reports/{$report->id}/generate-payouts", [
            'due_date' => now()->toDateString(),
            'reset_existing' => false,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['report_id' => $report->id]);
    }

    public function test_show_update_mark_paid_and_delete_payout(): void
    {
        $user = User::factory()->create();
        $partner = Partner::create([
            'created_by' => $user->id,
            'name' => 'Partner C',
            'email' => 'c@example.com',
        ]);
        $project = Project::create([
            'created_by' => $user->id,
            'title' => 'Project C',
        ]);
        $report = Report::create([
            'created_by' => $user->id,
            'platform' => 'steam',
            'period_month' => '2025-09',
            'currency' => 'USD',
            'gross_amount' => 1000,
        ]);

        $payout = Payout::create([
            'report_id' => $report->id,
            'project_id' => $project->id,
            'partner_id' => $partner->id,
            'currency' => 'USD',
            'amount' => 100,
            'status' => 'pending',
        ]);

        $this->getJson("/api/payouts/{$payout->id}")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $payout->id]);

        $this->putJson("/api/payouts/{$payout->id}", [
            'status' => 'scheduled',
        ])->assertStatus(200)
          ->assertJsonFragment(['status' => 'scheduled']);

        $this->postJson("/api/payouts/{$payout->id}/mark-paid", [
            'method' => 'pix',
        ])->assertStatus(200)
          ->assertJsonFragment(['status' => 'paid']);

        $this->deleteJson("/api/payouts/{$payout->id}")
            ->assertStatus(204);
    }
}
