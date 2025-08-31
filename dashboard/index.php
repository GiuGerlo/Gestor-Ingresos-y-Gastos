<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión a la base de datos
require_once '../config/connect.php';

// Obtener información del usuario
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_email = $_SESSION['user_email'] ?? '';
$user_rol = $_SESSION['user_rol'] ?? 'usuario';
$user_id = $_SESSION['user_id'];

// Obtener mes y año desde parámetros GET o usar actual
$mes_seleccionado = $_GET['mes'] ?? date('n');
$ano_seleccionado = $_GET['ano'] ?? date('Y');

// Validar mes y año
$mes_seleccionado = max(1, min(12, (int)$mes_seleccionado));
$ano_seleccionado = max(2020, min(2030, (int)$ano_seleccionado));

try {
    // Estadísticas de ingresos del mes seleccionado
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_ingresos 
        FROM ingresos 
        WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $total_ingresos = $stmt->fetch()['total_ingresos'];

    // Estadísticas de gastos del mes seleccionado
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_gastos 
        FROM gastos 
        WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $total_gastos = $stmt->fetch()['total_gastos'];

    // Estadísticas de gastos fijos activos
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_gastos_fijos 
        FROM gastos_fijos 
        WHERE user_id = ? AND activo = 1
    ");
    $stmt->execute([$user_id]);
    $total_gastos_fijos = $stmt->fetch()['total_gastos_fijos'];

    // Últimas transacciones del mes seleccionado
    $stmt = $pdo->prepare("
        (SELECT 'ingreso' as tipo, DATE_FORMAT(i.fecha, '%Y-%m-%d') as fecha, c.nombre as categoria, i.descripcion, i.monto, mp.color
         FROM ingresos i 
         JOIN categorias c ON i.categoria_id = c.id 
         JOIN metodos_pago mp ON i.metodo_pago_id = mp.id
         WHERE i.user_id = ? AND YEAR(i.fecha) = ? AND MONTH(i.fecha) = ?
         ORDER BY i.fecha DESC, i.created_at DESC 
         LIMIT 5)
        UNION ALL
        (SELECT 'gasto' as tipo, DATE_FORMAT(g.fecha, '%Y-%m-%d') as fecha, c.nombre as categoria, g.descripcion, g.monto, mp.color
         FROM gastos g 
         JOIN categorias c ON g.categoria_id = c.id 
         JOIN metodos_pago mp ON g.metodo_pago_id = mp.id
         WHERE g.user_id = ? AND YEAR(g.fecha) = ? AND MONTH(g.fecha) = ?
         ORDER BY g.fecha DESC, g.created_at DESC 
         LIMIT 5)
        ORDER BY fecha DESC
        LIMIT 8
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado, $user_id, $ano_seleccionado, $mes_seleccionado]);
    $ultimas_transacciones = $stmt->fetchAll();

    // Próximos gastos fijos (dentro de 7 días) - Usando zona horaria Argentina
    $hoy = date('Y-m-d');
    $dia_actual = (int)date('j');
    
    $stmt = $pdo->prepare("
        SELECT 
            nombre, 
            monto, 
            dia_mes,
            CASE 
                WHEN dia_mes >= ? THEN 
                    DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', LEAST(dia_mes, DAY(LAST_DAY(CURDATE())))))
                ELSE 
                    DATE(CONCAT(
                        CASE WHEN MONTH(CURDATE()) = 12 THEN YEAR(CURDATE()) + 1 ELSE YEAR(CURDATE()) END,
                        '-',
                        CASE WHEN MONTH(CURDATE()) = 12 THEN 1 ELSE MONTH(CURDATE()) + 1 END,
                        '-',
                        LEAST(dia_mes, DAY(LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))))
                    ))
            END as proxima_fecha
        FROM gastos_fijos 
        WHERE user_id = ? AND activo = 1
        HAVING proxima_fecha <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY proxima_fecha
        LIMIT 5
    ");
    $stmt->execute([$dia_actual, $user_id]);
    $proximos_gastos_fijos = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
    $total_ingresos = $total_gastos = $total_gastos_fijos = 0;
    $ultimas_transacciones = $proximos_gastos_fijos = [];
}

// Calcular balance
$balance_disponible = $total_ingresos - ($total_gastos + $total_gastos_fijos);

