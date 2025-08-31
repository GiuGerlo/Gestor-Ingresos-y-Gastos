<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Incluir conexión a la base de datos
require_once '../../../config/connect.php';

// Configurar cabeceras
header('Content-Type: application/json');

// Configurar zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    // Enrutar según método HTTP
    switch ($method) {
        case 'GET':
            handleGet($pdo, $action, $user_id);
            break;
        case 'POST':
            handlePost($pdo, $action, $user_id);
            break;
        case 'PUT':
        case 'PATCH':
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
    error_log("Error en gastos fijos controller: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor']);
}

/**
 * Manejar peticiones GET (obtener datos)
 */
function handleGet($pdo, $action, $user_id) {
    switch ($action) {
        case 'list':
            getAllFixedExpenses($pdo, $user_id);
            break;
        case 'details':
            getFixedExpenseDetails($pdo, $user_id, $_GET['id'] ?? 0);
            break;
        case 'stats':
            getFixedExpenseStats($pdo, $user_id);
            break;
        case 'alerts':
            getPaymentAlerts($pdo, $user_id);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones POST (crear)
 */
function handlePost($pdo, $action, $user_id) {
    switch ($action) {
        case 'create':
            createFixedExpense($pdo, $user_id, $_POST);
            break;
        case 'update':
            updateFixedExpense($pdo, $user_id, $_POST['id'] ?? 0, $_POST);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones PUT (actualizar)
 */
function handlePut($pdo, $action, $user_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            updateFixedExpense($pdo, $user_id, $input['id'] ?? 0, $input);
            break;
        case 'toggle':
            toggleFixedExpenseStatus($pdo, $user_id, $input['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones DELETE (eliminar)
 */
function handleDelete($pdo, $action, $user_id) {
    switch ($action) {
        case 'delete':
            deleteFixedExpense($pdo, $user_id, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Obtener todos los gastos fijos del usuario
 */
function getAllFixedExpenses($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                gf.id,
                gf.dia_mes,
                gf.nombre,
                gf.monto,
                gf.cuotas_restantes,
                DATE_FORMAT(gf.mes_ultima_cuota, '%Y-%m') as mes_ultima_cuota,
                gf.activo,
                gf.notificado,
                gf.created_at,
                gf.updated_at
            FROM gastos_fijos gf
            WHERE gf.user_id = ?
            ORDER BY gf.activo DESC, gf.dia_mes ASC, gf.id ASC
        ");
        
        $stmt->execute([$user_id]);
        $gastos_fijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar datos adicionales
        foreach ($gastos_fijos as &$gasto) {
            $gasto['proximo_pago'] = calculateNextPayment($gasto['dia_mes']);
            $gasto['dias_hasta_pago'] = calculateDaysUntilPayment($gasto['dia_mes']);
            $gasto['estado_cuotas'] = getQuotaStatus($gasto);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $gastos_fijos
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo gastos fijos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener gastos fijos']);
    }
}

/**
 * Obtener detalles específicos de un gasto fijo
 */
function getFixedExpenseDetails($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto fijo no válido']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                gf.id,
                gf.dia_mes,
                gf.nombre,
                gf.monto,
                gf.cuotas_restantes,
                DATE_FORMAT(gf.mes_ultima_cuota, '%Y-%m') as mes_ultima_cuota,
                gf.activo,
                gf.notificado,
                gf.created_at,
                gf.updated_at
            FROM gastos_fijos gf
            WHERE gf.id = ? AND gf.user_id = ?
        ");
        
        $stmt->execute([$id, $user_id]);
        $gasto_fijo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gasto_fijo) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto fijo no encontrado']);
            return;
        }
        
        // Agregar información adicional
        $gasto_fijo['proximo_pago'] = calculateNextPayment($gasto_fijo['dia_mes']);
        $gasto_fijo['dias_hasta_pago'] = calculateDaysUntilPayment($gasto_fijo['dia_mes']);
        $gasto_fijo['estado_cuotas'] = getQuotaStatus($gasto_fijo);
        
        echo json_encode([
            'success' => true,
            'data' => $gasto_fijo
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles del gasto fijo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles del gasto fijo']);
    }
}

/**
 * Crear un nuevo gasto fijo
 */
function createFixedExpense($pdo, $user_id, $data) {
    try {
        // Validar datos requeridos
        if (empty($data['dia_mes']) || !is_numeric($data['dia_mes'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El día del mes es requerido y debe ser un número']);
            return;
        }
        
        if ($data['dia_mes'] < 1 || $data['dia_mes'] > 31) {
            http_response_code(400);
            echo json_encode(['error' => 'El día del mes debe estar entre 1 y 31']);
            return;
        }
        
        if (empty($data['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre del gasto fijo es requerido']);
            return;
        }
        
        if (empty($data['monto']) || $data['monto'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El monto debe ser mayor a 0']);
            return;
        }
        
        // Validar cuotas restantes si está presente
        if (!empty($data['cuotas_restantes']) && $data['cuotas_restantes'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Las cuotas restantes deben ser mayor a 0']);
            return;
        }
        
        // Verificar que no exista otro gasto fijo con el mismo nombre para este usuario
        $stmt = $pdo->prepare("SELECT id FROM gastos_fijos WHERE user_id = ? AND nombre = ? AND activo = 1");
        $stmt->execute([$user_id, $data['nombre']]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe un gasto fijo activo con ese nombre']);
            return;
        }
        
        // Preparar datos para inserción
        $cuotas_restantes = !empty($data['cuotas_restantes']) ? (int)$data['cuotas_restantes'] : null;
        $mes_ultima_cuota = !empty($data['mes_ultima_cuota']) ? $data['mes_ultima_cuota'] . '-01' : null;
        $activo = isset($data['activo']) ? 1 : 0;
        
        // Insertar gasto fijo
        $stmt = $pdo->prepare("
            INSERT INTO gastos_fijos (
                user_id, dia_mes, nombre, monto, cuotas_restantes, 
                mes_ultima_cuota, activo, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $user_id,
            $data['dia_mes'],
            $data['nombre'],
            $data['monto'],
            $cuotas_restantes,
            $mes_ultima_cuota,
            $activo
        ]);
        
        $new_id = $pdo->lastInsertId();
        
        // Log de la acción
        error_log("Gasto fijo creado: {$data['nombre']} (ID: {$new_id}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => "Gasto fijo \"{$data['nombre']}\" creado exitosamente",
            'id' => $new_id
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando gasto fijo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el gasto fijo']);
    }
}

/**
 * Actualizar un gasto fijo existente
 */
function updateFixedExpense($pdo, $user_id, $id, $data) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto fijo no válido']);
            return;
        }
        
        // Verificar que el gasto fijo existe y pertenece al usuario
        $stmt = $pdo->prepare("SELECT nombre FROM gastos_fijos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto fijo no encontrado']);
            return;
        }
        
        // Validar datos requeridos
        if (empty($data['dia_mes']) || !is_numeric($data['dia_mes'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El día del mes es requerido y debe ser un número']);
            return;
        }
        
        if ($data['dia_mes'] < 1 || $data['dia_mes'] > 31) {
            http_response_code(400);
            echo json_encode(['error' => 'El día del mes debe estar entre 1 y 31']);
            return;
        }
        
        if (empty($data['nombre'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre del gasto fijo es requerido']);
            return;
        }
        
        if (empty($data['monto']) || $data['monto'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El monto debe ser mayor a 0']);
            return;
        }
        
        // Validar cuotas restantes si está presente
        if (!empty($data['cuotas_restantes']) && $data['cuotas_restantes'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Las cuotas restantes deben ser mayor a 0']);
            return;
        }
        
        // Verificar que no exista otro gasto fijo con el mismo nombre (excepto el actual)
        $stmt = $pdo->prepare("SELECT id FROM gastos_fijos WHERE user_id = ? AND nombre = ? AND id != ? AND activo = 1");
        $stmt->execute([$user_id, $data['nombre'], $id]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe otro gasto fijo activo con ese nombre']);
            return;
        }
        
        // Preparar datos para actualización
        $cuotas_restantes = !empty($data['cuotas_restantes']) ? (int)$data['cuotas_restantes'] : null;
        $mes_ultima_cuota = !empty($data['mes_ultima_cuota']) ? $data['mes_ultima_cuota'] . '-01' : null;
        $activo = isset($data['activo']) ? 1 : 0;
        
        // Actualizar gasto fijo
        $stmt = $pdo->prepare("
            UPDATE gastos_fijos 
            SET dia_mes = ?, nombre = ?, monto = ?, cuotas_restantes = ?, 
                mes_ultima_cuota = ?, activo = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([
            $data['dia_mes'],
            $data['nombre'],
            $data['monto'],
            $cuotas_restantes,
            $mes_ultima_cuota,
            $activo,
            $id,
            $user_id
        ]);
        
        // Log de la acción
        error_log("Gasto fijo actualizado: {$data['nombre']} (ID: {$id}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => "Gasto fijo \"{$data['nombre']}\" actualizado exitosamente"
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando gasto fijo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el gasto fijo']);
    }
}

/**
 * Eliminar un gasto fijo
 */
function deleteFixedExpense($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto fijo no válido']);
            return;
        }
        
        // Verificar que el gasto fijo existe y pertenece al usuario
        $stmt = $pdo->prepare("SELECT nombre FROM gastos_fijos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $gasto_fijo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gasto_fijo) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto fijo no encontrado']);
            return;
        }
        
        // Eliminar gasto fijo
        $stmt = $pdo->prepare("DELETE FROM gastos_fijos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        
        // Log de la acción
        error_log("Gasto fijo eliminado: {$gasto_fijo['nombre']} (ID: {$id}) por usuario {$user_id}");
        
        echo json_encode([
            'success' => true,
            'message' => "Gasto fijo \"{$gasto_fijo['nombre']}\" eliminado exitosamente"
        ]);
        
    } catch (PDOException $e) {
        error_log("Error eliminando gasto fijo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el gasto fijo']);
    }
}

/**
 * Obtener estadísticas de gastos fijos
 */
function getFixedExpenseStats($pdo, $user_id) {
    try {
        $stats = [];
        
        // Total mensual de gastos fijos activos
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(monto), 0) as total_mensual
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$user_id]);
        $stats['total_mensual'] = $stmt->fetch()['total_mensual'];
        
        // Cantidad de gastos fijos activos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as activos
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1
        ");
        $stmt->execute([$user_id]);
        $stats['activos'] = $stmt->fetch()['activos'];
        
        // Cantidad de gastos fijos con cuotas
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as con_cuotas
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1 AND cuotas_restantes IS NOT NULL
        ");
        $stmt->execute([$user_id]);
        $stats['con_cuotas'] = $stmt->fetch()['con_cuotas'];
        
        // Próximos pagos (próximos 7 días)
        $today = date('j'); // Día del mes actual
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as proximos_pagos
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1 
            AND dia_mes BETWEEN ? AND ?
        ");
        $stmt->execute([$user_id, $today, $today + 7]);
        $stats['proximos_pagos'] = $stmt->fetch()['proximos_pagos'];
        
        // Gastos que terminan pronto (menos de 3 cuotas)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as terminando_pronto
            FROM gastos_fijos 
            WHERE user_id = ? AND activo = 1 
            AND cuotas_restantes IS NOT NULL AND cuotas_restantes <= 3
        ");
        $stmt->execute([$user_id]);
        $stats['terminando_pronto'] = $stmt->fetch()['terminando_pronto'];
        
        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo estadísticas de gastos fijos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener estadísticas']);
    }
}

/**
 * Obtener alertas de próximos pagos
 */
function getPaymentAlerts($pdo, $user_id) {
    try {
        $today = date('j'); // Día del mes actual
        
        $stmt = $pdo->prepare("
            SELECT 
                gf.id,
                gf.dia_mes,
                gf.nombre,
                gf.monto,
                gf.cuotas_restantes,
                CASE 
                    WHEN gf.dia_mes = ? THEN 'HOY'
                    WHEN gf.dia_mes BETWEEN ? AND ? THEN CONCAT('En ', (gf.dia_mes - ?), ' días')
                    ELSE 'Futuro'
                END as urgencia
            FROM gastos_fijos gf
            WHERE gf.user_id = ? AND gf.activo = 1
            AND gf.dia_mes BETWEEN ? AND ?
            ORDER BY gf.dia_mes ASC
        ");
        
        $stmt->execute([
            $today,           // Para HOY
            $today + 1,       // Para En X días (inicio)
            $today + 7,       // Para En X días (fin)
            $today,           // Para cálculo de días
            $user_id,         // Usuario
            $today,           // Rango inicio
            $today + 7        // Rango fin
        ]);
        
        $alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $alertas
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo alertas de pagos: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener alertas']);
    }
}

/**
 * Alternar el estado activo/inactivo de un gasto fijo
 */
function toggleFixedExpenseStatus($pdo, $user_id, $id) {
    try {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de gasto fijo no válido']);
            return;
        }
        
        // Obtener estado actual
        $stmt = $pdo->prepare("SELECT nombre, activo FROM gastos_fijos WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        $gasto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gasto) {
            http_response_code(404);
            echo json_encode(['error' => 'Gasto fijo no encontrado']);
            return;
        }
        
        // Alternar estado
        $nuevo_estado = $gasto['activo'] ? 0 : 1;
        
        $stmt = $pdo->prepare("
            UPDATE gastos_fijos 
            SET activo = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        
        $stmt->execute([$nuevo_estado, $id, $user_id]);
        
        $estado_texto = $nuevo_estado ? 'activado' : 'desactivado';
        
        echo json_encode([
            'success' => true,
            'message' => "Gasto fijo \"{$gasto['nombre']}\" {$estado_texto} exitosamente",
            'nuevo_estado' => $nuevo_estado
        ]);
        
    } catch (PDOException $e) {
        error_log("Error alternando estado del gasto fijo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar el estado del gasto fijo']);
    }
}

/**
 * Calcular la fecha del próximo pago
 */
function calculateNextPayment($dia_mes) {
    $today = new DateTime();
    $current_day = (int)$today->format('j');
    $current_month = (int)$today->format('n');
    $current_year = (int)$today->format('Y');
    
    // Si ya pasó el día este mes, calcular para el próximo mes
    if ($current_day >= $dia_mes) {
        $next_month = $current_month + 1;
        $next_year = $current_year;
        
        if ($next_month > 12) {
            $next_month = 1;
            $next_year++;
        }
    } else {
        $next_month = $current_month;
        $next_year = $current_year;
    }
    
    // Ajustar si el día no existe en el mes (ej: 31 en febrero)
    $last_day_of_month = (int)date('t', mktime(0, 0, 0, $next_month, 1, $next_year));
    $payment_day = min($dia_mes, $last_day_of_month);
    
    $next_payment = new DateTime();
    $next_payment->setDate($next_year, $next_month, $payment_day);
    
    return $next_payment->format('Y-m-d');
}

/**
 * Calcular días hasta el próximo pago
 */
function calculateDaysUntilPayment($dia_mes) {
    $today = new DateTime();
    $next_payment = new DateTime(calculateNextPayment($dia_mes));
    
    $interval = $today->diff($next_payment);
    return $interval->days;
}

/**
 * Obtener estado de las cuotas
 */
function getQuotaStatus($gasto_fijo) {
    if (!$gasto_fijo['cuotas_restantes']) {
        return 'indefinido';
    }
    
    if ($gasto_fijo['cuotas_restantes'] <= 1) {
        return 'ultima_cuota';
    }
    
    if ($gasto_fijo['cuotas_restantes'] <= 3) {
        return 'terminando';
    }
    
    return 'normal';
}
?>
