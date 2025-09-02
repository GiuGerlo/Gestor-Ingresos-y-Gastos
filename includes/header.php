<?php
// Configurar zona horaria de Argentina para todo el sistema
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté definido para páginas del dashboard
if (basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    if (!isset($user)) {
        // Si no está definido, intentar obtenerlo desde la sesión o redirigir
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../index.php');
            exit();
        }
        $user = getCurrentUser();
    }
}

// Detectar el nivel de directorio para rutas relativas
$base_path = '';
$current_dir = dirname($_SERVER['SCRIPT_NAME']);

// Determinar la ruta base según la ubicación del archivo
if (strpos($current_dir, '/dashboard/') !== false && substr_count($current_dir, '/') > 2) {
    $base_path = '../../';
} elseif (strpos($current_dir, '/dashboard/') !== false) {
    $base_path = '../';
} else {
    $base_path = './';
}

// Configurar título por defecto si no está definido
if (!isset($page_title)) {
    $page_title = 'Ahorrito';
}

// Configurar página actual si no está definida
if (!isset($current_page)) {
    $current_page = '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#6548D5">
    <meta name="description" content="Sistema de gestión de finanzas personales - Control completo de ingresos, gastos y gastos fijos">
    <meta name="keywords" content="finanzas, gastos, ingresos, presupuesto, argentina">
    <meta name="author" content="Sistema de Finanzas">
    <title><?= htmlspecialchars($page_title) ?> - Ahorritoo</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= $base_path ?>assets/img/logo-completo.png" type="image/x-icon">
    <link rel="icon" href="<?= $base_path ?>assets/img/logo-original.png" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- DataTables CSS (solo si es necesario) -->
    <?php if (isset($include_datatables) && $include_datatables): ?>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <?php endif; ?>

    <!-- Chart.js (solo si es necesario) -->
    <?php if (isset($include_charts) && $include_charts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">

    <!-- CSS adicional específico de página -->
    <?php if (isset($additional_css)): ?>
        <?= $additional_css ?>
    <?php endif; ?>

    <!-- Meta tags para PWA (futuro) -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
</head>

<body class="d-flex flex-column min-vh-100" data-bs-theme="light">

    <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php'): ?>
        <!-- Navbar del Dashboard -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
            <div class="container-fluid px-4">
                <!-- Logo/Brand -->
                <a class="navbar-brand d-flex align-items-center" href="<?= $base_path ?>dashboard/">
                    <div class="icon-wrapper me-2" style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <span class="fw-bold">
                        <span class="text-dark">Finanzas</span><span class="text-secondary">Pro</span>
                    </span>
                </a>

                <!-- Botón toggle para móvil -->
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menú de navegación -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Menú principal -->
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page === 'dashboard') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page === 'ingresos') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/ingresos/">
                                <i class="fas fa-plus-circle me-1 text-success"></i>Ingresos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page === 'gastos') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/gastos/">
                                <i class="fas fa-minus-circle me-1 text-danger"></i>Gastos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page === 'gastos-fijos') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/gastos-fijos/">
                                <i class="fas fa-calendar-alt me-1 text-warning"></i>Gastos Fijos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= ($current_page === 'reportes') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/reportes/">
                                <i class="fas fa-chart-bar me-1 text-info"></i>Reportes
                            </a>
                        </li>

                        <!-- Menús solo para superadmin -->
                        <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'superadmin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?= (in_array($current_page, ['usuarios', 'configuracion'])) ? 'active' : '' ?>" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-cogs me-1 text-secondary"></i>Administración
                                </a>
                                <ul class="dropdown-menu shadow border-0">
                                    <li>
                                        <a class="dropdown-item <?= ($current_page === 'usuarios') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/usuarios/">
                                            <i class="fas fa-users me-2"></i>Gestión de Usuarios
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item <?= ($current_page === 'configuracion') ? 'active' : '' ?>" href="<?= $base_path ?>dashboard/configuracion/">
                                            <i class="fas fa-sliders-h me-2"></i>Configuración Global
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Información del usuario y menú -->
                    <ul class="navbar-nav">
                        <!-- Indicador de dinero disponible -->
                        <li class="nav-item d-none d-lg-flex align-items-center me-3">
                            <small class="text-muted">
                                <i class="fas fa-wallet me-1"></i>
                                <span id="dinero-disponible-nav">Cargando...</span>
                            </small>
                        </li>

                        <!-- Notificaciones -->
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 300px;">
                                <li>
                                    <h6 class="dropdown-header">Notificaciones</h6>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li id="notifications-container">
                                    <div class="text-center text-muted p-3">
                                        <i class="fas fa-bell-slash"></i>
                                        <br>No hay notificaciones
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <!-- Menú de usuario -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                                <span class="d-none d-md-inline">
                                    <?= isset($user) ? htmlspecialchars($user['nombre']) : 'Usuario' ?>
                                    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'superadmin'): ?>
                                        <small class="badge bg-warning text-dark ms-1">Admin</small>
                                    <?php endif; ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <h6 class="dropdown-header">
                                        <i class="fas fa-user-circle me-2"></i>Mi Cuenta
                                    </h6>
                                </li>
                                <li>
                                    <div class="dropdown-item-text">
                                        <small class="text-muted d-block"><?= isset($user) ? htmlspecialchars($user['email']) : '' ?></small>
                                        <small class="text-muted">
                                            Rol: <?= isset($_SESSION['user_rol']) ? ucfirst($_SESSION['user_rol']) : 'Usuario' ?>
                                        </small>
                                    </div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="toggleTheme()">
                                        <i class="fas fa-palette me-2"></i>Cambiar Tema
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= $base_path ?>controllers/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Mostrar alertas si existen -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['error_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="alert alert-warning alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['warning_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['warning_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['info_message'])): ?>
        <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['info_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['info_message']); ?>
    <?php endif; ?>

    <!-- Script para funcionalidades del header -->
    <script>
        // Configurar zona horaria Argentina para JavaScript
        const zonaHorariaArgentina = 'America/Argentina/Buenos_Aires';

        // Función para cambiar tema (preparación para futuro)
        function toggleTheme() {
            // Por ahora solo mostrar mensaje
            alert('Función de cambio de tema próximamente disponible');
        }

        // Actualizar dinero disponible en el navbar
        function actualizarDineroDisponible() {
            const elemento = document.getElementById('dinero-disponible-nav');
            if (elemento) {
                // Esta función se implementará cuando tengamos el dashboard listo
                fetch('<?= $base_path ?>dashboard/ajax/obtener_dinero_disponible.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            elemento.textContent = `$${data.dinero_disponible.toLocaleString('es-AR')}`;
                        }
                    })
                    .catch(error => {
                        console.log('Error obteniendo dinero disponible:', error);
                    });
            }
        }

        // Actualizar notificaciones
        function actualizarNotificaciones() {
            // Esta función se implementará cuando tengamos el sistema de notificaciones listo
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php'): ?>
                // Solo ejecutar en páginas del dashboard
                actualizarDineroDisponible();
                actualizarNotificaciones();

                // Actualizar cada 5 minutos
                setInterval(actualizarDineroDisponible, 300000);
                setInterval(actualizarNotificaciones, 300000);
            <?php endif; ?>
        });
    </script>