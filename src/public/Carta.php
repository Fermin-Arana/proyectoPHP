<?php

    class Carta {

        public function listarCartas($atributo, $nombre): array{
            $db = (new Conexion())->getDb();
            $query = "SELECT nombre, ataque, atributo_id FROM carta WHERE atributo_id = :atributo AND nombre LIKE :nombre";
            $stmt = $db->prepare($query);

            $nombreConLike = "%$nombre%";  // uso esta variable para usar bindParam, sino se usa bindValue
            $stmt->bindParam(":atributo", $atributo);
            $stmt->bindParam(":nombre", $nombreConLike);
            $stmt->execute();

            $carta = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $carta ?? [];
        }
    }
?>