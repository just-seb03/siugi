<?php

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo = $db->getCuentasConnection();
$pdo_info = $db->getInfoConnection();
$pdo_inv = $db->getInvConnection();


$softwares = $pdo->query("SELECT GLOSA_SOFTWARE FROM software ORDER BY GLOSA_SOFTWARE ASC")->fetchAll();
$sedes = $pdo->query("SELECT ID_SEDE, GLOSA_FISCALIA FROM sedes ORDER BY ID_SEDE ASC")->fetchAll();


$f_busqueda_usuario = isset($_GET['q_usr']) ? trim($_GET['q_usr']) : '';
$f_sw = $_GET['sw'] ?? '';
$f_sd_id = $_GET['sd'] ?? ''; 
$f_tipo_usr = $_GET['tipo_usr'] ?? '';
$f_generica = $_GET['f_gen'] ?? '';
$f_glosa = isset($_GET['glosa']) ? trim($_GET['glosa']) : '';
$f_req = isset($_GET['req']) ? trim($_GET['req']) : '';
$f_bajas = isset($_GET['ver_bajas']) && $_GET['ver_bajas'] == '1' ? 1 : 0; 
$f_alerta_bajas = isset($_GET['alerta_bajas']) && $_GET['alerta_bajas'] == '1' ? 1 : 0; 
$f_pendientes = isset($_GET['ver_pendientes']) && $_GET['ver_pendientes'] == '1' ? 1 : 0;

$export_params = $_GET;
foreach(['sw', 'sd', 'tipo_usr', 'f_gen', 'glosa', 'req', 'q_usr'] as $k) {
    if (isset($export_params[$k]) && $export_params[$k] === '') unset($export_params[$k]);
}
if (isset($export_params['ver_bajas']) && $export_params['ver_bajas'] == 0) unset($export_params['ver_bajas']);
if (isset($export_params['alerta_bajas']) && $export_params['alerta_bajas'] == 0) unset($export_params['alerta_bajas']);
if (isset($export_params['ver_pendientes']) && $export_params['ver_pendientes'] == 0) unset($export_params['ver_pendientes']);
$export_qs = http_build_query($export_params);


$query_u = "SELECT u.id, u.nombre, u.usuario, u.rut, u.cargo, u.ip, s.GLOSA_FISCALIA as Sede ,
            YEAR(CURDATE()) - YEAR(u.fec_nacimiento) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(u.fec_nacimiento, '%m%d')) AS edad
            FROM informatica.usuarios u
            LEFT JOIN informatica.fiscalias f ON u.cod_fiscalia = f.cod_fiscalia
            LEFT JOIN gestor_cuentas.sedes s ON f.cod_fiscalia = s.ID_SEDE
            WHERE 1=1";
$params_u = [];

if ($f_busqueda_usuario !== '') {
    $query_u .= " AND (u.nombre LIKE ? OR u.usuario LIKE ? OR u.rut LIKE ?)";
    $like_term = '%' . $f_busqueda_usuario . '%';
    $params_u[] = $like_term;
    $params_u[] = $like_term;
    $params_u[] = $like_term;
}
if ($f_sd_id !== '') {
    $query_u .= " AND u.cod_fiscalia = ?";
    $params_u[] = $f_sd_id;
}
if ($f_tipo_usr !== '') {
    $query_u .= " AND u.tipo_usuario = ?";
    $params_u[] = $f_tipo_usr;
}

$cuentaFiltersActive = ($f_sw !== '' || $f_generica !== '' || $f_bajas || $f_alerta_bajas || $f_pendientes || $f_glosa !== '' || $f_req !== '');
if ($cuentaFiltersActive) {
    $estado_filtro = "IN (1, 3)";
    if ($f_bajas) $estado_filtro = "= 0";
    if ($f_pendientes) $estado_filtro = "= 3";

    $query_u .= " AND EXISTS (SELECT 1 FROM gestor_cuentas.vista_cuentas_detalle c WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA $estado_filtro";
    if ($f_sw !== '') { $query_u .= " AND c.SOFTWARE = ?"; $params_u[] = $f_sw; }
    if ($f_generica !== '') { $query_u .= " AND c.ES_GENERICA = ?"; $params_u[] = (int)$f_generica; }
    if ($f_glosa !== '') { $query_u .= " AND c.GLOSA_CUENTA = ?"; $params_u[] = $f_glosa; }
    if ($f_req !== '') { $query_u .= " AND c.REQUERIMIENTO_INICIO_CUENTA = ?"; $params_u[] = $f_req; }
    $query_u .= ")";
}

