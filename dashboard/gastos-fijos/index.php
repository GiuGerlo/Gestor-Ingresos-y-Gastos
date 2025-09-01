<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Incluir conexión a la base de datos
require_once '../../config/connect.php';

$user_name = $_SESSION['user_name'] ?? 'Usuario';
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos Fijos - Gestor de Finanzas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #292929;
            --secondary-color: #6548D5;
            --fixed-expense-color: #E91E63; /* Rosa para gastos fijos */
            --fixed-expense-light: #FCE4EC;
        }

        .fixed-expense-theme {
            color: var(--fixed-expense-color) !important;
        }

        .btn-fixed-expense {
            background-color: var(--fixed-expense-color);
            border-color: var(--fixed-expense-color);
            color: white;
        }

        .btn-fixed-expense:hover {
            background-color: #C2185B;
            border-color: #C2185B;
            color: white;
        }

        .text-fixed-expense {
            color: var(--fixed-expense-color) !important;
        }

        .bg-fixed-expense-light {
            background-color: var(--fixed-expense-light) !important;
        }

        /* Cards para móvil */
        .mobile-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .mobile-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .mobile-card-header {
            background: linear-gradient(135deg, var(--fixed-expense-color), #C2185B);
            color: white;
            padding: 12px 15px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-card-body {
            padding: 15px;
        }

        .mobile-card-title {
            font-weight: 600;
            margin: 0;
            font-size: 1.1em;
        }

        .mobile-card-amount {
            font-weight: 700;
            font-size: 1.2em;
        }

        .mobile-card-info {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .mobile-card-info i {
            width: 20px;
            margin-right: 8px;
            color: var(--fixed-expense-color);
        }

        .mobile-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-mobile {
            flex: 1;
            padding: 8px 12px;
            font-size: 0.85em;
            border-radius: 6px;
        }

        /* Estado de gastos fijos */
        .status-badge {
            font-size: 0.75em;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-active {
            background-color: #E8F5E8;
            color: #2E7D32;
        }

        .status-inactive {
            background-color: #FFEBEE;
            color: #C62828;
        }

        .status-ending {
            background-color: #FFF3E0;
            color: #EF6C00;
        }

        /* Cuotas restantes */
        .quota-info {
            background-color: #F3E5F5;
            color: #7B1FA2;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.8em;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 767px) {
            .desktop-view {
                display: none !important;
            }
            .mobile-view {
                display: block !important;
            }
            
            .mobile-search-container {
                margin-bottom: 20px;
            }
            
            .mobile-search-input {
                border-radius: 25px;
                border: 2px solid var(--fixed-expense-color);
                padding: 12px 20px;
                font-size: 16px;
            }
            
            .mobile-search-input:focus {
                outline: none;
                box-shadow: 0 0 0 0.2rem rgba(233, 30, 99, 0.25);
            }
        }

        @media (min-width: 768px) {
            .desktop-view {
                display: block !important;
            }
            .mobile-view {
                display: none !important;
            }
        }

        /* DataTables personalización */
        .table-fixed-expenses th {
            background-color: var(--fixed-expense-color);
            color: white;
            border: none;
        }

        .table-fixed-expenses td {
            vertical-align: middle;
        }

        /* Eliminar scroll horizontal */
        .dataTables_wrapper .dataTables_scrollBody {
            overflow-x: hidden !important;
        }
        
        .dataTables_wrapper {
            overflow-x: hidden !important;
        }
        
        /* Contenedor de tabla sin scroll */
        .table-container {
            overflow: hidden !important;
            width: 100%;
        }
        
        /* Asegurar que la tabla se ajuste */
        .table-fixed-expenses {
            width: 100% !important;
            table-layout: auto;
        }
        
        /* Optimizar ancho de columnas */
        .table-fixed-expenses th:nth-child(1) { width: 12%; } /* Fecha Inicio */
        .table-fixed-expenses th:nth-child(2) { width: 8%; }  /* Día */
        .table-fixed-expenses th:nth-child(3) { width: 20%; } /* Nombre */
        .table-fixed-expenses th:nth-child(4) { width: 12%; } /* Monto */
        .table-fixed-expenses th:nth-child(5) { width: 12%; } /* Cuotas */
        .table-fixed-expenses th:nth-child(6) { width: 10%; } /* Estado */
        .table-fixed-expenses th:nth-child(7) { width: 14%; } /* Próximo Pago */
        .table-fixed-expenses th:nth-child(8) { width: 12%; } /* Acciones */

        /* Alertas de próximos vencimientos */
        .alert-next-payment {
            background-color: #FFF8E1;
            color: #F57F17;
            border: 1px solid #FFE082;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .day-highlight {
            background-color: var(--fixed-expense-color);
            color: white;
            padding: 4px 8px;
            border-radius: 50%;
            font-weight: bold;
            display: inline-block;
            min-width: 30px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/sidebar.php'; ?>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fixed-expense-theme">
                        <i class="fas fa-calendar-check me-2"></i>
                        Gastos Fijos
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-fixed-expense" data-bs-toggle="modal" data-bs-target="#addFixedExpenseModal">
                            <i class="fas fa-plus me-1"></i>
                            Nuevo Gasto Fijo
                        </button>
                    </div>
                </div>

                <!-- Alertas de próximos pagos -->
                <div id="nextPaymentsAlert" class="alert-next-payment d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Próximos pagos:</strong>
                    <div id="nextPaymentsList"></div>
                </div>

                <!-- Vista móvil -->
                <div class="mobile-view">
                    <!-- Buscador móvil -->
                    <div class="mobile-search-container">
                        <input type="text" 
                               id="mobileSearch" 
                               class="form-control mobile-search-input" 
                               placeholder="Buscar por nombre o día del mes...">
                    </div>

                    <!-- Contenedor de cards -->
                    <div id="mobileCardsContainer">
                        <!-- Las cards se cargarán aquí dinámicamente -->
                    </div>
                </div>

                <!-- Vista escritorio -->
                <div class="desktop-view">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-container">
                                <table id="fixedExpensesTable" class="table table-striped table-hover table-fixed-expenses">
                                    <thead>
                                        <tr>
                                            <th>Fecha Inicio</th>
                                            <th>Día</th>
                                            <th>Nombre</th>
                                            <th>Monto</th>
                                            <th>Cuotas</th>
                                            <th>Próximo Pago</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Los datos se cargarán vía AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-center bg-fixed-expense-light">
                            <div class="card-body">
                                <h5 class="card-title text-fixed-expense">Total Mensual</h5>
                                <h3 class="card-text text-fixed-expense" id="totalMonthly">$0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-success">Gastos Activos</h5>
                                <h3 class="card-text text-success" id="activeCount">0</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4" style="margin-bottom: 5rem;">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5 class="card-title text-warning">Con Cuotas</h5>
                                <h3 class="card-text text-warning" id="withQuotasCount">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Agregar Gasto Fijo -->
    <div class="modal fade" id="addFixedExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-fixed-expense-light">
                    <h5 class="modal-title text-fixed-expense">
                        <i class="fas fa-plus me-2"></i>
                        Nuevo Gasto Fijo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addFixedExpenseForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="add_fecha_inicio" 
                                       name="fecha_inicio" 
                                       value="<?php echo date('Y-m-d'); ?>"
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="add_dia_mes" class="form-label">Día del Mes *</label>
                                <select class="form-select" id="add_dia_mes" name="dia_mes" required>
                                    <option value="">Seleccionar día...</option>
                                    <?php for($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_monto" class="form-label">Monto *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="add_monto" 
                                           name="monto" 
                                           step="0.01" 
                                           min="0.01" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="add_nombre" class="form-label">Nombre del Gasto *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="add_nombre" 
                                       name="nombre" 
                                       placeholder="Ej: Alquiler, Netflix, Gimnasio..." 
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="add_cuotas_restantes" class="form-label">Cuotas Restantes</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="add_cuotas_restantes" 
                                       name="cuotas_restantes" 
                                       min="1" 
                                       placeholder="Dejar vacío para sin límite">
                                <small class="text-muted">Si no especificas, será un gasto fijo indefinido</small>
                            </div>
                            
                            <div class="col-md-6 mb-3" id="fechaFinContainer" style="display: none;">
                                <label for="add_fecha_fin" class="form-label">Fecha de Fin (Opcional)</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="add_fecha_fin" 
                                       name="fecha_fin">
                                <small class="text-muted">Solo para gastos con cuotas limitadas</small>
                            </div>
                        </div>

                        <div class="row" id="lastQuotaContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="add_mes_ultima_cuota" class="form-label">Mes de Última Cuota</label>
                                <input type="month" 
                                       class="form-control" 
                                       id="add_mes_ultima_cuota" 
                                       name="mes_ultima_cuota">
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="add_activo" 
                                   name="activo" 
                                   checked>
                            <label class="form-check-label" for="add_activo">
                                Activar inmediatamente
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-fixed-expense">
                            <i class="fas fa-save me-1"></i>
                            Guardar Gasto Fijo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Gasto Fijo -->
    <div class="modal fade" id="editFixedExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-fixed-expense-light">
                    <h5 class="modal-title text-fixed-expense">
                        <i class="fas fa-edit me-2"></i>
                        Editar Gasto Fijo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editFixedExpenseForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="edit_fecha_inicio" 
                                       name="fecha_inicio" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_dia_mes" class="form-label">Día del Mes *</label>
                                <select class="form-select" id="edit_dia_mes" name="dia_mes" required>
                                    <option value="">Seleccionar día...</option>
                                    <?php for($i = 1; $i <= 31; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_monto" class="form-label">Monto *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="edit_monto" 
                                           name="monto" 
                                           step="0.01" 
                                           min="0.01" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="edit_nombre" class="form-label">Nombre del Gasto *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="edit_nombre" 
                                       name="nombre" 
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_cuotas_restantes" class="form-label">Cuotas Restantes</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="edit_cuotas_restantes" 
                                       name="cuotas_restantes" 
                                       min="1" 
                                       placeholder="Dejar vacío para sin límite">
                            </div>
                            
                            <div class="col-md-6 mb-3" id="editFechaFinContainer" style="display: none;">
                                <label for="edit_fecha_fin" class="form-label">Fecha de Fin (Opcional)</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="edit_fecha_fin" 
                                       name="fecha_fin">
                            </div>
                        </div>

                        <div class="row" id="editLastQuotaContainer" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="edit_mes_ultima_cuota" class="form-label">Mes de Última Cuota</label>
                                <input type="month" 
                                       class="form-control" 
                                       id="edit_mes_ultima_cuota" 
                                       name="mes_ultima_cuota">
                            </div>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="edit_activo" 
                                   name="activo">
                            <label class="form-check-label" for="edit_activo">
                                Gasto activo
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-fixed-expense">
                            <i class="fas fa-save me-1"></i>
                            Actualizar Gasto Fijo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div class="modal fade" id="viewFixedExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-fixed-expense-light">
                    <h5 class="modal-title text-fixed-expense">
                        <i class="fas fa-eye me-2"></i>
                        Detalles del Gasto Fijo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nombre:</strong>
                            <p id="view_nombre" class="mb-2"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Monto:</strong>
                            <p id="view_monto" class="mb-2 text-fixed-expense"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Fecha de Inicio:</strong>
                            <p id="view_fecha_inicio" class="mb-2"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de Fin:</strong>
                            <p id="view_fecha_fin" class="mb-2"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Día del Mes:</strong>
                            <p id="view_dia_mes" class="mb-2"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Cuotas Restantes:</strong>
                            <p id="view_cuotas_restantes" class="mb-2"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Última Cuota:</strong>
                            <p id="view_mes_ultima_cuota" class="mb-2"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <strong>Próximo Pago:</strong>
                            <p id="view_proximo_pago" class="mb-2"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Creado:</strong>
                            <p id="view_created_at" class="mb-2 text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Actualizado:</strong>
                            <p id="view_updated_at" class="mb-2 text-muted"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let fixedExpensesTable;
        let fixedExpensesData = [];

        $(document).ready(function() {
            initializeDataTable();
            loadFixedExpenses();
            setupEventListeners();
            checkNextPayments();
        });

        function initializeDataTable() {
            fixedExpensesTable = $('#fixedExpensesTable').DataTable({
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[1, 'asc']], // Ordenar por día del mes (ascendente)
                columnDefs: [
                    { orderable: false, targets: -1 }, // Última columna (acciones) no ordenable
                    { responsivePriority: 1, targets: 2 }, // Nombre siempre visible
                    { responsivePriority: 2, targets: 3 }, // Monto siempre visible
                    { responsivePriority: 3, targets: 1 }, // Día del mes siempre visible
                    { responsivePriority: 4, targets: -1 }, // Acciones siempre visibles
                    { responsivePriority: 5, targets: 0 } // Fecha inicio visible cuando sea posible
                ],
                scrollX: false, // Desactivar scroll horizontal
                autoWidth: false // Desactivar auto width
            });
        }

        function setupEventListeners() {
            // Form submit events
            $('#addFixedExpenseForm').on('submit', handleAddFixedExpense);
            $('#editFixedExpenseForm').on('submit', handleEditFixedExpense);
            
            // Mobile search
            $('#mobileSearch').on('input', handleMobileSearch);
            
            // Cuotas restantes change events
            $('#add_cuotas_restantes').on('input', toggleLastQuotaField);
            $('#edit_cuotas_restantes').on('input', toggleLastQuotaFieldEdit);
        }

        function toggleLastQuotaField() {
            const cuotas = $('#add_cuotas_restantes').val();
            if (cuotas && cuotas > 0) {
                $('#lastQuotaContainer').show();
                $('#fechaFinContainer').show();
                $('#add_mes_ultima_cuota').prop('required', true);
            } else {
                $('#lastQuotaContainer').hide();
                $('#fechaFinContainer').hide();
                $('#add_mes_ultima_cuota').prop('required', false).val('');
                $('#add_fecha_fin').val('');
            }
        }

        function toggleLastQuotaFieldEdit() {
            const cuotas = $('#edit_cuotas_restantes').val();
            if (cuotas && cuotas > 0) {
                $('#editLastQuotaContainer').show();
                $('#editFechaFinContainer').show();
                $('#edit_mes_ultima_cuota').prop('required', true);
            } else {
                $('#editLastQuotaContainer').hide();
                $('#editFechaFinContainer').hide();
                $('#edit_mes_ultima_cuota').prop('required', false).val('');
                $('#edit_fecha_fin').val('');
            }
        }

        function loadFixedExpenses() {
            $.ajax({
                url: 'controllers/controller.php',
                method: 'GET',
                data: { action: 'list' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        fixedExpensesData = response.data;
                        updateDesktopTable(response.data);
                        updateMobileCards(response.data);
                        updateStats(response.data);
                    } else {
                        showError('Error al cargar gastos fijos: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al cargar gastos fijos');
                }
            });
        }

        function updateDesktopTable(data) {
            fixedExpensesTable.clear();
            
            data.forEach(function(expense) {
                const statusBadge = getStatusBadge(expense);
                const nextPayment = calculateNextPayment(expense);
                const quotaInfo = getQuotaInfo(expense);
                
                // Formatear fecha de inicio a dd/mm/yyyy
                function formatFecha(fechaStr) {
                    if (!fechaStr) return '';
                    const parts = fechaStr.split('-');
                    if (parts.length !== 3) return fechaStr;
                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                }

                fixedExpensesTable.row.add([
                    `<small>${formatFecha(expense.fecha_inicio)}</small>`,
                    `<span class="day-highlight">${expense.dia_mes}</span>`,
                    expense.nombre,
                    `<strong>$${parseFloat(expense.monto).toLocaleString('es-AR', {minimumFractionDigits: 2})}</strong>`,
                    quotaInfo,
                    nextPayment,
                    getActionButtons(expense.id, expense.nombre)
                ]);
            });
            
            fixedExpensesTable.draw();
        }

        function updateMobileCards(data) {
            const container = $('#mobileCardsContainer');
            container.empty();
            
            if (data.length === 0) {
                container.html(`
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay gastos fijos registrados</p>
                        <button type="button" class="btn btn-fixed-expense" data-bs-toggle="modal" data-bs-target="#addFixedExpenseModal">
                            <i class="fas fa-plus me-1"></i>
                            Agregar Primer Gasto Fijo
                        </button>
                    </div>
                `);
                return;
            }
            
            data.forEach(function(expense) {
                const card = createMobileCard(expense);
                container.append(card);
            });
        }

        function createMobileCard(expense) {
            const statusBadge = getStatusBadge(expense);
            const nextPayment = calculateNextPayment(expense);
            const quotaInfo = getQuotaInfo(expense);
            
            // Formatear fecha de inicio a dd/mm/yyyy
            function formatFecha(fechaStr) {
                if (!fechaStr) return '';
                const parts = fechaStr.split('-');
                if (parts.length !== 3) return fechaStr;
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            return `
                <div class="mobile-card" data-search="${expense.nombre.toLowerCase()} ${expense.dia_mes}">
                    <div class="mobile-card-header">
                        <h5 class="mobile-card-title">${expense.nombre}</h5>
                        <span class="mobile-card-amount">$${parseFloat(expense.monto).toLocaleString('es-AR', {minimumFractionDigits: 2})}</span>
                    </div>
                    <div class="mobile-card-body">
                        <div class="mobile-card-info">
                            <i class="fas fa-play-circle"></i>
                            <span>Inicio: ${formatFecha(expense.fecha_inicio)}</span>
                        </div>
                        <div class="mobile-card-info">
                            <i class="fas fa-calendar-day"></i>
                            <span>Día ${expense.dia_mes} de cada mes</span>
                        </div>
                        <div class="mobile-card-info">
                            <i class="fas fa-clock"></i>
                            <span>${nextPayment}</span>
                        </div>
                        <div class="mobile-card-info">
                            <i class="fas fa-list-ol"></i>
                            <span>${quotaInfo}</span>
                        </div>
                        <div class="mobile-card-actions">
                            <button type="button" class="btn btn-outline-primary btn-mobile" onclick="viewFixedExpense(${expense.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-mobile" onclick="editFixedExpense(${expense.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-mobile" onclick="deleteFixedExpense(${expense.id}, '${expense.nombre}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function getStatusBadge(expense) {
            if (!expense.activo) {
                return '<span class="status-badge status-inactive">Inactivo</span>';
            }
            
            // Si no hay cuotas restantes o es 0, está finalizado
            if (!expense.cuotas_restantes || expense.cuotas_restantes == 0) {
                return '<span class="status-badge status-inactive">Finalizado</span>';
            }
            
            // Si solo queda 1 cuota
            if (expense.cuotas_restantes == 1) {
                return '<span class="status-badge status-ending">Última Cuota</span>';
            }
            
            // Si quedan 2-3 cuotas, está finalizando
            if (expense.cuotas_restantes <= 3) {
                return '<span class="status-badge status-ending">Finalizando</span>';
            }
            
            return '<span class="status-badge status-active">Activo</span>';
        }

        function getQuotaInfo(expense) {
            if (!expense.cuotas_restantes) {
                return '<span class="text-muted">Sin límite</span>';
            }
            
            return `<span class="quota-info">${expense.cuotas_restantes} cuotas restantes</span>`;
        }

        function calculateNextPayment(expense) {
            const today = new Date();
            const currentDay = today.getDate();
            const currentMonth = today.getMonth();
            const currentYear = today.getFullYear();
            
            let nextPaymentMonth = currentMonth;
            let nextPaymentYear = currentYear;
            
            if (currentDay >= expense.dia_mes) {
                nextPaymentMonth++;
                if (nextPaymentMonth > 11) {
                    nextPaymentMonth = 0;
                    nextPaymentYear++;
                }
            }
            
            const nextPayment = new Date(nextPaymentYear, nextPaymentMonth, expense.dia_mes);
            const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            return `${expense.dia_mes} de ${monthNames[nextPaymentMonth]} ${nextPaymentYear}`;
        }

        function getActionButtons(id) {
            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewFixedExpense(${id})" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="editFixedExpense(${id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFixedExpense(${id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }

        function updateStats(data) {
            const totalMonthly = data.filter(e => e.activo).reduce((sum, e) => sum + parseFloat(e.monto), 0);
            const activeCount = data.filter(e => e.activo).length;
            const withQuotasCount = data.filter(e => e.cuotas_restantes).length;
            
            $('#totalMonthly').text('$' + totalMonthly.toLocaleString('es-AR', {minimumFractionDigits: 2}));
            $('#activeCount').text(activeCount);
            $('#withQuotasCount').text(withQuotasCount);
        }

        function checkNextPayments() {
            const today = new Date();
            const currentDay = today.getDate();
            
            const upcomingPayments = fixedExpensesData.filter(expense => {
                if (!expense.activo) return false;
                
                const daysUntilPayment = expense.dia_mes - currentDay;
                return daysUntilPayment >= 0 && daysUntilPayment <= 3;
            });
            
            if (upcomingPayments.length > 0) {
                const alert = $('#nextPaymentsAlert');
                const list = $('#nextPaymentsList');
                
                let paymentsList = '<ul class="mb-0 mt-2">';
                upcomingPayments.forEach(payment => {
                    const daysLeft = payment.dia_mes - currentDay;
                    const urgency = daysLeft === 0 ? 'HOY' : `en ${daysLeft} día${daysLeft > 1 ? 's' : ''}`;
                    paymentsList += `<li><strong>${payment.nombre}</strong> - $${parseFloat(payment.monto).toLocaleString('es-AR', {minimumFractionDigits: 2})} (${urgency})</li>`;
                });
                paymentsList += '</ul>';
                
                list.html(paymentsList);
                alert.removeClass('d-none');
            }
        }

        function handleMobileSearch() {
            const searchTerm = $('#mobileSearch').val().toLowerCase();
            
            if (searchTerm === '') {
                $('.mobile-card').show();
                return;
            }
            
            $('.mobile-card').each(function() {
                const searchData = $(this).data('search');
                if (searchData.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function handleAddFixedExpense(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'create');
            
            $.ajax({
                url: 'controllers/controller.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#addFixedExpenseModal').modal('hide');
                        $('#addFixedExpenseForm')[0].reset();
                        $('#lastQuotaContainer').hide();
                        loadFixedExpenses();
                        showSuccess('Gasto fijo creado exitosamente');
                    } else {
                        showError('Error al crear gasto fijo: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al crear gasto fijo');
                }
            });
        }

        function handleEditFixedExpense(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update');
            
            $.ajax({
                url: 'controllers/controller.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editFixedExpenseModal').modal('hide');
                        loadFixedExpenses();
                        showSuccess('Gasto fijo actualizado exitosamente');
                    } else {
                        showError('Error al actualizar gasto fijo: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al actualizar gasto fijo');
                }
            });
        }

        function viewFixedExpense(id) {
            $.ajax({
                url: 'controllers/controller.php',
                method: 'GET',
                data: { action: 'details', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const expense = response.data;
                        
                        $('#view_nombre').text(expense.nombre);
                        $('#view_monto').text('$' + parseFloat(expense.monto).toLocaleString('es-AR', {minimumFractionDigits: 2}));
                        $('#view_fecha_inicio').text(expense.fecha_inicio ? new Date(expense.fecha_inicio).toLocaleDateString('es-AR') : 'No definida');
                        $('#view_fecha_fin').text(expense.fecha_fin ? new Date(expense.fecha_fin).toLocaleDateString('es-AR') : 'No definida');
                        $('#view_dia_mes').html(`<span class="day-highlight">${expense.dia_mes}</span>`);
                        $('#view_cuotas_restantes').text(expense.cuotas_restantes || 'Sin límite');
                        $('#view_mes_ultima_cuota').text(expense.mes_ultima_cuota || 'N/A');
                        $('#view_proximo_pago').text(calculateNextPayment(expense));
                        $('#view_created_at').text(new Date(expense.created_at).toLocaleString('es-AR'));
                        $('#view_updated_at').text(new Date(expense.updated_at).toLocaleString('es-AR'));
                        
                        $('#viewFixedExpenseModal').modal('show');
                    } else {
                        showError('Error al cargar detalles: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al cargar detalles');
                }
            });
        }

        function editFixedExpense(id) {
            $.ajax({
                url: 'controllers/controller.php',
                method: 'GET',
                data: { action: 'details', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const expense = response.data;
                        
                        $('#edit_id').val(expense.id);
                        $('#edit_fecha_inicio').val(expense.fecha_inicio);
                        $('#edit_dia_mes').val(expense.dia_mes);
                        $('#edit_nombre').val(expense.nombre);
                        $('#edit_monto').val(expense.monto);
                        $('#edit_cuotas_restantes').val(expense.cuotas_restantes || '');
                        $('#edit_mes_ultima_cuota').val(expense.mes_ultima_cuota || '');
                        $('#edit_fecha_fin').val(expense.fecha_fin || '');
                        $('#edit_activo').prop('checked', expense.activo == 1);
                        
                        // Toggle fields based on quota presence
                        toggleLastQuotaFieldEdit();
                        
                        $('#editFixedExpenseModal').modal('show');
                    } else {
                        showError('Error al cargar datos: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al cargar datos');
                }
            });
        }

        function deleteFixedExpense(id, name = '') {
            Swal.fire({
                title: '¿Eliminar gasto fijo?',
                text: name ? `Se eliminará "${name}"` : 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E91E63',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Segunda confirmación
                    Swal.fire({
                        title: '¿Estás completamente seguro?',
                        text: 'Esta acción eliminará permanentemente el gasto fijo',
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Eliminar definitivamente',
                        cancelButtonText: 'Cancelar'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            performDelete(id);
                        }
                    });
                }
            });
        }

        function performDelete(id) {
            $.ajax({
                url: 'controllers/controller.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadFixedExpenses();
                        showSuccess(response.message || 'Gasto fijo eliminado exitosamente');
                    } else {
                        showError('Error al eliminar: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al eliminar');
                }
            });
        }

        function viewFixedExpense(id) {
            $.ajax({
                url: 'controllers/controller.php',
                method: 'GET',
                data: { action: 'details', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const expense = response.data;
                        
                        $('#view_nombre').text(expense.nombre);
                        $('#view_monto').text('$' + parseFloat(expense.monto).toLocaleString('es-AR', {minimumFractionDigits: 2}));
                        $('#view_fecha_inicio').text(expense.fecha_inicio ? new Date(expense.fecha_inicio).toLocaleDateString('es-AR') : 'No definida');
                        $('#view_fecha_fin').text(expense.fecha_fin ? new Date(expense.fecha_fin).toLocaleDateString('es-AR') : 'No definida');
                        $('#view_dia_mes').html(`<span class="day-highlight">${expense.dia_mes}</span>`);
                        $('#view_estado').html(getStatusBadge(expense));
                        $('#view_cuotas_restantes').text(expense.cuotas_restantes || 'Sin límite');
                        $('#view_mes_ultima_cuota').text(expense.mes_ultima_cuota || 'N/A');
                        $('#view_proximo_pago').text(calculateNextPayment(expense));
                        $('#view_created_at').text(new Date(expense.created_at).toLocaleString('es-AR'));
                        $('#view_updated_at').text(new Date(expense.updated_at).toLocaleString('es-AR'));
                        
                        $('#viewFixedExpenseModal').modal('show');
                    } else {
                        showError('Error al cargar detalles: ' + response.error);
                    }
                },
                error: function() {
                    showError('Error de conexión al cargar detalles');
                }
            });
        }

        function deleteFixedExpense(id, name) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el gasto fijo "${name}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'controllers/controller.php',
                        method: 'POST',
                        data: { action: 'delete', id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                loadFixedExpenses();
                                showSuccess('Gasto fijo eliminado exitosamente');
                            } else {
                                showError('Error al eliminar: ' + response.error);
                            }
                        },
                        error: function() {
                            showError('Error de conexión al eliminar');
                        }
                    });
                }
            });
        }

        function getActionButtons(id, name) {
            return `
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewFixedExpense(${id})" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="editFixedExpense(${id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteFixedExpense(${id}, '${name}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000
            });
        }
    </script>
</body>
</html>
