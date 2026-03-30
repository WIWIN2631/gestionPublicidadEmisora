// 1. Esperamos a que el HTML cargue totalmente
document.addEventListener('DOMContentLoaded', () => {

    // 2. Seleccionamos los botones por su ID (los que tú creaste)
    const btnLista = document.getElementById('tab-lista');
    const btnRegistro = document.getElementById('tab-registro');
    const btnNuevoTop = document.querySelector('.nueva-orden'); // El botón dorado de arriba

    // 3. Seleccionamos las secciones de contenido
    const sectionLista = document.getElementById('section-lista');
    const sectionRegistro = document.getElementById('section-registro');

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
    if (btnLista && btnRegistro && sectionLista && sectionRegistro) {
        btnLista.addEventListener('click', () => {
            cambiarTab(sectionLista, sectionRegistro, btnLista, btnRegistro);
        });

        btnRegistro.addEventListener('click', () => {
            cambiarTab(sectionRegistro, sectionLista, btnRegistro, btnLista);
        });
    }

    // Bonus: El botón dorado "+ NUEVO CLIENTE" también debe llevar al registro
    if(btnNuevoTop) {
        btnNuevoTop.addEventListener('click', () => {
            cambiarTab(sectionRegistro, sectionLista, btnRegistro, btnLista);
        });
    }

});