<?php $this->load->view('layouts/header'); ?>

<h2 class="mb-4">Concursos Disponibles</h2>

<!-- Filtros -->
<div class="card mb-4 p-3 shadow-sm">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Filtrar por Estado</label>
            <select id="filtroEstado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="nominacion">En Nominación</option>
                <option value="votacion">En Votación</option>
                <option value="cerrado">Cerrados</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha Desde</label>
            <input type="date" id="filtroFechaDesde" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha Hasta</label>
            <input type="date" id="filtroFechaHasta" class="form-control">
        </div>
    </div>
</div>

<!-- Listado de Concursos -->
<div id="listaConcursos" class="row">
    <?php if (!empty($concursos)): ?>
        <?php foreach ($concursos as $concurso): ?>
            <div class="col-md-6 col-lg-4 mb-4 filtro-item" data-estado="<?= $concurso['estado'] ?>"
                data-fecha="<?= $concurso['fecha_creacion'] ?>">
                <div class="card h-100 border-0 shadow-sm">

                    <img src="<?= user_image($concurso['imagen_url']) ?>" class="card-img-top" alt="Arte del concurso"
                        style="height: 180px; object-fit: cover;">

                    <div class="card-body">
                        <h5 class="card-title"><?= html_escape($concurso['titulo']) ?></h5>
                        <p class="card-text text-muted small">
                            <strong>Estado:</strong>
                            <span class="badge bg-<?=
                                $concurso['estado'] == 'nominacion' ? 'info' :
                                ($concurso['estado'] == 'votacion' ? 'success' : 'secondary')
                                ?>">
                                <?= ucfirst(html_escape($concurso['estado'])) ?>
                            </span><br>
                            <strong>Fecha:</strong> <?= date('d/m/Y', strtotime($concurso['fecha_creacion'])) ?><br>
                            <strong>Nominaciones:</strong> <?= $concurso['total_nominaciones'] ?>
                        </p>
                        <p class="card-text text-truncate" style="max-height: 4em;">
                            <?= html_escape(substr($concurso['descripcion'], 0, 150)) ?>
                            <?php if (strlen($concurso['descripcion']) > 150): ?>...<?php endif; ?>
                        </p>

                        <!-- Botones según rol y estado -->
                        <div class="d-grid gap-2">
                            <?php if ($this->session->userdata('rol') === 'administrador'): ?>
                                <a href="<?= base_url('admin/gestionar_nominados/' . $concurso['id']) ?>"
                                    class="btn btn-outline-primary btn-sm">
                                    Gestionar
                                </a>
                            <?php else: ?>
                                <?php if ($concurso['estado'] === 'nominacion'): ?>
                                    <?php if ($concurso['ya_nominado']): ?>
                                        <span class="btn btn-sm btn-secondary disabled" style="pointer-events: none;">Ya nominado</span>
                                    <?php else: ?>
                                        <a href="<?= base_url('usuario/nominar/' . $concurso['id']) ?>"
                                            class="btn btn-primary btn-sm">Nominar</a>
                                    <?php endif; ?>
                                <?php elseif ($concurso['estado'] === 'votacion'): ?>
                                    <?php if ($concurso['ya_voto']): ?>
                                        <span class="badge bg-success">Ya votó</span>
                                    <?php else: ?>
                                        <a href="<?= base_url('usuario/votar/' . $concurso['id']) ?>"
                                            class="btn btn-success btn-sm">Votar</a>
                                    <?php endif; ?>
                                <?php elseif ($concurso['estado'] === 'cerrado'): ?>
                                    <a href="<?= base_url('home/resultados/' . $concurso['id']) ?>"
                                        class="btn btn-info btn-sm">Ver Ganadores</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                No hay concursos disponibles en este momento.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Ganadores Recientes -->
<?php if (!empty($ganadores)): ?>
    <h3 class="mt-5 mb-3">Ganadores Recientes</h3>
    <div class="row">
        <?php foreach ($ganadores as $ganador): ?>
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm text-center">
                    <!-- Foto del ganador -->
                    <img src="<?= user_image($ganador['foto_url']) ?>"
                         class="card-img-top"
                         style="height: 150px; object-fit: cover;"
                         alt="Foto de <?= $ganador['nombres'] ?>">

                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <?= html_escape($ganador['nombres'] . ' ' . $ganador['apellidos']) ?>
                        </h5>
                        <p class="card-text">
                            <strong><?= html_escape($ganador['titulo_concurso']) ?></strong><br>
                            <small><?= html_escape($ganador['unidad']) ?> - <?= html_escape($ganador['ciudad']) ?></small><br>
                            <span class="badge bg-warning text-dark mt-2">Ganador <?= $ganador['posicion'] ?>°</span>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php $this->load->view('layouts/footer'); ?>
<!-- Filtro con JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const items = document.querySelectorAll('.filtro-item');
        const filtroEstado = document.getElementById('filtroEstado');
        const filtroFechaDesde = document.getElementById('filtroFechaDesde');
        const filtroFechaHasta = document.getElementById('filtroFechaHasta');

        function filtrar() {
            const estado = filtroEstado.value;
            const fechaDesde = filtroFechaDesde.value;
            const fechaHasta = filtroFechaHasta.value;

            items.forEach(item => {
                const itemEstado = item.dataset.estado;
                const itemFecha = item.dataset.fecha.split(' ')[0]; // Solo fecha

                let mostrar = true;

                if (estado && itemEstado !== estado) mostrar = false;
                if (fechaDesde && itemFecha < fechaDesde) mostrar = false;
                if (fechaHasta && itemFecha > fechaHasta) mostrar = false;

                item.style.display = mostrar ? 'block' : 'none';
            });
        }

        [filtroEstado, filtroFechaDesde, filtroFechaHasta].forEach(el => {
            el.addEventListener('change', filtrar);
        });
    });
</script>