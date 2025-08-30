<!-- Modal para Editar Método de Pago -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="editPaymentMethodModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    Editar Método de Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Loading state -->
                <div id="editPaymentMethodLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando información del método de pago...</p>
                </div>

                <!-- Formulario de edición -->
                <form id="editPaymentMethodForm" novalidate>
                    <input type="hidden" id="editPaymentMethodId" name="id">
                    
                    <div class="row g-3">
                        <!-- Nombre del método -->
                        <div class="col-12">
                            <label for="editPaymentMethodName" class="form-label fw-semibold">
                                <i class="fas fa-credit-card text-warning me-1"></i>
                                Nombre del Método *
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="editPaymentMethodName" 
                                name="nombre" 
                                placeholder="Ej: Tarjeta de Crédito, Efectivo, etc."
                                maxlength="50"
                                required
                            >
                            <div class="invalid-feedback">
                                Por favor, ingrese un nombre válido para el método de pago.
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                Máximo 50 caracteres
                            </div>
                        </div>

                        <!-- Color del método -->
                        <div class="col-12">
                            <label for="editPaymentMethodColor" class="form-label fw-semibold">
                                <i class="fas fa-palette text-warning me-1"></i>
                                Color Identificatorio *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text p-0" style="width: 50px;">
                                    <div id="editColorPreview" style="width: 100%; height: 38px; background-color: #6548D5; border-radius: 4px;"></div>
                                </span>
                                <input 
                                    type="color" 
                                    class="form-control form-control-color form-control-lg" 
                                    id="editPaymentMethodColor" 
                                    name="color" 
                                    value="#6548D5"
                                    title="Seleccionar color"
                                    required
                                    style="padding: 4px;"
                                >
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg" 
                                    id="editPaymentMethodColorHex" 
                                    placeholder="#6548D5"
                                    maxlength="7"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                >
                            </div>
                            <div class="invalid-feedback">
                                Por favor, seleccione un color válido.
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                Este color ayudará a identificar visualmente el método de pago en reportes
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-12">
                            <label for="editPaymentMethodStatus" class="form-label fw-semibold">
                                <i class="fas fa-toggle-on text-success me-1"></i>
                                Estado del Método
                            </label>
                            <select class="form-select form-select-lg" id="editPaymentMethodStatus" name="activo" required>
                                <option value="1">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Activo
                                </option>
                                <option value="0">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Inactivo
                                </option>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                Los métodos inactivos no aparecerán en los formularios de registro de transacciones
                            </div>
                        </div>

                        <!-- Vista previa -->
                        <div class="col-12">
                            <div class="alert alert-light border">
                                <h6 class="fw-bold text-secondary mb-2">
                                    <i class="fas fa-eye me-2"></i>
                                    Vista Previa
                                </h6>
                                <div class="d-flex align-items-center">
                                    <div id="editPreviewColorBox" style="width: 20px; height: 20px; background-color: #6548D5; border-radius: 4px; border: 1px solid #dee2e6;" class="me-3"></div>
                                    <span id="editPreviewMethodName" class="fw-semibold text-dark">Nombre del método</span>
                                    <span id="editPreviewMethodStatus" class="badge bg-success ms-2">Activo</span>
                                </div>
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
                <button type="submit" form="editPaymentMethodForm" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>
                    Actualizar Método
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Sincronizar color picker con input de texto (editar)
$(document).on('input', '#editPaymentMethodColor', function() {
    const color = $(this).val();
    $('#editPaymentMethodColorHex').val(color);
    $('#editColorPreview').css('background-color', color);
    $('#editPreviewColorBox').css('background-color', color);
});

$(document).on('input', '#editPaymentMethodColorHex', function() {
    const color = $(this).val();
    if (/^#[0-9A-Fa-f]{6}$/.test(color)) {
        $('#editPaymentMethodColor').val(color);
        $('#editColorPreview').css('background-color', color);
        $('#editPreviewColorBox').css('background-color', color);
    }
});

// Actualizar vista previa del nombre (editar)
$(document).on('input', '#editPaymentMethodName', function() {
    const name = $(this).val() || 'Nombre del método';
    $('#editPreviewMethodName').text(name);
});

// Actualizar vista previa del estado (editar)
$(document).on('change', '#editPaymentMethodStatus', function() {
    const status = $(this).val();
    const statusBadge = $('#editPreviewMethodStatus');
    
    if (status === '1') {
        statusBadge.removeClass('bg-danger').addClass('bg-success').text('Activo');
    } else {
        statusBadge.removeClass('bg-success').addClass('bg-danger').text('Inactivo');
    }
});
</script>
