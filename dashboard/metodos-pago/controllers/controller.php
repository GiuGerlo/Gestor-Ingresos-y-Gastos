<?php
/**
 * CONTROLADOR: Gestión de Métodos de Pago
 * =======================================
 * Maneja todas las operaciones CRUD para métodos de pago
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
    error_log("Error en controlador métodos de pago: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

/**
 * Manejar peticiones GET
 */
function handleGet($pdo, $action) {
    switch ($action) {
        case 'list':
        case 'getAll':
        case '':
            getAllPaymentMethods($pdo);
            break;
        case 'stats':
            getPaymentMethodStats($pdo);
            break;
        case 'details':
        case 'getOne':
            getPaymentMethodDetails($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones POST (crear método de pago)
 */
function handlePost($pdo, $action) {
    // Detectar si es FormData o JSON
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
    } else {
        // FormData desde formulario
        $input = $_POST;
    }
    
    switch ($action) {
        case 'create':
            createPaymentMethod($pdo, $input);
            break;
        case 'update':
            updatePaymentMethod($pdo, $input);
            break;
        case 'delete':
            deletePaymentMethod($pdo, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones PUT (actualizar método de pago)
 */
function handlePut($pdo, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            updatePaymentMethod($pdo, $input, $_GET['id'] ?? 0);
            break;
        case 'toggle-status':
            togglePaymentMethodStatus($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones DELETE (eliminar método de pago)
 */
function handleDelete($pdo, $action) {
    switch ($action) {
        case 'delete':
            deletePaymentMethod($pdo, ['id' => $_GET['id'] ?? 0]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Obtener todos los métodos de pago
 */
function getAllPaymentMethods($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at
            FROM metodos_pago 
            ORDER BY created_at DESC
        ");
        
        $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos para la tabla
        $metodosFormatted = [];
        foreach ($metodos as $metodo) {
            $metodosFormatted[] = [
                'id' => $metodo['id'],
                'nombre' => $metodo['nombre'],
                'color' => $metodo['color'],
                'activo' => (bool)$metodo['activo'],
                'created_at' => $metodo['created_at'],
                'created_at_formatted' => date('d/m/Y H:i', strtotime($metodo['created_at']))
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $metodosFormatted,
            'total' => count($metodosFormatted)
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo métodos de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener métodos de pago']);
    }
}

/**
 * Obtener estadísticas de métodos de pago
 */
function getPaymentMethodStats($pdo) {
    try {
        $stats = [];
        
        // Total métodos de pago
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago");
        $stats['total'] = $stmt->fetch()['total'];
        
        // Métodos activos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 1");
        $stats['activos'] = $stmt->fetch()['total'];
        
        // Métodos inactivos
        $stats['inactivos'] = $stats['total'] - $stats['activos'];
        
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
 * Obtener detalles de un método de pago específico
 */
function getPaymentMethodDetails($pdo, $methodId) {
    if (!$methodId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de método de pago requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id, nombre, color, activo, created_at
            FROM metodos_pago 
            WHERE id = ?
        ");
        $stmt->execute([$methodId]);
        $metodo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$metodo) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Formatear fechas para mostrar en el modal
        $metodo['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($metodo['created_at']));
        
        // Convertir activo a booleano
        $metodo['activo'] = (bool)$metodo['activo'];
        
        echo json_encode([
            'success' => true,
            'data' => $metodo
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles del método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles del método de pago']);
    }
}

/**
 * Crear nuevo método de pago
 */
function createPaymentMethod($pdo, $data) {
    // Validar datos requeridos
    if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El nombre es obligatorio.'
        ]);
        return;
    }
    
    if (!isset($data['color']) || empty(trim($data['color']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El color es obligatorio.'
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
    
    // Validar formato de color hexadecimal
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).'
        ]);
        return;
    }
    
    try {
        // Verificar si el nombre ya existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE nombre = ?");
        $stmt->execute([trim($data['nombre'])]);
        
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe un método de pago con este nombre.'
            ]);
            return;
        }
        
        // Insertar nuevo método de pago
        $stmt = $pdo->prepare("
            INSERT INTO metodos_pago (nombre, color, activo) 
            VALUES (?, ?, ?)
        ");
        
        $activo = isset($data['activo']) ? (int)$data['activo'] : 1;
        $stmt->execute([trim($data['nombre']), $data['color'], $activo]);
        
        $newId = $pdo->lastInsertId();
        
        // Obtener el método de pago recién creado
        $stmt = $pdo->prepare("
            SELECT id, nombre, color, activo, created_at 
            FROM metodos_pago WHERE id = ?
        ");
        $stmt->execute([$newId]);
        $newMethod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago creado exitosamente',
            'data' => $newMethod
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando método de pago: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe un método de pago con este nombre.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor.'
            ]);
        }
    }
}

/**
 * Actualizar método de pago existente
 */
function updatePaymentMethod($pdo, $data, $id = null) {
    // Obtener ID del método de pago
    $methodId = $id ?? ($data['id'] ?? 0);
    
    if (!$methodId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID del método de pago requerido'
        ]);
        return;
    }
    
    // Validar datos requeridos
    if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El nombre es obligatorio.'
        ]);
        return;
    }
    
    if (!isset($data['color']) || empty(trim($data['color']))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El color es obligatorio.'
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
    
    // Validar formato de color hexadecimal
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El color debe ser un código hexadecimal válido (ej: #FF0000).'
        ]);
        return;
    }
    
    try {
        // Verificar que el método de pago existe
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE id = ?");
        $stmt->execute([$methodId]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Método de pago no encontrado'
            ]);
            return;
        }
        
        // Verificar si el nombre ya existe (excepto el actual)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE nombre = ? AND id != ?");
        $stmt->execute([trim($data['nombre']), $methodId]);
        
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe otro método de pago con este nombre.'
            ]);
            return;
        }
        
        // Actualizar método de pago
        $stmt = $pdo->prepare("
            UPDATE metodos_pago 
            SET nombre = ?, color = ?, activo = ?
            WHERE id = ?
        ");
        
        $activo = isset($data['activo']) ? (int)$data['activo'] : 1;
        $stmt->execute([trim($data['nombre']), $data['color'], $activo, $methodId]);
        
        // Obtener el método de pago actualizado
        $stmt = $pdo->prepare("
            SELECT id, nombre, color, activo, created_at 
            FROM metodos_pago WHERE id = ?
        ");
        $stmt->execute([$methodId]);
        $updatedMethod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago actualizado exitosamente',
            'data' => $updatedMethod
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando método de pago: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe otro método de pago con este nombre.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error interno del servidor.'
            ]);
        }
    }
}

