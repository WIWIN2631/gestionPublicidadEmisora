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
});