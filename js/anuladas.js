document.addEventListener('DOMContentLoaded', () => {

    const btnLista = document.getElementById('tab-lista');
    const btnGestion = document.getElementById('tab-gestion');
    const sectionLista = document.getElementById('section-lista');
    const sectionGestion = document.getElementById('section-gestion');

    const inputNumero = document.getElementById('g-numero_orden');
    const inputMotivo = document.getElementById('g-motivo');
    const textareaComentarios = document.getElementById('g-comentarios');

    function activarTab(seccionActiva, botonActivo) {
        [sectionLista, sectionGestion].forEach(s => s.classList.remove('active'));
        [btnLista, btnGestion].forEach(b => b.classList.remove('active'));
        seccionActiva.classList.add('active');
        botonActivo.classList.add('active');
    }

    if (btnLista && btnGestion && sectionLista && sectionGestion) {
        btnLista.addEventListener('click', () => activarTab(sectionLista, btnLista));
        btnGestion.addEventListener('click', () => activarTab(sectionGestion, btnGestion));
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

    // ====== BOTONES REVISAR ======
    const btnsRevisar = document.querySelectorAll('.btn-action.view');

    btnsRevisar.forEach(btn => {
        btn.addEventListener('click', () => {
            const fila = btn.closest('tr');
            if (!fila) return;

            // Tomar datos de la tabla
            const numeroOrden = fila.children[0].textContent.trim();
            const motivo = fila.children[3].textContent.trim();
            const comentarios = fila.children[4].textContent.trim(); // columna oculta

            // Llenar formulario
            if (inputNumero) inputNumero.value = numeroOrden;
            if (inputMotivo) inputMotivo.value = motivo;
            if (textareaComentarios) textareaComentarios.value = comentarios;

            // Cambiar a la pestaña de gestión
            activarTab(sectionGestion, btnGestion);
        });
    });

});