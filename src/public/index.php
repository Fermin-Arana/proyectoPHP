<?php 
require_once __DIR__ . '/Partida.php';
require_once __DIR__ .'/Conexion.php';
require_once __DIR__ .'/Mazo.php';
require_once __DIR__ .'/Usuario.php';
require_once __DIR__ .'/Carta.php';
require_once __DIR__ .'/Estadisticas.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware(); 

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/jugada/jugadaServidor', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $mazo_id = $data['mazo_id'];
    $partida = new Partida();
    $resultado = $partida->jugadaServidor($mazo_id);
    $response->getBody()->write(json_encode(['carta_id' => $resultado]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/Usuario/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    if (!$data) {
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                         ->write(json_encode(['status' => 400, 'message' => 'No se recibió JSON válido']));
    }

    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';
    $usuarioModelo = new Usuario();
    $result = $usuarioModelo->login( $usuario, $password);

    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->put('/usuario/editarUsuario/{usuario}', function (Request $request, Response $response, array $args) {
    $usuario = $args['usuario'];

    $data = $request->getParsedBody();
    $nuevoNombre = $data['nombre'] ?? null;
    $nuevoPassword = $data['password'] ?? null;

    $partida = new Partida();
    $resultado = $partida->editarUsuario($usuario, $nuevoNombre, $nuevoPassword);

    $response->getBody()->write(json_encode(['mensaje' => $resultado['message']]));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
});


$app->post('/Usuario/register', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $nombre = $data['nombre'] ?? '';
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';

    $partida = new Usuario();
    $result = $partida -> register($nombre, $usuario, $password);

    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');

});

$app->get('/usuario/obtenerInformacion', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $usuario = $data['usuario'] ?? '';

    $partida = new Partida();

    $result = $partida -> obtenerInformacion($usuario);
    $response ->getBody() ->write(json_encode($result));
    return $response->withHeader('', 'application/json');
});

$app->post('/partida/crearPartida', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $usuario = $data['usuario'];
    $mazo_id = $data['mazo_id'];

    $partida = new Partida();

    $result = $partida -> crearPartida( $usuario, $mazo_id );
    $response -> getBody() ->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
}); // falta probar con la parte de mazo

$app->post('/partida/jugadaUsuario', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $carta_id = $data ['carta_id'];
    $partida_id = $data['partida_id'];

    $partida = new Partida();

    $result = $partida -> jugadaUsuario($carta_id, $partida_id);
    $response ->getBody() ->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');

}); //falta probar con la parte de mazo

$app->post('/partida/indicarAtributos', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $mazo_id = $data ['mazo_id'];

    $partida = new Partida();

    $result = $partida -> indicarAtributos($mazo_id);
    $response ->getBody() ->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');

});

$app->get('/estadisticas/getEstadisticas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $estadisticas = new Estadisticas();

    $result = $estadisticas -> getEstadisticas();
    $response ->getBody() ->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');

});

$app->post('/Mazo/AltaMazo', function (Request $request, Response $response) {
    $datos = $request->getParsedBody();
    
    $usuario = $datos['usuario'] ?? null;
    $nombreMazo = $datos['nombreMazo'] ?? null;
    $cartas = $datos['cartas'] ?? [];

    if (empty($usuario) || empty($nombreMazo) || empty($cartas)) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Faltan datos: usuario, nombre del Mazo o Cartas'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $mazo = new Mazo();
    $resultado = $mazo->darAltaMazo($usuario, $nombreMazo, $cartas);

    $response->getBody()->write(json_encode($resultado));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->delete('/Mazo/BajaMazo', function (Request $request, Response $response) {
    $datos = $request->getParsedBody();

    $usuario = $datos['usuario'] ?? null;
    $id_mazo = $datos['id_mazo'] ?? null;

    $mazo = new Mazo();
    $resultado = $mazo->BajaMazo($id_mazo, $usuario);

    $response->getBody()->write(json_encode($resultado));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/Mazo/devolverMazo/{usuario}', function (Request $request, Response $response, array $args) {
    // Obtener el parámetro de la URL
    $usuarioNombre = $args['usuario'] ?? '';

    $mazo = new Mazo();
    $result = $mazo->devolverMazo($usuarioNombre);

    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->put('/Mazo/editarMazo/{mazo}', function (Request $request, Response $response, array $args) {
    $id_mazo = $args['mazo'] ?? '';

    $data = $request->getParsedBody();

    $usuario = $data['usuario'] ?? '';
    $nombre = $data['nombre'] ?? '';

    if (!$usuario || !$nombre || !$id_mazo) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Faltan datos: usuario, nombre o id_mazo'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $mazo = new Mazo();
    $result = $mazo->editarMazo($usuario,$nombre, $id_mazo);

    $statusCode = $result['status'] ?? 200;

    $response->getBody()->write(json_encode($result));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($statusCode);
});

$app->get('/Carta/listarCartas', function (Request $request, Response $response, array $args) {

    $queryAtributos = $request->getQueryParams();

    $nombre = $queryAtributos['nombre'] ??'';
    $atributo = $queryAtributos['atributo'] ??'';

    if (!$atributo || !$nombre) {
        $response->getBody()->write(json_encode([
            'status' => 400,
            'message' => 'Faltan datos: nombre o atributo'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $carta = new Carta();
    $result = $carta->listarCartas($atributo, $nombre);

    $statusCode = $result['status'] ?? 200;

    $response->getBody()->write(json_encode($result));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($statusCode);
});
$app->run();

?>