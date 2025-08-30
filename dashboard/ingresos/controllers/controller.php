<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit();
}

// Incluir conexión a la base de datos
require_once '../../../config/connect.php';

// Obtener el método HTTP y la acción
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

// Configurar tipo de contenido para JSON
header('Content-Type: application/json');

try {
    // Manejar diferentes métodos HTTP
    switch ($method) {
        case 'GET':
            handleGet($pdo, $action, $user_id);
            break;
        case 'POST':
            handlePost($pdo, $action, $user_id);
            break;
        case 'PUT':
            handlePut($pdo, $action, $user_id);
            break;
        case 'DELETE':
            handleDelete($pdo, $action, $user_id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    error_log("Error en controlador ingresos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

/**
 * Manejar peticiones GET
 */
function handleGet($pdo, $action, $user_id) {
    switch ($action) {
        case 'list':
        case '':
            getAllIncomes($pdo, $user_id);
            break;
        case 'stats':
            getIncomeStats($pdo, $user_id);
            break;
        case 'details':
            getIncomeDetails($pdo, $user_id, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones POST (crear ingreso)
 */
function handlePost($pdo, $action, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            createIncome($pdo, $user_id, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones PUT (actualizar ingreso)
 */
function handlePut($pdo, $action, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            updateIncome($pdo, $user_id, $_GET['id'] ?? 0, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones DELETE (eliminar ingreso)
 */
function handleDelete($pdo, $action, $user_id) {
    switch ($action) {
        case 'delete':
            deleteIncome($pdo, $user_id, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Obtener todos los ingresos del usuario
 */
function getAllIncomes($pdo, $user_id) {
    try {
        // Obtener filtros de mes/año si están presentes
        $mes = $_GET['mes'] ?? null;
        $ano = $_GET['ano'] ?? null;
        
        $sql = "
            SELECT 
                i.id,
                i.fecha,
                i.descripcion,
                i.monto,
                c.nombre as categoria,
                mp.nombre as metodo_pago,
                mp.color as color_metodo,
                i.created_at
            FROM ingresos i
            JOIN categorias c ON i.categoria_id = c.id
            JOIN metodos_pago mp ON i.metodo_pago_id = mp.id
            WHERE i.user_id = ?
        ";
        
        $params = [$user_id];
        
        // Agregar filtros si están presentes
        if ($mes && $ano && is_numeric($mes) && is_numeric($ano)) {
            $sql .= " AND YEAR(i.fecha) = ? AND MONTH(i.fecha) = ?";
            $params[] = $ano;
            $params[] = $mes;
        }
        
        $sql .= " ORDER BY i.fecha DESC, i.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $ingresos,
            'total' => count($ingresos),
            'filtros' => [
                'mes' => $mes,
                'ano' => $ano
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo ingresos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener ingresos']);
    }
}

/**
 * Obtener estadísticas de ingresos del usuario
 */
function getIncomeStats($pdo, $user_id) {
    try {
        $stats = [];
        
        // Total ingresos este mes
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total_mes
            FROM ingresos 
            WHERE user_id = ? AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
        ");
        $stmt->execute([$user_id]);
        $stats['total_mes'] = $stmt->fetch()['total_mes'];
        
        // Total ingresos año actual
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total_ano
            FROM ingresos 
            WHERE user_id = ? AND YEAR(fecha) = YEAR(CURDATE())
        ");
        $stmt->execute([$user_id]);
        $stats['total_ano'] = $stmt->fetch()['total_ano'];
        
        // Promedio mensual
        $stmt = $pdo->prepare("
            SELECT COALESCE(AVG(monto), 0) as promedio_mensual
            FROM ingresos 
            WHERE user_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ");
        $stmt->execute([$user_id]);
        $stats['promedio_mensual'] = $stmt->fetch()['promedio_mensual'];
        
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
 * Crear un nuevo ingreso
 */
function createIncome($pdo, $user_id, $data) {
    try {
        // Validar datos requeridos
        if (empty($data['fecha'])) {
            http_response_code(400);
            echo json_encode(['error' => 'La fecha es requerida']);
            return;
        }
        
        if (empty($data['descripcion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'La descripción es requerida']);
            return;
        }
        
        if (empty($data['monto']) || $data['monto'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El monto debe ser mayor a 0']);
            return;
        }
        
        if (empty($data['categoria_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'La categoría es requerida']);
            return;
        }
        
        if (empty($data['metodo_pago_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El método de pago es requerido']);
            return;
        }
        
        // Verificar que la categoría existe y es de tipo ingreso
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ? AND tipo = 'ingreso' AND activo = 1");
        $stmt->execute([$data['categoria_id']]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Categoría no válida']);
            return;
        }
        
        // Verificar que el método de pago existe y está activo
        $stmt = $pdo->prepare("SELECT id FROM metodos_pago WHERE id = ? AND activo = 1");
        $stmt->execute([$data['metodo_pago_id']]);
        if (!$stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Método de pago no válido']);
            return;
        }
        
        // Insertar nuevo ingreso
        $stmt = $pdo->prepare("
            INSERT INTO ingresos (user_id, fecha, categoria_id, descripcion, monto, metodo_pago_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $data['fecha'],
            $data['categoria_id'],
            $data['descripcion'],
            $data['monto'],
            $data['metodo_pago_id']
        ]);
        
        $incomeId = $pdo->lastInsertId();
        
        // Log de la acción
        error_log("Ingreso creado: {$data['descripcion']} - \${$data['monto']} (ID: {$incomeId}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Ingreso agregado exitosamente',
            'data' => [
                'id' => $incomeId,
                'descripcion' => $data['descripcion'],
                'monto' => $data['monto'],
                'fecha' => $data['fecha']
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando ingreso: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el ingreso']);
    }
}

/**
 * Obtener detalles de un ingreso específico
 */
function getIncomeDetails($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de ingreso no válido']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                i.id,
                i.fecha,
                i.descripcion,
                i.monto,
                i.categoria_id,
                i.metodo_pago_id,
                c.nombre as categoria,
                mp.nombre as metodo_pago,
                mp.color as color_metodo,
                i.created_at
            FROM ingresos i
            JOIN categorias c ON i.categoria_id = c.id
            JOIN metodos_pago mp ON i.metodo_pago_id = mp.id
            WHERE i.id = ? AND i.user_id = ?
        ");
        
        $stmt->execute([$id, $user_id]);
        $ingreso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ingreso) {
            http_response_code(404);
            echo json_encode(['error' => 'Ingreso no encontrado']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $ingreso
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles del ingreso: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles del ingreso']);
    }
}

/**
 * Actualizar un ingreso existente
 */
function updateIncome($pdo, $user_id, $id, $data) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de ingreso no válido']);
            return;
        }
        
        // Verificar que el ingreso existe y pertenece al usuario
        $stmt = $pdo->prepare("SELECT id FROM ingresos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Ingreso no encontrado']);
            return;
        }
        
        // Validar datos (similar a createIncome)
        // ... validaciones ...
        
        // Actualizar ingreso
        $stmt = $pdo->prepare("
            UPDATE ingresos 
            SET fecha = ?, categoria_id = ?, descripcion = ?, monto = ?, metodo_pago_id = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([
            $data['fecha'],
            $data['categoria_id'],
            $data['descripcion'],
            $data['monto'],
            $data['metodo_pago_id'],
            $id,
            $user_id
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Ingreso actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando ingreso: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el ingreso']);
    }
}

/**
 * Eliminar un ingreso
 */
function deleteIncome($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de ingreso no válido']);
            return;
        }
        
        // Verificar que el ingreso existe y pertenece al usuario
        $stmt = $pdo->prepare("SELECT descripcion FROM ingresos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $ingreso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ingreso) {
            http_response_code(404);
            echo json_encode(['error' => 'Ingreso no encontrado']);
            return;
        }
        
        // Eliminar ingreso
        $stmt = $pdo->prepare("DELETE FROM ingresos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        
        // Log de la acción
        error_log("Ingreso eliminado: {$ingreso['descripcion']} (ID: {$id}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => "Ingreso \"{$ingreso['descripcion']}\" eliminado exitosamente"
        ]);
        
    } catch (PDOException $e) {
        error_log("Error eliminando ingreso: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el ingreso']);
    }
}
?>
