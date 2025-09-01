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

// Obtener estadísticas de categorías desde la BD
try {
    // Total categorías
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
    $total_categorias = $stmt->fetch()['total'];
    
    // Categorías activas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
    $categorias_activas = $stmt->fetch()['total'];
    
    // Categorías de ingresos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE tipo = 'ingreso'");
    $categorias_ingresos = $stmt->fetch()['total'];
    
    // Categorías de gastos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE tipo = 'gasto'");
    $categorias_gastos = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
    $total_categorias = $categorias_activas = $categorias_ingresos = $categorias_gastos = 0;
}

// Variables para el header dinámico - ajustadas para subcarpeta
$current_page = 'categorias';
$header_buttons = '
<button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
    <i class="fas fa-plus me-1"></i>
    Nueva Categoría
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
                        <i class="fas fa-tags text-primary me-2"></i>
                        Gestión de Categorías
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
                                        <h4><?php echo $total_categorias; ?></h4>
                                        <small>Total Categorías</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-tags fa-2x"></i>
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
                                        <h4><?php echo $categorias_activas; ?></h4>
                                        <small>Categorías Activas</small>
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
                                        <h4><?php echo $categorias_ingresos; ?></h4>
                                        <small>Ingresos</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-arrow-up fa-2x"></i>
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
                                        <h4><?php echo $categorias_gastos; ?></h4>
                                        <small>Gastos</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-arrow-down fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de categorías -->
                <div class="card shadow">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            Lista de Categorías del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categoriasTable" class="table table-striped table-hover" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Categoría</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Registrada</th>
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

                <!-- Incluir modal para agregar categoría -->
                <?php include 'templates/add_category_modal.php'; ?>

                <!-- Incluir modal para editar categoría -->
                <?php include 'templates/edit_category_modal.php'; ?>

                <!-- Incluir modal para eliminar categoría -->
                <?php include 'templates/delete_category_modal.php'; ?>

                <!-- Incluir modal para ver detalles de la categoría -->
                <?php include 'templates/view_category_modal.php'; ?>

                <!-- Incluir selector de iconos -->
                <?php include '../includes/icon_picker.php'; ?>
            </main>
        </div>
    </div>

    <!-- Estilos personalizados para categorías -->
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
    </style>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<?php 
