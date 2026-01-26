<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/register",
     *   tags={"Auth"},
     *   summary="Registrar usuário",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","password","password_confirmation"},
     *       @OA\Property(property="name", type="string", example="Fulano"),
     *       @OA\Property(property="email", type="string", example="fulano@email.com"),
     *       @OA\Property(property="password", type="string", example="123456"),
     *       @OA\Property(property="password_confirmation", type="string", example="123456")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Usuário registrado",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Usuário registrado com sucesso")
     *     )
     *   )
     * )
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|string|unique:users,email',
                'password' => 'required|string|min:6|max:12|confirmed',
            ],
            [
                'name.required' => 'O campo de nome é obrigatório.',
                'name.string' => 'O campo de nome deve ser uma string.',
                'name.max' => 'O nome deve ter no máximo 255 caracteres.',
                'email.required' => 'O campo de email é obrigatório.',
                'email.email' => 'O campo de email deve ser um endereço de email válido.',
                'email.unique' => 'Este email já está em uso.',
                'password.required' => 'O campo de senha é obrigatório.',
                'password.string' => 'O campo de senha deve ser uma string.',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
                'password.max' => 'A senha deve ter no máximo 12 caracteres.',
                'password.confirmed' => 'A confirmação da senha não corresponde.',
            ]
        );

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(['message' => 'Usuário registrado com sucesso'], 201);
    }
}