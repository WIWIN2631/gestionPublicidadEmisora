<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['usuario']) || 
   ($_SESSION['usuario']['rol'] !== 'admin' && $_SESSION['usuario']['rol'] !== 'superadmin')) {

    header("Location: index.php");
    exit();
}

include("funciones/bd.php");

// Consulta usuarios
$busqueda = "";

if (isset($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];

    $stmt = mysqli_prepare($conexionBd, 
        "SELECT id, nombre, rol FROM usuarios 
         WHERE nombre LIKE ? OR rol LIKE ?"
    );

    $like = "%" . $busqueda . "%";

    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

} else {
    $query = "SELECT id, nombre, rol FROM usuarios";
    $resultado = mysqli_query($conexionBd, $query);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleAdmin.css?v=20260330d">
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

            <?php if(isset($_SESSION['usuario']) && 
                    ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'superadmin')): ?>
                
                <li><a href="confirmacion.php">CONFIRMACIÓN</a></li>
                <li><a href="administracion.php" class="active-link">ADMINISTRACIÓN</a></li>

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
                <h1>Administración</h1>
                <p>Gestión administrativa del sistema de publicidad</p>
            </div>
        </div>
    </section>

    <main class="page-grid">
        <div class="main-card">

            <div class="tabs-container">
                <button class="tab-link active">LISTA DE USUARIOS</button>
            </div>

            <hr class="tab-separator">

            <section class="tab-content active">

                <form method="GET" class="search-bar">
                    <input 
                        type="text" 
                        name="buscar"
                        placeholder="Buscar por usuario o rol..."
                        value="<?php echo htmlspecialchars($busqueda); ?>"
                    >
                    <button type="submit" class="btn-buscar">BUSCAR</button>
                </form>

                <div class="table-responsive">
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

                                    <!-- EDITAR -->
                                    <a class="btn-action edit" href="editarusuario.php?id=<?php echo $fila['id']; ?>">
                                        Editar
                                    </a>

                                    <!-- ELIMINAR (OCULTO PARA SUPERADMIN) -->
                                    <?php if($fila['rol'] !== 'superadmin'): ?>
                                    <a 
                                        class="btn-action delete btn-eliminar" 
                                        href="eliminarusuario.php?id=<?php echo $fila['id']; ?>"
                                        data-nombre="<?php echo $fila['nombre']; ?>"
                                    >
                                        Eliminar
                                    </a>
                                    <?php endif; ?>

                                </td>
                            </tr>
                            <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>

            </section>

        </div>
    </main>

    <script src="js/administracion.js"></script>

</body>
</html>