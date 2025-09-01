<?php
/**
 * COMPONENTE: Selector de Iconos Font Awesome
 * ==========================================
 * Componente reutilizable para seleccionar iconos Font Awesome
 */
?>

<!-- Modal para seleccionar icono -->
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-labelledby="iconPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="iconPickerModalLabel">
                    <i class="fas fa-icons me-2"></i>
                    Seleccionar Icono
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Buscador de iconos -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="iconSearchInput" placeholder="Buscar icono...">
                            <label for="iconSearchInput">
                                <i class="fas fa-search me-2"></i>Buscar icono
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="iconCategoryFilter" style="height: 58px;">
                            <option value="">Todas las categorías</option>
                            <option value="general">General</option>
                            <option value="finanzas">Finanzas</option>
                            <option value="hogar">Hogar</option>
                            <option value="transporte">Transporte</option>
                            <option value="entretenimiento">Entretenimiento</option>
                            <option value="salud">Salud</option>
                            <option value="trabajo">Trabajo</option>
                        </select>
                    </div>
                </div>

                <!-- Grid de iconos -->
                <div class="row" id="iconGrid">
                    <!-- Los iconos se generarán dinámicamente -->
                </div>

                <!-- Mensaje cuando no hay resultados -->
                <div id="noIconsMessage" class="text-center py-5 d-none">
                    <i class="fas fa-search-minus fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron iconos con esos criterios</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <span class="text-muted">Icono seleccionado:</span>
                        <span id="selectedIconPreview" class="ms-2">
                            <i class="fas fa-folder"></i>
                            <code>fas fa-folder</code>
                        </span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmIconSelection">
                            <i class="fas fa-check me-2"></i>Seleccionar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-item {
    cursor: pointer;
    transition: all 0.2s ease;
    border: 2px solid transparent;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    margin-bottom: 15px;
}

.icon-item:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    transform: translateY(-2px);
}

.icon-item.selected {
    background-color: #e7f3ff;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.icon-item i {
    font-size: 2rem;
    color: #6c757d;
    display: block;
    margin-bottom: 8px;
}

.icon-item.selected i {
    color: #007bff;
}

.icon-item small {
    font-size: 0.75rem;
    color: #6c757d;
    word-break: break-all;
}

.icon-item.selected small {
    color: #007bff;
    font-weight: 600;
}

#selectedIconPreview i {
    font-size: 1.2rem;
    margin-right: 8px;
}

#selectedIconPreview code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.875rem;
}
</style>

