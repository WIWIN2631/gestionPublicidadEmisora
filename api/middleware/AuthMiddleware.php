<?php

require_once __DIR__ . '/../config/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireAuth(): array {
    if (empty($_SESSION['user'])) {
        jsonResponse(['ok' => false, 'mensaje' => 'No autenticado.'], 401);
    }

    return $_SESSION['user'];
}

function requireRole(array $user, string $role): void {
    if (($user['rol'] ?? '') !== $role) {
        jsonResponse(['ok' => false, 'mensaje' => 'No tienes permiso para esta acción.'], 403);
    }
}
