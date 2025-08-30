<!-- Modal para editar usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit me-2"></i>
                    Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading state -->
                <div id="editUserLoading" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando información del usuario...</p>
                </div>

                <!-- Formulario para editar usuario -->
                <form id="editUserForm" novalidate style="display: none;">
                    <input type="hidden" id="editUserId" name="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editUserName" class="form-label">
                                    <i class="fas fa-user me-1"></i>
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="editUserName" name="nombre" 
                                       placeholder="Ingresa el nombre completo" required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editUserEmail" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="editUserEmail" name="email" 
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
                                <label for="editUserRole" class="form-label">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Rol del Usuario <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="editUserRole" name="rol" required>
                                    <option value="">Selecciona un rol</option>
                                    <option value="usuario">Usuario</option>
                                    <option value="superadmin">Super Administrador</option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona un rol.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editUserStatus" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado del Usuario
                                </label>
                                <select class="form-select" id="editUserStatus" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="editUserPassword" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Nueva Contraseña <small class="text-muted">(Dejar vacío para mantener la actual)</small>
                                </label>
                                <input type="password" class="form-control" id="editUserPassword" name="password" 
                                       placeholder="Mínimo 6 caracteres (opcional)" minlength="6">
                                <div class="invalid-feedback">
                                    La contraseña debe tener al menos 6 caracteres.
                                </div>
                                <small class="form-text text-muted">
                                    Solo ingresa una contraseña si deseas cambiarla.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Atención:</strong> 
                                Los cambios en el rol y estado del usuario tendrán efecto inmediato. 
                                Si cambias la contraseña, el usuario deberá usar la nueva en su próximo acceso.
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Error state -->
                <div id="editUserError" style="display: none;">
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error al cargar:</strong> No se pudieron obtener los datos del usuario para editar.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="saveEditUser" style="display: none;">
                    <span class="spinner-border spinner-border-sm me-2" style="display: none;" id="saveEditUserSpinner"></span>
                    <i class="fas fa-save me-1" id="saveEditUserIcon"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#editUserModal .form-control:focus,
#editUserModal .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

#editUserModal .was-validated .form-control:valid,
#editUserModal .was-validated .form-select:valid {
    border-color: #198754;
}

#editUserModal .was-validated .form-control:invalid,
#editUserModal .was-validated .form-select:invalid {
    border-color: #dc3545;
}

#editUserModal .alert {
    border: none;
    border-radius: 0.5rem;
}

#editUserModal .modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

#editUserModal .modal-header {
    border-bottom: 2px solid #ffc107;
}
</style>
