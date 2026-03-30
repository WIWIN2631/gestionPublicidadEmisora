<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// ================= CONSULTAS PARA ESTADÍSTICAS =================

// Total de órdenes activas (no anuladas)
$queryOrdenesActivas = "SELECT COUNT(*) as total FROM ordenes WHERE estado IS NULL OR estado != 'Anulada'";
$resultOrdenesActivas = mysqli_query($conexionBd, $queryOrdenesActivas);
$ordenesActivas = mysqli_fetch_assoc($resultOrdenesActivas);
$totalOrdenesActivas = $ordenesActivas['total'];

// Total facturado este mes (valor de órdenes activas creadas en el mes actual)
$mesActual = date('Y-m');
$queryFacturadoMes = "SELECT SUM(valor) as total FROM ordenes 
                      WHERE (estado IS NULL OR estado != 'Anulada') 
                      AND DATE_FORMAT(fecha_orden, '%Y-%m') = '$mesActual'";
$resultFacturadoMes = mysqli_query($conexionBd, $queryFacturadoMes);
$facturadoMes = mysqli_fetch_assoc($resultFacturadoMes);
$totalFacturadoMes = $facturadoMes['total'] ?? 0;

// Total de clientes activos (clientes que tienen órdenes activas)
$queryClientesActivos = "SELECT COUNT(DISTINCT cliente_id) as total FROM ordenes 
                         WHERE (estado IS NULL OR estado != 'Anulada')";
$resultClientesActivos = mysqli_query($conexionBd, $queryClientesActivos);
$clientesActivos = mysqli_fetch_assoc($resultClientesActivos);
$totalClientesActivos = $clientesActivos['total'];

// Total de órdenes anuladas
$queryOrdenesAnuladas = "SELECT COUNT(*) as total FROM ordenes WHERE estado = 'Anulada'";
$resultOrdenesAnuladas = mysqli_query($conexionBd, $queryOrdenesAnuladas);
$ordenesAnuladas = mysqli_fetch_assoc($resultOrdenesAnuladas);
$totalOrdenesAnuladas = $ordenesAnuladas['total'];

// ================= ÓRDENES RECIENTES (ÚLTIMAS 5) =================
$queryOrdenesRecientes = "SELECT numero_orden, nombre_cliente, producto, valor, estado 
                          FROM ordenes 
                          ORDER BY id DESC 
                          LIMIT 5";
$resultOrdenesRecientes = mysqli_query($conexionBd, $queryOrdenesRecientes);

// ================= ACTIVIDAD RECIENTE =================
// Obtener las últimas 5 órdenes con fecha para mostrar actividad
$queryActividadReciente = "SELECT numero_orden, nombre_cliente, producto, fecha_orden, estado 
                           FROM ordenes 
                           ORDER BY id DESC 
                           LIMIT 5";
$resultActividadReciente = mysqli_query($conexionBd, $queryActividadReciente);

// Función para formatear el valor en pesos colombianos
function formatearValor($valor) {
    if ($valor == 0 || $valor == null) {
        return '$0';
    }
    return '$' . number_format($valor, 0, ',', '.');
}

// Función para obtener el día de la semana en español
function obtenerDiaSemana($fecha) {
    $dias = array(
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    );
    $nombreDia = date('l', strtotime($fecha));
    return $dias[$nombreDia];
}

// Calcular variación de órdenes esta semana vs semana pasada
$semanaActual = date('Y-m-d', strtotime('monday this week'));
$semanaPasada = date('Y-m-d', strtotime('monday last week'));

$queryOrdenesSemanaActual = "SELECT COUNT(*) as total FROM ordenes WHERE fecha_orden >= '$semanaActual'";
$resultSemanaActual = mysqli_query($conexionBd, $queryOrdenesSemanaActual);
$ordenesSemanaActual = mysqli_fetch_assoc($resultSemanaActual)['total'];

$queryOrdenesSemanaPasada = "SELECT COUNT(*) as total FROM ordenes WHERE fecha_orden >= '$semanaPasada' AND fecha_orden < '$semanaActual'";
$resultSemanaPasada = mysqli_query($conexionBd, $queryOrdenesSemanaPasada);
$ordenesSemanaPasada = mysqli_fetch_assoc($resultSemanaPasada)['total'];

$variacion = $ordenesSemanaPasada > 0 ? round((($ordenesSemanaActual - $ordenesSemanaPasada) / $ordenesSemanaPasada) * 100) : 100;
$variacionTexto = ($variacion >= 0 ? '↑ ' : '↓ ') . abs($variacion) . '% ESTA SEMANA';

