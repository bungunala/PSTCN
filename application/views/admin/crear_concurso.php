<?php $this->load->view('layouts/header'); ?>

<h2>Crear Nuevo Concurso</h2>

<form action="<?= base_url('admin/guardar_concurso') ?>" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Título del Concurso</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción (máx. 500 caracteres)</label>
                <textarea name="descripcion" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Subir Arte (PNG ≤5MB)</label>
                <input type="file" name="imagen" class="form-control" accept="image/png" >
            </div>
        </div>
    </div>

    <h4 class="mt-4">Seleccionar Nominados Iniciales</h4>
    <p class="text-muted">Seleccione los funcionarios que participarán inicialmente en el concurso.</p>

    <!-- Filtros -->
    <div class="card mb-3 p-3">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control search-input" placeholder="Buscar por nombre..." data-table="tabla-usuarios">
            </div>
            <div class="col-md-4">
                <select id="filtro-provincia" class="form-select">
                    <option value="">Todas las provincias</option>
                    <?php
                    $provincias = array_unique(array_column($usuarios, 'provincia'));
                    foreach ($provincias as $prov): ?>
                        <option value="<?= $prov ?>"><?= $prov ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select id="filtro-ciudad" class="form-select">
                    <option value="">Todas las ciudades</option>
                    <?php
                    $ciudades = array_unique(array_column($usuarios, 'ciudad'));
                    foreach ($ciudades as $ciudad): ?>
                        <option value="<?= $ciudad ?>"><?= $ciudad ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select id="filtro-genero" class="form-select">
                    <option value="">Todos los géneros</option>
                    <option value="MASCULINO">Masculino</option>
                    <option value="FEMENINO">Femenino</option>                    
                </select>
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="table-responsive">
        <table class="table table-striped" id="tabla-usuarios">
            <thead class="table-dark">
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th>Nro</th>
                    <th>Apellidos</th>
                    <th>Nombres</th>
                    <th>Provincia</th>
                    <th>Ciudad</th>
                    <th>Unidad</th>
                    <th>Género</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $index => $usuario): ?>
                <tr data-provincia="<?= $usuario['provincia'] ?>"
                    data-ciudad="<?= $usuario['ciudad'] ?>"
                    data-genero="<?= $usuario['genero'] ?>">
                    <td><input type="checkbox" name="nominado_id[]" value="<?= $usuario['email'] ?>"></td>
                    <td><?= $index + 1 ?></td>
                    <td><?= html_escape($usuario['apellidos']) ?></td>
                    <td><?= html_escape($usuario['nombres']) ?></td>
                    <td><?= html_escape($usuario['provincia']) ?></td>
                    <td><?= html_escape($usuario['ciudad']) ?></td>
                    <td><?= html_escape($usuario['unidad']) ?></td>
                    <td><?= html_escape($usuario['genero']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button type="submit" class="btn btn-primary">Guardar Concurso y Nominados</button>
        <a href="<?= base_url('admin/index') ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php $this->load->view('layouts/footer'); ?>

<script>
// Filtros dinámicos
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('#tabla-usuarios tbody tr');
    const filters = [ 'provincia', 'ciudad', 'genero' ];

    filters.forEach(field => {
        const select = document.getElementById(`filtro-${field}`);
        if (select) {
            select.addEventListener('change', () => filterTable(rows, field, select.value));
        }
    });

    function filterTable(rows, field, value) {
        rows.forEach(row => {
            const rowValue = row.dataset[field];
            row.style.display = !value || rowValue === value ? '' : 'none';
        });
    }

    // Búsqueda predictiva
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Seleccionar/deseleccionar todos
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('input[name="nominado_id[]"]');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});
</script>