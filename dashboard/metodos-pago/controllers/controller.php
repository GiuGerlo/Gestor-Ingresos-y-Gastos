<?php
header('Content-Type: application/json');

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación y rol
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Incluir archivo de conexión
require_once '../../../config/connect.php';

try {
    // Obtener la acción solicitada
    $action = $_REQUEST['action'] ?? '';

    switch ($action) {
        case 'getAll':
            getAllPaymentMethods();
            break;
        case 'getOne':
            getOnePaymentMethod();
            break;
        case 'create':
            createPaymentMethod();
            break;
        case 'update':
            updatePaymentMethod();
            break;
        case 'delete':
            deletePaymentMethod();
            break;
        default:
            throw new Exception('Acción no válida');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

/**
 * Obtener todos los métodos de pago
 */
function getAllPaymentMethods() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as created_at_formatted
            FROM metodos_pago 
            ORDER BY id DESC
        ");
        
        $stmt->execute();
        $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir activo a boolean para JavaScript
        foreach ($paymentMethods as &$paymentMethod) {
            $paymentMethod['activo'] = (bool)$paymentMethod['activo'];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $paymentMethods,
            'message' => 'Métodos de pago obtenidos correctamente'
        ]);
        
    } catch (PDOException $e) {
        throw new Exception('Error al obtener métodos de pago: ' . $e->getMessage());
    }
}

/**
 * Obtener un método de pago específico
 */
function getOnePaymentMethod() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de método de pago no válido'
        ]);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as created_at_formatted
            FROM metodos_pago 
            WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        $paymentMethod = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$paymentMethod) {
            echo json_encode([
                'success' => false,
                'message' => 'Método de pago no encontrado'
            ]);
            return;
        }
        
        // Convertir activo a boolean
        $paymentMethod['activo'] = (bool)$paymentMethod['activo'];
        
        echo json_encode([
            'success' => true,
            'data' => $paymentMethod,
            'message' => 'Método de pago obtenido correctamente'
        ]);
        
    } catch (PDOException $e) {
        throw new Exception('Error al obtener método de pago: ' . $e->getMessage());
    }
}

/**
 * Crear un nuevo método de pago
 */
function createPaymentMethod() {
    global $pdo;
    
    // Validar datos de entrada
    $nombre = trim($_POST['nombre'] ?? '');
    $color = trim($_POST['color'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre es obligatorio'
        ]);
        return;
    }
    
    if (strlen($nombre) > 50) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre no puede exceder 50 caracteres'
        ]);
        return;
    }
    
    if (empty($color)) {
        echo json_encode([
            'success' => false,
            'message' => 'El color es obligatorio'
        ]);
        return;
    }
    
    // Validar formato de color hexadecimal
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        echo json_encode([
            'success' => false,
            'message' => 'El color debe ser un código hexadecimal válido (ej: #FF0000)'
        ]);
        return;
    }
    
    try {
        // Verificar si ya existe un método de pago con el mismo nombre
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE nombre = ?");
        $stmt->execute([$nombre]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un método de pago con este nombre'
            ]);
            return;
        }
        
        // Insertar nuevo método de pago
        $stmt = $pdo->prepare("
            INSERT INTO metodos_pago (nombre, color) 
            VALUES (?, ?)
        ");
        
        $stmt->execute([$nombre, $color]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago creado exitosamente',
            'data' => [
                'id' => $pdo->lastInsertId(),
                'nombre' => $nombre,
                'color' => $color
            ]
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un método de pago con este nombre'
            ]);
        } else {
            throw new Exception('Error al crear método de pago: ' . $e->getMessage());
        }
    }
}

/**
 * Actualizar un método de pago existente
 */
function updatePaymentMethod() {
    global $pdo;
    
    // Validar datos de entrada
    $id = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $activo = $_POST['activo'] ?? '1';
    
    if (!$id || !is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de método de pago no válido'
        ]);
        return;
    }
    
    // Validaciones
    if (empty($nombre)) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre es obligatorio'
        ]);
        return;
    }
    
    if (strlen($nombre) > 50) {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre no puede exceder 50 caracteres'
        ]);
        return;
    }
    
    if (empty($color)) {
        echo json_encode([
            'success' => false,
            'message' => 'El color es obligatorio'
        ]);
        return;
    }
    
    // Validar formato de color hexadecimal
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        echo json_encode([
            'success' => false,
            'message' => 'El color debe ser un código hexadecimal válido (ej: #FF0000)'
        ]);
        return;
    }
    
    // Convertir activo a boolean
    $activo = ($activo === '1' || $activo === 'true') ? 1 : 0;
    
    try {
        // Verificar si el método de pago existe
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        
        if (!$stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Método de pago no encontrado'
            ]);
            return;
        }
        
        // Verificar si ya existe otro método de pago con el mismo nombre
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM metodos_pago WHERE nombre = ? AND id != ?");
        $stmt->execute([$nombre, $id]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe otro método de pago con este nombre'
            ]);
            return;
        }
        
        // Actualizar método de pago
        $stmt = $pdo->prepare("
            UPDATE metodos_pago 
            SET nombre = ?, color = ?, activo = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$nombre, $color, $activo, $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago actualizado exitosamente',
            'data' => [
                'id' => $id,
                'nombre' => $nombre,
                'color' => $color,
                'activo' => $activo
            ]
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe otro método de pago con este nombre'
            ]);
        } else {
            throw new Exception('Error al actualizar método de pago: ' . $e->getMessage());
        }
    }
}

