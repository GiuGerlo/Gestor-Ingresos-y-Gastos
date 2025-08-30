<!-- Modal para eliminar categoría -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    CONFIRMACIÓN FINAL DE ELIMINACIÓN
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-skull-crossbones me-2"></i>
                    <strong>⚠️ ADVERTENCIA CRÍTICA ⚠️</strong>
                    <br>
                    Esta es tu ÚLTIMA OPORTUNIDAD para cancelar la eliminación.
                </div>

                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-tag me-2"></i>
                            Categoría que será ELIMINADA PERMANENTEMENTE
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p><strong>Nombre:</strong> <span id="deleteCategoryName" class="text-danger fw-bold">-</span></p>
                                <p><strong>Tipo:</strong> <span id="deleteCategoryType" class="text-danger fw-bold">-</span></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-tag text-danger" style="font-size: 4rem; opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mt-3" role="alert">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>QUÉ SE ELIMINARÁ:</h6>
                    <ul class="mb-0">
                        <li>La categoría y toda su información</li>
                        <li>Todos los ingresos/gastos asociados a esta categoría</li>
                        <li>Todas las referencias en otras tablas del sistema</li>
                    </ul>
                    <hr>
                    <p class="mb-0 fw-bold text-danger">
                        ⚠️ ESTA ACCIÓN NO SE PUEDE DESHACER ⚠️
                    </p>
                </div>

                <div class="alert alert-info" role="alert">
                    <h6><i class="fas fa-keyboard me-2"></i>CONFIRMACIÓN REQUERIDA:</h6>
                    <p class="mb-2">
                        Para confirmar la eliminación, escribe exactamente: 
                        <code class="bg-danger text-white px-2 py-1">ELIMINAR</code>
                    </p>
                    
                    <div class="mb-3">
                        <label for="deleteConfirmText" class="form-label">
                            <i class="fas fa-pencil-alt me-1"></i>
                            Escribe "ELIMINAR" para confirmar:
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg text-center fw-bold" 
                               id="deleteConfirmText" 
                               placeholder="Escribe: ELIMINAR"
                               autocomplete="off"
                               style="letter-spacing: 2px;">
                    </div>
                    
                    <div id="confirmTextError" class="alert alert-danger py-2" style="display: none;">
                        <small>
                            <i class="fas fa-times-circle me-1"></i>
                            Debes escribir exactamente "ELIMINAR" (sin comillas, en mayúsculas)
                        </small>
                    </div>
                </div>

                <div class="alert alert-secondary" role="alert">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Alternativa:</strong> Si solo quieres que la categoría no aparezca en los formularios, 
                        puedes <strong>desactivarla</strong> en lugar de eliminarla.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-lg me-auto" data-bs-dismiss="modal">
                    <i class="fas fa-shield-alt me-2"></i>
                    CANCELAR (Mantener Seguro)
                </button>
                <button type="button" class="btn btn-danger btn-lg" id="confirmDeleteCategory" disabled>
                    <span id="deleteCategorySpinner" class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                    <i id="deleteCategoryIcon" class="fas fa-trash-alt me-2"></i>
                    ELIMINAR PERMANENTEMENTE
                </button>
            </div>
        </div>
    </div>
</div>
