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

// Obtener estadísticas de usuarios desde la BD
try {
    // Total usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $stmt->fetch()['total'];
    
    // Usuarios activos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $usuarios_activos = $stmt->fetch()['total'];
    
    // Super admins
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'superadmin'");
    $superadmins = $stmt->fetch()['total'];
    
    // Usuarios con actividad hoy (basado en transacciones)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as total 
        FROM (
            SELECT user_id FROM ingresos WHERE DATE(created_at) = CURDATE()
            UNION
            SELECT user_id FROM gastos WHERE DATE(created_at) = CURDATE()
        ) as actividad_hoy
    ");
    $activos_hoy = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
    $total_usuarios = $usuarios_activos = $superadmins = $activos_hoy = 0;
}

// Variables para el header dinámico - ajustadas para subcarpeta
$current_page = 'usuarios';
$header_buttons = '
<button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
    <i class="fas fa-plus me-1"></i>
    Nuevo Usuario
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
                        <i class="fas fa-users text-primary me-2"></i>
                        Gestión de Usuarios
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
                                        <h4><?php echo $total_usuarios; ?></h4>
                                        <small>Total Usuarios</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-users fa-2x"></i>
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
                                        <h4><?php echo $usuarios_activos; ?></h4>
                                        <small>Usuarios Activos</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-user-check fa-2x"></i>
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
                                        <h4><?php echo $superadmins; ?></h4>
                                        <small>Super Admins</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-crown fa-2x"></i>
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
                                        <h4><?php echo $activos_hoy; ?></h4>
                                        <small>Activos Hoy</small>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de usuarios -->
                <div class="card shadow">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            Lista de Usuarios del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usuariosTable" class="table table-striped table-hover" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
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

                <!-- Incluir modal para agregar usuario -->
                <?php include 'templates/add_user_modal.php'; ?>

                <!-- Incluir modal para editar usuario -->
                <?php include 'templates/edit_user_modal.php'; ?>

                <!-- Incluir modal para eliminar usuario -->
                <?php include 'templates/delete_user_modal.php'; ?>

                <!-- Incluir modal para ver detalles del usuario -->
                <?php include 'templates/view_user_modal.php'; ?>
            </main>
        </div>
    </div>

    <!-- Estilos personalizados para usuarios -->
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
// ID del usuario actual desde PHP
const currentUserId = ' . $_SESSION['user_id'] . ';

