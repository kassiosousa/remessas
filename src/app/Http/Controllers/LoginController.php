<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class LoginController extends Controller
{
    /**
    * @OA\Post(
    *   path="/api/auth/login",
     *   tags={"Auth"},
     *   summary="Autenticar usuário",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", example="admin@email.com"),
     *       @OA\Property(property="password", type="string", example="123456")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Login realizado com sucesso",
     *     @OA\JsonContent(
     *       @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9"),
     *       @OA\Property(property="user", type="object")
     *     )
     *   ),
     *   @OA\Response(response=401, description="Credenciais inválidas")
     * )
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate(
            [
                'email' => 'required|email|string',
                'password' => 'required|string|min:6|max:12',
            ],
            [
                'email.required' => 'O campo de email é obrigatório.',
                'email.email' => 'O campo de email deve ser um endereço de email válido.',
                'password.required' => 'O campo de senha é obrigatório.',
                'password.string' => 'O campo de senha deve ser uma string.',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
                'password.max' => 'A senha deve ter no máximo 12 caracteres.',
            ]
        );

        try {
            if (!$token = JWTAuth::attempt($data)) {
                return response()->json(['error' => 'Credenciais inválidas'], 401);
            }

            return response()->json([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Não foi possível criar o token'], 500);
        }

    }   
}