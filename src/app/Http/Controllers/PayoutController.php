<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayoutGenerateRequest;
use App\Http\Requests\PayoutMarkPaidRequest;
use App\Http\Requests\PayoutUpdateRequest;
use App\Models\Payout;
use App\Models\Project;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $q = Payout::query()
            ->with(['partner:id,name,email', 'project:id,title', 'report:id,platform,period_month'])
            ->when($request->status, fn($x) => $x->where('status', $request->status))
            ->when($request->partner_id, fn($x) => $x->where('partner_id', $request->integer('partner_id')))
            ->when($request->project_id, fn($x) => $x->where('project_id', $request->integer('project_id')))
            ->when($request->report_id, fn($x) => $x->where('report_id', $request->integer('report_id')))
            ->orderBy('due_date')
            ->orderBy('id');

        return $q->paginate($request->integer('per_page', 20));
    }

    public function show(Payout $payout)
    {
        $payout->load(['partner','project','report']);
        return $payout;
    }

    // POST /reports/{report}/generate-payouts
    public function generateFromReport(PayoutGenerateRequest $request, Report $report)
    {
        $dueDate = $request->input('due_date');
        $reset   = $request->boolean('reset_existing');

        DB::transaction(function () use ($report, $dueDate, $reset) {
            // opcionalmente apaga pendentes para regenerar
            if ($reset) {
                Payout::where('report_id', $report->id)->whereIn('status', ['pending','scheduled'])->delete();
            }

            // pega alocações por projeto
            $report->load(['projects.project.partners' => function ($q) {
                $q->withPivot(['share_percent','valid_from','valid_until']);
            }]);

            foreach ($report->projects as $rp) {
                /** @var Project $project */
                $project = $rp->project;

                foreach ($project->partners as $partner) {
                    $percent = (float)$partner->pivot->share_percent;
                    if ($percent <= 0) continue;

                    $amount = round(($percent / 100) * (float)$rp->project_net_amount, 2);

                    // evita duplicação: procura payout existente para a tripla
                    $existing = Payout::where([
                        'report_id'  => $report->id,
                        'project_id' => $project->id,
                        'partner_id' => $partner->id,
                    ])->first();

                    if ($existing) {
                        // se já existe e ainda não pago/cancelado, atualiza o amount
                        if (!in_array($existing->status, ['paid','canceled'])) {
                            $existing->update([
                                'amount'   => $amount,
                                'currency' => $rp->currency,
                                'due_date' => $existing->due_date ?? $dueDate,
                            ]);
                        }
                        continue;
                    }

                    Payout::create([
                        'report_id'  => $report->id,
                        'project_id' => $project->id,
                        'partner_id' => $partner->id,
                        'currency'   => $rp->currency,
                        'amount'     => $amount,
                        'status'     => 'pending',
                        'due_date'   => $dueDate,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Payouts gerados/atualizados com sucesso.',
            'report_id' => $report->id,
        ], 201);
    }

    public function update(PayoutUpdateRequest $request, Payout $payout)
    {
        $payout->update($request->validated());
        return $payout->fresh();
    }

    // POST /payouts/{payout}/mark-paid
    public function markPaid(PayoutMarkPaidRequest $request, Payout $payout)
    {
        $data = $request->validated();

        // Uploads opcionais
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('payouts/receipts');
            $data['receipt_path'] = $path;
        }
        if ($request->hasFile('partner_invoice')) {
            $path = $request->file('partner_invoice')->store('payouts/partner_invoices');
            $data['partner_invoice_path'] = $path;
        }

        $data['status']  = 'paid';
        $data['paid_at'] = $data['paid_at'] ?? now();

        $payout->update($data);
        return $payout->fresh();
    }

    public function destroy(Payout $payout)
    {
        // Apaga arquivos ligados (se existirem)
        if ($payout->receipt_path) {
            Storage::delete($payout->receipt_path);
        }
        if ($payout->partner_invoice_path) {
            Storage::delete($payout->partner_invoice_path);
        }
        $payout->delete();
        return response()->noContent();
    }
}
