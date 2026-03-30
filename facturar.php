<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Verificar que se recibió el ID de la orden
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ordenes.php");
    exit();
}

$idOrden = intval($_GET['id']);

// Obtener los datos de la orden
$stmt = $conexionBd->prepare("
    SELECT * FROM ordenes 
    WHERE id = ?
");
$stmt->bind_param("i", $idOrden);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: ordenes.php");
    exit();
}

$orden = $resultado->fetch_assoc();

// Formatear fechas
$fechaOrden = date("d/m/Y", strtotime($orden['fecha_orden']));
$fechaInicio = date("d/m/Y", strtotime($orden['fecha_inicio']));
$fechaFin = date("d/m/Y", strtotime($orden['fecha_fin']));
$valorFormateado = "$" . number_format($orden['valor'], 0, ',', '.');
$valorLetras = convertirNumeroALetras($orden['valor']);

// ================= CALCULO CORREGIDO DE CUÑAS =================
// Calcular número de días en el período
$fechaInicioObj = new DateTime($orden['fecha_inicio']);
$fechaFinObj = new DateTime($orden['fecha_fin']);
$diferencia = $fechaInicioObj->diff($fechaFinObj);
$numeroDias = $diferencia->days + 1; // +1 para incluir el día final

// Calcular días de pauta según los días seleccionados
$diasSeleccionados = explode(', ', $orden['dias']);
$diasPorSemana = count($diasSeleccionados);

// Calcular número de semanas en el período
$semanas = ceil($numeroDias / 7);

// Calcular total de días de pauta en el período
if ($diasPorSemana == 7) {
    // Si son todos los días, son todos los días del período
    $totalDiasPauta = $numeroDias;
} else {
    // Calcular aproximadamente los días de pauta
    $totalDiasPauta = $diasPorSemana * $semanas;
    // Ajustar para no exceder el número real de días
    if ($totalDiasPauta > $numeroDias) {
        $totalDiasPauta = $numeroDias;
    }
}

// Calcular total de cuñas
$totalCunas = $orden['cunas_dia'] * $totalDiasPauta;
$valorUnitario = $orden['valor'] / $totalCunas;

// Función para convertir número a letras
function convertirNumeroALetras($numero) {
    $unidades = array(
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE',
        'VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'
    );
    
    $decenas = array(
        '', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
    );
    
    $centenas = array(
        '', 'CIEN', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
    );
    
    if ($numero == 0) return 'CERO';
    
    $letras = '';
    
    // Miles
    if ($numero >= 1000) {
        $miles = floor($numero / 1000);
        $resto = $numero % 1000;
        
        if ($miles == 1) {
            $letras .= 'MIL';
        } else {
            $letras .= convertirNumeroALetras($miles) . ' MIL';
        }
        
        if ($resto > 0) {
            $letras .= ' ' . convertirNumeroALetras($resto);
        }
        
        return $letras;
    }
    
    // Centenas
    if ($numero >= 100) {
        $centena = floor($numero / 100);
        $resto = $numero % 100;
        
        if ($centena == 1 && $resto > 0) {
            $letras .= 'CIENTO';
        } else {
            $letras .= $centenas[$centena];
        }
        
        if ($resto > 0) {
            $letras .= ' ' . convertirNumeroALetras($resto);
        }
        
        return $letras;
    }
    
    // Decenas y unidades
    if ($numero >= 30) {
        $decena = floor($numero / 10);
        $unidad = $numero % 10;
        
        $letras .= $decenas[$decena];
        
        if ($unidad > 0) {
            $letras .= ' Y ' . $unidades[$unidad];
        }
    } else {
        $letras .= $unidades[$numero];
    }
    
    return $letras;
}

// Obtener el rol del usuario
$rolUsuario = $_SESSION['usuario']['rol'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleOrdenes.css">
    <link rel="stylesheet" href="css/styleImprimir.css">
    <title>Factura - Celestial Stereo 104.1 FM</title>
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
            <li><a href="ordenes.php" class="active-link">ÓRDENES</a></li>
            <li><a href="anuladas.php">ANULADAS</a></li>
            <?php if(isset($_SESSION['usuario']) && ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'superadmin')): ?>
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
                $nombre = $_SESSION['usuario']['nombre'] ?? 'Usuario';
                $rol = $_SESSION['usuario']['rol'] ?? 'usuario';
                echo htmlspecialchars($nombre) . ' (' . htmlspecialchars(ucfirst($rol)) . ')';
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
            <h1>Factura de Publicidad</h1>
            <p>Factura N° <?= str_pad($orden['numero_orden'], 6, '0', STR_PAD_LEFT) ?></p>
        </div>
    </div>
</section>

