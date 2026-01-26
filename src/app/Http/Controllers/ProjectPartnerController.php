<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectPartnerSyncRequest;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class ProjectPartnerController extends Controller
{
    /**
     * @OA\Put(
     *   path="/api/projects/{project}/partners",
     *   tags={"Projects"},
     *   summary="Sincronizar parceiros do projeto",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"partners"},
     *       @OA\Property(
     *         property="partners",
     *         type="array",
     *         @OA\Items(
     *           @OA\Property(property="partner_id", type="integer", example=1),
     *           @OA\Property(property="share_percent", type="number", format="float", example=50),
     *           @OA\Property(property="role", type="string", nullable=true),
     *           @OA\Property(property="valid_from", type="string", format="date", nullable=true),
     *           @OA\Property(property="valid_until", type="string", format="date", nullable=true)
     *         )
     *       ),
     *       @OA\Property(property="mode", type="string", enum={"sync","attach","detach"}, example="sync"),
     *       @OA\Property(property="detach_missing", type="boolean", example=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Parceiros sincronizados")
     * )
     */
    public function sync(ProjectPartnerSyncRequest $request, Project $project)
    {
        $data = $request->validated();

        $mode = $data['mode'] ?? 'sync';
        $detachMissing = $mode === 'sync'
            ? (bool)($data['detach_missing'] ?? true)
            : true; 

        $payload = [];
        foreach ($data['partners'] as $p) {
            $payload[(int)$p['partner_id']] = [
                'share_percent' => $p['share_percent'],
                'role' => $p['role'] ?? null,
                'valid_from' => $p['valid_from'] ?? null,
                'valid_until' => $p['valid_until'] ?? null,
            ];
        }

        DB::transaction(function () use ($project, $mode, $detachMissing, $payload) {
            if ($mode === 'attach') {
                $project->partners()->syncWithoutDetaching($payload);
                return;
            }

            if ($mode === 'detach') {
                $project->partners()->detach(array_keys($payload));
                return;
            }

            // default: sync
            $project->partners()->sync($payload, $detachMissing);
        });

        $project->load('partners');

        return response()->json([
            'project' => $project,
            'partners' => $project->partners,
        ], 200);
    }

    /**
     * @OA\Get(
     *   path="/api/projects/{project}/partners",
     *   tags={"Projects"},
     *   summary="Listar parceiros do projeto",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Parceiros do projeto")
     * )
     */
    public function index(Project $project)
    {
        $project->load('partners');

        return response()->json([
            'project' => $project->only(['id', 'title']),
            'partners' => $project->partners,
        ]);
    }

}
