<?php
/**
 * @OA\Info(title="online-shop-295", version="1.0") 
 */

/**
 * @OA\Post(
 *     path="/category",
 *     summary="Create a new category.",
 *     tags={"category"},
 *     requestBody=@OA\RequestBody(
 *         request="/category",
 *         required=true,
 *         description="The request body must contain a JSON object with the following fields: 
 *                      sku, active, id_category, name, image, description, price, and stock. 
 *                      These fields define the properties of the product to be created.",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="active", type="integer", example="1"),
 *                 @OA\Property(property="name", type="string", example="sports equipment"),
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

$app->run();

?>