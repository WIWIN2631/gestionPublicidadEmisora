<?php
session_start();

include("funciones/verificarsuperadmin.php");
include("funciones/bd.php");

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = $_GET['id'];

// 🚫 Evitar eliminarse a sí mismo
if ($id == $_SESSION['usuario']['id']) {
    die("No puedes eliminarte a ti mismo");
}

// 🔍 Obtener datos del usuario ANTES
$stmt = mysqli_prepare($conexionBd, "SELECT rol FROM usuarios WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

if (!$user) {
    die("Usuario no encontrado");
}

// 🚫 Evitar eliminar superadmin
if ($user['rol'] === 'superadmin') {
    die("No puedes eliminar un superadmin");
}

// ✅ AHORA SÍ eliminar
$stmt = mysqli_prepare($conexionBd, "DELETE FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: administracion.php");
    exit();
} else {
    echo "Error al eliminar";
}