<?php
ob_start();
session_start();


require_once __DIR__ . '/../config/db.php';
$db = new DatabaseConnection();

$pdo_cuentas    = $db->getCuentasConnection();
$pdo_inventario = $db->getInvConnection();


if (isset($_GET['ajax_cuentas'])) {
    header('Content-Type: application/json');
    $id_usuario = (int)$_GET['id_usuario'];
    $f_bajas = isset($_GET['ver_bajas']) && $_GET['ver_bajas'] == '1' ? 1 : 0;
    
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $cond_estado = $f_bajas ? "ESTADO_CUENTA = 0" : "ESTADO_CUENTA IN (1, 3)";

    try {
        $stmt_cuentas = $pdo_cuentas->prepare("SELECT * FROM vista_cuentas_detalle WHERE USUARIO = ? AND $cond_estado ORDER BY FECHA_CREACION ASC LIMIT $limit OFFSET $offset");
        $stmt_cuentas->execute([$id_usuario]);
        $cuentasData = $stmt_cuentas->fetchAll();

        $stmt_count = $pdo_cuentas->prepare("SELECT COUNT(*) FROM vista_cuentas_detalle WHERE USUARIO = ? AND $cond_estado");
        $stmt_count->execute([$id_usuario]);
        $totalRecords = $stmt_count->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);
    } catch (PDOException $e) {
        echo json_encode([
            'tbody' => '<tr><td colspan="10" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error BD: ' . htmlspecialchars($e->getMessage()) . '</td></tr>',
            'pagination' => ''
        ]);
        exit;
    }

    ob_start();
    if (count($cuentasData) > 0) {
        foreach ($cuentasData as $cta) {
            $usuarioInexistente = empty($cta['NOMBRE_USUARIO']);
            $claseFila = $usuarioInexistente ? 'row-user-inactive hover-animate-row clickable-row' : 'hover-animate-row clickable-row';
            ?>
            <tr class="<?= $claseFila ?>" onclick="window.location='/SIUGI/editar_cuenta?id=<?= $cta['ID_CUENTA'] ?>'">
                <td class="align-middle font-weight-bold" style="color: var(--primary);">#<?= $cta['ID_CUENTA'] ?></td>
                <td class="align-middle"><?= htmlspecialchars($cta['SEDE'] ?? 'N/A') ?></td>
                <td class="align-middle font-weight-bold row-title"><?= htmlspecialchars($cta['SOFTWARE'] ?? 'N/A') ?></td>
                <td class="align-middle">
                    <?php if (isset($cta['ES_GENERICA']) && $cta['ES_GENERICA'] == 1): ?>
                        <span class="interactive-badge badge-generic">Genérica</span>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.75rem;"><i class="fas fa-user mr-1"></i>Personal</span>
                    <?php endif; ?>
                </td>
                <td class="align-middle"><?= htmlspecialchars($cta['GLOSA_CUENTA']) ?></td>
                <td class="align-middle text-center">
                    <?php if ($cta['ESTADO_CUENTA'] == 1): ?>
                        <span class="interactive-badge fecha-ok">Alta</span>
                    <?php elseif ($cta['ESTADO_CUENTA'] == 3): ?>
                        <span class="interactive-badge badge-pending">Pendiente</span>
                    <?php else: ?>
                        <span class="interactive-badge fecha-alerta">Baja</span>
                    <?php endif; ?>
                </td>
                <td class="align-middle text-muted"><?= htmlspecialchars($cta['REQUERIMIENTO_INICIO_CUENTA'] ?? 'N/A') ?></td>
                <td class="align-middle text-muted"><?= htmlspecialchars($cta['REQUERIMIENTO_TERMINO_CUENTA'] ?? 'N/A') ?></td>
                <td class="align-middle text-muted"><?= $cta['FECHA_CREACION'] ? date('d/m/Y', strtotime($cta['FECHA_CREACION'])) : 'N/A' ?></td>
                <td class="align-middle text-right" style="white-space: nowrap;">
                    <?php if ($cta['ESTADO_CUENTA'] == 1 || $cta['ESTADO_CUENTA'] == 3): 
                        $cancelUrl = "/SIUGI/src/anular_cuenta?id_cuenta=" . $cta['ID_CUENTA'] . "&id_usuario=" . $id_usuario;
                    ?>
                        <a href="<?= $cancelUrl ?>" class="btn-custom btn-danger-custom interactive-btn" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; height: auto;" onclick="event.stopPropagation(); return confirm('¿Anular esta cuenta?')">
                            <i class="fas fa-ban"></i>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="10"><div class="text-center py-5 text-muted"><i class="fas fa-box-open fa-3x mb-3" style="opacity: 0.2;"></i><h6 class="font-weight-bold">No hay cuentas en este estado</h6></div></td></tr>';
    }
    $tbody_html = ob_get_clean();

    ob_start();
    if ($totalPages > 1):
        $adjacents = 2;
        if ($page > 1) { echo '<a href="javascript:void(0)" onclick="fetchCuentas('.($page - 1).')" class="page-link-custom">&laquo;</a>'; }
        
        $active = ($page == 1) ? 'active' : '';
        echo '<a href="javascript:void(0)" onclick="fetchCuentas(1)" class="page-link-custom ' . $active . '">1</a>';
        if ($page > ($adjacents + 2)) echo '<span class="page-link-custom ellipsis">...</span>';
        
        $start = max(2, $page - $adjacents);
        $end = min($totalPages - 1, $page + $adjacents);
        for ($i = $start; $i <= $end; $i++) {
            $active = ($page == $i) ? 'active' : '';
            echo '<a href="javascript:void(0)" onclick="fetchCuentas('.$i.')" class="page-link-custom ' . $active . '">' . $i . '</a>';
        }
        
        if ($page < ($totalPages - $adjacents - 1)) echo '<span class="page-link-custom ellipsis">...</span>';
        $active = ($page == $totalPages) ? 'active' : '';
        if ($totalPages > 1) {
            echo '<a href="javascript:void(0)" onclick="fetchCuentas('.$totalPages.')" class="page-link-custom ' . $active . '">' . $totalPages . '</a>';
        }
        
        if ($page < $totalPages) { echo '<a href="javascript:void(0)" onclick="fetchCuentas('.($page + 1).')" class="page-link-custom">&raquo;</a>'; }
    endif;
    $pagination_html = ob_get_clean();

    echo json_encode(['tbody' => $tbody_html, 'pagination' => $pagination_html]);
    exit; 
}

