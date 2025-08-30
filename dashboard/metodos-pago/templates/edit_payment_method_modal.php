<!-- Modal para editar método de pago -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Editar Método de Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading state -->
                <div id="editPaymentMethodLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando datos del método de pago...</p>
                </div>

                <!-- Error state -->
                <div id="editPaymentMethodError" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Error al cargar los datos del método de pago. Por favor, inténtalo de nuevo.
                </div>

                <!-- Formulario para editar método de pago -->
                <form id="editPaymentMethodForm" style="display: none;" novalidate>
                    <input type="hidden" id="editPaymentMethodId" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="editPaymentMethodName" class="form-label">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Nombre del Método de Pago <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="editPaymentMethodName" name="nombre" 
                                       placeholder="Ej: Transferencia, Efectivo, Crédito..." required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editPaymentMethodColor" class="form-label">
                                    <i class="fas fa-palette me-1"></i>
                                    Color <span class="text-danger">*</span>
                                </label>
                                <input type="color" class="form-control form-control-color" id="editPaymentMethodColor" name="color" 
                                       title="Selecciona un color">
                                <small class="form-text text-muted">Color para identificar el método de pago</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="editPaymentMethodStatus" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado
                                </label>
                                <select class="form-select" id="editPaymentMethodStatus" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Atención:</strong> 
                                Los cambios afectarán a todas las transacciones futuras que usen este método de pago.
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
                <button type="button" class="btn btn-warning" id="saveEditPaymentMethod" style="display: none;">
                    <span id="saveEditPaymentMethodSpinner" class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                    <i id="saveEditPaymentMethodIcon" class="fas fa-save me-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>
