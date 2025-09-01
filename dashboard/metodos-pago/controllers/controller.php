<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado y sea superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Incluir conexión a la base de datos
require_once '../../../config/connect.php';

// Obtener el método HTTP (manejar override para DELETE)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $method = 'DELETE';
}
$action = $_GET['action'] ?? '';

// Configurar tipo de contenido para JSON
header('Content-Type: application/json');

try {
    // Manejar diferentes métodos HTTP
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
        case '':
            getAllPaymentMethods($pdo);
            break;
        case 'stats':
            getPaymentMethodStats($pdo);
            break;
        case 'details':
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
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            createPaymentMethod($pdo, $input);
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
            updatePaymentMethod($pdo, $_GET['id'] ?? 0, $input);
            break;
        case 'toggle':
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
            deletePaymentMethod($pdo, $_GET['id'] ?? 0);
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
                icono,
                color,
                activo,
                created_at
            FROM metodos_pago 
            ORDER BY created_at DESC
        ");
        
        $metodos_pago = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos para la tabla
        $metodosFormatted = [];
        foreach ($metodos_pago as $metodo) {
            $metodosFormatted[] = [
                'id' => $metodo['id'],
                'nombre' => $metodo['nombre'],
                'icono' => $metodo['icono'],
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
        
        // Métodos de pago activos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 1");
        $stats['activos'] = $stmt->fetch()['total'];
        
        // Métodos de pago inactivos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM metodos_pago WHERE activo = 0");
        $stats['inactivos'] = $stmt->fetch()['total'];
        
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
function getPaymentMethodDetails($pdo, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de método de pago no válido']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                icono,
                color,
                activo,
                created_at
            FROM metodos_pago 
            WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        $metodo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$metodo) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Formatear datos
        $metodoFormatted = [
            'id' => $metodo['id'],
            'nombre' => $metodo['nombre'],
            'icono' => $metodo['icono'],
            'color' => $metodo['color'],
            'activo' => (bool)$metodo['activo'],
            'created_at' => $metodo['created_at'],
            'created_at_formatted' => date('d/m/Y H:i', strtotime($metodo['created_at']))
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $metodoFormatted
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles del método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles del método de pago']);
    }
}

/**
 * Crear un nuevo método de pago
 */
function createPaymentMethod($pdo, $data) {
    try {
        // Validar datos requeridos
        if (empty($data['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es requerido']);
            return;
        }
        
        if (empty($data['color'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El color es requerido']);
            return;
        }
        
        // Verificar si ya existe un método de pago con el mismo nombre
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE nombre = ?");
        $stmt->execute([$data['nombre']]);
        
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe un método de pago con ese nombre']);
            return;
        }
        
        // Insertar nuevo método de pago
        $stmt = $pdo->prepare("
            INSERT INTO metodos_pago (nombre, icono, color, activo, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $activo = isset($data['activo']) ? ($data['activo'] == '1' || $data['activo'] === true ? 1 : 0) : 1;
        $icono = isset($data['icono']) && !empty(trim($data['icono'])) 
            ? trim($data['icono']) 
            : 'fas fa-credit-card';
        
        $stmt->execute([
            $data['nombre'],
            $icono,
            $data['color'],
            $activo
        ]);
        
        $methodId = $pdo->lastInsertId();
        
        // Log de la acción
        error_log("Método de pago creado: {$data['nombre']} (ID: {$methodId}) por usuario {$_SESSION['user_id']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago creado exitosamente',
            'data' => [
                'id' => $methodId,
                'nombre' => $data['nombre'],
                'color' => $data['color'],
                'activo' => (bool)$activo
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el método de pago']);
    }
}

/**
 * Actualizar un método de pago existente
 */
function updatePaymentMethod($pdo, $id, $data) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de método de pago no válido']);
            return;
        }
        
        // Validar datos requeridos
        if (empty($data['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es requerido']);
            return;
        }
        
        if (empty($data['color'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El color es requerido']);
            return;
        }
        
        // Verificar que el método de pago existe
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        $oldMethod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$oldMethod) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Verificar si ya existe otro método de pago con el mismo nombre
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE nombre = ? AND id != ?");
        $stmt->execute([$data['nombre'], $id]);
        
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe otro método de pago con ese nombre']);
            return;
        }
        
        // Actualizar método de pago
        $stmt = $pdo->prepare("
            UPDATE metodos_pago 
            SET nombre = ?, icono = ?, color = ?, activo = ?
            WHERE id = ?
        ");
        
        $activo = isset($data['activo']) ? ($data['activo'] == '1' || $data['activo'] === true ? 1 : 0) : 1;
        $icono = isset($data['icono']) && !empty(trim($data['icono'])) 
            ? trim($data['icono']) 
            : 'fas fa-credit-card';
        
        $stmt->execute([
            $data['nombre'],
            $icono,
            $data['color'],
            $activo,
            $id
        ]);
        
        // Log de la acción
        error_log("Método de pago actualizado: {$oldMethod['nombre']} -> {$data['nombre']} (ID: {$id}) por usuario {$_SESSION['user_id']}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago actualizado exitosamente',
            'data' => [
                'id' => (int)$id,
                'nombre' => $data['nombre'],
                'icono' => $icono,
                'color' => $data['color'],
                'activo' => (bool)$activo
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el método de pago']);
    }
}

/**
 * Cambiar estado activo/inactivo de un método de pago
 */
function togglePaymentMethodStatus($pdo, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de método de pago no válido']);
            return;
        }
        
        // Obtener estado actual
        $stmt = $pdo->prepare("SELECT nombre, activo FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        $method = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$method) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Cambiar estado
        $newStatus = $method['activo'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE metodos_pago SET activo = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        
        $statusText = $newStatus ? 'activado' : 'desactivado';
        
        // Log de la acción
        error_log("Método de pago {$statusText}: {$method['nombre']} (ID: {$id}) por usuario {$_SESSION['user_id']}");
        
        echo json_encode([
            'success' => true,
            'message' => "Método de pago {$statusText} exitosamente",
            'data' => [
                'id' => (int)$id,
                'activo' => (bool)$newStatus
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error cambiando estado del método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar el estado del método de pago']);
    }
}

/**
 * Eliminar un método de pago y sus registros asociados
 */
function deletePaymentMethod($pdo, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de método de pago no válido']);
            return;
        }
        
        // Verificar que el método de pago existe
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        $method = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$method) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Contar registros que se eliminarán (si hay tablas que referencien métodos de pago)
            $totalRecords = 0;
            $deletedRecords = [];
            
            // Aquí se pueden agregar consultas para eliminar registros de otras tablas
            // que referencien este método de pago, por ejemplo:
            // - transacciones
            // - ventas
            // - etc.
            
            // Eliminar el método de pago
            $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id = ?");
            $stmt->execute([$id]);
            
            // Confirmar transacción
            $pdo->commit();
            
            // Log de la acción
            error_log("Método de pago eliminado: {$method['nombre']} (ID: {$id}) por usuario {$_SESSION['user_id']}. Registros asociados eliminados: {$totalRecords}");
            
            echo json_encode([
                'success' => true,
                'message' => "Método de pago \"{$method['nombre']}\" eliminado exitosamente",
                'total_records' => $totalRecords,
                'deleted_records' => $deletedRecords
            ]);
            
        } catch (Exception $e) {
            // Revertir transacción
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Error eliminando método de pago: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el método de pago']);
    }
}
?>
