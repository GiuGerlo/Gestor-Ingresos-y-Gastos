<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Incluir conexión a la base de datos
require_once '../../config/connect.php';

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_id = $_SESSION['user_id'];

// Obtener mes y año desde parámetros GET o usar actual
$mes_seleccionado = $_GET['mes'] ?? date('n');
$ano_seleccionado = $_GET['ano'] ?? date('Y');

// Validar que sean valores válidos
if (!is_numeric($mes_seleccionado) || $mes_seleccionado < 1 || $mes_seleccionado > 12) {
    $mes_seleccionado = date('n');
}
if (!is_numeric($ano_seleccionado) || $ano_seleccionado < 2020 || $ano_seleccionado > date('Y') + 5) {
    $ano_seleccionado = date('Y');
}

// Obtener estadísticas de ingresos
try {
    // Total ingresos del período seleccionado
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_mes
        FROM ingresos 
        WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $total_mes = $stmt->fetch()['total_mes'];

    // Contar ingresos del período seleccionado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as cantidad_mes
        FROM ingresos 
        WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $cantidad_mes = $stmt->fetch()['cantidad_mes'];

    // Categoría más usada del período seleccionado
    $stmt = $pdo->prepare("
        SELECT c.nombre, COUNT(*) as total
        FROM ingresos i
        JOIN categorias c ON i.categoria_id = c.id
        WHERE i.user_id = ? AND YEAR(i.fecha) = ? AND MONTH(i.fecha) = ?
        GROUP BY c.id
        ORDER BY total DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $categoria_top = $stmt->fetch();
    $categoria_favorita = $categoria_top ? $categoria_top['nombre'] : 'N/A';

    // Obtener categorías de ingreso activas
    $stmt = $pdo->prepare("SELECT id, nombre FROM categorias WHERE tipo = 'ingreso' AND activo = 1 ORDER BY nombre");
    $stmt->execute();
    $categorias = $stmt->fetchAll();

    // Obtener métodos de pago activos
    $stmt = $pdo->prepare("SELECT id, nombre, color FROM metodos_pago WHERE activo = 1 ORDER BY nombre");
    $stmt->execute();
    $metodos_pago = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error obteniendo datos de ingresos: " . $e->getMessage());
    $total_mes = $cantidad_mes = 0;
    $categoria_favorita = 'N/A';
    $categorias = $metodos_pago = [];
}

// Variables para el header dinámico
$current_page = 'ingresos';
$nombre_mes = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];
$periodo_actual = $nombre_mes[$mes_seleccionado] . ' ' . $ano_seleccionado;

$header_buttons = '
<div class="btn-group me-2">
    <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        <i class="fas fa-calendar me-1"></i>
        ' . $periodo_actual . '
    </button>
    <ul class="dropdown-menu">
        <li><h6 class="dropdown-header">Seleccionar Período</h6></li>
        <li><hr class="dropdown-divider"></li>';

// Generar opciones de los últimos 12 meses únicos
$meses_generados = [];
$fecha_actual = new DateTime();
for ($i = 0; $i < 12; $i++) {
    $fecha_mes = clone $fecha_actual;
    $fecha_mes->sub(new DateInterval("P{$i}M"));

    $mes_opcion = (int)$fecha_mes->format('n');
    $ano_opcion = (int)$fecha_mes->format('Y');
    $clave_mes = $ano_opcion . '-' . str_pad($mes_opcion, 2, '0', STR_PAD_LEFT);

    // Evitar duplicados
    if (!in_array($clave_mes, $meses_generados)) {
        $meses_generados[] = $clave_mes;
        $nombre_opcion = $nombre_mes[$mes_opcion] . ' ' . $ano_opcion;
        $es_actual = ($mes_opcion == $mes_seleccionado && $ano_opcion == $ano_seleccionado);

        $header_buttons .= '<li><a class="dropdown-item' . ($es_actual ? ' active' : '') . '" href="?mes=' . $mes_opcion . '&ano=' . $ano_opcion . '">' . $nombre_opcion . '</a></li>';
    }
}

$header_buttons .= '
    </ul>
</div>
<button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
    <i class="fas fa-plus me-1"></i>
    Nuevo Ingreso
</button>
<a href="../index.php" class="btn btn-sm btn-outline-secondary">
    <i class="fas fa-arrow-left me-1"></i>
    Volver al Dashboard
