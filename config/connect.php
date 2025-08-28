<?php
// Iniciar sesión solo si es necesario y si no se mandaron headers
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Detectar entorno (local o producción)
$isLocal = (!isset($_SERVER['HTTP_HOST'])) ||
    in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ||
    str_contains($_SERVER['HTTP_HOST'], 'local');

if ($isLocal) {
    // Configuración para entorno local
    $server = 'localhost:3306';
    $username = 'root';
    $password = '';
    $database = 'gestor_finanzas'; // Asegúrate de que esta base de datos exista en tu entorno local
} else {
    // Configuración para entorno de producción (completá esto después)
    $server = 'localhost'; // Generalmente es 'localhost' en la mayoría de los hostings
    $username = '';
    $database = '';
    $password = '';
}

// Crear conexión PDO
try {
    $pdo = new PDO(
        "mysql:host=$server;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// También crear $conn para compatibilidad con algunos controladores
$conn = $pdo;
