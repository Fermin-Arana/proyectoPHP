<?php
    class Mazo{

        public function borrarMazo($mazo_id,$usuario) {
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