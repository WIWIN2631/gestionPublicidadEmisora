document.addEventListener('DOMContentLoaded', () => {
    const btnLista = document.getElementById('tab-lista');
    const btnRegistro = document.getElementById('tab-registro');
    const sectionLista = document.getElementById('lista');
    const sectionRegistro = document.getElementById('registro');

    const tabs = [btnLista, btnRegistro].filter(Boolean);
    const sections = [sectionLista, sectionRegistro].filter(Boolean);

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
});