if ($f_alerta_bajas) { $query_u .= " AND u.estado = 1"; } 
elseif (!$f_bajas) { $query_u .= " AND u.estado = 0"; }

$query_u .= " ORDER BY u.nombre ASC";
$stmt_u = $pdo_info->prepare($query_u);
$stmt_u->execute($params_u);
$usuariosData = $stmt_u->fetchAll();

$u_ids = array_column($usuariosData, 'id');
$usuarios_filtrados = count($u_ids) > 0;

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $where = ["1=1"];
    $params = [];

    if ($usuarios_filtrados) {
        $inQuery = implode(',', array_fill(0, count($u_ids), '?'));
        $where[] = "USUARIO IN ($inQuery)";
        $params = array_merge($params, $u_ids);
    } else {
        $where[] = "1=0";
    }

    if ($f_pendientes) { $where[] = "ESTADO_CUENTA = 3"; } 
    elseif ($f_bajas) { $where[] = "ESTADO_CUENTA = 0"; } 
    else { $where[] = "ESTADO_CUENTA IN (1, 3)"; }

    if ($f_sw !== '') { $where[] = "SOFTWARE = ?"; $params[] = $f_sw; }
    if ($f_generica !== '') { $where[] = "ES_GENERICA = ?"; $params[] = (int)$f_generica; }
    if ($f_glosa !== '') { $where[] = "GLOSA_CUENTA = ?"; $params[] = $f_glosa; }
    if ($f_req !== '') { $where[] = "REQUERIMIENTO_INICIO_CUENTA = ?"; $params[] = $f_req; }

    if ($f_sd_id !== '') {
        $f_sd_glosa = '';
        foreach ($sedes as $s) {
            if ($s['ID_SEDE'] == $f_sd_id) { $f_sd_glosa = $s['GLOSA_FISCALIA']; break; }
        }
        if ($f_sd_glosa !== '') { $where[] = "SEDE = ?"; $params[] = $f_sd_glosa; }
    }

    $whereClause = implode(" AND ", $where);
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM vista_cuentas_detalle WHERE $whereClause");
    $stmtCount->execute($params);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    $cuentasData = [];
    if ($totalRecords > 0) {
        $stmtData = $pdo->prepare("SELECT * FROM vista_cuentas_detalle WHERE $whereClause ORDER BY ID_CUENTA DESC LIMIT $limit OFFSET $offset");
        $stmtData->execute($params);
        $cuentasData = $stmtData->fetchAll();
    }


    $page_bienes = isset($_GET['page_bienes']) && is_numeric($_GET['page_bienes']) ? (int)$_GET['page_bienes'] : 1;
    $limit_bienes = 10;
    $offset_bienes = ($page_bienes - 1) * $limit_bienes;

    $where_bienes = ["d.ELIMINADO = 0"];
    $params_bienes = [];

    if ($usuarios_filtrados) {
        $inQuery_bienes = implode(',', array_fill(0, count($u_ids), '?'));
        $where_bienes[] = "u.ID_USUARIO_ASIGNADO IN ($inQuery_bienes)";
        $params_bienes = array_merge($params_bienes, $u_ids);
    } else {
        $where_bienes[] = "1=0"; 
    }

    $whereClause_bienes = implode(" AND ", $where_bienes);

    $sqlCountBienes = "SELECT COUNT(*) FROM gestor_inventario.dispositivos d 
                       LEFT JOIN gestor_inventario.ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION 
                       WHERE $whereClause_bienes";
    $stmtCountBienes = $pdo_inv->prepare($sqlCountBienes);
    $stmtCountBienes->execute($params_bienes);
    $totalRecordsBienes = $stmtCountBienes->fetchColumn();
    $totalPagesBienes = ceil($totalRecordsBienes / $limit_bienes);

    $bienesData = [];
    if ($totalRecordsBienes > 0) {
        $sqlDataBienes = "SELECT d.ID_DISP, sc.GLOSA_SUBCATEGORIA, m.GLOSA_MARCA, d.MODELO, d.SERIE, 
                                  ec.GLOSA_ESTADO, d.FECHA_REGISTRO, s.GLOSA_FISCALIA, usr.nombre as NOMBRE_USUARIO
                           FROM gestor_inventario.dispositivos d
                           LEFT JOIN gestor_inventario.ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION
                           LEFT JOIN gestor_inventario.sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT
                           LEFT JOIN gestor_inventario.sedes s ON u.FISCALIA_UBICACION = s.ID_SEDE
                           LEFT JOIN gestor_inventario.marcas m ON d.ID_MARCA = m.ID_MARCA
                           LEFT JOIN gestor_inventario.estado_cgu ec ON d.ID_ESTADO_CGU = ec.ID_ESTADO_CGU
                           LEFT JOIN informatica.usuarios usr ON u.ID_USUARIO_ASIGNADO = usr.id
                           WHERE $whereClause_bienes and ec.GLOSA_ESTADO='Alta'
                           ORDER BY d.ID_DISP DESC LIMIT $limit_bienes OFFSET $offset_bienes";
        $stmtDataBienes = $pdo_inv->prepare($sqlDataBienes);
        $stmtDataBienes->execute($params_bienes);
        $bienesData = $stmtDataBienes->fetchAll();
    }

    $stmtRut = $pdo_info->prepare("SELECT rut FROM usuarios WHERE id = ? LIMIT 1");

    
    ob_start();
    if ($usuarios_filtrados) {
        foreach ($usuariosData as $u) {
            $foto_url = "/SIUGI/public/avatar/" . rawurlencode($u['usuario']) . ".jpg";
            ?>
            <a href="/SIUGI/menu_usuario?id=<?= $u['id'] ?>" class="user-card-link">
                <div class="user-card-inner">
                    <div class="user-avatar">
                        <img src="<?= $foto_url ?>" onerror="this.onerror=null; this.outerHTML='<i class=\'fas fa-user\'></i>';">
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= htmlspecialchars($u['nombre']) ?></div>
                        <div class="user-rut"><i class="fas fa-id-card mr-1"></i> <?= htmlspecialchars($u['rut']) ?></div>
                        <div class="user-meta mt-1">
                            <span class="user-badge badge-neutral"><i class="fas fa-briefcase mr-1"></i><?= htmlspecialchars($u['cargo'] ?? 'Sin Cargo') ?></span>
                            <span class="user-badge badge-neutral"><i class="fas fa-calendar-alt mr-1"></i><?= htmlspecialchars($u['edad'] ?? 'N/A') ?> años</span>
                            <span class="user-badge badge-neutral"><i class="fas fa-network-wired mr-1"></i><?= htmlspecialchars($u['ip'] ?? 'Sin IP') ?></span>
                            <span class="user-badge badge-location"><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($u['Sede'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="user-arrow"><i class="fas fa-chevron-right"></i></div>
                </div>
            </a>
            <?php
        }
    } else {
        echo '<div class="text-center py-5 text-muted" style="width: 100%;"><i class="fas fa-users-slash fa-3x mb-3" style="opacity:0.2;"></i><h6>No hay usuarios que coincidan con los filtros</h6></div>';
    }
    $users_html = ob_get_clean();

    ob_start();
    if (count($cuentasData) > 0) {
        foreach ($cuentasData as $cta) {
            $usuarioInexistente = empty($cta['NOMBRE_USUARIO']);
            $claseFila = $usuarioInexistente ? 'row-user-inactive hover-animate-row clickable-row' : 'hover-animate-row clickable-row';

            $rut_valor = 'N/A';
            if (!empty($cta['USUARIO'])) {
                $stmtRut->execute([$cta['USUARIO']]);
                if ($resRut = $stmtRut->fetch()) $rut_valor = $resRut['rut'];
            }
            ?>
            <tr class="<?= $claseFila ?>" onclick="window.location='/SIUGI/editar_cuenta?id=<?= $cta['ID_CUENTA'] ?>'">
                <td style="font-weight: 600; color: var(--text-main);">#<?= $cta['ID_CUENTA'] ?></td>
                <td style="font-weight: 600;" class="text-dark row-title"><?= htmlspecialchars($cta['SOFTWARE'] ?? 'N/A') ?></td>
                <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($rut_valor) ?></td>
                <td><?= (isset($cta['ES_GENERICA']) && $cta['ES_GENERICA'] == 1) ? '<span class="interactive-badge badge-generic">Genérica</span>' : '<span style="color: var(--text-muted); font-size: 0.7rem;"><i class="fas fa-user mr-1"></i>Personal</span>' ?></td>
                <td><?= htmlspecialchars($cta['SEDE'] ?? 'N/A') ?></td>
                <td style="font-weight: 600;"><?= $usuarioInexistente ? '<span style="color: #b91c1c;"><i class="fas fa-user-slash mr-1"></i> Baja Institucional</span>' : htmlspecialchars($cta['NOMBRE_USUARIO']) ?></td>
                <td><?= htmlspecialchars($cta['GLOSA_CUENTA']) ?></td>
                <td class="align-middle text-center">
                    <?php if ($cta['ESTADO_CUENTA'] == 1): ?><span class="interactive-badge fecha-ok">Alta</span>
                    <?php elseif ($cta['ESTADO_CUENTA'] == 3): ?><span class="interactive-badge badge-pending">Pendiente</span>
                    <?php else: ?><span class="interactive-badge fecha-alerta">Baja</span><?php endif; ?>
                </td>
                <td class="align-middle text-muted"><?= htmlspecialchars($cta['REQUERIMIENTO_INICIO_CUENTA'] ?? 'N/A') ?></td>
                <td class="align-middle text-muted"><?= htmlspecialchars($cta['REQUERIMIENTO_TERMINO_CUENTA'] ?? 'N/A') ?></td>
                <td class="align-middle text-muted"><?= $cta['FECHA_CREACION'] ? date('d/m/Y', strtotime($cta['FECHA_CREACION'])) : 'N/A' ?></td>
                <td style="white-space: nowrap; text-align: right;">
                    <?php if ($cta['ESTADO_CUENTA'] == 1 || $cta['ESTADO_CUENTA'] == 3): 

                        $cancelUrl = "/SIUGI/src/anular_cuenta?id_cuenta=" . $cta['ID_CUENTA'] . "&id_usuario=" . $cta['USUARIO']; 
                    ?>
                        <a href="<?= $cancelUrl ?>" class="btn-custom btn-secondary-custom interactive-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; height: auto; border-color: #fecaca; color: var(--danger);" onclick="event.stopPropagation(); return confirm('¿Anular esta cuenta?')">
                            <i class="fas fa-ban"></i>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="12"><div class="text-center py-4 text-muted"><i class="fas fa-list fa-2x mb-2" style="opacity: 0.2;"></i><h6 class="mb-1">No se encontraron cuentas</h6></div></td></tr>';
    }
    $tbody_html = ob_get_clean();

    ob_start();
    if ($totalPages > 1):
        $adjacents = 2;
        if ($page > 1) echo '<a href="javascript:void(0)" onclick="fetchData('.($page - 1).', null)" class="page-link-custom">&laquo;</a>';
        echo '<a href="javascript:void(0)" onclick="fetchData(1, null)" class="page-link-custom '.($page == 1 ? 'active' : '').'">1</a>';
        if ($page > ($adjacents + 2)) echo '<span class="page-link-custom ellipsis">...</span>';
        for ($i = max(2, $page - $adjacents); $i <= min($totalPages - 1, $page + $adjacents); $i++) {
            echo '<a href="javascript:void(0)" onclick="fetchData('.$i.', null)" class="page-link-custom '.($page == $i ? 'active' : '').'">' . $i . '</a>';
        }
        if ($page < ($totalPages - $adjacents - 1)) echo '<span class="page-link-custom ellipsis">...</span>';
        echo '<a href="javascript:void(0)" onclick="fetchData('.$totalPages.', null)" class="page-link-custom '.($page == $totalPages ? 'active' : '').'">' . $totalPages . '</a>';
        if ($page < $totalPages) echo '<a href="javascript:void(0)" onclick="fetchData('.($page + 1).', null)" class="page-link-custom">&raquo;</a>';
    endif;
    $pagination_html = ob_get_clean();

    ob_start();
    if (count($bienesData) > 0) {
        foreach ($bienesData as $b) {
            $bStatus = ($b['GLOSA_ESTADO'] === 'Baja') ? 'fecha-alerta' : 'fecha-ok';
            $serieH = !empty($b['SERIE']) ? htmlspecialchars($b['SERIE']) : 'S/N';
            ?>
            <tr class="hover-animate-row clickable-row" onclick="window.location='/SIUGI/editar_bien?id=<?= $b['ID_DISP'] ?>'">
                <td style="font-weight: 600; color: var(--text-main);">#<?= $b['ID_DISP'] ?></td>
                <td style="font-weight: 600;" class="text-dark row-title"><?= htmlspecialchars($b['GLOSA_SUBCATEGORIA'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($b['GLOSA_MARCA'] ?? 'N/A') ?></td>
                <td class="text-muted"><?= htmlspecialchars($b['MODELO'] ?? 'N/A') ?></td>
                <td style="font-family: monospace; font-size: 0.8rem; color: var(--text-muted);"><?= $serieH ?></td>
                <td><?= htmlspecialchars($b['GLOSA_FISCALIA'] ?? 'N/A') ?></td>
                <td style="font-weight: 600;"><?= htmlspecialchars($b['NOMBRE_USUARIO'] ?? 'No Asignado') ?></td>
                <td class="align-middle text-center"><span class="interactive-badge <?= $bStatus ?>"><?= htmlspecialchars($b['GLOSA_ESTADO'] ?? 'N/A') ?></span></td>
                <td class="align-middle text-muted"><?= !empty($b['FECHA_REGISTRO']) ? date('d/m/Y', strtotime($b['FECHA_REGISTRO'])) : 'N/A' ?></td>
                <td style="white-space: nowrap; text-align: right;">
                    <a href="/SIUGI/editar_bien?id=<?= $b['ID_DISP'] ?>" class="btn-custom btn-secondary-custom interactive-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; height: auto;">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="10"><div class="text-center py-4 text-muted"><i class="fas fa-boxes fa-2x mb-2" style="opacity: 0.2;"></i><h6 class="mb-1">No se encontraron bienes</h6></div></td></tr>';
    }
    $tbody_bienes_html = ob_get_clean();

    ob_start();
    if ($totalPagesBienes > 1):
        $adjacents = 2;
        if ($page_bienes > 1) echo '<a href="javascript:void(0)" onclick="fetchData(null, '.($page_bienes - 1).')" class="page-link-custom">&laquo;</a>';
        echo '<a href="javascript:void(0)" onclick="fetchData(null, 1)" class="page-link-custom '.($page_bienes == 1 ? 'active' : '').'">1</a>';
        if ($page_bienes > ($adjacents + 2)) echo '<span class="page-link-custom ellipsis">...</span>';
        for ($i = max(2, $page_bienes - $adjacents); $i <= min($totalPagesBienes - 1, $page_bienes + $adjacents); $i++) {
            echo '<a href="javascript:void(0)" onclick="fetchData(null, '.$i.')" class="page-link-custom '.($page_bienes == $i ? 'active' : '').'">' . $i . '</a>';
        }
        if ($page_bienes < ($totalPagesBienes - $adjacents - 1)) echo '<span class="page-link-custom ellipsis">...</span>';
        echo '<a href="javascript:void(0)" onclick="fetchData(null, '.$totalPagesBienes.')" class="page-link-custom '.($page_bienes == $totalPagesBienes ? 'active' : '').'">' . $totalPagesBienes . '</a>';
        if ($page_bienes < $totalPagesBienes) echo '<a href="javascript:void(0)" onclick="fetchData(null, '.($page_bienes + 1).')" class="page-link-custom">&raquo;</a>';
    endif;
    $pagination_bienes_html = ob_get_clean();

    echo json_encode([
        'tbody' => $tbody_html, 
        'pagination' => $pagination_html, 
        'users_html' => $users_html,
        'tbody_bienes' => $tbody_bienes_html,
        'pagination_bienes' => $pagination_bienes_html
    ]);
    exit; 
}


include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/cuentas_usuarios.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="content-header mb-3 animate-up">
        <div class="container-fluid p-0">
            <h1 class="m-0 font-weight-bold" style="color: var(--text-main); font-size: 1.75rem; letter-spacing: -0.025em;">Cuentas y Usuarios</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Búsqueda y gestión de usuarios y accesos</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid p-0">
            
            <div class="page-layout">
                
                <div class="animate-up" style="animation-delay: 0.1s;">
                    <div class="card card-custom card-custom-1">
                        <div class="card-header-custom">
                            <h5 class="card-title-text"><i class="fas fa-filter mr-2" style="color: var(--text-muted);"></i> Filtros</h5>
                        </div>
                        <form id="filterForm" class="filter-form-vertical">
                            <div class="sidebar-scrollable">
                                <div class="form-group">
                                    <label>Buscar Funcionario</label>
                                    <input type="text" name="q_usr" id="q_usr" class="form-control" placeholder="Nombre, RUT o Usuario..." value="<?= htmlspecialchars($f_busqueda_usuario) ?>" oninput="triggerFilterDebounced()">
                                </div>

                                <div class="form-group mt-3">
                                    <label>Fiscalia</label>
                                    <select name="sd" id="sd" class="form-control" onchange="triggerFilter()">
                                        <option value="">Todas las Fiscalias</option>
                                        <?php foreach ($sedes as $sd): ?>
                                            <option value="<?= htmlspecialchars($sd['ID_SEDE']) ?>" <?= $f_sd_id == $sd['ID_SEDE'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sd['GLOSA_FISCALIA']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <label>Sistema</label>
                                    <select name="sw" id="sw" class="form-control" onchange="triggerFilter()">
                                        <option value="">Todos los sistemas</option>
                                        <?php foreach ($softwares as $sw): ?>
                                            <option value="<?= htmlspecialchars($sw['GLOSA_SOFTWARE']) ?>" <?= $f_sw == $sw['GLOSA_SOFTWARE'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sw['GLOSA_SOFTWARE']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <label>Tipo de Cuenta</label>
                                    <select name="f_gen" id="f_gen" class="form-control" onchange="triggerFilter()">
                                        <option value="">Todas las cuentas</option>
                                        <option value="0" <?= $f_generica === '0' ? 'selected' : '' ?>>Personal</option>
                                        <option value="1" <?= $f_generica === '1' ? 'selected' : '' ?>>Genérica</option>
                                    </select>
                                </div>
                                <div class="form-group mt-3">
                                    <label>Estamentos</label>
                                    <select name="tipo_usr" id="tipo_usr" class="form-control" onchange="triggerFilter()">
                                        <option value="">Cualquier estamento</option>
                                        <option value="1" <?= $f_tipo_usr == '1' ? 'selected' : '' ?>>Fiscal</option>
                                        <option value="2" <?= $f_tipo_usr == '2' ? 'selected' : '' ?>>Funcionario</option>
                                        <option value="3" <?= $f_tipo_usr == '3' ? 'selected' : '' ?>>Alumno en práctica</option>
                                    </select>
                                </div>

                                <hr style="border-color: var(--border); margin: 1.2rem 0;">

                                <div class="form-group">
                                    <label>Glosa Exacta</label>
                                    <input type="text" name="glosa" id="glosa" class="form-control" placeholder="Buscar glosa" value="<?= htmlspecialchars($f_glosa) ?>" oninput="triggerFilterDebounced()">
                                </div>
                                <div class="form-group mt-3">
                                    <label>Req. Inicio Exacto</label>
                                    <input type="text" name="req" id="req" class="form-control" placeholder="Buscar requerimiento" value="<?= htmlspecialchars($f_req) ?>" oninput="triggerFilterDebounced()">
                                </div>

                                <hr style="border-color: var(--border); margin: 1.2rem 0;">

                                <div class="form-group mt-2">
                                    <div class="switch-container">
                                        <span class="text-switch <?= $f_alerta_bajas ? '' : 'active-label' ?>" id="lblAlertasTodas">Sin Alertas</span>
                                        <label class="switch">
                                            <input type="checkbox" name="alerta_bajas" id="alerta_bajas" value="1" onchange="triggerFilter()" <?= $f_alerta_bajas ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="text-switch right <?= $f_alerta_bajas ? 'active-label' : '' ?>" style="<?= $f_alerta_bajas ? 'color: var(--danger);' : '' ?>" id="lblAlertas">Usuarios de Baja</span>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="switch-container">
                                        <span class="text-switch <?= $f_bajas ? '' : 'active-label' ?>" id="lblActivas">Cuentas Activas</span>
                                        <label class="switch">
                                            <input type="checkbox" name="ver_bajas" id="ver_bajas" value="1" onchange="triggerFilter()" <?= $f_bajas ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="text-switch right <?= $f_bajas ? 'active-label' : '' ?>" style="<?= $f_bajas ? 'color: var(--danger);' : '' ?>" id="lblBajas">Dadas de Baja</span>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="switch-container">
                                        <span class="text-switch <?= $f_pendientes ? '' : 'active-label' ?>" id="lblPendientesTodas">Otras</span>
                                        <label class="switch">
                                            <input type="checkbox" name="ver_pendientes" id="ver_pendientes" value="1" onchange="triggerFilter()" <?= $f_pendientes ? 'checked' : '' ?>>
                                            <span class="slider"></span>
                                        </label>
                                        <span class="text-switch right <?= $f_pendientes ? 'active-label' : '' ?>" style="<?= $f_pendientes ? 'color: var(--warning);' : '' ?>" id="lblPendientes">Pendientes</span>
                                    </div>
                                </div>
                                <hr style="border-color: var(--border); margin: 1.2rem 0;">
                                <div class="btn-group-vertical">
                                    <a href="/SIUGI/exportar_cuentas?<?= $export_qs ?>" id="btnExportar" target="_blank" class="btn-custom btn-danger-custom interactive-btn">
                                        <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                                    </a>
                                    <button type="button" class="btn-custom btn-secondary-custom interactive-btn mt-2" onclick="limpiarFiltros()">
                                        <i class="fas fa-eraser mr-2"></i> Limpiar Filtros
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="animate-up" style="animation-delay: 0.2s;">
                    <div class="card card-custom card-custom-2" style="height: calc(100vh - 120px);">
                        <div class="card-header-custom">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title-text"><i class="fas fa-users mr-2" style="color: var(--text-muted);"></i> Usuarios Resultantes</h5>
                                <div class="zoom-control-container">
                                    <i class="fas fa-search-minus"></i>
                                    <input type="range" class="zoom-slider" id="zoomRange" min="0.8" max="1.5" step="0.1" value="1">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <span class="badge border bg-white" id="userCountBadge" style="font-size: 0.75rem; color: var(--text-main);"><?= count($usuariosData) ?> encontrados</span>
                        </div>
                        <div class="card-body p-0 ajax-container-wrapper" id="usersContainerWrapper">
                            <div class="users-list-container" id="usersList">
                                <div id="usersZoomWrapper">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="row mt-4">
                <div class="col-12 animate-up" style="animation-delay: 0.3s;">
                    <div class="card card-custom card-custom-3">
                        <div class="card-header-custom">
                            <h5 class="card-title-text"><i class="fas fa-list-alt mr-2" style="color: var(--text-muted);"></i> Detalle de Cuentas Resultantes</h5>
                        </div>
                        <div id="tableContainer" class="ajax-container-wrapper card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th><th>SISTEMA</th><th>RUT</th><th>TIPO</th><th>SEDE</th><th>USUARIO ASIGNADO</th>
                                            <th>GLOSA_CUENTA</th><th style="text-align: center;">ESTADO</th><th>REQ. INICIO</th>
                                            <th>REQ. TÉRMINO</th><th>CREACIÓN</th><th style="text-align: right;">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cuentasTbody">
                                        </tbody>
                                </table>
                            </div>
                            <div id="paginationContainer" class="pagination-custom"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12 animate-up" style="animation-delay: 0.4s;">
                    <div class="card card-custom card-custom-3">
                        <div class="card-header-custom">
                            <h5 class="card-title-text"><i class="fas fa-boxes mr-2" style="color: var(--text-muted);"></i> Detalle de Bienes Resultantes</h5>
                        </div>
                        <div id="tableBienesContainer" class="ajax-container-wrapper card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th><th>SUBCATEGORÍA</th><th>MARCA</th><th>MODELO</th><th>SERIE</th>
                                            <th>SEDE</th><th>USUARIO ASIGNADO</th><th style="text-align: center;">ESTADO</th>
                                            <th>FECHA REG.</th><th style="text-align: right;">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bienesTbody">
                                        </tbody>
                                </table>
                            </div>
                            <div id="paginationBienesContainer" class="pagination-custom"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script src="/SIUGI/public/assets/js/cuentas_usuarios.js"></script>

<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>