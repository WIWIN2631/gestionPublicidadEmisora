<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: clientes.php");
    exit();
}

$id = (int) $_GET['id'];

try {
    $stmt = mysqli_prepare($conexionBd, "DELETE FROM clientes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    echo "<script>alert('Cliente eliminado correctamente'); window.location.href='clientes.php';</script>";
    exit();
} catch (mysqli_sql_exception $e) {
    echo "<script>alert('No se pudo eliminar el cliente'); window.location.href='clientes.php';</script>";
    exit();
}
