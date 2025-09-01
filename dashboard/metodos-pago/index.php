<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado y sea superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

// Incluir conexión a la base de datos
require_once '../../config/connect.php';

$user_name = $_SESSION['user_name'] ?? 'Super Administrador';

// Obtener estadísticas de métodos de pago desde la BD
try {
    // Total métodos de pago
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago");
    $total_metodos_pago = $stmt->fetch()['total'];
    
    // Métodos de pago activos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 1");
    $metodos_pago_activos = $stmt->fetch()['total'];
    
    // Métodos de pago inactivos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 0");
    $metodos_pago_inactivos = $stmt->fetch()['total'];
    
    // Método más usado (simulado para futuras referencias)
    $metodo_mas_usado = "Transferencia";
    
} catch (PDOException $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
    $total_metodos_pago = $metodos_pago_activos = $metodos_pago_inactivos = 0;
    $metodo_mas_usado = "N/A";
}

// Variables para el header dinámico - ajustadas para subcarpeta
$current_page = 'metodos-pago';
$header_buttons = '
<button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
    <i class="fas fa-plus me-1"></i>
    Nuevo Método de Pago
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
                        <i class="fas fa-credit-card text-primary me-2"></i>
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
                                        <h4><?php echo $total_metodos_pago; ?></h4>
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
                                        <h4><?php echo $metodos_pago_activos; ?></h4>
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
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $metodos_pago_inactivos; ?></h4>
                                        <small>Métodos Inactivos</small>
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
                                        <h4><?php echo $metodo_mas_usado; ?></h4>
                                        <small>Más Usado</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-star fa-2x"></i>
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
                                        <th>Método</th>
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

                <!-- Incluir selector de iconos -->
                <?php include '../includes/icon_picker.php'; ?>
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
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        
        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            margin-left: 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_info {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            margin: 0 0.125rem;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            color: #007bff;
            text-decoration: none;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        table.dataTable tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .table th {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .color-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<?php 
// Footer con scripts personalizados
$custom_scripts = '
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar DataTable con datos del servidor
    const table = $("#paymentMethodsTable").DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        ajax: {
            url: "controllers/controller.php?action=list",
            type: "GET",
            dataType: "json",
            dataSrc: function(json) {
                if (json && json.success) {
                    return json.data;
                } else {
                    console.error("Error cargando métodos de pago:", json ? json.error : "Respuesta vacía");
                    return [];
                }
            },
            error: function(xhr, error, thrown) {
                console.error("Error AJAX:", error);
                showAlert("Error al cargar los métodos de pago", "danger");
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
                data: "nombre",
                render: function(data, type, row) {
                    const iconClass = row.icono || "fas fa-credit-card";
                    return `
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" 
                                 style="background-color: ${row.color};">
                                <i class="${iconClass} text-white"></i>
                            </div>
                            <div>
                                <strong>${data}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i>
                                    ${row.color}
                                </small>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: "color",
                render: function(data) {
                    return `
                        <div class="d-flex align-items-center">
                            <span class="color-indicator" style="background-color: ${data};"></span>
                            <code class="text-muted">${data}</code>
                        </div>
                    `;
                }
            },
            {
                data: "activo",
                render: function(data) {
                    if (data) {
                        return `<span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Activo
                                </span>`;
                    } else {
                        return `<span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Inactivo
                                </span>`;
                    }
                }
            },
            {
                data: "created_at",
                render: function(data) {
                    const fecha = new Date(data);
                    return `
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            ${fecha.toLocaleDateString("es-AR")}
                            <br>
                            <i class="fas fa-clock me-1"></i>
                            ${fecha.toLocaleTimeString("es-AR", {hour: "2-digit", minute: "2-digit"})}
                        </small>
                    `;
                }
            },
            {
                data: "id",
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-info btn-sm btn-view" 
                                    data-id="${data}" title="Ver detalles" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm btn-edit" 
                                    data-id="${data}" title="Editar método de pago" data-bs-toggle="tooltip">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete" 
                                    data-id="${data}" title="Eliminar método de pago" data-bs-toggle="tooltip">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        dom: "<\"row\"<\"col-sm-12 col-md-6\"l><\"col-sm-12 col-md-6\"f>>" +
             "<\"row\"<\"col-sm-12\"tr>>" +
             "<\"row\"<\"col-sm-12 col-md-5\"i><\"col-sm-12 col-md-7\"p>>",
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
        order: [[0, "desc"]],
        drawCallback: function() {
            // Reinicializar tooltips después de cada redibujado
            initializeTooltips();
        }
    });
    
    // Función para inicializar tooltips
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=\"tooltip\"]"));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Función para mostrar alertas
    function showAlert(message, type = "info") {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insertar al inicio del main content
        $("main").prepend(alertHtml);
        
        // Auto-ocultar después de 5 segundos
        setTimeout(() => {
            $(".alert").fadeOut();
        }, 5000);
    }
    
    // Eventos para botones de acción
    $(document).on("click", ".btn-view", function() {
        const paymentMethodId = $(this).data("id");
        viewPaymentMethod(paymentMethodId);
    });
    
    $(document).on("click", ".btn-edit", function() {
        const paymentMethodId = $(this).data("id");
        editPaymentMethod(paymentMethodId);
    });
    
    $(document).on("click", ".btn-delete", function() {
        const paymentMethodId = $(this).data("id");
        deletePaymentMethod(paymentMethodId);
    });
    
    // Función para ver detalles del método de pago
    function viewPaymentMethod(paymentMethodId) {
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById("viewPaymentMethodModal"));
        modal.show();
        
        // Mostrar estado de carga
        $("#paymentMethodDetailsLoading").show();
        $("#paymentMethodDetailsContent").hide();
        $("#paymentMethodDetailsError").hide();
        
        // Cargar datos del método de pago via AJAX
        $.ajax({
            url: `controllers/controller.php?action=details&id=${paymentMethodId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    populatePaymentMethodDetails(response.data);
                    $("#paymentMethodDetailsLoading").hide();
                    $("#paymentMethodDetailsContent").show();
                } else {
                    showPaymentMethodDetailsError();
                }
            },
            error: function() {
                showPaymentMethodDetailsError();
            }
        });
    }
    
    // Función para poblar los detalles del método de pago en el modal
    function populatePaymentMethodDetails(paymentMethod) {
        // Información básica
        $("#paymentMethodDetailId").text(`#${paymentMethod.id}`);
        $("#paymentMethodDetailName").text(paymentMethod.nombre);
        $("#paymentMethodDetailFullName").text(paymentMethod.nombre);
        
        // Color
        $("#paymentMethodDetailColor").text(paymentMethod.color);
        $("#paymentMethodColorIndicator").css("background-color", paymentMethod.color);
        $("#paymentMethodColorSample").css("background-color", paymentMethod.color);
        
        // Estado con badge
        if (paymentMethod.activo) {
            $("#paymentMethodDetailStatus").removeClass().addClass("badge bg-success fs-6").html(`
                <i class="fas fa-check-circle me-1"></i>Activo
            `);
            $("#paymentMethodDetailStatusBadge").html(`
                <i class="fas fa-check-circle me-2"></i>Activo
            `).removeClass().addClass("badge bg-white bg-opacity-25 px-3 py-2 fs-6");
        } else {
            $("#paymentMethodDetailStatus").removeClass().addClass("badge bg-danger fs-6").html(`
                <i class="fas fa-times-circle me-1"></i>Inactivo
            `);
            $("#paymentMethodDetailStatusBadge").html(`
                <i class="fas fa-times-circle me-2"></i>Inactivo
            `).removeClass().addClass("badge bg-white bg-opacity-25 px-3 py-2 fs-6");
        }
        
        // Configurar botón de editar
        $("#editPaymentMethodFromModal").off("click").on("click", function() {
            $("#viewPaymentMethodModal").modal("hide");
            editPaymentMethod(paymentMethod.id);
        });
    }
    
    // Función para mostrar error en los detalles
    function showPaymentMethodDetailsError() {
        $("#paymentMethodDetailsLoading").hide();
        $("#paymentMethodDetailsContent").hide();
        $("#paymentMethodDetailsError").show();
    }
    
    // ========================================
    // FUNCIONALIDAD AGREGAR MÉTODO DE PAGO
    // ========================================
    
    // Evento para el botón de guardar nuevo método de pago
    $("#saveNewPaymentMethod").on("click", function() {
        const form = document.getElementById("addPaymentMethodForm");
        
        // Validar formulario
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const paymentMethodData = {
            nombre: formData.get("nombre"),
            icono: formData.get("icono") || "fas fa-credit-card",
            color: formData.get("color"),
            activo: formData.get("activo")
        };
        
        // Mostrar loading
        $("#savePaymentMethodSpinner").show();
        $("#savePaymentMethodIcon").hide();
        $("#saveNewPaymentMethod").prop("disabled", true);
        
        // Enviar datos via AJAX
        $.ajax({
            url: "controllers/controller.php?action=create",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(paymentMethodData),
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#addPaymentMethodModal").modal("hide");
                    showAlert(response.message, "success");
                    
                    // Recargar la tabla para mostrar el nuevo método de pago
                    table.ajax.reload();
                    
                    // Limpiar formulario
                    resetAddPaymentMethodForm();
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al crear el método de pago";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#savePaymentMethodSpinner").hide();
                $("#savePaymentMethodIcon").show();
                $("#saveNewPaymentMethod").prop("disabled", false);
            }
        });
    });
    
    // Función para resetear el formulario de agregar método de pago
    function resetAddPaymentMethodForm() {
        const form = document.getElementById("addPaymentMethodForm");
        form.reset();
        form.classList.remove("was-validated");
        
        // Resetear valores por defecto
        $("#addPaymentMethodStatus").val("1");
        $("#addPaymentMethodColor").val("#6548D5");
        $("#addPaymentMethodIcon").val("fas fa-credit-card");
        $("#addPaymentMethodIconPreview").html("<i class=\"fas fa-credit-card\"></i>");
    }
    
    // Resetear formulario cuando se cierra el modal
    $("#addPaymentMethodModal").on("hidden.bs.modal", function() {
        resetAddPaymentMethodForm();
    });
    
    // Función para editar método de pago
    function editPaymentMethod(paymentMethodId) {
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById("editPaymentMethodModal"));
        modal.show();
        
        // Mostrar estado de carga
        $("#editPaymentMethodLoading").show();
        $("#editPaymentMethodForm").hide();
        $("#editPaymentMethodError").hide();
        $("#saveEditPaymentMethod").hide();
        
        // Cargar datos del método de pago via AJAX
        $.ajax({
            url: `controllers/controller.php?action=details&id=${paymentMethodId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $("#editPaymentMethodLoading").hide();
                    $("#editPaymentMethodForm").show();
                    $("#saveEditPaymentMethod").show();
                } else {
                    showEditPaymentMethodError();
                }
            },
            error: function() {
                showEditPaymentMethodError();
            }
        });
    }
    
    // Función para poblar el formulario de edición
    function populateEditForm(paymentMethod) {
        $("#editPaymentMethodId").val(paymentMethod.id);
        $("#editPaymentMethodName").val(paymentMethod.nombre);
        $("#editPaymentMethodColor").val(paymentMethod.color);
        $("#editPaymentMethodStatus").val(paymentMethod.activo ? "1" : "0");
        
        // Actualizar campo de icono
        const iconoActual = paymentMethod.icono || "fas fa-credit-card";
        $("#editPaymentMethodIcon").val(iconoActual);
        $("#editPaymentMethodIconPreview").html("<i class=\"" + iconoActual + "\"></i>");
        
        // Limpiar validaciones previas
        $("#editPaymentMethodForm").removeClass("was-validated");
        $("#editPaymentMethodForm .form-control").removeClass("is-invalid is-valid");
        $("#editPaymentMethodForm .form-select").removeClass("is-invalid is-valid");
    }
    
    // Función para mostrar error en la carga de edición
    function showEditPaymentMethodError() {
        $("#editPaymentMethodLoading").hide();
        $("#editPaymentMethodForm").hide();
        $("#editPaymentMethodError").show();
        $("#saveEditPaymentMethod").hide();
    }
    
    // Evento para el botón de guardar cambios del método de pago
    $("#saveEditPaymentMethod").on("click", function() {
        const form = document.getElementById("editPaymentMethodForm");
        
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const paymentMethodData = {
            nombre: formData.get("nombre"),
            icono: formData.get("icono") || "fas fa-credit-card",
            color: formData.get("color"),
            activo: formData.get("activo")
        };
        
        const paymentMethodId = formData.get("id");
        
        // Mostrar loading
        $("#saveEditPaymentMethodSpinner").show();
        $("#saveEditPaymentMethodIcon").hide();
        $("#saveEditPaymentMethod").prop("disabled", true);
        
        // Enviar datos via AJAX
        $.ajax({
            url: `controllers/controller.php?action=update&id=${paymentMethodId}`,
            type: "PUT",
            contentType: "application/json",
            data: JSON.stringify(paymentMethodData),
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#editPaymentMethodModal").modal("hide");
                    showAlert(response.message, "success");
                    
                    // Recargar la tabla para mostrar los cambios
                    table.ajax.reload();
                    
                    // Limpiar formulario
                    resetEditPaymentMethodForm();
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al actualizar el método de pago";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#saveEditPaymentMethodSpinner").hide();
                $("#saveEditPaymentMethodIcon").show();
                $("#saveEditPaymentMethod").prop("disabled", false);
            }
        });
    });
    
    // Función para resetear el formulario de editar método de pago
    function resetEditPaymentMethodForm() {
        const form = document.getElementById("editPaymentMethodForm");
        form.reset();
        form.classList.remove("was-validated");
        $("#editPaymentMethodForm .form-control").removeClass("is-invalid is-valid");
        $("#editPaymentMethodForm .form-select").removeClass("is-invalid is-valid");
        
        // Resetear icono por defecto
        $("#editPaymentMethodIcon").val("fas fa-credit-card");
        $("#editPaymentMethodIconPreview").html("<i class=\"fas fa-credit-card\"></i>");
    }
    
    // Resetear formulario cuando se cierra el modal de edición
    $("#editPaymentMethodModal").on("hidden.bs.modal", function() {
        resetEditPaymentMethodForm();
    });
    
    // Función para eliminar método de pago
    function deletePaymentMethod(paymentMethodId) {
        // Primera confirmación con SweetAlert
        Swal.fire({
            title: "PRIMERA CONFIRMACIÓN",
            html: `
                <div class="text-center">
                    <h5>¿Estás completamente seguro?</h5>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Esta acción eliminará permanentemente:</strong>
                        <ul class="list-unstyled mt-2 text-start">
                            <li>• El método de pago y toda su información</li>
                            <li>• Todos los ingresos/gastos asociados</li>
                            <li>• Las referencias en otras tablas</li>
                        </ul>
                        <strong>Esta acción NO se puede deshacer.</strong>
                    </div>
                </div>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
            focusCancel: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Cargar datos del método de pago para mostrar en el modal de segunda confirmación
                $.ajax({
                    url: `controllers/controller.php?action=details&id=${paymentMethodId}`,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            showDeletePaymentMethodModal(response.data);
                        } else {
                            showAlert("Error al obtener los datos del método de pago", "danger");
                        }
                    },
                    error: function() {
                        showAlert("Error al cargar los datos del método de pago", "danger");
                    }
                });
            }
        });
    }
    
    // Función para mostrar el modal de eliminación con los datos del método de pago
    function showDeletePaymentMethodModal(paymentMethod) {
        // Poblar datos del método de pago en el modal
        $("#deletePaymentMethodName").text(paymentMethod.nombre);
        $("#deletePaymentMethodColor").text(paymentMethod.color);
        
        // Resetear formulario
        $("#deleteConfirmText").val("");
        $("#confirmDeletePaymentMethod").prop("disabled", true);
        $("#confirmTextError").hide();
        
        // Almacenar ID del método de pago para usar después
        $("#confirmDeletePaymentMethod").data("payment-method-id", paymentMethod.id);
        $("#confirmDeletePaymentMethod").data("payment-method-name", paymentMethod.nombre);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById("deletePaymentMethodModal"));
        modal.show();
    }
    
    // Validación en tiempo real para el campo de confirmación
    $("#deleteConfirmText").on("input", function() {
        const confirmText = $(this).val();
        const isValid = confirmText === "ELIMINAR";
        
        $("#confirmDeletePaymentMethod").prop("disabled", !isValid);
        
        if (confirmText.length > 0 && !isValid) {
            $("#confirmTextError").show();
        } else {
            $("#confirmTextError").hide();
        }
    });
    
    // Evento para confirmar eliminación final
    $("#confirmDeletePaymentMethod").on("click", function() {
        const paymentMethodId = $(this).data("payment-method-id");
        const paymentMethodName = $(this).data("payment-method-name");
        const confirmText = $("#deleteConfirmText").val();
        
        // Validación final
        if (confirmText !== "ELIMINAR") {
            $("#confirmTextError").show();
            return;
        }
        
        // Mostrar loading
        $("#deletePaymentMethodSpinner").show();
        $("#deletePaymentMethodIcon").hide();
        $("#confirmDeletePaymentMethod").prop("disabled", true);
        
        // Enviar petición de eliminación
        $.ajax({
            url: `controllers/controller.php?action=delete&id=${paymentMethodId}`,
            type: "POST",
            data: {_method: "DELETE"},
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#deletePaymentMethodModal").modal("hide");
                    
                    // Mostrar mensaje detallado con SweetAlert
                    let mensaje = response.message;
                    let detalles = "";
                    
                    if (response.total_records && response.total_records > 0) {
                        detalles = `<div class="alert alert-info mt-3">
                            <strong>Registros eliminados:</strong><br>
                            <small>Total: ${response.total_records} registros asociados</small>
                        </div>`;
                    }
                    
                    Swal.fire({
                        title: "Método de Pago Eliminado",
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                <p class="mt-3">${mensaje}</p>
                                ${detalles}
                            </div>
                        `,
                        icon: "success",
                        confirmButtonColor: "#198754",
                        confirmButtonText: "Entendido"
                    });
                    
                    // Recargar la tabla para quitar el método de pago eliminado
                    table.ajax.reload();
                    
                    // Log de la acción (opcional)
                    console.log(`Método de pago ${paymentMethodName} (ID: ${paymentMethodId}) eliminado exitosamente`);
                    if (response.deleted_records) {
                        console.log("Registros eliminados:", response.deleted_records);
                    }
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al eliminar el método de pago";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#deletePaymentMethodSpinner").hide();
                $("#deletePaymentMethodIcon").show();
                $("#confirmDeletePaymentMethod").prop("disabled", false);
            }
        });
    });
    
    // Resetear modal cuando se cierra
    $("#deletePaymentMethodModal").on("hidden.bs.modal", function() {
        $("#deleteConfirmText").val("");
        $("#confirmDeletePaymentMethod").prop("disabled", true);
        $("#confirmTextError").hide();
        $("#deletePaymentMethodSpinner").hide();
        $("#deletePaymentMethodIcon").show();
    });
    
    // Enfocar automáticamente el campo de confirmación cuando se abre el modal
    $("#deletePaymentMethodModal").on("shown.bs.modal", function() {
        $("#deleteConfirmText").focus();
    });
    
    // Inicializar tooltips iniciales
    initializeTooltips();
    
    console.log("✅ Gestión de Métodos de Pago - DataTable con datos reales inicializado correctamente");
});
</script>
';

// Incluir el footer
include '../includes/footer.php';
?>
