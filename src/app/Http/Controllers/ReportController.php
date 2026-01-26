<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportStoreRequest;
use App\Http\Requests\ReportUpdateRequest;
use App\Models\Report;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ReportController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/reports",
     *   tags={"Reports"},
     *   summary="Listar relatórios",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="platform", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="period_month", in="query", required=false, @OA\Schema(type="string", example="2024-12")),
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=20)),
     *   @OA\Response(response=200, description="Lista de relatórios")
     * )
     */
    public function index(Request $request)
    {
        $q = Report::query()
            ->withCount(['projects as projects_count', 'payouts as payouts_count'])
            ->when($request->platform, fn($x) => $x->where('platform', $request->platform))
            ->when($request->period_month, fn($x) => $x->where('period_month', $request->period_month))
            ->orderByDesc('id');

        return $q->paginate($request->integer('per_page', 20));
    }

    /**
     * @OA\Post(
     *   path="/api/reports",
     *   tags={"Reports"},
     *   summary="Criar relatório",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"platform","period_month","currency","gross_amount"},
     *       @OA\Property(property="title", type="string", nullable=true),
     *       @OA\Property(property="platform", type="string", example="steam"),
     *       @OA\Property(property="period_month", type="string", example="2024-12"),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="gross_amount", type="number", format="float", example=1000),
     *       @OA\Property(property="fees", type="number", format="float", example=50),
     *       @OA\Property(property="taxes", type="number", format="float", example=30),
     *       @OA\Property(property="net_amount", type="number", format="float", example=920),
     *       @OA\Property(property="statement_ref", type="string", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Relatório criado")
     * )
     */
    public function store(ReportStoreRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id ?? null;

        $report = Report::create($data);
        return response()->json($report, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/reports/{report}",
     *   tags={"Reports"},
     *   summary="Detalhar relatório",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="report", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Relatório")
     * )
     */
    public function show(Report $report)
    {
        $report->load(['projects.project', 'payouts.partner']);
        return $report;
    }
    

    /**
     * @OA\Put(
     *   path="/api/reports/{report}",
     *   tags={"Reports"},
     *   summary="Atualizar relatório",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="report", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       @OA\Property(property="title", type="string", nullable=true),
     *       @OA\Property(property="platform", type="string", example="steam"),
     *       @OA\Property(property="period_month", type="string", example="2024-12"),
     *       @OA\Property(property="currency", type="string", example="USD"),
     *       @OA\Property(property="gross_amount", type="number", format="float", example=1000),
     *       @OA\Property(property="fees", type="number", format="float", example=50),
     *       @OA\Property(property="taxes", type="number", format="float", example=30),
     *       @OA\Property(property="net_amount", type="number", format="float", example=920),
     *       @OA\Property(property="statement_ref", type="string", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Relatório atualizado")
     * )
     */
    public function update(ReportUpdateRequest $request, Report $report)
    {
        $report->update($request->validated());
        return $report->fresh();
    }

    /**
     * @OA\Delete(
     *   path="/api/reports/{report}",
     *   tags={"Reports"},
     *   summary="Remover relatório",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(name="report", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="Relatório removido")
     * )
     */
    public function destroy(Report $report)
    {
        $report->delete();
        return response()->noContent();
    }
}
