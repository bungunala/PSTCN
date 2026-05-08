<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Postulacion de Concursos - MPCEI</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-left: 40px;
            padding-right: 40px;
        }
        .navbar {
            background: linear-gradient(135deg, #003366, #0055a4);
        }
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
        }
        .footer {
            background-color: #003366;
            color: #ccc;
            padding: 20px 0;
            margin-top: 60px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        .btn-primary {
            background: #0055a4;
            border: none;
        }
        .btn-primary:hover {
            background: #003366;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('home') ?>">
                <strong>Concursos MPCEI</strong>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">Hola, <?= $this->session->userdata('nombre_completo') ?></span>
                <a class="nav-link" href="<?= base_url('auth/logout') ?>">Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4" style="max-width: 1400px; margin: 0 auto;">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>