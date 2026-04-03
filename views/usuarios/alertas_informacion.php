<?php

ob_start();


require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo_cuentas = $db->getCuentasConnection();
$pdo_info = $db->getInfoConnection();


$limit_baja = 10;
$page_baja = isset($_GET['p_baja']) && is_numeric($_GET['p_baja']) ? (int)$_GET['p_baja'] : 1;
$offset_baja = ($page_baja - 1) * $limit_baja;

if (isset($_GET['ajax_bajas'])) {
    header('Content-Type: application/json');
    
    $sql_count_bajas = "SELECT COUNT(*) FROM informatica.usuarios u 
                        WHERE u.estado = 1 AND EXISTS (
                            SELECT 1 FROM gestor_cuentas.cuentas c 
                            WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA IN (1, 3)
                        )";
    $total_bajas = $pdo_info->query($sql_count_bajas)->fetchColumn();
    $total_pages_baja = ceil($total_bajas / $limit_baja);

    $sqlBajas = "SELECT u.id, u.nombre AS Nombre, u.rut AS Rut, s.GLOSA_FISCALIA AS Sede, u.cargo AS Cargo,
                 (SELECT COUNT(*) FROM gestor_cuentas.cuentas c WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA IN (1, 3)) as cuentas_activas
                 FROM informatica.usuarios u 
                 LEFT JOIN informatica.fiscalias f ON u.cod_fiscalia = f.cod_fiscalia
                 LEFT JOIN gestor_cuentas.sedes s ON f.cod_fiscalia = s.ID_SEDE
                 WHERE u.estado = 1 AND EXISTS (
                    SELECT 1 FROM gestor_cuentas.cuentas c WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA IN (1, 3)
                 )
                 ORDER BY u.nombre ASC LIMIT $limit_baja OFFSET $offset_baja";
    $usuarios_baja = $pdo_info->query($sqlBajas)->fetchAll();

    ob_start();
    $contador_baja = $offset_baja + 1;
    foreach ($usuarios_baja as $ub): ?>
        <tr class="hover-animate-row">
            <td class="text-muted font-weight-bold text-center"><?= $contador_baja++ ?></td>
            <td class="font-weight-bold text-dark row-title">
                <?= htmlspecialchars($ub['Nombre'] ?? '') ?>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;"><?= htmlspecialchars($ub['Rut'] ?? '') ?></div>
            </td>
            <td><?= htmlspecialchars($ub['Cargo'] ?? 'Sin Cargo') ?></td>
            <td><?= htmlspecialchars($ub['Sede'] ?? 'No Asignada') ?></td>
            <td style="text-align: center;"><span class="interactive-badge badge-neutral"><?= $ub['cuentas_activas'] ?></span></td>
            <td style="text-align: right;">
                <a href="/SIUGI/menu_usuario?id=<?= $ub['id'] ?>" class="btn-custom btn-secondary-custom interactive-btn" style="height: auto; padding: 0.25rem 0.6rem; font-size: 0.75rem;"><i class="fas fa-eye"></i></a>
            </td>
        </tr>
    <?php endforeach;
    $tbody_html = ob_get_clean();

    ob_start();
    if ($total_pages_baja > 1):
        for ($i = 1; $i <= $total_pages_baja; $i++): 
            $active = ($page_baja == $i) ? 'active' : ''; ?>
            <a href="javascript:void(0)" onclick="fetchBajasData(<?= $i ?>)" class="page-link-custom <?= $active ?>"><?= $i ?></a>
        <?php endfor;
    endif;
    $pagination_html = ob_get_clean();

    echo json_encode(['tbody' => $tbody_html, 'pagination' => $pagination_html, 'count' => count($usuarios_baja)]);
    exit; 
}


$limit_prox = 10;
$page_prox = isset($_GET['p_prox']) && is_numeric($_GET['p_prox']) ? (int)$_GET['p_prox'] : 1;
$offset_prox = ($page_prox - 1) * $limit_prox;

