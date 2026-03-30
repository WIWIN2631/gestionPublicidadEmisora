<?php
include("funciones/bd.php");

$error = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Las contraseñas no coinciden";
    } else {

        $check = mysqli_prepare($conexionBd, "SELECT id FROM usuarios WHERE correo = ?");
        mysqli_stmt_bind_param($check, "s", $correo);
        mysqli_stmt_execute($check);
        $res = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($res) > 0) {
            $error = "El correo ya existe";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conexionBd, 
                "INSERT INTO usuarios (nombre, correo, contraseña, rol) VALUES (?, ?, ?, 'user')"
            );

            mysqli_stmt_bind_param($stmt, "sss", $nombre, $correo, $hash);

            if (mysqli_stmt_execute($stmt)) {
                $exito = "Usuario registrado exitosamente";
            } else {
                $error = "Error al registrar";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <title>Registro</title>
    <link rel="stylesheet" href="css/styleLogin.css">
</head>

<body>
<div class="screen-active">
    <div class="login-box">
        <div class="logo-login">🎙 Celestial</div>
        <div class="login-text">Stereo 104.1 FM · Registro de usuarios</div>
        <h2>Crear cuenta</h2>

        <?php if($error): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if($exito): ?>
            <div class="alert-success"><?php echo htmlspecialchars($exito); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">
                <label>Nombre</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="input-group">
                <label>Correo</label>
                <input type="email" name="correo" required>
            </div>

            <div class="input-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="confirm" required>
            </div>

            <button type="submit" class="btn-ingresar">Registrar</button>
            <button type="button" class="btn-crear" onclick="window.location.href='login.php'">
                Regresar
            </button>

        </form>

        <div class="login-footer">
            <p>Acceso de usuarios para el sistema de gestión publicitaria</p>
            <p>Celestial Stereo 104.1 FM</p>
        </div>
    </div>
</div>
</body>
</html>