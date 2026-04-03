<?php
// views/bienes/consultar_bienes.php

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();

// =======================================================
// BLOQUE AJAX (CORREGIDO)
// =======================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    
    $limit = 5; 
    $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
    $offset = ($page - 1) * $limit;

    $where = " WHERE 1=1";
    $params = [];

    if (isset($_GET['baja']) && $_GET['baja'] == '1') {
        $where .= " AND ESTADO = 'Baja'";
    } elseif (!empty($_GET['estado_filtro'])) {
        $where .= " AND ESTADO = :estado";
        $params[':estado'] = $_GET['estado_filtro'];
    } else {
        $where .= " AND ESTADO != 'Baja'";
    }

    if (!empty($_GET['sede'])) { $where .= " AND FISCALIA_UBICACION = :sede"; $params[':sede'] = (int)$_GET['sede']; }
    if (!empty($_GET['edificio'])) { $where .= " AND EDIFICIO_UBICACION = :edificio"; $params[':edificio'] = (int)$_GET['edificio']; }
    if (!empty($_GET['division'])) { $where .= " AND DIVISION_UBICACION = :division"; $params[':division'] = (int)$_GET['division']; }
    if (!empty($_GET['id_ubicacion'])) { $where .= " AND ID_UBICACION = :id_ubicacion"; $params[':id_ubicacion'] = (int)$_GET['id_ubicacion']; }
    if (isset($_GET['bodega']) && $_GET['bodega'] == '1') { $where .= " AND ID_UBICACION = 2"; }
    if (!empty($_GET['f_inicio'])) { $where .= " AND FECHA_REGISTRO >= :f_inicio"; $params[':f_inicio'] = $_GET['f_inicio']; }
    if (!empty($_GET['f_fin'])) { $where .= " AND FECHA_REGISTRO <= :f_fin"; $params[':f_fin'] = $_GET['f_fin']; }
    if (!empty($_GET['tipo'])) { $where .= " AND SUBCATEGORIA = :tipo"; $params[':tipo'] = $_GET['tipo']; }
    if (!empty($_GET['id_disp'])) { $where .= " AND ID = :id_disp"; $params[':id_disp'] = (int)$_GET['id_disp']; }
    if (!empty($_GET['serie_busqueda'])) { $where .= " AND SERIE LIKE :serie"; $params[':serie'] = '%' . $_GET['serie_busqueda'] . '%'; }
    if (!empty($_GET['cod_inv'])) { $where .= " AND CODIGO_INVENTARIO = :cod_inv"; $params[':cod_inv'] = (int)$_GET['cod_inv']; }
    
    if (!empty($_GET['modelo_busqueda'])) {
        $palabras_modelo = preg_split('/\s+/', trim($_GET['modelo_busqueda']));
        foreach ($palabras_modelo as $i => $palabra) {
            $where .= " AND MODELO LIKE :modelo_$i";
            $params[":modelo_$i"] = '%' . $palabra . '%';
        }
    }

    $stmt_count = $pdo_inv->prepare("SELECT COUNT(*) as total FROM vista_dispositivos $where");
    $stmt_count->execute($params);
    $total_registros = $stmt_count->fetchColumn();
    $total_pages = ceil($total_registros / $limit);

    $q_data = "SELECT vista_dispositivos.*, sedes.GLOSA_FISCALIA AS NOMBRE_SEDE 
               FROM vista_dispositivos 
               LEFT JOIN sedes ON vista_dispositivos.FISCALIA_UBICACION = sedes.ID_SEDE 
               $where ORDER BY vista_dispositivos.ID DESC LIMIT $limit OFFSET $offset";
               
    $stmt_data = $pdo_inv->prepare($q_data);
    $stmt_data->execute($params);
    $data = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    $q_stats = "SELECT SUBCATEGORIA, COUNT(*) as cant FROM vista_dispositivos $where GROUP BY SUBCATEGORIA";
    $stmt_stats = $pdo_inv->prepare($q_stats);
    $stmt_stats->execute($params);
    $chart_sets = ['principal' => []];
    while($st = $stmt_stats->fetch(PDO::FETCH_ASSOC)) {
        $chart_sets['principal'][$st['SUBCATEGORIA']] = $st['cant'];
    }

    if (isset($_GET['comparativo']) && $_GET['comparativo'] == '1') {
        $where_b = " WHERE 1=1";
        $params_b = [];
        if (isset($_GET['baja']) && $_GET['baja'] == '1') { $where_b .= " AND ESTADO = 'Baja'"; } 
        else { $where_b .= " AND ESTADO != 'Baja'"; }
        if (!empty($_GET['sede_b'])) { $where_b .= " AND FISCALIA_UBICACION = :sede_b"; $params_b[':sede_b'] = (int)$_GET['sede_b']; }
        if (!empty($_GET['edificio_b'])) { $where_b .= " AND EDIFICIO_UBICACION = :edificio_b"; $params_b[':edificio_b'] = (int)$_GET['edificio_b']; }
        if (!empty($_GET['division_b'])) { $where_b .= " AND DIVISION_UBICACION = :division_b"; $params_b[':division_b'] = (int)$_GET['division_b']; }
        if (!empty($_GET['id_ubicacion_b'])) { $where_b .= " AND ID_UBICACION = :id_ubicacion_b"; $params_b[':id_ubicacion_b'] = (int)$_GET['id_ubicacion_b']; }
        if (isset($_GET['bodega']) && $_GET['bodega'] == '1') { $where_b .= " AND ID_UBICACION = 2"; }
        if (!empty($_GET['f_inicio'])) { $where_b .= " AND FECHA_REGISTRO >= :f_inicio_b"; $params_b[':f_inicio_b'] = $_GET['f_inicio']; }
        if (!empty($_GET['f_fin'])) { $where_b .= " AND FECHA_REGISTRO <= :f_fin_b"; $params_b[':f_fin_b'] = $_GET['f_fin']; }
        if (!empty($_GET['modelo_busqueda'])) {
            $palabras_modelo_b = preg_split('/\s+/', trim($_GET['modelo_busqueda']));
            foreach ($palabras_modelo_b as $i => $palabra) {
                $where_b .= " AND MODELO LIKE :modelo_b_$i";
                $params_b[":modelo_b_$i"] = '%' . $palabra . '%';
            }
        }
        $res_stats_b = $pdo_inv->prepare("SELECT SUBCATEGORIA, COUNT(*) as cant FROM vista_dispositivos $where_b GROUP BY SUBCATEGORIA");
        $res_stats_b->execute($params_b);
        $chart_sets['secundario'] = [];
        while($stb = $res_stats_b->fetch(PDO::FETCH_ASSOC)) {
            $chart_sets['secundario'][$stb['SUBCATEGORIA']] = $stb['cant'];
        }
    }

    echo json_encode([
        'data' => $data,
        'total' => $total_registros,
        'pages' => $total_pages,
        'page' => $page,
        'chart' => $chart_sets
    ]);
    exit;
}

