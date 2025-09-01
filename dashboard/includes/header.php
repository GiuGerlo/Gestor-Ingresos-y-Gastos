<?php
/**
 * HEADER DINÁMICO DEL DASHBOARD
 * =============================
 * Include para el header que se adapta según la página actual
 */

// Detectar la página actual
$current_script = basename($_SERVER['PHP_SELF'], '.php');
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determinar la ruta base según el nivel de directorio
$base_path_calc = '';
if ($current_dir !== 'dashboard') {
    $base_path_calc = '../';
}

// Usar la variable $base_path si está definida, sino usar la calculada
$final_base_path = isset($base_path) ? $base_path : $base_path_calc;

// Configurar títulos según la página
$page_titles = [
    'admin' => ['Panel de Administración', 'fas fa-crown text-warning'],
    'index' => ['Dashboard', 'fas fa-chart-line text-primary'],
    'usuarios' => ['Gestión de Usuarios', 'fas fa-users text-primary'],
    'categorias' => ['Gestión de Categorías', 'fas fa-tags text-success'],
    'metodos-pago' => ['Métodos de Pago', 'fas fa-credit-card text-warning'],
    'ingresos' => ['Gestión de Ingresos', 'fas fa-plus-circle text-success'],
    'gastos' => ['Gestión de Gastos', 'fas fa-minus-circle text-warning'],
    'gastos-fijos' => ['Gastos Fijos', 'fas fa-calendar-alt text-danger'],
    'reportes' => ['Reportes Financieros', 'fas fa-chart-bar text-info'],
    'estadisticas' => ['Estadísticas', 'fas fa-chart-pie text-info']
];

// Determinar el título de la página
$page_key = isset($current_page) ? $current_page : $current_script;
if ($current_dir !== 'dashboard' && $current_script === 'index') {
    $page_key = $current_dir;
}

$title_info = $page_titles[$page_key] ?? ['Dashboard', 'fas fa-home text-primary'];
$page_title = $title_info[0];
$page_icon = $title_info[1];

// Meta título para el navegador
$browser_title = $page_title . ' - Ahorritoo';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $browser_title; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $final_base_path; ?>../assets/img/logo-original.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS personalizado -->
    <link href="<?php echo $final_base_path; ?>../assets/css/style.css" rel="stylesheet">
    
    <!-- Meta tags para SEO y responsividad -->
    <meta name="description" content="Sistema de gestión de finanzas personales">
    <meta name="keywords" content="finanzas, gestión, ingresos, gastos, dashboard">
    <meta name="author" content="Ahorritoo">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Configuración de tema -->
    <meta name="theme-color" content="#6548D5">
    <meta name="msapplication-navbutton-color" content="#6548D5">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Preconectar a CDNs para mejor rendimiento -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Variables CSS dinámicas -->
    <style>
        :root {
            --primary-color: #6548D5;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Indicador de carga -->
    <div id="loading" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white" style="z-index: 9999; display: none !important;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
