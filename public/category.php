<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use ReallySimpleJWT\Token;

require __DIR__ . "/../vendor/autoload.php";

require_once "db.php";

$secret = 'sec!ReT423*&';



$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);



/**
 * @OA\Post(
 *     path="/category",
 *     summary="Create a new category.",
 *     tags={"category"},
 *     requestBody=@OA\RequestBody(
 *         request="/category",
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields: 
 *                      active and name. 
 *                      These fields define the properties of the category to be created.",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="active", type="integer", example="1"),
 *                 @OA\Property(property="name", type="string", example="sports equipment")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="201", description="Category created successfully."),
 *     @OA\Response(response="500", description="Error while creating.")
 * )
 */

$app->post("/category", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $data = $request->getParsedBody();
    
    $statement = $connection->prepare("INSERT INTO category (active, name) VALUES (?, ?)");
    $result = $statement->execute(array(
        $data["active"], 
        $data["name"]
    ));

    if (!$result) {
        return $response->withStatus(500); //Error while creating.
    }
    return $response->withStatus(201); //The request was successfully processed and a new category record has been created.
});

/**
 * @OA\Get(
 *     path="/category",
 *     summary="Fetch information about all categories from table category.",
 *     tags={"category"},
 *     @OA\Parameter(
 *         name="category",
 *         in="path", 
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields: 
 *                      active and name. 
 *                      These fields define the properties of the category to be created.",
 *         @OA\Schema(
 *             @OA\Property(property="active", type="integer", example="1"),
 *             @OA\Property(property="name", type="string", example="bath articles")
 *         )
 *     ),  
 *     @OA\Response(response="200", description="Return category info."),
 *     @OA\Response(response="404", description="Category info not found."),
 *     @OA\Response(response="500", description="Request failed.")
 * )
 */

 $app->get("/category", function (Request $request, Response $response) {
    global $connection;
    global $secret;

    //Fetch all categories.
    $statement = $connection->prepare("SELECT * FROM category");
    $result = $statement->execute();

    if (!$result) {
        return $response->withStatus(500); //Request failed.
    }

    $result = $statement->get_result();
    $category = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($category)) {
        return $response->withStatus(404); //Category info not found.
    }

    $response->getBody()->write(json_encode($category));
    return $response->withStatus(200); //Return category info.
});

/**
 * @OA\Get(
 *     path="/category/{category_id}",
 *     summary="Fetch information about a single category with a given ID.",
 *     tags={"category"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path", 
 *         required=true,
 *         description="ID from an objekt in category.",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),  
 *     @OA\Response(response="200", description="Return category info."),
 *     @OA\Response(response="404", description="Category info not found."),
 *     @OA\Response(response="500", description="Request failed.")
 * )
 */

 $app->get("/category/{category_id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $category_id = $args["category_id"];

    //Fetch a single product.
    $statement = $connection->prepare("SELECT * FROM category WHERE category_id = ?");
    $result = $statement->execute(array($category_id));

    if (!$result) {
        return $response->withStatus(500); //Request failed.
    }

    $result = $statement->get_result();
    $category = $result->fetch_assoc();

    if (empty($category)) {
        return $response->withStatus(404); //Category info not found.
    }

    $response->getBody()->write(json_encode($category));
    return $response->withStatus(200); //Return category info.
});

/**
 * @OA\Patch(
 *     path="/category{category_id}",
 *     summary="Partially update an existing record.",
 *     tags={"category"},
 *     @OA\Parameter(
 *         name="category",
 *         in="path",
 *         required=true,
 *         description="ID from a objekt in category.",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),
 *     requestBody=@OA\RequestBody(
 *         request="/category_id",
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields:
 *                      active and name. 
 *                      These fields define the properties of the category to be created.",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="active", type="integer", example="1"),
 *                 @OA\Property(property="name", type="string", example="bath articles")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully updated."),
 *     @OA\Response(response="500", description="Error while updating.")
 * )
 */

 $app->patch("/category/{category_id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $category_id = $args["category_id"];
    $data = $request->getParsedBody();

    $statement = $connection->prepare("UPDATE category SET active = ?, name = ? WHERE category_id = ?");
    $result = $statement->execute(array( 
        $data["active"],  
        $data["name"],  
        $category_id
    ));

    if ($result) {
        return $response->withStatus(200); //Successfully updated.
    } else {
        return $response->withStatus(500); //Error while updating.
    }
});

/**
 * @OA\Delete(
 *     path="/category/{category_id}",
 *     summary="Delete a category with a given ID.",
 *     tags={"category"},
 *     @OA\Parameter(
 *         name="category",
 *         in="path",
 *         required=true,
 *         description="ID from a objekt in category.",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully deleted."),
 *     @OA\Response(response="500", description="Error while deleting.")
 * )
*/


$app->delete("/category/{category_id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $category_id = $args["category_id"];

    $statement = $connection->prepare("DELETE FROM category WHERE category_id = ?");
    $result = $statement->execute(array($category_id));

    if ($result) {
        return $response->withStatus(200); //Successfully deleted.
    } else {
        return $response->withStatus(500); //Error while deleting.
    }
});

?>