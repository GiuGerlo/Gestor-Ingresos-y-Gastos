<!-- Modal para Agregar Método de Pago -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addPaymentMethodModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    Agregar Nuevo Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addPaymentMethodForm" novalidate>
                    <div class="row g-3">
                        <!-- Nombre del método -->
                        <div class="col-12">
                            <label for="addPaymentMethodName" class="form-label fw-semibold">
                                <i class="fas fa-credit-card text-primary me-1"></i>
                                Nombre del Método *
                            </label>
                            <input 
                                type="text" 
                                class="form-control form-control-lg" 
                                id="addPaymentMethodName" 
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
                            <label for="addPaymentMethodColor" class="form-label fw-semibold">
                                <i class="fas fa-palette text-warning me-1"></i>
                                Color Identificatorio *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text p-0" style="width: 50px;">
                                    <div id="addColorPreview" style="width: 100%; height: 38px; background-color: #6548D5; border-radius: 4px;"></div>
                                </span>
                                <input 
                                    type="color" 
                                    class="form-control form-control-color form-control-lg" 
                                    id="addPaymentMethodColor" 
                                    name="color" 
                                    value="#6548D5"
                                    title="Seleccionar color"
                                    required
                                    style="padding: 4px;"
                                >
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg" 
                                    id="addPaymentMethodColorHex" 
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

                        <!-- Vista previa -->
                        <div class="col-12">
                            <div class="alert alert-light border">
                                <h6 class="fw-bold text-secondary mb-2">
                                    <i class="fas fa-eye me-2"></i>
                                    Vista Previa
                                </h6>
                                <div class="d-flex align-items-center">
                                    <div id="previewColorBox" style="width: 20px; height: 20px; background-color: #6548D5; border-radius: 4px; border: 1px solid #dee2e6;" class="me-3"></div>
                                    <span id="previewMethodName" class="fw-semibold text-dark">Nombre del método</span>
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
                <button type="submit" form="addPaymentMethodForm" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Crear Método de Pago
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Sincronizar color picker con input de texto
$(document).on('input', '#addPaymentMethodColor', function() {
    const color = $(this).val();
    $('#addPaymentMethodColorHex').val(color);
    $('#addColorPreview').css('background-color', color);
    $('#previewColorBox').css('background-color', color);
});

$(document).on('input', '#addPaymentMethodColorHex', function() {
    const color = $(this).val();
    if (/^#[0-9A-Fa-f]{6}$/.test(color)) {
        $('#addPaymentMethodColor').val(color);
        $('#addColorPreview').css('background-color', color);
        $('#previewColorBox').css('background-color', color);
    }
});

// Actualizar vista previa del nombre
$(document).on('input', '#addPaymentMethodName', function() {
    const name = $(this).val() || 'Nombre del método';
    $('#previewMethodName').text(name);
});

// Resetear vista previa al abrir modal
$('#addPaymentMethodModal').on('show.bs.modal', function() {
    $('#addPaymentMethodColor').val('#6548D5');
    $('#addPaymentMethodColorHex').val('#6548D5');
    $('#addColorPreview').css('background-color', '#6548D5');
    $('#previewColorBox').css('background-color', '#6548D5');
    $('#previewMethodName').text('Nombre del método');
});
</script>
