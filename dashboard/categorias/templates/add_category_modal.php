<!-- Modal para agregar nueva categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Nueva Categoría
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para crear categoría -->
                <form id="addCategoryForm" novalidate>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="addCategoryName" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    Nombre de la Categoría <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="addCategoryName" name="nombre" 
                                       placeholder="Ej: Comida, Transporte, Sueldo..." required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="addCategoryType" class="form-label">
                                    <i class="fas fa-exchange-alt me-1"></i>
                                    Tipo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="addCategoryType" name="tipo" required>
                                    <option value="">Selecciona un tipo</option>
                                    <option value="ingreso">
                                        <i class="fas fa-arrow-up"></i> Ingreso
                                    </option>
                                    <option value="gasto">
                                        <i class="fas fa-arrow-down"></i> Gasto
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor selecciona un tipo.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="addCategoryStatus" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado Inicial
                                </label>
                                <select class="form-select" id="addCategoryStatus" name="activo">
                                    <option value="1">Activa</option>
                                    <option value="0">Inactiva</option>
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
                                Las categorías pueden ser de tipo <strong>Ingreso</strong> o <strong>Gasto</strong>.
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
                <button type="button" class="btn btn-success" id="saveNewCategory">
                    <span id="saveCategorySpinner" class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                    <i id="saveCategoryIcon" class="fas fa-save me-1"></i>
                    Guardar Categoría
                </button>
            </div>
        </div>
    </div>
</div>
