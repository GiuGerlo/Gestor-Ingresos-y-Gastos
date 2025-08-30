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

// Obtener estadísticas de gastos
try {
    // Total gastos del período seleccionado
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(monto), 0) as total_mes
        FROM gastos 
        WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $total_mes = $stmt->fetch()['total_mes'];

    // Contar gastos del período seleccionado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as cantidad_mes
        FROM gastos 
        WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $cantidad_mes = $stmt->fetch()['cantidad_mes'];

    // Categoría más usada del período seleccionado
    $stmt = $pdo->prepare("
        SELECT c.nombre, COUNT(*) as total
        FROM gastos g
        JOIN categorias c ON g.categoria_id = c.id
        WHERE g.user_id = ? AND YEAR(g.fecha) = ? AND MONTH(g.fecha) = ?
        GROUP BY c.id
        ORDER BY total DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id, $ano_seleccionado, $mes_seleccionado]);
    $categoria_top = $stmt->fetch();
    $categoria_favorita = $categoria_top ? $categoria_top['nombre'] : 'N/A';

    // Promedio diario del período seleccionado
    $dias_en_mes = date('t', mktime(0, 0, 0, $mes_seleccionado, 1, $ano_seleccionado));
    $promedio_diario = $dias_en_mes > 0 ? $total_mes / $dias_en_mes : 0;

    // Obtener categorías de gasto activas
    $stmt = $pdo->prepare("SELECT id, nombre FROM categorias WHERE tipo = 'gasto' AND activo = 1 ORDER BY nombre");
    $stmt->execute();
    $categorias = $stmt->fetchAll();

    // Obtener métodos de pago activos
    $stmt = $pdo->prepare("SELECT id, nombre, color FROM metodos_pago WHERE activo = 1 ORDER BY nombre");
    $stmt->execute();
    $metodos_pago = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error obteniendo datos de gastos: " . $e->getMessage());
    $total_mes = $cantidad_mes = $promedio_diario = 0;
    $categoria_favorita = 'N/A';
    $categorias = $metodos_pago = [];
}

// Variables para el header dinámico
$current_page = 'gastos';
$nombre_mes = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
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

// Generar opciones de los últimos 12 meses
for ($i = 0; $i < 12; $i++) {
    $mes_opcion = date('n', strtotime("-$i months"));
    $ano_opcion = date('Y', strtotime("-$i months"));
    $nombre_opcion = $nombre_mes[$mes_opcion] . ' ' . $ano_opcion;
    $es_actual = ($mes_opcion == $mes_seleccionado && $ano_opcion == $ano_seleccionado);
    
    $header_buttons .= '<li><a class="dropdown-item' . ($es_actual ? ' active' : '') . '" href="?mes=' . $mes_opcion . '&ano=' . $ano_opcion . '">' . $nombre_opcion . '</a></li>';
}

$header_buttons .= '
    </ul>