</a>';

// Incluir header
include '../includes/header.php';
?>

<style>
    .stat-card {
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .income-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .dropdown-menu {
        max-height: 300px;
        overflow-y: auto;
    }

    .dropdown-item.active {
        background-color: #198754 !important;
        color: white !important;
    }

    /* Estilos para DataTables responsive sin scroll horizontal */
    .dataTables_wrapper {
        overflow-x: hidden !important;
        width: 100% !important;
    }

    .dataTables_scrollHead,
    .dataTables_scrollBody {
        overflow-x: hidden !important;
    }

    table.dataTable {
        width: 100% !important;
        table-layout: fixed;
        margin: 0 !important;
    }

    table.dataTable th,
    table.dataTable td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 8px 6px !important;
        border: none !important;
    }

    /* Estilos específicos para cada columna en desktop */
    @media (min-width: 768px) {
        table.dataTable th:nth-child(1),
        table.dataTable td:nth-child(1) { width: 12% !important; } /* Fecha */
        
        table.dataTable th:nth-child(2),
        table.dataTable td:nth-child(2) { 
            width: 25% !important; 
            white-space: normal !important;
            word-wrap: break-word !important;
        } /* Descripción */
        
        table.dataTable th:nth-child(3),
        table.dataTable td:nth-child(3) { width: 18% !important; } /* Categoría */
        
        table.dataTable th:nth-child(4),
        table.dataTable td:nth-child(4) { width: 18% !important; } /* Método de Pago */
        
        table.dataTable th:nth-child(5),
        table.dataTable td:nth-child(5) { 
            width: 15% !important; 
            text-align: right !important;
        } /* Monto */
        
        table.dataTable th:nth-child(6),
        table.dataTable td:nth-child(6) { 
            width: 12% !important; 
            text-align: center !important;
        } /* Acciones */
    }

    /* Estilos responsive para móviles */
    @media (max-width: 767px) {
        /* Ocultar tabla en móviles y usar cards */
        .table-responsive {
            display: none !important;
        }
        
        /* Mostrar vista de cards en móviles */
        #mobile-income-cards {
            display: block !important;
        }
        
        #mobile-search-container,
        #mobile-controls {
            display: flex !important;
        }
        
        #mobile-search-input {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        
        .mobile-income-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            padding: 15px;
            border-left: 4px solid #198754;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .mobile-income-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .mobile-income-card.filtered-out {
            display: none !important;
        }
        
        .mobile-income-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .mobile-income-amount {
            font-size: 1.25rem;
            font-weight: bold;
            color: #198754;
        }
        
        .mobile-income-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .mobile-income-description {
            font-weight: 600;
            margin-bottom: 8px;
            color: #212529;
        }
        
        .mobile-income-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .mobile-income-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .mobile-income-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .mobile-income-actions .btn {
            padding: 6px 12px !important;
            font-size: 0.8rem;
        }
        
        /* Header responsive */
        .d-flex.justify-content-between.flex-wrap {
            flex-direction: column !important;
            align-items: flex-start !important;
        }
        
        .btn-toolbar {
            margin-top: 15px !important;
            width: 100%;
        }
        
        .btn-toolbar .btn-group,
        .btn-toolbar .btn {
            margin-bottom: 8px;
            width: 100%;
        }
        
        .dropdown-menu {
            width: 100% !important;
        }
        
        /* Stats cards responsive */
        .stat-card .card-body {
            padding: 15px !important;
        }
        
        .income-icon {
            width: 40px !important;
            height: 40px !important;
        }
        
        .income-icon i {
            font-size: 1rem !important;
        }
        
        /* Action buttons responsive */
        .btn-lg {
            padding: 12px 15px !important;
            font-size: 0.9rem !important;
        }
        
        .btn-lg i {
            font-size: 1.5rem !important;
        }
        
        /* Modal responsive */
        .modal-dialog {
            margin: 10px !important;
            max-width: calc(100% - 20px) !important;
        }
        
        .modal-body {
            padding: 15px !important;
        }
        
        .modal-header {
            padding: 15px !important;
        }
        
        .modal-footer {
            padding: 15px !important;
        }
        
        .modal-title {
            font-size: 1.1rem !important;
        }
        
        /* Form responsive */
        .form-label {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .form-control,
        .form-select {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        
        /* DataTables responsive elements */
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            font-size: 0.85rem !important;
        }
        
        .dataTables_filter input {
            width: 100% !important;
            margin-left: 0 !important;
            margin-top: 5px !important;
        }
        
        .pagination .page-link {
            padding: 6px 10px !important;
            font-size: 0.8rem !important;
        }
    }

    /* Vista de cards móvil (oculta por defecto) */
    #mobile-income-cards {
        display: none;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar dinámico -->
        <?php include '../includes/sidebar.php'; ?>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
            <!-- Header del módulo -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 mb-1 text-success">
                        <i class="fas fa-plus-circle text-success me-2"></i>
                        Módulo de Ingresos
                    </h1>
                    <p class="text-muted mb-0">Gestiona y controla todos tus ingresos</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php echo $header_buttons; ?>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="income-icon bg-success bg-gradient text-white me-3">
                                    <i class="fas fa-dollar-sign fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Total del Período</h6>
                                    <h4 class="mb-0 text-success">$<?php echo number_format($total_mes, 2, ',', '.'); ?></h4>
                                    <small class="text-muted"><?php echo $periodo_actual; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="income-icon bg-primary bg-gradient text-white me-3">
                                    <i class="fas fa-list-ol fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Ingresos Registrados</h6>
                                    <h4 class="mb-0 text-primary"><?php echo $cantidad_mes; ?></h4>
                                    <small class="text-muted"><?php echo $periodo_actual; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="income-icon bg-info bg-gradient text-white me-3">
                                    <i class="fas fa-star fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Categoría Favorita</h6>
                                    <h6 class="mb-0 text-info"><?php echo htmlspecialchars($categoria_favorita); ?></h6>
                                    <small class="text-muted">Más utilizada</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Acciones Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#addIncomeModal">
                                        <i class="fas fa-plus-circle d-block mb-2 fa-2x"></i>
                                        <span>Agregar Ingreso</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="loadIncomeTable()">
                                        <i class="fas fa-table d-block mb-2 fa-2x"></i>
                                        <span>Ver Tabla</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-outline-info btn-lg w-100" onclick="showIncomeCharts()">
                                        <i class="fas fa-chart-bar d-block mb-2 fa-2x"></i>
                                        <span>Ver Gráficos</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-outline-secondary btn-lg w-100" onclick="exportIncomes()">
                                        <i class="fas fa-download d-block mb-2 fa-2x"></i>
                                        <span>Exportar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de ingresos -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historial de Ingresos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="incomesTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Categoría</th>
                                    <th>Método de Pago</th>
                                    <th>Monto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Vista de cards para móviles -->
                    <div id="mobile-income-cards">
                        <!-- Buscador móvil -->
                        <div id="mobile-search-container" class="mb-3" style="display: none;">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="mobile-search-input" placeholder="Buscar en ingresos...">
                                <button class="btn btn-outline-secondary" type="button" id="mobile-search-clear">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Controles móviles -->
                        <div id="mobile-controls" class="d-flex justify-content-between align-items-center mb-3" style="display: none;">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-sort me-1"></i>Ordenar
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="sortMobileCards('fecha-desc')">
                                        <i class="fas fa-calendar me-1"></i>Fecha (Reciente)
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortMobileCards('fecha-asc')">
                                        <i class="fas fa-calendar me-1"></i>Fecha (Antigua)
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortMobileCards('monto-desc')">
                                        <i class="fas fa-dollar-sign me-1"></i>Monto (Mayor)
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortMobileCards('monto-asc')">
                                        <i class="fas fa-dollar-sign me-1"></i>Monto (Menor)
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortMobileCards('descripcion')">
                                        <i class="fas fa-sort-alpha-down me-1"></i>Descripción
                                    </a></li>
                                </ul>
                            </div>
                            <small class="text-muted" id="mobile-results-count">0 registros</small>
                        </div>
                        
                        <!-- Container de cards -->
                        <div id="mobile-cards-container">
                            <!-- Los cards se cargarán vía JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para agregar ingreso -->
