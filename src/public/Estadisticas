<?php
    class Estadisticas{
        private function getIds():array{
            $db = (new Conexion()) -> getDb();

            $query = "SELECT id FROM usuario";

            $stmt = $db -> prepare($query);
            $stmt -> execute();
            $result = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }

        public function getEstadisticas():array{
            $ids = $this->getIds();
            if(!$ids){
                return[
                    'status' => '404',
                    'message' => 'No hay ningun usuario'
                ];
            }
            $db = (new Conexion()) -> getDb();

            foreach($ids as $id){
                $query = "SELECT estado FROM partida WHERE usuario_id = :usuario_id";
                $stmt = $db -> prepare($query);

                $stmt -> bindParam(':usuario_id', $id['id'], PDO::PARAM_INT);
                $stmt -> execute();
                $result[$id['id']] = $stmt -> fetchAll(PDO::FETCH_ASSOC);
            }
            return $result;
        }
    }


?>