<!-- Modal para ver detalles del método de pago -->
<div class="modal fade" id="viewPaymentMethodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Detalles del Método de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading state -->
                <div id="paymentMethodDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando detalles del método de pago...</p>
                </div>

                <!-- Error state -->
                <div id="paymentMethodDetailsError" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> No se pudieron cargar los detalles del método de pago.
                    <br>
                    <small>Por favor, cierra este modal e inténtalo de nuevo.</small>
                </div>

                <!-- Content -->
                <div id="paymentMethodDetailsContent" style="display: none;">
                    <!-- Header con información principal -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div id="paymentMethodColorIndicator" class="rounded-circle me-3" 
                                             style="width: 50px; height: 50px; border: 3px solid white; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-credit-card fa-lg text-white"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0" id="paymentMethodDetailFullName">Cargando...</h4>
                                            <span class="badge bg-white bg-opacity-25 px-3 py-2 fs-6" id="paymentMethodDetailStatusBadge">
                                                <i class="fas fa-circle me-1"></i>Estado
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles en cards -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Información Básica
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <strong>ID:</strong>
                                        </div>
                                        <div class="col-8">
                                            <span id="paymentMethodDetailId" class="badge bg-secondary">Cargando...</span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-4">
                                            <strong>Nombre:</strong>
                                        </div>
                                        <div class="col-8">
                                            <span id="paymentMethodDetailName">Cargando...</span>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="row">
                                        <div class="col-4">
                                            <strong>Estado:</strong>
                                        </div>
                                        <div class="col-8">
                                            <span id="paymentMethodDetailStatus" class="badge">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-palette me-2"></i>
                                        Diseño
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-4">
                                            <strong>Color:</strong>
                                        </div>
                                        <div class="col-8">
                                            <div class="d-flex align-items-center">
                                                <span class="color-indicator me-3" id="paymentMethodColorSample" 
                                                      style="width: 30px; height: 30px; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></span>
                                                <div>
                                                    <div class="fw-bold" id="paymentMethodDetailColor">Cargando...</div>
                                                    <small class="text-muted">Código del color</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cerrar
                </button>
                <button type="button" class="btn btn-warning" id="editPaymentMethodFromModal">
                    <i class="fas fa-edit me-1"></i>
                    Editar Método de Pago
                </button>
            </div>
        </div>
    </div>
</div>
