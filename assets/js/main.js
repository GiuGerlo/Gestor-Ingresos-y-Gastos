/**
 * GESTOR DE FINANZAS - JAVASCRIPT PRINCIPAL
 * ========================================
 * Funciones globales y utilidades del sistema
 */

// Configuración global
const CONFIG = {
    timezone: 'America/Argentina/Buenos_Aires',
    currency: 'ARS',
    dateFormat: 'es-AR',
    api: {
        timeout: 10000,
        retryAttempts: 3
    }
};

// Estado global de la aplicación
const AppState = {
    user: null,
    notifications: [],
    loading: false,
    theme: 'light'
};

/**
 * UTILIDADES DE FECHA Y HORA
 * ===========================
 */

// Obtener fecha actual en zona horaria argentina
function obtenerFechaArgentina(formato = 'yyyy-mm-dd') {
    const fecha = new Date();
    const opciones = {
        timeZone: CONFIG.timezone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    };
    
    const fechaFormateada = fecha.toLocaleDateString(CONFIG.dateFormat, opciones);
    
    if (formato === 'yyyy-mm-dd') {
        // Convertir de dd/mm/yyyy a yyyy-mm-dd para inputs
        const partes = fechaFormateada.split('/');
        return `${partes[2]}-${partes[1]}-${partes[0]}`;
    }
    
    return fechaFormateada;
}

// Formatear fecha para mostrar al usuario
function formatearFecha(fecha, incluirHora = false) {
    const opciones = {
        timeZone: CONFIG.timezone,
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    };
    
    if (incluirHora) {
        opciones.hour = '2-digit';
        opciones.minute = '2-digit';
    }
    
    return new Date(fecha).toLocaleDateString(CONFIG.dateFormat, opciones);
}

// Validar formato de fecha
function validarFecha(fecha) {
    const fechaObj = new Date(fecha);
    return fechaObj instanceof Date && !isNaN(fechaObj);
}

/**
 * UTILIDADES DE MONEDA
 * =====================
 */

// Formatear número como moneda argentina
function formatearMoneda(numero, simbolo = true) {
    const opciones = {
        style: simbolo ? 'currency' : 'decimal',
        currency: CONFIG.currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    };
    
    return new Intl.NumberFormat(CONFIG.dateFormat, opciones).format(numero);
}

// Limpiar formato de moneda para obtener número
function limpiarMoneda(texto) {
    return parseFloat(texto.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
}

// Validar monto ingresado
function validarMonto(monto) {
    const numero = parseFloat(monto);
    return !isNaN(numero) && numero >= 0;
}

/**
 * UTILIDADES DE VALIDACIÓN
 * =========================
 */

// Validar email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validar campos requeridos
function validarCamposRequeridos(formulario) {
    const camposRequeridos = formulario.querySelectorAll('[required]');
    let valido = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            mostrarErrorCampo(campo, 'Este campo es obligatorio');
            valido = false;
        } else {
            limpiarErrorCampo(campo);
        }
    });
    
    return valido;
}

// Mostrar error en campo específico
function mostrarErrorCampo(campo, mensaje) {
    limpiarErrorCampo(campo);
    
    campo.classList.add('is-invalid');
    
    const error = document.createElement('div');
    error.className = 'invalid-feedback';
    error.textContent = mensaje;
    
    campo.parentNode.appendChild(error);
}

// Limpiar error de campo
function limpiarErrorCampo(campo) {
    campo.classList.remove('is-invalid');
    
    const errorExistente = campo.parentNode.querySelector('.invalid-feedback');
    if (errorExistente) {
        errorExistente.remove();
    }
}

/**
 * UTILIDADES DE UI/UX
 * ===================
 */

