<?php

session_start();
include("funciones/bd.php");

$error = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $correo = $_POST['usuario'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conexionBd, 
        "SELECT id, nombre, contraseña, rol FROM usuarios WHERE correo = ?"
    );

    mysqli_stmt_bind_param($stmt, "s", $correo);
    mysqli_stmt_execute($stmt);

    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario && password_verify($password, $usuario['contraseña'])) {

        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol']
        ];

        header("Location: index.php");
        exit();

    } else {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <title>Login - Celestial Stereo 104.1 FM</title>
    <link rel="stylesheet" href="css/styleLogin.css">
</head>

<body>
    <div class="screen-active">
        <div class="login-box">
            <div class="logo-login">🎙 Celestial</div>
            <div class="login-text">Stereo 104.1 FM · La Radio Positiva</div>
            <h2>Ingreso al sistema</h2>

            <?php if($error): ?>
            <div class="alert-error">
                ⚠ Usuario o contraseña incorrectos.
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Correo</label>
                    <input type="text" name="usuario" required>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-ingresar">Ingresar</button>
                <button type="button" class="btn-crear" onclick="window.location.href='registro.php'">
                    Crear cuenta
                </button>
            </form>

            <div class="login-footer">
                <p>Sistema de Gestión Publicitaria - Corporación Juan Valdez</p>
                <p>de Santa Rosa de Cabal</p>
            </div>
        </div>
    </div>
</body>
</html>