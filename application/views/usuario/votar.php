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
        <strong>Vota por tu favorito</strong>
    </div>

    <?php if ($ya_voto): ?>
        <div class="alert alert-success text-center">
            <strong>✅ Ya ha ejercido su voto.</strong>
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

    <!-- Cuadrícula de Nominados -->
    <div class="row" id="lista-nominados">
        <?php if (!empty($nominados)): ?>
            <?php foreach ($nominados as $nom): ?>
                <div class="col-md-6 col-lg-4 mb-4 filtro-item"
                     data-ciudad="<?= $nom['ciudad'] ?>"
                     data-unidad="<?= $nom['unidad'] ?>"
                     data-nombre="<?= strtolower($nom['nombres'] . ' ' . $nom['apellidos']) ?>">
                    <div class="card h-100 shadow-sm text-center">
                        <!-- Foto con imagen por defecto -->
                        <img src="<?= user_image($nom['imagen_nominado']) ?>"
                             class="card-img-top" style="height: 150px; object-fit: cover;" alt="Foto de <?= $nom['nombres'] ?>">

                        <div class="card-body">
                            <h5 class="card-title"><?= html_escape($nom['nombres'] . ' ' . $nom['apellidos']) ?></h5>
                            <p class="card-text text-muted small">
                                <strong>Unidad:</strong> <?= html_escape($nom['unidad']) ?><br>
                                <strong>Ciudad:</strong> <?= html_escape($nom['ciudad']) ?><br>
                                <strong>Email:</strong> <?= html_escape($nom['email']) ?>
                            </p>

                            <!-- Botones de visualización -->
                            <div class="mb-2">
                                <?php if (!empty($nom['imagen_nominado'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalFoto<?= $nom['id'] ?>">
                                        Ver Foto
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary disabled" disabled>Ver Foto</button>
                                <?php endif; ?>

                                <?php if (!empty($nom['video_nominado'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalVideo<?= $nom['id'] ?>">
                                        Ver Video
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary disabled" disabled>Ver Video</button>
                                <?php endif; ?>
                            </div>

                            <!-- Formulario de voto -->
                            <?php if (!$ya_voto): ?>
                                <form action="<?= base_url('usuario/procesar_voto') ?>" method="post" onsubmit="return confirm('¿Confirmar voto por <?= addslashes($nom['nombres']) ?> <?= addslashes($nom['apellidos']) ?>?')">
                                    <input type="hidden" name="concurso_id" value="<?= $concurso['id'] ?>">
                                    <input type="hidden" name="nominado_id" value="<?= $nom['email'] ?>">
                                    <button type="submit" class="btn btn-success">Votar</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Ya votó</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal: Foto del Nominado Final -->
                <?php if (!empty($nom['imagen_nominado'])): ?>
                <div class="modal fade" id="modalFoto<?= $nom['id'] ?>" tabindex="-1" aria-labelledby="fotoLabel<?= $nom['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="fotoLabel<?= $nom['id'] ?>">Foto de <?= html_escape($nom['nombres']) ?> <?= html_escape($nom['apellidos']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="<?= base_url($nom['imagen_nominado']) ?>" class="img-fluid" alt="Foto de <?= $nom['nombres'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Modal: Video del Nominado Final -->
                <?php if (!empty($nom['video_nominado'])): ?>
                <div class="modal fade" id="modalVideo<?= $nom['id'] ?>" tabindex="-1" aria-labelledby="videoLabel<?= $nom['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="videoLabel<?= $nom['id'] ?>">Video de <?= html_escape($nom['nombres']) ?> <?= html_escape($nom['apellidos']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <video controls class="img-fluid" style="max-height: 400px;">
                                    <source src="<?= base_url($nom['video_nominado']) ?>" type="video/mp4">
                                    Tu navegador no soporta el elemento de video.
                                </video>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">No hay nominados finales disponibles para votar.</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-4">
        <a href="<?= base_url('home') ?>" class="btn btn-secondary">Regresar al Inicio</a>
    </div>
</div>

<?php $this->load->view('layouts/footer'); ?>
