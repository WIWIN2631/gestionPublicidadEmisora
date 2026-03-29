<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ClienteController {
    public function index(): void {
        requireAuth();

        $q = trim($_GET['q'] ?? '');
        $sql = 'SELECT id, nit, nombre, direccion, telefono, email, activo, creado_en FROM clientes WHERE activo = 1';
        $params = [];

        if ($q !== '') {
            $sql .= ' AND (nit LIKE ? OR nombre LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $sql .= ' ORDER BY nombre ASC';

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);

        jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
    }

    public function show(int $id): void {
        requireAuth();

        $stmt = getDB()->prepare('SELECT id, nit, nombre, direccion, telefono, email, activo, creado_en FROM clientes WHERE id = ? AND activo = 1');
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            jsonResponse(['ok' => false, 'mensaje' => 'Cliente no encontrado.'], 404);
        }

        jsonResponse(['ok' => true, 'data' => $item]);
    }

    public function store(): void {
        requireAuth();

        $body = getBody();
        requireFields($body, ['nit', 'nombre']);

        $check = getDB()->prepare('SELECT id FROM clientes WHERE nit = ? LIMIT 1');
        $check->execute([trim($body['nit'])]);

        if ($check->fetch()) {
            jsonResponse(['ok' => false, 'mensaje' => 'Ya existe un cliente con ese NIT.'], 409);
        }

        $stmt = getDB()->prepare('INSERT INTO clientes (nit, nombre, direccion, telefono, email, activo) VALUES (?, ?, ?, ?, ?, 1)');
        $stmt->execute([
            trim($body['nit']),
            trim($body['nombre']),
            trim((string)($body['direccion'] ?? '')),
            trim((string)($body['telefono'] ?? '')),
            trim((string)($body['email'] ?? ''))
        ]);

        jsonResponse(['ok' => true, 'mensaje' => 'Cliente creado.'], 201);
    }

    public function update(int $id): void {
        requireAuth();

        $body = getBody();
        requireFields($body, ['nit', 'nombre']);

        $stmt = getDB()->prepare('UPDATE clientes SET nit = ?, nombre = ?, direccion = ?, telefono = ?, email = ? WHERE id = ?');
        $stmt->execute([
            trim($body['nit']),
            trim($body['nombre']),
            trim((string)($body['direccion'] ?? '')),
            trim((string)($body['telefono'] ?? '')),
            trim((string)($body['email'] ?? '')),
            $id
        ]);

        jsonResponse(['ok' => true, 'mensaje' => 'Cliente actualizado.']);
    }

    public function destroy(int $id): void {
        $user = requireAuth();
        requireRole($user, 'admin');

        $stmt = getDB()->prepare('UPDATE clientes SET activo = 0 WHERE id = ?');
        $stmt->execute([$id]);

        jsonResponse(['ok' => true, 'mensaje' => 'Cliente desactivado.']);
    }
}
