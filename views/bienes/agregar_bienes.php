<?php
// views/bienes/agregar_bienes.php

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$usr_id_auto = isset($_GET['usr_id']) ? (int)$_GET['usr_id'] : ($_SESSION['userId'] ?? 0);

$auto_sede = ""; $auto_edificio = ""; $auto_division = ""; $auto_ubicacion = "";

if ($usr_id_auto > 0) {
    $stmt_auto = $pdo_inv->prepare("SELECT FISCALIA_UBICACION, EDIFICIO_UBICACION, DIVISION_UBICACION, ID_UBICACION FROM ubicaciones WHERE ID_USUARIO_ASIGNADO = :id LIMIT 1");
    $stmt_auto->execute([':id' => $usr_id_auto]);
    if ($row_auto = $stmt_auto->fetch()) {
        $auto_sede = $row_auto['FISCALIA_UBICACION'];
        $auto_edificio = $row_auto['EDIFICIO_UBICACION'];
        $auto_division = $row_auto['DIVISION_UBICACION'];
        $auto_ubicacion = $row_auto['ID_UBICACION'];
    }
}

$subcats = $pdo_inv->query("SELECT ID_SUBCAT, GLOSA_SUBCATEGORIA FROM sub_categorias")->fetchAll();
$marcas = $pdo_inv->query("SELECT ID_MARCA, GLOSA_MARCA FROM marcas")->fetchAll();
$proveedores = $pdo_inv->query("SELECT ID_PROV, GLOSA_PROVEEDOR FROM proveedores")->fetchAll();

