<?php 
require_once __DIR__ . '/Partida.php';
require_once __DIR__ .'/Conexion.php';
require_once __DIR__ .'/Usuario.php';
require_once __DIR__ .'/Mazo.php';
require_once __DIR__ .'/Estadisticas.php';
require_once __DIR__ . '/../vendor/autoload.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\CorsMiddleware;

$app = AppFactory::create();

// 1. Middleware de parsing del body (PRIMERO)
$app->addBodyParsingMiddleware();

// 2. Configuración CORS optimizada (versión actualizada)
$app->add(new CorsMiddleware([
    "origin" => ["*"],
    "methods" => ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    "headers.allow" => ["Authorization", "Content-Type"],
    "headers.expose" => ["Authorization"],
    "credentials" => true,
    "cache" => 0,
    "error" => function ($request, $response, $arguments) {
        return $response->withStatus(403);  // Faltaba el return y el cierre de la función
    }
]));

// 3. Ruta OPTIONS global para preflight
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/partida/jugadaServidor', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $mazo_id = $data['mazo_id'];
    $partida = new Partida();
    $resultado = $partida->jugadaServidor($mazo_id);

    $response->getBody()->write(json_encode([
        'status' => $resultado['status'],
        'message' => $resultado['message']
    ]));
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

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});//funciona

$app->get('/usuario/verify', function (Request $request, Response $response) {
    $headers = $request->getHeaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    
    if (!$authHeader || !isset($authHeader[0])) {
        $response->getBody()->write(json_encode([
            'valid' => false,
            'message' => 'Token no proporcionado'
        ]));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
    
    // Extraer el token del header "Bearer TOKEN"
    $token = str_replace('Bearer ', '', $authHeader[0]);
    
    $usr = new Usuario();
    $usuarioData = $usr->obtenerUsuarioPorToken($token);
    
    if ($usuarioData) {
        $response->getBody()->write(json_encode([
            'valid' => true,
            'user' => [
                'id' => (int)$usuarioData['id'],
                'usuario' => $usuarioData['usuario'],
                'nombre' => $usuarioData['nombre']
            ]
        ]));
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode([
            'valid' => false,
            'message' => 'Token inválido o expirado'
        ]));
        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }
});

$app->put('/usuarios/{usuario}', function (Request $request, Response $response, array $args) {//editar Usuario
    $usuarioId = $args['usuario'];
    $data = $request->getParsedBody();

    $nuevoNombre = $data['nombre'] ?? null;
    $nuevoPassword = $data['password'] ?? null;

    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $usr = new Usuario();
    $resultado = $usr->editarUsuario($usuarioId, $token, $nuevoNombre, $nuevoPassword);

    $response->getBody()->write(json_encode([
        'status' => $resultado['status'],
        'message' => $resultado['message']
    ]));

    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
}); //funciona

$app->post('/usuario/registro', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    error_log("Datos recibidos en /usuario/registro: " . json_encode($data));

    $nombre = $data['nombre'] ?? '';
    $usuario = $data['usuario'] ?? '';
    $password = $data['password'] ?? '';

    $usr = new Usuario();
    $result = $usr->register($nombre, $usuario, $password);

    $response->getBody()->write(string: json_encode(value: [
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});//funciona

$app->get('/usuarios/{usuario}', function (Request $request, Response $response, array $args) { // Obtener info usuario logueado
    $usuario_id = (int) $args['usuario'];
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $usr = new Usuario();
    $result = $usr->obtenerInformacion($token, $usuario_id);

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'data' => $result['message'] // o 'usuario' si querés ser más específico
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});
//funciona

$app->post('/partidas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    // Verificar que mazo_id exista y no esté vacío
    if (!isset($data['mazo_id']) || empty($data['mazo_id'])) {
        $error = [
            'status' => 400,
            'message' => 'El campo mazo_id es obligatorio.'
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $mazo_id = $data['mazo_id'];
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $partida = new Partida();
    $result = $partida->crearPartida($token, $mazo_id);

    $response->getBody()->write(json_encode($result));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});//funciona

$app->post('/jugadas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    // Validación de campos obligatorios
    if (!isset($data['carta_id']) || empty($data['carta_id']) ||
        !isset($data['partida_id']) || empty($data['partida_id'])) {

        $error = [
            'status' => 400,
            'message' => 'Los campos carta_id y partida_id son obligatorios.'
        ];
        $response->getBody()->write(json_encode($error));
        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $carta_id = $data['carta_id'];
    $partida_id = $data['partida_id'];
    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $partida = new Partida();
    $result = $partida->jugadaUsuario($carta_id, $partida_id, $token);

    if ($result['status'] !== 200) {
        $response->getBody()->write(json_encode([
            'status' => $result['status'],
            'message' => $result['message']
        ]));
    } else {
        $jsonResponse = [
            'status' => 200,
            'message' => $result['message'],
            'carta_servidor' => [
                'ataque' => $result['carta_servidor']->ataque
            ],
            'ataque_usuario' => $result['ataque_usuario'],
            'ataque_servidor' => $result['ataque_servidor']
        ];

        if (!empty($result['partida_finalizada'])) {
            $jsonResponse['partida_finalizada'] = $result['partida_finalizada'];
        }

        $response->getBody()->write(json_encode($jsonResponse));
    }

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});//funciona


$app->get('/usuarios/{usuario}/partidas/{partida}/cartas', function (Request $request, Response $response, array $args) { // Indicar Atributos
    $usuarioId = $args['usuario'];
    $partidaId = $args['partida'];

    $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));

    $partida = new Partida();
    $result = $partida->indicarAtributos($usuarioId, $partidaId, $token);

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

    return $response
        ->withStatus($result['status'])
        ->withHeader('Content-Type', 'application/json');
});//funciona

$app->get('/estadisticas/getEstadisticas', function (Request $request, Response $response) {
    $data = $request->getParsedBody();

    $estadisticas = new Estadisticas();
    $result = $estadisticas->getEstadisticas();

    $response->getBody()->write(json_encode([
        'status' => $result['status'],
        'message' => $result['message']
    ]));

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
            'status' => $resultado['status'],
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
});//Funciona perfecto, borro un mazo y me tiro excepcion en el otro



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

    $response->getBody()->write(json_encode([
        'status' => $resultado['status'],
        'message' => $resultado['message']
    ]));
    return $response
        ->withStatus($resultado['status'])
        ->withHeader('Content-Type', 'application/json');
}); //Funciona, recibe un id de mazo y edita unicamente el nombre

$app->get('/cartas', function (Request $request, Response $response) {
    $queryParams = $request->getQueryParams();

    $atributo = $queryParams['atributo'] ?? null;
    $nombre = $queryParams['nombre'] ?? null;

    $mazo = new Mazo(); 
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