<main class="page-grid">
    <div class="main-card factura-container">
        
        <div class="factura-header">
            <h1>FACTURA DE PUBLICIDAD</h1>
            <p>Celestial Stereo 104.1 FM - Sistema de Gestión Publicitaria</p>
        </div>

        <div class="factura-card">
            <!-- Datos de la empresa y factura -->
            <div class="factura-empresa">
                <div class="empresa-info">
                    <h2>Celestial Stereo 104.1 FM</h2>
                    <p>NIT: 900.123.456-7</p>
                    <p>Dirección: Calle 123 # 45-67, Bogotá D.C.</p>
                    <p>Teléfono: (601) 123 4567</p>
                    <p>Email: comercial@celestialstereo.com</p>
                </div>
                <div class="factura-numero">
                    <div class="numero">FACTURA N° <?= str_pad($orden['numero_orden'], 6, '0', STR_PAD_LEFT) ?></div>
                    <div class="fecha">Fecha de emisión: <?= date("d/m/Y") ?></div>
                </div>
            </div>

            <!-- Datos del cliente -->
            <div class="cliente-info">
                <h3>SEÑOR(ES):</h3>
                <p><strong><?= $orden['nombre_cliente'] ?></strong></p>
                <p>NIT: <?= $orden['nit_cliente'] ?></p>
            </div>

            <!-- Detalle de la factura con cálculo corregido -->
            <div class="factura-detalle">
                <table class="detalle-tabla">
                    <thead>
                        <tr>
                            <th>Cantidad</th>
                            <th>Descripción</th>
                            <th>Valor Unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= number_format($totalCunas, 0, ',', '.') ?> cuñas</td>
                            <td>
                                Servicio de publicidad - <?= $orden['producto'] ?><br>
                                <small style="color: var(--muted);">Referencia: <?= $orden['referencia'] ?></small><br>
                                <small style="color: var(--muted);">Período: <?= $fechaInicio ?> al <?= $fechaFin ?></small><br>
                                <small style="color: var(--muted);">Días de pauta: <?= $orden['dias'] ?></small><br>
                                <small style="color: var(--muted);">Cuñas por día: <?= $orden['cunas_dia'] ?></small><br>
                                <small style="color: var(--muted);">Horarios: <?= $orden['horarios'] ?></small>
                            </td>
                            <td><?= "$" . number_format($valorUnitario, 0, ',', '.') ?></td>
                            <td><?= $valorFormateado ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Desglose del cálculo -->
                <div class="factura-letras" style="margin-top: 1rem; background: rgba(201, 168, 76, 0.05);">
                    <p><strong>📊 DESGLOSE DEL CÁLCULO:</strong></p>
                    <p style="font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.5;">
                        Período: <?= $numeroDias ?> días (del <?= $fechaInicio ?> al <?= $fechaFin ?>)<br>
                        Días de pauta por semana: <?= $diasPorSemana ?> día(s)<br>
                        Total días de pauta en el período: <?= $totalDiasPauta ?> día(s)<br>
                        Cuñas por día: <?= $orden['cunas_dia'] ?><br>
                        <strong>Total de cuñas: <?= number_format($totalCunas, 0, ',', '.') ?></strong><br>
                        Valor por cuña: $<?= number_format($valorUnitario, 0, ',', '.') ?>
                    </p>
                </div>
            </div>

            <!-- Totales -->
            <div class="factura-totales">
                <div class="total-line">
                    <div class="label">SUBTOTAL:</div>
                    <div class="value"><?= $valorFormateado ?></div>
                </div>
                <div class="total-line">
                    <div class="label">IVA (19%):</div>
                    <div class="value">$0</div>
                </div>
                <div class="total-line total">
                    <div class="label">TOTAL A PAGAR:</div>
                    <div class="value"><?= $valorFormateado ?></div>
                </div>
            </div>

            <!-- Valor en letras -->
            <div class="factura-letras">
                <p><strong>VALOR EN LETRAS:</strong> <?= $valorLetras ?> PESOS COLOMBIANOS ($<?= number_format($orden['valor'], 0, ',', '.') ?>)</p>
            </div>

            <!-- Notas y condiciones -->
            <div class="factura-footer">
                <div class="footer-notas">
                    <div class="nota">
                        <h4>📌 Condiciones de pago</h4>
                        <p>Pago de contado contra entrega de factura. La orden comenzará a ejecutarse una vez se realice el pago correspondiente.</p>
                    </div>
                    <div class="nota">
                        <h4>🏦 Información bancaria</h4>
                        <p>Banco: Bancolombia<br>
                        Cuenta Corriente: 123-456789-01<br>
                        A nombre: Celestial Stereo 104.1 FM</p>
                    </div>
                </div>

                <div class="firma">
                    <div class="firma-linea">
                        <p>_________________________</p>
                        <p>Firma Autorizada</p>
                        <p>Celestial Stereo 104.1 FM</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="button-group">
            <a href="ordenes.php" class="btn-regresar">
                ← REGRESAR
            </a>
            <button onclick="window.print()" class="btn-imprimir">
                🖨️ IMPRIMIR FACTURA
            </button>
        </div>

    </div>
</main>

</body>
</html>