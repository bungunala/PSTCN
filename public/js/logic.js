/**
 * logic.js
 * 
 * Lógica principal: votaciones, confirmaciones, validaciones.
 * 
 */

/*
function confirmarNominacion(nombre) {
    if (confirm(`¿Confirmar nominación de ${nombre}?`)) {
        document.getElementById('form-nominar').submit();
        alert(`${nombre} ya está participando... ¡mucha suerte!`);
    }
}*/

function confirmarNominacion() {
    const selected = document.querySelector('input[name="nominee_id"]:checked');
    if (selected) {
        const nombre = document.querySelector(`label[for="nominee_${selected.value}"]`)?.textContent || 'este funcionario';
        if (confirm(`¿Confirmar nominación de ${nombre}?`)) {
            document.getElementById('form-nominar').submit();
            alert(`${nombre} ya está participando... ¡mucha suerte!`);
        }
    } else {
        alert('Debe seleccionar un funcionario.');
    }
}

function votarPor(concursoId, nomineeId, nombre) {
    if (confirm(`¿Votar por ${nombre}?`)) {
        fetch('<?= base_url("votacion/votar") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `concurso_id=${concursoId}&nominado_id=${nomineeId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Voto exitoso');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => console.error('Error:', err));
    }
}

// Deshabilitar botones de voto si ya votó
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.btn-votar');
    buttons.forEach(btn => {
        if (btn.dataset.voted === 'true') {
            btn.disabled = true;
            btn.textContent = 'Ya ha ejercido su voto';
        }
    });
});