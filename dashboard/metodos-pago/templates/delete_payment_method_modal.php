<!-- Modal para eliminar método de pago -->
<div class="modal fade" id="deletePaymentMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    SEGUNDA CONFIRMACIÓN - Eliminar Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Información del método de pago a eliminar -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-danger" role="alert">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">¡ACCIÓN IRREVERSIBLE!</h5>
                                    <small>Esta acción NO se puede deshacer</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <strong>Método de pago a eliminar:</strong>
                                <div class="mt-2 p-3 bg-white rounded border">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-credit-card fa-lg me-2 text-danger"></i>
                                        <div>
                                            <strong>Nombre:</strong> <span id="deletePaymentMethodName" class="text-dark">Cargando...</span>
                                            <br>
                                            <strong>Color:</strong> <span id="deletePaymentMethodColor" class="text-muted">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-0">
                                <strong>Se eliminarán permanentemente:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>El método de pago y toda su información</li>
                                    <li>Todos los ingresos que usen este método de pago</li>
                                    <li>Todos los gastos que usen este método de pago</li>
                                    <li>Las referencias en otras tablas del sistema</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campo de confirmación -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0 text-dark">
                                    <i class="fas fa-keyboard me-2"></i>
                                    Confirmación Requerida
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">
                                    Para confirmar que entiendes las consecuencias de esta acción, 
                                    escribe exactamente la palabra <strong class="text-danger">ELIMINAR</strong> en el campo de abajo:
                                </p>
                                
                                <div class="mb-3">
                                    <label for="deleteConfirmText" class="form-label">
                                        <strong>Escribe "ELIMINAR" para confirmar:</strong>
                                    </label>
                                    <input type="text" class="form-control" id="deleteConfirmText" 
                                           placeholder="Escribe ELIMINAR para confirmar" autocomplete="off">
                                    <div id="confirmTextError" class="text-danger mt-2" style="display: none;">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Debes escribir exactamente "ELIMINAR" para confirmar.
                                    </div>
                                </div>
                                
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>
                                        El botón de eliminación se habilitará solo cuando escribas correctamente la palabra de confirmación.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeletePaymentMethod" disabled>
                    <span id="deletePaymentMethodSpinner" class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                    <i id="deletePaymentMethodIcon" class="fas fa-trash me-1"></i>
                    ELIMINAR PERMANENTEMENTE
                </button>
            </div>
        </div>
    </div>
</div>
