<!-- Modal para Eliminar Método de Pago -->
<div class="modal fade" id="deletePaymentMethodModal" tabindex="-1" aria-labelledby="deletePaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deletePaymentMethodModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Eliminar Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="text-danger mb-3">
                        <i class="fas fa-trash-alt" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-danger mb-3">¿Está seguro de que desea eliminar este método de pago?</h5>
                    <div class="alert alert-danger border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle text-danger me-3 fs-4"></i>
                            <div class="text-start">
                                <h6 class="fw-bold text-danger mb-1">Método de Pago a Eliminar:</h6>
                                <p class="mb-0 fs-5 fw-bold" id="deletePaymentMethodName">Nombre del Método</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle text-warning me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold text-warning mb-2">⚠️ Advertencia Importante</h6>
                            <ul class="text-secondary mb-0 small">
                                <li>Esta acción <strong>NO SE PUEDE DESHACER</strong></li>
                                <li>Si el método está siendo usado en transacciones, no se podrá eliminar</li>
                                <li>Se recomienda <strong>desactivar</strong> en lugar de eliminar</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form id="deletePaymentMethodForm">
                    <input type="hidden" id="deletePaymentMethodId" name="id">
                    
                    <div class="mb-4">
                        <label for="deleteConfirmationText" class="form-label fw-semibold text-danger">
                            <i class="fas fa-keyboard text-danger me-1"></i>
                            Para confirmar, escriba exactamente el nombre del método:
                        </label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg border-danger" 
                            id="deleteConfirmationText" 
                            name="confirmacion"
                            placeholder="Escriba el nombre del método aquí..."
                            autocomplete="off"
                            required
                        >
                        <div class="invalid-feedback">
                            El texto no coincide con el nombre del método de pago.
                        </div>
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle text-info me-1"></i>
                            Esto es una medida de seguridad para evitar eliminaciones accidentales
                        </div>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-lightbulb text-info me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-info mb-2">💡 Alternativa Recomendada</h6>
                                <p class="mb-2 text-secondary">En lugar de eliminar, considere <strong>desactivar</strong> el método de pago.</p>
                                <p class="mb-0 small text-muted">
                                    Esto mantendrá el historial de transacciones y podrá reactivarlo cuando sea necesario.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                </button>
                <button type="submit" form="deletePaymentMethodForm" class="btn btn-danger" disabled>
                    <i class="fas fa-trash me-1"></i>
                    Eliminar Permanentemente
                </button>
            </div>
        </div>
    </div>
</div>
