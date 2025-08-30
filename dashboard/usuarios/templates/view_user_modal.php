<!-- Modal para ver detalles del usuario -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-circle me-2"></i>
                    Detalles del Usuario
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading state -->
                <div id="userDetailsLoading" class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando información del usuario...</p>
                </div>

                <!-- User details content -->
                <div id="userDetailsContent" style="display: none;">
                    <div class="row">
                        <!-- Avatar y información básica -->
                        <div class="col-md-4 text-center">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="avatar-lg bg-info rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-user text-white fa-2x"></i>
                                    </div>
                                    <h5 id="userDetailName" class="card-title mb-1">-</h5>
                                    <p id="userDetailRole" class="text-muted mb-2">-</p>
                                    <span id="userDetailStatus" class="badge">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Información detallada -->
                        <div class="col-md-8">
                            <div class="card border-0">
                                <div class="card-body">
                                    <h6 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-info-circle text-info me-2"></i>
                                        Información Personal
                                    </h6>
                                    
                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-bold text-muted">ID Usuario:</div>
                                        <div class="col-sm-8">
                                            <span id="userDetailId" class="badge bg-secondary">#-</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-bold text-muted">Nombre Completo:</div>
                                        <div class="col-sm-8" id="userDetailFullName">-</div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-bold text-muted">Email:</div>
                                        <div class="col-sm-8">
                                            <i class="fas fa-envelope text-muted me-1"></i>
                                            <span id="userDetailEmail">-</span>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-bold text-muted">Rol del Sistema:</div>
                                        <div class="col-sm-8" id="userDetailRoleBadge">-</div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-4 fw-bold text-muted">Estado:</div>
                                        <div class="col-sm-8" id="userDetailStatusBadge">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de fechas -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        Información de Registro
                                    </h6>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar-plus text-success me-2"></i>
                                                <strong class="me-2">Fecha de Registro:</strong>
                                                <span id="userDetailCreatedAt">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-calendar-edit text-warning me-2"></i>
                                                <strong class="me-2">Última Actualización:</strong>
                                                <span id="userDetailUpdatedAt">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-info mb-0" role="alert">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Tiempo en el sistema:</strong> 
                                                <span id="userDetailTimeInSystem">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error state -->
                <div id="userDetailsError" style="display: none;">
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error al cargar:</strong> No se pudieron obtener los detalles del usuario.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-warning" id="editUserFromModal">
                    <i class="fas fa-edit me-1"></i>Editar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-lg {
    width: 80px;
    height: 80px;
    font-size: 24px;
}

#viewUserModal .modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

#viewUserModal .card {
    transition: transform 0.2s;
}

#viewUserModal .card:hover {
    transform: translateY(-2px);
}

#viewUserModal .row.mb-3:hover {
    background-color: rgba(0, 123, 255, 0.05);
    border-radius: 0.375rem;
    padding: 0.25rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
