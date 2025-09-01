<!-- Modal para editar categoría -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Editar Categoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Estado de carga -->
                <div id="editCategoryLoading" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando datos de la categoría...</p>
                </div>

                <!-- Estado de error -->
                <div id="editCategoryError" class="text-center py-4" style="display: none;">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <p class="mt-2 text-danger">Error al cargar los datos de la categoría.</p>
                    <p class="text-muted">Por favor, inténtalo de nuevo.</p>
                </div>

                <!-- Formulario para editar categoría -->
                <form id="editCategoryForm" novalidate style="display: none;">
                    <input type="hidden" id="editCategoryId" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="editCategoryName" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    Nombre de la Categoría <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="editCategoryName" name="nombre" 
                                       placeholder="Ej: Comida, Transporte, Sueldo..." required>
                                <div class="invalid-feedback">
                                    Por favor ingresa un nombre válido.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="editCategoryType" class="form-label">
                                    <i class="fas fa-exchange-alt me-1"></i>
                                    Tipo <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="editCategoryType" name="tipo" required>
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategoryIcon" class="form-label">
                                    <i class="fas fa-icons me-1"></i>
                                    Icono
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <span id="editCategoryIconPreview">
                                            <i class="fas fa-folder"></i>
                                        </span>
                                    </span>
                                    <input type="text" class="form-control" id="editCategoryIcon" name="icono" 
                                           value="fas fa-folder" readonly>
                                    <button type="button" class="btn btn-outline-primary" onclick="openIconPicker('editCategoryIcon', 'editCategoryIconPreview', document.getElementById('editCategoryIcon').value)">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="form-text">Haz clic en el botón para elegir un icono</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editCategoryStatus" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>
                                    Estado
                                </label>
                                <select class="form-select" id="editCategoryStatus" name="activo">
                                    <option value="1">Activa</option>
                                    <option value="0">Inactiva</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Nota:</strong> 
                                Al cambiar el tipo de categoría, asegúrate de que sea coherente con los registros existentes.
                                Los campos marcados con <span class="text-danger">*</span> son obligatorios.
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
                <button type="button" class="btn btn-warning" id="saveEditCategory" style="display: none;">
                    <span id="saveEditCategorySpinner" class="spinner-border spinner-border-sm me-2" style="display: none;"></span>
                    <i id="saveEditCategoryIcon" class="fas fa-save me-1"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>
