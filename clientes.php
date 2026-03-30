<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// 🔍 BUSQUEDA
$busqueda = "";

if (isset($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];

    $stmt = mysqli_prepare($conexionBd,
        "SELECT * FROM clientes WHERE nit LIKE ? OR nombre LIKE ?"
    );

    $like = "%" . $busqueda . "%";
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

} else {
    $resultado = mysqli_query($conexionBd, "SELECT * FROM clientes");
}

// ➕ REGISTRAR CLIENTE
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nit = $_POST['nit'];
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];

    $stmt = mysqli_prepare($conexionBd,
        "INSERT INTO clientes (nit, nombre, direccion, telefono, correo)
         VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "sssss", $nit, $nombre, $direccion, $telefono, $correo);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Cliente registrado correctamente');
            window.location.href='clientes.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Error: el cliente ya existe o hubo un problema');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleClientes.css?v=20260330b">
    <title>Clientes</title>
</head>

<body>

<header>
    <div class="topbar-brand">
        🎙 Celestial <span>104.1 FM</span>
    </div>

    <nav> 
        <ul>
            <li><a href="index.php">DASHBOARD</a></li>
            <li><a href="clientes.php" class="active-link" aria-current="page">CLIENTES</a></li>
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
            <h1>Clientes</h1>
            <p>Gestión de clientes publicitarios</p>
        </div>
    </div>
</section>

<main class="page-grid">
<div class="main-card">

    <!-- TABS -->
    <div class="tabs-container">
        <button class="tab-link active" id="tab-lista">LISTA DE CLIENTES</button>
        <button class="tab-link" id="tab-registro">REGISTRAR CLIENTE</button>
    </div>

    <hr class="tab-separator">

    <!-- LISTA -->
    <section id="section-lista" class="tab-content active">

        <form method="GET" class="search-bar">
            <input 
                type="text" 
                name="buscar"
                placeholder="Buscar por NIT o nombre..."
                value="<?php echo htmlspecialchars($busqueda); ?>"
            >
            <button type="submit" class="btn-buscar">BUSCAR</button>
        </form>

        <div class="table-responsive">
            <table class="main-table">
                <thead>
                    <tr>
                        <th>NIT</th>
                        <th>NOMBRE</th>
                        <th>DIRECCIÓN</th>
                        <th>TELÉFONO</th>
                        <th>CORREO</th>
                        <th>FECHA REGISTRO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($fila = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?php echo $fila['nit']; ?></td>
                        <td><?php echo $fila['nombre']; ?></td>
                        <td><?php echo $fila['direccion']; ?></td>
                        <td><?php echo $fila['telefono']; ?></td>
                        <td><?php echo $fila['correo']; ?></td>

                        <!-- 🆕 FECHA FORMATEADA -->
                        <td>
                            <?php 
                            if (!empty($fila['fecha_registro'])) {
                                echo date("d/m/Y", strtotime($fila['fecha_registro']));
                            } else {
                                echo "—";
                            }
                            ?>
                        </td>

                        <td class="acciones-cell">
                            <!-- EDITAR -->
                            <a class="btn-action edit" href="editarcliente.php?id=<?php echo $fila['id']; ?>">
                                Editar
                            </a>

                            <!-- ELIMINAR -->
                            <a 
                                class="btn-action delete btn-eliminar" 
                                href="eliminarcliente.php?id=<?php echo $fila['id']; ?>"
                                data-nombre="<?php echo $fila['nombre']; ?>"
                            >
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </section>

    <!-- REGISTRO -->
    <section id="section-registro" class="tab-content">

        <div class="registro-clientes-tex">
            <div class="titulo-clientes-registro">
                <h1>Registrar Nuevo Cliente</h1>
                <p>Módulo Celestial Stereo — Clientes</p>
            </div>
        </div>

        <form method="POST" class="form-registro">

            <div class="form-grid">

                <div class="form-group">
                    <label>NIT / CÉDULA</label>
                    <input type="text" name="nit" required>
                </div>

                <div class="form-group">
                    <label>NOMBRE</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="form-group">
                    <label>DIRECCIÓN</label>
                    <input type="text" name="direccion">
                </div>

                <div class="form-group">
                    <label>TELÉFONO</label>
                    <input type="text" name="telefono">
                </div>

                <div class="form-group">
                    <label>CORREO</label>
                    <input type="email" name="correo">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">GUARDAR CLIENTE</button>
                <button type="reset" class="btn-limpiar">LIMPIAR</button>
            </div>

        </form>

    </section>

</div>
</main>

<script src="js/clientes.js?v=20260330b"></script>

</body>
</html>