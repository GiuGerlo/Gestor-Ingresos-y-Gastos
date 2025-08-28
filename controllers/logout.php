<?php
session_start();

// Configurar zona horaria Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Incluir archivos necesarios
require_once '../config/connect.php';
require_once 'auth.php';

try {
    // Registrar el logout en los logs si el usuario está logueado
    if (isset($_SESSION['user_id'])) {
        logAccesoRol($_SESSION['user_id'], 'auth', 'Logout exitoso');
    }
    
    // Cerrar sesión del usuario
    logoutUser();
    
    // Mensaje de despedida
    session_start(); // Reiniciar sesión para mostrar mensaje
    $_SESSION['info_message'] = 'Has cerrado sesión correctamente. ¡Hasta pronto!';
    
    // Redireccionar al login
    header('Location: ../index.php');
    exit();
    
} catch (Exception $e) {
    // Error al cerrar sesión
    error_log("Error en logout: " . $e->getMessage());
    
    // Forzar limpieza de sesión
    session_destroy();
    
    // Redireccionar con mensaje de error
    session_start();
    $_SESSION['warning_message'] = 'Sesión cerrada con advertencias.';
    header('Location: ../index.php');
    exit();
}
?>
