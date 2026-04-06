<?php $this->load->view('layouts/header'); ?>

<div class="container mt-4">
    <h2>Elegir Ganadores - <?= html_escape($concurso['titulo']) ?></h2>
    <p class="text-muted"><?= html_escape($concurso['descripcion']) ?></p>

    <?php if ($concurso['imagen_url']): ?>
        <img src="<?= base_url($concurso['imagen_url']) ?>" class="img-fluid mb-3" style="max-height: 200px;">
    <?php endif; ?>

    <div class="alert alert-info">
        <strong>Nota:</strong> Seleccione los ganadores y luego haga clic en "Asignar Posiciones por Votos".
    </div>

    <!-- Filtro predictivo -->
    <div class="mb-3">
        <input type="text" id="search-nominados" class="form-control" placeholder="Buscar en nominados...">
    </div>

    <!-- Nominados Finales (ordenados por votos) -->
    <h4>Nominados Finales (ordenados por votos)</h4>
    <div class="table-responsive">
        <table class="table table-striped" id="tabla-nominados">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-nominados"></th>
                    <th>ID</th>
                    <th>Apellidos</th>
                    <th>Nombres</th>
                    <th>Provincia</th>
                    <th>Ciudad</th>
                    <th>Unidad</th>
                    <th>Votos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nominados as $nom): ?>
                    <tr data-id="<?= $nom['usuario_email'] ?>" data-votos="<?= $nom['total_votos'] ?>">
                        <td><input type="checkbox" class="select-nominado"></td>
                        <td><?= $nom['id'] ?></td>
                        <td><?= html_escape($nom['apellidos']) ?></td>
                        <td><?= html_escape($nom['nombres']) ?></td>
                        <td><?= html_escape($nom['provincia']) ?></td>
                        <td><?= html_escape($nom['ciudad']) ?></td>
                        <td><?= html_escape($nom['unidad']) ?></td>
                        <td><strong><?= $nom['total_votos'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Botón para mover seleccionados -->
    <button type="button" class="btn btn-primary my-3" id="btn-mover-ganadores">→ Agregar a Ganadores</button>

    <!-- Lista de Ganadores -->
    <!-- Lista de Ganadores -->
    <h4>Ganadores a Publicar</h4>
    <form action="<?= base_url('admin/guardar_ganadores') ?>" method="post">
        <input type="hidden" name="concurso_id" value="<?= $concurso['id'] ?>">
        <button type="button" class="btn btn-warning mb-2" id="btn-ordenar-votos">Asignar Posiciones por Votos</button>

        <div class="table-responsive">
            <table class="table table-striped" id="tabla-ganadores">
                <thead class="table-dark">
                    <tr>
                        <th>Eliminar</th>
                        <th>ID</th>
                        <th>Apellidos</th>
                        <th>Nombres</th>
                        <th>Provincia</th>
                        <th>Ciudad</th>
                        <th>Unidad</th>
                        <th>Votos</th>
                        <th>Posición</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Cargar ganadores ya guardados -->
                    <?php foreach ($ganadores_guardados as $g): ?>
                        <tr data-id="<?= $g['usuario_id'] ?>" data-votos="0">
                            <td><button type="button" class="btn btn-sm btn-danger btn-eliminar">Eliminar</button></td>
                            <td><?= $g['usuario_id'] ?></td>
                            <td><?= html_escape($g['apellidos']) ?></td>
                            <td><?= html_escape($g['nombres']) ?></td>
                            <td><?= html_escape($g['provincia']) ?></td>
                            <td><?= html_escape($g['ciudad']) ?></td>
                            <td><?= html_escape($g['unidad']) ?></td>
                            <td><strong><?= $g['total_votos'] ?></strong></td>
                            <td>
                                <input type="number" name="posicion[]" value="<?= $g['posicion'] ?>" min="1"
                                    class="form-control form-control-sm" required>
                                <input type="hidden" name="ganador_id[]" value="<?= $g['usuario_id'] ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
            <button type="submit" class="btn btn-success">Guardar Ganadores</button>
            <a href="<?= base_url('admin/index') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php $this->load->view('layouts/footer'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnMover = document.getElementById('btn-mover-ganadores');
        const btnOrdenar = document.getElementById('btn-ordenar-votos');
        const tablaNominados = document.getElementById('tabla-nominados');
        const tablaGanadores = document.getElementById('tabla-ganadores').querySelector('tbody');
        const searchInput = document.getElementById('search-nominados');
        const selectAll = document.getElementById('select-all-nominados');

        // Mover seleccionados a ganadores
        btnMover.addEventListener('click', function () {
            const checks = tablaNominados.querySelectorAll('.select-nominado:checked');
            checks.forEach(cb => {
                const tr = cb.closest('tr');
                const id = tr.dataset.id;

                if (document.querySelector(`#tabla-ganadores [data-id="${id}"]`)) {
                    return;
                }

                const clone = tr.cloneNode(true);
                clone.dataset.id = id;

                // Botón eliminar
                const tdSelect = clone.cells[0];
                tdSelect.innerHTML = '<button type="button" class="btn btn-sm btn-danger btn-eliminar">Eliminar</button>';

                // Añadir input oculto para id
                const tdId = clone.cells[1];
                tdId.innerHTML += `<input type="hidden" name="ganador_id[]" value="${id}">`;

                // Añadir campo de posición
                const tdPosicion = document.createElement('td');
                tdPosicion.innerHTML = `<input type="number" name="posicion[]" value="1" min="1" class="form-control form-control-sm" required>`;
                clone.appendChild(tdPosicion);

                tablaGanadores.appendChild(clone);
            });

            // Deseleccionar
            checks.forEach(cb => cb.checked = false);
        });

        // Eliminar de ganadores
        tablaGanadores.addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-eliminar')) {
                e.target.closest('tr').remove();
            }
        });

        // Filtro predictivo
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const filter = this.value.toLowerCase();
                const tr = tablaNominados.querySelectorAll('tbody tr');
                tr.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        // Seleccionar/deseleccionar todos
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const checkboxes = tablaNominados.querySelectorAll('.select-nominado');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Asignar posiciones por votos (orden descendente, con empates)
        // Asignar posiciones por votos (orden descendente)
        btnOrdenar.addEventListener('click', function () {
            const filas = Array.from(tablaGanadores.querySelectorAll('tr'))
                .map(tr => {
                    const votos = parseInt(tr.dataset.votos);
                    return { tr, votos };
                })
                .sort((a, b) => b.votos - a.votos);

            let posicion = 1;
            let ultimoVoto = null;

            filas.forEach((fila, index) => {
                const inputPosicion = fila.tr.querySelector('input[name="posicion[]"]');
                if (ultimoVoto !== null && fila.votos < ultimoVoto) {
                    posicion = index + 1;
                }
                inputPosicion.value = posicion;
                ultimoVoto = fila.votos;
            });
        });
    });
</script>