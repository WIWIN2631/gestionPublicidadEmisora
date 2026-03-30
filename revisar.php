<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Validar que venga el número de orden por GET
if (!isset($_GET['orden']) || empty($_GET['orden'])) {
    header("Location: anuladas.php");
    exit();
}

$numeroOrden = $_GET['orden'];

// Traer información de la orden desde la base de datos
$stmt = $conexionBd->prepare("
    SELECT o.numero_orden, o.nombre_cliente, a.motivo, a.comentarios, a.fecha_anulacion
    FROM ordenes_anuladas a
    JOIN ordenes o ON o.numero_orden = a.numero_orden
    WHERE o.numero_orden = ?
");
$stmt->bind_param("i", $numeroOrden);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Orden no encontrada.";
    exit();
}

$orden = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styleAnuladas.css">
    <title>Revisar Orden <?= $orden['numero_orden'] ?></title>
</head>
<body>
<header>
    <div class="topbar-brand">
        🎙 Celestial <span>104.1 FM</span>
    </div>
    <nav> 
        <ul>
            <li><a href="index.php" class="active-link">DASHBOARD</a></li>
            <li><a href="clientes.php">CLIENTES</a></li>
            <li><a href="ordenes.php">ÓRDENES</a></li>
            <li><a href="anuladas.php">ANULADAS</a></li>
            <li><a href="confirmacion.php">CONFIRMACIÓN</a></li>
            <li><a href="administracion.php">ADMINISTRACIÓN</a></li>
        </ul>
    </nav>

    <div class="header-actions">
        <div class="admin-status is-active" aria-label="Estado del usuario">
            <span class="status-dot"></span>
            <span><?= $_SESSION['usuario']['nombre'] ?></span>
        </div>
        <div class="boton-salir">
            <button><a href="funciones/logout.php">Salir</a></button>
        </div>
    </div>
</header>

<section class="text-prin">
    <div class="dashboard-container">
        <div class="titulo-boton">
            <h1>Revisión de Orden Anulada</h1>
            <p>Detalle de la anulación registrada para la orden <?= $orden['numero_orden'] ?></p>
        </div>
    </div>
</section>

<main class="page-grid">
    <div class="main-card">
        <div class="registro-clientes-tex">
            <div class="titulo-clientes-registro">
                <h1>Revisar Orden <?= $orden['numero_orden'] ?></h1>
                <p>Módulo Celestial Stereo — Anuladas</p>
            </div>
        </div>

        <form class="form-registro">
            <div class="form-grid">
                <div class="form-group">
                    <label>ID DE ORDEN</label>
                    <input type="number" value="<?= $orden['numero_orden'] ?>" readonly>
                </div>
                <div class="form-group">
                    <label>CLIENTE</label>
                    <input type="text" value="<?= $orden['nombre_cliente'] ?>" readonly>
                </div>
                <div class="form-group">
                    <label>MOTIVO DE ANULACIÓN</label>
                    <input type="text" value="<?= $orden['motivo'] ?>" readonly>
                </div>
                <div class="form-group full-width">
                    <label>COMENTARIOS</label>
                    <textarea rows="3" readonly><?= $orden['comentarios'] ?></textarea>
                </div>
                <div class="form-group">
                    <label>FECHA ANULACIÓN</label>
                    <input type="text" value="<?= date("Y-m-d H:i", strtotime($orden['fecha_anulacion'])) ?>" readonly>
                </div>
            </div>
            <div class="form-actions">
                <a href="anuladas.php" class="btn-limpiar">Volver a Anuladas</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>