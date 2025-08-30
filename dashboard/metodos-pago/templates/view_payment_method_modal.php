<!-- Modal para Ver Detalles del Método de Pago -->
<div class="modal fade" id="viewPaymentMethodModal" tabindex="-1" aria-labelledby="viewPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title fw-bold" id="viewPaymentMethodModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    Detalles del Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Loading state -->
                <div id="paymentMethodDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Cargando detalles del método de pago...</p>
                </div>

                <!-- Error state -->
                <div id="paymentMethodDetailsError" class="text-center py-5" style="display: none;">
                    <div class="text-danger mb-3">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-danger mb-2">Error al cargar los datos</h5>
                    <p class="text-muted">No se pudieron obtener los detalles del método de pago.</p>
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cerrar
                    </button>
                </div>

                <!-- Contenido de detalles -->
                <div id="paymentMethodDetailsContent" style="display: none;">
                    <!-- Header con información principal -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                                <div class="card-body text-center text-white p-4">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="fas fa-credit-card" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-1 fw-bold" id="paymentMethodDetailName">Nombre del Método</h3>
                                            <p class="mb-0 opacity-75">
                                                <small>ID: <span id="paymentMethodDetailId" class="badge bg-white bg-opacity-25">#123</span></small>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <div class="d-flex align-items-center badge bg-white bg-opacity-25 px-3 py-2 fs-6">
                                            <div id="paymentMethodDetailColorBadge" style="width: 16px; height: 16px; background-color: #6548D5; border-radius: 3px;" class="me-2"></div>
                                            <span id="paymentMethodDetailColorCode">#6548D5</span>
                                        </div>
                                        <div id="paymentMethodDetailStatusBadge" class="badge bg-white bg-opacity-25 px-3 py-2 fs-6">
                                            <i class="fas fa-check-circle me-2"></i>Activo
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información simplificada -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-credit-card text-info"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-semibold">Nombre</small>
                                    <div class="fw-bold" id="paymentMethodDetailFullName">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-palette text-warning"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-semibold">Color</small>
                                    <div class="d-flex align-items-center">
                                        <div id="paymentMethodDetailColorPreview" style="width: 20px; height: 20px; background-color: #6548D5; border-radius: 4px; border: 1px solid #dee2e6;" class="me-2"></div>
                                        <code id="paymentMethodDetailColor" class="fw-bold">#6548D5</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-toggle-on text-success"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <div>
                                        <small class="text-muted fw-semibold">Estado</small>
                                    </div>
                                    <div>
                                        <span id="paymentMethodDetailStatus" class="badge bg-success fs-6">
                                            <i class="fas fa-check-circle me-1"></i>Activo
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info border-0 shadow-sm" style="background: linear-gradient(45deg, #e3f2fd, #f3e5f5);">
                                <div class="d-flex align-items-start">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3 mt-1">
                                        <i class="fas fa-lightbulb text-info"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-info mb-2">Información sobre este Método de Pago</h6>
                                        <p class="mb-0 text-secondary">
                                            Este método de pago puede ser utilizado para clasificar transacciones de ingresos y gastos.
                                            Los métodos <strong>inactivos</strong> no aparecerán en los formularios de registro de nuevas transacciones.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-warning" id="editPaymentMethodFromModal">
                    <i class="fas fa-edit me-1"></i>
                    Editar Método
                </button>
            </div>
        </div>
    </div>
</div>