if (isset($_GET['ajax_prox'])) {
    header('Content-Type: application/json');
    
    $sql_count_prox = "SELECT COUNT(*) FROM informatica.usuarios u WHERE u.fec_termino_funciones IS NOT NULL and u.estado = 0";
    $total_prox = $pdo_info->query($sql_count_prox)->fetchColumn();
    $total_pages_prox = ceil($total_prox / $limit_prox);

    $sqlUsers = "SELECT u.id, u.nombre AS Nombre, u.rut AS Rut, s.GLOSA_FISCALIA AS Sede, u.cargo AS Cargo, u.fec_termino_funciones 
                 FROM informatica.usuarios u
                 LEFT JOIN informatica.fiscalias f ON u.cod_fiscalia = f.cod_fiscalia
                 LEFT JOIN gestor_cuentas.sedes s ON f.cod_fiscalia = s.ID_SEDE
                 WHERE u.fec_termino_funciones IS NOT NULL and u.estado = 0
                 ORDER BY u.fec_termino_funciones ASC LIMIT $limit_prox OFFSET $offset_prox";
    $usuarios_proximos = $pdo_info->query($sqlUsers)->fetchAll();

    ob_start();
    $hoy = new DateTime();
    $contador_prox = $offset_prox + 1;
    foreach ($usuarios_proximos as $usr): 
        $fecha_termino = new DateTime($usr['fec_termino_funciones']);
        $intervalo = $hoy->diff($fecha_termino);
        $dias_restantes = (int)$intervalo->format('%R%a');
        $clase_fecha = ($dias_restantes <= 15) ? 'fecha-alerta' : 'fecha-ok';
    ?>
        <tr class="hover-animate-row">
            <td class="text-muted font-weight-bold text-center"><?= $contador_prox++ ?></td>
            <td class="font-weight-bold text-dark row-title">
                <?= htmlspecialchars($usr['Nombre'] ?? '') ?>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;"><?= htmlspecialchars($usr['Sede'] ?? 'No Asignada') ?></div>
            </td>
            <td><?= htmlspecialchars($usr['Cargo'] ?? 'Sin Cargo') ?></td>
            <td class="font-weight-bold" style="color: var(--text-muted);"><?= $fecha_termino->format('d/m/Y') ?></td>
            <td style="text-align: center;"><span class="interactive-badge <?= $clase_fecha ?>"><?= $dias_restantes ?> días</span></td>
            <td style="text-align: right;">
                <a href="/SIUGI/menu_usuario?id=<?= $usr['id'] ?>" class="btn-custom btn-secondary-custom interactive-btn" style="height: auto; padding: 0.25rem 0.6rem; font-size: 0.75rem;"><i class="fas fa-arrow-right"></i></a>
            </td>
        </tr>
    <?php endforeach;
    $tbody_html = ob_get_clean();

    ob_start();
    if ($total_pages_prox > 1):
        for ($i = 1; $i <= $total_pages_prox; $i++): 
            $active = ($page_prox == $i) ? 'active' : ''; ?>
            <a href="javascript:void(0)" onclick="fetchProxData(<?= $i ?>)" class="page-link-custom <?= $active ?>"><?= $i ?></a>
        <?php endfor;
    endif;
    $pagination_html = ob_get_clean();

    echo json_encode(['tbody' => $tbody_html, 'pagination' => $pagination_html, 'count' => count($usuarios_proximos)]);
    exit; 
}


if (isset($_GET['ajax_chart'])) {
    header('Content-Type: application/json');
    $f_sw = $_GET['software'] ?? '';
    $f_sd = $_GET['sede'] ?? '';

    $whereChart = "ESTADO_CUENTA IN (1, 3)"; 
    $paramsChart = [];

    if ($f_sw !== '') { $whereChart .= " AND SOFTWARE = ?"; $paramsChart[] = $f_sw; }
    if ($f_sd !== '') { $whereChart .= " AND SEDE = ?"; $paramsChart[] = $f_sd; }

    $sqlChart = "SELECT SEDE, SOFTWARE, COUNT(*) as total FROM vista_cuentas_detalle WHERE $whereChart GROUP BY SEDE, SOFTWARE";
    $stmtChart = $pdo_cuentas->prepare($sqlChart);
    $stmtChart->execute($paramsChart);
    $rawData = $stmtChart->fetchAll();

    $sedes_labels = []; $software_labels = []; $matrix = [];
    foreach ($rawData as $row) {
        $sd = $row['SEDE'] ?: 'Sin Sede Asignada';
        $sw = $row['SOFTWARE'] ?: 'Desconocido';
        if (!in_array($sd, $sedes_labels)) $sedes_labels[] = $sd;
        if (!in_array($sw, $software_labels)) $software_labels[] = $sw;
        $matrix[$sd][$sw] = (int)$row['total'];
    }

    $datasets = [];
    $colores = ['#64748b', '#0ea5e9', '#f59e0b', '#f43f5e', '#14b8a6', '#8b5cf6', '#10b981', '#6366f1', '#ec4899', '#84cc16'];
    $color_idx = 0;

    foreach ($software_labels as $sw) {
        $data_array = [];
        foreach ($sedes_labels as $sd) { $data_array[] = isset($matrix[$sd][$sw]) ? $matrix[$sd][$sw] : 0; }
        $datasets[] = [
            'label' => $sw, 'data' => $data_array,
            'backgroundColor' => $colores[$color_idx % count($colores)],
            'borderRadius' => 4, 'borderSkipped' => false
        ];
        $color_idx++;
    }

    echo json_encode(['labels' => $sedes_labels, 'datasets' => $datasets, 'empty' => empty($rawData)]);
    exit; 
}


