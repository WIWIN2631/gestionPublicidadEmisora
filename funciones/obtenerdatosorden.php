<?php
// Incluir la conexión a la base de datos
include("bd.php");

// Configurar cabecera para JSON
header('Content-Type: application/json');

// Verificar que se recibió el número de orden
if (!isset($_GET['numero_orden']) || empty($_GET['numero_orden'])) {
    echo json_encode(['error' => 'Número de orden no proporcionado']);
    exit();
}

$numeroOrden = intval($_GET['numero_orden']);

// Consultar los datos de la orden
$stmt = $conexionBd->prepare("
    SELECT numero_orden, nit_cliente, nombre_cliente, producto, referencia, 
           fecha_inicio, fecha_fin, cunas_dia, dias, horarios
    FROM ordenes
    WHERE numero_orden = ?
");

if (!$stmt) {
    echo json_encode(['error' => 'Error en la consulta: ' . $conexionBd->error]);
    exit();
}

$stmt->bind_param("i", $numeroOrden);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $orden = $resultado->fetch_assoc();
    
    // Formatear fechas si es necesario
    if ($orden['fecha_inicio']) {
        $orden['fecha_inicio'] = date('d/m/Y', strtotime($orden['fecha_inicio']));
    }
    if ($orden['fecha_fin']) {
        $orden['fecha_fin'] = date('d/m/Y', strtotime($orden['fecha_fin']));
    }
    
    echo json_encode($orden);
} else {
    echo json_encode(['error' => 'Orden no encontrada']);
}

$stmt->close();
$conexionBd->close();
?>