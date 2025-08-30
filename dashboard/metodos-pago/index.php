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

// Variables para el header dinámico - ajustadas para subcarpeta
$current_page = 'metodos-pago';
$header_buttons = '<a href="../admin.php" class="btn btn-sm btn-outline-secondary">
    <i class="fas fa-arrow-left me-1"></i>
    Volver al Panel
</a>';

// Ajustar paths para subcarpeta
$base_path = '../';

// Incluir header
include '../includes/header.php';
?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar dinámico -->
            <?php include '../includes/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-credit-card text-warning me-2"></i>
                        Gestión de Métodos de Pago
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php echo $header_buttons; ?>
                        </div>
                    </div>
                </div>

                <!-- Contenido de gestión de métodos de pago -->
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-credit-card me-2"></i>
                    <strong>Panel de Gestión de Métodos de Pago</strong>
                    Administra los métodos de pago disponibles para todos los usuarios.
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list me-2"></i>
                                    Lista de Métodos de Pago
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    La funcionalidad de gestión de métodos de pago se implementará aquí.
                                </p>
                                <p class="text-muted">
                                    Incluirá: crear, editar, eliminar y gestionar métodos de pago disponibles.
                                </p>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Ejemplos de métodos:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-credit-card text-primary me-2"></i>Tarjeta de Crédito</li>
                                            <li><i class="fas fa-university text-success me-2"></i>Transferencia Bancaria</li>
                                            <li><i class="fas fa-money-bill text-warning me-2"></i>Efectivo</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary">Métodos digitales:</h6>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-mobile-alt text-info me-2"></i>Billetera Digital</li>
                                            <li><i class="fas fa-qrcode text-secondary me-2"></i>QR/Transferencia</li>
                                            <li><i class="fab fa-paypal text-primary me-2"></i>PayPal</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-plus me-2"></i>
                                    Nuevo Método de Pago
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    Formulario para crear nuevos métodos de pago se mostrará aquí.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
