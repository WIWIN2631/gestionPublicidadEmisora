<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Consulta usuarios
$query = "SELECT id, nombre, rol FROM usuarios";
$resultado = mysqli_query($conexionBd, $query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleAdmin.css">
    <title>Celestial Stereo 104.1 FM — Administración</title>
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
                <li><a href="anuladas.php">ANULADAS</a></li>
                <li><a href="confirmacion.php">CONFIRMACIÓN</a></li>
                <li><a href="administracion.php" class="active-link">ADMINISTRACIÓN</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <div class="admin-status is-active">
                <span class="status-dot"></span>
                <span><?php echo $_SESSION['usuario']['nombre']; ?></span>
            </div>
            <div class="boton-salir">
                <button><a href="funciones/logout.php">Salir</a></button>
            </div>
        </div>
    </header>

    <section class="text-prin">
        <div class="dashboard-container">
            <div class="titulo-boton">
                <h1>Administración</h1>
                <p>Gestión administrativa del sistema de publicidad</p>
            </div>
        </div>
    </section>

    <main class="page-grid">
        <div class="main-card">

            <!-- SOLO UNA PESTAÑA -->
            <div class="tabs-container">
                <button class="tab-link active">LISTA DE USUARIOS</button>
            </div>

            <hr class="tab-separator">

            <section class="tab-content active">

                <div class="search-bar">
                    <input type="text" placeholder="Buscar por usuario o rol...">
                    <button type="button" class="btn-buscar">BUSCAR</button>
                </div>

                <table class="main-table">
                    <thead>
                        <tr>
                            <th>USUARIO</th>
                            <th>ROL</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo $fila['nombre']; ?></td>
                            <td><?php echo ucfirst($fila['rol']); ?></td>
                            <td class="acciones-cell">
                                <a class="btn-action edit" href="editarusuario.php?id=<?php echo $fila['id']; ?>">
                                    Editar
                                </a>

                                <a class="btn-action delete" href="eliminarusuario.php?id=<?php echo $fila['id']; ?>">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>

            </section>

        </div>
    </main>

</body>
</html>