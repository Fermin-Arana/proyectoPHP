
<?php
    class Partida{
        public function jugadaServidor(){
            $db = (new Conexion())->getDb();
            $mazo =(new Mazo());
            $cartas_servidor = $mazo->getCartasMazo(1);
            foreach($cartas_servidor as $carta){
                if(!$mazo ->cartaFueUsada(1,$carta -> carta_id)){
                    return [
                        'status' => 200,
                        'message' => $carta -> carta_id
                    ];
                }
            }
            return [
                'status' => 404,
                'message' => "Mazo incompleto"
                ];
        }

        public function crearPartida($token, $mazo_id): array{
            $usr = new Usuario();
            $mazo = new Mazo();
            $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);

            if (!$usuarioLogueado) {
                return [
                    'status' => 401,
                    'message' => 'El usuario no está logueado'
                ];
            }

            if ($usuarioLogueado['id'] == 0) {
                return [
                    'status' => 404,
                    'message' => 'No se encontró el id del usuario'
                ];
            }

            if (!$usr->mazoDelUsuario($usuarioLogueado['id'], $mazo_id)) {
                return [
                    'status' => 404,
                    'message' => 'El mazo no es del usuario'
                ];
            }

            $mazo_usuario = $mazo->getCartasMazo($mazo_id);
            $mazo_servidor = $mazo->getCartasMazo(1);

            if (!$mazo_usuario) {
                return [
                    'status' => 404,
                    'message' => 'El mazo está vacío'
                ];
            }

            // valido que no haya una partida activa previamente
            $db = (new Conexion())->getDb();
            $query = "SELECT * FROM partida WHERE usuario_id = :usuario_id AND estado = 'en_curso'";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioLogueado['id']);
            $stmt->execute();
            $partidaEnCurso = $stmt->fetch();

            if ($partidaEnCurso) {
                return [
                    'status' => 401,
                    'message' => 'Ya existe una partida en curso para este usuario'
                ];
            }

            // Actualizo estado de las cartas
            foreach ($mazo_usuario as $carta) {
                $id = $carta->carta_id;
                $mazo->actualizarEstadoCarta($id, $mazo_id, "en_mano");
            }

            foreach ($mazo_servidor as $carta_servidor) {
                $id_servidor = $carta_servidor->carta_id;
                $mazo->actualizarEstadoCarta($id_servidor, 1, "en_mano");
            }

            // Creo la partida
            $query = "INSERT INTO partida (usuario_id, fecha, mazo_id, estado) VALUES (:usuario_id, :fecha, :mazo_id, :estado)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioLogueado['id']);
            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindParam(':mazo_id', $mazo_id);
            $stmt->bindValue(':estado', "en_curso");
            $stmt->execute();

            $id_partida = $db->lastInsertId();

            // respuesta final si todo sale bien
            return [
                'status' => 200,
                'message' => "Partida creada exitosamente",
                'id' => $id_partida,
                'MAZO' => $mazo_usuario
            ];
        }


        private function getIdMazoActual($id_partida): int {
            $db = (new Conexion)->getDb();
        
            $query = "SELECT mazo_id FROM partida WHERE id = :id_partida";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':id_partida', $id_partida);
            $stmt->execute();
        
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result && isset($result["mazo_id"])) {
                return (int)$result["mazo_id"];
            }
        
            return 0; 
        }
        private function ganaA($atributo1,$atributo2):bool{
            $db = (new Conexion)->getDb();

            $query = "SELECT atributo_id2 FROM gana_a WHERE atributo_id = :atributo1 AND atributo_id2 = :atributo2";
            
            $stmt = $db->prepare($query);

            $stmt->bindValue(':atributo1', $atributo1);
            $stmt->bindValue(':atributo2', $atributo2);

            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($result){
                return true;
            }

            return false;

        }

        private function getDatosCarta($carta_id){
            $db = (new Conexion)->getDb();
            
            $query = "SELECT ataque, atributo_id FROM carta WHERE id = :carta_id";

            $stmt = $db->prepare($query);

            $stmt->bindValue(':carta_id', $carta_id);

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_OBJ);

            return $result;
        }

        private function getIdUsuario($id_partida): int {
            $db = (new Conexion)->getDb();
            $query = "SELECT usuario_id FROM partida WHERE id = :id_partida";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':id_partida', $id_partida);
            $stmt->execute();
        
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result && isset($result['usuario_id'])) {
                return (int)$result['usuario_id'];
            }
        
            return 0; // o podés lanzar una excepción si preferís
        }
        public function jugadaUsuario($carta_id, $id_partida, $token) {
            $mazo = new Mazo();
            $usr = new Usuario();
            $db = (new Conexion())->getDb();

            $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);
            if (!$usuarioLogueado) {
                return ['status' => 401, 'message' => 'El usuario no está logueado'];
            }

            $mazo_id = $this->getIdMazoActual($id_partida);

            // Validar que la partida esté en curso
            $stmt = $db->prepare("SELECT estado FROM partida WHERE id = :id");
            $stmt->bindParam(":id", $id_partida);
            $stmt->execute();
            $estado = $stmt->fetchColumn();

            if ($estado !== 'en_curso') {
                return ['status' => 403, 'message' => 'La partida no está activa'];
            }

            // Validar que la carta pertenezca al mazo y no esté descartada
            if (!$mazo->cartaExisteEnMazo($mazo_id, $carta_id)) {
                return ['status' => 404, 'message' => 'La carta no pertenece al mazo del jugador'];
            }
            if ($mazo->cartaFueUsada($mazo_id, $carta_id)) {
                return ['status' => 409, 'message' => 'La carta ya fue utilizada'];
            }

            // Jugada del servidor
            $id_carta_servidor = $this->jugadaServidor();
            $cartaUsuario = $this->getDatosCarta($carta_id);
            $cartaServidor = $this->getDatosCarta($id_carta_servidor['message']);

            // Aplicar bonificación
            if ($this->ganaA($cartaUsuario->atributo_id, $cartaServidor->atributo_id)) {
                $cartaUsuario->ataque *= 1.30;
            } elseif ($this->ganaA($cartaServidor->atributo_id, $cartaUsuario->atributo_id)) {
                $cartaServidor->ataque *= 1.30;
            }

            // Determinar resultado
            if ($cartaUsuario->ataque > $cartaServidor->ataque) {
                $resultado_carta = "gano";
            } elseif ($cartaUsuario->ataque < $cartaServidor->ataque) {
                $resultado_carta = "perdio";
            } else {
                $resultado_carta = "empato";
            }

            // Actualizar estado de cartas
            $mazo->actualizarEstadoCarta($carta_id, $mazo_id, "descartado");
            $mazo->actualizarEstadoCarta($id_carta_servidor['message'], 1, "descartado");

            // Registrar jugada
            $query = "INSERT INTO jugada (partida_id, carta_id_a, carta_id_b, el_usuario) 
                    VALUES (:partida_id, :carta_id_a, :carta_id_b, :el_usuario)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':partida_id', $id_partida);
            $stmt->bindParam(':carta_id_a', $carta_id);
            $stmt->bindParam(':carta_id_b', $id_carta_servidor['message']);
            $stmt->bindParam(':el_usuario', $resultado_carta);
            $stmt->execute();

            // Si es la 5ta jugada, finalizar partida
            $mensaje_final = null;
            if ($mazo->ultimaRonda($mazo_id)) {
                // Finalizar partida
                $resultado_partida = $this->resultadoDeLaPartida($id_partida);
                $query = "UPDATE partida SET estado = 'finalizada', el_usuario = :el_usuario WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':el_usuario', $resultado_partida);
                $stmt->bindParam(':id', $id_partida);
                $stmt->execute();

                // Resetear cartas
                $query = "UPDATE mazo_carta SET estado = 'en_mazo' WHERE mazo_id IN (:mazo_usuario, 1)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':mazo_usuario', $mazo_id);
                $stmt->execute();

                $mensaje_final = "Partida finalizada. Resultado: $resultado_partida";
            }

            return [
                'status' => 200,
                'message' => $resultado_carta,
                'carta_servidor' => $cartaServidor,
                'ataque_usuario' => $cartaUsuario->ataque,
                'ataque_servidor' => $cartaServidor->ataque,
                'partida_finalizada' => $mensaje_final
            ];
        }

        
        private function resultadoDeLaPartida($id_partida): string {
            $db = (new Conexion)->getDb();  
        
            $query = "SELECT el_usuario FROM jugada WHERE partida_id = :partida_id";
        
            $stmt = $db->prepare($query);
            $stmt->bindParam(':partida_id', $id_partida, PDO::PARAM_INT);
            $stmt->execute();
            
            $jugadas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $conteo = [
                'gano' => 0,
                'perdio' => 0,
                'empato' => 0
            ];
        
            foreach ($jugadas as $resultado) {
                if (isset($conteo[$resultado])) {
                    $conteo[$resultado]++;
                }
            }
            $resultadoFinal = array_search(max($conteo), $conteo);
        
            return $resultadoFinal; 
        }
        

    

        private function atributosDeCartas($mazo_act): array {
            $db = (new Conexion())->getDb();
            $result = [];
        
            foreach ($mazo_act as $carta) {
                $query = "SELECT atributo_id FROM carta WHERE id = :carta_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':carta_id', $carta['carta_id']); // corregido
                $stmt->execute();
                $atributo = $stmt->fetch(PDO::FETCH_ASSOC);
        
                $result[$carta['carta_id']] = $atributo ? $atributo['atributo_id'] : null;
            }
        
            return $result;
        }

       public function indicarAtributos($usuarioId, $partidaId, $token): array {
            $db = (new Conexion())->getDb();

            if ($usuarioId == 1) {
                // Caso especial: servidor, no se valida token ni usuario logueado

                $consultaMazo = "SELECT mazo_id FROM partida WHERE id = :partida_id AND usuario_id = 1";
                $stmt = $db->prepare($consultaMazo);
                $stmt->bindParam(':partida_id', $partidaId);
                $stmt->execute();

                $mazo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$mazo) {
                    return [
                        'status' => 404,
                        'message' => 'No se encontró la partida del servidor'
                    ];
                }

                $mazoId = $mazo['mazo_id'];

                // Obtener cartas en mano del mazo del servidor
                $query = "SELECT c.id, c.nombre, c.ataque, c.ataque_nombre, c.imagen, c.atributo_id
                        FROM mazo_carta mc
                        JOIN carta c ON mc.carta_id = c.id
                        WHERE mc.mazo_id = :mazo_id AND mc.estado = 'en_mano'";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':mazo_id', $mazoId);
                $stmt->execute();
                $cartas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($cartas) {
                    return [
                        'status' => 200,
                        'message' => $cartas
                    ];
                }

                return [
                    'status' => 404,
                    'message' => 'No se encontraron cartas en mano para la partida del servidor'
                ];

            } else {
                // Para otros usuarios sí se valida token y que coincida con usuarioId
                $usr = new Usuario();
                $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);

                if (!$usuarioLogueado) {
                    return [
                        'status' => 401,
                        'message' => "El usuario no está logueado"
                    ];
                }

                if ($usuarioLogueado['id'] != $usuarioId) {
                    return [
                        'status' => 403,
                        'message' => "No tiene permiso para ver esta partida"
                    ];
                }

                // Obtengo mazo para la partida y usuario
                $consultaMazo = "SELECT mazo_id FROM partida WHERE id = :partida_id AND usuario_id = :usuario_id";
                $stmt = $db->prepare($consultaMazo);
                $stmt->bindParam(':partida_id', $partidaId);
                $stmt->bindParam(':usuario_id', $usuarioId);
                $stmt->execute();

                $mazo = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$mazo) {
                    return [
                        'status' => 403,
                        'message' => 'La partida no pertenece al usuario o no existe'
                    ];
                }

                $mazoId = $mazo['mazo_id'];

                // Obtengo cartas en mano de ese mazo
                $query = "SELECT c.id, c.nombre, c.ataque, c.ataque_nombre, c.imagen, c.atributo_id
                        FROM mazo_carta mc
                        JOIN carta c ON mc.carta_id = c.id
                        WHERE mc.mazo_id = :mazo_id AND mc.estado = 'en_mano'";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':mazo_id', $mazoId);
                $stmt->execute();
                $cartas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($cartas) {
                    return [
                        'status' => 200,
                        'message' => $cartas
                    ];
                }

                return [
                    'status' => 404,
                    'message' => 'No se encontraron cartas en mano para esta partida'
                ];
            }
        }
}
?>
