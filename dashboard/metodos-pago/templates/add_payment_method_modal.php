<!-- Modal para agregar nuevo método de pago -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Nuevo Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para crear método de pago -->
                <form id="addPaymentMethodForm" novalidate>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="addPaymentMethodName" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Nombre del Método de Pago <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="addPaymentMethodName" name="nombre" 
                                       placeholder="Ej: Transferencia, Efectivo, Crédito..." required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="addPaymentMethodColor" class="form-label">
                                    <i class="fas fa-palette me-1"></i>
                                    Color <span class="text-danger">*</span>
                                </label>
                                <input type="color" class="form-control form-control-color" id="addPaymentMethodColor" name="color" 
                                       value="#6548D5" title="Selecciona un color">
                                <small class="form-text text-muted">Color para identificar el método de pago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="addPaymentMethodStatus" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado Inicial
                                </label>
                                <select class="form-select" id="addPaymentMethodStatus" name="activo">
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
                                El color será usado para identificar visualmente este método de pago en las transacciones.
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
                <button type="button" class="btn btn-success" id="saveNewPaymentMethod">
                    <span id="savePaymentMethodSpinner" class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                    <i id="savePaymentMethodIcon" class="fas fa-save me-1"></i>
                    Guardar Método de Pago
                </button>
            </div>
        </div>
    </div>
</div>
