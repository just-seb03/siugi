<?php
ob_start();
session_start();

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo_info       = $db->getInfoConnection();
$pdo_cuentas    = $db->getCuentasConnection();

// --- LECTURA DE DIRECTORIO DE FORMULARIOS ---
$formularios_dir = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/formularios';
$formularios_files = [];
if (is_dir($formularios_dir)) {
    $files = scandir($formularios_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $formularios_files[] = $file;
        }
    }
}
// --------------------------------------------

$id_usuario = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuarioDataFiltrado = [];
$tipo_usuario = 0;
$sede_usuario = '';
$tipo_str = 'Desconocido';
$usuarioRaw = null;

if ($id_usuario > 0) {
    $stmt_info = $pdo_info->prepare("SELECT Nombre, usuario, Rut, Cod_Fiscalia, Cargo, correo_electronico, telefono, ip, tipo_usuario, fec_inicio_funciones, fec_termino_funciones, estado FROM usuarios WHERE id = ?"); 
    $stmt_info->execute([$id_usuario]);
    $usuarioRaw = $stmt_info->fetch();

    if ($usuarioRaw) {
        $tipo_usuario = (int)$usuarioRaw['tipo_usuario'];
        $sede_usuario = $usuarioRaw['Cod_Fiscalia'];
        
        if ($tipo_usuario === 1) $tipo_str = 'Fiscal';
        elseif ($tipo_usuario === 2) $tipo_str = 'Funcionario';
        elseif ($tipo_usuario === 3) $tipo_str = 'Alumno en práctica';

        $stmt_sede = $pdo_cuentas->prepare("SELECT GLOSA_FISCALIA FROM sedes WHERE ID_SEDE = ?");
        $stmt_sede->execute([$sede_usuario]);
        $nombre_sede = $stmt_sede->fetchColumn();
        $sede_str = $nombre_sede ? $nombre_sede : 'Sede Desconocida (' . $sede_usuario . ')';

        $fecha_inicio = !empty($usuarioRaw['fec_inicio_funciones']) ? date('d/m/Y', strtotime($usuarioRaw['fec_inicio_funciones'])) : null;
        $fecha_termino = !empty($usuarioRaw['fec_termino_funciones']) ? date('d/m/Y', strtotime($usuarioRaw['fec_termino_funciones'])) : null;

        $usuarioDataFiltrado = [
            'Nombre'            => $usuarioRaw['Nombre'],
            'Rut'               => $usuarioRaw['Rut'],
            'Tipo Usuario'      => $tipo_str,
            'Sede / Fiscalía'   => $sede_str,
            'Cargo'             => $usuarioRaw['Cargo'],
            'Correo'            => $usuarioRaw['correo_electronico'],
            'Teléfono'          => $usuarioRaw['telefono'],
            'Dirección IP'      => $usuarioRaw['ip'],
            'Inicio Funciones'  => $fecha_inicio,
            'Término Funciones' => $fecha_termino
        ];
    }
}

$softwares = $pdo_cuentas->query("SELECT ID_SOFTWARE, GLOSA_SOFTWARE FROM software ORDER BY GLOSA_SOFTWARE ASC")->fetchAll();
$sedes = $pdo_cuentas->query("SELECT ID_SEDE, GLOSA_FISCALIA FROM sedes ORDER BY GLOSA_FISCALIA ASC")->fetchAll();

$lista_nombres_sw = [];
if ($tipo_usuario === 1) { 
    $lista_nombres_sw = ['SAF', 'SAO', 'MonitoWEB', 'RPA', 'WINDOWS', 'CORREO ELECTRONICO', 'OPA', 'SIAU', 'INTERCONEXION 3', 'RPP', 'VPN', 'CONSULTA INTEGRADA DE CAUSAS', 'ESCRITORIO APLICACIONES FN'];
} elseif ($tipo_usuario === 2) { 
    $lista_nombres_sw = ['SAF', 'SAO', 'MonitoWEB', 'RPA', 'WINDOWS', 'CORREO ELECTRONICO', 'OPA', 'SIAU', 'INTERCONEXION 3', 'VPN', 'CONSULTA INTEGRADA DE CAUSAS', 'ESCRITORIO APLICACIONES FN'];
} elseif ($tipo_usuario === 3) { 
    $lista_nombres_sw = ['WINDOWS', 'CORREO ELECTRONICO', 'ESCRITORIO APLICACIONES FN'];
}

