<?php
require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo_info  = $db->getInfoConnection();
$pdo_inv  = $db->getInvConnection();

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$usuario_actual = $_SESSION['user'] ?? '';

$total_activos = $pdo_inv->query("SELECT COUNT(*) FROM dispositivos WHERE ELIMINADO = 0 AND ID_ESTADO_CGU = (SELECT ID_ESTADO_CGU FROM estado_cgu WHERE GLOSA_ESTADO = 'Alta')")->fetchColumn();
$total_bodega = $pdo_inv->query("SELECT COUNT(*) FROM dispositivos WHERE ELIMINADO = 0 AND ID_UBICACION = 2")->fetchColumn(); 
$total_movimientos = $pdo_inv->query("SELECT COUNT(*) FROM registros")->fetchColumn();

$estados_labels = ['Alta', 'Bodega']; 
$estados_values = [$total_activos, $total_bodega];

$res_sedes = $pdo_inv->query("SELECT s.GLOSA_FISCALIA, COUNT(*) AS TOTAL FROM vista_dispositivos v JOIN sedes s ON v.FISCALIA_UBICACION = s.ID_SEDE WHERE v.ESTADO = 'Alta' GROUP BY s.GLOSA_FISCALIA ORDER BY TOTAL DESC LIMIT 10");
$sedes_labels = []; $sedes_values = [];
while ($row = $res_sedes->fetch()) { 
    $sedes_labels[] = $row['GLOSA_FISCALIA']; 
    $sedes_values[] = $row['TOTAL']; 
}

$res_bodega = $pdo_inv->query("SELECT c.GLOSA_CATEGORIA, COUNT(d.ID_DISP) as TOTAL FROM dispositivos d JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT JOIN categorias c ON sc.ID_CAT = c.ID_CAT WHERE d.ELIMINADO = 0 AND d.ID_UBICACION = 2 GROUP BY c.GLOSA_CATEGORIA ORDER BY TOTAL DESC");
$bod_cat_labels = []; $bod_cat_values = [];
while($row = $res_bodega->fetch()){ 
    $bod_cat_labels[] = $row['GLOSA_CATEGORIA']; 
    $bod_cat_values[] = $row['TOTAL']; 
}

$res_alta = $pdo_inv->query("SELECT c.GLOSA_CATEGORIA, COUNT(d.ID_DISP) as TOTAL FROM dispositivos d JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT JOIN categorias c ON sc.ID_CAT = c.ID_CAT WHERE d.ELIMINADO = 0 AND d.ID_ESTADO_CGU = 1 GROUP BY c.GLOSA_CATEGORIA ORDER BY TOTAL DESC");
$alt_cat_labels = []; $alt_cat_values = [];
while($row = $res_alta->fetch()){ 
    $alt_cat_labels[] = $row['GLOSA_CATEGORIA']; 
    $alt_cat_values[] = $row['TOTAL']; 
}

$cod_fiscalia = 0;
$nombre_sede = "Sede Desconocida";
$mi_sede_labels = []; $mi_sede_values = [];

if (!empty($usuario_actual)) {
    $stmt_usuario = $pdo_info->prepare("SELECT cod_fiscalia FROM usuarios WHERE usuario = :user LIMIT 1");
    $stmt_usuario->execute([':user' => $usuario_actual]);
    if ($row_user = $stmt_usuario->fetch()) {
        $cod_fiscalia = (int)$row_user['cod_fiscalia'];
    }
}

if ($cod_fiscalia > 0) {
    $stmt_nom_sede = $pdo_inv->prepare("SELECT GLOSA_FISCALIA FROM sedes WHERE ID_SEDE = :id");
    $stmt_nom_sede->execute([':id' => $cod_fiscalia]);
    if ($row_nom = $stmt_nom_sede->fetch()) {
        $nombre_sede = $row_nom['GLOSA_FISCALIA'];
    }

    $q_misede = "SELECT c.GLOSA_CATEGORIA, COUNT(d.ID_DISP) as TOTAL 
                 FROM dispositivos d 
                 JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION 
                 JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT 
                 JOIN categorias c ON sc.ID_CAT = c.ID_CAT 
                 WHERE d.ELIMINADO = 0 AND u.FISCALIA_UBICACION = :cod AND d.ID_ESTADO_CGU = 0
                 GROUP BY c.GLOSA_CATEGORIA 
                 ORDER BY TOTAL DESC";
    $stmt_misede = $pdo_inv->prepare($q_misede);
    $stmt_misede->execute([':cod' => $cod_fiscalia]);
    while($row = $stmt_misede->fetch()){
        $mi_sede_labels[] = $row['GLOSA_CATEGORIA'];
        $mi_sede_values[] = $row['TOTAL'];
    }
}

