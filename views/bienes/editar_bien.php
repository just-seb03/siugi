<?php

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();

$id_editar = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt_disp = $pdo_inv->prepare("SELECT d.*, u.FISCALIA_UBICACION, u.EDIFICIO_UBICACION, u.DIVISION_UBICACION 
                                FROM dispositivos d 
                                LEFT JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION 
                                WHERE d.ID_DISP = :id");
$stmt_disp->execute([':id' => $id_editar]);
$disp = $stmt_disp->fetch();

if (!$disp) { die("Error: Dispositivo no encontrado."); }

$subcats = $pdo_inv->query("SELECT ID_SUBCAT, GLOSA_SUBCATEGORIA FROM sub_categorias")->fetchAll();
$estados = $pdo_inv->query("SELECT ID_ESTADO_CGU, GLOSA_ESTADO FROM estado_cgu")->fetchAll();
$marcas = $pdo_inv->query("SELECT ID_MARCA, GLOSA_MARCA FROM marcas")->fetchAll();
$proveedores = $pdo_inv->query("SELECT ID_PROV, GLOSA_PROVEEDOR FROM proveedores")->fetchAll();

$data_sedes = $pdo_inv->query("SELECT * FROM sedes ORDER BY GLOSA_FISCALIA")->fetchAll();
$data_edificios = $pdo_inv->query("SELECT * FROM edificios ORDER BY GLOSA_EDIFICIO")->fetchAll();
$data_divisiones = $pdo_inv->query("SELECT * FROM divisiones ORDER BY GLOSA_DIVISION")->fetchAll();
$data_ubicaciones = $pdo_inv->query("SELECT ID_UBICACION, DIVISION_UBICACION, GLOSA_UBICACION FROM ubicaciones ORDER BY GLOSA_UBICACION")->fetchAll();

$url_params = $_GET; unset($url_params['id']);
$url_retorno = "/SIUGI/consultar_bienes?" . http_build_query($url_params);

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/editar_bien.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.8rem; color: #1e293b;">Editar Bien</h1>
    </div>

    <div class="card-custom animate-up" style="animation-delay: 0.1s">
        <div class="card-header-custom d-flex justify-content-between align-items-center w-100">
            
            <span class="font-weight-bold text-uppercase">
                <i class="fas fa-edit mr-2"></i> ID de Registro 
                <span class="badge badge-light border ml-1 px-2 py-1">#<?php echo htmlspecialchars($id_editar); ?></span>
            </span>
            
            <div class="ml-auto d-flex align-items-center" style="gap: 10px;">
                <a href="/SIUGI/ficha_bien?id=<?php echo $id_editar; ?>" target="_blank" class="btn-ficha m-0">
                    <i class="fas fa-file-alt"></i> Ver Ficha
                </a>
                <a href="<?php echo htmlspecialchars($url_retorno); ?>" class="btn-cancel m-0">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
        
        <form id="formEditar" action="/SIUGI/src/editar_bien" method="POST" enctype="multipart/form-data">
            <div class="card-body" style="padding: 2rem;">
                <input type="hidden" name="id_disp" value="<?php echo htmlspecialchars($id_editar); ?>">
                <input type="hidden" name="url_retorno" value="<?php echo htmlspecialchars($url_retorno); ?>">
                
                <div class="form-layout">
                    <div class="panel-box panel-selects">
                        <div class="panel-title"><i class="fas fa-tags mr-2 text-muted"></i> 1. Clasificación y Estado</div>
                        
                        <div class="input-group-custom">
                            <label>Subcategoría *</label>
                            <select name="id_subcat" required>
                                <option value="">Seleccione...</option>
                                <?php foreach($subcats as $r): ?>
                                    <option value="<?php echo $r['ID_SUBCAT']; ?>" <?php if($r['ID_SUBCAT']==$disp['ID_SUBCAT']) echo 'selected'; ?>><?php echo htmlspecialchars($r['GLOSA_SUBCATEGORIA']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group-custom">
                            <label>Marca *</label>
                            <select name="id_marca" required>
                                <option value="">Seleccione...</option>
                                <?php foreach($marcas as $r): ?>
                                    <option value="<?php echo $r['ID_MARCA']; ?>" <?php if($r['ID_MARCA']==$disp['ID_MARCA']) echo 'selected'; ?>><?php echo htmlspecialchars($r['GLOSA_MARCA']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group-custom">
                            <label>Proveedor *</label>
                            <select name="id_proveedor" required>
                                <option value="">Seleccione...</option>
                                <?php foreach($proveedores as $r): ?>
                                    <option value="<?php echo $r['ID_PROV']; ?>" <?php if($r['ID_PROV']==$disp['ID_PROVEEDOR']) echo 'selected'; ?>><?php echo htmlspecialchars($r['GLOSA_PROVEEDOR']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group-custom">
                            <label>Estado CGU *</label>
                            <select name="id_estado_cgu" required>
                                <?php foreach($estados as $r): ?>
                                    <option value="<?php echo $r['ID_ESTADO_CGU']; ?>" <?php if($r['ID_ESTADO_CGU']==$disp['ID_ESTADO_CGU']) echo 'selected'; ?>><?php echo htmlspecialchars($r['GLOSA_ESTADO']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="divider-dashed"></div>
                        
                        <div class="panel-title mt-2"><i class="fas fa-map-marker-alt mr-2 text-muted"></i> 2. Ubicación Actual</div>
                        <div class="input-group-custom">
                            <label>Sede / Fiscalía</label>
                            <select id="sel_sede" onchange="updateEdificios()"><option value="">Seleccione...</option></select>
                        </div>
                        <div class="input-group-custom">
                            <label>Edificio</label>
                            <select id="sel_edificio" onchange="updateDivisiones()" disabled><option value="">-</option></select>
                        </div>
                        <div class="input-group-custom">
                            <label>División / Piso</label>
                            <select id="sel_division" onchange="updateUbicaciones()" disabled><option value="">-</option></select>
                        </div>
                        <div class="input-group-custom">
                            <label>Ubicación Exacta *</label>
                            <select name="id_ubicacion" id="sel_ubicacion" required disabled><option value="">-</option></select>
                        </div>
                    </div>

                    <div class="panel-box panel-inputs">
                        <div class="panel-title"><i class="fas fa-keyboard mr-2 text-muted"></i> 3. Datos a Modificar</div>
                        
                        <div class="middle-grid">
                            <div class="input-group-custom">
                                <label>Modelo del Equipo *</label>
                                <input type="text" name="modelo" required value="<?php echo htmlspecialchars($disp['MODELO']); ?>">
                            </div>
                            <div class="input-group-custom">
                                <label>Fecha de Registro</label>
                                <input type="text" value="<?php echo htmlspecialchars(date('d/m/Y', strtotime($disp['FECHA_REGISTRO']))); ?>" disabled>
                            </div>
                        </div>

                        <div class="middle-grid mt-2">
                            <div class="input-group-custom">
                                <label>Número de Serie / S/N</label>
                                <input type="text" name="serie" value="<?php echo htmlspecialchars($disp['SERIE']); ?>">
                            </div>
                            <div class="input-group-custom">
                                <label>Código Inventario</label>
                                <input type="number" name="codigo_inventario" value="<?php echo htmlspecialchars($disp['CODIGO_INVENTARIO']); ?>">
                            </div>
                            <div class="input-group-custom">
                                <label>Dirección IP</label>
                                <input type="text" name="ip" value="<?php echo htmlspecialchars($disp['IP']); ?>">
                            </div>
                            <div class="input-group-custom">
                                <label>Dirección MAC</label>
                                <input type="text" name="mac" value="<?php echo htmlspecialchars($disp['MAC']); ?>">
                            </div>
                            <div class="input-group-custom">
                                <label>Nombre en Red (Host)</label>
                                <input type="text" name="nombre_maquina" value="<?php echo htmlspecialchars($disp['NOMBRE_MAQUINA']); ?>">
                            </div>
                            <div class="input-group-custom">
                                <label>Clave de Acceso</label>
                                <input type="text" name="clave_acceso" value="<?php echo htmlspecialchars($disp['CLAVE_ACCESO']); ?>">
                            </div>
                        </div>

                        <div class="input-group-custom mt-3" style="flex-grow: 1;">
                            <label>Observación (Motivo de Edición)</label>
                            <textarea name="observacion" rows="4" style="height: 50%; resize: vertical;"><?php echo htmlspecialchars($disp['OBSERVACION']); ?></textarea>
                        </div>

                        <div class="input-group-custom mt-3" style="flex-grow: 1;">
                            <label>Imagen</label>
                            <div class="device-image-wrapper" id="wrapper_imagen" style="cursor: pointer;" title="Clic para cambiar imagen">
                                <input type="file" name="imagen_dispositivo" id="imagen_dispositivo" accept=".jpg, .jpeg, .png, .webp" style="display: none;">
                                <?php 
                                     $nombre_archivo = $disp['IMAGEN'] ?: '';
                                     $ruta_relativa = "/SIUGI/public/img_dispositivos/" . $nombre_archivo;
                                    $ruta_absoluta = $_SERVER['DOCUMENT_ROOT'] . $ruta_relativa;
                                    
                                    if (!empty($nombre_archivo) && file_exists($ruta_absoluta)): 
                                ?>
                                    <img id="preview_imagen" src="<?php echo htmlspecialchars($ruta_relativa); ?>?v=<?php echo time(); ?>" alt="Dispositivo" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="no-image-placeholder" id="placeholder_imagen">
                                        <i class="fas fa-camera-retro"></i>
                                        <span>Sin Imagen Asociada<br>(Clic para subir)</span>
                                    </div>
                                    <img id="preview_imagen" src="" alt="Dispositivo" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <button type="submit" id="btnGuardar" class="btn-submit-custom">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const sedes = <?php echo json_encode($data_sedes); ?>;
    const edificios = <?php echo json_encode($data_edificios); ?>;
    const divisiones = <?php echo json_encode($data_divisiones); ?>;
    const ubicaciones = <?php echo json_encode($data_ubicaciones); ?>;

    const currentSede = "<?php echo htmlspecialchars($disp['FISCALIA_UBICACION']); ?>"; 
    const currentEdificio = "<?php echo htmlspecialchars($disp['EDIFICIO_UBICACION']); ?>"; 
    const currentDivision = "<?php echo htmlspecialchars($disp['DIVISION_UBICACION']); ?>"; 
    const currentUbicacion = "<?php echo htmlspecialchars($disp['ID_UBICACION']); ?>"; 
</script>
<script src="/SIUGI/public/assets/js/editar_bien.js"></script>

<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>