$subcats_list = $pdo_inv->query("SELECT DISTINCT SUBCATEGORIA FROM vista_dispositivos ORDER BY SUBCATEGORIA ASC")->fetchAll(PDO::FETCH_ASSOC);
$estados_list = $pdo_inv->query("SELECT DISTINCT ESTADO FROM vista_dispositivos ORDER BY ESTADO ASC")->fetchAll(PDO::FETCH_ASSOC);
$data_sedes = $pdo_inv->query("SELECT ID_SEDE, GLOSA_FISCALIA FROM sedes ORDER BY GLOSA_FISCALIA")->fetchAll(PDO::FETCH_ASSOC);
$data_edificios = $pdo_inv->query("SELECT ID_SEDE, ID_EDIFICIO, GLOSA_EDIFICIO FROM edificios ORDER BY GLOSA_EDIFICIO")->fetchAll(PDO::FETCH_ASSOC);
$data_divisiones = $pdo_inv->query("SELECT ID_EDIFICIO, ID_DIVISION, GLOSA_DIVISION FROM divisiones ORDER BY GLOSA_DIVISION")->fetchAll(PDO::FETCH_ASSOC);
$data_ubicaciones = $pdo_inv->query("SELECT ID_UBICACION, DIVISION_UBICACION, GLOSA_UBICACION FROM ubicaciones ORDER BY GLOSA_UBICACION")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/consultar_bienes.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.6rem; color: var(--text-main);">Consultar Bienes</h1>
        <p class="text-muted" style="font-size: 0.85rem;">Búsqueda y filtros avanzados dinámicos</p>
    </div>

    <div class="page-layout">
        <div class="animate-up" style="animation-delay: 0.1s;">
            <form id="filterForm">
                <div class="sidebar-wrapper">
                    <input type="hidden" id="currentPage" name="p" value="1">
                    <button type="button" class="btn-limpiar" onclick="limpiarFiltros()">
                        <i class="fas fa-broom text-muted"></i> <span>Limpiar Filtros</span>
                    </button>
                    <div class="sidebar-scrollable">
                        <div class="switches-wrapper">
                            <div class="switch-container" id="cont_comp">
                                <span class="font-weight-bold"><i class="fas fa-columns mr-1"></i> Comparativo</span>
                                <label class="switch"><input type="checkbox" name="comparativo" value="1" onchange="toggleComparativo(this)"><span class="slider"></span></label>
                            </div>
                            <div class="switch-container" id="cont_baja">
                                <span class="font-weight-bold"><i class="fas fa-arrow-down mr-1"></i> Ver Bajas</span>
                                <label class="switch"><input type="checkbox" name="baja" value="1" onchange="toggleStyle(this, 'cont_baja', 'active-baja'); triggerSearch();"><span class="slider"></span></label>
                            </div>
                            <div class="switch-container" id="cont_bodega">
                                <span class="font-weight-bold"><i class="fas fa-boxes mr-1"></i> Solo Bodega</span>
                                <label class="switch"><input type="checkbox" name="bodega" value="1" onchange="toggleStyle(this, 'cont_bodega', 'active-bodega'); triggerSearch();"><span class="slider"></span></label>
                            </div>
                        </div>

                        <div class="filter-box crema">
                            <div class="filter-title"><i class="fas fa-map-marker-alt mr-2 text-muted"></i> Ubicación (A)</div>
                            <div class="input-group-custom"><label>Sede</label><select name="sede" id="sel_sede" onchange="updateMando('sede', '')"><option value="">Todas</option></select></div>
                            <div class="input-group-custom"><label>Edificio</label><select name="edificio" id="sel_edificio" onchange="updateMando('edificio', '')" disabled><option value="">Todos</option></select></div>
                            <div class="input-group-custom"><label>División</label><select name="division" id="sel_division" onchange="updateMando('division', '')" disabled><option value="">Todas</option></select></div>
                            <div class="input-group-custom"><label>Ubicación</label><select name="id_ubicacion" id="sel_ubicacion" disabled><option value="">Todas</option></select></div>
                        </div>

                        <div id="filtros_b" class="anim-container filter-box crema anim-hide">
                            <div class="filter-title" style="color: var(--warning);"><i class="fas fa-map-marker-alt mr-2"></i> Ubicación (B)</div>
                            <div class="input-group-custom"><label>Sede B</label><select name="sede_b" id="sel_sede_b" onchange="updateMando('sede', '_b')"><option value="">Todas</option></select></div>
                            <div class="input-group-custom"><label>Edificio B</label><select name="edificio_b" id="sel_edificio_b" onchange="updateMando('edificio', '_b')" disabled><option value="">Todos</option></select></div>
                            <div class="input-group-custom"><label>División B</label><select name="division_b" id="sel_division_b" onchange="updateMando('division', '_b')" disabled><option value="">Todas</option></select></div>
                            <div class="input-group-custom"><label>Ubicación B</label><select name="id_ubicacion_b" id="sel_ubicacion_b" disabled><option value="">Todas</option></select></div>
                        </div>

                        <div class="filter-box">
                            <div class="filter-title"><i class="fas fa-tags mr-2 text-muted"></i> Atributos</div>
                            <div class="input-group-custom"><label>Desde Fecha</label><input type="date" name="f_inicio"></div>
                            <div class="input-group-custom"><label>Hasta Fecha</label><input type="date" name="f_fin"></div>
                            <div class="input-group-custom">
                                <label>Subcategoría</label>
                                <select name="tipo"><option value="">Todas</option>
                                    <?php foreach($subcats_list as $t): ?>
                                        <option value="<?php echo htmlspecialchars($t['SUBCATEGORIA']); ?>"><?php echo htmlspecialchars($t['SUBCATEGORIA']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="input-group-custom">
                                <label>Estado CGU</label>
                                <select name="estado_filtro"><option value="">Todos (Excp. Baja)</option>
                                    <?php foreach($estados_list as $e): if($e['ESTADO'] == 'Baja') continue; ?>
                                        <option value="<?php echo htmlspecialchars($e['ESTADO']); ?>"><?php echo htmlspecialchars($e['ESTADO']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="filter-box">
                            <div class="filter-title"><i class="fas fa-keyboard mr-2 text-muted"></i> Búsqueda Directa</div>
                            <div class="input-group-custom"><label>ID Disp.</label><input type="number" name="id_disp" placeholder="Ej: 1045"></div>
                            <div class="input-group-custom"><label>Modelo</label><input type="text" name="modelo_busqueda" placeholder="Buscar por modelo"></div>
                            <div class="input-group-custom"><label>Num. Serie</label><input type="text" name="serie_busqueda" placeholder="Buscar por serie"></div>
                            <div class="input-group-custom"><label>Cod. Inv.</label><input type="number" name="cod_inv" placeholder="Buscar por código"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="main-results animate-up" style="animation-delay: 0.2s">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="m-0 font-weight-bold d-flex align-items-center" style="color: var(--text-main); font-size: 1.1rem;">
                    <i class="fas fa-list-ul mr-2 text-muted"></i> 
                    <span id="textResultados">Resultados (0)</span>
                    <i class="fas fa-circle-notch fa-spin ml-2 text-primary" id="loadingIcon" style="display: none; font-size: 1rem;"></i>
                </h5>
                <a href="/SIUGI/exportar_bienes" id="btnExportPDF" target="_blank" class="btn btn-sm font-weight-bold" style="display:none; background: var(--danger-bg); color: var(--danger); border: 1px solid #fecaca; border-radius: 6px; padding: 6px 12px; font-size: 0.8rem;">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </div>

            <div class="card-custom">
                <div class="table-responsive" style="min-height: 250px;">
                    <table class="table table-borderless m-0" id="tablaResultados">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subcategoría</th>
                                <th>Fiscalía</th>
                                <th>Ubicación</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Estado</th>
                                <th>Serie</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="card-footer bg-white" style="border-top: 1px solid var(--border); padding: 0.8rem 1rem;" id="containerPaginacion"></div>
            </div>

            <div class="card-custom" id="containerGraficoWrapper" style="display:none;">
                <div class="collapse-btn" data-toggle="collapse" data-target="#collapseGrafico">
                    <span><i class="fas fa-chart-pie mr-2"></i> Estadísticas Visuales</span>
                    <i class="fas fa-chevron-up" id="toggleIconGrafico"></i>
                </div>
                <div id="collapseGrafico" class="collapse show">
                    <div class="card-body" style="padding: 1.5rem;">
                        <div style="height:300px"><canvas id="mainChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const sedes = <?= json_encode($data_sedes); ?>;
    const edificios = <?= json_encode($data_edificios); ?>;
    const divisiones = <?= json_encode($data_divisiones); ?>;
    const ubicaciones = <?= json_encode($data_ubicaciones); ?>;
</script>

<script src="/SIUGI/public/assets/js/consultar_bienes.js"></script>
<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>