/**
 * Eliminar un método de pago
 */
function deletePaymentMethod() {
    global $pdo;
    
    $id = $_POST['id'] ?? null;
    
    if (!$id || !is_numeric($id)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de método de pago no válido'
        ]);
        return;
    }
    
    try {
        // Verificar si el método de pago existe
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        $paymentMethod = $stmt->fetch();
        
        if (!$paymentMethod) {
            echo json_encode([
                'success' => false,
                'message' => 'Método de pago no encontrado'
            ]);
            return;
        }
        
        // Verificar si el método de pago está siendo usado en ingresos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE metodo_pago_id = ?");
        $stmt->execute([$id]);
        $ingresosCount = $stmt->fetchColumn();
        
        // Verificar si el método de pago está siendo usado en gastos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM gastos WHERE metodo_pago_id = ?");
        $stmt->execute([$id]);
        $gastosCount = $stmt->fetchColumn();
        
        if ($ingresosCount > 0 || $gastosCount > 0) {
            echo json_encode([
                'success' => false,
                'message' => "No se puede eliminar el método de pago '{$paymentMethod['nombre']}' porque está siendo utilizado en " . 
                           ($ingresosCount + $gastosCount) . " transacción(es). Considere desactivarlo en su lugar."
            ]);
            return;
        }
        
        // Eliminar método de pago
        $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => "Método de pago '{$paymentMethod['nombre']}' eliminado exitosamente"
        ]);
        
    } catch (PDOException $e) {
        throw new Exception('Error al eliminar método de pago: ' . $e->getMessage());
    }
}

/**
 * Función auxiliar para sanitizar entrada
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Función auxiliar para validar formato de color
 */
function isValidHexColor($color) {
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
}
?>
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Incluir la conexión a la base de datos
require_once '../../../config/connect.php';

// Verificar que el usuario esté logueado y sea superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para realizar esta acción']);
    exit();
}

// Headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener un método de pago específico
                getPaymentMethod($_GET['id']);
            } else {
                // Obtener todos los métodos de pago
                getAllPaymentMethods();
            }
            break;
            
        case 'POST':
            // Crear nuevo método de pago
            createPaymentMethod();
            break;
            
        case 'PUT':
            // Actualizar método de pago existente
            updatePaymentMethod();
            break;
            
        case 'DELETE':
            // Eliminar método de pago
            deletePaymentMethod();
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
}

/**
 * Obtener todos los métodos de pago
 */
function getAllPaymentMethods() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as created_at_formatted
            FROM metodos_pago 
            ORDER BY nombre ASC
        ");
        
        $stmt->execute();
        $methods = $stmt->fetchAll();
        
        // Procesar los datos para mejor visualización
        foreach ($methods as &$method) {
            $method['activo'] = (bool) $method['activo'];
            $method['estado'] = $method['activo'] ? 'activo' : 'inactivo';
        }
        
        echo json_encode([
            'success' => true,
            'data' => $methods,
            'total' => count($methods)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener métodos de pago: ' . $e->getMessage()]);
    }
}

/**
 * Obtener un método de pago específico
 */
