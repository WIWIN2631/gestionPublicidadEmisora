<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: clientes.php");
    exit();
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare($conexionBd, "SELECT id, nit, nombre, direccion, telefono, correo FROM clientes WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($resultado);

if (!$cliente) {
    header("Location: clientes.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nit = trim($_POST['nit'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    if ($nit === '' || $nombre === '') {
        $error = "NIT y nombre son obligatorios.";
    } else {
        try {
            $stmtUpdate = mysqli_prepare(
                $conexionBd,
                "UPDATE clientes SET nit = ?, nombre = ?, direccion = ?, telefono = ?, correo = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmtUpdate, "sssssi", $nit, $nombre, $direccion, $telefono, $correo, $id);
            mysqli_stmt_execute($stmtUpdate);

            echo "<script>alert('Cliente actualizado correctamente'); window.location.href='clientes.php';</script>";
            exit();
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                $error = "El NIT ya existe en otro cliente.";
            } else {
                $error = "No se pudo actualizar el cliente.";
            }
        }
    }

    $cliente['nit'] = $nit;
    $cliente['nombre'] = $nombre;
    $cliente['direccion'] = $direccion;
    $cliente['telefono'] = $telefono;
    $cliente['correo'] = $correo;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleLogin.css">
    <title>Editar cliente</title>
</head>
<body>
<div class="screen-active">
    <div class="login-box">
        <div class="logo-login">🎙 Celestial</div>
        <div class="login-text">Stereo 104.1 FM · Clientes</div>
        <h2>Editar cliente</h2>

        <?php if ($error !== ''): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>NIT</label>
                <input type="text" name="nit" required value="<?php echo htmlspecialchars($cliente['nit']); ?>">
            </div>

            <div class="input-group">
                <label>Nombre</label>
                <input type="text" name="nombre" required value="<?php echo htmlspecialchars($cliente['nombre']); ?>">
            </div>

            <div class="input-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>">
            </div>

            <div class="input-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>">
            </div>

            <div class="input-group">
                <label>Correo</label>
                <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn-ingresar">Guardar cambios</button>
            <button type="button" class="btn-crear" onclick="window.location.href='clientes.php'">Regresar</button>
        </form>
    </div>
</div>
</body>
</html>
