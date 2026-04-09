<?php $this->load->view('layouts/header'); ?>

<h2>Editar Concurso: <?= html_escape($concurso['titulo']) ?></h2>

<!-- UN SOLO FORMULARIO -->
<form action="<?= base_url('admin/guardar_nominados') ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="concurso_id" value="<?= $concurso['id'] ?>">

    <div class="row">
        <div class="col-md-12">
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" value="<?= html_escape($concurso['titulo']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" maxlength="500"><?= html_escape($concurso['descripcion']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="diseño" <?= $concurso['estado'] == 'diseño' ? 'selected' : '' ?>>Diseño</option>
                    <option value="nominacion" <?= $concurso['estado'] == 'nominacion' ? 'selected' : '' ?>>Nominación</option>
                    <option value="votacion" <?= $concurso['estado'] == 'votacion' ? 'selected' : '' ?>>Votación</option>
                    <option value="cerrado" <?= $concurso['estado'] == 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Imagen del Concurso (PNG ≤5MB)</label>
                <input type="file" name="imagen" class="form-control" accept="image/png">
                <?php if (!empty($concurso['imagen_url'])): ?>
                    <div class="mt-2">
                        <img src="<?= base_url($concurso['imagen_url']) ?>" alt="Imagen actual" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="eliminar_imagen" id="eliminar_imagen" class="form-check-input" value="1">
                        <label for="eliminar_imagen" class="form-check-label">Eliminar imagen actual</label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Nominados Iniciales (arriba) -->
    <h4 class="mt-4">Nominados Iniciales (ordenados por número de nominaciones)</h4>
    <p class="text-muted">Seleccione los que participarán en la votación.</p>

    <div class="mb-3">
        <input type="text" class="form-control search-input" placeholder="Buscar en iniciales..." data-table="tabla-iniciales">
    </div>

    <div class="table-responsive">
        <table class="table table-striped" id="tabla-iniciales">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-iniciales"></th>
                    <th>Nro</th>
                    <th>Apellidos</th>
                    <th>Nombres</th>
                    <th>Provincia</th>
                    <th>Ciudad</th>
                    <th>Unidad</th>
                    <th>Votos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nominados_iniciales as $index => $nom): ?>
                    <tr>
                        <td><input type="checkbox" name="nominados_iniciales_check[]" value="<?= $nom['usuario_email'] ?>"></td>
                        <td><?= $index + 1 ?></td>
                        <td><?= html_escape($nom['apellidos']) ?></td>
                        <td><?= html_escape($nom['nombres']) ?></td>
                        <td><?= html_escape($nom['provincia']) ?></td>
                        <td><?= html_escape($nom['ciudad']) ?></td>
                        <td><?= html_escape($nom['unidad']) ?></td>
                        <td><strong><?= $nom['total_nominaciones'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <button type="button" class="btn btn-primary" id="btn-mover-a-finales">→ Agregar a Finales</button>
    </div>

    <!-- Nominados Finales (abajo) -->
    <h4 class="mt-4">Nominados Finales (con foto y video)</h4>
    <p class="text-muted">Asigne foto y video a cada nominado final.</p>

    <div class="table-responsive">
        <table class="table table-striped" id="tabla-finales">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-finales"></th>
                    <th>Nro</th>
                    <th>Apellidos</th>
                    <th>Nombres</th>
                    <th>Provincia</th>
                    <th>Ciudad</th>
                    <th>Unidad</th>
                    <th>Foto (PNG ≤5MB)</th>
                    <th>Video (MP4 ≤90MB)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nominados_finales as $index => $nom): ?>
                    <tr>
                        <td><input type="checkbox" name="nominados_finales[]" value="<?= $nom['id'] ?>" checked></td>
                        <td><?= $index + 1 ?></td>
                        <td><?= html_escape($nom['apellidos']) ?></td>
                        <td><?= html_escape($nom['nombres']) ?></td>
                        <td><?= html_escape($nom['provincia']) ?></td>
                        <td><?= html_escape($nom['ciudad']) ?></td>
                        <td><?= html_escape($nom['unidad']) ?></td>
                        <td>
                            <input type="file" name="imagen_<?= $nom['id'] ?>" class="form-control form-control-sm" accept="image/png">
                            <?php if (!empty($nom['imagen_nominado'])): ?>
                                <div class="mt-2">
                                    <a href="<?= base_url($nom['imagen_nominado']) ?>" target="_blank">
                                        <img src="<?= base_url($nom['imagen_nominado']) ?>" alt="Foto" class="img-thumbnail" style="max-width: 120px; max-height: 120px;">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="file" name="video_<?= $nom['id'] ?>" class="form-control form-control-sm" accept="video/mp4">
                            <?php if (!empty($nom['video_nominado'])): ?>
                                <div class="mt-2">
                                    <video controls style="max-width: 240px; max-height: 160px;" class="rounded">
                                        <source src="<?= base_url($nom['video_nominado']) ?>" type="video/mp4">
                                        Tu navegador no soporta video HTML5.
                                    </video>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button type="submit" class="btn btn-primary">Guardar Cambios del Concurso</button>
        <a href="<?= base_url('admin') ?>" class="btn btn-secondary">Regresar</a>
    </div>
</form>

<?php $this->load->view('layouts/footer'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnMover = document.getElementById('btn-mover-a-finales');
        const tablaIniciales = document.getElementById('tabla-iniciales');
        const tablaFinales = document.getElementById('tabla-finales');

        // Mover a finales (COPIA, no elimina)
        // Mover a finales
        btnMover.addEventListener('click', function() {
            const checks = tablaIniciales.querySelectorAll('input[name="nominados_iniciales_check[]"]:checked');
            checks.forEach(cb => {
                const tr = cb.closest('tr');
                const email = cb.value; // ← Ahora es el email

                if (document.querySelector(`#tabla-finales input[value="${email}"]`)) {
                    return; // Ya existe
                }

                const clone = tr.cloneNode(true);
                const input = clone.querySelector('input[type="checkbox"]');
                input.name = 'nominados_finales[]';
                input.checked = true;
                input.value = email;

                // Añadir celdas de foto y video
                const celdasMedia = `
            <td>
                <input type="file" name="imagen_${email}" class="form-control form-control-sm" accept="image/png">
            </td>
            <td>
                <input type="file" name="video_${email}" class="form-control form-control-sm" accept="video/mp4">
            </td>
        `;
                const trMedia = document.createElement('tr');
                trMedia.innerHTML = clone.innerHTML + celdasMedia;
                trMedia.querySelector('input[type="checkbox"]').name = 'nominados_finales[]';
                trMedia.querySelector('input[type="checkbox"]').checked = true;
                trMedia.querySelector('input[type="checkbox"]').value = email;

                document.querySelector('#tabla-finales tbody').appendChild(trMedia);
            });

            checks.forEach(cb => cb.checked = false);
        });

        // Filtros
        document.querySelectorAll('.search-input').forEach(input => {
            input.addEventListener('keyup', function() {
                const table = document.getElementById(this.dataset.table);
                const filter = this.value.toLowerCase();
                const tr = table.getElementsByTagName('tr');
                for (let i = 1; i < tr.length; i++) {
                    const text = tr[i].textContent.toLowerCase();
                    tr[i].style.display = text.includes(filter) ? '' : 'none';
                }
            });
        });
    });
</script>
