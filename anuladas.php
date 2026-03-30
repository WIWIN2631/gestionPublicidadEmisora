<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Traer todas las órdenes anuladas
$ordenesAnuladas = mysqli_query($conexionBd, "
    SELECT o.numero_orden, o.nombre_cliente, a.motivo, a.comentarios, a.fecha_anulacion
    FROM ordenes_anuladas a
    JOIN ordenes o ON o.numero_orden = a.numero_orden
    ORDER BY a.fecha_anulacion DESC
");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleAnuladas.css?v=20260330c">
    <title>Celestial Stereo 104.1 FM — Órdenes Anuladas</title>
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
            <li><a href="ordenes.php">ÓRDENES</a></li>
            <li><a href="anuladas.php" class="active-link">ANULADAS</a></li>

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
            <h1>Anuladas</h1>
            <p>Gestión de órdenes de publicidad anuladas</p>
        </div>
    </div>
</section>

<main class="page-grid">
    <div class="main-card">
        <div class="tabs-container">
            <button class="tab-link active" id="tab-lista">LISTA DE ANULADAS</button>
        </div>

        <hr class="tab-separator">

        <!-- LISTA DE ANULADAS -->
        <section id="section-lista" class="tab-content active">
            <div class="search-bar">
                <input type="text" placeholder="Buscar por ID o cliente..." id="input-busqueda">
                <button type="button" class="btn-buscar">BUSCAR</button>
            </div>

            <div class="table-responsive">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>ID ORDEN</th>
                            <th>CLIENTE</th>
                            <th>FECHA ANULACIÓN</th>
                            <th>MOTIVO</th>
                            <th style="display:none;">COMENTARIOS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($o = mysqli_fetch_assoc($ordenesAnuladas)): ?>
                        <tr>
                            <td><?= $o['numero_orden'] ?></td>
                            <td><?= $o['nombre_cliente'] ?></td>
                            <td><?= date("Y-m-d H:i", strtotime($o['fecha_anulacion'])) ?></td>
                            <td><?= $o['motivo'] ?></td>
                            <td style="display:none;"><?= $o['comentarios'] ?></td>
                            <td class="acciones-cell">
                                <a href="revisar.php?orden=<?= $o['numero_orden'] ?>" class="btn-action view">Revisar</a>
                                <button type="button" class="btn-action restore" onclick="window.location.href='restaurarorden.php?orden=<?= $o['numero_orden'] ?>'">↩ Restaurar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script src="js/anuladas.js?v=20260330c"></script>
</body>
</html>