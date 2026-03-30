<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("funciones/bd.php");

// Obtener todas las órdenes disponibles para llenar el select
$ordenesDisponibles = [];
$queryOrdenes = "SELECT numero_orden FROM ordenes ORDER BY numero_orden DESC";
$resultadoOrdenes = $conexionBd->query($queryOrdenes);
while ($row = $resultadoOrdenes->fetch_assoc()) {
    $ordenesDisponibles[] = $row['numero_orden'];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/avif" href="img/logo.avif">
    <link rel="stylesheet" href="css/styleConfirmacion.css">
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
            <li><a href="ordenes.php">ÓRDENES</a></li>
            <li><a href="anuladas.php">ANULADAS</a></li>

            <?php if(isset($_SESSION['usuario']) && 
                    ($_SESSION['usuario']['rol'] === 'admin' || $_SESSION['usuario']['rol'] === 'superadmin')): ?>
                
                <li><a href="confirmacion.php" class="active-link">CONFIRMACIÓN</a></li>
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
                <h1>Confirmación</h1>
                <p>Envío de confirmación de órdenes por correo electrónico</p>
            </div>
        </div>
    </section>

    <main class="page-grid">
        <div class="main-card">
            <div class="registro-clientes-tex">
                <div class="titulo-clientes-registro">
                    <h1>Confirmación de Orden</h1>
                    <p>Módulo Celestial Stereo — Notificación por correo</p>
                </div>
            </div>

            <div class="confirmacion-layout">
                <form class="form-registro form-confirmacion" id="form-confirmacion">
                    <div class="form-grid form-grid-confirmacion">
                        <div class="form-group">
                            <label>NIT DEL CLIENTE</label>
                            <input type="text" id="confirm-nit" readonly placeholder="Seleccione una orden">
                        </div>

                        <div class="form-group">
                            <label>N° ORDEN</label>
                            <select id="confirm-orden" required>
                                <option value="">Seleccione una orden</option>
                                <?php foreach ($ordenesDisponibles as $orden): ?>
                                    <option value="<?php echo $orden; ?>"><?php echo $orden; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label>CORREO DEL CLIENTE</label>
                            <input type="email" id="confirm-correo" required>
                        </div>
                    </div>

                    <div class="token-toolbar">
                        <button type="button" class="token-btn" data-token="Orden N°: ">N° Orden</button>
                        <button type="button" class="token-btn" data-token="Producto: ">Producto</button>
                        <button type="button" class="token-btn" data-token="Referencia: ">Referencia</button>
                        <button type="button" class="token-btn" data-token="Vigencia: ">Fecha Vigencia</button>
                        <button type="button" class="token-btn" data-token="Cuñas diarias: ">Cuñas día</button>
                        <button type="button" class="token-btn" data-token="Días de pauta: ">Días Pautas</button>
                        <button type="button" class="token-btn" data-token="Horarios: ">Horarios</button>
                    </div>

                    <div class="form-group full-width">
                        <label>ASUNTO</label>
                        <input type="text" id="confirm-asunto" value="Confirmación de orden de publicidad" required>
                    </div>

                    <div class="form-group full-width">
                        <label>CUERPO DEL MENSAJE</label>
                        <textarea id="confirm-mensaje" rows="12" required>Cordial saludo,

A continuación relacionamos la orden recibida.

</textarea>
                    </div>

                    <div class="confirmacion-status" id="confirmacion-status" aria-live="polite"></div>

                    <div class="confirmacion-actions">
                        <button type="submit" class="btn-save">ENVIAR CONFIRMACIÓN</button>
                    </div>
                </form>

                <aside class="preview-card">
                    <h2>Vista previa</h2>
                    <p class="preview-text">Contenido que recibirá el cliente según la configuración actual.</p>
                    <div class="preview-mail">
                        <div class="preview-mail-top">
                            <span>Para:</span>
                            <strong id="preview-correo">Seleccione una orden</strong>
                        </div>
                        <div class="preview-mail-top">
                            <span>Asunto:</span>
                            <strong id="preview-asunto">Confirmación de orden de publicidad</strong>
                        </div>
                        <div class="preview-mail-body" id="preview-mensaje"></div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <script>
        // Función para actualizar la vista previa
        function actualizarVistaPrevia() {
            const correo = document.getElementById('confirm-correo').value;
            const asunto = document.getElementById('confirm-asunto').value;
            const mensaje = document.getElementById('confirm-mensaje').value;
            
            document.getElementById('preview-correo').textContent = correo || 'No especificado';
            document.getElementById('preview-asunto').textContent = asunto;
            document.getElementById('preview-mensaje').innerHTML = mensaje.replace(/\n/g, '<br>');
        }
        
        // Evento cuando se selecciona una orden
        document.getElementById('confirm-orden').addEventListener('change', function() {
            const numeroOrden = this.value;
            
            if (!numeroOrden) {
                // Limpiar campos si no hay orden seleccionada
                document.getElementById('confirm-nit').value = '';
                document.getElementById('confirm-correo').value = '';
                document.getElementById('confirm-mensaje').value = 'Cordial saludo,\n\nA continuación relacionamos la orden recibida.\n\n';
                actualizarVistaPrevia();
                return;
            }
            
            // Mostrar mensaje de carga
            const statusDiv = document.getElementById('confirmacion-status');
            statusDiv.innerHTML = '<span style="color: blue;">Cargando datos de la orden...</span>';
            
            // Hacer petición AJAX para obtener los datos de la orden
            fetch('funciones/obtenerdatosorden.php?numero_orden=' + numeroOrden)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    console.log('Datos recibidos:', data); // Para depuración
                    
                    // Llenar el NIT
                    document.getElementById('confirm-nit').value = data.nit_cliente || '';
                    
                    // Generar correo basado en el nombre del cliente
                    let correoGenerado = '';
                    if (data.nombre_cliente) {
                        correoGenerado = data.nombre_cliente.toLowerCase().replace(/\s/g, '') + '@example.com';
                    }
                    document.getElementById('confirm-correo').value = correoGenerado;
                    
                    // Construir el mensaje con los datos de la orden
                    const mensaje = `Cordial saludo,

A continuación relacionamos la orden recibida.

Orden N°: ${data.numero_orden}
Producto: ${data.producto || ''}
Referencia: ${data.referencia || ''}
Vigencia: ${data.fecha_inicio || ''} — ${data.fecha_fin || ''}
Cuñas diarias: ${data.cunas_dia || ''}
Días de pauta: ${data.dias || ''}
Horarios: ${data.horarios || ''}`;
                    
                    document.getElementById('confirm-mensaje').value = mensaje;
                    
                    // Actualizar los botones token con los datos reales
                    const tokenBtns = document.querySelectorAll('.token-btn');
                    tokenBtns.forEach(btn => {
                        const currentToken = btn.getAttribute('data-token');
                        if (currentToken.includes('Orden N°:')) {
                            btn.setAttribute('data-token', `Orden N°: ${data.numero_orden}`);
                        } else if (currentToken.includes('Producto:')) {
                            btn.setAttribute('data-token', `Producto: ${data.producto || ''}`);
                        } else if (currentToken.includes('Referencia:')) {
                            btn.setAttribute('data-token', `Referencia: ${data.referencia || ''}`);
                        } else if (currentToken.includes('Vigencia:')) {
                            btn.setAttribute('data-token', `Vigencia: ${data.fecha_inicio || ''} — ${data.fecha_fin || ''}`);
                        } else if (currentToken.includes('Cuñas diarias:')) {
                            btn.setAttribute('data-token', `Cuñas diarias: ${data.cunas_dia || ''}`);
                        } else if (currentToken.includes('Días de pauta:')) {
                            btn.setAttribute('data-token', `Días de pauta: ${data.dias || ''}`);
                        } else if (currentToken.includes('Horarios:')) {
                            btn.setAttribute('data-token', `Horarios: ${data.horarios || ''}`);
                        }
                    });
                    
                    // Limpiar mensaje de estado
                    statusDiv.innerHTML = '<span style="color: green;">Datos cargados correctamente</span>';
                    setTimeout(() => {
                        statusDiv.innerHTML = '';
                    }, 3000);
                    
                    // Actualizar vista previa
                    actualizarVistaPrevia();
                })
                .catch(error => {
                    console.error('Error:', error);
                    statusDiv.innerHTML = '<span style="color: red;">Error al cargar los datos: ' + error.message + '</span>';
                    
                    // Limpiar campos
                    document.getElementById('confirm-nit').value = '';
                    document.getElementById('confirm-correo').value = '';
                    document.getElementById('confirm-mensaje').value = 'Cordial saludo,\n\nA continuación relacionamos la orden recibida.\n\n';
                    actualizarVistaPrevia();
                });
        });
        
        // Eventos para actualizar vista previa
        document.getElementById('confirm-correo').addEventListener('input', actualizarVistaPrevia);
        document.getElementById('confirm-asunto').addEventListener('input', actualizarVistaPrevia);
        document.getElementById('confirm-mensaje').addEventListener('input', actualizarVistaPrevia);
        
        // Inicializar vista previa
        actualizarVistaPrevia();
    </script>
</body>

</html>