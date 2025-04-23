<?php
    class Mazo{
        public function getCartasMazo($mazo_id): array {
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
            $query ="UPDATE mazo_carta SET estado = :nuevo_estado WHERE mazo_id = :mazo_id AND carta_id = :carta_id";

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
                return true;
                }else{
                    return false;
                }
        }

        public function ultimaRonda($mazo_id):bool{
            $cartas = $this->getCartasMazo($mazo_id);

            foreach ($cartas as $carta) {
                if ($carta ->estado == "en_mazo"){
                    return false;
                }
            }
            return true;
        }



    }
?>