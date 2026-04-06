<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso no autorizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 100px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-white bg-danger">
                    <div class="card-body text-center">
                        <h5 class="card-title">⛔ Acceso Denegado</h5>
                        <p class="card-text"><?= html_escape($mensaje) ?></p>
                        <a href="javascript:history.back()" class="btn btn-light">Regresar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>