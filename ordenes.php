<?php
session_start();
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Número automático
$resultNum = mysqli_query($conexionBd, "SELECT MAX(numero_orden) as maximo FROM ordenes");
$filaNum = mysqli_fetch_assoc($resultNum);
$siguienteOrden = $filaNum['maximo'] ? $filaNum['maximo'] + 1 : 1;

// Clientes
$clientes = mysqli_query($conexionBd, "SELECT id, nit, nombre FROM clientes");

// Guardar
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stmtCliente = mysqli_prepare($conexionBd, "SELECT nombre, nit FROM clientes WHERE id = ?");
    mysqli_stmt_bind_param($stmtCliente, "i", $_POST['cliente_id']);
    mysqli_stmt_execute($stmtCliente);
    $resCliente = mysqli_stmt_get_result($stmtCliente);
    $cliente = mysqli_fetch_assoc($resCliente);

    $stmt = mysqli_prepare($conexionBd,
        "INSERT INTO ordenes 
        (numero_orden, numero_presupuesto, cliente_id, nit_cliente, nombre_cliente, producto, referencia,
        fecha_orden, fecha_inicio, fecha_fin, cunas_dia, duracion, valor, horarios, dias)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "iiisssssssiidss",
        $_POST['numero_orden'],
        $_POST['numero_presupuesto'],
        $_POST['cliente_id'],
        $cliente['nit'],
        $cliente['nombre'],
        $_POST['producto'],
        $_POST['referencia'],
        $_POST['fecha_orden'],
        $_POST['fecha_inicio'],
        $_POST['fecha_fin'],
        $_POST['cunas'],
        $_POST['duracion'],
        $_POST['valor'],
        $_POST['horarios'],
        $_POST['dias']
    );

    mysqli_stmt_execute($stmt);

    echo "<script>
        alert('Orden guardada correctamente');
        window.location='ordenes.php';
    </script>";
    exit();
}

// Listado
$ordenes = mysqli_query($conexionBd, "SELECT * FROM ordenes ORDER BY id DESC");

// Obtener rol del usuario
$rolUsuario = $_SESSION['usuario']['rol'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleOrdenes.css?v=20260330b">
    <title>Celestial Stereo 104.1 FM — Sistema de Gestión Publicitaria</title>
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
            <li><a href="ordenes.php" class="active-link" aria-current="page">ÓRDENES</a></li>
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
            <h1>Órdenes de Publicidad</h1>
            <p>Registro y gestión de órdenes de pauta</p>
        </div>
    </div>
</section>

<main class="page-grid">
    <div class="main-card">

        <!-- TABS -->
        <div class="tabs-container">
            <button class="tab-link active" id="tab-lista">LISTA DE ÓRDENES</button>
            <button class="tab-link" id="tab-registro">NUEVA ORDEN</button>
        </div>

        <hr class="tab-separator">

        <!-- LISTA -->
        <section id="lista" class="tab-content active">

            <div class="table-responsive">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>N° Orden</th>
                            <th>N° Presupuesto</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Referencia</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Cuñas/día</th>
                            <th>Duración</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while($o = mysqli_fetch_assoc($ordenes)): ?>
                            <tr>
                                <td><?= $o['numero_orden'] ?></td>
                                <td><?= $o['numero_presupuesto'] ?></td>
                                <td><?= $o['nombre_cliente'] ?></td>
                                <td><?= $o['producto'] ?></td>
                                <td><?= $o['referencia'] ?></td>
                                <td><?= date("d/m/Y", strtotime($o['fecha_inicio'])) ?></td>
                                <td><?= date("d/m/Y", strtotime($o['fecha_fin'])) ?></td>
                                <td><?= $o['cunas_dia'] ?></td>
                                <td><?= $o['duracion'] ?> seg</td>
                                <td>$<?= number_format($o['valor'], 0, ',', '.') ?></td>
                                <td><?= $o['estado'] ?? 'Activa' ?></td>
                                <td class="acciones-cell">
                                    <a class="btn-action factura" href="facturar.php?id=<?= $o['id'] ?>" target="_blank" rel="noopener noreferrer">Factura</a>
                                    <a class="btn-action certificacion" href="certificacion.php?id=<?= $o['id'] ?>" target="_blank" rel="noopener noreferrer">Certificación</a>
                                    <button type="button" class="btn-action anular" 
                                            data-id="<?= $o['id'] ?>" 
                                            data-rol="<?= $rolUsuario ?>">
                                        Anular
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </section>

        <!-- REGISTRO -->
        <section id="registro" class="tab-content">

            <form method="POST" class="form-registro">

                <input type="hidden" name="cliente_id" id="cliente_id">

                <div class="form-grid">

                    <div class="form-group">
                        <label>Número de Orden</label>
                        <input type="number" name="numero_orden" value="<?= $siguienteOrden ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Cliente (NIT)</label>
                        <select id="cliente_select">
                            <option value="">Seleccione</option>
                            <?php while($c = mysqli_fetch_assoc($clientes)): ?>
                                <option value="<?= $c['id'] ?>" data-nombre="<?= $c['nombre'] ?>">
                                    <?= $c['nit'] ?> — <?= $c['nombre'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Número Presupuesto</label>
                        <input type="number" name="numero_presupuesto">
                    </div>

                    <div class="form-group">
                        <label>Nombre del Cliente</label>
                        <input type="text" name="nombre_cliente" id="cliente_nombre" readonly>
                    </div>

                    <div class="form-group">
                        <label>Producto</label>
                        <input type="text" name="producto">
                    </div>

                    <div class="form-group">
                        <label>Fecha de Orden</label>
                        <input type="date" name="fecha_orden" value="<?= date('Y-m-d') ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text" name="referencia">
                    </div>

                    <div class="form-group">
                        <label>Valor ($)</label>
                        <input type="number" name="valor">
                    </div>

                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio">
                    </div>

                    <div class="form-group">
                        <label>Fecha de Terminación</label>
                        <input type="date" name="fecha_fin">
                    </div>

                    <div class="form-group">
                        <label>No. de Cuñas Diarias</label>
                        <input type="number" name="cunas">
                    </div>

                    <div class="form-group">
                        <label>Duración (segundos)</label>
                        <input type="number" name="duracion">
                    </div>

                </div>

                <!-- DÍAS -->
                <div id="dias-container">
                    <label><input type="checkbox" value="Lunes"><span>Lunes</span></label>
                    <label><input type="checkbox" value="Martes"><span>Martes</span></label>
                    <label><input type="checkbox" value="Miércoles"><span>Miércoles</span></label>
                    <label><input type="checkbox" value="Jueves"><span>Jueves</span></label>
                    <label><input type="checkbox" value="Viernes"><span>Viernes</span></label>
                    <label><input type="checkbox" value="Sábado"><span>Sábado</span></label>
                    <label><input type="checkbox" value="Domingo"><span>Domingo</span></label>
                </div>

                <!-- HORARIOS -->
                <div class="form-group">
                    <label>Horarios</label>
                    <div id="horarios-container">
                        <input type="time" class="hora">
                        <button type="button" id="addHora">+ Añadir horario</button>
                    </div>
                </div>

                <input type="hidden" name="dias" id="ord-dias">
                <input type="hidden" name="horarios" id="ord-horarios">

                <!-- BOTONES -->
                <div class="orden-actions-row">
                    <button type="submit" class="btn btn-gold">💾 Guardar Orden</button>
                    <button type="reset" class="btn btn-outline">Limpiar</button>
                </div>

            </form>
        </section>

    </div>
</main>

<script src="js/ordenes.js?v=20260330b"></script>

</body>
</html>