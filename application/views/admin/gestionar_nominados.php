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
                        <button type="button" class="btn btn-danger btn-sm ms-2" onclick="eliminarImagenConcurso('<?= $concurso['imagen_url'] ?>', <?= $concurso['id'] ?>)">Eliminar</button>
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
                    <th>Acción</th>
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
                                    <button type="button" class="btn btn-sm btn-danger mt-1" onclick="eliminarMedia('imagen', '<?= $nom['id'] ?>', '<?= $nom['imagen_nominado'] ?>')">Eliminar</button>
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
                                    <button type="button" class="btn btn-sm btn-danger mt-1" onclick="eliminarMedia('video', '<?= $nom['id'] ?>', '<?= $nom['video_nominado'] ?>')">Eliminar</button>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning btn-quitar-final-php" 
                                data-email="<?= $nom['id'] ?>"
                                data-nombre="<?= html_escape($nom['apellidos'] . ' ' . $nom['nombres']) ?>"
                                data-provincia="<?= html_escape($nom['provincia']) ?>"
                                data-ciudad="<?= html_escape($nom['ciudad']) ?>"
                                data-unidad="<?= html_escape($nom['unidad']) ?>">
                                Quitar
                            </button>
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

        // Mover a finales (ELIMINA de iniciales, no copia)
        // Mover a finales
        btnMover.addEventListener('click', function() {
            const checks = tablaIniciales.querySelectorAll('input[name="nominados_iniciales_check[]"]:checked');
            checks.forEach(cb => {
                const tr = cb.closest('tr');
                const email = cb.value;
                const nombre = tr.cells[2].textContent + ' ' + tr.cells[3].textContent;
                const provincia = tr.cells[4].textContent;
                const ciudad = tr.cells[5].textContent;
                const unidad = tr.cells[6].textContent;

                if (document.querySelector(`#tabla-finales input[value="${email}"]`)) {
                    return; // Ya existe
                }

                // Añadir celdas de foto y video para la fila de finales
                const celdasMedia = `
                    <td>
                        <input type="file" name="imagen_${email}" class="form-control form-control-sm" accept="image/png">
                    </td>
                    <td>
                        <input type="file" name="video_${email}" class="form-control form-control-sm" accept="video/mp4">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-quitar-final" data-email="${email}" data-nombre="${nombre}" data-provincia="${provincia}" data-ciudad="${ciudad}" data-unidad="${unidad}">Quitar</button>
                    </td>
                `;

                // Crear nueva fila en finales
                const trFinal = document.createElement('tr');
                trFinal.innerHTML = `
                    <td><input type="checkbox" name="nominados_finales[]" value="${email}" checked></td>
                    <td></td>
                    <td>${tr.cells[2].textContent}</td>
                    <td>${tr.cells[3].textContent}</td>
                    <td>${provincia}</td>
                    <td>${ciudad}</td>
                    <td>${unidad}</td>
                ` + celdasMedia;

                // Agregar evento al botón quitar
                trFinal.querySelector('.btn-quitar-final').addEventListener('click', function() {
                    quitarDeFinales(this);
                });

                document.querySelector('#tabla-finales tbody').appendChild(trFinal);

                // Eliminar la fila de iniciales
                tr.remove();
            });

            // Actualizar números de fila en ambas tablas
            actualizarNumerosFila('tabla-iniciales');
            actualizarNumerosFila('tabla-finales');

            checks.forEach(cb => cb.checked = false);
        });

        // Función para quitar de finales y devolver a iniciales
        function quitarDeFinales(boton) {
            const tr = boton.closest('tr');
            const email = tr.querySelector('input[type="checkbox"]').value;
            const nombre = boton.dataset.nombre;
            const provincia = boton.dataset.provincia;
            const ciudad = boton.dataset.ciudad;
            const unidad = boton.dataset.unidad;

            // Crear fila en iniciales
            const trInicial = document.createElement('tr');
            trInicial.innerHTML = `
                <td><input type="checkbox" name="nominados_iniciales_check[]" value="${email}"></td>
                <td></td>
                <td>${nombre.split(' ')[0] || ''}</td>
                <td>${nombre.split(' ').slice(1).join(' ') || ''}</td>
                <td>${provincia}</td>
                <td>${ciudad}</td>
                <td>${unidad}</td>
                <td>0</td>
            `;

            document.querySelector('#tabla-iniciales tbody').appendChild(trInicial);

            // Eliminar la fila de finales
            tr.remove();

            // Actualizar números de fila
            actualizarNumerosFila('tabla-iniciales');
            actualizarNumerosFila('tabla-finales');
        }

        // Función para actualizar números de fila
        function actualizarNumerosFila(tablaId) {
            const tabla = document.getElementById(tablaId);
            const filas = tabla.querySelectorAll('tbody tr');
            filas.forEach((fila, index) => {
                const celdaNumero = fila.cells[1];
                if (celdaNumero) {
                    celdaNumero.textContent = index + 1;
                }
            });
        }

        // Asignar evento a los botones de quitar existentes
        document.querySelectorAll('.btn-quitar-final, .btn-quitar-final-php').forEach(boton => {
            boton.addEventListener('click', function() {
                quitarDeFinales(this);
            });
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

    // Función para eliminar media (imagen/video) por AJAX
    function eliminarMedia(tipo, usuarioEmail, rutaArchivo) {
        if (!confirm('¿Está seguro de eliminar este ' + tipo + '?')) {
            return;
        }

        const concursoId = document.querySelector('input[name="concurso_id"]')?.value || 
                          window.location.pathname.split('/').pop();

        fetch('<?= base_url("admin/eliminar_media") ?>', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'concurso_id=' + encodeURIComponent(concursoId) + 
                  '&usuario_email=' + encodeURIComponent(usuarioEmail) + 
                  '&tipo=' + encodeURIComponent(tipo) + 
                  '&ruta=' + encodeURIComponent(rutaArchivo)
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Error: Respuesta inválida del servidor');
            }
        })
        .catch(error => {
            alert('Error de conexión: ' + error.message);
        });
    }

    // Función para eliminar imagen del concurso por AJAX
    function eliminarImagenConcurso(rutaArchivo, concursoId) {
        if (!confirm('¿Está seguro de eliminar la imagen del concurso?')) {
            return;
        }

        fetch('<?= base_url("admin/eliminar_imagen_concurso") ?>', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'concurso_id=' + encodeURIComponent(concursoId) + 
                  '&ruta=' + encodeURIComponent(rutaArchivo)
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                alert('Error: Respuesta inválida del servidor');
            }
        })
        .catch(error => {
            alert('Error de conexión: ' + error.message);
        });
    }
</script>