<script>
// Definir iconos disponibles organizados por categorías
const availableIcons = {
    general: [
        'fas fa-folder', 'fas fa-file', 'fas fa-star', 'fas fa-heart', 'fas fa-bookmark',
        'fas fa-tag', 'fas fa-tags', 'fas fa-flag', 'fas fa-bell', 'fas fa-check-circle',
        'fas fa-times-circle', 'fas fa-info-circle', 'fas fa-exclamation-triangle',
        'fas fa-question-circle', 'fas fa-plus-circle', 'fas fa-minus-circle',
        'fas fa-arrow-up', 'fas fa-arrow-down', 'fas fa-arrow-left', 'fas fa-arrow-right', 'fa-solid fa-qrcode'
    ],
    finanzas: [
        'fas fa-money-bill-wave', 'fas fa-coins', 'fas fa-dollar-sign', 'fas fa-euro-sign',
        'fas fa-credit-card', 'fas fa-university', 'fas fa-piggy-bank', 'fas fa-wallet',
        'fas fa-chart-line', 'fas fa-chart-bar', 'fas fa-chart-pie', 'fas fa-percentage',
        'fas fa-calculator', 'fas fa-receipt', 'fas fa-hand-holding-usd', 'fas fa-donate',
        'fas fa-exchange-alt', 'fas fa-balance-scale', 'fas fa-vault', 'fas fa-gem', 'fa-solid fa-handshake', 'fa-solid fa-money-bill-transfer'
    ],
    hogar: [
        'fas fa-home', 'fas fa-bed', 'fas fa-couch', 'fas fa-bath', 'fas fa-shower',
        'fas fa-utensils', 'fas fa-blender', 'fas fa-coffee', 'fas fa-wine-glass',
        'fas fa-pizza-slice', 'fas fa-hamburger', 'fas fa-apple-alt', 'fas fa-bread-slice',
        'fas fa-lightbulb', 'fas fa-plug', 'fas fa-tv', 'fas fa-washing-machine',
        'fas fa-tools', 'fas fa-hammer', 'fas fa-wrench', 'fas fa-key', 'fa-solid fa-cart-shopping'
    ],
    transporte: [
        'fas fa-car', 'fas fa-bus', 'fas fa-train', 'fas fa-plane', 'fas fa-ship',
        'fas fa-bicycle', 'fas fa-motorcycle', 'fas fa-taxi', 'fas fa-subway',
        'fas fa-gas-pump', 'fas fa-parking', 'fas fa-road', 'fas fa-map-marker-alt',
        'fas fa-route', 'fas fa-traffic-light', 'fas fa-truck', 'fas fa-trailer'
    ],
    entretenimiento: [
        'fas fa-gamepad', 'fas fa-film', 'fas fa-music', 'fas fa-headphones',
        'fas fa-camera', 'fas fa-video', 'fas fa-play-circle', 'fas fa-theater-masks',
        'fas fa-ticket-alt', 'fas fa-bowling-ball', 'fas fa-golf-ball', 'fas fa-futbol',
        'fas fa-basketball-ball', 'fas fa-dumbbell', 'fas fa-running', 'fas fa-swimmer',
        'fas fa-skiing', 'fas fa-skating', 'fas fa-chess', 'fas fa-dice', 'fa-solid fa-shirt', 'fa-solid fa-mobile-screen'
    ],
    salud: [
        'fas fa-heartbeat', 'fas fa-stethoscope', 'fas fa-pills', 'fas fa-syringe',
        'fas fa-thermometer', 'fas fa-band-aid', 'fas fa-first-aid', 'fas fa-hospital',
        'fas fa-ambulance', 'fas fa-user-md', 'fas fa-tooth', 'fas fa-eye',
        'fas fa-brain', 'fas fa-lungs', 'fas fa-dna', 'fas fa-microscope',
        'fas fa-x-ray', 'fas fa-wheelchair', 'fas fa-hand-holding-heart'
    ],
    trabajo: [
        'fas fa-briefcase', 'fas fa-laptop', 'fas fa-desktop', 'fas fa-mobile-alt',
        'fas fa-phone', 'fas fa-fax', 'fas fa-envelope', 'fas fa-pen', 'fas fa-pencil-alt',
        'fas fa-marker', 'fas fa-highlighter', 'fas fa-paperclip', 'fas fa-stapler',
        'fas fa-folder-open', 'fas fa-archive', 'fas fa-clipboard', 'fas fa-calendar',
        'fas fa-clock', 'fas fa-user-tie', 'fas fa-building', 'fas fa-industry'
    ]
};