if (isset($_GET['ajax_bienes'])) {
    header('Content-Type: application/json');
    $id_usuario = (int)$_GET['id_usuario'];
    
    $f_bajas = isset($_GET['ver_bajas']) && $_GET['ver_bajas'] == '1' ? 1 : 0;
    $estado_filtro = $f_bajas ? 'Baja' : 'Alta';

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    try {
        $sql = "SELECT d.ID_DISP, s.GLOSA_FISCALIA AS SEDE, sc.GLOSA_SUBCATEGORIA, m.GLOSA_MARCA, d.MODELO, d.SERIE, d.CODIGO_INVENTARIO, est.GLOSA_ESTADO, u.GLOSA_UBICACION 
                FROM dispositivos d 
                JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION 
                LEFT JOIN sedes s ON u.FISCALIA_UBICACION = s.ID_SEDE 
                LEFT JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT 
                LEFT JOIN marcas m ON d.ID_MARCA = m.ID_MARCA 
                LEFT JOIN estado_cgu est ON d.ID_ESTADO_CGU = est.ID_ESTADO_CGU 
                WHERE u.ID_USUARIO_ASIGNADO = ? AND d.ELIMINADO = 0 AND est.GLOSA_ESTADO = ?
                ORDER BY s.GLOSA_FISCALIA DESC, sc.GLOSA_SUBCATEGORIA DESC LIMIT $limit OFFSET $offset";
        
        $stmt_bienes = $pdo_inventario->prepare($sql);
        $stmt_bienes->execute([$id_usuario, $estado_filtro]);
        $bienesData = $stmt_bienes->fetchAll();

        $stmt_count_b = $pdo_inventario->prepare("
            SELECT COUNT(*) 
            FROM dispositivos d 
            JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION 
            LEFT JOIN estado_cgu est ON d.ID_ESTADO_CGU = est.ID_ESTADO_CGU 
            WHERE u.ID_USUARIO_ASIGNADO = ? AND d.ELIMINADO = 0 AND est.GLOSA_ESTADO = ?
        ");
        $stmt_count_b->execute([$id_usuario, $estado_filtro]);
        $totalRecords = $stmt_count_b->fetchColumn();
        $totalPages = ceil($totalRecords / $limit);

    } catch (PDOException $e) {
        echo json_encode([
            'tbody' => '<tr><td colspan="8" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>Error BD: ' . htmlspecialchars($e->getMessage()) . '</td></tr>',
            'pagination' => ''
        ]);
        exit;
    }

    ob_start();
    if (count($bienesData) > 0) {
        foreach ($bienesData as $b) {
            ?>
            <tr class="hover-animate-row clickable-row" onclick="window.location='/SIUGI/editar_bien?id=<?= $b['ID_DISP'] ?>'">
                <td class="align-middle font-weight-bold" style="color: var(--primary);">#<?= $b['ID_DISP'] ?></td>
                <td class="align-middle"><?= htmlspecialchars($b['SEDE'] ?? 'N/A') ?></td>
                <td class="align-middle"><?= htmlspecialchars($b['GLOSA_SUBCATEGORIA'] ?? 'N/A') ?></td>
                <td class="align-middle font-weight-bold row-title"><?= htmlspecialchars($b['GLOSA_MARCA'].' '.$b['MODELO']) ?></td>
                <td class="align-middle"><?= htmlspecialchars($b['SERIE'] ?? 'N/A') ?></td>
                <td class="align-middle"><?= htmlspecialchars($b['CODIGO_INVENTARIO'] ?? 'N/A') ?></td>
                <td class="align-middle text-center">
                    <?php if ($b['GLOSA_ESTADO'] == 'Baja'): ?>
                        <span class="interactive-badge fecha-alerta">Baja</span>
                    <?php else: ?>
                        <span class="interactive-badge fecha-ok"><?= htmlspecialchars($b['GLOSA_ESTADO']) ?></span>
                    <?php endif; ?>
                </td>
                <td class="align-middle text-muted"><?= htmlspecialchars($b['GLOSA_UBICACION'] ?? 'N/A') ?></td>
            </tr>
            <?php
        }
    } else {
        echo '<tr><td colspan="8"><div class="text-center py-5 text-muted"><i class="fas fa-desktop fa-3x mb-3" style="opacity: 0.2;"></i><h6 class="font-weight-bold">No hay bienes en este estado</h6></div></td></tr>';
    }
    $tbody_html = ob_get_clean();

    ob_start();
    if ($totalPages > 1) {
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($page == $i) ? 'active' : '';
            echo '<a href="javascript:void(0)" onclick="fetchBienes('.$i.')" class="page-link-custom ' . $active . '">' . $i . '</a>';
        }
    }
    $pagination_html = ob_get_clean();

    echo json_encode(['tbody' => $tbody_html, 'pagination' => $pagination_html]);
    exit; 
}