// Calcular variación de facturación
$queryFacturadoMesAnterior = "SELECT SUM(valor) as total FROM ordenes 
                              WHERE (estado IS NULL OR estado != 'Anulada') 
                              AND DATE_FORMAT(fecha_orden, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m')";
$resultFacturadoMesAnterior = mysqli_query($conexionBd, $queryFacturadoMesAnterior);
$facturadoMesAnterior = mysqli_fetch_assoc($resultFacturadoMesAnterior)['total'] ?? 0;

$variacionFacturacion = $facturadoMesAnterior > 0 ? round((($totalFacturadoMes - $facturadoMesAnterior) / $facturadoMesAnterior) * 100) : 100;
$variacionFacturacionTexto = ($variacionFacturacion >= 0 ? '+ ' : '- ') . abs($variacionFacturacion) . '% vs anterior';

// Obtener fecha actual formateada
$fechaActual = date('l, j \d\e F \d\e Y');
$diasSemana = array(
    'Monday' => 'lunes',
    'Tuesday' => 'martes',
    'Wednesday' => 'miércoles',
    'Thursday' => 'jueves',
    'Friday' => 'viernes',
    'Saturday' => 'sábado',
    'Sunday' => 'domingo'
);
$fechaActual = $diasSemana[date('l')] . ', ' . date('j \d\e F \d\e Y');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="../img/logo.avif">
    <link rel="shortcut icon" href="../img/logo.avif">
    <link rel="stylesheet" href="../css/style.css">
    <title>Celestial Stereo 104.1 FM - Dashboard</title>
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
                <h1>Dashboard</h1>
                <p>Resumen de gestión publicitaria · <?= $fechaActual ?></p>
            </div>
            <button class="nueva-orden"><a href="ordenes.php">+ NUEVA ORDEN</a></button>
        </div>
    </section>

    <section class="kpis-container">
        <div class="card">
            <h2 class="kpi-value"><?= $totalOrdenesActivas ?></h2>
            <span class="kpi-title">ÓRDENES ACTIVAS</span>
            <div class="trend-badge positive"><?= $variacionTexto ?></div>
        </div>

        <div class="card">
            <h2 class="kpi-value"><?= formatearValor($totalFacturadoMes) ?></h2>
            <span class="kpi-title">Facturado este mes</span>
            <div class="trend-badge positive"><?= $variacionFacturacionTexto ?></div>
        </div>

        <div class="card">
            <h2 class="kpi-value"><?= $totalClientesActivos ?></h2>
            <span class="kpi-title">Clientes activos</span>
            <div class="trend-badge positive"><?= $totalClientesActivos ?> ACTIVOS</div>
        </div>

        <div class="card">
            <h2 class="kpi-value"><?= $totalOrdenesAnuladas ?></h2>
            <span class="kpi-title">Órdenes anuladas</span>
            <div class="trend-badge danger">REQUIEREN REVISIÓN</div>
        </div>
    </section>

    <main class="dashboard-grid">
        <section class="recent-orders">
            <div class="card-header">
                <h3>Órdenes Recientes</h3>
                <span>ÚLTIMAS 5</span>
            </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>N° ORDEN</th>
                        <th>CLIENTE</th>
                        <th>PRODUCTO</th>
                        <th>VALOR</th>
                        <th>ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($resultOrdenesRecientes) > 0): ?>
                        <?php while($orden = mysqli_fetch_assoc($resultOrdenesRecientes)): ?>
                            <tr>
                                <td><?= $orden['numero_orden'] ?></td>
                                <td><?= htmlspecialchars($orden['nombre_cliente']) ?></td>
                                <td><?= htmlspecialchars($orden['producto']) ?></td>
                                <td><?= formatearValor($orden['valor']) ?></td>
                                <td>
                                    <?php 
                                    $estado = $orden['estado'] ?? 'Activa';
                                    $estadoClass = ($estado == 'Anulada') ? 'inactive' : 'active';
                                    $estadoText = ($estado == 'Anulada') ? 'ANULADA' : 'ACTIVA';
                                    ?>
                                    <span class="status-pill <?= $estadoClass ?>"><?= $estadoText ?></span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay órdenes registradas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <aside class="recent-activity">
            <div class="card-header">
                <h3>Actividad Reciente</h3>
            </div>
            <p class="date-label">ÚLTIMAS ÓRDENES</p>
            
            <div class="activity-feed">
                <?php if(mysqli_num_rows($resultActividadReciente) > 0): ?>
                    <?php while($actividad = mysqli_fetch_assoc($resultActividadReciente)): ?>
                        <div class="activity-item">
                            <div class="activity-text">
                                <strong>Orden #<?= $actividad['numero_orden'] ?> creada</strong>
                                <small>
                                    <?php 
                                    $fecha = new DateTime($actividad['fecha_orden']);
                                    echo $fecha->format('h:i a') . ' · ' . htmlspecialchars($actividad['nombre_cliente']);
                                    ?>
                                </small>
                            </div>
                            <?php 
                            $estado = $actividad['estado'] ?? 'Activa';
                            $tagClass = ($estado == 'Anulada') ? 'tag-warning' : 'tag-new';
                            $tagText = ($estado == 'Anulada') ? 'ANULADA' : 'NUEVA';
                            ?>
                            <span class="tag <?= $tagClass ?>"><?= $tagText ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <div class="activity-text">
                            <strong>No hay actividad reciente</strong>
                            <small>Esperando nuevas órdenes</small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </main>
    
    <script src="../js/clientes.js"></script>
</body>
</html>