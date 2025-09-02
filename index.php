<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard/index.php');
    exit();
}

// Verificar si viene del logout
$logout_message = '';
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $logout_message = '<div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        Has cerrado sesión correctamente. ¡Hasta pronto!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}

// Configurar variables para el header
$page_title = 'Iniciar Sesión';
$additional_css = '
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    
    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        max-width: 900px;
        width: 100%;
    }
    
    .login-left {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-color) 100%);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .login-left::before {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: url("data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Ccircle cx=\"30\" cy=\"30\" r=\"2\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        animation: float 20s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }
    
    .login-logo {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(10px);
        position: relative;
        z-index: 2;
    }
    
    .login-logo i {
        font-size: 2rem;
        color: white;
    }
    
    .login-right {
        padding: 3rem 2.5rem;
    }
    
    .form-floating .form-control {
        border: 2px solid var(--gray-200);
        border-radius: 10px;
        padding: 1.625rem 1rem 0.625rem;
        height: auto;
        font-size: 1rem;
        background-color: #f8f9fa;
    }
    
    .form-floating .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(101, 72, 213, 0.15);
        background-color: white;
    }
    
    .form-floating label {
        padding: 1rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    .btn-login {
        background: linear-gradient(135deg, var(--secondary-color) 0%, #5640b3 100%);
        border: none;
        border-radius: 10px;
        padding: 0.875rem 2rem;
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(101, 72, 213, 0.3);
        color: white;
    }
    
    .btn-login:active {
        transform: translateY(0);
    }
    
    .feature-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }
    
    .feature-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        backdrop-filter: blur(5px);
    }
    
    .btn-login:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    .form-control:focus {
        border-color: var(--secondary-color);
        box-shadow: 0 0 0 0.2rem rgba(101, 72, 213, 0.15);
    }
    
    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        color: var(--gray-600);
        cursor: pointer;
        z-index: 5;
        padding: 0.5rem;
        border-radius: 4px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
    }
    
    .password-toggle:hover {
        color: var(--secondary-color);
        background: rgba(101, 72, 213, 0.1);
    }
    
    .password-toggle:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(101, 72, 213, 0.2);
        background: rgba(101, 72, 213, 0.1);
    }
    
    .password-toggle:active {
        transform: translateY(-50%) scale(0.95);
    }
    
    /* Asegurar que el botón sea accesible en móviles */
    @media (max-width: 768px) {
        .password-toggle {
            width: 35px;
            height: 35px;
            right: 10px;
        }
        
        .form-control.pe-5 {
            padding-right: 3rem !important;
        }
    }
    
    @media (max-width: 768px) {
        .login-left {
            padding: 2rem 1.5rem;
            text-align: center;
        }
        
        .login-right {
            padding: 2rem 1.5rem;
        }
        
        .login-card {
            margin: 1rem;
            border-radius: 15px;
        }
        
        .feature-item {
            justify-content: center;
            text-align: center;
        }
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 2rem;
        position: relative;
        z-index: 2;
    }
    
    .stat-item {
        background: rgba(255, 255, 255, 0.1);
        padding: 1rem;
        border-radius: 10px;
        text-align: center;
        backdrop-filter: blur(5px);
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        display: block;
    }
    
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
</style>
';

