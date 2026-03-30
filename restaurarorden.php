<?php
session_start();
include("funciones/bd.php");

if (!isset($_GET['orden'])) {
    header("Location: anuladas.php");
    exit();
}

$numeroOrden = $_GET['orden'];

// 1️⃣ Cambiar estado en la tabla ordenes
$stmtUpdate = $conexionBd->prepare("
    UPDATE ordenes
    SET estado = 'Activa'
    WHERE numero_orden = ?
");
$stmtUpdate->bind_param("i", $numeroOrden);
$stmtUpdate->execute();

// 2️⃣ Eliminar el registro de la tabla ordenes_anuladas
$stmtDelete = $conexionBd->prepare("
    DELETE FROM ordenes_anuladas
    WHERE numero_orden = ?
");
$stmtDelete->bind_param("i", $numeroOrden);
$stmtDelete->execute();

header("Location: anuladas.php");
exit();
?>