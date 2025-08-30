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

                <!-- Modal para agregar usuario -->
                <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Nuevo Usuario
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Formulario para crear nuevos usuarios se implementará aquí.
                                </p>
                                <form id="addUserForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre Completo</label>
                                                <input type="text" class="form-control" placeholder="Nombre del usuario">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" placeholder="email@ejemplo.com">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Contraseña</label>
                                                <input type="password" class="form-control" placeholder="Contraseña segura">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Rol</label>
                                                <select class="form-select">
                                                    <option value="usuario">Usuario</option>
                                                    <option value="superadmin">Super Administrador</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </button>
                                <button type="button" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Crear Usuario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

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
    
    // Función para editar usuario
    function editUser(userId) {
        showAlert(`Editar usuario #${userId}`, "warning");
    }
    
    // Función para eliminar usuario
    function deleteUser(userId) {
        if (confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
            showAlert(`Eliminar usuario #${userId}`, "danger");
        }
    }
    
    // Efectos hover para las cards de estadísticas
    $(".card").hover(
        function() {
            $(this).addClass("shadow-lg").css("transform", "translateY(-2px)");
        },
        function() {
            $(this).removeClass("shadow-lg").css("transform", "translateY(0)");
        }
    );
    
    // Inicializar tooltips iniciales
    initializeTooltips();
    
    console.log("✅ Gestión de Usuarios - DataTable con datos reales inicializado correctamente");
});
</script>
';

// Incluir el footer
include '../includes/footer.php';
?>
