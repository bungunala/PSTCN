<?php $this->load->view('layouts/header'); ?>

<!-- CDN de Confetti javascript -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

<div class="container mt-4">
    <h2><?= html_escape($concurso['titulo']) ?> - Ganadores</h2>
    <p class="text-muted"><?= html_escape($concurso['descripcion']) ?></p>

    <?php if ($concurso['imagen_url']): ?>
        <div class="mb-4">
            <img src="<?= base_url($concurso['imagen_url']) ?>" class="img-fluid rounded shadow" style="width: 100%; max-height: 350px; object-fit: cover;">
        </div>
    <?php endif; ?>

    <div class="alert alert-info text-center">
        <strong>🏆 Resultados Oficiales</strong>
    </div>

    <div class="row mt-4">
        <?php foreach ($ganadores as $ganador): ?>
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow-sm">
                    <!-- Foto del usuario (perfil) -->
                    <img src="<?= user_image($ganador['foto_url']) ?>"
                         class="card-img-top"
                         style="height: 150px; object-fit: cover;"
                         alt="Foto de <?= $ganador['nombres'] ?>">

                    <div class="card-body">
                        <h5 class="card-title"><?= html_escape($ganador['nombres'] . ' ' . $ganador['apellidos']) ?></h5>
                        <p class="card-text">
                            <strong><?= html_escape($ganador['unidad']) ?></strong><br>
                            <span class="badge bg-warning text-dark">Puesto <?= $ganador['posicion'] ?>°</span>
                        </p>

                        <!-- Botones: Ver Foto del concurso y Ver Video -->
                        <div class="mt-2">
                            <?php if (!empty($ganador['imagen_nominado'])): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalFoto<?= $ganador['usuario_id'] ?>">
                                    Ver Foto
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary disabled" disabled>
                                    Ver Foto
                                </button>
                            <?php endif; ?>

                            <?php if (!empty($ganador['video_nominado'])): ?>
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalVideo<?= $ganador['usuario_id'] ?>">
                                    Ver Video
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary disabled" disabled>
                                    Ver Video
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: Foto del Nominado Final (asociada al concurso) -->
            <?php if (!empty($ganador['imagen_nominado'])): ?>
            <div class="modal fade" id="modalFoto<?= $ganador['usuario_id'] ?>" tabindex="-1" aria-labelledby="fotoLabel<?= $ganador['usuario_id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fotoLabel<?= $ganador['usuario_id'] ?>">Foto del Concurso: <?= html_escape($ganador['nombres']) ?> <?= html_escape($ganador['apellidos']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="<?= base_url($ganador['imagen_nominado']) ?>" class="img-fluid" alt="Foto del concurso de <?= $ganador['nombres'] ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Modal: Video del Nominado Final -->
            <?php if (!empty($ganador['video_nominado'])): ?>
            <div class="modal fade" id="modalVideo<?= $ganador['usuario_id'] ?>" tabindex="-1" aria-labelledby="videoLabel<?= $ganador['usuario_id'] ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="videoLabel<?= $ganador['usuario_id'] ?>">Video de <?= html_escape($ganador['nombres']) ?> <?= html_escape($ganador['apellidos']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <video controls class="img-fluid" style="max-height: 400px;">
                                <source src="<?= base_url($ganador['video_nominado']) ?>" type="video/mp4">
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
    </div>

    <div class="text-center mt-4">
        <a href="<?= base_url('home') ?>" class="btn btn-secondary">Regresar al Inicio</a>
    </div>
</div>

<!-- Script de Confetti -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($ganadores)): ?>
    // Efecto de confeti solo si hay ganadores
    const duration = 3000; // 3 segundos
    const end = Date.now() + duration;

    (function frame() {
        confetti({
            particleCount: 4,
            angle: 60,
            spread: 55,
            origin: { x: 0 },
            colors: ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#6c757d']
        });
        confetti({
            particleCount: 4,
            angle: 120,
            spread: 55,
            origin: { x: 1 },
            colors: ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#6c757d']
        });

        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    }());
    <?php endif; ?>
});
</script>

<?php $this->load->view('layouts/footer'); ?>
