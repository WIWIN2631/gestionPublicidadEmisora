<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/EmailService.php';

class OrdenController {
    public function index(): void {
        requireAuth();

        $estado = trim($_GET['estado'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $sql = 'SELECT o.id, o.numero_orden, o.numero_presupuesto, o.producto, o.referencia, o.fecha_orden, o.fecha_inicio, o.fecha_fin, o.cunas_diarias, o.duracion_seg, o.horarios, o.dias_pauta, o.valor, o.estado, c.id AS cliente_id, c.nit, c.nombre AS cliente_nombre, c.email AS cliente_email FROM ordenes o INNER JOIN clientes c ON c.id = o.cliente_id WHERE 1=1';
        $params = [];

        if ($estado !== '') {
            $sql .= ' AND o.estado = ?';
            $params[] = $estado;
        }

        if ($q !== '') {
            $sql .= ' AND (o.numero_orden LIKE ? OR c.nombre LIKE ? OR o.producto LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $sql .= ' ORDER BY o.numero_orden DESC';

        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);

        jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
    }

    public function show(int $id): void {
        requireAuth();

        $stmt = getDB()->prepare('SELECT o.*, c.nit, c.nombre AS cliente_nombre, c.email AS cliente_email FROM ordenes o INNER JOIN clientes c ON c.id = o.cliente_id WHERE o.id = ?');
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            jsonResponse(['ok' => false, 'mensaje' => 'Orden no encontrada.'], 404);
        }

        jsonResponse(['ok' => true, 'data' => $item]);
    }

    public function store(): void {
        $user = requireAuth();
        $body = getBody();
        requireFields($body, ['cliente_id', 'producto', 'fecha_orden', 'fecha_inicio', 'fecha_fin', 'cunas_diarias', 'duracion_seg', 'valor']);

        $next = (int)getDB()->query('SELECT COALESCE(MAX(numero_orden),100) + 1 AS n FROM ordenes')->fetch()['n'];

        $stmt = getDB()->prepare('INSERT INTO ordenes (numero_orden, numero_presupuesto, cliente_id, producto, referencia, fecha_orden, fecha_inicio, fecha_fin, cunas_diarias, duracion_seg, horarios_24h, horarios, dias_pauta, valor, estado, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "activa", ?)');
        $stmt->execute([
            $next,
            ($body['numero_presupuesto'] === '' ? null : $body['numero_presupuesto']) ?? null,
            (int)$body['cliente_id'],
            trim((string)$body['producto']),
            trim((string)($body['referencia'] ?? '')),
            $body['fecha_orden'],
            $body['fecha_inicio'],
            $body['fecha_fin'],
            (int)$body['cunas_diarias'],
            (int)$body['duracion_seg'],
            isset($body['horarios_24h']) ? (int)$body['horarios_24h'] : 1,
            trim((string)($body['horarios'] ?? '')),
            trim((string)($body['dias_pauta'] ?? '')),
            (float)$body['valor'],
            (int)$user['id']
        ]);

        jsonResponse(['ok' => true, 'mensaje' => 'Orden creada.', 'numero_orden' => $next], 201);
    }

    public function update(int $id): void {
        requireAuth();
        $body = getBody();
        requireFields($body, ['producto', 'fecha_inicio', 'fecha_fin', 'valor']);

        $estadoStmt = getDB()->prepare('SELECT estado FROM ordenes WHERE id = ?');
        $estadoStmt->execute([$id]);
        $estadoRow = $estadoStmt->fetch();

        if (!$estadoRow) {
            jsonResponse(['ok' => false, 'mensaje' => 'Orden no encontrada.'], 404);
        }

        if (($estadoRow['estado'] ?? '') === 'anulada') {
            jsonResponse(['ok' => false, 'mensaje' => 'No se puede editar una orden anulada.'], 422);
        }

        $stmt = getDB()->prepare('UPDATE ordenes SET numero_presupuesto = ?, producto = ?, referencia = ?, fecha_orden = ?, fecha_inicio = ?, fecha_fin = ?, cunas_diarias = ?, duracion_seg = ?, horarios_24h = ?, horarios = ?, dias_pauta = ?, valor = ? WHERE id = ?');
        $stmt->execute([
            ($body['numero_presupuesto'] === '' ? null : $body['numero_presupuesto']) ?? null,
            trim((string)$body['producto']),
            trim((string)($body['referencia'] ?? '')),
            $body['fecha_orden'] ?? date('Y-m-d'),
            $body['fecha_inicio'],
            $body['fecha_fin'],
            (int)($body['cunas_diarias'] ?? 1),
            (int)($body['duracion_seg'] ?? 30),
            isset($body['horarios_24h']) ? (int)$body['horarios_24h'] : 1,
            trim((string)($body['horarios'] ?? '')),
            trim((string)($body['dias_pauta'] ?? '')),
            (float)$body['valor'],
            $id
        ]);

        jsonResponse(['ok' => true, 'mensaje' => 'Orden actualizada.']);
    }

    public function anular(int $id): void {
        $user = requireAuth();
        $body = getBody();
        requireFields($body, ['motivo']);

        $db = getDB();
        $db->beginTransaction();

        try {
            $check = $db->prepare('SELECT estado FROM ordenes WHERE id = ?');
            $check->execute([$id]);
            $orden = $check->fetch();

            if (!$orden) {
                $db->rollBack();
                jsonResponse(['ok' => false, 'mensaje' => 'Orden no encontrada.'], 404);
            }

            if (($orden['estado'] ?? '') === 'anulada') {
                $db->rollBack();
                jsonResponse(['ok' => false, 'mensaje' => 'La orden ya estaba anulada.'], 422);
            }

            $up = $db->prepare('UPDATE ordenes SET estado = "anulada" WHERE id = ?');
            $up->execute([$id]);

            $ins = $db->prepare('INSERT INTO anulaciones (orden_id, motivo, anulado_por) VALUES (?, ?, ?)');
            $ins->execute([$id, trim((string)$body['motivo']), (int)$user['id']]);

            $db->commit();
            jsonResponse(['ok' => true, 'mensaje' => 'Orden anulada.']);
        } catch (Throwable $e) {
            $db->rollBack();
            jsonResponse(['ok' => false, 'mensaje' => 'No se pudo anular la orden.'], 500);
        }
    }

    public function anuladas(): void {
        requireAuth();

        $stmt = getDB()->prepare('SELECT o.numero_orden, c.nombre AS cliente, o.producto, o.referencia, o.fecha_orden, o.cunas_diarias, o.dias_pauta, o.horarios, o.valor, a.motivo, a.anulado_en FROM anulaciones a INNER JOIN ordenes o ON o.id = a.orden_id INNER JOIN clientes c ON c.id = o.cliente_id ORDER BY a.anulado_en DESC');
        $stmt->execute();

        jsonResponse(['ok' => true, 'data' => $stmt->fetchAll()]);
    }

    public function confirmar(int $id): void {
        requireAuth();

        $stmt = getDB()->prepare('SELECT o.numero_orden, o.producto, o.referencia, o.fecha_inicio, o.fecha_fin, o.cunas_diarias, o.dias_pauta, o.horarios, o.valor, c.nombre AS cliente_nombre, c.email AS cliente_email FROM ordenes o INNER JOIN clientes c ON c.id = o.cliente_id WHERE o.id = ?');
        $stmt->execute([$id]);
        $orden = $stmt->fetch();

        if (!$orden) {
            jsonResponse(['ok' => false, 'mensaje' => 'Orden no encontrada.'], 404);
        }

        $ok = EmailService::confirmarOrden($orden, (string)$orden['cliente_email'], (string)$orden['cliente_nombre']);

        jsonResponse([
            'ok' => true,
            'mensaje' => $ok ? 'Confirmación enviada (o simulada) correctamente.' : 'No se pudo enviar el correo.',
            'email_ok' => $ok
        ]);
    }
}
