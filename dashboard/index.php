<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Obtener información del usuario
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_email = $_SESSION['user_email'] ?? '';
$user_rol = $_SESSION['user_rol'] ?? 'usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestor de Finanzas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar para móvil -->
    <nav class="navbar navbar-expand-md navbar-dark bg-primary d-md-none">
        <div class="container-fluid">
            <span class="navbar-brand">💰 Gestor Finanzas</span>
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
                        <h5 class="text-white">💰 Gestor Finanzas</h5>
                        <small class="text-light">
                            <?php echo htmlspecialchars($user_name); ?>
                            <span class="badge bg-light text-primary ms-1">
                                <?php echo ucfirst($user_rol); ?>
                            </span>
                        </small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="ingresos/">
                                <i class="fas fa-plus-circle text-success me-2"></i>
                                Ingresos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="gastos/">
                                <i class="fas fa-minus-circle text-warning me-2"></i>
                                Gastos Variables
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="gastos-fijos/">
                                <i class="fas fa-calendar-alt text-danger me-2"></i>
                                Gastos Fijos
                            </a>
                        </li>
                        
                        <?php if ($user_rol === 'superadmin'): ?>
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="admin.php">
                                <i class="fas fa-crown text-warning me-2"></i>
                                Panel de Administración
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="usuarios/">
                                <i class="fas fa-users me-2"></i>
                                Gestión Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-light" href="configuracion/">
                                <i class="fas fa-cog me-2"></i>
                                Configuración
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                        <li class="nav-item">
                            <a class="nav-link text-light" href="../controllers/logout.php">
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
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-calendar me-1"></i>
                                <?php echo date('d/m/Y'); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resumen estadísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Ingresos (Mes Actual)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Gastos Variables
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Gastos Fijos
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Balance
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">$0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-balance-scale fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos y contenido adicional -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-area me-2"></i>
                                    Resumen Financiero
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">
                                        Aquí aparecerán los gráficos de tus finanzas cuando agregues datos.
                                    </p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="ingresos/" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus me-1"></i>
                                            Agregar Ingreso
                                        </a>
                                        <a href="gastos/" class="btn btn-warning btn-sm">
                                            <i class="fas fa-minus me-1"></i>
                                            Agregar Gasto
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-list me-2"></i>
                                    Acciones Rápidas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="ingresos/" class="btn btn-outline-success">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        Nuevo Ingreso
                                    </a>
                                    <a href="gastos/" class="btn btn-outline-warning">
                                        <i class="fas fa-minus-circle me-2"></i>
                                        Nuevo Gasto
                                    </a>
                                    <a href="gastos-fijos/" class="btn btn-outline-danger">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Gastos Fijos
                                    </a>
                                    <hr>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Última conexión: <?php echo date('d/m/Y H:i'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas de bienvenida -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>¡Bienvenido/a, <?php echo htmlspecialchars($user_name); ?>!</strong>
                    Has iniciado sesión correctamente en el Gestor de Finanzas.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS personalizado -->
    <script src="../assets/js/main.js"></script>
</body>
</html>
