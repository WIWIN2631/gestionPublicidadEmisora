<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['usuario']['rol'] !== 'superadmin') {
    echo "Acceso denegado";
    exit();
}