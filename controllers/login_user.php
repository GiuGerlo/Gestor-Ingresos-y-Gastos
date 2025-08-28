<?php
// Evitar cualquier output antes del JSON
ob_start();

// Configurar para no mostrar errores en output (solo en logs)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/connect.php';

// Limpiar cualquier output previo
ob_clean();

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Función para enviar respuesta JSON
function sendJsonResponse($success, $message, $data = null) {
    ob_clean(); // Limpiar cualquier output previo
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Método no permitido');
}

// Obtener datos del formulario
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember_me']);

// Validar campos obligatorios
if (empty($email) || empty($password)) {
    sendJsonResponse(false, 'Por favor, completa todos los campos.');
}

// Antes de session_start(), configurar duración de sesión si "remember_me"
if (isset($_POST['remember_me'])) {
    // 30 días en segundos
    $lifetime = 30 * 24 * 60 * 60;
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'domain' => '', // Cambia si usas subdominios
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Iniciar sesión
session_start();

try {
    // Log de datos recibidos (para depuración)
    error_log("LOGIN ATTEMPT - Email: $email, Remember: " . ($remember_me ? 'Yes' : 'No'));
    
    // Buscar usuario por email
    $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    // Log del resultado de la búsqueda
    error_log("USER SEARCH - Found: " . ($usuario ? 'Yes' : 'No'));

    if ($usuario && password_verify($password, $usuario['password'])) {
        // Log de login exitoso
        error_log("LOGIN SUCCESS - User ID: " . $usuario['id']);
        
        // Login exitoso
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_rol'] = $usuario['rol'];
        
        // Actualizar último acceso
        $stmt = $pdo->prepare("UPDATE usuarios SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$usuario['id']]);
        
        // Recordar email si está marcado
        if ($remember_me) {
            setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/'); // 30 días
        } else {
            setcookie('remember_email', '', time() - 3600, '/'); // Eliminar cookie
        }
        
        // Determinar redirección según el rol
        $redirect_url = ($usuario['rol'] === 'superadmin') ? 'dashboard/admin.php' : 'dashboard/';
        
        sendJsonResponse(true, 'Login exitoso', [
            'redirect' => $redirect_url
        ]);
    } else {
        // Log de login fallido con más detalle
        if ($usuario) {
            error_log("LOGIN FAILED - Password verification failed for user ID: " . $usuario['id']);
        } else {
            error_log("LOGIN FAILED - User not found for email: $email");
        }
        
        // Login fallido
        sendJsonResponse(false, 'Email o contraseña incorrectos.');
    }
    
} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    sendJsonResponse(false, 'Error del sistema. Intenta nuevamente.');
} catch (Exception $e) {
    error_log("Error general en login: " . $e->getMessage());
    sendJsonResponse(false, 'Error inesperado. Intenta nuevamente.');
}
?>