let iconPicker = {
    currentTargetInput: null,
    currentTargetPreview: null,
    selectedIcon: 'fas fa-folder',
    
    // Inicializar el componente
    init: function() {
        this.renderIcons();
        this.bindEvents();
    },
    
    // Abrir el picker para un input específico
    open: function(targetInputId, targetPreviewId, currentIcon = "fas fa-folder") {
        this.currentTargetInput = document.getElementById(targetInputId);
        this.currentTargetPreview = document.getElementById(targetPreviewId);
        
        if (!this.currentTargetInput || !this.currentTargetPreview) {
            console.error("No se pudieron encontrar los elementos:", targetInputId, targetPreviewId);
            return;
        }
        
        this.selectedIcon = currentIcon || "fas fa-folder";
        
        // Actualizar preview
        this.updateSelectedIconPreview();
        
        // Seleccionar el icono actual en el grid
        this.selectIconInGrid(this.selectedIcon);
        
        // Mostrar el modal
        const modalElement = document.getElementById("iconPickerModal");
        if (!modalElement) {
            return;
        }
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    },
    
    // Renderizar todos los iconos
    renderIcons: function() {
        const iconGrid = document.getElementById('iconGrid');
        iconGrid.innerHTML = '';
        
        // Obtener todos los iconos
        const allIcons = Object.values(availableIcons).flat();
        
        allIcons.forEach(iconClass => {
            const iconDiv = this.createIconElement(iconClass);
            iconGrid.appendChild(iconDiv);
        });
        
        // Agregar event listeners
        this.addIconClickListeners();
    },
    
    // Crear elemento de icono individual
    createIconElement: function(iconClass) {
        const col = document.createElement('div');
        col.className = 'col-md-2 col-sm-3 col-4';
        
        const iconItem = document.createElement('div');
        iconItem.className = 'icon-item';
        iconItem.setAttribute('data-icon', iconClass);
        
        iconItem.innerHTML = `
            <i class="${iconClass}"></i>
            <small>${iconClass}</small>
        `;
        
        col.appendChild(iconItem);
        return col;
    },
    
    // Agregar event listeners a los iconos
    addIconClickListeners: function() {
        document.querySelectorAll('.icon-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const iconClass = e.currentTarget.getAttribute('data-icon');
                this.selectIcon(iconClass);
            });
        });
    },
    
    // Seleccionar un icono
    selectIcon: function(iconClass) {
        // Remover selección anterior
        document.querySelectorAll('.icon-item.selected').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Seleccionar nuevo icono
        const iconItem = document.querySelector(`[data-icon="${iconClass}"]`);
        if (iconItem) {
            iconItem.classList.add('selected');
        }
        
        this.selectedIcon = iconClass;
        this.updateSelectedIconPreview();
    },
    
    // Seleccionar icono en el grid visualmente
    selectIconInGrid: function(iconClass) {
        setTimeout(() => {
            this.selectIcon(iconClass);
        }, 100);
    },
    
    // Actualizar preview del icono seleccionado
    updateSelectedIconPreview: function() {
        const preview = document.getElementById('selectedIconPreview');
        preview.innerHTML = `
            <i class="${this.selectedIcon}"></i>
            <code>${this.selectedIcon}</code>
        `;
    },
    
    // Confirmar selección
    confirmSelection: function() {
        if (this.currentTargetInput && this.currentTargetPreview) {
            // Actualizar input
            this.currentTargetInput.value = this.selectedIcon;
            
            // Actualizar preview
            this.currentTargetPreview.innerHTML = `<i class="${this.selectedIcon}"></i>`;
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('iconPickerModal'));
            modal.hide();
        }
    },
    
    // Filtrar iconos por búsqueda
    filterIcons: function(searchTerm, category = '') {
        const iconGrid = document.getElementById('iconGrid');
        const noIconsMessage = document.getElementById('noIconsMessage');
        
        // Obtener iconos a mostrar
        let iconsToShow;
        if (category && availableIcons[category]) {
            iconsToShow = availableIcons[category];
        } else {
            iconsToShow = Object.values(availableIcons).flat();
        }
        
        // Filtrar por término de búsqueda
        if (searchTerm) {
            iconsToShow = iconsToShow.filter(iconClass => 
                iconClass.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }
        
        // Mostrar/ocultar mensaje de "sin resultados"
        if (iconsToShow.length === 0) {
            iconGrid.style.display = 'none';
            noIconsMessage.classList.remove('d-none');
        } else {
            iconGrid.style.display = '';
            noIconsMessage.classList.add('d-none');
        }
        
        // Renderizar iconos filtrados
        iconGrid.innerHTML = '';
        iconsToShow.forEach(iconClass => {
            const iconDiv = this.createIconElement(iconClass);
            iconGrid.appendChild(iconDiv);
        });
        
        // Reagregar event listeners
        this.addIconClickListeners();
        
        // Reseleccionar icono actual si está visible
        if (iconsToShow.includes(this.selectedIcon)) {
            this.selectIconInGrid(this.selectedIcon);
        }
    },
    
    // Bind events
    bindEvents: function() {
        // Búsqueda
        document.getElementById('iconSearchInput').addEventListener('input', (e) => {
            const searchTerm = e.target.value;
            const category = document.getElementById('iconCategoryFilter').value;
            this.filterIcons(searchTerm, category);
        });
        
        // Filtro de categoría
        document.getElementById('iconCategoryFilter').addEventListener('change', (e) => {
            const category = e.target.value;
            const searchTerm = document.getElementById('iconSearchInput').value;
            this.filterIcons(searchTerm, category);
        });
        
        // Confirmar selección
        document.getElementById('confirmIconSelection').addEventListener('click', () => {
            this.confirmSelection();
        });
        
        // Limpiar filtros al cerrar modal
        document.getElementById('iconPickerModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('iconSearchInput').value = '';
            document.getElementById('iconCategoryFilter').value = '';
            this.renderIcons();
        });
    }
};

// Función global para abrir el picker (para usar desde otros archivos)
function openIconPicker(targetInputId, targetPreviewId, currentIcon) {
    iconPicker.open(targetInputId, targetPreviewId, currentIcon);
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function() {
    iconPicker.init();
});

// También inicializar cuando jQuery esté listo (para compatibilidad)
$(document).ready(function() {
    if (typeof iconPicker !== "undefined") {
        iconPicker.init();
    }
});
</script>