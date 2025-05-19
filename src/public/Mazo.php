<?php
class Mazo {
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

    public function actualizarEstadoCarta($carta_id, $mazo_id, $nuevo_estado): bool {
        $db = (new Conexion())->getDb();
        $query = "UPDATE mazo_carta SET estado = :nuevo_estado WHERE mazo_id = :mazo_id AND carta_id = :carta_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nuevo_estado', $nuevo_estado);
        $stmt->bindParam(':mazo_id', $mazo_id);
        $stmt->bindParam(':carta_id', $carta_id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function ultimaRonda($mazo_id): bool {
        $db = (new Conexion())->getDb();
        $query = 'SELECT estado FROM mazo_carta WHERE mazo_id = :mazo_id1 AND mazo_id = :mazo_id2';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':mazo_id1', $mazo_id);
        $stmt->bindValue(':mazo_id2', 1);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }

    public function crearMazo($token, $cartas, $nombreMazo) {

        //Verifico en el endpoint y aca nuevamente por las dudas
        if (empty($nombreMazo) || !is_array($cartas) || count($cartas) === 0) {
        return [
            'status' => 400,
            'message' => "Faltan datos: nombre del mazo o cartas inválidas"
        ];
    }

        $usr = new Usuario();
        $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);

        if (!$usuarioLogueado) {
            return [
                'status' => 401,
                'message' => "El usuario no está logueado"
            ];
        }

        // Valido las cartas: sin duplicados y exactamente 5
        if (count($cartas) !== count(array_unique($cartas))) {
            return [
                'status' => 400,
                'message' => "Las cartas deben ser de IDs distintos"
            ];
        }

        if (count($cartas) !== 5) {
            return [
                'status' => 400,
                'message' => "Debes enviar exactamente 5 cartas"
            ];
        }
        

        // Valido que las cartas existan
        $db = (new Conexion())->getDb();
        $marcadores = implode(',', array_fill(0, count($cartas), '?'));
        $query = "SELECT id FROM carta WHERE id IN ($marcadores)";
        $stmt = $db->prepare($query);
        $stmt->execute($cartas);
        $cartasValidas = $stmt->fetchAll(PDO::FETCH_COLUMN);


        if (count($cartasValidas) !== count($cartas)) {
            return [
                'status' => 400,
                'message' => "Una o más cartas no existen o no pertenecen al usuario"
            ];
        }

        // Verifico límite de mazos
        $stmt = $db->prepare("SELECT COUNT(*) as cantidad FROM mazo WHERE usuario_id = :usuario_id");
        $stmt->bindParam(':usuario_id', $usuarioLogueado['id']);
        $stmt->execute();
        $cantidad = $stmt->fetch()['cantidad'];

        if ($cantidad >= 3) {
            return [
                'status' => 400,
                'message' => "No puedes tener más de 3 mazos"
            ];//funca bien
        }

        // Inserto mazo
        $stmt = $db->prepare("INSERT INTO mazo (usuario_id, nombre) VALUES (?, ?)");
        $success = $stmt->execute([$usuarioLogueado['id'], $nombreMazo]);
        $mazoId = $db->lastInsertId();

         // Inserto cartas asociadas al mazo
        $stmt = $db->prepare("INSERT INTO mazo_carta (carta_id, mazo_id, estado) VALUES (?, ?, 'en_mazo')");
        foreach ($cartas as $idCarta) {
            $stmt->execute([$idCarta, $mazoId]);
        }

        return [
            'status' => 200,
            'message' => "Mazo creado con éxito",
            'data' => [
                'mazo_id' => $mazoId,
                'nombre' => $nombreMazo
            ]
        ];
    }

    public function borrarMazo($mazo_id, $token) {
        $usr = new Usuario();
        $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);
    
        if (!$usuarioLogueado) {
            return [
                'status' => 401,
                'message' => "El usuario no está logueado"
            ];
        }
    
        try {
            $db = (new Conexion())->getDb();
    
            // Verificar que el mazo pertenece al usuario logueado
            $stmt = $db->prepare("SELECT usuario_id FROM mazo WHERE id = :mazo_id");
            $stmt->bindParam(':mazo_id', $mazo_id);
            $stmt->execute();
            $mazo = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$mazo || $mazo['usuario_id'] != $usuarioLogueado['id']) {
                return [
                    'status' => 401,
                    'message' => "No tienes permiso para borrar este mazo"
                ];
            }
    
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
    
            return [
                'status' => 200,
                'message' => "Mazo borrado correctamente"
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'message' => "Error interno: " . $e->getMessage()
            ];
        }
    }
    

    public function listarMazosUsuario($token, $usuarioId): array {
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
            'message' => "No tiene permiso para ver los mazos de otro usuario"
        ];
    }

    $db = (new Conexion())->getDb();
    $query = "SELECT id, nombre FROM mazo WHERE usuario_id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":usuario_id", $usuarioId, PDO::PARAM_INT);
    $stmt->execute();

    $mazos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$mazos) {
        return [
            'status' => 200,
            'message' => "El usuario no tiene ningún mazo"
        ];
    }

    return [
        'status' => 200,
        'message' => $mazos
    ];
}


    public function editarMazo($token, $nombre, $id_mazo): array {
        $usr = new Usuario();
        $usuarioLogueado = $usr->obtenerUsuarioPorToken($token);

        if ($usuarioLogueado) {
            $db = (new Conexion())->getDb();
            $query = "UPDATE mazo SET nombre = :nombre WHERE usuario_id = :usuario AND id = :id_mazo";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":usuario", $usuarioLogueado['id']);
            $stmt->bindParam(":id_mazo", $id_mazo);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return [
                    'status' => 200,
                    'message' => "Mazo actualizado correctamente"
                ];
            } else {
                return [
                    'status' => 404,
                    'message' => "El mazo no existe o no pertenece al usuario"
                ];
            }
        } else {
            return [
                'status' => 401,
                'message' => "El usuario no está logueado"
            ];
        }
    }


    public function listarCartas(?string $atributo = null, ?string $nombre = null): array {//este metodo puede recibir como no parametros, por eso los envio de esa manera
        $db = (new Conexion())->getDb();

        
        $query = "SELECT nombre, ataque, atributo_id FROM carta";
        $params = [];
        $conditions = [];

        // Filtros opcionales
        if (!empty($atributo)) {
            $conditions[] = "atributo_id = :atributo";
            $params[':atributo'] = $atributo;
        }

        if (!empty($nombre)) {
            $conditions[] = "nombre LIKE :nombre";
            $params[':nombre'] = '%' . $nombre . '%';
        }

        // Si hay condiciones, las agregamos al WHERE
        if (count($conditions) > 0) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $db->prepare($query);

        // Pasamos los parámetros al statement
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $cartas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $cartas ?? [];
    }

}
?>
