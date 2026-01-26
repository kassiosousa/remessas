<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Payout;
use App\Models\Project;
use App\Models\ProjectPartner;
use App\Models\Report;
use App\Models\ReportProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSeed extends Seeder
{
    public function run(): void
    {
        // 1) Usuário admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@remessas.local'],
            [
                'name' => 'Admin Remessas',
                'password' => Hash::make('secret'),
                'type' => 'admin'
            ]
        );

        // 2) Parceiros
        $p1 = Partner::create([
            'created_by' => $admin->id,
            'name' => 'Alice Dev',
            'email' => 'alice@example.com',
        ]);

        $p2 = Partner::create([
            'created_by' => $admin->id,
            'name' => 'Bob Art',
            'email' => 'bob@example.com',
        ]);

        // 3) Projeto
        $proj = Project::create([
            'created_by' => $admin->id,
            'title' => 'Lighthouse',
            'description' => 'Jogo puzzle narrativo.',
            'url' => 'https://example.com/lighthouse',
        ]);

        // 4) Vínculo projeto-parceiros (60/40)
        ProjectPartner::create([
            'project_id' => $proj->id,
            'partner_id' => $p1->id,
            'share_percent' => 60.00,
            'role' => 'Design',
            'valid_from' => now()->subYears(1)->toDateString(),
        ]);
        ProjectPartner::create([
            'project_id' => $proj->id,
            'partner_id' => $p2->id,
            'share_percent' => 40.00,
            'role' => 'Art',
            'valid_from' => now()->subYears(1)->toDateString(),
        ]);

        // 5) Report (entrada)
        $report = Report::create([
            'created_by'   => $admin->id,
            'title'        => 'STEAM 2025-09',
            'platform'     => 'steam',
            'period_month' => '2025-09',
            'currency'     => 'USD',
            'gross_amount' => 10000.00,
            'fees'         => 500.00,
            'taxes'        => 1000.00,
            'net_amount'   => 8500.00,
            'statement_ref'=> 'STMT-ABC-123',
        ]);

        // 6) Alocação do report por projeto (100% deste report para Lighthouse)
        $rp = ReportProject::create([
            'project_id'         => $proj->id,
            'report_id'          => $report->id,
            'units_sold'         => 1200,
            'project_net_amount' => 8500.00,
            'currency'           => 'USD',
        ]);

        // 7) Payouts (saídas) a partir dos percentuais vigentes
        $shares = $proj->partners()->withPivot(['share_percent','valid_from','valid_until'])->get();

        foreach ($shares as $partner) {
            $amount = round(($partner->pivot->share_percent / 100) * $rp->project_net_amount, 2);

            Payout::create([
                'report_id'  => $report->id,
                'project_id' => $proj->id,
                'partner_id' => $partner->id,
                'currency'   => $rp->currency,
                'amount'     => $amount,
                'status'     => 'pending',
                'due_date'   => now()->addDays(7)->toDateString(),
                'notes'      => 'Seed gerado automaticamente.',
            ]);
        }

    }
}
