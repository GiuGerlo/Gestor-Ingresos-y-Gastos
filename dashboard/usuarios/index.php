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
$current_page = 'usuarios';
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
                        <i class="fas fa-users text-primary me-2"></i>
                        Gestión de Usuarios
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php echo $header_buttons; ?>
                        </div>
                    </div>
                </div>

                <!-- Contenido de gestión de usuarios -->
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Panel de Gestión de Usuarios</strong>
                    Aquí puedes administrar todos los usuarios del sistema.
                </div>

                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            Lista de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            La funcionalidad de gestión de usuarios se implementará aquí.
                        </p>
                        <p class="text-muted">
                            Incluirá: crear, editar, eliminar y gestionar roles de usuarios.
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