// Mostrar notificación toast
function mostrarToast(mensaje, tipo = 'info', duracion = 3000) {
    // Crear elemento toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${getIconoTipo(tipo)} me-2"></i>
                ${mensaje}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Agregar al contenedor de toasts
    let contenedor = document.getElementById('toast-container');
    if (!contenedor) {
        contenedor = document.createElement('div');
        contenedor.id = 'toast-container';
        contenedor.className = 'toast-container position-fixed top-0 end-0 p-3';
        contenedor.style.zIndex = '9999';
        document.body.appendChild(contenedor);
    }
    
    contenedor.appendChild(toast);
    
    // Inicializar y mostrar toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: duracion
    });
    
    bsToast.show();
    
    // Remover elemento después de ocultarse
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Obtener icono según tipo de mensaje
function getIconoTipo(tipo) {
    const iconos = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle',
        'primary': 'star',
        'secondary': 'cog'
    };
    
    return iconos[tipo] || 'info-circle';
}

// Mostrar/ocultar loading en elemento
function toggleLoading(elemento, mostrar = true) {
    if (mostrar) {
        elemento.disabled = true;
        elemento.classList.add('loading');
        
        const textOriginal = elemento.textContent;
        elemento.dataset.originalText = textOriginal;
        elemento.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Cargando...
        `;
    } else {
        elemento.disabled = false;
        elemento.classList.remove('loading');
        elemento.textContent = elemento.dataset.originalText || 'Enviar';
    }
}

// Confirmar acción
function confirmarAccion(mensaje, callback) {
    if (confirm(mensaje)) {
        callback();
    }
}

/**
 * UTILIDADES DE AJAX
 * ==================
 */

// Realizar petición AJAX con configuración estándar
async function realizarPeticion(url, opciones = {}) {
    const configuracion = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        ...opciones
    };
    
    try {
        AppState.loading = true;
        
        const respuesta = await fetch(url, configuracion);
        
        if (!respuesta.ok) {
            throw new Error(`Error HTTP: ${respuesta.status}`);
        }
        
        const datos = await respuesta.json();
        
        AppState.loading = false;
        
        return datos;
    } catch (error) {
        AppState.loading = false;
        console.error('Error en petición AJAX:', error);
        mostrarToast('Error de conexión. Intenta nuevamente.', 'danger');
        throw error;
    }
}

// Enviar formulario por AJAX
async function enviarFormulario(formulario, url = null) {
    if (!validarCamposRequeridos(formulario)) {
        return false;
    }
    
    const datos = new FormData(formulario);
    const urlDestino = url || formulario.action;
    
    try {
        const respuesta = await realizarPeticion(urlDestino, {
            method: 'POST',
            body: datos,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (respuesta.success) {
            mostrarToast(respuesta.message || 'Operación exitosa', 'success');
            
            // Limpiar formulario si es necesario
            if (respuesta.resetForm) {
                formulario.reset();
            }
            
            // Recargar página si es necesario
            if (respuesta.reload) {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
            
            // Redireccionar si es necesario
            if (respuesta.redirect) {
                setTimeout(() => {
                    window.location.href = respuesta.redirect;
                }, 1000);
            }
        } else {
            mostrarToast(respuesta.message || 'Error en la operación', 'danger');
        }
        
        return respuesta;
    } catch (error) {
        mostrarToast('Error al enviar el formulario', 'danger');
        return false;
    }
}

/**
 * UTILIDADES DE TABLAS
 * =====================
 */

// Configuración estándar para DataTables
function getConfiguracionDataTable() {
    return {
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']], // Ordenar por primera columna descendente
        columnDefs: [
            {
                targets: [-1], // Última columna (acciones)
                orderable: false,
                searchable: false
            }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function() {
            // Reinicializar tooltips después de cada redibujado
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    };
}

/**
 * UTILIDADES DE LOCAL STORAGE
 * ============================
 */

// Guardar datos en localStorage
function guardarEnStorage(clave, valor) {
    try {
        localStorage.setItem(clave, JSON.stringify(valor));
        return true;
    } catch (error) {
        console.error('Error guardando en localStorage:', error);
        return false;
    }
}

// Obtener datos de localStorage
function obtenerDeStorage(clave, valorPorDefecto = null) {
    try {
        const valor = localStorage.getItem(clave);
        return valor ? JSON.parse(valor) : valorPorDefecto;
    } catch (error) {
        console.error('Error obteniendo de localStorage:', error);
        return valorPorDefecto;
    }
}

// Remover datos de localStorage
function removerDeStorage(clave) {
    try {
        localStorage.removeItem(clave);
        return true;
    } catch (error) {
        console.error('Error removiendo de localStorage:', error);
        return false;
    }
}

/**
 * INICIALIZACIÓN DEL SISTEMA
 * ===========================
 */

// Función que se ejecuta cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Inicializar popovers
    const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
    popovers.forEach(popover => {
        new bootstrap.Popover(popover);
    });
    
    // Configurar campos de moneda
    const camposMoneda = document.querySelectorAll('.currency-input');
    camposMoneda.forEach(campo => {
        campo.addEventListener('input', function() {
            let valor = this.value.replace(/[^\d]/g, '');
            if (valor) {
                valor = parseInt(valor).toLocaleString('es-AR');
                this.value = valor;
            }
        });
    });
    
    // Configurar campos de fecha con fecha actual por defecto
    const camposFecha = document.querySelectorAll('input[type="date"]');
    camposFecha.forEach(campo => {
        if (!campo.value) {
            campo.value = obtenerFechaArgentina();
        }
    });
    
    // Configurar formularios AJAX
    const formulariosAjax = document.querySelectorAll('.ajax-form');
    formulariosAjax.forEach(formulario => {
        formulario.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const botonSubmit = this.querySelector('button[type="submit"]');
            if (botonSubmit) {
                toggleLoading(botonSubmit, true);
            }
            
            try {
                await enviarFormulario(this);
            } finally {
                if (botonSubmit) {
                    toggleLoading(botonSubmit, false);
                }
            }
        });
    });
    
    // Cargar preferencias del usuario
    cargarPreferencias();
    
    console.log('✅ Sistema de Finanzas inicializado correctamente');
});

// Cargar preferencias guardadas
function cargarPreferencias() {
    // Cargar tema
    const temaGuardado = obtenerDeStorage('tema', 'light');
    if (temaGuardado === 'dark') {
        document.body.setAttribute('data-bs-theme', 'dark');
        AppState.theme = 'dark';
    }
    
    // Cargar otras preferencias
    const configuracionTablas = obtenerDeStorage('configuracion_tablas', {});
    if (configuracionTablas.pageLength) {
        // Aplicar configuración guardada a DataTables
    }
}

// Funciones de utilidad para exportar globalmente
window.FinanzasUtils = {
    formatearMoneda,
    formatearFecha,
    mostrarToast,
    validarEmail,
    validarMonto,
    obtenerFechaArgentina,
    realizarPeticion,
    enviarFormulario,
    getConfiguracionDataTable,
    guardarEnStorage,
    obtenerDeStorage
};

/**
 * SIDEBAR RESPONSIVE
 * ==================
 */

// Auto-cerrar sidebar en móvil al hacer clic en enlace
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('#sidebar');
    const sidebarLinks = document.querySelectorAll('#sidebar .nav-link');
    
    // Cerrar sidebar al hacer clic en un enlace (solo en móvil)
    if (sidebarLinks.length > 0) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 768 && sidebar && sidebar.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(sidebar, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            });
        });
    }
    
    // Cerrar sidebar al hacer clic fuera de él (solo en móvil)
    if (sidebar) {
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 768 && sidebar.classList.contains('show')) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle) {
                    const bsCollapse = new bootstrap.Collapse(sidebar, {
                        toggle: false
                    });
                    bsCollapse.hide();
                }
            }
        });
    }
});

console.log('📊 Gestor de Finanzas - JavaScript cargado');
