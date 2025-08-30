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
    if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password']) || !isset($data['rol'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos incompletos. Todos los campos son obligatorios.'
        ]);
        return;
    }
    
    // Validaciones adicionales
    if (strlen(trim($data['nombre'])) < 2) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El nombre debe tener al menos 2 caracteres.'
        ]);
        return;
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El formato del email no es válido.'
        ]);
        return;
    }
    
    if (strlen($data['password']) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'La contraseña debe tener al menos 6 caracteres.'
        ]);
        return;
    }
    
    if (!in_array($data['rol'], ['usuario', 'superadmin'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Rol no válido.'
        ]);
        return;
    }
    
    try {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'El email ya está registrado en el sistema.'
            ]);
            return;
        }
        
        // Hashear la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Determinar estado activo (por defecto 1)
        $activo = isset($data['activo']) ? (int)$data['activo'] : 1;
        
        // Insertar usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, activo) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            trim($data['nombre']),
            strtolower(trim($data['email'])),
            $hashedPassword,
            $data['rol'],
            $activo
        ]);
        
        $userId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => [
                'id' => $userId,
                'nombre' => trim($data['nombre']),
                'email' => strtolower(trim($data['email'])),
                'rol' => $data['rol'],
                'activo' => $activo
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor al crear el usuario.'
        ]);
    }
}

/**
 * Actualizar usuario
 */
function updateUser($pdo, $data, $userId) {
    if (!$userId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de usuario requerido'
        ]);
        return;
    }
    
    // Validar que el usuario existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no encontrado'
        ]);
        return;
    }
    
    // Validaciones adicionales
    if (isset($data['nombre']) && strlen(trim($data['nombre'])) < 2) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El nombre debe tener al menos 2 caracteres.'
        ]);
        return;
    }
    
    if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El formato del email no es válido.'
        ]);
        return;
    }
    
    if (isset($data['password']) && !empty($data['password']) && strlen($data['password']) < 6) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'La contraseña debe tener al menos 6 caracteres.'
        ]);
        return;
    }
    
    if (isset($data['rol']) && !in_array($data['rol'], ['usuario', 'superadmin'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Rol no válido.'
        ]);
        return;
    }
    
    try {
        // Construir query dinámicamente
        $fields = [];
        $values = [];
        
        if (isset($data['nombre'])) {
            $fields[] = "nombre = ?";
            $values[] = trim($data['nombre']);
        }
        
        if (isset($data['email'])) {
            // Verificar que el email no esté en uso por otro usuario
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'El email ya está en uso por otro usuario'
                ]);
                return;
            }
            $fields[] = "email = ?";
            $values[] = strtolower(trim($data['email']));
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
            $values[] = (int)$data['activo'];
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'No hay datos para actualizar'
            ]);
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
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor al actualizar el usuario'
        ]);
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
        echo json_encode([
            'success' => false,
            'error' => 'ID de usuario requerido'
        ]);
        return;
    }
    
    // Verificar que no sea el usuario actual
    if ($userId == $_SESSION['user_id']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No puedes eliminar tu propio usuario'
        ]);
        return;
    }
    
    try {
        // Verificar que el usuario existe
        $stmt = $pdo->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Usuario no encontrado'
            ]);
            return;
        }
        
        // Iniciar transacción para garantizar consistencia
        $pdo->beginTransaction();
        
        // Contar registros asociados antes de eliminar
        $registrosEliminados = [
            'ingresos' => 0,
            'gastos' => 0,
            'gastos_fijos' => 0,
            'categorias' => 0,
            'metodos_pago' => 0
        ];
        
        // Eliminar ingresos del usuario (si la tabla existe)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE user_id = ?");
            $stmt->execute([$userId]);
            $registrosEliminados['ingresos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM ingresos WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // Tabla no existe, continuar
        }
        
        // Eliminar gastos del usuario (si la tabla existe)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM gastos WHERE user_id = ?");
            $stmt->execute([$userId]);
            $registrosEliminados['gastos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM gastos WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // Tabla no existe, continuar
        }
        
        // Eliminar gastos fijos del usuario (si la tabla existe)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM gastos_fijos WHERE user_id = ?");
            $stmt->execute([$userId]);
            $registrosEliminados['gastos_fijos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM gastos_fijos WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // Tabla no existe, continuar
        }
        
        // Eliminar categorías personalizadas del usuario (si la tabla las tiene con user_id)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE user_id = ?");
            $stmt->execute([$userId]);
            $registrosEliminados['categorias'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // Columna user_id no existe en categorias, continuar
        }
        
        // Eliminar métodos de pago personalizados del usuario (si la tabla los tiene con user_id)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE user_id = ?");
            $stmt->execute([$userId]);
            $registrosEliminados['metodos_pago'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE user_id = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            // Columna user_id no existe en metodos_pago, continuar
        }
        
        // Finalmente, eliminar el usuario
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            // Confirmar transacción
            $pdo->commit();
            
            // Preparar mensaje detallado
            $totalRegistros = array_sum($registrosEliminados);
            $detalles = [];
            
            foreach ($registrosEliminados as $tipo => $cantidad) {
                if ($cantidad > 0) {
                    $detalles[] = "$cantidad " . ucfirst(str_replace('_', ' ', $tipo));
                }
            }
            
            $mensajeDetalle = '';
            if ($totalRegistros > 0) {
                $mensajeDetalle = ' y ' . $totalRegistros . ' registros asociados (' . implode(', ', $detalles) . ')';
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Usuario '{$usuario['nombre']}' eliminado exitosamente{$mensajeDetalle}",
                'deleted_user' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email']
                ],
                'deleted_records' => $registrosEliminados,
                'total_records' => $totalRegistros
            ]);
        } else {
            $pdo->rollback();
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo eliminar el usuario'
            ]);
        }
        
    } catch (PDOException $e) {
        // Rollback en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        error_log("Error eliminando usuario: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor al eliminar el usuario'
        ]);
    }
}
?>
