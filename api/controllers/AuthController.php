<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

class AuthController {
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $body = getBody();
        requireFields($body, ['usuario', 'password']);

        $stmt = getDB()->prepare('SELECT id, usuario, password, rol, activo FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([trim($body['usuario'])]);
        $user = $stmt->fetch();

        if (!$user || (int)$user['activo'] !== 1 || !password_verify((string)$body['password'], (string)$user['password'])) {
            jsonResponse(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.'], 401);
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'usuario' => $user['usuario'],
            'rol' => $user['rol']
        ];

        jsonResponse(['ok' => true, 'data' => $_SESSION['user']]);
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();

        jsonResponse(['ok' => true, 'mensaje' => 'Sesión cerrada.']);
    }
}
