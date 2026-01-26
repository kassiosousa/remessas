<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartnerStoreRequest;
use App\Http\Requests\PartnerUpdateRequest;
use App\Models\Partner;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class PartnerController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/partners",
     *   tags={"Partners"},
     *   summary="Listar parceiros",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="q",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(response=200, description="Lista de parceiros")
     * )
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $partners = Partner::query()
            ->when($q, fn($qb) => $qb->where('name', 'like', "%{$q}%"))
            ->orderByDesc('id', 'name')
            ->paginate(20);

        return response()->json($partners);

    }

    /**
     * @OA\Post(
     *   path="/api/partners",
     *   tags={"Partners"},
     *   summary="Criar parceiro",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"created_by","name","email"},
     *       @OA\Property(property="created_by", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Parceiro X"),
     *       @OA\Property(property="email", type="string", example="parceiro@email.com"),
     *       @OA\Property(property="portfolio", type="string", nullable=true),
     *       @OA\Property(property="birthday", type="string", format="date", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=201, description="Parceiro criado")
     * )
     */
    public function store(PartnerStoreRequest $request)
    {
        $partner = Partner::create($request->validated());
        return response()->json($partner, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/partners/{partner}",
     *   tags={"Partners"},
     *   summary="Detalhar parceiro",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="partner",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=200, description="Parceiro")
     * )
     */
    public function show(Partner $partner)
    {
        // se quiser trazer projetos do sócio:
        $partner->load('projects');
        return response()->json($partner);
    }

    /**
     * @OA\Put(
     *   path="/api/partners/{partner}",
     *   tags={"Partners"},
     *   summary="Atualizar parceiro",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="partner",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"created_by","name","email"},
     *       @OA\Property(property="created_by", type="integer", example=1),
     *       @OA\Property(property="name", type="string", example="Parceiro X"),
     *       @OA\Property(property="email", type="string", example="parceiro@email.com"),
     *       @OA\Property(property="portfolio", type="string", nullable=true),
     *       @OA\Property(property="birthday", type="string", format="date", nullable=true)
     *     )
     *   ),
     *   @OA\Response(response=200, description="Parceiro atualizado")
     * )
     */
    public function update(PartnerUpdateRequest $request, Partner $partner)
    {
        $partner->update($request->validated());
        return response()->json($partner);
    }

    /**
     * @OA\Delete(
     *   path="/api/partners/{partner}",
     *   tags={"Partners"},
     *   summary="Remover parceiro",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="partner",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response=204, description="Parceiro removido")
     * )
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();
        return response()->json([], 204);
    }
}
