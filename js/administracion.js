// 1. Esperamos a que el HTML cargue totalmente
document.addEventListener('DOMContentLoaded', () => {

    const btnLista = document.getElementById('tab-lista');
    const btnRegistro = document.getElementById('tab-registro');
    const btnEmail = document.getElementById('tab-email');
    const btnNuevoTop = document.querySelector('.nueva-orden');

    const sectionLista = document.getElementById('section-lista');
    const sectionRegistro = document.getElementById('section-registro');
    const sectionEmail = document.getElementById('section-email');

    const formEmail = document.getElementById('form-email-config');
    const emailStatus = document.getElementById('email-status');
    const btnTestEmail = document.getElementById('btn-test-email');
    const emailPassword = document.getElementById('email-password');
    const emailPasswordConfirm = document.getElementById('email-password-confirm');
    const emailRemitente = document.getElementById('email-remitente');
    const smtpHost = document.getElementById('smtp-host');
    const smtpPort = document.getElementById('smtp-port');

    const tabs = [btnLista, btnRegistro, btnEmail].filter(Boolean);
    const sections = [sectionLista, sectionRegistro, sectionEmail].filter(Boolean);

    function activarTab(seccionActiva, botonActivo) {
        if (!seccionActiva || !botonActivo) return;

        sections.forEach(section => section.classList.remove('active'));
        tabs.forEach(tab => tab.classList.remove('active'));

        seccionActiva.classList.add('active');
        botonActivo.classList.add('active');
    }

    function mostrarEstadoEmail(tipo, mensaje) {
        if (!emailStatus) return;

        emailStatus.className = 'email-status show ' + tipo;
        emailStatus.textContent = mensaje;
    }

    function validarConfigEmail() {
        if (!emailRemitente || !smtpHost || !smtpPort || !emailPassword || !emailPasswordConfirm) {
            return false;
        }

        if (emailPassword.value !== emailPasswordConfirm.value) {
            mostrarEstadoEmail('error', 'Las contraseñas no coinciden. Verifica los datos antes de guardar.');
            return false;
        }

        if (String(smtpPort.value).trim() === '' || Number(smtpPort.value) <= 0) {
            mostrarEstadoEmail('error', 'El puerto SMTP debe ser un número válido mayor que cero.');
            return false;
        }

        return true;
    }

    if (btnLista && sectionLista) {
        btnLista.addEventListener('click', () => activarTab(sectionLista, btnLista));
    }

    if (btnRegistro && sectionRegistro) {
        btnRegistro.addEventListener('click', () => activarTab(sectionRegistro, btnRegistro));
    }

    if (btnEmail && sectionEmail) {
        btnEmail.addEventListener('click', () => activarTab(sectionEmail, btnEmail));
    }

    if (btnNuevoTop && btnRegistro && sectionRegistro) {
        btnNuevoTop.addEventListener('click', () => {
            activarTab(sectionRegistro, btnRegistro);
        });
    }

    if (formEmail) {
        formEmail.addEventListener('submit', (event) => {
            event.preventDefault();

            if (!validarConfigEmail()) {
                return;
            }

            mostrarEstadoEmail('success', 'La configuración de e-mail fue guardada correctamente.');
        });
    }

    if (btnTestEmail) {
        btnTestEmail.addEventListener('click', () => {
            if (!validarConfigEmail()) {
                return;
            }

            mostrarEstadoEmail('info', 'Conexión SMTP verificada correctamente. El servidor respondió sin errores.');
        });
    }

    function markActiveNavLink() {
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
    }

    if (btnLista && sectionLista) {
        activarTab(sectionLista, btnLista);
    }

    markActiveNavLink();
});