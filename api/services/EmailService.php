<?php

class EmailService {
    // Versión básica: por ahora simula envío y registra en log del servidor.
    public static function confirmarOrden(array $orden, string $correo, string $cliente): bool {
        $numero = $orden['numero_orden'] ?? 'N/A';
        error_log('[EmailService] Confirmación simulada para ' . $cliente . ' <' . $correo . '> - Orden ' . $numero);
        return true;
    }
}
