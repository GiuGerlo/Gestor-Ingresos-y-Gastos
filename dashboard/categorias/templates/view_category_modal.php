<!-- Modal para ver detalles de la categoría -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Detalles de la Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Estado de carga -->
                <div id="categoryDetailsLoading" class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando información de la categoría...</p>
                </div>

                <!-- Estado de error -->
                <div id="categoryDetailsError" class="text-center py-4" style="display: none;">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-danger">Error al cargar la información de la categoría.</p>
                    <p class="text-muted">Por favor, inténtalo de nuevo.</p>
                </div>

                <!-- Contenido de detalles -->
                <div id="categoryDetailsContent" style="display: none;">
                    <!-- Header con información principal -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body text-center text-white p-4">
                                    <div class="d-flex justify-content-center align-items-center mb-3">
                                        <div class="bg-white bg-opacity-20 rounded-circle p-3 me-3">
                                            <i class="fas fa-tag" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="text-start">
                                            <h3 class="mb-1 fw-bold" id="categoryDetailName">Nombre de la Categoría</h3>
                                            <p class="mb-0 opacity-75">
                                                <small>ID: <span id="categoryDetailId" class="badge bg-white bg-opacity-25">#123</span></small>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-center gap-3">
                                        <div id="categoryDetailTypeBadge" class="badge bg-white bg-opacity-25 px-3 py-2 fs-6">
                                            <i class="fas fa-arrow-up me-2"></i>Ingreso
                                        </div>
                                        <div id="categoryDetailStatusBadge" class="badge bg-white bg-opacity-25 px-3 py-2 fs-6">
                                            <i class="fas fa-check-circle me-2"></i>Activa
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
                                <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-tag text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-semibold">Nombre</small>
                                    <div class="fw-bold" id="categoryDetailFullName">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-exchange-alt text-success"></i>
                                </div>
                                <div>
                                    <small class="text-muted fw-semibold">Tipo</small>
                                    <div class="fw-bold" id="categoryDetailType">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="fas fa-toggle-on text-info"></i>
                                </div>
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <div>
                                        <small class="text-muted fw-semibold">Estado</small>
                                    </div>
                                    <div>
                                        <span id="categoryDetailStatus" class="badge bg-success fs-6">
                                            <i class="fas fa-check-circle me-1"></i>Activa
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
                                        <h6 class="fw-bold text-info mb-2">Información sobre esta Categoría</h6>
                                        <p class="mb-0 text-secondary">
                                            Esta categoría puede ser utilizada para clasificar transacciones del tipo correspondiente.
                                            Las categorías <strong>inactivas</strong> no aparecerán en los formularios de registro de nuevas transacciones.
                                        </p>
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
                <button type="button" class="btn btn-warning" id="editCategoryFromModal">
                    <i class="fas fa-edit me-1"></i>
                    Editar Categoría
                </button>
            </div>
        </div>
    </div>
</div>
