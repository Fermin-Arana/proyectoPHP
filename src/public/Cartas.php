<?php
class Cartas {
    public function getCartas(): array {
        $db = (new Conexion())->getDb();
        $stmt = $db->query("SELECT id, nombre, ataque FROM carta");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}