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
    
    // Normalizar el rol
    $rol = strtolower(trim($rol));
    
    // Lista de roles válidos según tu ENUM
    $rolesValidos = ['user', 'admin', 'superadmin'];
    
    // Validar que el rol sea válido
    if (!in_array($rol, $rolesValidos)) {
        echo "<script>
            alert('Rol no válido. Los roles permitidos son: user, admin, superadmin');
            window.location.href='editarusuario.php?id=$id';
        </script>";
        exit();
    }

    // 🚨 PROTECCIÓN: evitar modificar superadmin
    if ($usuario['rol'] === 'superadmin') {
        echo "<script>
            alert('No se puede modificar un superadmin');
            window.location.href='administracion.php';
        </script>";
        exit();
    }

    $stmt = mysqli_prepare($conexionBd, "UPDATE usuarios SET rol = ? WHERE id = ?");
    
    if (!$stmt) {
        die("Error en la preparación: " . mysqli_error($conexionBd));
    }
    
    mysqli_stmt_bind_param($stmt, "si", $rol, $id);

    if (mysqli_stmt_execute($stmt)) {
        $rolMostrar = ($rol == 'user') ? 'Usuario' : ucfirst($rol);
        echo "<script>
            alert('Rol actualizado correctamente a: " . $rolMostrar . "');
            window.location.href='administracion.php';
        </script>";
        exit();
    } else {
        $error = mysqli_stmt_error($stmt);
        echo "<script>
            alert('Error al actualizar: " . addslashes($error) . "');
            window.location.href='administracion.php';
        </script>";
        exit();
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
    <style>
        .info-message {
            color: var(--gold);
            background: rgba(201, 168, 76, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.8rem;
            text-align: center;
        }
        .warning-message {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.8rem;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="screen-active">
    <div class="login-box">
        <div class="logo-login">🎙 Celestial</div>
        <div class="login-text">Stereo 104.1 FM · Gestión de usuarios</div>
        <h2>Editar usuario</h2>

        <?php if ($usuario['rol'] === 'superadmin'): ?>
            <div class="warning-message">
                ⚠️ Este usuario es SUPERADMIN y no puede ser modificado
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">
                <label>Nombre</label>
                <input type="text" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled>
            </div>

            <div class="input-group">
                <label>Correo</label>
                <input type="text" value="<?php echo htmlspecialchars($usuario['correo']); ?>" disabled>
            </div>

            <div class="input-group">
                <label>Rol actual: <strong>
                    <?php 
                    if($usuario['rol'] == 'user') echo 'Usuario';
                    elseif($usuario['rol'] == 'admin') echo 'Admin';
                    elseif($usuario['rol'] == 'superadmin') echo 'Superadmin';
                    ?>
                </strong></label>
                <select name="rol" <?php echo ($usuario['rol'] === 'superadmin') ? 'disabled' : ''; ?>>
                    <option value="user" <?php if($usuario['rol'] == "user") echo "selected"; ?>>Usuario</option>
                    <option value="admin" <?php if($usuario['rol'] == "admin") echo "selected"; ?>>Admin</option>
                </select>
                <?php if ($usuario['rol'] === 'superadmin'): ?>
                    <small style="color: #ff6b6b; display: block; margin-top: 5px;">
                        Los superadministradores no pueden ser modificados
                    </small>
                <?php endif; ?>
            </div>

            <?php if ($usuario['rol'] !== 'superadmin'): ?>
                <button type="submit" class="btn-ingresar">Guardar cambios</button>
            <?php endif; ?>
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