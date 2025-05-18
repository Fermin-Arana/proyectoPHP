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

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->load();

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
}); //funciona

$app->post('/usuario/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';

    $usr = new Usuario();
    $result = $usr->login($usuario, $password);

    $response->getBody()->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funciona

$app->put('/usuario/editarUsuario', function (Request $request, Response $response) {
    $nuevoNombre = $data['nombre'] ?? null;
    $nuevoPassword = $data['password'] ?? null;
    
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $usr= new Usuario();
    $resultado = $usr->editarUsuario($token, $nuevoNombre, $nuevoPassword);

    $response->getBody()->write(json_encode([$resultado['message']]));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funciona


$app->post('/usuario/register', function (Request $request, Response $response) {
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
}); //funciona

$app->get('/usuario/obtenerInformacion', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $usr = new Usuario();

    $result = $usr -> obtenerInformacion($token);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funciona

$app->post('/partida/crearPartida', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $mazo_id = $data['mazo_id'];

    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $partida = new Partida();

    $result = $partida -> crearPartida( $token, $mazo_id );
    $response -> getBody() ->write(json_encode($result['id']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funciona

$app->post('/partida/jugadaUsuario', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $carta_id = $data['carta_id'];
    $partida_id = $data['partida_id'];

    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $partida = new Partida();

    $result = $partida -> jugadaUsuario($carta_id, $partida_id, $token);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funciona


$app->post('/partida/indicarAtributos', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $mazo_id = $data ['mazo_id'];

    $partida = new Partida();

    $result = $partida -> indicarAtributos($mazo_id);
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funciona

$app->get('/estadisticas/getEstadisticas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $estadisticas = new Estadisticas();

    $result = $estadisticas -> getEstadisticas();
    $response ->getBody() ->write(json_encode($result['message']));
    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');

}); //funciona

$app->post('/mazos', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    // validacion de los datos en el body
    if (
        !isset($data['cartas']) || 
        !is_array($data['cartas']) || 
        count($data['cartas']) === 0 ||
        empty($data['nombre'])
    ) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Faltan datos requeridos: debes enviar nombre del mazo y un array de 5 cartas.'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $cartas = $data['cartas'];
    $nombreMazo = $data['nombre'];

    $mazo = new Mazo();
    $resultado = $mazo->crearMazo($token, $cartas, $nombreMazo);

    if ($resultado['status'] !== 200) {
        $response->getBody()->write(json_encode([
            'status' => $resultado['status'],
            'message' => $resultado['message']
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($resultado['status']);
    }

    $response->getBody()->write(json_encode([
        'message' => $resultado['message'],
        'data' => $resultado['data']
    ]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($resultado['status']);
});//funciona


$app->delete('/mazos/{mazo}', function (Request $request, Response $response, array $args) {
    $mazo_id = $args['mazo'] ?? null;
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    if (!$mazo_id || !$token) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Faltan datos: ID de mazo o token de autorización'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $mazo = new Mazo();
        $resultado = $mazo->borrarMazo($mazo_id, $token);

        $response->getBody()->write(json_encode([
            'message' => $resultado['message']
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($resultado['status']);

    } catch (Exception $e) {
        $response->getBody()->write(json_encode([
            'status' => 409,
            'message' => $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
    }
}); //Funciona perfecto, borro un mazo y me tiro excepcion en el otro



$app->get('/usuarios/{usuario}/mazos', function (Request $request, Response $response, array $args) {
    $usuarioId = (int) $args['usuario'];
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $mazo = new Mazo();
    $resultado = $mazo->listarMazosUsuario($token, $usuarioId);

    $response->getBody()->write(json_encode([
        'status' => $resultado['status'],
        'data' => $resultado['message']
    ]));

    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
});//corregido


$app->put('/mazos/{mazo}', function (Request $request, Response $response, array $args) {
    $id_mazo = $args['mazo'] ?? '';

    $data = $request->getParsedBody();

    $nombre = $data['nombre'] ?? '';

    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    if (!$nombre || !$id_mazo) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Faltan datos: nombre o id_mazo'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $mazo = new Mazo();
    $resultado = $mazo->editarMazo($token,$nombre, $id_mazo);

    $response->getBody()->write(json_encode([$resultado['message']]));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
}); //Funciona, recibe un id de mazo y edita unicamente el nombre

$app->get('/cartas', function (Request $request, Response $response) {
    $queryParams = $request->getQueryParams();

    $atributo = $queryParams['atributo'] ?? null;
    $nombre = $queryParams['nombre'] ?? null;

    $mazo = new Mazo(); // o Carta si tenés una clase separada
    $resultado = $mazo->listarCartas($atributo, $nombre);

    $response->getBody()->write(json_encode([
        'status' => 200,
        'cartas' => $resultado
    ]));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});//corregido





$app->run();

?>