$data_sedes = $pdo_inv->query("SELECT * FROM sedes ORDER BY GLOSA_FISCALIA")->fetchAll();
$data_edificios = $pdo_inv->query("SELECT * FROM edificios ORDER BY GLOSA_EDIFICIO")->fetchAll();
$data_divisiones = $pdo_inv->query("SELECT * FROM divisiones ORDER BY GLOSA_DIVISION")->fetchAll();
$data_ubicaciones = $pdo_inv->query("SELECT GLOSA_UBICACION, ID_UBICACION, DIVISION_UBICACION FROM ubicaciones ORDER BY GLOSA_UBICACION")->fetchAll();

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/agregar_bienes.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.8rem; color: var(--text-main);">Añadir Bienes</h1>
        <p class="text-muted">Ingreso de dispositivos al sistema</p>
    </div>

    <div class="card-custom animate-up" style="animation-delay: 0.1s">
        <div class="card-header-custom">
            <span style="font-weight: 700; text-transform: uppercase;"><i class="fas fa-plus-circle mr-2"></i> Formulario de Dispositivo</span>
            <div class="switch-container">
                <span class="text-xs font-weight-bold" style="font-size: 0.7rem;"><i class="fas fa-layer-group mr-1"></i> MODO MASIVO</span>
                <label class="switch"><input type="checkbox" id="tipoRegistro" onchange="toggleMasivo()"><span class="slider"></span></label>
            </div>
        </div>
        
        <form action="/SIUGI/src/agregar_bienes" method="POST">
            <div class="card-body" style="padding: 2rem;">
                <input type="hidden" name="es_masivo" id="es_masivo_hidden" value="0">
                <input type="hidden" name="id_usuario_registro" value="<?php echo htmlspecialchars($usr_id_auto); ?>"> 

                <div class="section-box grid-3">
                    <div class="full-width section-title"><i class="fas fa-tags mr-2"></i> 1. Clasificación del Equipo</div>
                    <div class="input-group-custom">
                        <label>Subcategoría *</label>
                        <select name="id_subcat" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($subcats as $r): ?><option value="<?php echo $r['ID_SUBCAT']; ?>"><?php echo $r['GLOSA_SUBCATEGORIA']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group-custom">
                        <label>Marca *</label>
                        <select name="id_marca" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($marcas as $r): ?><option value="<?php echo $r['ID_MARCA']; ?>"><?php echo $r['GLOSA_MARCA']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group-custom">
                        <label>Proveedor *</label>
                        <select name="id_proveedor" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($proveedores as $r): ?><option value="<?php echo $r['ID_PROV']; ?>"><?php echo $r['GLOSA_PROVEEDOR']; ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="section-box solid">
                    <div class="section-title"><i class="fas fa-keyboard mr-2"></i> 2. Detalles Técnicos</div>
                    <div class="input-group-custom mb-3">
                        <label>Modelo del Equipo *</label>
                        <input type="text" name="modelo" required placeholder="Ej: ThinkPad T14, OptiPlex 3080...">
                    </div>
                    <div id="cantidad_container" class="anim-container anim-hide">
                        <div class="input-group-custom">
                            <label style="color: var(--warning);"><i class="fas fa-boxes mr-1"></i> Cantidad a ingresar</label>
                            <input type="number" name="cantidad" value="1" min="1" style="border-color: #fde68a; background-color: #fffbeb;">
                        </div>
                    </div>
                    <div id="detalles_container" class="anim-container anim-show grid-2">
                        <div class="input-group-custom"><label>Número de Serie</label><input type="text" name="serie" placeholder="Ingrese serie..."></div>
                        <div class="input-group-custom"><label>Código Inventario</label><input type="number" name="codigo_inventario" placeholder="Solo números"></div>
                        <div class="input-group-custom"><label>Dirección IP</label><input type="text" name="ip" placeholder="Ej: 192.168.1.10"></div>
                        <div class="input-group-custom"><label>Dirección MAC</label><input type="text" name="mac" placeholder="Ej: 00:1B:..."></div>
                        <div class="input-group-custom"><label>Nombre en Red</label><input type="text" name="nombre_maquina" placeholder="Ej: PC-CONTAB"></div>
                        <div class="input-group-custom"><label>Clave</label><input type="text" name="clave_acceso" placeholder="Opcional"></div>
                    </div>
                </div>

                <div id="ubicaciones_container" class="anim-container anim-show section-box grid-4">
                    <div class="full-width section-title"><i class="fas fa-map-marker-alt mr-2"></i> 3. Ubicación Inicial</div>
                    <div class="input-group-custom"><label>1. Sede</label><select id="sel_sede" onchange="updateEdificios()"><option value="">Seleccione...</option></select></div>
                    <div class="input-group-custom"><label>2. Edificio</label><select id="sel_edificio" onchange="updateDivisiones()" disabled><option value="">-</option></select></div>
                    <div class="input-group-custom"><label>3. División</label><select id="sel_division" onchange="updateUbicaciones()" disabled><option value="">-</option></select></div>
                    <div class="input-group-custom"><label>4. Ubicación Exacta *</label><select name="id_ubicacion" id="sel_ubicacion" class="req-ubicacion" required disabled><option value="">-</option></select></div>
                </div>

                <div id="observacion_container" class="anim-container anim-show">
                    <div class="input-group-custom mb-3"><label>Observación Inicial</label><textarea name="observacion" rows="3"></textarea></div>
                </div>

                <button type="submit" class="btn-submit-custom"><i class="fas fa-save"></i> Registrar Dispositivo</button>
            </div>
        </form>
    </div>
</div>

<script>
    const sedesData = <?= json_encode($data_sedes); ?>;
    const edificiosData = <?= json_encode($data_edificios); ?>;
    const divisionesData = <?= json_encode($data_divisiones); ?>;
    const ubicacionesData = <?= json_encode($data_ubicaciones); ?>;
    const autoData = {
        sede: "<?= htmlspecialchars($auto_sede) ?>",
        edificio: "<?= htmlspecialchars($auto_edificio) ?>",
        division: "<?= htmlspecialchars($auto_division) ?>",
        ubicacion: "<?= htmlspecialchars($auto_ubicacion) ?>"
    };
</script>
<script src="/SIUGI/public/assets/js/agregar_bienes.js"></script>
<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>