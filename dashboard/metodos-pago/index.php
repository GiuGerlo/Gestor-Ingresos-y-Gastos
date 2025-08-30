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

// Incluir conexión a la base de datos
require_once '../../config/connect.php';

// Obtener estadísticas de métodos de pago
try {
    // Total de métodos de pago
    $stmt_total = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago");
    $total_metodos = $stmt_total->fetch()['total'];

    // Métodos activos
    $stmt_activos = $pdo->query("SELECT COUNT(*) as activos FROM metodos_pago WHERE activo = 1");
    $metodos_activos = $stmt_activos->fetch()['activos'];

    // Métodos inactivos
    $metodos_inactivos = $total_metodos - $metodos_activos;

    // Último método agregado
    $stmt_ultimo = $pdo->query("SELECT nombre FROM metodos_pago ORDER BY created_at DESC LIMIT 1");
    $ultimo_metodo = $stmt_ultimo->fetch()['nombre'] ?? 'Ninguno';
} catch (PDOException $e) {
    $total_metodos = 0;
    $metodos_activos = 0;
    $metodos_inactivos = 0;
    $ultimo_metodo = 'Error';
}

// Variables para el header dinámico - ajustadas para subcarpeta
$current_page = 'metodos-pago';
$header_buttons = '
<button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
    <i class="fas fa-plus me-1"></i>
    Nuevo Método
</button>
<a href="../admin.php" class="btn btn-sm btn-outline-secondary">
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

            <!-- Estadísticas rápidas -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $total_metodos; ?></h4>
                                    <small>Total Métodos</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-credit-card fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $metodos_activos; ?></h4>
                                    <small>Métodos Activos</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $metodos_inactivos; ?></h4>
                                    <small>Inactivos</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="text-truncate"><?php echo $ultimo_metodo; ?></h6>
                                    <small>Último Agregado</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-plus-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de métodos de pago -->
            <div class="card shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>
                        Lista de Métodos de Pago del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="paymentMethodsTable" class="table table-striped table-hover" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Color</th>
                                    <th>Estado</th>
                                    <th>Registrado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán dinámicamente via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Incluir modal para agregar método de pago -->
            <?php include 'templates/add_payment_method_modal.php'; ?>

            <!-- Incluir modal para editar método de pago -->
            <?php include 'templates/edit_payment_method_modal.php'; ?>

            <!-- Incluir modal para eliminar método de pago -->
            <?php include 'templates/delete_payment_method_modal.php'; ?>

            <!-- Incluir modal para ver detalles del método de pago -->
            <?php include 'templates/view_payment_method_modal.php'; ?>
        </main>
    </div>
</div>

