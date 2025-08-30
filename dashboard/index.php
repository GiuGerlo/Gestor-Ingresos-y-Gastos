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

// Variables para el header dinámico
$header_buttons = '<button type="button" class="btn btn-sm btn-outline-secondary">
    <i class="fas fa-clock me-1"></i>
    ' . date('H:i') . '
</button>';

// Incluir header
include 'includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar dinámico -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>
                        Dashboard
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php echo $header_buttons; ?>
                        </div>
                    </div>
                </div>

                <!-- Contenido del dashboard -->
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>¡Bienvenido, <?php echo htmlspecialchars($user_name); ?>!</strong>
                    Este es tu panel de control principal para gestionar tus finanzas personales.
                </div>

                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Resumen Financiero
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Aquí se mostrará un resumen de tus ingresos y gastos.</p>
                                <!-- El contenido del resumen se agregará aquí -->
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-tasks me-2"></i>
                                    Acciones Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="ingresos/" class="btn btn-success">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        Agregar Ingreso
                                    </a>
                                    <a href="gastos/" class="btn btn-warning">
                                        <i class="fas fa-minus-circle me-2"></i>
                                        Registrar Gasto
                                    </a>
                                    <a href="gastos-fijos/" class="btn btn-danger">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        Gestionar Gastos Fijos
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Más contenido del dashboard se puede agregar aquí -->
            </main>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