$(document).ready(function() {
    // Inicializar DataTable con datos del servidor
    const table = $("#usuariosTable").DataTable({
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
                    console.error("Error cargando usuarios:", json.error);
                    return [];
                }
            },
            error: function(xhr, error, thrown) {
                console.error("Error AJAX:", error);
                showAlert("Error al cargar los usuarios", "danger");
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
                render: function(data) {
                    return `
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <strong>${data}</strong>
                        </div>
                    `;
                }
            },
            {
                data: "email",
                render: function(data) {
                    return `<i class="fas fa-envelope text-muted me-1"></i>${data}`;
                }
            },
            {
                data: "rol",
                render: function(data) {
                    if (data === "superadmin") {
                        return `<span class="badge bg-warning text-dark">
                                    <i class="fas fa-crown me-1"></i>Super Admin
                                </span>`;
                    } else {
                        return `<span class="badge bg-info">
                                    <i class="fas fa-user me-1"></i>Usuario
                                </span>`;
                    }
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
                    let buttons = `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-info btn-sm btn-view" 
                                    data-id="${data}" title="Ver detalles" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm btn-edit" 
                                    data-id="${data}" title="Editar usuario" data-bs-toggle="tooltip">
                                <i class="fas fa-edit"></i>
                            </button>
                    `;
                    
                    // No mostrar botón de eliminar para el usuario actual
                    if (data != currentUserId) {
                        buttons += `
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete" 
                                    data-id="${data}" title="Eliminar usuario" data-bs-toggle="tooltip">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    }
                    
                    buttons += `</div>`;
                    return buttons;
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
        const userId = $(this).data("id");
        viewUser(userId);
    });
    
    $(document).on("click", ".btn-edit", function() {
        const userId = $(this).data("id");
        editUser(userId);
    });
    
    $(document).on("click", ".btn-delete", function() {
        const userId = $(this).data("id");
        deleteUser(userId);
    });
    
    // Función para ver detalles del usuario
    function viewUser(userId) {
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById("viewUserModal"));
        modal.show();
        
        // Mostrar estado de carga
        $("#userDetailsLoading").show();
        $("#userDetailsContent").hide();
        $("#userDetailsError").hide();
        
        // Cargar datos del usuario via AJAX
        $.ajax({
            url: `controllers/controller.php?action=details&id=${userId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    populateUserDetails(response.data);
                    $("#userDetailsLoading").hide();
                    $("#userDetailsContent").show();
                } else {
                    showUserDetailsError();
                }
            },
            error: function() {
                showUserDetailsError();
            }
        });
    }
    
    // Función para poblar los detalles del usuario en el modal
    function populateUserDetails(user) {
        // Información básica
        $("#userDetailId").text(`#${user.id}`);
        $("#userDetailName").text(user.nombre);
        $("#userDetailFullName").text(user.nombre);
        $("#userDetailEmail").text(user.email);
        $("#userDetailCreatedAt").text(user.created_at_formatted);
        $("#userDetailUpdatedAt").text(user.updated_at_formatted);
        $("#userDetailTimeInSystem").text(user.time_in_system);
        
        // Rol con badge
        if (user.rol === "superadmin") {
            $("#userDetailRole").text("Super Administrador");
            $("#userDetailRoleBadge").html(`
                <span class="badge bg-warning text-dark">
                    <i class="fas fa-crown me-1"></i>Super Administrador
                </span>
            `);
        } else {
            $("#userDetailRole").text("Usuario");
            $("#userDetailRoleBadge").html(`
                <span class="badge bg-info">
                    <i class="fas fa-user me-1"></i>Usuario
                </span>
            `);
        }
        
        // Estado con badge
        if (user.activo) {
            $("#userDetailStatus").removeClass().addClass("badge bg-success").html(`
                <i class="fas fa-check-circle me-1"></i>Activo
            `);
            $("#userDetailStatusBadge").html(`
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i>Activo
                </span>
            `);
        } else {
            $("#userDetailStatus").removeClass().addClass("badge bg-danger").html(`
                <i class="fas fa-times-circle me-1"></i>Inactivo
            `);
            $("#userDetailStatusBadge").html(`
                <span class="badge bg-danger">
                    <i class="fas fa-times-circle me-1"></i>Inactivo
                </span>
            `);
        }
        
        // Configurar botón de editar
        $("#editUserFromModal").off("click").on("click", function() {
            $("#viewUserModal").modal("hide");
            editUser(user.id);
        });
    }
    
    // Función para mostrar error en los detalles
    function showUserDetailsError() {
        $("#userDetailsLoading").hide();
        $("#userDetailsContent").hide();
        $("#userDetailsError").show();
    }
    
    // ========================================
    // FUNCIONALIDAD AGREGAR USUARIO
    // ========================================
    
    // Evento para el botón de guardar nuevo usuario
    $("#saveNewUser").on("click", function() {
        const form = document.getElementById("addUserForm");
        
        // Validar formulario
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const userData = {
            nombre: formData.get("nombre"),
            email: formData.get("email"),
            password: formData.get("password"),
            rol: formData.get("rol"),
            activo: formData.get("activo")
        };
        
        // Mostrar loading
        $("#saveUserSpinner").show();
        $("#saveUserIcon").hide();
        $("#saveNewUser").prop("disabled", true);
        
        // Enviar datos via AJAX
        $.ajax({
            url: "controllers/controller.php?action=create",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(userData),
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#addUserModal").modal("hide");
                    showAlert(response.message, "success");
                    
                    // Recargar la tabla para mostrar el nuevo usuario
                    table.ajax.reload();
                    
                    // Limpiar formulario
                    resetAddUserForm();
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al crear el usuario";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#saveUserSpinner").hide();
                $("#saveUserIcon").show();
                $("#saveNewUser").prop("disabled", false);
            }
        });
    });
    
    // Función para resetear el formulario de agregar usuario
    function resetAddUserForm() {
        const form = document.getElementById("addUserForm");
        form.reset();
        form.classList.remove("was-validated");
        
        // Resetear valores por defecto
        $("#addUserStatus").val("1");
    }
    
    // Resetear formulario cuando se cierra el modal
    $("#addUserModal").on("hidden.bs.modal", function() {
        resetAddUserForm();
    });
    
    // Validación en tiempo real para mejor UX
    $("#addUserEmail").on("blur", function() {
        const email = $(this).val();
        if (email && !isValidEmail(email)) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });
    
    $("#addUserPassword").on("input", function() {
        const password = $(this).val();
        if (password.length > 0 && password.length < 6) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });
    
    // Función auxiliar para validar email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Función para editar usuario
    function editUser(userId) {
        // Mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById("editUserModal"));
        modal.show();
        
        // Mostrar estado de carga
        $("#editUserLoading").show();
        $("#editUserForm").hide();
        $("#editUserError").hide();
        $("#saveEditUser").hide();
        
        // Cargar datos del usuario via AJAX
        $.ajax({
            url: `controllers/controller.php?action=details&id=${userId}`,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $("#editUserLoading").hide();
                    $("#editUserForm").show();
                    $("#saveEditUser").show();
                } else {
                    showEditUserError();
                }
            },
            error: function() {
                showEditUserError();
            }
        });
    }
    
    // Función para poblar el formulario de edición
    function populateEditForm(user) {
        $("#editUserId").val(user.id);
        $("#editUserName").val(user.nombre);
        $("#editUserEmail").val(user.email);
        $("#editUserRole").val(user.rol);
        $("#editUserStatus").val(user.activo ? "1" : "0");
        $("#editUserPassword").val(""); // Siempre vacío
        
        // Limpiar validaciones previas
        $("#editUserForm").removeClass("was-validated");
        $("#editUserForm .form-control").removeClass("is-invalid is-valid");
        $("#editUserForm .form-select").removeClass("is-invalid is-valid");
    }
    
    // Función para mostrar error en la carga de edición
    function showEditUserError() {
        $("#editUserLoading").hide();
        $("#editUserForm").hide();
        $("#editUserError").show();
        $("#saveEditUser").hide();
    }
    
    // Evento para el botón de guardar cambios del usuario
    $("#saveEditUser").on("click", function() {
        const form = document.getElementById("editUserForm");
        
        // Validar formulario (excluyendo password si está vacío)
        const password = $("#editUserPassword").val();
        if (password && password.length < 6) {
            $("#editUserPassword").addClass("is-invalid");
            form.classList.add("was-validated");
            return;
        }
        
        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }
        
        // Obtener datos del formulario
        const formData = new FormData(form);
        const userData = {
            nombre: formData.get("nombre"),
            email: formData.get("email"),
            rol: formData.get("rol"),
            activo: formData.get("activo")
        };
        
        // Solo incluir password si se ingresó una nueva
        if (password) {
            userData.password = password;
        }
        
        const userId = formData.get("id");
        
        // Mostrar loading
        $("#saveEditUserSpinner").show();
        $("#saveEditUserIcon").hide();
        $("#saveEditUser").prop("disabled", true);
        
        // Enviar datos via AJAX
        $.ajax({
            url: `controllers/controller.php?action=update&id=${userId}`,
            type: "PUT",
            contentType: "application/json",
            data: JSON.stringify(userData),
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#editUserModal").modal("hide");
                    showAlert(response.message, "success");
                    
                    // Recargar la tabla para mostrar los cambios
                    table.ajax.reload();
                    
                    // Limpiar formulario
                    resetEditUserForm();
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al actualizar el usuario";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#saveEditUserSpinner").hide();
                $("#saveEditUserIcon").show();
                $("#saveEditUser").prop("disabled", false);
            }
        });
    });
    
    // Función para resetear el formulario de editar usuario
    function resetEditUserForm() {
        const form = document.getElementById("editUserForm");
        form.reset();
        form.classList.remove("was-validated");
        $("#editUserForm .form-control").removeClass("is-invalid is-valid");
        $("#editUserForm .form-select").removeClass("is-invalid is-valid");
    }
    
    // Resetear formulario cuando se cierra el modal de edición
    $("#editUserModal").on("hidden.bs.modal", function() {
        resetEditUserForm();
    });
    
    // Validación en tiempo real para el formulario de edición
    $("#editUserEmail").on("blur", function() {
        const email = $(this).val();
        if (email && !isValidEmail(email)) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });
    
    $("#editUserPassword").on("input", function() {
        const password = $(this).val();
        if (password.length > 0 && password.length < 6) {
            $(this).addClass("is-invalid");
        } else {
            $(this).removeClass("is-invalid");
        }
    });
    
    // Función para eliminar usuario
    function deleteUser(userId) {
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
                            <li>• El usuario y toda su información</li>
                            <li>• Todos sus ingresos registrados</li>
                            <li>• Todos sus gastos y gastos fijos</li>
                            <li>• Sus categorías y métodos de pago personalizados</li>
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
                // Cargar datos del usuario para mostrar en el modal de segunda confirmación
                $.ajax({
                    url: `controllers/controller.php?action=details&id=${userId}`,
                    type: "GET",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            showDeleteUserModal(response.data);
                        } else {
                            showAlert("Error al obtener los datos del usuario", "danger");
                        }
                    },
                    error: function() {
                        showAlert("Error al cargar los datos del usuario", "danger");
                    }
                });
            }
        });
    }
    
    // Función para mostrar el modal de eliminación con los datos del usuario
    function showDeleteUserModal(user) {
        // Poblar datos del usuario en el modal
        $("#deleteUserName").text(user.nombre);
        $("#deleteUserEmail").text(user.email);
        
        // Resetear formulario
        $("#deleteConfirmText").val("");
        $("#confirmDeleteUser").prop("disabled", true);
        $("#confirmTextError").hide();
        
        // Almacenar ID del usuario para usar después
        $("#confirmDeleteUser").data("user-id", user.id);
        $("#confirmDeleteUser").data("user-name", user.nombre);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById("deleteUserModal"));
        modal.show();
    }
    
    // Validación en tiempo real para el campo de confirmación
    $("#deleteConfirmText").on("input", function() {
        const confirmText = $(this).val();
        const isValid = confirmText === "ELIMINAR";
        
        $("#confirmDeleteUser").prop("disabled", !isValid);
        
        if (confirmText.length > 0 && !isValid) {
            $("#confirmTextError").show();
        } else {
            $("#confirmTextError").hide();
        }
    });
    
    // Evento para confirmar eliminación final
    $("#confirmDeleteUser").on("click", function() {
        const userId = $(this).data("user-id");
        const userName = $(this).data("user-name");
        const confirmText = $("#deleteConfirmText").val();
        
        // Validación final
        if (confirmText !== "ELIMINAR") {
            $("#confirmTextError").show();
            return;
        }
        
        // Mostrar loading
        $("#deleteUserSpinner").show();
        $("#deleteUserIcon").hide();
        $("#confirmDeleteUser").prop("disabled", true);
        
        // Enviar petición de eliminación
        $.ajax({
            url: `controllers/controller.php?action=delete&id=${userId}`,
            type: "DELETE",
            success: function(response) {
                if (response.success) {
                    // Éxito - cerrar modal y recargar tabla
                    $("#deleteUserModal").modal("hide");
                    
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
                        title: "Usuario Eliminado",
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
                    
                    // Recargar la tabla para quitar el usuario eliminado
                    table.ajax.reload();
                    
                    // Log de la acción (opcional)
                    console.log(`Usuario ${userName} (ID: ${userId}) eliminado exitosamente`);
                    if (response.deleted_records) {
                        console.log("Registros eliminados:", response.deleted_records);
                    }
                } else {
                    // Error del servidor
                    showAlert(response.error, "danger");
                }
            },
            error: function(xhr) {
                let errorMessage = "Error al eliminar el usuario";
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                showAlert(errorMessage, "danger");
            },
            complete: function() {
                // Ocultar loading
                $("#deleteUserSpinner").hide();
                $("#deleteUserIcon").show();
                $("#confirmDeleteUser").prop("disabled", false);
            }
        });
    });
    
    // Resetear modal cuando se cierra
    $("#deleteUserModal").on("hidden.bs.modal", function() {
        $("#deleteConfirmText").val("");
        $("#confirmDeleteUser").prop("disabled", true);
        $("#confirmTextError").hide();
        $("#deleteUserSpinner").hide();
        $("#deleteUserIcon").show();
    });
    
    // Enfocar automáticamente el campo de confirmación cuando se abre el modal
    $("#deleteUserModal").on("shown.bs.modal", function() {
        $("#deleteConfirmText").focus();
    });
    
    // Inicializar tooltips iniciales
    initializeTooltips();
    
    console.log("✅ Gestión de Usuarios - DataTable con datos reales inicializado correctamente");
});
</script>
';

// Incluir el footer
include '../includes/footer.php';
?>