$mapa_software = [];
foreach ($softwares as $sw) {
    $mapa_software[strtoupper($sw['GLOSA_SOFTWARE'])] = $sw['ID_SOFTWARE'];
}

$ids_requeridos = [];
$sw_no_encontrados = [];
foreach ($lista_nombres_sw as $sw) {
    $sw_u = strtoupper(trim($sw));
    if (isset($mapa_software[$sw_u])) $ids_requeridos[] = $mapa_software[$sw_u];
    else $sw_no_encontrados[] = $sw; 
}

$stmt_ex = $pdo_cuentas->prepare("SELECT ID_SOFTWARE FROM cuentas WHERE USUARIO = ? AND ESTADO_CUENTA IN (1, 3)");
$stmt_ex->execute([$id_usuario]);
$ids_existentes = $stmt_ex->fetchAll(PDO::FETCH_COLUMN);

$ids_faltantes = array_diff($ids_requeridos, $ids_existentes);
$cantidad_existentes = count(array_intersect($ids_requeridos, $ids_existentes));
$cantidad_crear = count($ids_faltantes);

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/menu_usuario.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="content-header mb-4" style="animation: fadeInUp 0.5s ease-out forwards;">
        <div class="container-fluid d-flex justify-content-between align-items-end flex-wrap" style="gap: 1rem;">
            <div>
                <h1 class="m-0 font-weight-bold" style="color: var(--text-main); font-size: 1.75rem; letter-spacing: -0.025em;">Ficha del Usuario</h1>
            </div>
            <a href="javascript:history.back()" class="btn-custom btn-secondary-custom">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            <div class="card card-custom card-custom-1">
                <div class="card-header-custom">
                    <h4 class="mb-0 font-weight-bold" style="color: var(--text-main); font-size: 1.1rem;">
                        <i class="fas fa-user-circle mr-2" style="color: var(--text-muted);"></i> Datos Personales (ID #<?= htmlspecialchars($id_usuario) ?>)
                    </h4>
                    <a href="/SIUGI/ficha_usuario?id=<?= htmlspecialchars($id_usuario) ?>" target="_blank" class="btn-action btn-action-success" style="text-decoration: none;">
                        <i class="fas fa-file-pdf mr-2"></i> Ficha
                    </a>
                </div>
                
                <div class="card-body" style="padding: 2rem;">
                    <?php if (!empty($usuarioDataFiltrado)): ?>
                        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start" style="gap: 2.5rem;">
                            <div class="d-flex flex-column align-items-center" style="gap: 0.75rem;">
                                <div class="user-avatar-large">
                                    <img src="/SIUGI/public/avatar/<?= rawurlencode($usuarioRaw['usuario']) ?>.jpg?v=1" onerror="this.onerror=null; this.outerHTML='<i class=\'fas fa-user\'></i>';">
                                </div>
                                <?php if (isset($usuarioRaw['estado']) && $usuarioRaw['estado'] == 0): ?>
                                    <span class="interactive-badge fecha-ok" style="font-size: 0.8rem; padding: 0.35rem 1rem; width: 100%;">
                                        <i class="fas fa-check-circle mr-1"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="interactive-badge fecha-alerta" style="font-size: 0.8rem; padding: 0.35rem 1rem; width: 100%;">
                                        <i class="fas fa-times-circle mr-1"></i> Inactivo
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="row flex-grow-1 w-100">
                                <?php foreach ($usuarioDataFiltrado as $etiqueta => $valor): ?>
                                    <div class="col-md-4 col-sm-6 mb-4">
                                        <div class="data-label"><?= htmlspecialchars($etiqueta) ?></div>
                                        <p class="data-value"><?= htmlspecialchars($valor ?? 'N/A') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x mb-3" style="opacity: 0.2;"></i>
                            <h5 class="text-muted font-weight-bold">No se encontró información</h5>
                            <p style="font-size: 0.85rem; color: var(--text-muted);">El usuario ID #<?= htmlspecialchars($id_usuario) ?> no existe.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($usuarioDataFiltrado)): ?>
                
                <div class="card card-custom card-custom-2">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center" style="gap: 1rem; flex-wrap: wrap;">
                            <h4 class="mb-0 font-weight-bold" style="font-size: 1.1rem; color: var(--text-main);" id="tituloTabla">       
                                <i class="fas fa-key mr-2" style="color: var(--text-muted);"></i> Cuentas <span style="color: var(--primary); font-weight: normal; font-size: 1rem;">(Activas / Pendientes)</span>
                            </h4>
                            <div class="d-flex gap-2" style="gap: 0.5rem; margin-left: 0.5rem;">
                                <button type="button" class="btn-action btn-action-primary" onclick="togglePanel('panelCrear')">
                                    <i class="fas fa-plus mr-2"></i> Crear
                                </button>
                                <button type="button" class="btn-action btn-action-success" onclick="togglePanel('panelAutoAsignar')">
                                    <i class="fas fa-magic mr-2"></i> Auto-Asignar
                                </button>
                                <a href="/SIUGI/exportar_cuentas?usr=<?= htmlspecialchars($id_usuario) ?>" target="_blank" class="btn-action btn-action-danger" style="text-decoration: none;">
                                    <i class="fas fa-file-pdf mr-2"></i> Exportar
                                </a>
                                <button type="button" class="btn-action btn-action-info text-white" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);" onclick="openModalFormularios()">
                                    <i class="fas fa-folder-open mr-2"></i> Formularios
                                </button>
                            </div>
                        </div>
                        
                        <div class="switch-container">
                            <span class="text-activa active-label" id="lblActivas" style="color: var(--text-muted);">Activas</span>
                            <label class="switch mx-2">
                                <input type="checkbox" id="ver_bajas_switch" onchange="fetchCuentas(1)">
                                <span class="slider"></span>
                            </label>
                            <span class="text-baja" id="lblBajas" style="color: var(--text-muted);">Bajas</span>
                        </div>
                    </div>
                    
                    <div id="panelAutoAsignar" style="display: none; background-color: #f8fafc; border-bottom: 1px solid var(--border);">
                        <div class="card-body" style="padding: 2rem;">
                            <h5 class="font-weight-bold mb-4" style="color: var(--text-main); font-size: 1rem;">
                                <i class="fas fa-magic mr-2" style="color: var(--success);"></i> Asignación automática de perfil
                            </h5>
                            <?php if ($tipo_usuario === 0): ?>
                                <div class="alert-custom" style="background-color: var(--danger-bg); color: #991b1b; border-color: #fecaca;">
                                    <i class="fas fa-times-circle mt-1" style="font-size: 1.25rem;"></i>
                                    <div>
                                        <h6 class="font-weight-bold mb-1">Perfil Inválido</h6>
                                        <p class="mb-0">El usuario no tiene un tipo de perfil válido para inicializar cuentas.</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end"><button type="button" class="btn-custom btn-secondary-custom" onclick="togglePanel('panelAutoAsignar')">Cerrar</button></div>
                            <?php elseif ($cantidad_crear == 0): ?>
                                <div class="alert-custom" style="background-color: var(--success-bg); color: #166534; border-color: #bbf7d0;">
                                    <i class="fas fa-check-circle mt-1" style="font-size: 1.25rem;"></i>
                                    <div>
                                        <h6 class="font-weight-bold mb-1">Perfil Completo</h6>
                                        <p class="mb-0">El usuario ya tiene inicializadas (activas o pendientes) todas las cuentas correspondientes al estamento <strong><?= $tipo_str ?></strong>.</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end"><button type="button" class="btn-custom btn-secondary-custom" onclick="togglePanel('panelAutoAsignar')">Cerrar</button></div>
                            <?php else: ?>
                                <?php if ($cantidad_existentes > 0): ?>
                                    <div class="alert-custom" style="background-color: var(--warning-bg); color: #92400e; border-color: #fef3c7;">
                                        <i class="fas fa-exclamation-triangle mt-1" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">Aviso de cuentas existentes</h6>
                                            <p class="mb-0">Detectamos que el usuario ya posee <strong><?= $cantidad_existentes ?></strong> cuenta(s) activa(s) o pendiente(s). Sólo se crearán las <strong><?= $cantidad_crear ?></strong> cuentas faltantes.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert-custom" style="background-color: #f0f9ff; color: #1e40af; border-color: #bfdbfe;">
                                        <i class="fas fa-info-circle mt-1" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">Inicialización Nueva</h6>
                                            <p class="mb-0">Se crearán <strong><?= $cantidad_crear ?></strong> cuentas nuevas en estado <strong>Pendiente</strong>, asociadas a su sede.</p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (count($sw_no_encontrados) > 0): ?>
                                    <div class="alert-custom" style="background-color: var(--danger-bg); color: #991b1b; border-color: #fecaca;">
                                        <i class="fas fa-exclamation-circle mt-1" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <strong class="d-block mb-1">Atención:</strong> 
                                            <p class="mb-0">Los siguientes sistemas no existen en el catálogo y serán ignorados: <em><?= implode(', ', $sw_no_encontrados) ?></em></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <form action="/SIUGI/src/asignar_cuentas" method="POST">
                                    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                                    <input type="hidden" name="sede_usuario" value="<?= htmlspecialchars($sede_usuario) ?>">
                                    <input type="hidden" name="confirmar_init" value="1">
                                    <?php foreach ($ids_faltantes as $idf): ?>
                                        <input type="hidden" name="sw_faltantes[]" value="<?= $idf ?>">
                                    <?php endforeach; ?>
                                    
                                    <div class="d-flex justify-content-end gap-3 mt-4" style="gap: 1rem;">
                                        <button type="button" class="btn-custom btn-secondary-custom" onclick="togglePanel('panelAutoAsignar')">Cancelar</button>
                                        <button type="submit" class="btn-action btn-action-success"><i class="fas fa-check mr-2"></i>Confirmar Inicialización</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div id="panelCrear" style="display: none; background-color: #f8fafc; border-bottom: 1px solid var(--border);">
                        <div class="card-body" style="padding: 2rem;">
                            <h5 class="font-weight-bold mb-4" style="color: var(--text-main); font-size: 1rem;">
                                <i class="fas fa-plus-circle mr-2" style="color: var(--primary);"></i> Creación Manual de Cuenta
                            </h5>
                            <form action="/SIUGI/src/crear_cuenta" method="POST">
                                <input type="hidden" name="confirmar_crear" value="1">
                                <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">

                                <div class="row">
                                    <div class="col-md-6 form-group mb-4">
                                        <label>Software / Sistema <span class="text-danger">*</span></label>
                                        <select name="software" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($softwares as $sw): ?>
                                                <option value="<?= $sw['ID_SOFTWARE'] ?>"><?= htmlspecialchars($sw['GLOSA_SOFTWARE']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group mb-4">
                                        <label>Sede <span class="text-danger">*</span></label>
                                        <select name="sede" class="form-control" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($sedes as $sd): ?>
                                                <option value="<?= $sd['ID_SEDE'] ?>" <?= $sd['ID_SEDE'] == $sede_usuario ? 'selected' : '' ?>><?= htmlspecialchars($sd['GLOSA_FISCALIA']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8 form-group mb-4">
                                        <label>Glosa Cuenta</label>
                                        <input type="text" name="glosa" class="form-control" placeholder="Ej: vpn_usuario">
                                    </div>
                                    <div class="col-md-4 form-group mb-4">
                                        <label>Tipo de Cuenta</label>
                                        <select name="es_generica" class="form-control">
                                            <option value="0">Personal</option>
                                            <option value="1">Genérica</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group mb-4">
                                        <label>Req. Inicio</label>
                                        <input type="text" name="req_inicio" class="form-control" placeholder="N° Requerimiento">
                                    </div>
                                    <div class="col-md-4 form-group mb-4">
                                        <label>Req. Término</label>
                                        <input type="text" name="req_termino" class="form-control" placeholder="N° Requerimiento">
                                    </div>
                                    <div class="col-md-4 form-group mb-4">
                                        <label>Fecha Creación</label>
                                        <input type="date" name="fecha_creacion" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group mb-4">
                                        <label>Estado</label>
                                        <select name="estado" class="form-control">
                                            <option value="3">Pendiente</option>
                                            <option value="1">Alta (Activa)</option>
                                            <option value="0">Baja (Inactiva)</option>
                                        </select>
                                    </div>
                                </div>
                                <hr class="mt-2 mb-4" style="border-color: var(--border);">
                                <div class="d-flex justify-content-end gap-3" style="gap: 1rem;">
                                    <button type="button" class="btn-custom btn-secondary-custom" onclick="togglePanel('panelCrear')">Cancelar</button>
                                    <button type="submit" class="btn-action btn-action-primary"><i class="fas fa-save mr-2"></i>Guardar Cuenta</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-body p-0 ajax-wrapper" id="tableContainer">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th>ID_CUENTA</th>
                                        <th>SEDE</th>
                                        <th>SOFTWARE</th>
                                        <th>TIPO</th>
                                        <th>GLOSA_CUENTA</th>
                                        <th style="text-align: center;">ESTADO</th>
                                        <th>REQ. INICIO</th>
                                        <th>REQ. TÉRMINO</th>
                                        <th>CREACIÓN</th>
                                        <th style="text-align: right;">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody id="cuentasTbody"></tbody>
                            </table>
                        </div>
                        <div id="paginationContainer" class="pagination-custom"></div>
                    </div>
                </div>

                <div class="card card-custom card-custom-2 mt-4">
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center" style="gap: 1rem; flex-wrap: wrap;">
                            <h4 class="mb-0 font-weight-bold" style="font-size: 1.1rem; color: var(--text-main);" id="tituloTablaBienes">      
                                <i class="fas fa-desktop mr-2" style="color: var(--text-muted);"></i> Bienes <span style="color: var(--primary); font-weight: normal; font-size: 1rem;">(Alta)</span>
                            </h4>
                            <div class="d-flex gap-2" style="gap: 0.5rem; margin-left: 0.5rem;">
                                <a href="/SIUGI/agregar_bienes?usr_id=<?= htmlspecialchars($id_usuario) ?>" class="btn-action btn-action-primary" style="text-decoration: none;">
                                    <i class="fas fa-plus mr-2"></i> Añadir Bien
                                </a>
                                <a href="/SIUGI/exportar_bienes?usr=<?= htmlspecialchars($id_usuario) ?>" target="_blank" class="btn-action btn-action-danger" style="text-decoration: none;">
                                    <i class="fas fa-file-pdf mr-2"></i> Exportar
                                </a>
                            </div>
                        </div>

                        <div class="switch-container">
                            <span class="text-activa active-label" id="lblActivasBienes" style="color: var(--text-muted);">Alta</span>
                            <label class="switch mx-2">
                                <input type="checkbox" id="ver_bajas_bienes_switch" onchange="fetchBienes(1)">
                                <span class="slider"></span>
                            </label>
                            <span class="text-baja" id="lblBajasBienes" style="color: var(--text-muted);">Baja</span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0 ajax-wrapper" id="bienesTableContainer">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" style="white-space: nowrap;">
                                <thead>
                                    <tr>
                                        <th>ID_DISP</th>
                                        <th>SEDE</th>
                                        <th>TIPO</th>
                                        <th>MARCA / MODELO</th>
                                        <th>SERIE</th>
                                        <th>CÓDIGO ACTIVO FIJO</th>
                                        <th style="text-align: center;">ESTADO</th>
                                        <th>UBICACIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="bienesTbody"></tbody>
                            </table>
                        </div>
                        <div id="bienesPaginationContainer" class="pagination-custom"></div>
                    </div>
                </div>

            <?php endif; ?> 
        </div>
    </section>
</div>

<div id="modalFormularios" class="modal-custom">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h5 class="m-0 font-weight-bold" style="color: var(--text-main); font-size: 1.1rem;">
                <i class="fas fa-folder-open mr-2" style="color: #0ea5e9;"></i> Formularios Disponibles
            </h5>
            <span class="close-modal" onclick="closeModalFormularios()">&times;</span>
        </div>
        <div class="modal-body-custom">
            <?php if (empty($formularios_files)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-box-open fa-2x mb-3 text-muted" style="opacity: 0.5;"></i>
                    <p class="text-muted mb-0">No hay formularios disponibles en el directorio.</p>
                </div>
            <?php else: ?>
                <ul class="file-list">
                    <?php foreach ($formularios_files as $file): ?>
                        <li>
                            <?php 
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $icon = 'fa-file-alt text-primary';
                                if(in_array($ext, ['xls', 'xlsx', 'csv'])) $icon = 'fa-file-excel text-success';
                                elseif(in_array($ext, ['doc', 'docx'])) $icon = 'fa-file-word text-info';
                                elseif($ext == 'pdf') $icon = 'fa-file-pdf text-danger';
                            ?>
                            <i class="fas <?= $icon ?> mr-3" style="font-size: 1.2rem;"></i>
                            <a href="/SIUGI/public/formularios/<?= rawurlencode($file) ?>" target="_blank" download><?= htmlspecialchars($file) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const idUsuarioLocal = <?= htmlspecialchars($id_usuario) ?>;
</script>

<script src="/SIUGI/public/assets/js/menu_usuario.js"></script>

<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>