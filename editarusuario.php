<?php
session_start();
include("funciones/bd.php");

// 🚫 VALIDAR SUPERADMIN
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'superadmin') {
    echo "<script>
        alert('No tienes permisos de superadministrador');
        window.location.href='administracion.php';
    </script>";
    exit();
}

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = $_GET['id'];

// Obtener datos actuales
$stmt = mysqli_prepare($conexionBd, "SELECT nombre, correo, rol FROM usuarios WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);

if (!$usuario) {
    die("Usuario no encontrado");
}

// ACTUALIZAR SOLO ROL
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rol = $_POST['rol'];

    // 🚨 PROTECCIÓN: evitar modificar superadmin
    if ($usuario['rol'] === 'superadmin') {
        echo "<script>
            alert('No se puede modificar un superadmin');
            window.location.href='administracion.php';
        </script>";
        exit();
    }

    $stmt = mysqli_prepare($conexionBd, 
        "UPDATE usuarios SET rol=? WHERE id=?"
    );

    mysqli_stmt_bind_param($stmt, "si", $rol, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Rol actualizado correctamente');
            window.location.href='administracion.php';
        </script>";
        exit();
    } else {
        echo "Error al actualizar";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <title>Editar usuario</title>
    <link rel="stylesheet" href="css/styleLogin.css">
</head>

<body>
<div class="screen-active">
    <div class="login-box">
        <div class="logo-login">🎙 Celestial</div>
        <div class="login-text">Stereo 104.1 FM · Gestión de usuarios</div>
        <h2>Editar usuario</h2>

        <form method="POST">

            <div class="input-group">
                <label>Nombre</label>
                <input type="text" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled>
            </div>

            <div class="input-group">
                <label>Rol</label>
                <select name="rol">
                    <option value="usuario" <?php if($usuario['rol']==="usuario") echo "selected"; ?>>Usuario</option>
                    <option value="admin" <?php if($usuario['rol']==="admin") echo "selected"; ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn-ingresar">Guardar cambios</button>
            <button type="button" class="btn-crear" onclick="window.location.href='administracion.php'">Regresar</button>

        </form>

        <div class="login-footer">
            <p>Actualización controlada de roles</p>
            <p>Solo disponible para superadministrador</p>
        </div>
    </div>
</div>
</body>
</html>