$sql_count_bajas = "SELECT COUNT(*) FROM informatica.usuarios u WHERE u.estado = 1 AND EXISTS (SELECT 1 FROM gestor_cuentas.cuentas c WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA IN (1, 3))";
$total_bajas = $pdo_info->query($sql_count_bajas)->fetchColumn();
$total_pages_baja = ceil($total_bajas / $limit_baja);

$sqlBajas = "SELECT u.id, u.nombre AS Nombre, u.rut AS Rut, s.GLOSA_FISCALIA AS Sede, u.cargo AS Cargo, (SELECT COUNT(*) FROM gestor_cuentas.cuentas c WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA IN (1, 3)) as cuentas_activas FROM informatica.usuarios u LEFT JOIN informatica.fiscalias f ON u.cod_fiscalia = f.cod_fiscalia LEFT JOIN gestor_cuentas.sedes s ON f.cod_fiscalia = s.ID_SEDE WHERE u.estado = 1 AND EXISTS (SELECT 1 FROM gestor_cuentas.cuentas c WHERE c.USUARIO = u.id AND c.ESTADO_CUENTA IN (1, 3)) ORDER BY u.nombre ASC LIMIT $limit_baja OFFSET $offset_baja";
$usuarios_baja = $pdo_info->query($sqlBajas)->fetchAll();

$sql_count_prox = "SELECT COUNT(*) FROM informatica.usuarios u WHERE u.fec_termino_funciones IS NOT NULL and u.estado = 0";
$total_prox = $pdo_info->query($sql_count_prox)->fetchColumn();
$total_pages_prox = ceil($total_prox / $limit_prox);

$sqlUsers = "SELECT u.id, u.nombre AS Nombre, u.rut AS Rut, s.GLOSA_FISCALIA AS Sede, u.cargo AS Cargo, u.fec_termino_funciones FROM informatica.usuarios u LEFT JOIN informatica.fiscalias f ON u.cod_fiscalia = f.cod_fiscalia LEFT JOIN gestor_cuentas.sedes s ON f.cod_fiscalia = s.ID_SEDE WHERE u.fec_termino_funciones IS NOT NULL and u.estado = 0 ORDER BY u.fec_termino_funciones ASC LIMIT $limit_prox OFFSET $offset_prox";
$usuarios_proximos = $pdo_info->query($sqlUsers)->fetchAll();

$softwares = $pdo_cuentas->query("SELECT GLOSA_SOFTWARE FROM software ORDER BY GLOSA_SOFTWARE ASC")->fetchAll();
$sedes = $pdo_cuentas->query("SELECT GLOSA_FISCALIA FROM sedes ORDER BY GLOSA_FISCALIA ASC")->fetchAll();

