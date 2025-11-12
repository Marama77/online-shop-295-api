<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use ReallySimpleJWT\Token;

require __DIR__ . "/../vendor/autoload.php";

require_once "db.php";

require "product.php";

require "category.php";

$secret = 'sec!ReT423*&';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);

/**
 * @OA\Post(
 *     path="/authenticate",
 *     summary="Authenticate an unauthenticated user.",
 *     tags={"general"},
 *     requestBody=@OA\RequestBody(
 *         request="/authenticate",
 *         required=true,
 *         description="ID of the member to fetch.",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="username", type="string", example="php-user"),
 *                 @OA\Property(property="password", type="string", example="Admin123")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="200", description="Authentication successfull."),
 *     @OA\Response(response="401", description="Invalid credentials.")
 * )
 */

 $app->post("/authenticate", function (Request $request, Response $response, $args) {
    global $secret;

    $data = $request->getParsedBody();

    if ($data["username"] != "php-user" || $data["password"] != "Admin123Admin12") {
        $response->getBody()->write(json_encode(array(
            "error" => "Invalid credentials."
        )));
        return $response->withStatus(401); //Authentication failed.
    }

    $userId = 1;
    $expiration = time() + 3600;
    $issuer = 'localhost';

    $token = Token::create($userId, $secret, $expiration, $issuer);

    setcookie("token", $token);

    return $response->withStatus(200); //The request was successfully processed.
});

?>