<?php
// Obtener estadísticas para el footer si no están definidas y estamos en el dashboard
if (!isset($footer_stats) && basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    try {
        // Solo obtener estadísticas si tenemos conexión a BD disponible
        if (isset($pdo) && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $ano_actual = date('Y');
            $mes_actual = date('n');
            
            // Ingresos del mes actual
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(monto), 0) as total_ingresos
                FROM ingresos 
                WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
            ");
            $stmt->execute([$user_id, $ano_actual, $mes_actual]);
            $ingresos_mes = $stmt->fetchColumn();

            // Gastos del mes actual
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(monto), 0) as total_gastos
                FROM gastos 
                WHERE user_id = ? AND YEAR(fecha) = ? AND MONTH(fecha) = ?
            ");
            $stmt->execute([$user_id, $ano_actual, $mes_actual]);
            $gastos_mes = $stmt->fetchColumn();

            // Gastos fijos activos
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_fijos
                FROM gastos_fijos 
                WHERE user_id = ? AND activo = 1
            ");
            $stmt->execute([$user_id]);
            $gastos_fijos_activos = $stmt->fetchColumn();

            // Calcular dinero disponible
            $dinero_disponible = $ingresos_mes - $gastos_mes;

            $footer_stats = [
                'ingresos_mes' => $ingresos_mes,
                'gastos_mes' => $gastos_mes,
                'gastos_fijos_activos' => $gastos_fijos_activos,
                'dinero_disponible' => $dinero_disponible
            ];
        } else {
            // Valores por defecto si no hay conexión
            $footer_stats = [
                'ingresos_mes' => 0,
                'gastos_mes' => 0,
                'gastos_fijos_activos' => 0,
                'dinero_disponible' => 0
            ];
        }
    } catch (Exception $e) {
        // En caso de error, usar valores por defecto
        $footer_stats = [
            'ingresos_mes' => 0,
            'gastos_mes' => 0,
            'gastos_fijos_activos' => 0,
            'dinero_disponible' => 0
        ];
    }
}
?>

    <!-- Footer -->
    <footer class="bg-white border-top mt-5">
        <div class="container-fluid">
            <div class="row py-4">
                <!-- Información del sistema -->
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-wrapper me-2" style="background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                        <h5 class="mb-0 fw-bold">
                            <span class="text-dark">Ahorritoo</span>
                        </h5>
                    </div>
                    <p class="text-muted mb-3">
                        Sistema integral de gestión de finanzas personales. 
                        Controla tus ingresos, gastos y gastos fijos de manera eficiente y profesional.
                    </p>
                    <div class="text-muted">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-secondary"></i>
                            <small>Argentina - Zona Horaria UTC-3</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar me-2 text-secondary"></i>
                            <small>Última actualización: <span id="last-update-time"><?= date('d/m/Y H:i') ?></span></small>
                        </div>
                    </div>
                </div>
                
                <!-- Enlaces rápidos -->
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h6 class="fw-bold text-dark mb-3">Navegación</h6>
                    <ul class="list-unstyled">
                        <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php'): ?>
                        <li class="mb-2">
                            <a href="<?= $base_path ?>dashboard/" class="footer-link">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= $base_path ?>dashboard/ingresos/" class="footer-link">
                                <i class="fas fa-plus-circle me-2"></i>Ingresos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= $base_path ?>dashboard/gastos/" class="footer-link">
                                <i class="fas fa-minus-circle me-2"></i>Gastos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= $base_path ?>dashboard/gastos-fijos/" class="footer-link">
                                <i class="fas fa-calendar-alt me-2"></i>Gastos Fijos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= $base_path ?>dashboard/reportes/" class="footer-link">
                                <i class="fas fa-chart-bar me-2"></i>Reportes
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="mb-2">
                            <a href="index.php" class="footer-link">
                                <i class="fas fa-star me-2"></i>Login
                            </a>
                        </li>
                        <!-- 
                        <li class="mb-2">
                            <a href="#about" class="footer-link">
                                <i class="fas fa-info-circle me-2"></i>Acerca de
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="register.php" class="footer-link">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </a>
                        </li> -->
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Estadísticas del usuario -->
                <?php if (basename($_SERVER['PHP_SELF']) !== 'index.php' && basename($_SERVER['PHP_SELF']) !== 'register.php' && isset($footer_stats)): ?>
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="fw-bold text-dark mb-3">Tu Estado Financiero</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: rgba(40, 167, 69, 0.1);">
                                <div class="fw-bold text-success">$<?= number_format($footer_stats['ingresos_mes'], 0, ',', '.') ?></div>
                                <small class="text-muted">Ingresos del Mes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: rgba(220, 53, 69, 0.1);">
                                <div class="fw-bold text-danger">$<?= number_format($footer_stats['gastos_mes'], 0, ',', '.') ?></div>
                                <small class="text-muted">Gastos del Mes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: rgba(255, 193, 7, 0.1);">
                                <div class="fw-bold text-warning"><?= $footer_stats['gastos_fijos_activos'] ?></div>
                                <small class="text-muted">Gastos Fijos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 rounded" style="background: rgba(101, 72, 213, 0.1);">
                                <div class="fw-bold text-secondary">$<?= number_format($footer_stats['dinero_disponible'], 0, ',', '.') ?></div>
                                <small class="text-muted">Disponible</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 8px; height: 8px; background: #28a745;"></div>
                            <small class="text-muted">Sistema Operativo</small>
                        </div>
                        <div class="d-flex align-items-center mt-1">
                            <i class="fas fa-database me-2 text-muted" style="font-size: 10px;"></i>
                            <small class="text-muted">Base de datos: Conectada</small>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Información para páginas públicas -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6 class="fw-bold text-dark mb-3">Características</h6>
                    <div class="text-muted">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <small>Control de ingresos y gastos</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <small>Gestión de gastos fijos</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <small>Reportes y gráficos</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 text-success"></i>
                            <small>Sistema de alertas</small>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Información de contacto y versión -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold text-dark mb-3">Información del Sistema</h6>
                    <div class="text-muted">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-code me-2 text-secondary"></i>
                            <small>Versión: 1.0.0</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar me-2 text-secondary"></i>
                            <small>Año: <?= date('Y') ?></small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-shield-alt me-2 text-secondary"></i>
                            <small>Seguro y confiable</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-mobile-alt me-2 text-secondary"></i>
                            <small>Responsive Design</small>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'superadmin'): ?>
                    <div class="mt-3 p-2 rounded" style="background: rgba(255, 193, 7, 0.1);">
                        <small class="text-warning">
                            <i class="fas fa-crown me-1"></i>
                            Modo Administrador
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="row border-top pt-3 pb-2">
                <div class="col-md-6">
                    <small class="text-muted">
                        © <?= date('Y') ?> Ahorritoo. Sistema desarrollado con dedicación.
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        <span class="text-success">
                            <i class="fas fa-circle me-1" style="font-size: 6px;"></i>
                            Online
                        </span>
                        <span class="ms-3">
                            <i class="fas fa-clock me-1"></i>
                            Hora: <span id="current-time"><?= date('H:i') ?></span>
                        </span>
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- CSS adicional para efectos del footer -->
    <style>
        .footer-link {
            transition: var(--transition-fast);
        }
        
        .footer-link:hover {
            color: var(--secondary-color) !important;
            text-decoration: none;
            transform: translateX(5px);
        }
        
        footer {
            margin-top: auto;
        }
        
        @media (max-width: 768px) {
            footer .col-lg-4,
            footer .col-lg-2,
            footer .col-lg-3 {
                text-align: center;
                margin-bottom: 2rem;
            }
            
            footer .d-flex {
                justify-content: center;
            }
        }
    </style>
    
    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS (solo si es necesario) -->
    <?php if (isset($include_datatables) && $include_datatables): ?>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <?php endif; ?>
    
    <!-- Scripts personalizados -->
    <script src="<?= $base_path ?>assets/js/main.js"></script>
    
    <!-- Scripts adicionales específicos de página -->
    <?php if(isset($additional_js)): ?>
        <?= $additional_js ?>
    <?php endif; ?>
    
    <!-- Script para funcionalidades generales del footer -->
    <script>
        // Actualizar la hora cada minuto
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('es-AR', {
                timeZone: 'America/Argentina/Buenos_Aires',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = timeString;
            }
            
            // También actualizar el último tiempo de actualización
            const lastUpdateElement = document.getElementById('last-update-time');
            if (lastUpdateElement) {
                const updateString = now.toLocaleString('es-AR', {
                    timeZone: 'America/Argentina/Buenos_Aires',
                    day: '2-digit',
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                lastUpdateElement.textContent = updateString;
            }
        }
        
        // Actualizar inmediatamente y luego cada minuto
        updateTime();
        setInterval(updateTime, 60000);
        
        // Cerrar alerts automáticamente después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
        
        // Confirmar acciones de eliminación
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('[data-action="delete"]');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
                        e.preventDefault();
                    }
                });
            });
        });
        
        // Función para formatear números con separador de miles argentino
        function formatearMoneda(numero) {
            return new Intl.NumberFormat('es-AR', {
                style: 'currency',
                currency: 'ARS',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(numero);
        }
        
        // Función para formatear fechas en formato argentino
        function formatearFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-AR', {
                timeZone: 'America/Argentina/Buenos_Aires',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    </script>
</body>
</html>
