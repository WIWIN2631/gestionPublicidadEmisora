<?php
include("funciones/verificarsuperadmin.php");
include("funciones/bd.php");

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = $_GET['id'];

// Evitar que se elimine a sí mismo
if ($id == $_SESSION['usuario']['id']) {
    die("No puedes eliminarte a ti mismo");
}

$stmt = mysqli_prepare($conexionBd, "DELETE FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: administracion.php");
    exit();
} else {
    echo "Error al eliminar";
}

$stmt = mysqli_prepare($conexionBd, "SELECT rol FROM usuarios WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);

if ($user['rol'] === 'superadmin') {
    die("No puedes eliminar un superadmin");
}