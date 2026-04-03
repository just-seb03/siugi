<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../src/AuthMiddleware.php'; 
verificarSesion(); 

$id_usuario_sesion = $_SESSION['userId'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIUGI</title>
    <link rel="shortcut icon" href="/SIUGI/public/assets/images/favicon_.png" title="Favicon"/>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="/SIUGI/public/assets/css/plugins/all.min.css">
    <link rel="stylesheet" href="/SIUGI/public/assets/css/plugins/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="/SIUGI/public/assets/css/style.css">
    <link rel="stylesheet" href="/SIUGI/public/assets/css/inicio.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="/SIUGI/public/assets/js/plugins/jquery.min.js"></script>
    <script src="/SIUGI/public/assets/js/plugins/bootstrap.bundle.min.js"></script>
    <script src="/SIUGI/public/assets/js/plugins/adminlte.js"></script>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="/SIUGI/logout" class="nav-link" role="button">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar elevation-0">
        <a href="/SIUGI/index" class="brand-link">
            <img src="/SIUGI/public/assets/images/logo_200_b.png" width="120" style="opacity: 1" alt="Logo">
        </a>

        <div class="sidebar" style="padding: 0;">
            <div class="user-panel d-flex">
                <div class="info w-100 pl-3">
                    <a href="<?= $id_usuario_sesion > 0 ? "/SIUGI/menu_usuario?id=" . $id_usuario_sesion : "#" ?>" class="user-link-inline">
                        <?php 
                            $user_session = $_SESSION['user'] ?? ''; 
                            $foto_path = "/SIUGI/public/avatar/" . $user_session . ".jpg"; 
                            $full_path = $_SERVER['DOCUMENT_ROOT'] . $foto_path;

                            if (!empty($user_session) && file_exists($full_path)): 
                        ?>
                            <div class="avatar-container">
                                <img src="<?php echo $foto_path; ?>" class="user-avatar-aside" alt="User Photo">
                            </div>
                        <?php else: ?>
                            <i class="fa fa-user-circle" style="font-size: 1.8rem; margin-right: 10px;"></i> 
                        <?php endif; ?>

                        <span class="user-name-span"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Invitado'); ?></span>
                    </a>
                </div>
            </div>

            <nav class="mt-3 pb-4">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-header">Operaciones</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-desktop"></i>
                            <p>Bienes <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="/SIUGI/estadisticas_registros" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Estadísticas y Registros</p></a></li>
                            <li class="nav-item"><a href="/SIUGI/agregar_bienes" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Agregar Bienes</p></a></li>
                            <li class="nav-item"><a href="/SIUGI/consultar_bienes" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Consultar Bienes</p></a></li>
                        </ul>
                    </li> 

                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Usuarios <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="/SIUGI/alertas_informacion" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Alertas e Información</p></a></li>
                            <li class="nav-item"><a href="/SIUGI/cuentas_usuarios" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Cuentas y Usuarios</p></a></li>
                        </ul>
                    </li> 

                    <li class="nav-header">Configuración</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Mantenedores <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="/SIUGI/parametros_bienes" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Parámetros de Inventario</p></a></li>
                            <li class="nav-item"><a href="/SIUGI/parametros_usuarios" class="nav-link"><i class="fas fa-circle nav-icon"></i><p>Parámetros de Usuarios</p></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">