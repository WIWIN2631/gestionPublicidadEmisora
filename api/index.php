<?php

require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ClienteController.php';
require_once __DIR__ . '/controllers/OrdenController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';

setCORS();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uriPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uriPath = preg_replace('#^api/?#', '', $uriPath);
$parts = array_values(array_filter(explode('/', $uriPath)));

$resource = $parts[0] ?? '';
$id = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : null;
$sub = $parts[2] ?? null;

if ($resource === 'auth') {
    $ctrl = new AuthController();

    if ($method === 'POST' && ($parts[1] ?? '') === 'login') {
        $ctrl->login();
    }

    if ($method === 'POST' && ($parts[1] ?? '') === 'logout') {
        $ctrl->logout();
    }

    jsonResponse(['ok' => false, 'mensaje' => 'Ruta auth no válida.'], 404);
}

if ($resource === 'clientes') {
    $ctrl = new ClienteController();

    if ($method === 'GET' && $id === null) $ctrl->index();
    if ($method === 'GET' && $id !== null) $ctrl->show($id);
    if ($method === 'POST' && $id === null) $ctrl->store();
    if ($method === 'PUT' && $id !== null) $ctrl->update($id);
    if ($method === 'DELETE' && $id !== null) $ctrl->destroy($id);

    jsonResponse(['ok' => false, 'mensaje' => 'Método no permitido en clientes.'], 405);
}

if ($resource === 'ordenes') {
    $ctrl = new OrdenController();

    if ($method === 'GET' && ($parts[1] ?? '') === 'anuladas') {
        $ctrl->anuladas();
    }

    if ($method === 'POST' && $id !== null && $sub === 'anular') {
        $ctrl->anular($id);
    }

    if ($method === 'POST' && $id !== null && $sub === 'confirmar') {
        $ctrl->confirmar($id);
    }

    if ($method === 'GET' && $id === null) $ctrl->index();
    if ($method === 'GET' && $id !== null) $ctrl->show($id);
    if ($method === 'POST' && $id === null) $ctrl->store();
    if ($method === 'PUT' && $id !== null) $ctrl->update($id);

    jsonResponse(['ok' => false, 'mensaje' => 'Método no permitido en órdenes.'], 405);
}

if ($resource === 'usuarios') {
    $ctrl = new UsuarioController();

    if ($method === 'GET' && $id === null) $ctrl->index();
    if ($method === 'POST' && $id === null) $ctrl->store();
    if ($method === 'PUT' && $id !== null) $ctrl->update($id);
    if ($method === 'DELETE' && $id !== null) $ctrl->destroy($id);

    jsonResponse(['ok' => false, 'mensaje' => 'Método no permitido en usuarios.'], 405);
}

if ($resource === 'dashboard') {
    requireAuth();
    $db = getDB();

    $stats = [
        'ordenes_activas' => (int)$db->query("SELECT COUNT(*) AS total FROM ordenes WHERE estado = 'activa'")->fetch()['total'],
        'ordenes_anuladas' => (int)$db->query("SELECT COUNT(*) AS total FROM ordenes WHERE estado = 'anulada'")->fetch()['total'],
        'clientes_activos' => (int)$db->query("SELECT COUNT(*) AS total FROM clientes WHERE activo = 1")->fetch()['total'],
        'facturado_mes' => (float)$db->query("SELECT COALESCE(SUM(valor),0) AS total FROM ordenes WHERE estado='activa' AND MONTH(fecha_orden)=MONTH(CURDATE()) AND YEAR(fecha_orden)=YEAR(CURDATE())")->fetch()['total'],
    ];

    $recientes = $db->query("SELECT o.numero_orden, c.nombre AS cliente, o.producto, o.valor, o.estado FROM ordenes o INNER JOIN clientes c ON c.id = o.cliente_id ORDER BY o.creado_en DESC LIMIT 5")->fetchAll();

    jsonResponse(['ok' => true, 'stats' => $stats, 'recientes' => $recientes]);
}

jsonResponse(['ok' => false, 'mensaje' => 'Ruta no encontrada.'], 404);