<!-- Estilos personalizados para métodos de pago -->
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
    }

    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }

    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .text-truncate-custom {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        const table = $('#paymentMethodsTable').DataTable({
            "ajax": {
                "url": "controllers/controller.php",
                "type": "GET",
                "data": {
                    action: 'getAll'
                },
                "dataSrc": function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        console.error('Error al cargar métodos de pago:', json.message);
                        return [];
                    }
                }
            },
            "columns": [{
                    "data": "id",
                    "width": "60px",
                    "className": "text-center fw-bold"
                },
                {
                    "data": "nombre",
                    "render": function(data, type, row) {
                        return `<span class="fw-semibold">${data}</span>`;
                    }
                },
                {
                    "data": "color",
                    "render": function(data, type, row) {
                        return `
                        <div class="d-flex align-items-center">
                            <div class="me-2" style="width: 20px; height: 20px; background-color: ${data}; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                            <code class="text-muted small">${data}</code>
                        </div>
                    `;
                    },
                    "width": "150px"
                },
                {
                    "data": "activo",
                    "render": function(data, type, row) {
                        if (data == 1) {
                            return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Activo</span>';
                        } else {
                            return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactivo</span>';
                        }
                    },
                    "width": "100px",
                    "className": "text-center"
                },
                {
                    "data": "created_at_formatted",
                    "width": "130px",
                    "className": "text-center"
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-info btn-action" onclick="viewPaymentMethod(${row.id})" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-action" onclick="editPaymentMethod(${row.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-action" onclick="deletePaymentMethod(${row.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                    },
                    "orderable": false,
                    "searchable": false,
                    "width": "120px",
                    "className": "text-center"
                }
            ],
            "order": [
                [0, "desc"]
            ],
            "pageLength": 25,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
            },
            "responsive": true,
            "autoWidth": false,
            "dom": 'Bfrtip',
            "buttons": [{
                text: '<i class="fas fa-sync-alt me-1"></i>Actualizar',
                className: 'btn btn-outline-primary btn-sm',
                action: function(e, dt, node, config) {
                    dt.ajax.reload();
                }
            }]
        });

        // Event listeners para formularios
        $('#addPaymentMethodForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'create');

            // Deshabilitar botón de envío
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Creando...');

            $.ajax({
                url: 'controllers/controller.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#addPaymentMethodModal').modal('hide');
                        $('#addPaymentMethodForm')[0].reset();
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión al servidor'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        $('#editPaymentMethodForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'update');

            // Deshabilitar botón de envío
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...');

            $.ajax({
                url: 'controllers/controller.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#editPaymentMethodModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión al servidor'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        $('#deletePaymentMethodForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'delete');

            // Deshabilitar botón de envío
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Eliminando...');

            $.ajax({
                url: 'controllers/controller.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#deletePaymentMethodModal').modal('hide');
                        table.ajax.reload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión al servidor'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Limpiar formularios al cerrar modales
        $('#addPaymentMethodModal').on('hidden.bs.modal', function() {
            $('#addPaymentMethodForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
        });

        $('#editPaymentMethodModal').on('hidden.bs.modal', function() {
            $('#editPaymentMethodForm')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();
        });
    });

    // ========================================
    // FUNCIONES PARA OPERACIONES CRUD
    // ========================================

    // Función para ver detalles de un método de pago
    function viewPaymentMethod(id) {
        // Mostrar loading
        $('#paymentMethodDetailsLoading').show();
        $('#paymentMethodDetailsContent').hide();
        $('#paymentMethodDetailsError').hide();
        $('#viewPaymentMethodModal').modal('show');

        $.ajax({
            url: 'controllers/controller.php',
            type: 'GET',
            data: {
                action: 'getOne',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    setTimeout(() => {
                        populatePaymentMethodDetails(response.data);
                        $('#paymentMethodDetailsLoading').hide();
                        $('#paymentMethodDetailsContent').show();
                    }, 300);
                } else {
                    $('#paymentMethodDetailsLoading').hide();
                    showPaymentMethodDetailsError();
                }
            },
            error: function() {
                $('#paymentMethodDetailsLoading').hide();
                showPaymentMethodDetailsError();
            }
        });
    }

    // Función para editar un método de pago
    function editPaymentMethod(id) {
        $.ajax({
            url: 'controllers/controller.php',
            type: 'GET',
            data: {
                action: 'getOne',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const paymentMethod = response.data;
                    $('#editPaymentMethodId').val(paymentMethod.id);
                    $('#editPaymentMethodName').val(paymentMethod.nombre);
                    $('#editPaymentMethodColor').val(paymentMethod.color);
                    $('#editPaymentMethodStatus').val(paymentMethod.activo);
                    $('#editPaymentMethodModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la información del método de pago'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión al servidor'
                });
            }
        });
    }

    // Función para eliminar un método de pago
    function deletePaymentMethod(id) {
        $.ajax({
            url: 'controllers/controller.php',
            type: 'GET',
            data: {
                action: 'getOne',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    const paymentMethod = response.data;
                    $('#deletePaymentMethodId').val(paymentMethod.id);
                    $('#deletePaymentMethodName').text(paymentMethod.nombre);
                    $('#deleteConfirmationText').val('');
                    $('#deletePaymentMethodModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar la información del método de pago'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión al servidor'
                });
            }
        });
    }

    // ========================================
    // FUNCIONES AUXILIARES
    // ========================================

    // Función para poblar los detalles del método de pago en el modal
    function populatePaymentMethodDetails(paymentMethod) {
        $('#paymentMethodDetailName').text(paymentMethod.nombre);
        $('#paymentMethodDetailId').text('#' + paymentMethod.id);
        $('#paymentMethodDetailFullName').text(paymentMethod.nombre);
        $('#paymentMethodDetailColor').text(paymentMethod.color);

        // Mostrar el color visualmente
        $('#paymentMethodDetailColorPreview').css('background-color', paymentMethod.color);
        $('#paymentMethodDetailColorBadge').css('background-color', paymentMethod.color);
        $('#paymentMethodDetailColorCode').text(paymentMethod.color);

        // Estado con badge
        if (paymentMethod.activo) {
            $('#paymentMethodDetailStatus').removeClass().addClass('badge bg-success fs-6').html(`
            <i class="fas fa-check-circle me-1"></i>Activo
        `);
            $('#paymentMethodDetailStatusBadge').html(`
            <i class="fas fa-check-circle me-2"></i>Activo
        `).removeClass().addClass('badge bg-white bg-opacity-25 px-3 py-2 fs-6');
        } else {
            $('#paymentMethodDetailStatus').removeClass().addClass('badge bg-danger fs-6').html(`
            <i class="fas fa-times-circle me-1"></i>Inactivo
        `);
            $('#paymentMethodDetailStatusBadge').html(`
            <i class="fas fa-times-circle me-2"></i>Inactivo
        `).removeClass().addClass('badge bg-white bg-opacity-25 px-3 py-2 fs-6');
        }

        // Configurar botón de editar
        $('#editPaymentMethodFromModal').off('click').on('click', function() {
            $('#viewPaymentMethodModal').modal('hide');
            editPaymentMethod(paymentMethod.id);
        });
    }

    // Función para mostrar error en los detalles
    function showPaymentMethodDetailsError() {
        $('#paymentMethodDetailsLoading').hide();
        $('#paymentMethodDetailsContent').hide();
        $('#paymentMethodDetailsError').show();
    }

    // ========================================
    // VALIDACIONES DE FORMULARIOS
    // ========================================

    // Validación en tiempo real del texto de confirmación
    $(document).on('input', '#deleteConfirmationText', function() {
        const confirmText = $(this).val();
        const targetText = $('#deletePaymentMethodName').text();
        const submitBtn = $('#deletePaymentMethodForm button[type="submit"]');

        if (confirmText === targetText) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            submitBtn.prop('disabled', false);
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
            submitBtn.prop('disabled', true);
        }
    });
</script>

<!-- Modales -->
<?php include 'templates/add_payment_method_modal.php'; ?>
<?php include 'templates/edit_payment_method_modal.php'; ?>
<?php include 'templates/view_payment_method_modal.php'; ?>
<?php include 'templates/delete_payment_method_modal.php'; ?>

<?php include '../includes/footer.php'; ?>