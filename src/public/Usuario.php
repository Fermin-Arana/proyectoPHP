<?php
    class Usuario{
        public function login($usuario,$password): array{
            $db =(new Conexion()) -> getDb();

            $query = "SELECT id FROM usuario WHERE usuario = :usuario AND password = :password";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':password', $password);
            

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);        
            
            if($result && isset($result['id'])) {
                $id = $result['id'];
                $token = $this->nuevoToken($usuario, $id);
                if ($token) {
                    return[
                    'status' => 200,
                    'token' => $token
                    ];
                } else {
                    return[
                        'status'=> 404,
                        'message'=> "el usuario existe pero no se pudo generar el token"
                    ];
                }
            }
            return[
                'status'=> 404,
                'message'=> "No se pudo generar el token o el usuario no existe."
            ];
        }

        public function nuevoToken($usuario, $id): ?string {
            error_log("Entrando a nuevoToken() con usuario: $usuario y id: $id");
            $db = (new Conexion())->getDb();

                do {
                    $token = bin2hex(random_bytes(64));
        
                    //Verificamos si ya está en uso
                    $query = "SELECT COUNT(*) as total FROM usuario WHERE token = :token";
                    //Preparar consulta
                    $stmt = $db->prepare($query);
                    //Asociar valores
                    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                    //ejecutar
                    $stmt->execute();
                    var_dump($stmt->rowCount());
                    //Contamos las filas
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                } while ($result['total'] > 0);  //lo repite mientras el token no sea unico
                date_default_timezone_set('America/Argentina/Buenos_Aires');
                $vencimiento = date('Y-m-d H:i:s', strtotime('+1 hour')); //vencimiento en 1 hora
        
                // Guardamos el token
                $update = "UPDATE usuario SET token = :token, vencimiento_token = :vencimiento WHERE id = :id AND usuario = :usuario";
                $stmt = $db->prepare($update);
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':usuario', $usuario);
                $stmt->bindParam(':vencimiento', $vencimiento);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
        
                return $token;
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

        public function editarUsuario($usuario, $nombre, $password): array {
            if (!$this->estaLogueado($usuario)) {
                return [
                    'status'=> 401,
                    'message'=> "El usuario no está logueado"
                ];
            }
        
            if (empty($nombre) || empty($password)) {
                return [
                    'status'=> 400,
                    'message'=> "Nombre y contraseña son obligatorios"
                ];
            }
        
            if (!$this->verificarContraseña($password)) {
                return [
                    'status'=> 400,
                    'message'=> "La clave debe tener más de 8 caracteres y ser alfanumérica con mayúsculas, minúsculas, números y símbolos"
                ];
            }
        
            $db = (new Conexion())->getDb();
        
            $query = "UPDATE usuario SET nombre = :nombre, password = :password WHERE usuario = :usuario";
        
            $stmt = $db->prepare($query);
        
            //$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':usuario', $usuario);
        
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

        public function estaLogueado($usuario): bool{
            $db = (new Conexion())->getDb();

            $query = "SELECT token, vencimiento_token FROM usuario WHERE usuario = :usuario";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':usuario', $usuario);

            $stmt->execute();
        
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result) {
                $token = $result['token'];
                $vencimiento = $result['vencimiento_token'];
                $ahora = date('Y-m-d H:i:s');
        
                // Verificar si el token existe y no ha expirado
                if (!empty($token) && $vencimiento > $ahora) {
                    return true;
                }
            }
            return false;
        }

        public function getUsuario($usuario_id):string{
            $db = (new Conexion())->getDb();
            $query = "SELECT usuario FROM usuario WHERE id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (string) $result['usuario'];
        }

        public function obtenerInformacion($usuario): array{
            $db = (new Conexion())->getDb();

            if(!$this ->estaLogueado($usuario)){
                return [
                    'status' => 404,
                    'message'=> 'No esta logueado'
                ];
            }

            $query = "SELECT * FROM usuario WHERE usuario = :usuario";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario', $usuario);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if($result){
                return [
                    'status'=> 200,
                    'data' => [
                        'usuario' => $result['usuario'],
                        'nombre' => $result['nombre']
                    ]
                ];
            }
            return [
                'status'=> 404,
                'message'=> 'ERROR'
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

            $query = "SELECT * FROM mazo WHERE usuario_id = ':usuario_id'";

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