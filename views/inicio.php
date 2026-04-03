<?php

require_once __DIR__ . '/../config/db.php';
$db = new DatabaseConnection();

$pdo_info    = $db->getInfoConnection();
$pdo_cuentas = $db->getCuentasConnection();
$pdo_inv     = $db->getInvConnection();

$nombre_sesion = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : (isset($_SESSION['user']) ? $_SESSION['user'] : 'Usuario');

$query_ultimas_cuentas = "SELECT * FROM vista_cuentas_detalle WHERE ESTADO_CUENTA = 3 ORDER BY FECHA_CREACION DESC, ID_CUENTA DESC LIMIT 4";
$stmt_ultimas = $pdo_cuentas->query($query_ultimas_cuentas);
$ultimas_cuentas = $stmt_ultimas->fetchAll();

$query_chart = "SELECT SOFTWARE, COUNT(*) as total FROM vista_cuentas_detalle WHERE ESTADO_CUENTA IN (1, 3) AND SOFTWARE IS NOT NULL AND SOFTWARE != '' GROUP BY SOFTWARE ORDER BY total DESC";
$stmt_chart = $pdo_cuentas->query($query_chart);
$chart_labels = [];
$chart_values = [];
while ($row = $stmt_chart->fetch()) {
    $chart_labels[] = $row['SOFTWARE'];
    $chart_values[] = $row['total'];
}

$query_nuevo = "SELECT id, usuario, nombre, rut, cargo, fec_nacimiento, fec_inicio_funciones FROM usuarios WHERE estado = 0 AND fec_inicio_funciones IS NOT NULL ORDER BY fec_inicio_funciones DESC LIMIT 1";
$stmt_nuevo = $pdo_info->query($query_nuevo);
$nuevo_usr = $stmt_nuevo->fetch();

$cuenta_count = 0;
$cuenta_pendientes_count = 0;
if ($nuevo_usr) {
    $stmt_cuentas_count = $pdo_cuentas->prepare("SELECT COUNT(*) FROM cuentas WHERE USUARIO = ? AND ESTADO_CUENTA = 1");
    $stmt_cuentas_count->execute([$nuevo_usr['id']]);
    $cuenta_count = $stmt_cuentas_count->fetchColumn();

    $stmt_pendientes = $pdo_cuentas->prepare("SELECT COUNT(*) FROM cuentas WHERE USUARIO = ? AND ESTADO_CUENTA = 3");
    $stmt_pendientes->execute([$nuevo_usr['id']]);
    $cuenta_pendientes_count = $stmt_pendientes->fetchColumn();
}

$stmt_disp = $pdo_inv->query("SELECT v.*, s.GLOSA_FISCALIA FROM vista_dispositivos v JOIN sedes s ON v.FISCALIA_UBICACION = s.ID_SEDE ORDER BY v.ID DESC LIMIT 5");
$ultimos_disp = $stmt_disp->fetchAll();

$stmt_mov = $pdo_inv->query("SELECT r.*, sc.GLOSA_SUBCATEGORIA, d.CODIGO_INVENTARIO FROM vista_registros_detallada r LEFT JOIN dispositivos d ON r.ID_DISPOSITIVO = d.ID_DISP LEFT JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT ORDER BY r.ID_REGISTRO DESC LIMIT 15");
$ultimos_movimientos = $stmt_mov->fetchAll();