$ultimos_disp = $pdo_inv->query("SELECT v.*, s.GLOSA_FISCALIA FROM vista_dispositivos v JOIN sedes s ON v.FISCALIA_UBICACION = s.ID_SEDE ORDER BY v.ID DESC LIMIT 5")->fetchAll();
$result_mov = $pdo_inv->query("SELECT r.*, sc.GLOSA_SUBCATEGORIA, d.CODIGO_INVENTARIO FROM vista_registros_detallada r LEFT JOIN dispositivos d ON r.ID_DISPOSITIVO = d.ID_DISP LEFT JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT ORDER BY r.ID_REGISTRO DESC LIMIT 15")->fetchAll();

include __DIR__ . '/../../templates/layout_top.php';

echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/estadisticas_registros.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.8rem; color: #1e293b;">Estadísticas y Registros</h1>
        <p class="text-muted">Panel de Control y Accesos</p>
    </div>

    <div class="animate-up" style="animation-delay: 0.1s">
        <div class="collapse-btn" data-toggle="collapse" data-target="#statsArea">
            <span><i class="fas fa-chart-pie mr-2"></i> Estadísticas Generales</span>
            <i class="fas fa-chevron-down toggle-icon" id="toggleIconStats"></i>
        </div>
        <div id="statsArea" class="collapse">
            <div class="chart-grid">
                <div class="chart-card-inner"><small class="stat-label d-block mb-3">Equipos operativos por Fiscalia</small><div style="height:230px"><canvas id="chartSedes"></canvas></div></div>
                <div class="chart-card-inner"><small class="stat-label d-block mb-3">Distribución Global</small><div style="height:230px"><canvas id="chartDonut"></canvas></div></div>
                <div class="chart-card-inner"><small class="stat-label d-block mb-3">Categorías en Bodega</small><div style="height:230px"><canvas id="chartBodega"></canvas></div></div>
                <div class="chart-card-inner"><small class="stat-label d-block mb-3">Categorías Operativas</small><div style="height:230px"><canvas id="chartAlta"></canvas></div></div>
            </div>
        </div>
    </div>

    <div class="row animate-up" style="animation-delay: 0.2s">
        <div class="col-md-4">
            <div class="stat-box">
                <div class="stat-icon" style="background:var(--success-bg); color:var(--success);"><i class="fas fa-check-circle"></i></div>
                <div><span class="stat-label">Equipos Operativos</span><h3 class="stat-val"><?php echo $total_activos; ?></h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <div class="stat-icon" style="background:var(--warning-bg); color:var(--warning);"><i class="fas fa-boxes"></i></div>
                <div><span class="stat-label">Equipos en Bodega</span><h3 class="stat-val"><?php echo $total_bodega; ?></h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <div class="stat-icon" style="background:var(--primary-light); color:var(--primary);"><i class="fas fa-exchange-alt"></i></div>
                <div><span class="stat-label">Movimientos Totales</span><h3 class="stat-val"><?php echo $total_movimientos; ?></h3></div>
            </div>
        </div>
    </div>

    <div class="animate-up" style="animation-delay: 0.3s">
        <div class="collapse-btn" data-toggle="collapse" data-target="#collapseIngresos">
            <span><i class="fas fa-list-ul mr-2"></i> Últimos Ingresos al Sistema</span>
            <i class="fas fa-chevron-up toggle-icon" id="toggleIconIngresos"></i>
        </div>
        <div id="collapseIngresos" class="collapse show">
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-estadisticas table-borderless m-0">
                        <thead><tr><th>ID</th><th>Dispositivo / Modelo</th><th>Sede / Ubicación</th><th>Serie</th><th>Fecha</th><th>Registra</th></tr></thead>
                        <tbody>
                            <?php foreach($ultimos_disp as $d): ?>
                            <tr onclick="window.location='/SIUGI/editar_bien?id=<?php echo $d['ID']; ?>'" style="cursor:pointer">
                                <td class="text-center"><span class="table-id-badge">#<?php echo $d['ID']; ?></span></td>
                                <td>
                                    <strong class="text-dark"><?php echo $d['SUBCATEGORIA']; ?></strong><br>
                                    <small class="text-muted"><?php echo $d['MODELO']; ?></small>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark"><?php echo $d['GLOSA_FISCALIA']; ?></span><br>
                                    <small class="text-muted"><?php echo $d['UBICACION_DETALLE']; ?></small>
                                </td>
                                <td>
                                    <?php if(!empty(trim($d['SERIE']))): ?>
                                        <span class="table-serie"><?php echo htmlspecialchars($d['SERIE']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted font-italic" style="font-size: 0.75rem;">S/N</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-dark font-weight-bold"><?php echo date('d/m/Y', strtotime($d['FECHA_REGISTRO'])); ?></span>
                                </td>
                                <td><span class="user-badge"><?php echo htmlspecialchars($d['QUIEN_REGISTRA']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="animate-up" style="animation-delay: 0.4s">
        <div class="collapse-btn" data-toggle="collapse" data-target="#collapseMiSede">
            <span><i class="fas fa-building mr-2"></i> Inventario en Mi Sede (<?php echo htmlspecialchars($nombre_sede); ?>)</span>
            <i class="fas fa-chevron-down toggle-icon" id="toggleIconMiSede"></i>
        </div>
        <div id="collapseMiSede" class="collapse">
            <div class="chart-card-inner full-width-chart">
                <div style="height: 250px; width: 100%;">
                    <?php if (count($mi_sede_labels) > 0): ?>
                        <canvas id="chartMiSede"></canvas>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            No hay inventario registrado para esta sede aún.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="animate-up" style="animation-delay: 0.5s">
        <div class="collapse-btn" data-toggle="collapse" data-target="#collapseActividad">
            <span><i class="fas fa-history mr-2"></i> Actividad Reciente Detallada</span>
            <i class="fas fa-chevron-down toggle-icon" id="toggleIconActividad"></i>
        </div>
        <div id="collapseActividad" class="collapse">
            <div class="card-custom">
                <div class="activity-feed">
                    <?php foreach($result_mov as $m): ?>
                        <?php 
                            $es_anulacion = (stripos($m['TIPO'], 'Anulacion') !== false);
                            $es_edicion = (stripos($m['TIPO'], 'Edicion') !== false);
                            $indicator_class = $es_anulacion ? 'type-anulacion' : ($es_edicion ? 'type-edicion' : 'type-movimiento');
                        ?>
                        <div class="activity-item" onclick="window.location='/SIUGI/editar_bien?id=<?php echo $m['ID_DISPOSITIVO']; ?>'">
                            <div class="indicator-bar <?php echo $indicator_class; ?>"></div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($m['GLOSA_SUBCATEGORIA'] ?? 'Desconocido'); ?>
                                    </span>
                                    <span class="badge-soft badge-inv ml-2">
                                        <?php echo !empty($m['CODIGO_INVENTARIO']) ? 'Inv: ' . $m['CODIGO_INVENTARIO'] : 'S/N'; ?>
                                    </span>
                                </div>
                                <small class="text-muted"><i class="far fa-clock mr-1"></i><?php echo date('d/m/y H:i', strtotime($m['FECHA_MOVIMIENTO'])); ?></small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div style="font-size: 13px;">
                                    <?php if ($es_anulacion): ?>
                                        <span class="text-danger font-weight-bold"><i class="fas fa-trash mr-1"></i> Se eliminó.</span> <span class="text-muted">Estaba en:</span>
                                    <?php elseif ($es_edicion): ?>
                                        <span class="text-muted"><i class="fas fa-edit mr-1"></i> Editado. Ubicación:</span>
                                    <?php else: ?>
                                        <span class="text-success font-weight-bold"><i class="fas fa-arrow-right mr-1"></i> Movido a:</span>
                                    <?php endif; ?>
                                    <span class="font-weight-bold text-dark ml-1"><?php echo htmlspecialchars($m['NUEVA_SEDE'] ?? 'No especificada'); ?></span>
                                </div>
                                <span class="user-badge"><i class="fas fa-user-edit mr-1"></i><?php echo htmlspecialchars($m['USUARIO_REGISTRO']); ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div>
                                    <?php if(!empty($m['OBSERVACION'])): ?>
                                        <span class="text-muted" style="font-size: 12px; font-style: italic;">
                                            <i class="fas fa-comment-dots mr-1"></i>"<?php echo htmlspecialchars($m['OBSERVACION']); ?>"
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted font-weight-bold"><?php echo ($m['TIPO'] ?? 'Registro'); ?> #<?php echo $m['ID_DISPOSITIVO']; ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const sedesLabels   = <?= json_encode($sedes_labels); ?>;
    const sedesValues   = <?= json_encode($sedes_values); ?>;
    const estadosLabels = <?= json_encode($estados_labels); ?>;
    const estadosValues = <?= json_encode($estados_values); ?>;
    const bodCatLabels  = <?= json_encode($bod_cat_labels); ?>;
    const bodCatValues  = <?= json_encode($bod_cat_values); ?>;
    const altCatLabels  = <?= json_encode($alt_cat_labels); ?>;
    const altCatValues  = <?= json_encode($alt_cat_values); ?>;
    const miSedeLabels  = <?= json_encode($mi_sede_labels); ?>;
    const miSedeValues  = <?= json_encode($mi_sede_values); ?>;
</script>

<script src="/SIUGI/public/assets/js/estadisticas_registros.js"></script>

<?php 
include __DIR__ . '/../../templates/layout_bottom.php'; 
?>