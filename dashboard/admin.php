<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado y sea superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión para obtener estadísticas
require_once '../config/connect.php';

// Obtener información del usuario
$user_name = $_SESSION['user_name'] ?? 'Super Administrador';
$user_email = $_SESSION['user_email'] ?? '';

// Obtener estadísticas generales
try {
    // Total de usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $total_usuarios = $stmt->fetch()['total'];
    
    // Usuarios registrados este mes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $usuarios_mes = $stmt->fetch()['total'];
    
    // Total de categorías
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
    $total_categorias = $stmt->fetch()['total'];
    
    // Total de métodos de pago
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 1");
    $total_metodos = $stmt->fetch()['total'];
    
    // Total de transacciones (ingresos + gastos + gastos_fijos)
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM ingresos) +
            (SELECT COUNT(*) FROM gastos) +
            (SELECT COUNT(*) FROM gastos_fijos) as total
    ");
    $total_transacciones = $stmt->fetch()['total'];
    
    // Usuarios más activos (últimos 30 días)
    $stmt = $pdo->query("
        SELECT u.nombre, u.email, 
               COUNT(DISTINCT COALESCE(i.id, 0)) + COUNT(DISTINCT COALESCE(g.id, 0)) + COUNT(DISTINCT COALESCE(gf.id, 0)) as actividad
        FROM usuarios u
        LEFT JOIN ingresos i ON u.id = i.usuario_id AND i.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        LEFT JOIN gastos g ON u.id = g.usuario_id AND g.fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        LEFT JOIN gastos_fijos gf ON u.id = gf.usuario_id AND gf.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        WHERE u.activo = 1 AND u.rol = 'usuario'
        GROUP BY u.id, u.nombre, u.email
        ORDER BY actividad DESC
        LIMIT 5
    ");
    $usuarios_activos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
    $total_usuarios = $usuarios_mes = $total_categorias = $total_metodos = $total_transacciones = 0;
    $usuarios_activos = [];
}

// Variables para el header dinámico
$header_buttons = '<button type="button" class="btn btn-sm btn-outline-secondary">
    <i class="fas fa-calendar me-1"></i>
    ' . date('d/m/Y H:i') . '
</button>';

// Variables globales necesarias para estadísticas del sidebar
$GLOBALS['total_usuarios'] = $total_usuarios;
$GLOBALS['total_categorias'] = $total_categorias;
$GLOBALS['total_metodos'] = $total_metodos;

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
                        Panel de Administración
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <?php echo $header_buttons; ?>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas principales -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Usuarios
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_usuarios; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Usuarios Este Mes
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $usuarios_mes; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-plus fa-2x text-success"></i>
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
                                            Total Transacciones
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_transacciones; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exchange-alt fa-2x text-warning"></i>
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
                                            Configuraciones
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_categorias + $total_metodos; ?></div>
                                        <small class="text-muted"><?php echo $total_categorias; ?> Cat. + <?php echo $total_metodos; ?> Métodos</small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cogs fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulos principales -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="mb-3">
                            <i class="fas fa-cubes text-primary me-2"></i>
                            Módulos de Administración
                        </h4>
                    </div>
                    
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow h-100 hover-card">
                            <div class="card-body text-center p-4">
                                <div class="icon-circle bg-primary text-white mb-3 mx-auto">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <h5 class="card-title">Gestión de Usuarios</h5>
                                <p class="card-text text-muted">
                                    Administra usuarios, roles, permisos y monitorea la actividad del sistema.
                                </p>
                                <div class="mb-3">
                                    <span class="badge bg-primary me-2"><?php echo $total_usuarios; ?> usuarios</span>
                                    <span class="badge bg-success"><?php echo $usuarios_mes; ?> este mes</span>
                                </div>
                                <a href="usuarios/" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Administrar Usuarios
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card shadow h-100 hover-card">
                            <div class="card-body text-center p-4">
                                <div class="icon-circle bg-success text-white mb-3 mx-auto">
                                    <i class="fas fa-tags fa-2x"></i>
                                </div>
                                <h5 class="card-title">Gestión de Categorías</h5>
                                <p class="card-text text-muted">
                                    Crea y administra categorías para organizar ingresos y gastos de todos los usuarios.
                                </p>
                                <div class="mb-3">
                                    <span class="badge bg-success me-2"><?php echo $total_categorias; ?> categorías</span>
                                    <span class="badge bg-info">Global</span>
                                </div>
                                <a href="categorias/" class="btn btn-success">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Administrar Categorías
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card shadow h-100 hover-card">
                            <div class="card-body text-center p-4">
                                <div class="icon-circle bg-warning text-white mb-3 mx-auto">
                                    <i class="fas fa-credit-card fa-2x"></i>
                                </div>
                                <h5 class="card-title">Métodos de Pago</h5>
                                <p class="card-text text-muted">
                                    Configura métodos de pago disponibles para todos los usuarios del sistema.
                                </p>
                                <div class="mb-3">
                                    <span class="badge bg-warning text-dark me-2"><?php echo $total_metodos; ?> métodos</span>
                                    <span class="badge bg-info">Compartido</span>
                                </div>
                                <a href="metodos-pago/" class="btn btn-warning text-dark">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Administrar Métodos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad reciente y usuarios activos -->
                <div class="row">
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Estadísticas del Sistema
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">
                                        Aquí se mostrarán gráficos de uso del sistema y estadísticas detalladas.
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Panel de estadísticas en desarrollo
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-fire me-2"></i>
                                    Usuarios Más Activos
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($usuarios_activos)): ?>
                                    <?php foreach ($usuarios_activos as $index => $usuario): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rank-badge">
                                            <?php echo $index + 1; ?>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($usuario['nombre']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($usuario['email']); ?></small>
                                        </div>
                                        <span class="badge bg-primary"><?php echo $usuario['actividad']; ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted">
                                        <i class="fas fa-user-clock fa-2x mb-3"></i>
                                        <p>No hay actividad reciente</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas de bienvenida -->
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-crown me-2"></i>
                    <strong>¡Bienvenido al Panel de Administración, <?php echo htmlspecialchars($user_name); ?>!</strong>
                    Tienes acceso completo a todas las funciones administrativas del sistema.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </main>
        </div>
    </div>

    <!-- Estilos adicionales específicos de admin -->
    <style>
        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .rank-badge {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>

<?php include 'includes/footer.php'; ?>
