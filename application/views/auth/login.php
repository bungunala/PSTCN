<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?> - Sistema de Postulacion de Concursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 400px;">
        <div class="card shadow">
            <div class="card-body">
                <h4 class="text-center mb-4">Sistema de Postulacion de Concursos</h4>
                <p class="text-muted text-center">correo institucional</p>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
                <?php endif; ?>

                <form action="<?= base_url('auth/authenticate') ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Institucional</label>
                        <input type="email" name="email" id="email" class="form-control" required placeholder="nombre@produccion.gob.ec">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>
            </div>
        </div>
        <p class="text-center mt-3 text-muted small">
            Etse login es temporal. La idea es que se integrará con LDAP/Zimbra.
        </p>
    </div>
</body>
</html>