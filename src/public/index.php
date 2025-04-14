<?php 
require_once __DIR__ . '/Partida.php';
require_once __DIR__ .'/Conexion.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); 

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/partida/jugadaServidor', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $mazo_id = $data['mazo_id'];
    $partida = new Partida();
    $resultado = $partida->jugadaServidor($mazo_id);
    $response->getBody()->write(json_encode(['carta_id' => $resultado]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/partida/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';
    $partida = new Partida();
    $result = $partida->login( $usuario, $password);

    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->put('/partida/editarUsuario', function (Request $request, Response $response) {

    $data = $request->getParsedBody();
    $nuevoNombre = $data['nombre'] ?? null;
    $nuevoPassword = $data['password'] ?? null;
    $usuario = $data['usuario'] ?? null;

    $partida = new Partida();
    $resultado = $partida->editarUsuario($usuario, $nuevoNombre, $nuevoPassword);

    $response->getBody()->write(json_encode(['mensaje' => $resultado['message']]));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
});


$app->get('/partida/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $nombre = $data['nombre'] ?? '';
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';

    $partida = new Partida();
    $result = $partida -> register($nombre, $usuario, $password);

    $response -> getBody() ->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/partida/obtenerInformacion', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $usuario = $data['usuario'] ?? '';

    $partida = new Partida();

    $result = $partida -> obtenerInformacion($usuario);
    $response ->getBody() ->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});



$app->run();

?>