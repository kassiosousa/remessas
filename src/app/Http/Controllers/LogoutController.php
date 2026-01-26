<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Annotations as OA;

class LogoutController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Auth"},
     *   summary="Logout do usuário",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Usuário deslogado",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Usuário deslogado com sucesso")
     *     )
     *   )
     * )
     */
    public function __invoke(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Usuário deslogado com sucesso'], 200);
    }
}