<div class="modal fade" id="addIncomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    Agregar Nuevo Ingreso
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addIncomeForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="monto" class="form-label">Monto *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="monto" name="monto" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required placeholder="Describe tu ingreso..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoria_id" class="form-label">Categoría *</label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="metodo_pago_id" class="form-label">Método de Pago *</label>
                            <select class="form-select" id="metodo_pago_id" name="metodo_pago_id" required>
                                <option value="">Seleccionar método</option>
                                <?php foreach ($metodos_pago as $metodo): ?>
                                    <option value="<?php echo $metodo['id']; ?>" data-color="<?php echo $metodo['color']; ?>">
                                        <?php echo htmlspecialchars($metodo['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        <span id="submitText">Guardar Ingreso</span>
                        <div id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar ingreso -->
<div class="modal fade" id="editIncomeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Editar Ingreso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editIncomeForm">
                <input type="hidden" id="editIncomeId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editFecha" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Fecha
                                </label>
                                <input type="date" class="form-control" id="editFecha" name="fecha" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategoria" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Categoría
                                </label>
                                <select class="form-select" id="editCategoria" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDescripcion" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Descripción
                        </label>
                        <input type="text" class="form-control" id="editDescripcion" name="descripcion" placeholder="Ej: Sueldo del mes..." required maxlength="255">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editMonto" class="form-label">
                                    <i class="fas fa-dollar-sign me-1"></i>Monto
                                </label>
                                <input type="number" class="form-control" id="editMonto" name="monto" placeholder="0.00" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editMetodoPago" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>Método de Pago
                                </label>
                                <select class="form-select" id="editMetodoPago" name="metodo_pago_id" required>
                                    <option value="">Seleccionar método...</option>
                                    <?php foreach ($metodos_pago as $metodo): ?>
                                        <option value="<?= $metodo['id'] ?>"><?= htmlspecialchars($metodo['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>
                        <span id="editSubmitText">Actualizar Ingreso</span>
                        <div id="editSubmitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Footer con scripts personalizados
$custom_scripts = <<<'EOD'
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    loadIncomeTable();
    
    // Manejar formulario de agregar ingreso
    $("#addIncomeForm").on("submit", function(e) {
        e.preventDefault();
        
        const submitBtn = $("#addIncomeForm button[type=submit]");
        const submitText = $("#submitText");
        const submitSpinner = $("#submitSpinner");
        
        // Mostrar loading
        submitBtn.prop("disabled", true);
        submitText.text("Guardando...");
        submitSpinner.show();
        
        // Obtener datos del formulario
        const formData = {
            fecha: $("#fecha").val(),
            monto: $("#monto").val(),
            descripcion: $("#descripcion").val(),
            categoria_id: $("#categoria_id").val(),
            metodo_pago_id: $("#metodo_pago_id").val()
        };
        
        // Enviar petición AJAX
        $.ajax({
            url: "controllers/controller.php?action=create",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    // Éxito
                    $("#addIncomeModal").modal("hide");
                    $("#addIncomeForm")[0].reset();
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        title: "¡Ingreso Agregado!",
                        text: response.message,
                        icon: "success",
                        confirmButtonColor: "#198754"
                    }).then(() => {
                        // Recargar la página para actualizar todo
                        location.reload();
                    });
                } else {
                    // Error
                    Swal.fire({
                        title: "Error",
                        text: response.error,
                        icon: "error",
                        confirmButtonColor: "#dc3545"
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al guardar el ingreso";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                Swal.fire({
                    title: "Error",
                    text: errorMessage,
                    icon: "error",
                    confirmButtonColor: "#dc3545"
                });
            },
            complete: function() {
                // Ocultar loading
                submitBtn.prop("disabled", false);
                submitText.text("Guardar Ingreso");
                submitSpinner.hide();
            }
        });
    });

    // Manejador del formulario de edición de ingreso
    $("#editIncomeForm").on("submit", function(e) {
        e.preventDefault();
        
        const submitBtn = $("#editIncomeForm button[type=submit]");
        const submitText = $("#editSubmitText");
        const submitSpinner = $("#editSubmitSpinner");
        
        // Mostrar loading
        submitBtn.prop("disabled", true);
        submitText.text("Actualizando...");
        submitSpinner.show();
        
        const formData = new FormData(this);
        const id = formData.get('id');
        
        // Convertir FormData a objeto
        const data = {};
        for (let [key, value] of formData.entries()) {
            if (key !== 'id') {
                data[key] = value;
            }
        }
        
        $.ajax({
            url: `controllers/controller.php?action=update&id=${id}`,
            type: "PUT",
            contentType: "application/json",
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    // Éxito
                    Swal.fire({
                        title: "¡Actualizado!",
                        text: response.message,
                        icon: "success",
                        confirmButtonColor: "#198754"
                    }).then(() => {
                        $("#editIncomeModal").modal("hide");
                        // Recargar la página para actualizar todo
                        location.reload();
                    });
                    
                    // Limpiar formulario
                    $("#editIncomeForm")[0].reset();
                } else {
                    // Error
                    Swal.fire({
                        title: "Error",
                        text: response.error,
                        icon: "error",
                        confirmButtonColor: "#dc3545"
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al actualizar el ingreso";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                Swal.fire({
                    title: "Error",
                    text: errorMessage,
                    icon: "error",
                    confirmButtonColor: "#dc3545"
                });
            },
            complete: function() {
                // Ocultar loading
                submitBtn.prop("disabled", false);
                submitText.text("Actualizar Ingreso");
                submitSpinner.hide();
            }
        });
    });
    
    // Event listeners para búsqueda móvil
    $(document).on('input', '#mobile-search-input', function() {
        const searchTerm = $(this).val();
        filterMobileCards(searchTerm);
    });
    
    $(document).on('click', '#mobile-search-clear', function() {
        $('#mobile-search-input').val('');
        filterMobileCards('');
    });
});