</div>
<button type="button" class="btn btn-warning btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
    <i class="fas fa-plus me-1"></i>
    Nuevo Gasto
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
    .expense-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }
    .category-card {
        border-left: 4px solid #ffc107;
        transition: all 0.3s ease;
    }
    .category-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }
    .dropdown-menu {
        max-height: 300px;
        overflow-y: auto;
    }
    .dropdown-item.active {
        background-color: #ffc107 !important;
        color: black !important;
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
                    <h1 class="h2 mb-1">
                        <i class="fas fa-minus-circle text-warning me-2"></i>
                        Módulo de Gastos
                    </h1>
                    <p class="text-muted mb-0">Controla y analiza todos tus gastos variables</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <?php echo $header_buttons; ?>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="expense-icon bg-warning bg-gradient text-white me-3">
                                    <i class="fas fa-dollar-sign fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Total del Período</h6>
                                    <h4 class="mb-0 text-warning">$<?php echo number_format($total_mes, 2, ',', '.'); ?></h4>
                                    <small class="text-muted"><?php echo $periodo_actual; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="expense-icon bg-primary bg-gradient text-white me-3">
                                    <i class="fas fa-list-ol fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Gastos Registrados</h6>
                                    <h4 class="mb-0 text-primary"><?php echo $cantidad_mes; ?></h4>
                                    <small class="text-muted"><?php echo $periodo_actual; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="expense-icon bg-info bg-gradient text-white me-3">
                                    <i class="fas fa-star fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Categoría Top</h6>
                                    <h6 class="mb-0 text-info"><?php echo htmlspecialchars($categoria_favorita); ?></h6>
                                    <small class="text-muted">Más utilizada</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="expense-icon bg-success bg-gradient text-white me-3">
                                    <i class="fas fa-chart-line fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-1">Promedio Diario</h6>
                                    <h4 class="mb-0 text-success">$<?php echo number_format($promedio_diario, 2, ',', '.'); ?></h4>
                                    <small class="text-muted"><?php echo $periodo_actual; ?></small>
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
                                    <button type="button" class="btn btn-warning btn-lg w-100" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                        <i class="fas fa-plus-circle d-block mb-2 fa-2x"></i>
                                        <span>Registrar Gasto</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="loadExpenseTable()">
                                        <i class="fas fa-table d-block mb-2 fa-2x"></i>
                                        <span>Ver Tabla</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-outline-info btn-lg w-100" onclick="showExpenseAnalysis()">
                                        <i class="fas fa-chart-pie d-block mb-2 fa-2x"></i>
                                        <span>Análisis</span>
                                    </button>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <button type="button" class="btn btn-outline-secondary btn-lg w-100" onclick="exportExpenses()">
                                        <i class="fas fa-download d-block mb-2 fa-2x"></i>
                                        <span>Exportar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categorías populares -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tags me-2"></i>
                                Agregar Gasto por Categoría
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php 
                                $category_icons = [
                                    'Supermercado' => 'shopping-cart',
                                    'Comida' => 'utensils',
                                    'Transporte' => 'car',
                                    'Suscripciones' => 'tv',
                                    'Ropa' => 'tshirt',
                                    'Varios' => 'ellipsis-h'
                                ];
                                
                                foreach (array_slice($categorias, 0, 6) as $categoria): 
                                    $icon = $category_icons[$categoria['nombre']] ?? 'tag';
                                ?>
                                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                        <div class="card category-card h-100" onclick="quickAddExpense(<?php echo $categoria['id']; ?>, '<?php echo htmlspecialchars($categoria['nombre']); ?>')">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-<?php echo $icon; ?> fa-2x text-warning mb-2"></i>
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($categoria['nombre']); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de gastos -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historial de Gastos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="expensesTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
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
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para agregar gasto -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fas fa-minus-circle me-2"></i>
                    Registrar Nuevo Gasto
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addExpenseForm">
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
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required placeholder="Describe tu gasto..."></textarea>
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
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>
                        <span id="submitText">Guardar Gasto</span>
                        <div id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Footer con scripts personalizados
$custom_scripts = '
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
    loadExpenseTable();
    
    // Manejar formulario de agregar gasto
    $("#addExpenseForm").on("submit", function(e) {
        e.preventDefault();
        
        const submitBtn = $("#addExpenseForm button[type=submit]");
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
                    $("#addExpenseModal").modal("hide");
                    $("#addExpenseForm")[0].reset();
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        title: "¡Gasto Registrado!",
                        text: response.message,
                        icon: "success",
                        confirmButtonColor: "#ffc107"
                    });
                    
                    // Recargar tabla
                    $("#expensesTable").DataTable().ajax.reload();
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
                let errorMessage = "Error al guardar el gasto";
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
                submitText.text("Guardar Gasto");
                submitSpinner.hide();
            }
        });
    });
});

// Función para cargar tabla de gastos
function loadExpenseTable() {
    if ($.fn.DataTable.isDataTable("#expensesTable")) {
        $("#expensesTable").DataTable().destroy();
    }
    
    // Obtener parámetros de URL para el filtro
    const urlParams = new URLSearchParams(window.location.search);
    const mes = urlParams.get("mes") || "<?php echo $mes_seleccionado; ?>";
    const ano = urlParams.get("ano") || "<?php echo $ano_seleccionado; ?>";
    
    $("#expensesTable").DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        ajax: {
            url: `controllers/controller.php?action=list&mes=${mes}&ano=${ano}`,
            type: "GET",
            dataSrc: function(json) {
                if (json.success) {
                    return json.data;
                } else {
                    console.error("Error cargando gastos:", json.error);
                    return [];
                }
            }
        },
        columns: [
            {
                data: "id",
                render: function(data) {
                    return `<span class="badge bg-secondary">#${data}</span>`;
                }
            },
            {
                data: "fecha",
                render: function(data) {
                    const fecha = new Date(data);
                    return fecha.toLocaleDateString("es-AR");
                }
            },
            {
                data: "descripcion",
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            {
                data: "categoria",
                render: function(data) {
                    return `<span class="badge bg-warning">${data}</span>`;
                }
            },
            {
                data: "metodo_pago",
                render: function(data, type, row) {
                    return `<span class="badge" style="background-color: ${row.color_metodo};">${data}</span>`;
                }
            },
            {
                data: "monto",
                render: function(data) {
                    return `<span class="fw-bold text-danger">$${parseFloat(data).toLocaleString("es-AR", {minimumFractionDigits: 2})}</span>`;
                }
            },
            {
                data: "id",
                orderable: false,
                searchable: false,
                render: function(data) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="editExpense(${data})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteExpense(${data})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, "desc"]],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]]
    });
}

// Función para agregar gasto rápido por categoría
function quickAddExpense(categoriaId, categoriaNombre) {
    $("#categoria_id").val(categoriaId);
    $("#addExpenseModal").modal("show");
}

// Funciones placeholder para futuras implementaciones
function showExpenseAnalysis() {
    Swal.fire("Próximamente", "El análisis de gastos estará disponible pronto", "info");
}

function exportExpenses() {
    Swal.fire("Próximamente", "La funcionalidad de exportación estará disponible pronto", "info");
}

function editExpense(id) {
    Swal.fire("Próximamente", "La funcionalidad de edición estará disponible pronto", "info");
}

function deleteExpense(id) {
    Swal.fire({
        title: "¿Estás seguro?",
        text: "Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí se implementará la eliminación
            Swal.fire("Próximamente", "La funcionalidad de eliminación estará disponible pronto", "info");
        }
    });
}
</script>
';

// Incluir el footer
include '../includes/footer.php';
?>
