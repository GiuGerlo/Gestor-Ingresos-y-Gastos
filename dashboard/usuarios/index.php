<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado y sea superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Super Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Gestor de Finanzas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar para móvil -->
    <nav class="navbar navbar-expand-md navbar-dark bg-primary d-md-none">
        <div class="container-fluid">
            <span class="navbar-brand">👥 Usuarios</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebar">
                <div class="sidebar-sticky">
                    <div class="text-center mb-4 pt-3">
                        <h5 class="text-white">👥 Usuarios</h5>
                        <small class="text-light">Panel de Administración</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../admin.php">
                                <i class="fas fa-arrow-left me-2"></i>
                                Volver al Panel Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="index.php">
                                <i class="fas fa-users me-2"></i>
                                Gestión de Usuarios
                            </a>
                        </li>
                        
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../categorias/">
                                <i class="fas fa-tags me-2"></i>
                                Gestión de Categorías
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../metodos-pago/">
                                <i class="fas fa-credit-card me-2"></i>
                                Métodos de Pago
                            </a>
                        </li>
                        
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../../controllers/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-users text-primary me-2"></i>
                        Gestión de Usuarios
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            Nuevo Usuario
                        </button>
                    </div>
                </div>

                <!-- Contenido del módulo -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-body text-center p-5">
                                <i class="fas fa-users fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted mb-3">Módulo de Gestión de Usuarios</h4>
                                <p class="text-muted mb-4">
                                    Este módulo estará disponible próximamente. Aquí podrás administrar todos los usuarios del sistema.
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="../admin.php" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Volver al Panel
                                    </a>
                                    <a href="../categorias/" class="btn btn-outline-success">
                                        <i class="fas fa-tags me-2"></i>
                                        Ir a Categorías
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
