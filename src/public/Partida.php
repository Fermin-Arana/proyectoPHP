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

        public function crearPartida($usuario, $mazo_id): array{
            $usr = (new Usuario());
            $mazo = (new Mazo());
            if(!$usr -> estaLogueado($usuario)){
                return [
                    'status'=> 401,
                    'message'=> 'El usuario no esta logueado'
                ];
            }

            $usuario_id = $usr->getIdUsuario( $usuario );
            if($usuario_id == 0){
                return[
                    'status'=> 404,
                    'message'=> 'No se encontro el id del usuario'
                ];
            }

            if(!$usr -> mazoDelUsuario($usuario_id,$mazo_id)){
                return[
                    'status'=> 404,
                    'message'=> 'El mazo no es del usuario'
                ];
            }

            $mazo_usuario = $mazo ->getCartasMazo($mazo_id);
            $mazo_servidor = $mazo ->getCartasMazo(1);

            if(!$mazo_usuario){
                return [
                    'status'=> 404,
                    'message' => 'El mazo esta vacio'
                ];
            }

            foreach($mazo_usuario as $carta){
                $id = $carta -> carta_id;
                $mazo ->actualizarEstadoCarta($id,$mazo_id,"en_mano");
            }

            foreach($mazo_servidor as $carta_servidor){
                $id_servidor = $carta_servidor -> carta_id;
                $mazo ->actualizarEstadoCarta($id_servidor,1,"en_mano");
            }


            $db = (new Conexion())->getDb();

            $query = "INSERT INTO partida (usuario_id,fecha,mazo_id,estado) VALUES (:usuario_id,:fecha,:mazo_id,:estado)";

            $stmt = $db->prepare($query);

            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindValue(':fecha', date('Y-m-d H:i:s'));
            $stmt->bindParam(':mazo_id', $mazo_id);
            $stmt->bindValue(':estado', "en_curso");

            $stmt->execute();

            $id_partida = $db ->lastInsertId();

            return [
                'status' => 200,
                'id' => $id_partida,
                [
                    $mazo_usuario
                ]
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
        public function jugadaUsuario($carta_id, $id_partida){
            $mazo = (new Mazo());
            $usr = (new Usuario());
            $mazo_id = $this ->getIdMazoActual($id_partida);
            if(!$usr ->estaLogueado($usr -> getUsuario($this ->getIdUsuario($id_partida)))){
                return [
                    'status' => 401,
                    'message' => 'El usuario no esta logueado'
                ];
            }
            if(!$mazo -> cartaExisteEnMazo($mazo_id, $carta_id,)){
                return [
                    'status' => 404,
                    'message' => 'Esa carta no existe en el mazo'
                ];
            }
            if($mazo -> cartaFueUsada($mazo_id,$carta_id)){
                return [
                    'status' => 404,
                    'message'=> 'La carta ya fue usada'
                ];
            }
            $db = (new Conexion)->getDb(); 
            $id_carta_servidor = $this -> jugadaServidor();
            $cartaUsuario = $this -> getDatosCarta(carta_id: $carta_id);
            $cartaServidor = $this -> getDatosCarta($id_carta_servidor['message']);

            

            if($this -> ganaA($cartaUsuario -> atributo_id,$cartaServidor -> atributo_id)){
                $cartaUsuario -> ataque *= 1.30;
            } elseif($this -> ganaA($cartaServidor -> atributo_id,$cartaUsuario -> atributo_id)){
                $cartaServidor -> ataque *= 1.30;
            }


            if($cartaUsuario -> ataque > $cartaServidor -> ataque){
                $resultado_carta = "gano";
            } elseif($cartaUsuario -> ataque < $cartaServidor -> ataque) {
                $resultado_carta = "perdio";
            } else {
                $resultado_carta = "empato";
            }

            $mazo -> actualizarEstadoCarta($carta_id,$mazo_id,"descartado");
            $mazo -> actualizarEstadoCarta($id_carta_servidor,1,"descartado");

            $query = "INSERT INTO jugada (partida_id, carta_id_a, carta_id_b, el_usuario) VALUES (:partida_id,:carta_id_a,:carta_id_b,:el_usuario)";
            $stmt = $db -> prepare($query);


            $stmt ->bindParam(':partida_id', $id_partida);
            $stmt->bindParam(':carta_id_a', $carta_id);
            $stmt->bindParam(':carta_id_b', $id_carta_servidor);
            $stmt->bindParam(':el_usuario', $resultado_carta);

            $stmt -> execute();

            if($mazo -> ultimaRonda($mazo_id)){
                $query = "UPDATE partida SET estado = :estado WHERE id = :id_partida";

                $stmt = $db -> prepare($query);
                $stmt ->bindParam(':id_partida', $id_partida);
                $stmt->bindValue(':estado', "finalizada");

                $stmt-> execute();
            }
            return [
                'status' => 200,
                'message' => $resultado_carta,
            ];
        }  

        public function jugarPartida($id_partida){
            $mazo = new Mazo();
            $mazo_usuario_id = $this -> getIdMazoActual($id_partida);
            $mazo_usuario = $mazo -> getCartasMazo($mazo_usuario_id);

            $cantGano = 0;
            $cantPerdio = 0;
            $cantEmpato = 0;

            foreach ($mazo_usuario as $carta){
                $resultado = $this -> jugadaUsuario($carta -> carta_id, $id_partida);
                if($resultado['message'] == "gano"){
                    $cantGano++;
                } elseif($resultado['message'] == "perdio"){
                    $cantPerdio++;
                } else{
                    $cantEmpato++;
                }
            }

            if($cantGano >= 3){
                $resultado_final = "gano";
            } elseif ($cantPerdio >= 3){
                $resultado_final = "perdio";
            } else {
                $resultado_final = "empato";
            }
            $db = (new Conexion())-> getDb();
            $query = "UPDATE mazo_carta SET estado = :estado WHERE mazo_id = :mazo_usuario_id OR mazo_id = :mazo_servidor_id";
            $stmt = $db -> prepare($query);
            $stmt -> bindValue(":estado" , "en_mazo");
            $stmt -> bindParam(":mazo_usuario_id", $mazo_usuario_id);
            $stmt -> bindValue(":mazo_servidor_id", 1);
            $stmt -> execute();

            $query = "UPDATE partida SET el_usuario = :el_usuario WHERE id = :id_partida";
            $stmt = $db -> prepare($query);
            $stmt -> bindParam(":el_usuario", $resultado_final);
            $stmt -> bindParam(":id_partida", $id_partida);
            $stmt -> execute(); 

            return [
                'status' => 200,
                'message' => $resultado_final,
            ];
        }

        private function atributosDeCartas($mazo_act): array {
            $db = (new Conexion())->getDb();
            $result = [];
        
            foreach ($mazo_act as $carta) {
                $query = "SELECT atributo_id FROM carta WHERE id = :carta_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':carta_id', $carta['carta_id']); // corregido
                $stmt->execute();
<<<<<<< HEAD
                $result[$carta_id['carta_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
=======
                $atributo = $stmt->fetch(PDO::FETCH_ASSOC);
        
                $result[$carta['carta_id']] = $atributo ? $atributo['atributo_id'] : null;
>>>>>>> origin/ramaFer
            }
        
            return $result;
        }

        public function indicarAtributos($mazo_id):array{
            $db = (new Conexion())-> getDb();
            
            $query = "SELECT carta_id FROM mazo_carta WHERE mazo_id = :mazo_id AND estado = :estado";
            $stmt = $db -> prepare($query);
            $stmt ->bindParam(':mazo_id', $mazo_id);
            $stmt ->bindValue(':estado', "en_mazo");
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if($result){
                return [
                    'status' => 200,
                    'message' => $this -> atributosDeCartas($result),
                ];
            }
            return [
                'status'=> 404,
                'message' => "no se encontro la carta"
            ];
        }


    }
?>