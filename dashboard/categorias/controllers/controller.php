<?php
/**
 * CONTROLADOR: Gestión de Categorías
 * ==================================
 * Maneja todas las operaciones CRUD para categorías
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
    error_log("Error en controlador categorías: " . $e->getMessage());
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
            getAllCategories($pdo);
            break;
        case 'stats':
            getCategoryStats($pdo);
            break;
        case 'details':
            getCategoryDetails($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones POST (crear categoría)
 */
function handlePost($pdo, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'create':
            createCategory($pdo, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones PUT (actualizar categoría)
 */
function handlePut($pdo, $action) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($action) {
        case 'update':
            updateCategory($pdo, $input, $_GET['id'] ?? 0);
            break;
        case 'toggle-status':
            toggleCategoryStatus($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Manejar peticiones DELETE (eliminar categoría)
 */
function handleDelete($pdo, $action) {
    switch ($action) {
        case 'delete':
            deleteCategory($pdo, $_GET['id'] ?? 0);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
}

/**
 * Obtener todas las categorías
 */
function getAllCategories($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                id,
                nombre,
                tipo,
                activo,
                created_at
            FROM categorias 
            ORDER BY created_at DESC
        ");
        
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear datos para la tabla
        $categoriasFormatted = [];
        foreach ($categorias as $categoria) {
            $categoriasFormatted[] = [
                'id' => $categoria['id'],
                'nombre' => $categoria['nombre'],
                'tipo' => $categoria['tipo'],
                'activo' => (bool)$categoria['activo'],
                'created_at' => $categoria['created_at'],
                'created_at_formatted' => date('d/m/Y H:i', strtotime($categoria['created_at']))
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $categoriasFormatted,
            'total' => count($categoriasFormatted)
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo categorías: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener categorías']);
    }
}

/**
 * Obtener estadísticas de categorías
 */
function getCategoryStats($pdo) {
    try {
        $stats = [];
        
        // Total categorías
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
        $stats['total'] = $stmt->fetch()['total'];
        
        // Categorías activas
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
        $stats['activas'] = $stmt->fetch()['total'];
        
        // Categorías de ingresos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE tipo = 'ingreso'");
        $stats['ingresos'] = $stmt->fetch()['total'];
        
        // Categorías de gastos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE tipo = 'gasto'");
        $stats['gastos'] = $stmt->fetch()['total'];
        
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
 * Obtener detalles de una categoría específica
 */
function getCategoryDetails($pdo, $categoryId) {
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id, nombre, tipo, activo, created_at
            FROM categorias 
            WHERE id = ?
        ");
        $stmt->execute([$categoryId]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoria) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoría no encontrada']);
            return;
        }
        
        // Formatear fechas para mostrar en el modal
        $categoria['created_at_formatted'] = date('d/m/Y H:i:s', strtotime($categoria['created_at']));
        
        // Convertir activo a booleano
        $categoria['activo'] = (bool)$categoria['activo'];
        
        echo json_encode([
            'success' => true,
            'data' => $categoria
        ]);
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalles de la categoría: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener detalles de la categoría']);
    }
}

/**
 * Crear nueva categoría
 */
function createCategory($pdo, $data) {
    // Validar datos requeridos
    if (!isset($data['nombre']) || !isset($data['tipo'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos incompletos. Nombre y tipo son obligatorios.'
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
    
    if (!in_array($data['tipo'], ['ingreso', 'gasto'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Tipo no válido. Debe ser "ingreso" o "gasto".'
        ]);
        return;
    }
    
    try {
        // Verificar si el nombre ya existe para el mismo tipo
        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ? AND tipo = ?");
        $stmt->execute([trim($data['nombre']), $data['tipo']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe una categoría con ese nombre para el tipo seleccionado.'
            ]);
            return;
        }
        
        // Determinar estado activo (por defecto 1)
        $activo = isset($data['activo']) ? (int)$data['activo'] : 1;
        
        // Insertar categoría
        $stmt = $pdo->prepare("
            INSERT INTO categorias (nombre, tipo, activo) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            trim($data['nombre']),
            $data['tipo'],
            $activo
        ]);
        
        $categoryId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => [
                'id' => $categoryId,
                'nombre' => trim($data['nombre']),
                'tipo' => $data['tipo'],
                'activo' => $activo
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Error creando categoría: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor al crear la categoría.'
        ]);
    }
}

/**
 * Actualizar categoría
 */
function updateCategory($pdo, $data, $categoryId) {
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de categoría requerido'
        ]);
        return;
    }
    
    // Verificar que la categoría existe
    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
    $stmt->execute([$categoryId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Categoría no encontrada'
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
    
    if (isset($data['tipo']) && !in_array($data['tipo'], ['ingreso', 'gasto'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Tipo no válido. Debe ser "ingreso" o "gasto".'
        ]);
        return;
    }
    
    try {
        // Construir query dinámicamente
        $fields = [];
        $values = [];
        
        if (isset($data['nombre'])) {
            // Verificar que el nombre no esté en uso por otra categoría del mismo tipo
            $stmt = $pdo->prepare("
                SELECT id FROM categorias 
                WHERE nombre = ? AND tipo = (SELECT tipo FROM categorias WHERE id = ?) AND id != ?
            ");
            $stmt->execute([trim($data['nombre']), $categoryId, $categoryId]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'error' => 'El nombre ya está en uso por otra categoría del mismo tipo'
                ]);
                return;
            }
            $fields[] = "nombre = ?";
            $values[] = trim($data['nombre']);
        }
        
        if (isset($data['tipo'])) {
            // Verificar que no haya conflicto de nombre si se cambia el tipo
            if (isset($data['nombre'])) {
                $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ? AND tipo = ? AND id != ?");
                $stmt->execute([trim($data['nombre']), $data['tipo'], $categoryId]);
                if ($stmt->fetch()) {
                    http_response_code(409);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Ya existe una categoría con ese nombre para el tipo seleccionado'
                    ]);
                    return;
                }
            }
            $fields[] = "tipo = ?";
            $values[] = $data['tipo'];
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
        
        $values[] = $categoryId;
        
        $stmt = $pdo->prepare("
            UPDATE categorias 
            SET " . implode(', ', $fields) . " 
            WHERE id = ?
        ");
        
        $stmt->execute($values);
        
        echo json_encode([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente'
        ]);
        
    } catch (PDOException $e) {
        error_log("Error actualizando categoría: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor al actualizar la categoría'
        ]);
    }
}

/**
 * Cambiar estado activo/inactivo de la categoría
 */
function toggleCategoryStatus($pdo, $categoryId) {
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de categoría requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE categorias SET activo = !activo WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Estado de la categoría actualizado'
        ]);
        
    } catch (PDOException $e) {
        error_log("Error cambiando estado de la categoría: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Error al cambiar estado de la categoría']);
    }
}

/**
 * Eliminar categoría
 */
function deleteCategory($pdo, $categoryId) {
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de categoría requerido'
        ]);
        return;
    }
    
    try {
        // Verificar que la categoría existe
        $stmt = $pdo->prepare("SELECT id, nombre, tipo FROM categorias WHERE id = ?");
        $stmt->execute([$categoryId]);
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$categoria) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Categoría no encontrada'
            ]);
            return;
        }
        
        // Iniciar transacción para garantizar consistencia
        $pdo->beginTransaction();
        
        // Contar registros asociados antes de eliminar
        $registrosEliminados = [
            'ingresos' => 0,
            'gastos' => 0
        ];
        
        // Eliminar ingresos asociados (si la tabla existe)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM ingresos WHERE categoria_id = ?");
            $stmt->execute([$categoryId]);
            $registrosEliminados['ingresos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM ingresos WHERE categoria_id = ?");
            $stmt->execute([$categoryId]);
        } catch (PDOException $e) {
            // Tabla no existe o no tiene categoria_id, continuar
        }
        
        // Eliminar gastos asociados (si la tabla existe)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM gastos WHERE categoria_id = ?");
            $stmt->execute([$categoryId]);
            $registrosEliminados['gastos'] = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM gastos WHERE categoria_id = ?");
            $stmt->execute([$categoryId]);
        } catch (PDOException $e) {
            // Tabla no existe o no tiene categoria_id, continuar
        }
        
        // Finalmente, eliminar la categoría
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        if ($stmt->rowCount() > 0) {
            // Confirmar transacción
            $pdo->commit();
            
            // Preparar mensaje detallado
            $totalRegistros = array_sum($registrosEliminados);
            $detalles = [];
            
            foreach ($registrosEliminados as $tipo => $cantidad) {
                if ($cantidad > 0) {
                    $detalles[] = "$cantidad " . ucfirst($tipo);
                }
            }
            
            $mensajeDetalle = '';
            if ($totalRegistros > 0) {
                $mensajeDetalle = ' y ' . $totalRegistros . ' registros asociados (' . implode(', ', $detalles) . ')';
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Categoría '{$categoria['nombre']}' eliminada exitosamente{$mensajeDetalle}",
                'deleted_category' => [
                    'id' => $categoria['id'],
                    'nombre' => $categoria['nombre'],
                    'tipo' => $categoria['tipo']
                ],
                'deleted_records' => $registrosEliminados,
                'total_records' => $totalRegistros
            ]);
        } else {
            $pdo->rollback();
            echo json_encode([
                'success' => false,
                'error' => 'No se pudo eliminar la categoría'
            ]);
        }
        
    } catch (PDOException $e) {
        // Rollback en caso de error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        error_log("Error eliminando categoría: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor al eliminar la categoría'
        ]);
    }
}
?>
