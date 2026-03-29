// 1. Esperamos a que el HTML cargue totalmente
document.addEventListener('DOMContentLoaded', () => {

    // 2. Seleccionamos los botones por su ID
    const btnLista = document.getElementById('tab-lista');
    const btnGestion = document.getElementById('tab-gestion');

    // 3. Seleccionamos las secciones de contenido
    const sectionLista = document.getElementById('section-lista');
    const sectionGestion = document.getElementById('section-gestion');

    // 4. Función para cambiar de pestaña
    function cambiarTab(seccionAMostrar, seccionAOcultar, botonActivo, botonInactivo) {
        // Usamos clases en lugar de inline styles por consistency
        seccionAOcultar.classList.remove('active');
        seccionAMostrar.classList.add('active');

        // Cambiamos la apariencia de los botones
        botonActivo.classList.add('active');
        botonInactivo.classList.remove('active');
    }

    // 5. Escuchamos los clics en los botones
    if (btnLista && btnGestion && sectionLista && sectionGestion) {
        btnLista.addEventListener('click', () => {
            cambiarTab(sectionLista, sectionGestion, btnLista, btnGestion);
        });

        btnGestion.addEventListener('click', () => {
            cambiarTab(sectionGestion, sectionLista, btnGestion, btnLista);
        });
    }

    // 6. Marca el enlace activo de navegación según URL
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

    markActiveNavLink();
});