// Variables para el header dinámico
$current_page = 'dashboard';

// Array de meses para el dropdown
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

$header_buttons = '<div class="btn-group">
    <form method="GET" class="d-flex gap-2">
        <select name="mes" class="form-select form-select-sm" onchange="this.form.submit()">
            ' . array_reduce(array_keys($meses), function($html, $num_mes) use ($meses, $mes_seleccionado) {
                $selected = $num_mes == $mes_seleccionado ? 'selected' : '';
                return $html . "<option value=\"{$num_mes}\" {$selected}>{$meses[$num_mes]}</option>";
            }, '') . '
        </select>
        <select name="ano" class="form-select form-select-sm" onchange="this.form.submit()">
            ' . array_reduce(range(2020, 2030), function($html, $ano) use ($ano_seleccionado) {
                $selected = $ano == $ano_seleccionado ? 'selected' : '';
                return $html . "<option value=\"{$ano}\" {$selected}>{$ano}</option>";
            }, '') . '
        </select>
    </form>
    <button type="button" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-clock me-1"></i>
        ' . date('H:i') . '
    </button>
</div>';

// Incluir header
include 'includes/header.php';
?>

<style>
/* Estilos para el dropdown del mes */
.btn-toolbar form {
    align-items: center;
}

.btn-toolbar .form-select-sm {
    font-size: 0.875rem;
    min-width: 120px;
}

.btn-toolbar .form-select-sm:first-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-toolbar .form-select-sm:last-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    border-left: 0;
}

/* Mejoras visuales para las cards de estadísticas */
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

