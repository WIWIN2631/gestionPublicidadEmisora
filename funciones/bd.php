<?php

$host = 'localhost';
$bdname = 'gestionpublicidademisora';
$user = 'root';
$pass = '';

try { 
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conexionBd = mysqli_connect($host, $user, $pass, $bdname);
    mysqli_set_charset($conexionBd, "utf8mb4");

} catch (mysqli_sql_exception $error) {
    header("Content-type: application/json");
    echo json_encode(["ok" => false, "mensaje"=> "Error de conexión: ".$error->getMessage()]);
    exit;
}