// Función para cargar tabla de ingresos
function loadIncomeTable() {
    if ($.fn.DataTable.isDataTable("#incomesTable")) {
        $("#incomesTable").DataTable().destroy();
    }
    
    // Obtener parámetros de URL para el filtro
    const urlParams = new URLSearchParams(window.location.search);
    const mes = urlParams.get("mes") || "<?php echo $mes_seleccionado; ?>";
    const ano = urlParams.get("ano") || "<?php echo $ano_seleccionado; ?>";
    
    $("#incomesTable").DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        scrollX: false,
        autoWidth: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        columnDefs: [
            { width: "12%", targets: 0 },  // Fecha
            { width: "25%", targets: 1 },  // Descripción
            { width: "18%", targets: 2 },  // Categoría
            { width: "18%", targets: 3 },  // Método de Pago
            { width: "15%", targets: 4 },  // Monto
            { width: "12%", targets: 5, orderable: false, searchable: false }   // Acciones
        ],
        ajax: {
            url: `controllers/controller.php?action=list&mes=${mes}&ano=${ano}`,
            type: "GET",
            dataSrc: function(json) {
                if (json.success) {
                    // Cargar vista móvil también
                    loadMobileIncomeCards(json.data);
                    return json.data;
                } else {
                    console.error("Error cargando ingresos:", json.error);
                    return [];
                }
            }
        },
        columns: [
            {
                data: "fecha",
                render: function(data, type, row) {
                    if (type === 'display') {
                        // Crear fecha sin conversión de zona horaria para evitar el problema de +1 día
                        const fechaParts = data.split('-');
                        const fecha = new Date(fechaParts[0], fechaParts[1] - 1, fechaParts[2]);
                        return fecha.toLocaleDateString("es-AR");
                    }
                    // Para ordenamiento, usar fecha + ID como criterio
                    return data + '_' + String(row.id).padStart(10, '0');
                }
            },
            {
                data: "descripcion",
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<strong>${data}</strong>`;
                    }
                    // Para ordenamiento, usar descripción + ID
                    return data + '_' + String(row.id).padStart(10, '0');
                }
            },
            {
                data: "categoria",
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<span class="badge bg-primary">${data}</span>`;
                    }
                    // Para ordenamiento, usar categoría + ID
                    return data + '_' + String(row.id).padStart(10, '0');
                }
            },
            {
                data: "metodo_pago",
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<span class="badge" style="background-color: ${row.color_metodo};">${data}</span>`;
                    }
                    // Para ordenamiento, usar método de pago + ID
                    return data + '_' + String(row.id).padStart(10, '0');
                }
            },
            {
                data: "monto",
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `<span class="fw-bold text-success">$${parseFloat(data).toLocaleString("es-AR", {minimumFractionDigits: 2})}</span>`;
                    }
                    // Para ordenamiento, usar monto como número + ID
                    return parseFloat(data) + parseFloat('0.' + String(row.id).padStart(10, '0'));
                }
            },
            {
                data: "id",
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="editIncome(${data})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteIncome(${data})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, "desc"]],  // Ordenar por fecha descendente
        orderFixed: [[0, "desc"]],  // Fijar ordenamiento por fecha
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]]
    });
}

// Función para cargar vista móvil de cards
let mobileIncomeData = []; // Variable global para almacenar datos

function loadMobileIncomeCards(data) {
    // Almacenar datos globalmente para búsqueda y ordenamiento
    mobileIncomeData = data || [];
    
    // Mostrar controles móviles si hay datos
    const searchContainer = document.getElementById('mobile-search-container');
    const controlsContainer = document.getElementById('mobile-controls');
    
    if (mobileIncomeData.length > 0) {
        searchContainer.style.display = 'block';
        controlsContainer.style.display = 'flex';
    } else {
        searchContainer.style.display = 'none';
        controlsContainer.style.display = 'none';
    }
    
    // Ordenar por fecha descendente por defecto, luego por ID ascendente
    mobileIncomeData.sort((a, b) => {
        const fechaComparison = new Date(b.fecha) - new Date(a.fecha);
        // Si las fechas son iguales, ordenar por ID ascendente (más viejo primero)
        return fechaComparison !== 0 ? fechaComparison : parseInt(a.id) - parseInt(b.id);
    });
    
    renderMobileCards(mobileIncomeData);
    updateMobileResultsCount(mobileIncomeData.length);
}

function renderMobileCards(data) {
    const container = document.getElementById('mobile-cards-container');
    
    if (!data || data.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay ingresos registrados</h5>
                <p class="text-muted">Comienza agregando tu primer ingreso</p>
            </div>
        `;
        return;
    }
    
    let cardsHTML = '';
    
    data.forEach(function(income) {
        // Formatear fecha
        const fechaParts = income.fecha.split('-');
        const fecha = new Date(fechaParts[0], fechaParts[1] - 1, fechaParts[2]);
        const fechaFormateada = fecha.toLocaleDateString("es-AR");
        
        // Formatear monto
        const montoFormateado = parseFloat(income.monto).toLocaleString("es-AR", {minimumFractionDigits: 2});
        
        cardsHTML += `
            <div class="mobile-income-card" data-search="${income.descripcion.toLowerCase()} ${income.categoria.toLowerCase()} ${income.metodo_pago.toLowerCase()}" data-fecha="${income.fecha}" data-monto="${income.monto}">
                <div class="mobile-income-header">
                    <div>
                        <div class="mobile-income-amount">$${montoFormateado}</div>
                        <div class="mobile-income-date">${fechaFormateada}</div>
                    </div>
                    <div class="mobile-income-actions">
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="editIncome(${income.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteIncome(${income.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="mobile-income-description">${income.descripcion}</div>
                <div class="mobile-income-details">
                    <span class="badge bg-primary mobile-income-badge">${income.categoria}</span>
                    <span class="badge mobile-income-badge" style="background-color: ${income.color_metodo};">${income.metodo_pago}</span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = cardsHTML;
}

// Función para ordenar cards móviles
function sortMobileCards(sortType) {
    let sortedData = [...mobileIncomeData];
    
    switch(sortType) {
        case 'fecha-desc':
            sortedData.sort((a, b) => {
                const fechaComparison = new Date(b.fecha) - new Date(a.fecha);
                // Si las fechas son iguales, ordenar por ID ascendente (más viejo primero)
                return fechaComparison !== 0 ? fechaComparison : parseInt(a.id) - parseInt(b.id);
            });
            break;
        case 'fecha-asc':
            sortedData.sort((a, b) => {
                const fechaComparison = new Date(a.fecha) - new Date(b.fecha);
                // Si las fechas son iguales, ordenar por ID ascendente (más viejo primero)
                return fechaComparison !== 0 ? fechaComparison : parseInt(a.id) - parseInt(b.id);
            });
            break;
        case 'monto-desc':
            sortedData.sort((a, b) => {
                const montoComparison = parseFloat(b.monto) - parseFloat(a.monto);
                // Si los montos son iguales, ordenar por ID ascendente
                return montoComparison !== 0 ? montoComparison : parseInt(a.id) - parseInt(b.id);
            });
            break;
        case 'monto-asc':
            sortedData.sort((a, b) => {
                const montoComparison = parseFloat(a.monto) - parseFloat(b.monto);
                // Si los montos son iguales, ordenar por ID ascendente
                return montoComparison !== 0 ? montoComparison : parseInt(a.id) - parseInt(b.id);
            });
            break;
        case 'descripcion':
            sortedData.sort((a, b) => {
                const descripcionComparison = a.descripcion.localeCompare(b.descripcion);
                // Si las descripciones son iguales, ordenar por ID ascendente
                return descripcionComparison !== 0 ? descripcionComparison : parseInt(a.id) - parseInt(b.id);
            });
            break;
    }
    
    renderMobileCards(sortedData);
    
    // Aplicar filtro de búsqueda si existe
    const searchTerm = document.getElementById('mobile-search-input').value;
    if (searchTerm) {
        filterMobileCards(searchTerm);
    }
}

// Función para filtrar cards móviles
function filterMobileCards(searchTerm) {
    const cards = document.querySelectorAll('.mobile-income-card');
    const searchLower = searchTerm.toLowerCase();
    let visibleCount = 0;
    
    cards.forEach(card => {
        const searchData = card.getAttribute('data-search');
        const isVisible = searchData.includes(searchLower);
        
        if (isVisible) {
            card.classList.remove('filtered-out');
            visibleCount++;
        } else {
            card.classList.add('filtered-out');
        }
    });
    
    updateMobileResultsCount(visibleCount);
}

// Función para actualizar contador de resultados
function updateMobileResultsCount(count) {
    const counter = document.getElementById('mobile-results-count');
    if (counter) {
        counter.textContent = `${count} registro${count !== 1 ? 's' : ''}`;
    }
}

// Funciones placeholder para futuras implementaciones
function showIncomeCharts() {
    Swal.fire("Próximamente", "La funcionalidad de gráficos estará disponible pronto", "info");
}

function exportIncomes() {
    Swal.fire("Próximamente", "La funcionalidad de exportación estará disponible pronto", "info");
}

function editIncome(id) {
    // Obtener datos del ingreso
    fetch(`controllers/controller.php?action=details&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const income = data.data;
                
                // Llenar el formulario de edición
                document.getElementById('editIncomeId').value = income.id;
                document.getElementById('editFecha').value = income.fecha;
                document.getElementById('editCategoria').value = income.categoria_id;
                document.getElementById('editDescripcion').value = income.descripcion;
                document.getElementById('editMonto').value = income.monto;
                document.getElementById('editMetodoPago').value = income.metodo_pago_id;
                
                // Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('editIncomeModal'));
                modal.show();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.error || 'No se pudieron cargar los datos del ingreso',
                    icon: 'error',
                    confirmButtonColor: '#198754'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error de conexión al cargar los datos',
                icon: 'error',
                confirmButtonColor: '#198754'
            });
        });
}

