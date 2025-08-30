<!-- Modal de confirmación para eliminar usuario -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-times text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="mb-3">¿Estás seguro de eliminar este usuario?</h5>
                    
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Usuario a eliminar:</strong>
                        <br>
                        <span id="deleteUserName" class="fw-bold">-</span>
                        <br>
                        <small id="deleteUserEmail" class="text-muted">-</small>
                    </div>
                    
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡ELIMINACIÓN PERMANENTE!</strong> 
                        <br>
                        Esta acción eliminará:
                        <ul class="list-unstyled mt-2 mb-0">
                            <li>✗ El usuario y toda su información personal</li>
                            <li>✗ Todos sus ingresos y transacciones</li>
                            <li>✗ Todos sus gastos y gastos fijos</li>
                            <li>✗ Sus categorías y métodos de pago personalizados</li>
                        </ul>
                        <strong>Esta acción NO se puede revertir.</strong>
                    </div>
                    
                    <!-- Campo de confirmación -->
                    <div class="mb-3">
                        <label for="deleteConfirmText" class="form-label">
                            Para confirmar, escribe <strong>"ELIMINAR"</strong> en el campo:
                        </label>
                        <input type="text" class="form-control text-center" id="deleteConfirmText" 
                               placeholder="Escribe ELIMINAR para confirmar" autocomplete="off">
                        <div class="form-text text-danger" id="confirmTextError" style="display: none;">
                            Debes escribir "ELIMINAR" exactamente para confirmar.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUser" disabled>
                    <span class="spinner-border spinner-border-sm me-2" style="display: none;" id="deleteUserSpinner"></span>
                    <i class="fas fa-trash me-1" id="deleteUserIcon"></i>
                    Eliminar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<style>
#deleteUserModal .modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(220, 53, 69, 0.3);
}

#deleteUserModal .alert {
    border: none;
    border-radius: 0.5rem;
}

#deleteUserModal .btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#deleteUserModal .fas.fa-user-times {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

#deleteUserModal .modal-dialog {
    max-width: 500px;
}
</style>