$sqlChart = "SELECT SEDE, SOFTWARE, COUNT(*) as total FROM vista_cuentas_detalle WHERE ESTADO_CUENTA IN (1, 3) GROUP BY SEDE, SOFTWARE";
$rawData = $pdo_cuentas->query($sqlChart)->fetchAll();
$sedes_labels = []; $software_labels = []; $matrix = [];
foreach ($rawData as $row) {
    $sd = $row['SEDE'] ?: 'Sin Sede Asignada'; $sw = $row['SOFTWARE'] ?: 'Desconocido';
    if (!in_array($sd, $sedes_labels)) $sedes_labels[] = $sd;
    if (!in_array($sw, $software_labels)) $software_labels[] = $sw;
    $matrix[$sd][$sw] = (int)$row['total'];
}
$datasets = [];
$colores = ['#64748b', '#0ea5e9', '#f59e0b', '#f43f5e', '#14b8a6', '#8b5cf6', '#10b981', '#6366f1', '#ec4899', '#84cc16'];
$color_idx = 0;
foreach ($software_labels as $sw) {
    $data_array = [];
    foreach ($sedes_labels as $sd) { $data_array[] = isset($matrix[$sd][$sw]) ? $matrix[$sd][$sw] : 0; }
    $datasets[] = ['label' => $sw, 'data' => $data_array, 'backgroundColor' => $colores[$color_idx % count($colores)], 'borderRadius' => 4, 'borderSkipped' => false];
    $color_idx++;
}

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/alertas_informacion.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="content-header mb-3" style="animation: fadeInUp 0.5s ease-out forwards;">
        <div class="container-fluid p-0">
            <h1 class="m-0 font-weight-bold" style="color: var(--text-main); font-size: 1.75rem; letter-spacing: -0.025em;">
                Alertas e Información
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Panel de administración y monitoreo de usuarios</p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid p-0">
            <div class="top-tables-grid">
                
                <div class="card card-custom card-custom-1">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle header-icon" style="color: var(--warning);"></i>
                            <h5 class="card-title-text">Usuarios de Baja con Cuentas vigentes</h5>
                        </div>
                        <a href="/SIUGI/exportar_cuentas?alerta_bajas=1" target="_blank" class="btn-custom btn-danger-custom interactive-btn" style="height: 30px; font-size: 0.75rem; padding: 0 0.8rem;">
                            <i class="fas fa-file-pdf mr-1"></i> Exportar
                        </a>
                    </div>
                    
                    <div id="bajasTableWrapper" class="ajax-container-wrapper card-body p-0">
                        <?php if (count($usuarios_baja) > 0): ?>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead><tr><th style="width: 50px; text-align: center;">#</th><th>USUARIO</th><th>CARGO</th><th>SEDE</th><th style="text-align: center;">PEND.</th><th style="text-align: right;">ACCIÓN</th></tr></thead>
                                    <tbody id="bajasTbody">
                                        <?php $contador_baja = $offset_baja + 1; foreach ($usuarios_baja as $ub): ?>
                                        <tr class="hover-animate-row">
                                            <td class="text-muted font-weight-bold text-center"><?= $contador_baja++ ?></td>
                                            <td class="font-weight-bold text-dark row-title">
                                                <?= htmlspecialchars($ub['Nombre'] ?? '') ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;"><?= htmlspecialchars($ub['Rut'] ?? '') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($ub['Cargo'] ?? 'Sin Cargo') ?></td>
                                            <td><?= htmlspecialchars($ub['Sede'] ?? 'No Asignada') ?></td>
                                            <td style="text-align: center;"><span class="interactive-badge badge-neutral"><?= $ub['cuentas_activas'] ?></span></td>
                                            <td style="text-align: right;"><a href="/SIUGI/menu_usuario?id=<?= $ub['id'] ?>" class="btn-custom btn-secondary-custom interactive-btn" style="height: auto; padding: 0.25rem 0.6rem; font-size: 0.75rem;"><i class="fas fa-eye"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="bajasPaginationContainer" class="pagination-custom">
                                <?php if ($total_pages_baja > 1): for ($i = 1; $i <= $total_pages_baja; $i++): $active = ($page_baja == $i) ? 'active' : ''; ?>
                                    <a href="javascript:void(0)" onclick="fetchBajasData(<?= $i ?>)" class="page-link-custom <?= $active ?>"><?= $i ?></a>
                                <?php endfor; endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-check-circle fa-3x mb-3" style="color: var(--success); opacity: 0.3;"></i>
                                <h6 class="font-weight-bold">Todo en orden</h6>
                                <p class="mb-0" style="font-size: 0.8rem;">No hay usuarios de baja con cuentas activas.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card card-custom card-custom-2">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock header-icon" style="color: var(--danger);"></i>
                            <h5 class="card-title-text">Próximos Términos de Funciones</h5>
                        </div>
                        <a href="/SIUGI/exportar_cuentas?proximos_terminos=1" target="_blank" class="btn-custom btn-danger-custom interactive-btn" style="height: 30px; font-size: 0.75rem; padding: 0 0.8rem;">
                            <i class="fas fa-file-pdf mr-1"></i> Exportar
                        </a>
                    </div>
                    
                    <div id="proxTableWrapper" class="ajax-container-wrapper card-body p-0">
                        <?php if (count($usuarios_proximos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead><tr><th style="width: 50px; text-align: center;">#</th><th>USUARIO</th><th>CARGO</th><th>FECHA</th><th style="text-align: center;">RESTANTE</th><th style="text-align: right;">ACCIÓN</th></tr></thead>
                                    <tbody id="proxTbody">
                                        <?php $hoy = new DateTime(); $contador_prox = $offset_prox + 1; foreach ($usuarios_proximos as $usr): 
                                            $fecha_termino = new DateTime($usr['fec_termino_funciones']); $intervalo = $hoy->diff($fecha_termino); $dias_restantes = (int)$intervalo->format('%R%a'); $clase_fecha = ($dias_restantes <= 15) ? 'fecha-alerta' : 'fecha-ok';
                                        ?>
                                        <tr class="hover-animate-row">
                                            <td class="text-muted font-weight-bold text-center"><?= $contador_prox++ ?></td>
                                            <td class="font-weight-bold text-dark row-title">
                                                <?= htmlspecialchars($usr['Nombre'] ?? '') ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal; margin-top: 2px;"><?= htmlspecialchars($usr['Sede'] ?? 'No Asignada') ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($usr['Cargo'] ?? 'Sin Cargo') ?></td>
                                            <td class="font-weight-bold" style="color: var(--text-muted);"><?= $fecha_termino->format('d/m/Y') ?></td>
                                            <td style="text-align: center;"><span class="interactive-badge <?= $clase_fecha ?>"><?= $dias_restantes ?> días</span></td>
                                            <td style="text-align: right;"><a href="/SIUGI/menu_usuario?id=<?= $usr['id'] ?>" class="btn-custom btn-secondary-custom interactive-btn" style="height: auto; padding: 0.25rem 0.6rem; font-size: 0.75rem;"><i class="fas fa-arrow-right"></i></a></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="proxPaginationContainer" class="pagination-custom">
                                <?php if ($total_pages_prox > 1): for ($i = 1; $i <= $total_pages_prox; $i++): $active = ($page_prox == $i) ? 'active' : ''; ?>
                                    <a href="javascript:void(0)" onclick="fetchProxData(<?= $i ?>)" class="page-link-custom <?= $active ?>"><?= $i ?></a>
                                <?php endfor; endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-check fa-3x mb-3" style="opacity: 0.2;"></i>
                                <h6 class="font-weight-bold">Sin vencimientos</h6>
                                <p class="mb-0" style="font-size: 0.8rem;">No hay usuarios próximos a vencer funciones.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div> 

            <div class="card card-custom card-custom-3">
                <div class="card-header-custom">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-bar header-icon"></i>
                        <h5 class="card-title-text">Distribución de Cuentas</h5>
                    </div>
                </div>
                <div class="filter-form">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Sede Fiscal</label>
                        <select id="filtroSede" class="form-control" onchange="fetchChartData()">
                            <option value="">Todas las Sedes</option>
                            <?php foreach ($sedes as $sd): ?>
                                <option value="<?= htmlspecialchars($sd['GLOSA_FISCALIA']) ?>"><?= htmlspecialchars($sd['GLOSA_FISCALIA']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Plataforma / Sistema</label>
                        <select id="filtroSoftware" class="form-control" onchange="fetchChartData()">
                            <option value="">Todos los Sistemas</option>
                            <?php foreach ($softwares as $sw): ?>
                                <option value="<?= htmlspecialchars($sw['GLOSA_SOFTWARE']) ?>"><?= htmlspecialchars($sw['GLOSA_SOFTWARE']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn-custom btn-secondary-custom interactive-btn" onclick="resetFilters()">
                            <i class="fas fa-eraser mr-2"></i> Limpiar
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container-wrapper" id="chartWrapper">
                        <div id="chartEmptyState" class="empty-state-message" style="<?= empty($rawData) ? 'display:block;' : 'display:none;' ?>">
                            <i class="fas fa-chart-pie fa-3x mb-3" style="opacity: 0.2;"></i>
                            <h6>No hay datos para graficar con estos filtros.</h6>
                        </div>
                        <canvas id="cuentasChart" style="<?= empty($rawData) ? 'display:none;' : '' ?>"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
    const initialLabels = <?= json_encode($sedes_labels) ?>;
    const initialDatasets = <?= json_encode($datasets) ?>;
</script>

<script src="/SIUGI/public/assets/js/alertas_informacion.js"></script>

<?php 

include __DIR__ . '/../../templates/layout_bottom.php'; 
?>