/**
 * ui.js
 * 
 * deberia manejar aqui elementos de interfaz: búsqueda, estilos, animaciones.
 
 */

document.addEventListener('DOMContentLoaded', function () {
    
   // Búsqueda predictiva en la tabla de concursos
 document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('#search-concursos');
    const table = document.getElementById('tabla-concursos');
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});
});