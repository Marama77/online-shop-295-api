<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . "/../vendor/autoload.php";

require_once "db.php";


$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);


$app->get("/", function (Request $request, Response $response, $args) {
    //app-get erstellt einen Endpoint für eine Get-Methode
    //request kann man header auslesen etc.
    //response ist einer von 3 Parametern
    $response->getBody()->write("World not found!");
    //macht dasselbe wie echo "Hello world not found!";
    return $response->withStatus(404);
});

$app->run();

?>