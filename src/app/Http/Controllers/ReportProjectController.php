<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportAllocateRequest;
use App\Models\Report;
use App\Models\ReportProject;
use Illuminate\Support\Facades\DB;

class ReportProjectController extends Controller
{
    // POST /reports/{report}/allocate
    public function allocate(ReportAllocateRequest $request, Report $report)
    {
        $data      = $request->validated();
        $allocs    = $data['allocations'];
        $overwrite = (bool)($data['overwrite'] ?? false);

        print_r($request); 
        echo $report->id;
        die;

        // Valida se todos os projects existem antes de escrever no BD
        $projectIds = collect($allocs)->pluck('project_id')->unique()->values();
        $found = \App\Models\Project::whereIn('id', $projectIds)->pluck('id');
        $missing = $projectIds->diff($found);
        if ($missing->isNotEmpty()) {
            return response()->json([
                'error' => 'Project(s) inexistente(s).',
                'missing_project_ids' => $missing->values(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($report, $allocs, $overwrite) {
                if ($overwrite) {
                    ReportProject::where('report_id', $report->id)->delete();
                }
                foreach ($allocs as $a) {
                    ReportProject::updateOrCreate(
                        ['report_id' => $report->id, 'project_id' => $a['project_id']],
                        [
                            'project_net_amount' => $a['project_net_amount'],
                            'currency'           => $a['currency'] ?? 'USD',
                            'units_sold'         => $a['units_sold'] ?? null,
                        ]
                    );
                }
            });

            // Retorno leve (sem eager load pesado)
            $items = ReportProject::where('report_id', $report->id)
                ->select('project_id','project_net_amount','currency','units_sold')
                ->orderBy('project_id')
                ->get();

            return response()->json([
                'message'   => 'Alocações salvas com sucesso.',
                'report_id' => $report->id,
                'items'     => $items,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('allocate query error', ['report_id' => $report->id, 'msg' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Erro de banco ao alocar projetos.',
                'message' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            \Log::error('allocate error', ['report_id' => $report->id, 'msg' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Erro ao alocar projetos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
