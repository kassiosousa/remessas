<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="RemessasSteam API",
 *     version="1.0.0",
 *     description="API para gestão de projetos, parceiros e divisão de receitas"
 *   )
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\PathItem(
 *   path="/api",
 *   @OA\Get(
 *     tags={"Health"},
 *     summary="Status da API",
 *     @OA\Response(
 *       response=200,
 *       description="API disponível",
 *       @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="api ok")
 *       )
 *     )
 *   )
 * )
 */
class OpenApi {}
