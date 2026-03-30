<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Verificar que se recibió el ID de la orden
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ordenes.php");
    exit();
}

$idOrden = intval($_GET['id']);

// Obtener los datos de la orden
$stmt = $conexionBd->prepare("
    SELECT * FROM ordenes 
    WHERE id = ?
");
$stmt->bind_param("i", $idOrden);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: ordenes.php");
    exit();
}

$orden = $resultado->fetch_assoc();

// Formatear fechas
$fechaOrden = date("d/m/Y", strtotime($orden['fecha_orden']));
$fechaInicio = date("d/m/Y", strtotime($orden['fecha_inicio']));
$fechaFin = date("d/m/Y", strtotime($orden['fecha_fin']));
$valorFormateado = "$" . number_format($orden['valor'], 0, ',', '.');

// Obtener el rol del usuario
$rolUsuario = $_SESSION['usuario']['rol'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleOrdenes.css">
    <link rel="stylesheet" href="css/styleImprimir.css">
    <title>Certificación de Orden - Celestial Stereo 104.1 FM</title>

</head>

<body>

<header>
    <div class="topbar-brand">
        🎙 Celestial <span>104.1 FM</span>
    </div>

    <nav> 
        <ul>
            <li><a href="index.php">DASHBOARD</a></li>
            <li><a href="clientes.php">CLIENTES</a></li>
            <li><a href="ordenes.php" class="active-link">ÓRDENES</a></li>
            <li><a href="anuladas.php">ANULADAS</a></li>

            <?php if(isset($_SESSION['usuario']) && 
                    ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'superadmin')): ?>
                
                <li><a href="confirmacion.php">CONFIRMACIÓN</a></li>
                <li><a href="administracion.php">ADMINISTRACIÓN</a></li>

            <?php endif; ?>
        </ul>
    </nav>

    <div class="header-actions">
        <div class="admin-status is-active" aria-label="Estado del usuario">
            <span class="status-dot"></span>
            <span>
                <?php 
                // Mostrar nombre y rol del usuario logueado
                if(isset($_SESSION['usuario'])) {
                    $nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';
                    $rol = $_SESSION['usuario']['rol'] ?? 'usuario';
                    
                    // Capitalizar el rol para mostrarlo bonito
                    $rolMostrar = ucfirst($rol);
                    
                    echo htmlspecialchars($nombre) . ' (' . htmlspecialchars($rolMostrar) . ')';
                } else {
                    echo 'Invitado';
                }
                ?>
            </span>
        </div>
        <div class="boton-salir">
            <button><a href="funciones/logout.php">Salir</a></button>
        </div>
    </div>
</header>

<section class="text-prin">
    <div class="dashboard-container">
        <div class="titulo-boton">
            <h1>Certificación de Orden</h1>
            <p>Documento de certificación para orden de publicidad N° <?= $orden['numero_orden'] ?></p>
        </div>
    </div>
</section>

<main class="page-grid">
    <div class="main-card certificacion-container">
        
        <div class="certificacion-header">
            <h1>CERTIFICADO DE PUBLICIDAD</h1>
            <p>Celestial Stereo 104.1 FM - Sistema de Gestión Publicitaria</p>
        </div>

        <div class="certificacion-card">
            <div class="certificacion-info">
                <div class="info-group">
                    <label>N° ORDEN</label>
                    <div class="info-value"><?= $orden['numero_orden'] ?></div>
                </div>

                <div class="info-group">
                    <label>N° PRESUPUESTO</label>
                    <div class="info-value"><?= $orden['numero_presupuesto'] ?: 'N/A' ?></div>
                </div>

                <div class="info-group">
                    <label>NIT CLIENTE</label>
                    <div class="info-value"><?= $orden['nit_cliente'] ?></div>
                </div>

                <div class="info-group">
                    <label>CLIENTE</label>
                    <div class="info-value"><?= $orden['nombre_cliente'] ?></div>
                </div>

                <div class="info-group">
                    <label>PRODUCTO</label>
                    <div class="info-value"><?= $orden['producto'] ?></div>
                </div>

                <div class="info-group">
                    <label>REFERENCIA</label>
                    <div class="info-value"><?= $orden['referencia'] ?></div>
                </div>

                <div class="info-group">
                    <label>FECHA DE ORDEN</label>
                    <div class="info-value"><?= $fechaOrden ?></div>
                </div>

                <div class="info-group">
                    <label>FECHA DE INICIO</label>
                    <div class="info-value"><?= $fechaInicio ?></div>
                </div>

                <div class="info-group">
                    <label>FECHA DE TERMINACIÓN</label>
                    <div class="info-value"><?= $fechaFin ?></div>
                </div>

                <div class="info-group">
                    <label>CUÑAS DIARIAS</label>
                    <div class="info-value"><?= $orden['cunas_dia'] ?> cuñas por día</div>
                </div>

                <div class="info-group">
                    <label>DURACIÓN POR CUÑA</label>
                    <div class="info-value"><?= $orden['duracion'] ?> segundos</div>
                </div>

                <div class="info-group">
                    <label>VALOR TOTAL</label>
                    <div class="info-value" style="color: var(--gold); font-size: 1.2rem;"><?= $valorFormateado ?></div>
                </div>

                <div class="info-group full-width">
                    <label>DÍAS DE PAUTA</label>
                    <div class="info-value"><?= $orden['dias'] ?></div>
                </div>

                <div class="info-group full-width">
                    <label>HORARIOS</label>
                    <div class="info-value"><?= $orden['horarios'] ?></div>
                </div>

                <?php if (!empty($orden['estado']) && $orden['estado'] == 'Anulada'): ?>
                <div class="info-group full-width">
                    <label>ESTADO</label>
                    <div class="info-value" style="color: var(--danger);">ANULADA</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="separator"></div>

            <div class="certificacion-footer">
                <p><strong>Nota:</strong> Este certificado acredita que la orden de publicidad N° <?= $orden['numero_orden'] ?> ha sido registrada en el sistema de gestión publicitaria de Celestial Stereo 104.1 FM para su respectiva programación y emisión según los parámetros establecidos.</p>
                <p style="margin-top: 0.5rem;">Fecha de emisión: <?= date("d/m/Y H:i:s") ?></p>
            </div>
        </div>

        <div class="button-group">
            <a href="ordenes.php" class="btn-regresar">
                ← REGRESAR
            </a>
            <button onclick="window.print()" class="btn-imprimir">
                🖨️ IMPRIMIR CERTIFICADO
            </button>
        </div>

    </div>
</main>

</body>
</html>