<!-- Modal para agregar nuevo usuario -->
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
                <!-- Formulario para crear usuario -->
                <form id="addUserForm" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addUserName" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="addUserName" name="nombre" 
                                       placeholder="Ingresa el nombre completo" required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addUserEmail" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="addUserEmail" name="email" 
                                       placeholder="usuario@ejemplo.com" required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un email válido.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addUserPassword" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control" id="addUserPassword" name="password" 
                                       placeholder="Mínimo 6 caracteres" required minlength="6">
                                <div class="invalid-feedback">
                                    La contraseña debe tener al menos 6 caracteres.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="addUserRole" class="form-label">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Rol del Usuario <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="addUserRole" name="rol" required>
                                    <option value="">Selecciona un rol</option>
                                    <option value="usuario">Usuario</option>
                                    <option value="superadmin">Super Administrador</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona un rol.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="addUserStatus" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado Inicial
                                </label>
                                <select class="form-select" id="addUserStatus" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Información:</strong> 
                                Los campos marcados con <span class="text-danger">*</span> son obligatorios. 
                                El usuario podrá cambiar su contraseña después del primer acceso.
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-success" id="saveNewUser">
                    <span class="spinner-border spinner-border-sm me-2" style="display: none;" id="saveUserSpinner"></span>
                    <i class="fas fa-save me-1" id="saveUserIcon"></i>
                    Crear Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#addUserModal .form-control:focus,
#addUserModal .form-select:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

#addUserModal .was-validated .form-control:valid,
#addUserModal .was-validated .form-select:valid {
    border-color: #198754;
}

#addUserModal .was-validated .form-control:invalid,
#addUserModal .was-validated .form-select:invalid {
    border-color: #dc3545;
}

#addUserModal .alert {
    border: none;
    border-radius: 0.5rem;
}

#addUserModal .form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

#addUserModal .modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
</style>