function getPaymentMethod($id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as created_at_formatted
            FROM metodos_pago 
            WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        $method = $stmt->fetch();
        
        if (!$method) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        $method['activo'] = (bool) $method['activo'];
        $method['estado'] = $method['activo'] ? 'activo' : 'inactivo';
        
        echo json_encode([
            'success' => true,
            'data' => $method
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener método de pago: ' . $e->getMessage()]);
    }
}

/**
 * Crear nuevo método de pago
 */
function createPaymentMethod() {
    global $pdo;
    
    // Leer datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre es requerido']);
        return;
    }
    
    if (!isset($input['color']) || empty(trim($input['color']))) {
        http_response_code(400);
        echo json_encode(['error' => 'El color es requerido']);
        return;
    }
    
    $nombre = trim($input['nombre']);
    $color = trim($input['color']);
    $activo = isset($input['activo']) ? (bool) $input['activo'] : true;
    
    // Validar que el nombre no esté duplicado
    try {
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE nombre = ?");
        $stmt->execute([$nombre]);
        
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un método de pago con este nombre']);
            return;
        }
        
        // Validar formato de color hex
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            http_response_code(400);
            echo json_encode(['error' => 'El formato del color debe ser hexadecimal (#RRGGBB)']);
            return;
        }
        
        // Insertar nuevo método de pago
        $stmt = $pdo->prepare("
            INSERT INTO metodos_pago (nombre, color, activo) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$nombre, $color, $activo]);
        
        $newId = $pdo->lastInsertId();
        
        // Obtener el método de pago recién creado
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as created_at_formatted
            FROM metodos_pago 
            WHERE id = ?
        ");
        
        $stmt->execute([$newId]);
        $newMethod = $stmt->fetch();
        $newMethod['activo'] = (bool) $newMethod['activo'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago creado exitosamente',
            'data' => $newMethod
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Código para violación de restricción única
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un método de pago con este nombre']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear método de pago: ' . $e->getMessage()]);
        }
    }
}

/**
 * Actualizar método de pago existente
 */
function updatePaymentMethod() {
    global $pdo;
    
    // Leer datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar que se proporcione el ID
    if (!isset($input['id']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID del método de pago es requerido']);
        return;
    }
    
    $id = (int) $input['id'];
    
    // Verificar que el método de pago existe
    try {
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Validar datos
        if (!isset($input['nombre']) || empty(trim($input['nombre']))) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es requerido']);
            return;
        }
        
        if (!isset($input['color']) || empty(trim($input['color']))) {
            http_response_code(400);
            echo json_encode(['error' => 'El color es requerido']);
            return;
        }
        
        $nombre = trim($input['nombre']);
        $color = trim($input['color']);
        $activo = isset($input['activo']) ? (bool) $input['activo'] : true;
        
        // Validar que el nombre no esté duplicado (excepto el actual)
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE nombre = ? AND id != ?");
        $stmt->execute([$nombre, $id]);
        
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otro método de pago con este nombre']);
            return;
        }
        
        // Validar formato de color hex
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            http_response_code(400);
            echo json_encode(['error' => 'El formato del color debe ser hexadecimal (#RRGGBB)']);
            return;
        }
        
        // Actualizar método de pago
        $stmt = $pdo->prepare("
            UPDATE metodos_pago 
            SET nombre = ?, color = ?, activo = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$nombre, $color, $activo, $id]);
        
        // Obtener el método de pago actualizado
        $stmt = $pdo->prepare("
            SELECT 
                id,
                nombre,
                color,
                activo,
                created_at,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as created_at_formatted
            FROM metodos_pago 
            WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        $updatedMethod = $stmt->fetch();
        $updatedMethod['activo'] = (bool) $updatedMethod['activo'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago actualizado exitosamente',
            'data' => $updatedMethod
        ]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe otro método de pago con este nombre']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar método de pago: ' . $e->getMessage()]);
        }
    }
}

/**
 * Eliminar método de pago
 */
function deletePaymentMethod() {
    global $pdo;
    
    // Leer datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar que se proporcione el ID
    if (!isset($input['id']) || empty($input['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID del método de pago es requerido']);
        return;
    }
    
    $id = (int) $input['id'];
    
    try {
        // Verificar que el método de pago existe
        $stmt = $pdo->prepare("SELECT nombre FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        $method = $stmt->fetch();
        
        if (!$method) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado']);
            return;
        }
        
        // Verificar si el método de pago está siendo usado en transacciones
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_uses FROM (
                SELECT metodo_pago_id FROM ingresos WHERE metodo_pago_id = ?
                UNION ALL
                SELECT metodo_pago_id FROM gastos WHERE metodo_pago_id = ?
            ) as uses
        ");
        $stmt->execute([$id, $id]);
        $result = $stmt->fetch();
        
        if ($result['total_uses'] > 0) {
            http_response_code(409);
            echo json_encode([
                'error' => 'No se puede eliminar este método de pago porque está siendo usado en ' . $result['total_uses'] . ' transacción(es)',
                'suggestion' => 'Puedes desactivarlo en lugar de eliminarlo'
            ]);
            return;
        }
        
        // Eliminar método de pago
        $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Método de pago "' . $method['nombre'] . '" eliminado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar método de pago: ' . $e->getMessage()]);
    }
}
?>