/* Responsive para el header */
@media (max-width: 768px) {
    .btn-toolbar {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-toolbar form {
        width: 100%;
    }
    
    .btn-toolbar .form-select-sm {
        min-width: auto;
        flex: 1;
    }
}
</style>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar dinámico -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
                <!-- Header del Dashboard -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-tachometer-alt text-primary me-2"></i>
                            Dashboard Financiero
                            <small class="text-muted">- <?php echo $meses[$mes_seleccionado] . ' ' . $ano_seleccionado; ?></small>
                        </h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user me-1"></i>
                            Bienvenido, <?php echo htmlspecialchars($user_name); ?>
                        </p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <?php echo $header_buttons; ?>
                    </div>
                </div>

                <!-- Tarjetas de Estadísticas -->
                <div class="row mb-4">
                    <!-- Balance Disponible -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-primary bg-gradient rounded-circle p-3">
                                            <i class="fas fa-wallet text-white fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1">Balance Disponible</h6>
                                        <h4 class="mb-0 <?php echo $balance_disponible >= 0 ? 'text-success' : 'text-danger'; ?>">
                                            $<?php echo number_format($balance_disponible, 2, ',', '.'); ?>
                                        </h4>
                                        <small class="text-muted">
                                            <?php echo $balance_disponible >= 0 ? 'Superávit' : 'Déficit'; ?> del mes
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Ingresos -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success bg-gradient rounded-circle p-3">
                                            <i class="fas fa-arrow-up text-white fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1">Ingresos del Mes</h6>
                                        <h4 class="mb-0 text-success">
                                            $<?php echo number_format($total_ingresos, 2, ',', '.'); ?>
                                        </h4>
                                        <small class="text-muted"><?php echo $meses[$mes_seleccionado] . ' ' . $ano_seleccionado; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Gastos -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning bg-gradient rounded-circle p-3">
                                            <i class="fas fa-arrow-down text-white fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1">Gastos del Mes</h6>
                                        <h4 class="mb-0 text-warning">
                                            $<?php echo number_format($total_gastos, 2, ',', '.'); ?>
                                        </h4>
                                        <small class="text-muted">Variables</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gastos Fijos -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-danger bg-gradient rounded-circle p-3">
                                            <i class="fas fa-calendar-alt text-white fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="text-muted mb-1">Gastos Fijos</h6>
                                        <h4 class="mb-0 text-danger">
                                            $<?php echo number_format($total_gastos_fijos, 2, ',', '.'); ?>
                                        </h4>
                                        <small class="text-muted">Mensuales</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Módulos Principales -->
                <div class="row mb-4">
                    <!-- Módulo Ingresos -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-success text-white border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-plus-circle me-2"></i>
                                    Módulo de Ingresos
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Gestiona todos tus ingresos de manera fácil y organizada.</p>
                                
                                <div class="d-grid gap-2">
                                    <a href="ingresos/" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>
                                        Agregar Nuevo Ingreso
                                    </a>
                                    <a href="ingresos/" class="btn btn-outline-success">
                                        <i class="fas fa-list me-2"></i>
                                        Ver Historial de Ingresos
                                    </a>
                                </div>

                                <hr>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Este mes:</small>
                                    <span class="badge bg-success fs-6">
                                        $<?php echo number_format($total_ingresos, 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Módulo Gastos -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-warning text-white border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-minus-circle me-2"></i>
                                    Módulo de Gastos
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Registra y controla todos tus gastos variables.</p>
                                
                                <div class="d-grid gap-2">
                                    <a href="gastos/" class="btn btn-warning">
                                        <i class="fas fa-minus me-2"></i>
                                        Registrar Nuevo Gasto
                                    </a>
                                    <a href="gastos/" class="btn btn-outline-warning">
                                        <i class="fas fa-chart-pie me-2"></i>
                                        Ver Análisis de Gastos
                                    </a>
                                </div>

                                <hr>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Este mes:</small>
                                    <span class="badge bg-warning fs-6">
                                        $<?php echo number_format($total_gastos, 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Módulo Gastos Fijos -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-danger text-white border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Módulo de Gastos Fijos
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Organiza tus gastos recurrentes y suscripciones.</p>
                                
                                <div class="d-grid gap-2">
                                    <a href="gastos-fijos/" class="btn btn-danger">
                                        <i class="fas fa-plus me-2"></i>
                                        Agregar Gasto Fijo
                                    </a>
                                    <a href="gastos-fijos/" class="btn btn-outline-danger">
                                        <i class="fas fa-bell me-2"></i>
                                        Ver Recordatorios
                                    </a>
                                </div>

                                <hr>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Mensual:</small>
                                    <span class="badge bg-danger fs-6">
                                        $<?php echo number_format($total_gastos_fijos, 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Actividad Reciente -->
                <div class="row">
                    <!-- Últimas Transacciones -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Actividad Reciente - <?php echo $meses[$mes_seleccionado]; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($ultimas_transacciones)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No hay transacciones recientes</p>
                                        <small class="text-muted">Comienza agregando tu primer ingreso o gasto</small>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($ultimas_transacciones as $transaccion): ?>
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="rounded-circle p-2" style="background-color: <?php echo $transaccion['color']; ?>20;">
                                                            <i class="fas fa-<?php echo $transaccion['tipo'] === 'ingreso' ? 'arrow-up text-success' : 'arrow-down text-danger'; ?>"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <h6 class="mb-1"><?php echo htmlspecialchars($transaccion['descripcion']); ?></h6>
                                                                <small class="text-muted">
                                                                    <span class="badge" style="background-color: <?php echo $transaccion['color']; ?>;">
                                                                        <?php echo htmlspecialchars($transaccion['categoria']); ?>
                                                                    </span>
                                                                    • <?php echo date('d/m/Y', strtotime($transaccion['fecha'])); ?>
                                                                </small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="fw-bold <?php echo $transaccion['tipo'] === 'ingreso' ? 'text-success' : 'text-danger'; ?>">
                                                                    <?php echo $transaccion['tipo'] === 'ingreso' ? '+' : '-'; ?>$<?php echo number_format($transaccion['monto'], 2, ',', '.'); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Próximos Gastos Fijos -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light border-0">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bell me-2"></i>
                                    Próximos Vencimientos
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($proximos_gastos_fijos)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                        <p class="text-muted mb-0">¡Todo al día!</p>
                                        <small class="text-muted">No hay gastos fijos próximos</small>
                                    </div>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($proximos_gastos_fijos as $gasto_fijo): ?>
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($gasto_fijo['nombre']); ?></h6>
                                                        <small class="text-muted">
                                                            Vence: <?php echo date('d/m/Y', strtotime($gasto_fijo['proxima_fecha'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="fw-bold text-danger">
                                                            $<?php echo number_format($gasto_fijo['monto'], 2, ',', '.'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="gastos-fijos/" class="btn btn-sm btn-outline-danger">
                                            Ver todos los gastos fijos
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
