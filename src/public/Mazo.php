<?php
    class Mazo{

        public function getCartasMazo(int $mazo_id): array {
            try {
                $db = (new Conexion())->getDb();
                $query = "SELECT carta_id, estado FROM mazo_carta WHERE mazo_id = :mazo_id";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':mazo_id', $mazo_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                
                return $result ?: [];
            } catch (PDOException $e) {
                error_log("Error en getCartasMazo: " . $e->getMessage());
                return [];
            }
        }

        public function cartaExisteEnMazo($mazo_id, $carta_id): bool {
            $cartas = $this->getCartasMazo($mazo_id);
        
            foreach ($cartas as $carta) {
                if ($carta->carta_id === $carta_id) {
                    return true;
                }
            }
        
            return false;
        }
        
        public function cartaFueUsada($mazo_id, $carta_id): bool {
            $cartas = $this->getCartasMazo($mazo_id);
        
            foreach ($cartas as $carta) {
                if ($carta->carta_id === $carta_id && $carta->estado === 'descartado') {
                    return true;
                }
            }
        
            return false;
        }

        public function actualizarEstadoCarta($carta_id, $mazo_id,$nuevo_estado):bool{
            $db = (new Conexion())->getDb();

            //hago la consulta
            $query ="UPDATE mazo_carta SET estado =: nuevo_estado WHERE mazo_id = :mazo_id AND carta_id = :carta_id";

            //preparo la consulta
            $stmt = $db->prepare($query);

            //asocio los valores 
            $stmt->bindParam(':nuevo_estado', $nuevo_estado);
            $stmt->bindParam(':mazo_id', $mazo_id);
            $stmt->bindParam(':carta_id', $carta_id);

            //ejecuto la consulta
            $stmt->execute();

            //verifico si se actualizo correctamente
            if($stmt->rowCount() > 0){
                $db = null;
                $stmt = null;
                return true;
                }else{
                    $db = null;
                    $stmt = null;
                    return false;
                }
        }

        public function ultimaRonda($mazo_id):bool{
            $cartas = $this->getCartasMazo($mazo_id);

            foreach ($cartas as $carta) {
                if ($carta['estado'] == "en_mazo"){
                    return false;
                }
            }
            return true;
        }
        public function tiene3Mazos($usuario_id): bool {
            $db = (new Conexion())->getDb();
            $query = "SELECT COUNT(*) as total FROM mazo WHERE usuario_id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
            
            return $resultado && $resultado['total'] >= 3;
        }
        
        

        public function darAltaMazo($usuario,$nombreMazo,$cartas): array{
            $user = new Usuario();
            if(!$user->estaLogueado($usuario)){
                return [
                    "status"=> "404",
                    "message"=> "No esta Logueado",
                ];
            }

            $usuarioId = $user->getIdUsuario($usuario);

            if($this->tiene3Mazos($usuarioId)){
                return [
                    "status"=> "404",
                    "message"=> "El usuario ya tiene 3 mazos creados"  
                ];
            }

            // Validar cantidad de cartas y que no haya repetidas
            if (count($cartas) > 5 || count(array_unique($cartas)) < count($cartas)) {
                return [
                    "status" => "400",
                    "message" => "Debes enviar hasta 5 cartas y no deben repetirse",
                ];
            }

            $db = (new Conexion()) -> getDb();

            // Validar que todas las cartas existan
            $in = str_repeat('?,', count($cartas) - 1) . '?';
            //Genera una lista de signos de interrogación (?, ?, ?, ...) para usar en el IN de la consulta SQL
            // Esto se hace en base a la cantidad de cartas recibidas, para que sea dinámico y seguro
            $stmt = $db->prepare("SELECT id FROM carta WHERE id IN ($in)");
            $stmt->execute($cartas);
            $cartasExistentes = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($cartasExistentes) != count($cartas)) {
                return [
                    "status" => "400",
                    "message" => "Alguna de las cartas no existe",
                ];
            }
            
            // Insertar el mazo
            $stmt = $db->prepare("INSERT INTO mazo (usuario_id, nombre) VALUES (:usuario_id, :nombre)");
            $stmt->bindParam(":usuario_id", $usuarioId);
            $stmt->bindParam(":nombre", $nombreMazo);
            $stmt->execute();

            $mazoId = $db->lastInsertId();// se ejecuta y luego se inserta el id, para guardar el id del nuevo mazo

            // Insertar en mazo_carta
            $stmt = $db->prepare("INSERT INTO mazo_carta (carta_id, mazo_id, estado) VALUES (:carta_id, :mazo_id, 'en_mazo')");
            foreach ($cartas as $cartaId) {
                $stmt->bindValue(":carta_id", $cartaId);
                $stmt->bindValue(":mazo_id", $mazoId);
                $stmt->execute();
            }

            return [
                "status" => "200",
                "message" => "Mazo creado con éxito",
                "mazo_id" => $mazoId,
                "nombre" => $nombreMazo
            ];
        }
        public function BajaMazo($mazo_id,$usuario) {
            $user = new Usuario();
            if($user-> estaLogueado($usuario)){
                try{
                    $db = (new Conexion())->getDb();

                    // Verificar si el mazo participó en una partida
                    $stmt = $db->prepare("SELECT COUNT(*) FROM partida WHERE mazo_id = :mazo_id");
                    $stmt->bindParam(':mazo_id', $mazo_id);
                    $stmt->execute();
                    $cantidad = $stmt->fetchColumn();

                    if ($cantidad > 0) {
                        throw new Exception("Este mazo ya participó de una partida y no puede borrarse.");
                    }

                    // Borrar primero de mazo_carta
                    $stmt = $db->prepare("DELETE FROM mazo_carta WHERE mazo_id = :mazo_id");
                    $stmt->bindParam(':mazo_id', $mazo_id);
                    $stmt->execute();

                    // Borrar el mazo
                    $stmt = $db->prepare("DELETE FROM mazo WHERE id = :mazo_id");
                    $stmt->bindParam(':mazo_id', $mazo_id);
                    $stmt->execute();

                    echo "Mazo borrado correctamente.";
                }
                catch(Exception $e){
                    echo'Error: '.$e->getMessage();
                }
            } else {
                echo "El usuario no esta logueado o el token expiró";
            }
        }

        public function devolverMazo($usuario): array{
            $usuarioModel = new Usuario(); 
            if ($usuarioModel->estaLogueado($usuario)){
                $db = (new Conexion())->getDb();

                $query = "SELECT * FROM mazo WHERE usuario_id = :usuario_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":usuario_id", $usuario);
                $stmt->execute();

                $mazos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                //echo $mazos;
                return $mazos ?? [];
            } else {
                echo "El usuario no esta logueado o el token expiro";
                return [];
            }
        }

        public function editarMazo($usuario, $nombre, $id_mazo): array{
            $usuarioModel = new Usuario();
            if ($usuarioModel->estaLogueado($usuario)){
                $db = (new Conexion())->getDb();

                $query = "UPDATE mazo SET nombre = :nombre WHERE usuario = :usuario AND id_mazo = :id_mazo";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":usuario", $usuario);
                $stmt->bindParam(":id_mazo", $id_mazo);
                $stmt->bindParam(":nombre", $nombre);
                $stmt->execute();

                return [
                    'status' => 200,
                    'message' => "Mazo actualizado correctamente"
                ];

            } else {
                return [
                    'status' => 401,
                    'message' => "El usuario no está logueado o el token expiró"
                ];
            }
        }

    }
?>