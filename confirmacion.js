document.addEventListener('DOMContentLoaded', () => {
    const inputCorreo = document.getElementById('confirm-correo');
    const inputAsunto = document.getElementById('confirm-asunto');
    const inputMensaje = document.getElementById('confirm-mensaje');
    const previewCorreo = document.getElementById('preview-correo');
    const previewAsunto = document.getElementById('preview-asunto');
    const previewMensaje = document.getElementById('preview-mensaje');
    const formConfirmacion = document.getElementById('form-confirmacion');
    const statusConfirmacion = document.getElementById('confirmacion-status');
    const btnGuardarPlantilla = document.getElementById('btn-guardar-plantilla');
    const tokenButtons = document.querySelectorAll('.token-btn');

    function mostrarEstado(tipo, mensaje) {
        if (!statusConfirmacion) return;

        statusConfirmacion.className = 'confirmacion-status show ' + tipo;
        statusConfirmacion.textContent = mensaje;
    }

    function actualizarVistaPrevia() {
        if (previewCorreo && inputCorreo) {
            previewCorreo.textContent = inputCorreo.value || 'sin-destinatario@correo.com';
        }

        if (previewAsunto && inputAsunto) {
            previewAsunto.textContent = inputAsunto.value || 'Sin asunto';
        }

        if (previewMensaje && inputMensaje) {
            previewMensaje.textContent = inputMensaje.value || 'Sin contenido';
        }
    }

    function insertarToken(token) {
        if (!inputMensaje) return;

        const start = inputMensaje.selectionStart ?? inputMensaje.value.length;
        const end = inputMensaje.selectionEnd ?? inputMensaje.value.length;
        const before = inputMensaje.value.slice(0, start);
        const after = inputMensaje.value.slice(end);
        const insertion = (before && !before.endsWith('\n') ? '\n' : '') + token;

        inputMensaje.value = before + insertion + after;
        inputMensaje.focus();

        const newCursor = before.length + insertion.length;
        inputMensaje.setSelectionRange(newCursor, newCursor);
        actualizarVistaPrevia();
    }

    if (inputCorreo) {
        inputCorreo.addEventListener('input', actualizarVistaPrevia);
    }

    if (inputAsunto) {
        inputAsunto.addEventListener('input', actualizarVistaPrevia);
    }

    if (inputMensaje) {
        inputMensaje.addEventListener('input', actualizarVistaPrevia);
    }

    tokenButtons.forEach(button => {
        button.addEventListener('click', () => {
            const token = button.getAttribute('data-token');
            if (token) {
                insertarToken(token);
            }
        });
    });

    if (formConfirmacion) {
        formConfirmacion.addEventListener('submit', (event) => {
            event.preventDefault();

            if (!inputCorreo || !inputCorreo.value.trim()) {
                mostrarEstado('info', 'Debes indicar el correo del cliente antes de enviar la confirmación.');
                return;
            }

            mostrarEstado('success', 'La confirmación quedó lista para envío al cliente.');
        });
    }

    if (btnGuardarPlantilla) {
        btnGuardarPlantilla.addEventListener('click', () => {
            mostrarEstado('info', 'La plantilla del correo fue guardada correctamente.');
        });
    }

    const menuLinks = document.querySelectorAll('header nav ul li a');
    const currentPath = window.location.pathname.split('/').pop();

    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (href === 'index.html' && currentPath === '')) {
            link.classList.add('active-link');
        } else {
            link.classList.remove('active-link');
        }
    });

    actualizarVistaPrevia();
});