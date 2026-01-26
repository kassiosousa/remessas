<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Requests\ProjectUpdateRequest;
use App\Models\Project;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/projects",
     *   tags={"Projects"},
     *   summary="Listar projetos",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="q",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="Lista de projetos")
     * )
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $projects = Project::query()
            ->when($q, fn($qb) => $qb->where('title', 'like', "%{$q}%"))
            ->orderByDesc('id', 'description')
            ->paginate(20);

        return response()->json($projects);
    }

    /**
     * @OA\Post(
     *   path="/api/projects",
     *   tags={"Projects"},
     *   summary="Criar projeto",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"created_by","title"},
     *       @OA\Property(property="created_by", type="integer", example=1),
     *       @OA\Property(property="title", type="string", example="Projeto X"),
     *       @OA\Property(property="description", type="string", nullable=true),
     *       @OA\Property(property="date_release", type="string", format="date", nullable=true),
     *       @OA\Property(property="finished", type="boolean", nullable=true),
     *       @OA\Property(property="url", type="string", nullable=true),
     *       @OA\Property(property="steam_id", type="integer", nullable=true),
     *       @OA\Property(property="capsule", type="string", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Projeto criado")
     * )
     */
    public function store(ProjectStoreRequest $request)
    {
        $project = Project::create($request->validated());
        return response()->json($project, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/projects/{project}",
     *   tags={"Projects"},
     *   summary="Detalhar projeto",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Projeto")
     * )
     */
    public function show(Project $project)
    {
        // trazer sócios e pivots
        $project->load('partners');
        return response()->json($project);
    }

    /**
     * @OA\Put(
     *   path="/api/projects/{project}",
     *   tags={"Projects"},
     *   summary="Atualizar projeto",
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
     *       required={"created_by","title"},
     *       @OA\Property(property="created_by", type="integer", example=1),
     *       @OA\Property(property="title", type="string", example="Projeto X"),
     *       @OA\Property(property="description", type="string", nullable=true),
     *       @OA\Property(property="date_release", type="string", format="date", nullable=true),
     *       @OA\Property(property="finished", type="boolean", nullable=true),
     *       @OA\Property(property="url", type="string", nullable=true),
     *       @OA\Property(property="steam_id", type="integer", nullable=true),
     *       @OA\Property(property="capsule", type="string", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Projeto atualizado")
     * )
     */
    public function update(ProjectUpdateRequest $request, Project $project)
    {
        $project->update($request->validated());
        return response()->json($project);
    }

    /**
     * @OA\Delete(
     *   path="/api/projects/{project}",
     *   tags={"Projects"},
     *   summary="Remover projeto",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="project",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=204, description="Projeto removido")
     * )
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json([], 204);
    }
}
