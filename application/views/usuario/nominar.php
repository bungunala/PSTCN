<?php $this->load->view('layouts/header'); ?>

<div class="container mt-4">
    <h2><?= html_escape($concurso['titulo']) ?></h2>
    <p class="text-muted"><?= html_escape($concurso['descripcion']) ?></p>

    <?php if ($concurso['imagen_url']): ?>
        <div class="mb-4">
            <img src="<?= base_url($concurso['imagen_url']) ?>" class="img-fluid rounded shadow" style="width: 100%; max-height: 350px; object-fit: cover;">
        </div>
    <?php endif; ?>

    <div class="alert alert-info text-center">
        <strong>Nomina a tu favorito</strong>
    </div>

    <?php if ($ya_nominado): ?>
        <div class="alert alert-success text-center">
            <strong>✅ Ya has ejercido tu nominación en este concurso.</strong>
        </div>
    <?php else: ?>
        <!-- Filtros -->
        <div class="card mb-4 p-3 shadow-sm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Ciudad</label>
                    <select id="filtro-ciudad" class="form-select">
                        <option value="">Todas</option>
                        <?php                        
                        $ciudades = array_unique(array_column($nominados, 'ciudad'));
                        foreach ($ciudades as $ciudad): ?>
                            <option value="<?= $ciudad ?>"><?= $ciudad ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filtrar por Unidad</label>
                    <select id="filtro-unidad" class="form-select">
                        <option value="">Todas</option>
                        <?php
                        $unidades = array_unique(array_column($nominados, 'unidad'));
                        foreach ($unidades as $unidad): ?>
                            <option value="<?= $unidad ?>"><?= $unidad ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Buscar por Nombre</label>
                    <input type="text" id="filtro-nombre" class="form-control" placeholder="Escribe para buscar...">
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabla de Nominados -->
    <div class="row" id="lista-nominados">
        <?php if (!empty($nominados)): ?>
            <?php foreach ($nominados as $nom): ?>
                <div class="col-md-6 col-lg-4 mb-4 filtro-item"
                     data-ciudad="<?= $nom['ciudad'] ?>"
                     data-unidad="<?= $nom['unidad'] ?>"
                     data-nombre="<?= strtolower($nom['nombres'] . ' ' . $nom['apellidos']) ?>">
                    <div class="card h-100 shadow-sm text-center">
                        <!-- Foto con imagen por defecto -->
                        <img src="<?= !empty($nom['foto_url']) ? base_url($nom['foto_url']) : 'https://via.placeholder.com/150/0055a4/FFFFFF?text=' . urlencode(substr($nom['nombres'], 0, 1)) ?>"
                             class="card-img-top" style="height: 150px; object-fit: cover;" alt="Foto de <?= $nom['nombres'] ?>">

                        <div class="card-body">
                            <h5 class="card-title"><?= html_escape($nom['nombres'] . ' ' . $nom['apellidos']) ?></h5>
                            <p class="card-text text-muted small">
                                <strong>Ciudad:</strong> <?= html_escape($nom['ciudad']) ?><br>
                                <strong>Unidad:</strong> <?= html_escape($nom['unidad']) ?><br>
                                <strong>Email:</strong> <?= html_escape($nom['email']) ?>
                            </p>

                            <?php if (!$ya_nominado): ?>
                                <form action="<?= base_url('usuario/procesar_nominacion') ?>" method="post" onsubmit="return confirm('¿Confirmar nominación de <?= addslashes($nom['nombres']) ?> <?= addslashes($nom['apellidos']) ?>?')">
                                    <input type="hidden" name="concurso_id" value="<?= $concurso['id'] ?>">
                                    <input type="hidden" name="nominee_id" value="<?= $nom['email'] ?>">
                                    <button type="submit" class="btn btn-primary">Nominar</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Ya nominado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No hay nominados disponibles.</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <a href="<?= base_url('home') ?>" class="btn btn-secondary">Regresar al Inicio</a>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>

<script>
// Filtros dinámicos
document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.filtro-item');
    const filtroCiudad = document.getElementById('filtro-ciudad');
    const filtroUnidad = document.getElementById('filtro-unidad');
    const filtroNombre = document.getElementById('filtro-nombre');

    function filtrar() {
        const ciudad = filtroCiudad.value;
        const unidad = filtroUnidad.value;
        const nombre = filtroNombre.value.toLowerCase();

        items.forEach(item => {
            const itemCiudad = item.dataset.ciudad;
            const itemUnidad = item.dataset.unidad;
            const itemNombre = item.dataset.nombre;

            let mostrar = true;
            if (ciudad && itemCiudad !== ciudad) mostrar = false;
            if (unidad && itemUnidad !== unidad) mostrar = false;
            if (nombre && !itemNombre.includes(nombre)) mostrar = false;

            item.style.display = mostrar ? 'block' : 'none';
        });
    }

    [filtroCiudad, filtroUnidad, filtroNombre].forEach(el => {
        el.addEventListener('change', filtrar);
    });

    filtroNombre.addEventListener('keyup', filtrar);
});
</script>