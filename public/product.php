<?php
/**
 * @OA\Info(title="online-shop-295", version="1.0") 
 */

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

/**
 * @OA\Post(
 *     path="/product",
 *     summary="Create a new product.",
 *     tags={"product"},
 *     requestBody=@OA\RequestBody(
 *         request="/product",
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields: 
 *                      sku, active, id_category, name, image, description, price, and stock. 
 *                      These fields define the properties of the product to be created.",
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
 *     @OA\Response(response="201", description="Product created successfully."),
 *     @OA\Response(response="500", description="Error while creating.")
 * )
 */

$app->post("/product", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $data = $request->getParsedBody();
    
    $statement = $connection->prepare("INSERT INTO product (sku, active, id_category, name, image, description, price, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $statement->execute(array(
        $data["sku"], 
        $data["active"], 
        $data["id_category"], 
        $data["name"], 
        $data["image"], 
        $data["description"], 
        $data["price"], 
        $data["stock"]
    ));

    if (!$result) {
        return $response->withStatus(500); //Error while creating.
    }
    return $response->withStatus(201); //The request was successfully processed and a new product record has been created.
});

/**
 * @OA\Get(
 *     path="/product",
 *     summary="Fetch information about all products from table products.",
 *     tags={"product"},
 *     @OA\Parameter(
 *         name="product",
 *         in="path", 
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields: 
 *                      sku, active, id_category, name, image, description, price, and stock. 
 *                      These fields define the properties of the product to be created.",
 *         @OA\Schema(
 *             @OA\Property(property="sku", type="string", example="SP1001"),
 *             @OA\Property(property="active", type="integer", example="1"),
 *             @OA\Property(property="id_category", type="integer", example="3"),
 *             @OA\Property(property="name", type="string", example="tennis racket"),
 *             @OA\Property(property="image", type="string", example=""),
 *             @OA\Property(property="description", type="string", example="A tennis racket for adults"),
 *             @OA\Property(property="price", type="decimal", example="99,00"),
 *             @OA\Property(property="stock", type="integer", example="13")
 *         )
 *     ),  
 *     @OA\Response(response="200", description="Return product info."),
 *     @OA\Response(response="404", description="Product info not found."),
 *     @OA\Response(response="500", description="Request failed.")
 * )
 */

$app->get("/product", function (Request $request, Response $response) {
    global $connection;
    global $secret;

    //Fetch all products.
    $statement = $connection->prepare("SELECT * FROM product");
    $result = $statement->execute();

    if (!$result) {
        return $response->withStatus(500); //Request failed.
    }

    $result = $statement->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($products)) {
        return $response->withStatus(404); //Product info not found.
    }

    $response->getBody()->write(json_encode($products));
    return $response->withStatus(200); //Return product info.
});


/**
 * @OA\Get(
 *     path="/product/{product_id}",
 *     summary="Fetch information about a single product with a given ID.",
 *     tags={"product"},
 *     @OA\Parameter(
 *         name="product_id",
 *         in="path", 
 *         required=true,
 *         description="ID from an objekt in product.",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),  
 *     @OA\Response(response="200", description="Return product info."),
 *     @OA\Response(response="404", description="Product info not found."),
 *     @OA\Response(response="500", description="Request failed.")
 * )
 */

$app->get("/product/{product_id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $product_id = $args["product_id"];

    //Fetch a single product.
    $statement = $connection->prepare("SELECT * FROM product WHERE product_id = ?");
    $result = $statement->execute(array($product_id));

    if (!$result) {
        return $response->withStatus(500); //Request failed.
    }

    $result = $statement->get_result();
    $product = $result->fetch_assoc();

    if (empty($product)) {
        return $response->withStatus(404); //Product info not found.
    }

    $response->getBody()->write(json_encode($product));
    return $response->withStatus(200); //Return product info.
});

/**
 * @OA\Patch(
 *     path="/product{product_id}",
 *     summary="Partially update an existing record.",
 *     tags={"product"},
 *     @OA\Parameter(
 *         name="product",
 *         in="path",
 *         required=true,
 *         description="ID from a objekt in product.",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),
 *     requestBody=@OA\RequestBody(
 *         request="/product_id",
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields:
 *                      sku, active, id_category, name, image, description, price, and stock. 
 *                      These fields define the properties of the product to be created.",
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
 *     @OA\Response(response="200", description="Successfully updated."),
 *     @OA\Response(response="500", description="Error while updating.")
 * )
 */

$app->patch("/product/{product_id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $product_id = $args["product_id"];
    $data = $request->getParsedBody();

    $statement = $connection->prepare("UPDATE product SET sku = ?, active = ?, id_category = ?, name = ?, image = ?, description = ?, price = ?, stock = ? WHERE product_id = ?");
    $result = $statement->execute(array(
        $data["sku"], 
        $data["active"], 
        $data["id_category"], 
        $data["name"], 
        $data["image"], 
        $data["description"], 
        $data["price"], 
        $data["stock"], 
        $product_id
    ));

    if ($result) {
        return $response->withStatus(200); //Successfully updated.
    } else {
        return $response->withStatus(500); //Error while updating.
    }
});

/**
 * @OA\Delete(
 *     path="/product/{product_id}",
 *     summary="Delete a product with a given ID.",
 *     tags={"product"},
 *     @OA\Parameter(
 *         name="product",
 *         in="path",
 *         required=true,
 *         description="ID from a objekt in product.",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully deleted."),
 *     @OA\Response(response="500", description="Error while deleting.")
 * )
*/


$app->delete("/product/{product_id}", function (Request $request, Response $response, $args) {
    global $connection;
    global $secret;

    $product_id = $args["product_id"];

    $statement = $connection->prepare("DELETE FROM product WHERE product_id = ?");
    $result = $statement->execute(array($product_id));

    if ($result) {
        return $response->withStatus(200); //Successfully deleted.
    } else {
        return $response->withStatus(500); //Error while deleting.
    }
});


$app->run();
?>