// Incluir header
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="row g-0 h-100">
            <!-- Panel izquierdo - Información del sistema -->
            <div class="col-lg-5 login-left">
                <div class="login-logo">
                    <img src="assets/img/logo-original.png" alt="Logo" style="width:100px;height:100px;object-fit:contain;border-radius:16px;background:rgba(255,255,255,0.5);box-shadow:0 2px 8px rgba(0,0,0,0.08);padding:8px;">
                </div>

                <h2 class="fw-bold mb-3">Ahorritoo</h2>
                <p class="mb-4 text-center">
                    Sistema integral de gestión financiera personal.
                    Controla tus ingresos, gastos y planifica tu futuro económico.
                </p>

                <div class="w-100">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <strong>Control de Ingresos</strong><br>
                            <small>Registra y categoriza todos tus ingresos</small>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-minus-circle"></i>
                        </div>
                        <div>
                            <strong>Gestión de Gastos</strong><br>
                            <small>Monitorea y controla tus gastos diarios</small>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <strong>Gastos Fijos</strong><br>
                            <small>Programa y recibe alertas de pagos recurrentes</small>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div>
                            <strong>Reportes Visuales</strong><br>
                            <small>Gráficos y análisis de tu situación financiera</small>
                        </div>
                    </div>
                </div>

                <!-- <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Gratis</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <span class="stat-label">Disponible</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">🔒</span>
                        <span class="stat-label">Seguro</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">📱</span>
                        <span class="stat-label">Responsive</span>
                    </div>
                </div> -->
            </div>

            <!-- Panel derecho - Formulario de login -->
            <div class="col-lg-7 login-right">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-dark mb-2">¡Bienvenido!</h3>
                    <p class="text-muted">Ingresa tus credenciales para acceder al sistema</p>
                </div>

                <!-- Mensaje de logout -->
                <?php echo $logout_message; ?>

                <!-- Formulario de login -->
                <form method="POST" id="loginForm" novalidate>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="tu@email.com"
                                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating mb-3 position-relative">
                                <input type="password"
                                    class="form-control pe-5"
                                    id="password"
                                    name="password"
                                    placeholder="Contraseña">
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <button type="button" class="password-toggle" onclick="togglePassword()" title="Mostrar/Ocultar contraseña">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                            <label class="form-check-label text-muted" for="remember_me">
                                Recordarme
                            </label>
                        </div>
                        <!-- <a href="#" class="text-decoration-none" onclick="mostrarAyuda()">
                            <small>¿Olvidaste tu contraseña?</small>
                        </a> -->
                    </div>

                    <button type="submit" class="btn btn-login w-100 mb-4" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <span id="btnText">Iniciar Sesión</span>
                    </button>
                </form>

                <!-- Información adicional -->
                <div class="text-center">
                    <hr class="my-4">
                    <p class="text-muted mb-3">
                        <small>
                            <i class="fas fa-shield-alt me-1"></i>
                            Conexión segura y datos protegidos
                        </small>
                    </p>

                    <!-- Credenciales de prueba -->
                    <!-- <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle me-2"></i>Credenciales de prueba:</strong><br>
                        <small>
                            <strong>Superadmin:</strong> admin@gestorfinanzas.com / admin123<br>
                            <strong>Usuario:</strong> Crear cuenta nueva con el registro
                        </small>
                    </div> -->

                    <!-- <p class="text-muted">
                        ¿No tienes cuenta? 
                        <a href="register.php" class="text-decoration-none fw-medium">
                            Regístrate aquí
                        </a>
                    </p> -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para mostrar/ocultar contraseña
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');
        const toggleBtn = document.querySelector('.password-toggle');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
            toggleBtn.title = 'Ocultar contraseña';

            // Feedback visual
            toggleBtn.style.color = 'var(--secondary-color)';
            setTimeout(() => {
                toggleBtn.style.color = '';
            }, 200);
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
            toggleBtn.title = 'Mostrar contraseña';

            // Feedback visual
            toggleBtn.style.color = 'var(--gray-600)';
            setTimeout(() => {
                toggleBtn.style.color = '';
            }, 200);
        }
    }

    // Función para mostrar alertas con SweetAlert2
    function showAlert(type, title, message, timer = null) {
        const alertConfig = {
            title: title,
            text: message,
            icon: type,
            confirmButtonColor: type === 'success' ? '#28a745' : '#dc3545',
            confirmButtonText: 'Aceptar',
            allowOutsideClick: false,
            allowEscapeKey: false
        };

        if (timer) {
            alertConfig.timer = timer;
            alertConfig.timerProgressBar = true;
        }

        return Swal.fire(alertConfig);
    }

    // Función para validar el formulario
    function validateForm() {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        // Validar email
        if (!email) {
            showAlert('warning', 'Campo requerido', 'Por favor ingresa tu correo electrónico');
            document.getElementById('email').focus();
            return false;
        }

        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showAlert('warning', 'Email inválido', 'Por favor ingresa un correo electrónico válido');
            document.getElementById('email').focus();
            return false;
        }

        // Validar contraseña
        if (!password) {
            showAlert('warning', 'Campo requerido', 'Por favor ingresa tu contraseña');
            document.getElementById('password').focus();
            return false;
        }

        if (password.length < 6) {
            showAlert('warning', 'Contraseña muy corta', 'La contraseña debe tener al menos 6 caracteres');
            document.getElementById('password').focus();
            return false;
        }

        return true;
    }

    // Auto-completar credenciales para desarrollo
    document.addEventListener('DOMContentLoaded', function() {
        // Enfocar en el campo email al cargar
        document.getElementById('email').focus();

        // Detectar parámetros de URL para autocompletado
        const urlParams = new URLSearchParams(window.location.search);
        const tipo = urlParams.get('tipo');

        if (tipo === 'admin') {
            document.getElementById('email').value = 'admin@gestorfinanzas.com';
            document.getElementById('password').focus();
        }

        // Manejar envío del formulario via AJAX
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validar formulario antes de enviar
                if (!validateForm()) {
                    return;
                }

                const submitBtn = document.getElementById('loginBtn');
                const btnText = document.getElementById('btnText');
                const originalText = btnText.innerHTML;

                // Mostrar spinner de carga
                btnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Iniciando sesión...';
                submitBtn.disabled = true;

                // Preparar datos del formulario
                const formData = new FormData(this);

                // Enviar petición AJAX
                fetch('controllers/login_user.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        return response.text(); // Primero obtenemos como texto para debug
                    })
                    .then(text => {
                        console.log('Raw response:', text);

                        try {
                            const data = JSON.parse(text);
                            console.log('Parsed data:', data);

                            if (data.success) {
                                // Login exitoso
                                showAlert('success', '¡Bienvenido!', data.message, 1500).then(() => {
                                    // Redirigir después de la alerta
                                    window.location.href = data.data.redirect;
                                });
                            } else {
                                // Login fallido
                                showAlert('error', 'Error de acceso', data.message);

                                // Restaurar botón
                                btnText.innerHTML = originalText;
                                submitBtn.disabled = false;

                                // Enfocar en campo de contraseña si hay error
                                document.getElementById('password').focus();
                                document.getElementById('password').select();
                            }
                        } catch (parseError) {
                            console.error('JSON Parse Error:', parseError);
                            console.error('Raw text was:', text);
                            showAlert('error', 'Error del servidor', 'Error de formato en la respuesta del servidor.');

                            // Restaurar botón
                            btnText.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Error de conexión', 'Error de conexión. Intenta nuevamente.');

                        // Restaurar botón
                        btnText.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        }
    });

    // Manejar Enter en los campos
    document.getElementById('email').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('password').focus();
        }
    });

    document.getElementById('password').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('loginForm').dispatchEvent(new Event('submit'));
        }
    });
</script>

<?php include 'includes/footer.php'; ?>