<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Contracts\JWTSubject;
use OpenApi\Annotations as OA;

class AuthUserController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/auth/user",
     *   tags={"Auth"},
     *   summary="Usuário autenticado",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Usuário atual",
     *     @OA\JsonContent(
     *       @OA\Property(property="user", type="object")
     *     )
     *   )
     * )
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        return response()->json(compact('user'), 200);
    }
}