function deleteIncome(id) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción eliminará el ingreso permanentemente",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            // Segunda confirmación
            Swal.fire({
                title: "¡Última confirmación!",
                text: "¿Realmente quieres eliminar este ingreso? No se puede deshacer",
                icon: "error",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "SÍ, ELIMINAR DEFINITIVAMENTE",
                cancelButtonText: "No, cancelar"
            }).then((finalResult) => {
                if (finalResult.isConfirmed) {
                    // Realizar la eliminación
                    fetch(`controllers/controller.php?action=delete&id=${id}`, {
                        method: 'DELETE'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "¡Eliminado!",
                                text: data.message || "El ingreso ha sido eliminado exitosamente",
                                icon: "success",
                                confirmButtonColor: "#198754"
                            }).then(() => {
                                // Recargar la tabla
                                $("#incomesTable").DataTable().ajax.reload();
                                // Recargar estadísticas si es necesario
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: data.error || "No se pudo eliminar el ingreso",
                                icon: "error",
                                confirmButtonColor: "#198754"
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: "Error",
                            text: "Error de conexión al eliminar el ingreso",
                            icon: "error",
                            confirmButtonColor: "#198754"
                        });
                    });
                }
            });
        }
    });
}
</script>
EOD;

// Incluir el footer
include '../includes/footer.php';
?>