<?php
use Firebase\JWT\JWT;
    class Usuario{
        public function login($usuario, $password): array {
            $db = (new Conexion())->getDb();
        
            $query = "SELECT id FROM usuario WHERE usuario = :usuario AND password = :password";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':password', $password); 
            $stmt->execute();
        
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result && isset($result['id'])) {
                $id = $result['id'];
                $token = $this->nuevoToken($usuario, $id);
        
                if ($token) {
                    return [
                        'status' => 200,
                        'message' => [
                            'token' => $token,
                            'usuario' => $usuario,
                            'id' => $id
                        ]
                    ];
                } else {
                    return [
                        'status' => 500,
                        'message' => "El usuario existe pero no se pudo generar el token."
                    ];
                }
            }
        
            return [
                'status' => 404,
                'message' => "Usuario o contraseña incorrectos."
            ];
        }
        
        public function nuevoToken($usuario, $id): ?string {
            $clave_secreta = $_ENV['JWT_SECRET'] ?? 'contraseña_default';
        
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $ahora = time();
            $vencimiento = $ahora + 3600; // 1 hora
            $fechaVencimiento = date('Y-m-d H:i:s', $vencimiento);
        
            $payload = [
                'iss' => 'http://localhost:8000',
                'aud' => 'http://localhost:8000',
                'iat' => $ahora,
                'nbf' => $ahora,
                'exp' => $vencimiento,
                'data' => [
                    'id' => $id,
                    'usuario' => $usuario
                ]
            ];
        
            $jwt = JWT::encode($payload, $clave_secreta, 'HS256');
        
            $db = (new Conexion())->getDb();
            $query = "UPDATE usuario SET token = :token, vencimiento_token = :vencimiento WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token', $jwt);
            $stmt->bindParam(':vencimiento', $fechaVencimiento);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        
            return $jwt;
        }

        public function verificarExistenciaUsuario($usuario): bool{
            $db = (new Conexion())->getDb();

            $query = "SELECT COUNT(*) as total FROM usuario WHERE usuario = :usuario";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result['total'] > 0){
                return true;
            }
            return false;
        }

        public function verificarContraseña($password): bool{
            if(strlen($password) < 8 || !preg_match('/[A-Z]/',$password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)){
                return false;
            }
            return true;
        }

        public function validarCampos($nombre,$usuario,$password){
            if (empty($nombre) || empty($usuario) || empty($password)){
                echo "Los campos son obligatorios";
                return false;
            }
            return true;
        }

        public function verificarUsuario($usuario): bool{
            
            if(strlen($usuario) < 6 || strlen($usuario) > 20 || !ctype_alnum($usuario)){
                return false;
            }
            return true;
        }

        public function editarUsuario(int $usuarioId, string $token, ?string $nombre, ?string $password): array {
            $usuarioLogueado = $this->obtenerUsuarioPorToken($token);

            if (!$usuarioLogueado) {
                return [
                    'status' => 401,
                    'message' => "El usuario no está logueado"
                ];
            }

            if ($usuarioLogueado['id'] != $usuarioId) {
                return [
                    'status' => 403,
                    'message' => "No tiene permiso para editar a otro usuario"
                ];
            }

            if (empty($nombre) || empty($password)) {
                return [
                    'status' => 400,
                    'message' => "Nombre y contraseña son obligatorios"
                ];
            }

            if (!$this->verificarContraseña($password)) {
                return [
                    'status' => 400,
                    'message' => "La clave debe tener más de 8 caracteres y ser alfanumérica con mayúsculas, minúsculas, números y símbolos"
                ];
            }

            $db = (new Conexion())->getDb();

            $query = "UPDATE usuario SET nombre = :nombre, password = :password WHERE id = :id";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':id', $usuarioId);

            $stmt->execute();

            return [
                'status' => 200,
                'message' => "Usuario actualizado correctamente"
            ];
        }

        

        public function register($nombre,$usuario,$password): array{
            $db = (new Conexion())->getDb();

            if (!$this -> validarCampos($nombre,$usuario,$password)) {
                return [
                    'status' => 400,
                    'message' => 'Todos los campos son obligatorios.'
                ];
            }

            if (!$this->verificarUsuario($usuario)) {
                return [
                    'status' => 400,
                    'message' => 'El nombre de usuario debe tener entre 6 y 20 caracteres y ser alfanumérico.'
                ];
            }

            if(!$this->verificarContraseña($password)){
                return [
                    'status'=> 400,
                    'message'=> "La clave debe ser de mas de 8 digitos y alfanumerica"
                ];
            }

            if($this->verificarExistenciaUsuario($usuario)){
                return [
                    'status' => 400,
                    'message' => "Ese usuario ya existe"
                ];
            }

            $query = "INSERT INTO usuario (nombre,usuario, password) VALUES (:nombre, :usuario, :password)";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':password', $password);

            $stmt->execute();
            if($stmt->rowCount() > 0){
                return [
                    'status' => 200,
                    'message'=> "Se agrego el usuario"
                ];
            }
            return [
                "status"=> 404,
                "message"=> "ERROR"
            ];
        }

        public function obtenerUsuarioPorToken($token) {
            try {
                $db = (new Conexion())->getDb();
                $token = urldecode(trim($token));
                
                // Opción 1: Sin verificación de tiempo (para debug)
                $query = "SELECT * FROM usuario WHERE token = :token";
                
                // Opción 2: Con verificación de tiempo (producción)
                // $query = "SELECT * FROM usuario WHERE token = :token AND vencimiento_token > UTC_TIMESTAMP()";
                
                $stmt = $db->prepare($query);
                $stmt->bindValue(':token', $token, PDO::PARAM_STR);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error en consulta SQL");
                }
                
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                
            } catch (Exception $e) {
                error_log("Error en obtenerUsuarioPorToken: " . $e->getMessage());
                return null;
            }
        }
        

        public function getUsuario($usuario_id): string {
            $db = (new Conexion())->getDb();
            $query = "SELECT usuario FROM usuario WHERE id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
        
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result && isset($result['usuario'])) {
                return (string)$result['usuario'];
            }
        
            return "404"; 
        }

        public function obtenerInformacion($token, $usuario_id): array {
            $usr = new Usuario();
            $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);

            if (!$usuarioLogueado) {
                return [
                    'status' => 401,
                    'message' => 'El usuario no está logueado o el token expiró'
                ];
            }

            $usuario_id = (int)$usuario_id;
            $usuarioLogueado['id'] = (int)$usuarioLogueado['id'];

            if ($usuarioLogueado['id'] != $usuario_id) {
                return [
                    'status' => 403,
                    'message' => 'No tiene permiso para ver esta información'
                ];
            }

            return [
                'status' => 200,
                'message' => [
                    'usuario' => $usuarioLogueado['usuario'],
                    'nombre' => $usuarioLogueado['nombre']
                ]
            ];
        }




        public function getIdUsuario($usuario):int {
            $db = (new Conexion())->getDb();

            $query = "SELECT id FROM usuario WHERE usuario = :usuario";
            $stmt = $db->prepare($query);

            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result && isset($result['id'] )) {
                return (int)$result['id'];
            }
            return 0;

        }

        public function mazoDelUsuario($usuario_id,$mazo_id):bool{
            $db = (new Conexion())->getDb();

            $query = "SELECT * FROM mazo WHERE usuario_id = :usuario_id";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result){
                return true;
            }
            return false;
        }

    }


?>