/**
 * Eliminar método de pago
 */
function deletePaymentMethod($pdo, $data) {
    $methodId = $data['id'] ?? 0;
    
    if (!$methodId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID del método de pago requerido'
        ]);
        return;
    }
    
    try {
        // Verificar que el método de pago existe
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE id = ?");
        $stmt->execute([$methodId]);
        $method = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$method) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Método de pago no encontrado'
            ]);
            return;
        }
        
        // Verificar si el método de pago está siendo usado en transacciones
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM ingresos WHERE metodo_pago_id = ?) +
                (SELECT COUNT(*) FROM gastos WHERE metodo_pago_id = ?) as total_uses
        ");
        $stmt->execute([$methodId, $methodId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_uses'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => "No se puede eliminar el método de pago '{$method['nombre']}' porque está siendo utilizado en {$result['total_uses']} transacción(es). Considere desactivarlo en su lugar."
            ]);
            return;
        }
        
        // Eliminar método de pago
        $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id = ?");
        $stmt->execute([$methodId]);
        
        echo json_encode([
            'success' => true,
            'message' => "Método de pago '{$method['nombre']}' eliminado exitosamente"
        ]);
        
    } catch (PDOException $e) {
        error_log("Error eliminando método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor.'
        ]);
    }
}

/**
 * Cambiar estado activo/inactivo del método de pago
 */
function togglePaymentMethodStatus($pdo, $methodId) {
    if (!$methodId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID del método de pago requerido'
        ]);
        return;
    }
    
    try {
        // Obtener estado actual
        $stmt = $pdo->prepare("SELECT activo FROM metodos_pago WHERE id = ?");
        $stmt->execute([$methodId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Método de pago no encontrado'
            ]);
            return;
        }
        
        // Cambiar estado
        $newStatus = $result['activo'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE metodos_pago SET activo = ? WHERE id = ?");
        $stmt->execute([$newStatus, $methodId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'new_status' => (bool)$newStatus
        ]);
        
    } catch (PDOException $e) {
        error_log("Error cambiando estado del método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor.'
        ]);
    }
}
?>
