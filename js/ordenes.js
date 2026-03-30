document.addEventListener('DOMContentLoaded', () => {
    const btnLista = document.getElementById('tab-lista');
    const btnRegistro = document.getElementById('tab-registro');
    const btnFactura = document.getElementById('tab-factura');
    const btnCertificacion = document.getElementById('tab-certificacion');

    const sectionLista = document.getElementById('lista');
    const sectionRegistro = document.getElementById('registro');
    const sectionFactura = document.getElementById('factura');
    const sectionCertificacion = document.getElementById('certificacion');

    const btnGoFactura = document.getElementById('btn-go-factura');
    const btnGoCertificacion = document.getElementById('btn-go-certificacion');

    const tabs = [btnLista, btnRegistro, btnFactura, btnCertificacion].filter(Boolean);
    const sections = [sectionLista, sectionRegistro, sectionFactura, sectionCertificacion].filter(Boolean);

    function inicializarVista() {
        if (!btnLista || !sectionLista) return;
        activarTab(sectionLista, btnLista);
    }

    function activarTab(seccionActiva, botonActivo) {
        if (!seccionActiva || !botonActivo) return;

        sections.forEach(seccion => {
            seccion.classList.remove('active');
        });

        tabs.forEach(tab => {
            tab.classList.remove('active');
        });

        seccionActiva.classList.add('active');
        botonActivo.classList.add('active');
    }

    inicializarVista();

    if (btnLista && sectionLista) {
        btnLista.addEventListener('click', () => activarTab(sectionLista, btnLista));
    }

    if (btnRegistro && sectionRegistro) {
        btnRegistro.addEventListener('click', () => activarTab(sectionRegistro, btnRegistro));
    }

    if (btnFactura && sectionFactura) {
        btnFactura.addEventListener('click', () => activarTab(sectionFactura, btnFactura));
    }

    if (btnCertificacion && sectionCertificacion) {
        btnCertificacion.addEventListener('click', () => activarTab(sectionCertificacion, btnCertificacion));
    }

    if (btnGoFactura && btnFactura && sectionFactura) {
        btnGoFactura.addEventListener('click', () => activarTab(sectionFactura, btnFactura));
    }

    if (btnGoCertificacion && btnCertificacion && sectionCertificacion) {
        btnGoCertificacion.addEventListener('click', () => activarTab(sectionCertificacion, btnCertificacion));
    }

    const menuLinks = document.querySelectorAll('header nav ul li a');
    const currentPath = window.location.pathname.split('/').pop().toLowerCase();

    menuLinks.forEach(link => {
        const href = (link.getAttribute('href') || '').toLowerCase();
        if (href === currentPath || (href === 'index.html' && (currentPath === '' || currentPath === 'index.html'))) {
            link.classList.add('active-link');
        } else {
            link.classList.remove('active-link');
        }
    });
 // ================= DIAS =================
    const checkDias = document.querySelectorAll('#dias-container input');
    const inputDias = document.getElementById('ord-dias');

    if (checkDias.length && inputDias) {
        checkDias.forEach(chk => {
            chk.addEventListener('change', () => {
                const seleccionados = Array.from(checkDias)
                    .filter(c => c.checked)
                    .map(c => c.value);

                inputDias.value = seleccionados.join(', ');
            });
        });
    }

    // ================= HORARIOS =================
    const contHorarios = document.getElementById('horarios-container');
    const inputHorarios = document.getElementById('ord-horarios');
    const btnAdd = document.getElementById('addHora');

    if (contHorarios && inputHorarios && btnAdd) {

        btnAdd.addEventListener('click', () => {
            const input = document.createElement('input');
            input.type = 'time';
            input.classList.add('hora');

            contHorarios.insertBefore(input, btnAdd);
        });

        // Capturar antes de enviar
        const form = document.querySelector('form');

        if (form) {
            form.addEventListener('submit', () => {
                const horas = document.querySelectorAll('.hora');

                const lista = Array.from(horas)
                    .map(h => h.value)
                    .filter(v => v !== '');

                inputHorarios.value = lista.join(', ');
            });
        }
    }

    // ================= CLIENTE =================
    const selectCliente = document.getElementById('cliente_select');
    const inputNombre = document.getElementById('cliente_nombre');
    const inputId = document.getElementById('cliente_id'); // NUEVO: input oculto

    if (selectCliente && inputNombre && inputId) {
        selectCliente.addEventListener('change', () => {
            const opcion = selectCliente.options[selectCliente.selectedIndex];

            const nombre = opcion.getAttribute('data-nombre') || '';
            const id = opcion.value || ''; // el id seleccionado

            inputNombre.value = nombre;  // llena el nombre
            inputId.value = id;          // llena el input oculto
        });
    }

    // ================= FACTURA =================
    const botonesFactura = document.querySelectorAll('.btn-action.factura');

    botonesFactura.forEach(btn => {
        btn.addEventListener('click', () => {
            const idOrden = btn.getAttribute('data-id');
            if (!idOrden) return;

            // Abrir PDF en nueva ventana
            window.open(`facturar.php?id=${idOrden}`, '_blank');
        });
    });

    // ================= ANULAR ORDEN =================
    const botonesAnular = document.querySelectorAll('.btn-action.anular');

    botonesAnular.forEach(btn => {
        btn.addEventListener('click', () => {
            const rol = btn.getAttribute('data-rol');

            // Verificar rol
            if (rol !== 'admin' && rol !== 'superadmin') {
                alert('No tienes permisos para anular órdenes');
                return;
            }

            const idOrden = btn.getAttribute('data-id');
            if (!idOrden) return;

            // Pedir motivo
            let motivo = prompt("Ingrese el motivo de la anulación:", "Por revisar");
            if (motivo === null) return; // Usuario canceló

            // Pedir comentarios adicionales
            let comentarios = prompt("Ingrese comentarios adicionales (opcional):", "");
            if (comentarios === null) comentarios = "";

            // Confirmación final
            const confirmar = confirm(`¿Deseas realmente anular la orden ${idOrden}?`);
            if (!confirmar) return;

            // Redirigir a script de anulación con parámetros
            window.location.href = `funciones/anularorden.php?id=${idOrden}&motivo=${encodeURIComponent(motivo)}&comentarios=${encodeURIComponent(comentarios)}`;
        });
    });
    // ================= CERTIFICACIÓN =================
    const botonesCertificacion = document.querySelectorAll('.btn-action.certificacion');

    botonesCertificacion.forEach(btn => {
        btn.addEventListener('click', () => {
            // Obtener el ID de la orden desde el botón
            // Necesitamos agregar el atributo data-id al botón de certificación
            // Por ahora, buscaremos el ID desde la fila
            const fila = btn.closest('tr');
            const idOrden = btn.getAttribute('data-id');
            
            if (!idOrden) {
                // Si no tiene data-id, intentamos obtenerlo de otra manera
                // Buscamos el botón de factura en la misma fila que tiene data-id
                const botonFactura = fila.querySelector('.btn-action.factura');
                if (botonFactura) {
                    const id = botonFactura.getAttribute('data-id');
                    if (id) {
                        window.open(`certificacion.php?id=${id}`, '_blank');
                        return;
                    }
                }
                alert('Error: No se pudo obtener el ID de la orden');
                return;
            }
            
            // Abrir certificación en nueva ventana
            window.open(`certificacion.php?id=${idOrden}`, '_blank');
        });
    });
});