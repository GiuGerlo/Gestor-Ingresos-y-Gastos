<?php
/**
 * FOOTER DINÁMICO DEL DASHBOARD
 * =============================
 * Include para el footer común del dashboard
 */

// Determinar la ruta base según el nivel de directorio
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$base_path = '';
if ($current_dir !== 'dashboard') {
    $base_path = '../';
}
?>

            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- JS personalizado -->
    <script src="<?php echo $base_path; ?>../assets/js/main.js"></script>
    
    <?php if (isset($additional_scripts) && !empty($additional_scripts)): ?>
        <?php echo $additional_scripts; ?>
    <?php endif; ?>
    
    <!-- Scripts específicos del dashboard -->
    <script>
        // Configuración global del dashboard
        window.DashboardConfig = {
            userRole: '<?php echo $_SESSION['user_rol'] ?? 'usuario'; ?>',
            userId: <?php echo $_SESSION['user_id'] ?? 0; ?>,
            basePath: '<?php echo $base_path; ?>',
            currentPage: '<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>',
            timezone: 'America/Argentina/Buenos_Aires'
        };
        
        // Auto-actualizar fecha cada minuto
        setInterval(function() {
            const now = new Date();
            const options = {
                timeZone: 'America/Argentina/Buenos_Aires',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            };
            const dateStr = now.toLocaleDateString('es-AR', options).replace(',', '');
            
            const timeButton = document.querySelector('.btn-outline-secondary');
            if (timeButton) {
                timeButton.innerHTML = '<i class="fas fa-calendar me-1"></i>' + dateStr;
            }
        }, 60000);
        
        console.log('🎛️ Dashboard cargado - Rol:', window.DashboardConfig.userRole);
    </script>
</body>
</html>
