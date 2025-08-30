<?php
/**
 * CONTROLADOR: Gestión de Usuarios
 * ================================
 * Maneja todas las operaciones CRUD para usuarios
 */

session_start();
require_once '../../../config/connect.php';

// Verificar que el usuario esté logueado y sea superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// Configurar headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo, $action);
            break;
        case 'POST':
            handlePost($pdo, $action);
            break;
        case 'PUT':
            handlePut($pdo, $action);
            break;
        case 'DELETE':
            handleDelete($pdo, $action);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    error_log("Error en controlador usuarios: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

/**
 * Manejar peticiones GET
 */
function handleGet($pdo, $action) {
    switch ($action) {
        case 'list':
        case '':
            getAllUsers($pdo);
            break;
        case 'stats':
            getUserStats($pdo);
            break;
        case 'details':
            getUserDetails($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones POST (crear usuario)
 */
function handlePost($pdo, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            createUser($pdo, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones PUT (actualizar usuario)
 */
function handlePut($pdo, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            updateUser($pdo, $input, $_GET['id'] ?? 0);
            break;
        case 'toggle-status':
            toggleUserStatus($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones DELETE (eliminar usuario)
 */
function handleDelete($pdo, $action) {
    switch ($action) {
        case 'delete':
            deleteUser($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Obtener todos los usuarios
 */
function getAllUsers($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                id,
                nombre,
                email,
                rol,
                activo,
                created_at,
                updated_at
            FROM usuarios 
            ORDER BY created_at DESC
        ");
        
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos para la tabla
        $usuariosFormatted = [];
        foreach ($usuarios as $usuario) {
            $usuariosFormatted[] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'activo' => (bool)$usuario['activo'],
                'created_at' => $usuario['created_at'],
                'updated_at' => $usuario['updated_at'],
                'created_at_formatted' => date('d/m/Y H:i', strtotime($usuario['created_at']))
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $usuariosFormatted,
            'total' => count($usuariosFormatted)
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo usuarios: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener usuarios']);
    }
}

/**
 * Obtener estadísticas de usuarios
 */
function getUserStats($pdo) {
    try {
        $stats = [];
        
        // Total usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $stats['total'] = $stmt->fetch()['total'];
        
        // Usuarios activos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
        $stats['activos'] = $stmt->fetch()['total'];
        
        // Super admins
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'superadmin'");
        $stats['superadmins'] = $stmt->fetch()['total'];
        
        // Usuarios con actividad hoy (basado en transacciones)
        $stmt = $pdo->query("
            SELECT COUNT(DISTINCT user_id) as total 
            FROM (
                SELECT user_id FROM ingresos WHERE DATE(created_at) = CURDATE()
                UNION
                SELECT user_id FROM gastos WHERE DATE(created_at) = CURDATE()
            ) as actividad_hoy
        ");
        $stats['activos_hoy'] = $stmt->fetch()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo estadísticas: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener estadísticas']);
    }
}

/**
 * Obtener detalles de un usuario específico
 */
function getUserDetails($pdo, $userId) {
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id, nombre, email, rol, activo, created_at, updated_at
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }
        
        // Formatear fechas para mostrar en el modal
        $usuario['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($usuario['created_at']));
        $usuario['updated_at_formatted'] = date('d/m/Y H:i:s', strtotime($usuario['updated_at']));
        
        // Calcular tiempo en el sistema
        $created = new DateTime($usuario['created_at']);
        $now = new DateTime();
        $interval = $created->diff($now);
        
        if ($interval->days > 0) {
            $timeInSystem = $interval->days . ' días';
        } elseif ($interval->h > 0) {
            $timeInSystem = $interval->h . ' horas';
        } else {
            $timeInSystem = $interval->i . ' minutos';
        }
        
        $usuario['time_in_system'] = $timeInSystem;
        
        echo json_encode([
            'success' => true,
            'data' => $usuario
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles del usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles del usuario']);
    }
}

/**
 * Crear nuevo usuario
 */
function createUser($pdo, $data) {
    // Validar datos requeridos
    if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos incompletos']);
        return;
    }
    
    try {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'El email ya está registrado']);
            return;
        }
        
        // Hashear la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Insertar usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, activo) 
            VALUES (?, ?, ?, ?, 1)
        ");
        
        $rol = $data['rol'] ?? 'usuario';
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            $hashedPassword,
            $rol
        ]);
        
        $userId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => ['id' => $userId]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear usuario']);
    }
}

/**
 * Actualizar usuario
 */
function updateUser($pdo, $data, $userId) {
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    try {
        // Construir query dinámicamente
        $fields = [];
        $values = [];
        
        if (isset($data['nombre'])) {
            $fields[] = "nombre = ?";
            $values[] = $data['nombre'];
        }
        
        if (isset($data['email'])) {
            // Verificar que el email no esté en uso por otro usuario
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'El email ya está en uso']);
                return;
            }
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($data['rol'])) {
            $fields[] = "rol = ?";
            $values[] = $data['rol'];
        }
        
        if (isset($data['activo'])) {
            $fields[] = "activo = ?";
            $values[] = $data['activo'] ? 1 : 0;
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'No hay datos para actualizar']);
            return;
        }
        
        $values[] = $userId;
        
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET " . implode(', ', $fields) . " 
            WHERE id = ?
        ");
        
        $stmt->execute($values);
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar usuario']);
    }
}

/**
 * Cambiar estado activo/inactivo del usuario
 */
function toggleUserStatus($pdo, $userId) {
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET activo = !activo WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado del usuario actualizado'
        ]);
        
    } catch (PDOException $e) {
        error_log("Error cambiando estado del usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar estado del usuario']);
    }
}

/**
 * Eliminar usuario
 */
function deleteUser($pdo, $userId) {
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de usuario requerido']);
        return;
    }
    
    // Verificar que no sea el usuario actual
    if ($userId == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode(['error' => 'No puedes eliminar tu propio usuario']);
        return;
    }
    
    try {
        // Verificar si el usuario tiene transacciones
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM ingresos WHERE user_id = ?) +
                (SELECT COUNT(*) FROM gastos WHERE user_id = ?) +
                (SELECT COUNT(*) FROM gastos_fijos WHERE user_id = ?) as total_transacciones
        ");
        $stmt->execute([$userId, $userId, $userId]);
        $transacciones = $stmt->fetch()['total_transacciones'];
        
        if ($transacciones > 0) {
            // Si tiene transacciones, solo desactivar
            $stmt = $pdo->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuario desactivado (tiene transacciones asociadas)'
            ]);
        } else {
            // Si no tiene transacciones, eliminar completamente
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Error eliminando usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar usuario']);
    }
}
?>
