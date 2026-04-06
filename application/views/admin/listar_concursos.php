<?php $this->load->view('layouts/header'); ?>

<h2 class="mb-4">Administración de Concursos</h2>

<!-- Filtros -->
<div class="card mb-4 p-3 shadow-sm">
    <form method="get" class="row g-3">
        <div class="col-md-3">
            <label class="form-label">Filtrar por Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                <option value="diseño" <?= set_select('estado', 'diseño') ?>>Diseño</option>
                <option value="nominacion" <?= set_select('estado', 'nominacion') ?>>Nominación</option>
                <option value="votacion" <?= set_select('estado', 'votacion') ?>>Votación</option>
                <option value="cerrado" <?= set_select('estado', 'cerrado') ?>>Cerrado</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= html_escape($fecha_desde_filtro) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= html_escape($fecha_hasta_filtro) ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Búsqueda predictiva -->
    <div class="row mt-3">
        <div class="col-12">
            <input type="text" id="search-concursos" class="form-control"
                placeholder="Buscar en todos los campos (ID, nombre, estado)...">
        </div>
    </div>
</div>

<!-- Botones de acción -->
<div class="mb-3 d-flex justify-content-between">
    <a href="<?= base_url('admin/crear_concurso') ?>" class="btn btn-success">
        + Crear Nuevo Concurso
    </a>
    <a href="<?= base_url('admin/exportar_concursos') ?>" class="btn btn-info">
        📥 Exportar Todos los Concursos
    </a>
</div>

<!-- Tabla de concursos -->
<div class="table-responsive">
    <table class="table table-striped table-hover" id="tabla-concursos">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre del Concurso</th>
                <th>Estado</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($concursos)): ?>
                <?php foreach ($concursos as $concurso): ?>
                    <tr>
                        <td><?= $concurso['id'] ?></td>
                        <td><?= html_escape($concurso['titulo']) ?></td>
                        <td>
                            <span class="badge bg-<?=
                                $concurso['estado'] == 'diseño' ? 'secondary' :
                                ($concurso['estado'] == 'nominacion' ? 'info' :
                                    ($concurso['estado'] == 'votacion' ? 'success' : 'danger'))
                                ?>">
                                <?= ucfirst(html_escape($concurso['estado'])) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($concurso['fecha_creacion'])) ?></td>
                        <td>
                            <a href="<?= base_url('admin/concurso/' . $concurso['id']) ?>"
                                class="btn btn-sm btn-outline-primary">Editar</a>

                            <?php if ($concurso['estado'] === 'cerrado'): ?>
                                <a href="<?= base_url('admin/elegir_ganadores/' . $concurso['id']) ?>"
                                    class="btn btn-sm btn-warning mt-2 mt-md-0">
                                    Elegir Ganadores
                                </a>
                            <?php endif; ?>

                            <!-- Botón de exportación por concurso -->
                            <a href="<?= base_url('admin/exportar_concurso_detalle/' . $concurso['id']) ?>"
                                class="btn btn-sm btn-outline-success mt-2 mt-md-0">
                                📄 Exportar Detalle
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">No se encontraron concursos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->load->view('layouts/footer'); ?>

<script>
    // Filtro de búsqueda predictiva
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-concursos');
        const table = document.getElementById('tabla-concursos');
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');

        if (searchInput && tbody) {
            searchInput.addEventListener('keyup', function () {
                const filter = this.value.toLowerCase().trim();

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }
    });
</script>