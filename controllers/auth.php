<?php
// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

/**
 * Middleware de autenticación para el sistema de finanzas
 * Maneja login, logout y verificación de permisos por roles
 */

/**
 * Verificar si el usuario está autenticado
 * @param string $redirect_to URL a donde redirigir si no está autenticado
 */
function requireAuth($redirect_to = '../index.php') {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol'])) {
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Verificar si el usuario tiene el rol requerido
 * @param string $required_role Rol requerido ('superadmin' o 'usuario')
 * @param string $redirect_to URL a donde redirigir si no tiene permisos
 */
function requireRole($required_role, $redirect_to = '../index.php') {
    requireAuth($redirect_to);
    
    if ($_SESSION['user_rol'] !== $required_role) {
        // Si no es superadmin y la página requiere superadmin
        if ($required_role === 'superadmin' && $_SESSION['user_rol'] !== 'superadmin') {
            $_SESSION['error_message'] = 'No tienes permisos para acceder a esta sección.';
            header('Location: ../index.php');
            exit();
        }
    }
}

/**
 * Obtener información del usuario actual
 * @return array|null Datos del usuario o null si no está autenticado
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, nombre, email, rol, activo FROM usuarios WHERE id = ? AND activo = 1");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error obteniendo usuario actual: " . $e->getMessage());
        return null;
    }
}

/**
 * Verificar si el usuario es superadmin
 * @return bool
 */
function isSuperAdmin() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'superadmin';
}

/**
 * Verificar si el usuario es usuario normal
 * @return bool
 */
function isUsuario() {
    return isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'usuario';
}

/**
 * Iniciar sesión del usuario
 * @param array $user_data Datos del usuario
 */
function loginUser($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['user_nombre'] = $user_data['nombre'];
    $_SESSION['user_email'] = $user_data['email'];
    $_SESSION['user_rol'] = $user_data['rol'];
    $_SESSION['login_time'] = time();
    
    // Log del inicio de sesión
    logAccesoRol($user_data['id'], 'login', 'Inicio de sesión exitoso');
}

/**
 * Cerrar sesión del usuario
 */
function logoutUser() {
    // Log del cierre de sesión
    if (isset($_SESSION['user_id'])) {
        logAccesoRol($_SESSION['user_id'], 'logout', 'Cierre de sesión');
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Limpiar cookies si existen
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

/**
 * Verificar propiedad de datos para usuarios normales
 * @param int $user_id ID del usuario actual
 * @param int $registro_id ID del registro a verificar
 * @param string $tabla Nombre de la tabla
 * @return bool
 */
function verificarPropiedadDatos($user_id, $registro_id, $tabla) {
    global $pdo;
    
    // Superadmin puede ver todo
    if ($_SESSION['user_rol'] === 'superadmin') {
        return true;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM {$tabla} WHERE id = ?");
        $stmt->execute([$registro_id]);
        $resultado = $stmt->fetch();
        
        return $resultado && $resultado['user_id'] == $user_id;
    } catch (Exception $e) {
        error_log("Error verificando propiedad de datos: " . $e->getMessage());
        return false;
    }
}

/**
 * Aplicar filtro de usuario según rol
 * @param int $user_id ID del usuario actual
 * @param string $tabla_alias Alias de la tabla en la consulta
 * @return string Condición SQL para filtrar por usuario
 */
function aplicarFiltroUsuario($user_id, $tabla_alias = '') {
    $prefijo = $tabla_alias ? $tabla_alias . '.' : '';
    
    if ($_SESSION['user_rol'] === 'superadmin') {
        // Superadmin ve todo, pero puede filtrar por usuario específico
        $filtro_usuario = isset($_GET['filtro_usuario']) && $_GET['filtro_usuario'] !== '' 
            ? " AND {$prefijo}user_id = " . intval($_GET['filtro_usuario']) 
            : '';
    } else {
        // Usuario normal solo ve sus datos
        $filtro_usuario = " AND {$prefijo}user_id = " . intval($user_id);
    }
    
    return $filtro_usuario;
}

/**
 * Registrar accesos y acciones por rol (para auditoría)
 * @param int $user_id ID del usuario
 * @param string $modulo Módulo accedido
 * @param string $accion Acción realizada
 */
function logAccesoRol($user_id, $modulo, $accion) {
    global $pdo;
    
    try {
        // Verificar si existe la tabla de logs
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'logs_seguridad'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Crear tabla de logs si no existe
            $pdo->exec("
                CREATE TABLE logs_seguridad (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    modulo VARCHAR(50),
                    accion VARCHAR(255),
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
                )
            ");
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO logs_seguridad (user_id, modulo, accion, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $modulo,
            $accion,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    } catch (Exception $e) {
        // Error al registrar log no debe interrumpir la aplicación
        error_log("Error registrando log de seguridad: " . $e->getMessage());
    }
}

/**
 * Verificar permisos de rol usando procedimiento almacenado
 * @param int $user_id ID del usuario
 * @param string $accion Acción a verificar
 * @return bool
 */
function verificarPermisoRol($user_id, $accion) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("CALL VerificarPermisoRol(?, ?, @permitido)");
        $stmt->execute([$user_id, $accion]);
        
        $stmt = $pdo->query("SELECT @permitido as permitido");
        $resultado = $stmt->fetch();
        
        return $resultado['permitido'] == 1;
    } catch (Exception $e) {
        error_log("Error verificando permiso de rol: " . $e->getMessage());
        return false;
    }
}

/**
 * Limpiar sesiones expiradas (llamar periódicamente)
 * @param int $tiempo_expiracion Tiempo en segundos (por defecto 24 horas)
 */
function limpiarSesionesExpiradas($tiempo_expiracion = 86400) {
    // Esta función puede ser llamada por un cron job o en login
    $tiempo_limite = time() - $tiempo_expiracion;
    
    if (isset($_SESSION['login_time']) && $_SESSION['login_time'] < $tiempo_limite) {
        logoutUser();
        header('Location: index.php?mensaje=sesion_expirada');
        exit();
    }
}

/**
 * Regenerar ID de sesión para seguridad
 */
function regenerarSesion() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
?>
