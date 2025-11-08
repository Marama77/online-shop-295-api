<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use ReallySimpleJWT\Token;

require __DIR__ . "/../vendor/autoload.php";

require_once "db.php";

$secret = 'sec!ReT423*&';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);

/**
 * @OA\Info(title="online-shop-295-api", version="1") 
 */

/**
 * @OA\Post(
 *     path="/authenticate",
 *     summary="Authenticate an unauthenticatet user.",
 *     tags={"product"},
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
     *     @OA\Response(response="200", description="The request was successfully processed."))
     * )
*/


$app->post("/authenticate", function (Request $request, Response $response, $args) {
    global $secret;

    $data = $request->getParsedBody();

    if ($data["username"] != "php-user" || $data["password"] != "Admin123Admin12") {
        $response->getBody()->write(json_encode(array(
            "error" => "Invalid credentials."
        )));
        return $response->withStatus(401);
    }

    $userId = 1;
    $expiration = time() + 3600;
    $issuer = 'localhost';

    $token = Token::create($userId, $secret, $expiration, $issuer);

    setcookie("token", $token);

    return $response;
});

/**
     * @OA\Post(
     *     path="/product",
     *     summary="Create a new product.",
     *     tags={"product"},
     *     requestBody=@OA\RequestBody(
     *         request="/product",
     *         required=true,
     *         description="Beschreiben was im Request Body enthalten sein muss",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="sku", type="string", example="SP1001"),
     *                 @OA\Property(property="active", type="integer", example="1"),
     *                 @OA\Property(property="id_category", type="integer", example="3"),
     *                 @OA\Property(property="name", type="string", example="tennis racket"),
     *                 @OA\Property(property="image", type="string", example=""),
     *                 @OA\Property(property="description", type="string", example="A tennis racket for adults"),
     *                 @OA\Property(property="price", type="decimal", example="99,00"),
     *                 @OA\Property(property="stock", type="integer", example="13")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Erklärung der Antwort mit Status 200"))
     * )
*/

$app->post("/product", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $data = $request->getParsedBody();

    //if ($data["user"]);

    //insert into db (online-shop-295)
    //mysqli_query($connection, "INSERT INTO product(sku, active, id_category, name, image, description, price, stock) VALUES('" . $data["sku"] . "', '" . $data["active"] . "', '" . $data["id_category"] . "', '" . $data["name"] . "', '" . $data["image"] . "', '" . $data["description"] . "', '" . $data["price"] . "', '" . $data["stock"] . "')");
    $statement = $connection->prepare("INSERT INTO product(sku, active, id_category, name, image, description, price, stock) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
    $statement->execute(array($data["sku"], $data["active"], $data["id_category"], $data["name"], $data["image"], $data["description"], $data["price"], $data["stock"]));

    return $response->withStatus(201);
});


/**
     * @OA\Get(
     *     path="/product/{id}",
     *     summary="Fetch information about a product with a given ID.",
     *     tags={"product"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path", 
     *         required=true,
     *         description="ID from a objekt in product",
     *         @OA\Schema(
     *             type="integer",
     *             example="1"
     *         )
     *     ),
     *    
     *     @OA\Response(response="200", description="Return product info")), //alle responses angeben, die dieser Endpoint zurückgeben kann
     *     @OA\Response(response="404", description="Product info not found")) //etc
 */

 $app->get("/product/{id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    //Read from DB
    $result = mysqli_query($connection, "SELECT * from product WHERE id = " . $args["id"]);
    if ($result === false) {
        return $response->withStatus(500);
    }
    else if ($result === true) {
        //not in case of SELECT!
    }
    else {
        $row_count = mysqli_num_rows($result);

        if ($row_count == 0) {
            return $response->withStatus(404);
        }

        $onlineShop295 = mysqli_fetch_assoc($result);

        $response->getBody()->write(json_encode($onlineShop295));
        return $response;
    }
});


$app->run();

?>