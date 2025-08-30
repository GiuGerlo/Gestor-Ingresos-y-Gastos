<?php
/**
 * SIDEBAR DINÁMICO DEL DASHBOARD
 * ==============================
 * Include para el sidebar que se adapta según el rol del usuario y la página actual
 */

// Verificar que las variables de sesión estén disponibles
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_rol = $_SESSION['user_rol'] ?? 'usuario';

// Detectar la página actual para marcar el enlace activo
$current_script = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Función para determinar si un enlace está activo
function isActive($page, $dir = '') {
    global $current_script, $current_dir;
    
    if ($dir) {
        return $current_dir === $dir ? 'active' : '';
    }
    return $current_script === $page ? 'active' : '';
}

// Determinar la ruta base según el nivel de directorio
$base_path_calc = '';
if ($current_dir !== 'dashboard') {
    $base_path_calc = '../';
}

// Usar la variable $base_path si está definida, sino usar la calculada
$final_base_path = isset($base_path) ? $base_path : $base_path_calc;

// Incluir conexión para obtener estadísticas en tiempo real
try {
    if (!isset($pdo)) {
        require_once $final_base_path . '../config/connect.php';
    }
    
    // Usar las estadísticas globales si están disponibles
    if (isset($GLOBALS['total_usuarios'])) {
        $total_usuarios = $GLOBALS['total_usuarios'];
        $total_categorias = $GLOBALS['total_categorias'];
        $total_metodos = $GLOBALS['total_metodos'];
    } else {
        // Obtener estadísticas actualizadas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
        $total_usuarios = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
        $total_categorias = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 1");
        $total_metodos = $stmt->fetch()['total'] ?? 0;
    }
    
} catch (Exception $e) {
    $total_usuarios = $total_categorias = $total_metodos = 0;
}
?>

<!-- Navbar para móvil -->
<nav class="navbar navbar-expand-md navbar-dark bg-primary d-md-none">
    <div class="container-fluid">
        <span class="navbar-brand">
            <?php if ($user_rol === 'superadmin'): ?>
                👑 Panel Admin
            <?php else: ?>
                💰 Gestor Finanzas
            <?php endif; ?>
        </span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-controls="sidebar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>

<!-- Sidebar -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse" id="sidebar">
    <div class="sidebar-sticky">
        <div class="text-center mb-4 pt-3">
            <?php if ($user_rol === 'superadmin'): ?>
                <h5 class="text-white">Panel Admin</h5>
                <small class="text-light">
                    <?php echo htmlspecialchars($user_name); ?>
                </small>
            <?php else: ?>
                <h5 class="text-white">Gestor Finanzas</h5>
                <small class="text-light">
                    <?php echo htmlspecialchars($user_name); ?>
                    <span class="badge bg-light text-primary ms-1">
                        <?php echo ucfirst($user_rol); ?>
                    </span>
                </small>
            <?php endif; ?>
        </div>
        
        <ul class="nav flex-column">
            <!-- Navegación principal -->
            <?php if ($user_rol === 'superadmin'): ?>
                <!-- Panel de administración -->
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($current_script === 'admin') ? 'active' : ''; ?>" href="<?php echo $final_base_path; ?>admin.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Panel Admin
                    </a>
                </li>
                
                <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                <li class="nav-item">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                        <span>Gestión del Sistema</span>
                    </h6>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo isActive('index', 'usuarios'); ?>" href="<?php echo $final_base_path; ?>usuarios/">
                        <i class="fas fa-users me-2"></i>
                        Gestión de Usuarios
                        <span class="badge bg-info ms-auto"><?php echo $total_usuarios; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo isActive('index', 'categorias'); ?>" href="<?php echo $final_base_path; ?>categorias/">
                        <i class="fas fa-tags me-2"></i>
                        Gestión de Categorías
                        <span class="badge bg-success ms-auto"><?php echo $total_categorias; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo isActive('index', 'metodos-pago'); ?>" href="<?php echo $final_base_path; ?>metodos-pago/">
                        <i class="fas fa-credit-card me-2"></i>
                        Métodos de Pago
                        <span class="badge bg-warning ms-auto"><?php echo $total_metodos; ?></span>
                    </a>
                </li>
                
                <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                <li class="nav-item">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                        <span>Vista de Usuario</span>
                    </h6>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo ($current_script === 'index' && $current_dir === 'dashboard') ? 'active' : ''; ?>" href="<?php echo $final_base_path; ?>index.php">
                        <i class="fas fa-user me-2"></i>
                        Vista Usuario Normal
                    </a>
                </li>
            <?php else: ?>
                <!-- Vista de usuario normal -->
                <li class="nav-item">
                    <a class="nav-link text-white <?php echo ($current_script === 'index' && $current_dir === 'dashboard') ? 'active' : ''; ?>" href="<?php echo $final_base_path; ?>index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo isActive('index', 'ingresos'); ?>" href="<?php echo $final_base_path; ?>ingresos/">
                        <i class="fas fa-plus-circle text-success me-2"></i>
                        Ingresos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo isActive('index', 'gastos'); ?>" href="<?php echo $final_base_path; ?>gastos/">
                        <i class="fas fa-minus-circle text-warning me-2"></i>
                        Gastos Variables
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-light <?php echo isActive('index', 'gastos-fijos'); ?>" href="<?php echo $final_base_path; ?>gastos-fijos/">
                        <i class="fas fa-calendar-alt text-danger me-2"></i>
                        Gastos Fijos
                    </a>
                </li>
            <?php endif; ?>
            
            <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
            <li class="nav-item">
                <a class="nav-link text-light" href="<?php echo $final_base_path; ?>../controllers/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</nav>
