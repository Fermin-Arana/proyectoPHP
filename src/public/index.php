<?php 
require_once __DIR__ . '/Partida.php';
require_once __DIR__ .'/Conexion.php';
require_once __DIR__ .'/Usuario.php';
require_once __DIR__ .'/Mazo.php';
require_once __DIR__ .'/Estadisticas.php';
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
    $response->getBody()->write(json_encode($resultado ['message']));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funca

$app->post('/usuario/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';
    $usr = new Usuario();
    $result = $usr->login( $usuario, $password);

    $response->getBody()->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funca

$app->put('/usuario/editarUsuario', function (Request $request, Response $response) {

    $data = $request->getParsedBody();
    $nuevoNombre = $data['nombre'] ?? null;
    $nuevoPassword = $data['password'] ?? null;
    $usuario = $data['usuario'] ?? null;

    $usr= new Usuario();
    $resultado = $usr->editarUsuario($usuario, $nuevoNombre, $nuevoPassword);

    $response->getBody()->write(json_encode(['mensaje' => $resultado['message']]));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funca


$app->get('/usuario/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $nombre = $data['nombre'] ?? '';
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';

    $usr = new Usuario();
    $result = $usr -> register($nombre, $usuario, $password);

    $response -> getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funca

$app->get('/usuario/obtenerInformacion', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $usuario = $data['usuario'] ?? '';

    $usr = new Usuario();

    $result = $usr -> obtenerInformacion($usuario);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funca

$app->post('/partida/crearPartida', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $usuario = $data['usuario'];
    $mazo_id = $data['mazo_id'];

    $partida = new Partida();

    $result = $partida -> crearPartida( $usuario, $mazo_id );
    $response -> getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funca

$app->post('/partida/jugadaUsuario', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $carta_id = $data ['carta_id'];
    $partida_id = $data['partida_id'];

    $partida = new Partida();

    $result = $partida -> jugadaUsuario($carta_id, $partida_id);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funca

$app->post('/partida/jugarPartida', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $partida_id = $data['partida_id'];

    $partida = new Partida();

    $result = $partida -> jugarPartida($partida_id);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funca, es para probar si funcionaba la partida completa

$app->post('/partida/indicarAtributos', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $mazo_id = $data ['mazo_id'];

    $partida = new Partida();

    $result = $partida -> indicarAtributos($mazo_id);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funca

$app->get('/estadisticas/getEstadisticas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $estadisticas = new Estadisticas();

    $result = $estadisticas -> getEstadisticas();
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funca





$app->run();

?>