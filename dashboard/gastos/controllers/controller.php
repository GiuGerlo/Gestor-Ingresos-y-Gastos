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
    error_log("Error en controlador gastos: " . $e->getMessage());
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
            getAllExpenses($pdo, $user_id);
            break;
        case 'stats':
            getExpenseStats($pdo, $user_id);
            break;
        case 'details':
            getExpenseDetails($pdo, $user_id, $_GET['id'] ?? 0);
            break;
        case 'analysis':
            getExpenseAnalysis($pdo, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones POST (crear gasto)
 */
function handlePost($pdo, $action, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            createExpense($pdo, $user_id, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones PUT (actualizar gasto)
 */
function handlePut($pdo, $action, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            updateExpense($pdo, $user_id, $_GET['id'] ?? 0, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones DELETE (eliminar gasto)
 */
function handleDelete($pdo, $action, $user_id) {
    switch ($action) {
        case 'delete':
            deleteExpense($pdo, $user_id, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Obtener todos los gastos del usuario
 */
function getAllExpenses($pdo, $user_id) {
    try {
        // Obtener filtros de mes/año si están presentes
        $mes = $_GET['mes'] ?? null;
        $ano = $_GET['ano'] ?? null;
        
        $sql = "
            SELECT 
                g.id,
                DATE_FORMAT(g.fecha, '%Y-%m-%d') as fecha,
                g.descripcion,
                g.monto,
                c.nombre as categoria,
                mp.nombre as metodo_pago,
                mp.color as color_metodo,
                g.created_at
            FROM gastos g
            JOIN categorias c ON g.categoria_id = c.id
            JOIN metodos_pago mp ON g.metodo_pago_id = mp.id
            WHERE g.user_id = ?
        ";
        
        $params = [$user_id];
        
        // Agregar filtros si están presentes
        if ($mes && $ano && is_numeric($mes) && is_numeric($ano)) {
            $sql .= " AND YEAR(g.fecha) = ? AND MONTH(g.fecha) = ?";
            $params[] = $ano;
            $params[] = $mes;
        }
        
        $sql .= " ORDER BY g.fecha DESC, g.id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $gastos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $gastos,
            'total' => count($gastos),
            'filtros' => [
                'mes' => $mes,
                'ano' => $ano
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo gastos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener gastos']);
    }
}

/**
 * Obtener estadísticas de gastos del usuario
 */
function getExpenseStats($pdo, $user_id) {
    try {
        $stats = [];
        
        // Total gastos este mes
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total_mes
            FROM gastos 
            WHERE user_id = ? AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
        ");
        $stmt->execute([$user_id]);
        $stats['total_mes'] = $stmt->fetch()['total_mes'];
        
        // Total gastos año actual
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total_ano
            FROM gastos 
            WHERE user_id = ? AND YEAR(fecha) = YEAR(CURDATE())
        ");
        $stmt->execute([$user_id]);
        $stats['total_ano'] = $stmt->fetch()['total_ano'];
        
        // Promedio mensual
        $stmt = $pdo->prepare("
            SELECT COALESCE(AVG(monto), 0) as promedio_mensual
            FROM gastos 
            WHERE user_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        ");
        $stmt->execute([$user_id]);
        $stats['promedio_mensual'] = $stmt->fetch()['promedio_mensual'];
        
        // Gastos por categoría este mes
        $stmt = $pdo->prepare("
            SELECT c.nombre, COALESCE(SUM(g.monto), 0) as total
            FROM categorias c
            LEFT JOIN gastos g ON c.id = g.categoria_id AND g.user_id = ? AND YEAR(g.fecha) = YEAR(CURDATE()) AND MONTH(g.fecha) = MONTH(CURDATE())
            WHERE c.tipo = 'gasto' AND c.activo = 1
            GROUP BY c.id, c.nombre
            ORDER BY total DESC
        ");
        $stmt->execute([$user_id]);
        $stats['por_categoria'] = $stmt->fetchAll();
        
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
 * Obtener análisis de gastos
 */
function getExpenseAnalysis($pdo, $user_id) {
    try {
        $analysis = [];
        
        // Gastos por mes últimos 6 meses
        $stmt = $pdo->prepare("
            SELECT 
                YEAR(fecha) as ano,
                MONTH(fecha) as mes,
                MONTHNAME(fecha) as nombre_mes,
                COALESCE(SUM(monto), 0) as total
            FROM gastos 
            WHERE user_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY YEAR(fecha), MONTH(fecha)
            ORDER BY ano DESC, mes DESC
        ");
        $stmt->execute([$user_id]);
        $analysis['por_mes'] = $stmt->fetchAll();
        
        // Top 5 categorías este año
        $stmt = $pdo->prepare("
            SELECT 
                c.nombre,
                COALESCE(SUM(g.monto), 0) as total,
                COUNT(g.id) as cantidad
            FROM categorias c
            LEFT JOIN gastos g ON c.id = g.categoria_id AND g.user_id = ? AND YEAR(g.fecha) = YEAR(CURDATE())
            WHERE c.tipo = 'gasto' AND c.activo = 1
            GROUP BY c.id, c.nombre
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $analysis['top_categorias'] = $stmt->fetchAll();
        
        // Gastos por método de pago este mes
        $stmt = $pdo->prepare("
            SELECT 
                mp.nombre,
                mp.color,
                COALESCE(SUM(g.monto), 0) as total
            FROM metodos_pago mp
            LEFT JOIN gastos g ON mp.id = g.metodo_pago_id AND g.user_id = ? AND YEAR(g.fecha) = YEAR(CURDATE()) AND MONTH(g.fecha) = MONTH(CURDATE())
            WHERE mp.activo = 1
            GROUP BY mp.id, mp.nombre, mp.color
            ORDER BY total DESC
        ");
        $stmt->execute([$user_id]);
        $analysis['por_metodo_pago'] = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $analysis
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo análisis: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener análisis']);
    }
}

/**
 * Crear un nuevo gasto
 */
function createExpense($pdo, $user_id, $data) {
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
        
        // Verificar que la categoría existe y es de tipo gasto
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ? AND tipo = 'gasto' AND activo = 1");
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
        
        // Insertar nuevo gasto
        $stmt = $pdo->prepare("
            INSERT INTO gastos (user_id, fecha, categoria_id, descripcion, monto, metodo_pago_id, created_at) 
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
        
        $expenseId = $pdo->lastInsertId();
        
        // Log de la acción
        error_log("Gasto creado: {$data['descripcion']} - \${$data['monto']} (ID: {$expenseId}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => 'Gasto registrado exitosamente',
            'data' => [
                'id' => $expenseId,
                'descripcion' => $data['descripcion'],
                'monto' => $data['monto'],
                'fecha' => $data['fecha']
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando gasto: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el gasto']);
    }
}

/**
 * Obtener detalles de un gasto específico
 */
function getExpenseDetails($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto no válido']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                g.id,
                DATE_FORMAT(g.fecha, '%Y-%m-%d') as fecha,
                g.descripcion,
                g.monto,
                g.categoria_id,
                g.metodo_pago_id,
                c.nombre as categoria,
                mp.nombre as metodo_pago,
                mp.color as color_metodo,
                g.created_at
            FROM gastos g
            JOIN categorias c ON g.categoria_id = c.id
            JOIN metodos_pago mp ON g.metodo_pago_id = mp.id
            WHERE g.id = ? AND g.user_id = ?
        ");
        
        $stmt->execute([$id, $user_id]);
        $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gasto) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto no encontrado']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $gasto
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles del gasto: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles del gasto']);
    }
}

/**
 * Actualizar un gasto existente
 */
function updateExpense($pdo, $user_id, $id, $data) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto no válido']);
            return;
        }
        
        // Verificar que el gasto existe y pertenece al usuario
        $stmt = $pdo->prepare("SELECT id FROM gastos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto no encontrado']);
            return;
        }
        
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
        
        // Verificar que la categoría existe y es de tipo gasto
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ? AND tipo = 'gasto' AND activo = 1");
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
        
        // Actualizar gasto
        $stmt = $pdo->prepare("
            UPDATE gastos 
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
            'message' => 'Gasto actualizado exitosamente'
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando gasto: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el gasto']);
    }
}

/**
 * Eliminar un gasto
 */
function deleteExpense($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto no válido']);
            return;
        }
        
        // Verificar que el gasto existe y pertenece al usuario
        $stmt = $pdo->prepare("SELECT descripcion FROM gastos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gasto) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto no encontrado']);
            return;
        }
        
        // Eliminar gasto
        $stmt = $pdo->prepare("DELETE FROM gastos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        
        // Log de la acción
        error_log("Gasto eliminado: {$gasto['descripcion']} (ID: {$id}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => "Gasto \"{$gasto['descripcion']}\" eliminado exitosamente"
        ]);
        
    } catch (PDOException $e) {
        error_log("Error eliminando gasto: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el gasto']);
    }
}
?>