include __DIR__ . '/../templates/layout_top.php';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.6rem; color: #1e293b;">Bienvenido/a, <?php echo htmlspecialchars($nombre_sesion); ?></h1>
    </div>

    <div class="card-custom animate-up" style="animation-delay: 0.1s">
        <div class="card-header-custom">
            <i class="fas fa-chart-line mr-2 text-muted" style="font-size: 1rem;"></i> Ultimas Cuentas Registradas
        </div>
        <div class="card-body p-0">
            <div class="d-flex panel-split">
                
                <div class="panel-split-left" style="flex: 0 0 60%; border-right: 1px solid var(--border);">
                    <?php if (count($ultimas_cuentas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sistema / Sede</th>
                                    <th>Usuario Asignado</th>
                                    <th>Glosa</th>
                                    <th style="text-align: center;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_cuentas as $cta): 
                                    $usuarioInexistente = empty($cta['NOMBRE_USUARIO']);
                                ?>
                                <tr class="clickable-row" onclick="window.location='/SIUGI/editar_cuenta?id=<?= $cta['ID_CUENTA'] ?>'">
                                    <td>
                                        <strong class="row-title text-dark"><?= htmlspecialchars($cta['SOFTWARE'] ?? 'N/A') ?></strong><br>
                                        <span class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($cta['SEDE'] ?? '') ?></span>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?php if($usuarioInexistente): ?>
                                            <span style="color: #b91c1c;"><i class="fas fa-user-slash mr-1"></i> Baja Institucional</span>
                                        <?php else: ?>
                                            <?= htmlspecialchars($cta['NOMBRE_USUARIO']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-size: 0.8rem;"><?= htmlspecialchars($cta['GLOSA_CUENTA']) ?></span></td>
                                    <td class="text-center">
                                        <?php if ($cta['ESTADO_CUENTA'] == 1): ?>
                                            <span class="interactive-badge fecha-ok">Alta</span>
                                        <?php elseif ($cta['ESTADO_CUENTA'] == 3): ?>
                                            <span class="interactive-badge badge-pending">Pendiente</span>
                                        <?php else: ?>
                                            <span class="interactive-badge fecha-alerta">Baja</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2" style="opacity:0.2;"></i>
                        <h6 style="font-size: 0.85rem;">No hay cuentas recientes</h6>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="flex: 0 0 40%; display: flex; flex-direction: column; justify-content: center; padding: 1rem 1.5rem;">
                    <div class="chart-container-title">
                        <i class="fas fa-chart-pie mr-1"></i> Distribución de Cuentas Activas
                        <p class="mt-1 mb-0" style="font-size: 0.65rem; color: #94a3b8; font-weight: 500; text-transform: none;">* Clic en un color para ir al buscador</p>
                    </div>
                    <div style="position: relative; height: 190px; width: 100%; display: flex; justify-content: center; align-items: center;">
                        <canvas id="softwareDoughnutChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="card-custom animate-up" style="animation-delay: 0.2s">
        <div class="card-header-custom">
            <i class="fas fa-user-astronaut mr-2 text-muted" style="font-size: 1rem;"></i> Usuario mas reciente
        </div>
        
        <?php if ($nuevo_usr): ?>
            <div class="card-profile-hover" onclick="window.location='/SIUGI/menu_usuario?id=<?= $nuevo_usr['id'] ?>'">
                
                <div class="user-avatar-container-large">
                    <?php 
                        $usuario_nuevo = $nuevo_usr['usuario'] ?? '';
                        $foto_nuevo_url = "/SIUGI/public/avatar/" . $usuario_nuevo . ".jpg";
                        $check_nuevo_path = $_SERVER['DOCUMENT_ROOT'] . $foto_nuevo_url;
                        
                        if (!empty($usuario_nuevo) && file_exists($check_nuevo_path)):
                    ?>
                        <img src="<?php echo $foto_nuevo_url; ?>">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                
                <div class="profile-info">
                    <h2 class="profile-name"><?= htmlspecialchars($nuevo_usr['nombre']) ?></h2>
                    <p class="profile-rut"><i class="fas fa-id-card mr-2"></i><?= htmlspecialchars($nuevo_usr['rut']) ?></p>
                    
                    <div class="profile-badges">
                        <span class="profile-badge-item">Cargo: <?= htmlspecialchars($nuevo_usr['cargo'] ?: 'Sin Asignar') ?></span>
                        
                        <?php if(!empty($nuevo_usr['fec_nacimiento'])): ?>
                        <span class="profile-badge-item">Nacimiento: <?= date('d/m/Y', strtotime($nuevo_usr['fec_nacimiento'])) ?></span>
                        <?php endif; ?>
                        
                        <span class="profile-badge-item">Inicio: <?= date('d/m/Y', strtotime($nuevo_usr['fec_inicio_funciones'])) ?></span>
                        <span class="profile-badge-item"><?= $cuenta_count ?> Cuentas en Alta</span>
                        <span class="profile-badge-item" style="background: var(--warning-bg); border-color: #fde68a; color: #92400e;">
                            <?= $cuenta_pendientes_count ?> Cuentas Pendientes
                        </span>
                    </div>
                </div>
                
                <div style="flex-shrink: 0; padding-left: 1rem;">
                    <i class="fas fa-chevron-right text-muted" style="font-size: 2.5rem; opacity: 0.2;"></i>
                </div>
            </div>
        <?php else: ?>
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-user-slash fa-3x mb-3" style="opacity:0.2;"></i>
                <h6>No hay usuarios recientes registrados</h6>
            </div>
        <?php endif; ?>
    </div>

    <div class="animate-up" style="animation-delay: 0.3s">
        <div class="collapse-btn" data-toggle="collapse" data-target="#collapseIngresos">
            <span><i class="fas fa-boxes mr-2"></i> Últimos Ingresos de Bienes</span>
            <i class="fas fa-chevron-down toggle-icon" id="toggleIconIngresos"></i>
        </div>
        <div id="collapseIngresos" class="collapse">
            <div class="card-custom">
                <div class="table-responsive">
                    <table class="table table-hover m-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Dispositivo / Modelo</th>
                                <th>Sede / Ubicación</th>
                                <th>Serie</th>
                                <th>Fecha</th>
                                <th>Registra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimos_disp as $d): ?>
                            <tr class="clickable-row" onclick="window.location='/SIUGI/editar_bien?id=<?php echo $d['ID']; ?>'">
                                <td class="text-center"><span class="table-id-badge">#<?php echo $d['ID']; ?></span></td>
                                <td>
                                    <strong class="row-title text-dark"><?php echo htmlspecialchars($d['SUBCATEGORIA'] ?? ''); ?></strong><br>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($d['MODELO'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-dark"><?php echo htmlspecialchars($d['GLOSA_FISCALIA'] ?? ''); ?></span><br>
                                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($d['UBICACION_DETALLE'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <?php if(!empty(trim($d['SERIE']))): ?>
                                        <span class="table-serie"><?php echo htmlspecialchars($d['SERIE']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted font-italic" style="font-size: 0.7rem;">S/N</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="text-dark font-weight-bold" style="font-size: 0.75rem;"><?php echo !empty($d['FECHA_REGISTRO']) ? date('d/m/Y', strtotime($d['FECHA_REGISTRO'])) : 'N/A'; ?></span></td>
                                <td><span class="user-badge"><?php echo htmlspecialchars($d['QUIEN_REGISTRA'] ?? 'N/A'); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="animate-up" style="animation-delay: 0.4s">
        <div class="collapse-btn" data-toggle="collapse" data-target="#collapseActividad">
            <span><i class="fas fa-history mr-2"></i> Últimos movimientos y ediciones del inventario</span>
            <i class="fas fa-chevron-down toggle-icon" id="toggleIconActividad"></i>
        </div>
        <div id="collapseActividad" class="collapse">
            <div class="card-custom">
                <div class="activity-feed">
                    <?php foreach ($ultimos_movimientos as $m): ?>
                        <?php 
                            $es_anulacion = (stripos($m['TIPO'], 'Anulacion') !== false);
                            $es_edicion = (stripos($m['TIPO'], 'Edicion') !== false);
                            $indicator_class = $es_anulacion ? 'type-anulacion' : ($es_edicion ? 'type-edicion' : 'type-movimiento');
                        ?>
                        <div class="activity-item" onclick="window.location='/SIUGI/editar_bien?id=<?php echo $m['ID_DISPOSITIVO']; ?>'">
                            <div class="indicator-bar <?php echo $indicator_class; ?>"></div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="font-weight-bold" style="font-size: 0.85rem; color: var(--primary);">
                                        <?php echo htmlspecialchars($m['GLOSA_SUBCATEGORIA'] ?? 'Desconocido'); ?>
                                    </span>
                                    <span class="badge-soft badge-inv ml-2">
                                        <?php echo !empty($m['CODIGO_INVENTARIO']) ? 'Inv: ' . $m['CODIGO_INVENTARIO'] : 'S/N'; ?>
                                    </span>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="far fa-clock mr-1"></i><?php echo date('d/m/y H:i', strtotime($m['FECHA_MOVIMIENTO'])); ?></small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div style="font-size: 12px;">
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const swLabels = <?= json_encode($chart_labels) ?>;
    const swValues = <?= json_encode($chart_values) ?>;
</script>

<script src="/SIUGI/public/assets/js/inicio.js"></script>

<?php include __DIR__ . '/../templates/layout_bottom.php'; ?>