// Footer con scripts personalizados
$custom_scripts = '
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
    const table = $("#categoriasTable").DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        ajax: {
            url: "controllers/controller.php?action=list",
            type: "GET",
            dataSrc: function(json) {
                if (json.success) {
                    return json.data;
                } else {
                    console.error("Error cargando categorías:", json.error);
                    return [];
                }
            },
            error: function(xhr, error, thrown) {
                console.error("Error AJAX:", error);
                showAlert("Error al cargar las categorías", "danger");
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
                    const iconClass = row.icono || "fas fa-folder";
                    const iconType = row.tipo === "ingreso" ? "arrow-up text-success" : "arrow-down text-danger";
                    return `
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                <i class="${iconClass} text-white"></i>
                            </div>
                            <div>
                                <strong>${data}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-${iconType} me-1"></i>
                                    ${row.tipo.charAt(0).toUpperCase() + row.tipo.slice(1)}
                                </small>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: "tipo",
                render: function(data) {
                    if (data === "ingreso") {
                        return `<span class="badge bg-success">
                                    <i class="fas fa-arrow-up me-1"></i>Ingreso
                                </span>`;
                    } else {
                        return `<span class="badge bg-danger">
                                    <i class="fas fa-arrow-down me-1"></i>Gasto
                                </span>`;
                    }
                }
            },
            {
                data: "activo",
                render: function(data) {
                    if (data) {
                        return `<span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Activa
                                </span>`;
                    } else {
                        return `<span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Inactiva
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
                                    data-id="${data}" title="Editar categoría" data-bs-toggle="tooltip">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete" 
                                    data-id="${data}" title="Eliminar categoría" data-bs-toggle="tooltip">
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
        const categoryId = $(this).data("id");
        viewCategory(categoryId);
    });
    
    $(document).on("click", ".btn-edit", function() {
        const categoryId = $(this).data("id");
        editCategory(categoryId);
    });
    
    $(document).on("click", ".btn-delete", function() {
        const categoryId = $(this).data("id");
        deleteCategory(categoryId);
    });
    
    // Función para ver detalles de la categoría
    function viewCategory(categoryId) {
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById("viewCategoryModal"));
        modal.show();
        
        // Mostrar estado de carga
        $("#categoryDetailsLoading").show();
        $("#categoryDetailsContent").hide();
        $("#categoryDetailsError").hide();
        
        // Cargar datos de la categoría via AJAX
        $.ajax({
            url: `controllers/controller.php?action=details&id=${categoryId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    populateCategoryDetails(response.data);
                    $("#categoryDetailsLoading").hide();
                    $("#categoryDetailsContent").show();
                } else {
                    showCategoryDetailsError();
                }
            },
            error: function() {
                showCategoryDetailsError();
            }
        });
    }
    
    // Función para poblar los detalles de la categoría en el modal
    function populateCategoryDetails(category) {
        // Información básica
        $("#categoryDetailId").text(`#${category.id}`);
        $("#categoryDetailName").text(category.nombre);
        $("#categoryDetailFullName").text(category.nombre);
        
        // Tipo con badge
        if (category.tipo === "ingreso") {
            $("#categoryDetailType").text("Ingreso");
            $("#categoryDetailTypeBadge").html(`
                <i class="fas fa-arrow-up me-2"></i>Ingreso
            `).removeClass().addClass("badge bg-white bg-opacity-25 px-3 py-2 fs-6");
        } else {
            $("#categoryDetailType").text("Gasto");
            $("#categoryDetailTypeBadge").html(`
                <i class="fas fa-arrow-down me-2"></i>Gasto
            `).removeClass().addClass("badge bg-white bg-opacity-25 px-3 py-2 fs-6");
        }
        
        // Estado con badge
        if (category.activo) {
            $("#categoryDetailStatus").removeClass().addClass("badge bg-success fs-6").html(`
                <i class="fas fa-check-circle me-1"></i>Activa
            `);
            $("#categoryDetailStatusBadge").html(`
                <i class="fas fa-check-circle me-2"></i>Activa
            `).removeClass().addClass("badge bg-white bg-opacity-25 px-3 py-2 fs-6");
        } else {
            $("#categoryDetailStatus").removeClass().addClass("badge bg-danger fs-6").html(`
                <i class="fas fa-times-circle me-1"></i>Inactiva
            `);
            $("#categoryDetailStatusBadge").html(`
                <i class="fas fa-times-circle me-2"></i>Inactiva
            `).removeClass().addClass("badge bg-white bg-opacity-25 px-3 py-2 fs-6");
        }
        
        // Configurar botón de editar
        $("#editCategoryFromModal").off("click").on("click", function() {
            $("#viewCategoryModal").modal("hide");
            editCategory(category.id);
        });
    }
    
    // Función para mostrar error en los detalles
    function showCategoryDetailsError() {
        $("#categoryDetailsLoading").hide();
        $("#categoryDetailsContent").hide();
        $("#categoryDetailsError").show();
    }
    
    // ========================================
    // FUNCIONALIDAD AGREGAR CATEGORÍA
    // ========================================
    
    // Evento para el botón de guardar nueva categoría
    $("#saveNewCategory").on("click", function() {
        const form = document.getElementById("addCategoryForm");
        
        // Validar formulario
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const categoryData = {
            nombre: formData.get("nombre"),
            tipo: formData.get("tipo"),
            icono: formData.get("icono") || "fas fa-folder",
            activo: formData.get("activo")
        };
        
        // Mostrar loading
        $("#saveCategorySpinner").show();
        $("#saveCategoryIcon").hide();
        $("#saveNewCategory").prop("disabled", true);
        
        // Enviar datos via AJAX
        $.ajax({
            url: "controllers/controller.php?action=create",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(categoryData),
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#addCategoryModal").modal("hide");
                    showAlert(response.message, "success");
                    
                    // Recargar la tabla para mostrar la nueva categoría
                    table.ajax.reload();
                    
                    // Limpiar formulario
                    resetAddCategoryForm();
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al crear la categoría";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#saveCategorySpinner").hide();
                $("#saveCategoryIcon").show();
                $("#saveNewCategory").prop("disabled", false);
            }
        });
    });
    
    // Función para resetear el formulario de agregar categoría
    function resetAddCategoryForm() {
        const form = document.getElementById("addCategoryForm");
        form.reset();
        form.classList.remove("was-validated");
        
        // Resetear valores por defecto
        $("#addCategoryStatus").val("1");
        $("#addCategoryIcon").val("fas fa-folder");
        $("#addCategoryIconPreview").html("<i class=\"fas fa-folder\"></i>");
    }
    
    // Resetear formulario cuando se cierra el modal
    $("#addCategoryModal").on("hidden.bs.modal", function() {
        resetAddCategoryForm();
    });
    
    // Función para editar categoría
    function editCategory(categoryId) {
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById("editCategoryModal"));
        modal.show();
        
        // Mostrar estado de carga
        $("#editCategoryLoading").show();
        $("#editCategoryForm").hide();
        $("#editCategoryError").hide();
        $("#saveEditCategory").hide();
        
        // Cargar datos de la categoría via AJAX
        $.ajax({
            url: `controllers/controller.php?action=details&id=${categoryId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $("#editCategoryLoading").hide();
                    $("#editCategoryForm").show();
                    $("#saveEditCategory").show();
                } else {
                    showEditCategoryError();
                }
            },
            error: function() {
                showEditCategoryError();
            }
        });
    }
    
    // Función para poblar el formulario de edición
    function populateEditForm(category) {
        $("#editCategoryId").val(category.id);
        $("#editCategoryName").val(category.nombre);
        $("#editCategoryType").val(category.tipo);
        $("#editCategoryStatus").val(category.activo ? "1" : "0");
        
        // Actualizar campo de icono
        const iconoActual = category.icono || "fas fa-folder";
        $("#editCategoryIcon").val(iconoActual);
        $("#editCategoryIconPreview").html(`<i class="${iconoActual}"></i>`);
        
        // Limpiar validaciones previas
        $("#editCategoryForm").removeClass("was-validated");
        $("#editCategoryForm .form-control").removeClass("is-invalid is-valid");
        $("#editCategoryForm .form-select").removeClass("is-invalid is-valid");
    }
    
    // Función para mostrar error en la carga de edición
    function showEditCategoryError() {
        $("#editCategoryLoading").hide();
        $("#editCategoryForm").hide();
        $("#editCategoryError").show();
        $("#saveEditCategory").hide();
    }
    
    // Evento para el botón de guardar cambios de la categoría
    $("#saveEditCategory").on("click", function() {
        const form = document.getElementById("editCategoryForm");
        
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const categoryData = {
            nombre: formData.get("nombre"),
            tipo: formData.get("tipo"),
            icono: formData.get("icono") || "fas fa-folder",
            activo: formData.get("activo")
        };
        
        const categoryId = formData.get("id");
        
        // Mostrar loading
        $("#saveEditCategorySpinner").show();
        $("#saveEditCategoryIcon").hide();
        $("#saveEditCategory").prop("disabled", true);
        
        // Enviar datos via AJAX
        $.ajax({
            url: `controllers/controller.php?action=update&id=${categoryId}`,
            type: "PUT",
            contentType: "application/json",
            data: JSON.stringify(categoryData),
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#editCategoryModal").modal("hide");
                    showAlert(response.message, "success");
                    
                    // Recargar la tabla para mostrar los cambios
                    table.ajax.reload();
                    
                    // Limpiar formulario
                    resetEditCategoryForm();
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al actualizar la categoría";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#saveEditCategorySpinner").hide();
                $("#saveEditCategoryIcon").show();
                $("#saveEditCategory").prop("disabled", false);
            }
        });
    });
    
    // Función para resetear el formulario de editar categoría
    function resetEditCategoryForm() {
        const form = document.getElementById("editCategoryForm");
        form.reset();
        form.classList.remove("was-validated");
        $("#editCategoryForm .form-control").removeClass("is-invalid is-valid");
        $("#editCategoryForm .form-select").removeClass("is-invalid is-valid");
        
        // Resetear icono por defecto
        $("#editCategoryIcon").val("fas fa-folder");
        $("#editCategoryIconPreview").html("<i class=\"fas fa-folder\"></i>");
    }
    
    // Resetear formulario cuando se cierra el modal de edición
    $("#editCategoryModal").on("hidden.bs.modal", function() {
        resetEditCategoryForm();
    });
    
    // Función para eliminar categoría
    function deleteCategory(categoryId) {
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
                            <li>• La categoría y toda su información</li>
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
                // Cargar datos de la categoría para mostrar en el modal de segunda confirmación
                $.ajax({
                    url: `controllers/controller.php?action=details&id=${categoryId}`,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            showDeleteCategoryModal(response.data);
                        } else {
                            showAlert("Error al obtener los datos de la categoría", "danger");
                        }
                    },
                    error: function() {
                        showAlert("Error al cargar los datos de la categoría", "danger");
                    }
                });
            }
        });
    }
    
    // Función para mostrar el modal de eliminación con los datos de la categoría
    function showDeleteCategoryModal(category) {
        // Poblar datos de la categoría en el modal
        $("#deleteCategoryName").text(category.nombre);
        $("#deleteCategoryType").text(category.tipo === "ingreso" ? "Ingreso" : "Gasto");
        
        // Resetear formulario
        $("#deleteConfirmText").val("");
        $("#confirmDeleteCategory").prop("disabled", true);
        $("#confirmTextError").hide();
        
        // Almacenar ID de la categoría para usar después
        $("#confirmDeleteCategory").data("category-id", category.id);
        $("#confirmDeleteCategory").data("category-name", category.nombre);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById("deleteCategoryModal"));
        modal.show();
    }
    
    // Validación en tiempo real para el campo de confirmación
    $("#deleteConfirmText").on("input", function() {
        const confirmText = $(this).val();
        const isValid = confirmText === "ELIMINAR";
        
        $("#confirmDeleteCategory").prop("disabled", !isValid);
        
        if (confirmText.length > 0 && !isValid) {
            $("#confirmTextError").show();
        } else {
            $("#confirmTextError").hide();
        }
    });
    
    // Evento para confirmar eliminación final
    $("#confirmDeleteCategory").on("click", function() {
        const categoryId = $(this).data("category-id");
        const categoryName = $(this).data("category-name");
        const confirmText = $("#deleteConfirmText").val();
        
        // Validación final
        if (confirmText !== "ELIMINAR") {
            $("#confirmTextError").show();
            return;
        }
        
        // Mostrar loading
        $("#deleteCategorySpinner").show();
        $("#deleteCategoryIcon").hide();
        $("#confirmDeleteCategory").prop("disabled", true);
        
        // Enviar petición de eliminación
        $.ajax({
            url: `controllers/controller.php?action=delete&id=${categoryId}`,
            type: "POST",
            data: {_method: "DELETE"},
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#deleteCategoryModal").modal("hide");
                    
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
                        title: "Categoría Eliminada",
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
                    
                    // Recargar la tabla para quitar la categoría eliminada
                    table.ajax.reload();
                    
                    // Log de la acción (opcional)
                    console.log(`Categoría ${categoryName} (ID: ${categoryId}) eliminada exitosamente`);
                    if (response.deleted_records) {
                        console.log("Registros eliminados:", response.deleted_records);
                    }
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al eliminar la categoría";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#deleteCategorySpinner").hide();
                $("#deleteCategoryIcon").show();
                $("#confirmDeleteCategory").prop("disabled", false);
            }
        });
    });
    
    // Resetear modal cuando se cierra
    $("#deleteCategoryModal").on("hidden.bs.modal", function() {
        $("#deleteConfirmText").val("");
        $("#confirmDeleteCategory").prop("disabled", true);
        $("#confirmTextError").hide();
        $("#deleteCategorySpinner").hide();
        $("#deleteCategoryIcon").show();
    });
    
    // Enfocar automáticamente el campo de confirmación cuando se abre el modal
    $("#deleteCategoryModal").on("shown.bs.modal", function() {
        $("#deleteConfirmText").focus();
    });
    
    // Inicializar tooltips iniciales
    initializeTooltips();
    
    console.log("✅ Gestión de Categorías - DataTable con datos reales inicializado correctamente");
});
</script>
';

// Incluir el footer
include '../includes/footer.php';
?>
