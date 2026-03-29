<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class UsuarioController {
    public function index(): void {
        $user = requireAuth();
        requireRole($user, 'admin');

        $stmt = getDB()->prepare('SELECT id, usuario, rol, activo, creado_en FROM usuarios ORDER BY id ASC');
        $stmt->execute();

        jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
    }

    public function store(): void {
        $user = requireAuth();
        requireRole($user, 'admin');

        $body = getBody();
        requireFields($body, ['usuario', 'password', 'rol']);

        if (!in_array($body['rol'], ['admin', 'invitado'], true)) {
            jsonResponse(['ok' => false, 'mensaje' => 'Rol inválido.'], 422);
        }

        $check = getDB()->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
        $check->execute([trim($body['usuario'])]);

        if ($check->fetch()) {
            jsonResponse(['ok' => false, 'mensaje' => 'Ese usuario ya existe.'], 409);
        }

        $stmt = getDB()->prepare('INSERT INTO usuarios (usuario, password, rol, activo) VALUES (?, ?, ?, 1)');
        $stmt->execute([
            trim($body['usuario']),
            password_hash((string)$body['password'], PASSWORD_BCRYPT),
            $body['rol']
        ]);

        jsonResponse(['ok' => true, 'mensaje' => 'Usuario creado.'], 201);
    }

    public function update(int $id): void {
        $user = requireAuth();
        requireRole($user, 'admin');

        $body = getBody();
        $fields = [];
        $params = [];

        if (!empty($body['password'])) {
            $fields[] = 'password = ?';
            $params[] = password_hash((string)$body['password'], PASSWORD_BCRYPT);
        }

        if (!empty($body['rol']) && in_array($body['rol'], ['admin', 'invitado'], true)) {
            $fields[] = 'rol = ?';
            $params[] = $body['rol'];
        }

        if (isset($body['activo'])) {
            $fields[] = 'activo = ?';
            $params[] = (int)$body['activo'];
        }

        if (empty($fields)) {
            jsonResponse(['ok' => false, 'mensaje' => 'No hay campos para actualizar.'], 422);
        }

        $params[] = $id;

        $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);

        jsonResponse(['ok' => true, 'mensaje' => 'Usuario actualizado.']);
    }

    public function destroy(int $id): void {
        $user = requireAuth();
        requireRole($user, 'admin');

        if ((int)$user['id'] === $id) {
            jsonResponse(['ok' => false, 'mensaje' => 'No puedes desactivar tu mismo usuario.'], 422);
        }

        $stmt = getDB()->prepare('UPDATE usuarios SET activo = 0 WHERE id = ?');
        $stmt->execute([$id]);

        jsonResponse(['ok' => true, 'mensaje' => 'Usuario desactivado.']);
    }
}
