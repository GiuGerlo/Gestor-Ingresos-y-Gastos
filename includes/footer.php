<!-- Footer -->
<footer class="bg-light border-top mt-5">
    <div class="container-fluid py-4">
        <div class="row align-items-center">
            <!-- Logo y nombre de la empresa -->
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <a href="https://www.artisansthinking.com/" target="_blank" class="logo-link">
                        <img src="<?= $base_path ?>assets/img/Logo_Artisans.webp" alt="Logo Artisans" class="me-3 logo-img" style="height: 40px; width: auto;">
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">Ahorritoo</h5>
                        <small class="text-muted">Sistema de gestión financiera</small>
                    </div>
                </div>
            </div>
            <!-- Información de desarrollo -->
            <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Desarrollado por <a href="https://www.artisansthinking.com/" target="_blank" style="text-decoration: none;"><strong><span style="color: #D0D049;">Artisans </span><span style="color: #D633C6;">Thinking</span></strong></a><br>
                        © <?= date('Y') ?> Todos los derechos reservados
                    </small>
            </div>
        </div>
    </div>
</footer>

<!-- CSS adicional para efectos del footer -->
<style>
    footer {
        margin-top: auto;
    }

    .logo-link {
        text-decoration: none;
        transition: transform 0.3s ease;
    }

    .logo-img {
        transition: transform 0.3s ease, filter 0.3s ease;
    }

    .logo-link:hover .logo-img {
        transform: scale(1.1);
        filter: brightness(1.2);
    }

    @media (max-width: 768px) {
        footer .d-flex {
            justify-content: center;
        }
    }
</style>

<!-- Scripts de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
<?php if (isset($additional_js)): ?>
    <?= $additional_js ?>
<?php endif; ?>

<!-- Script para funcionalidades generales del footer -->
<script>
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
</script>
</body>

</html>