<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo_cuentas    = $db->getCuentasConnection();
$pdo_info       = $db->getInfoConnection();
$pdo_inventario = $db->getInvConnection();

$data_sedes = $pdo_cuentas->query("SELECT * FROM sedes ORDER BY GLOSA_FISCALIA ASC")->fetchAll();
$data_software = $pdo_cuentas->query("SELECT * FROM software ORDER BY GLOSA_SOFTWARE ASC")->fetchAll();
$data_usuarios = $pdo_info->query("SELECT * FROM usuarios ORDER BY nombre ASC")->fetchAll();
$data_unidades = $pdo_info->query("SELECT * FROM unidad ORDER BY gls_unidad ASC")->fetchAll();

$data_inv_sedes = $pdo_inventario->query("SELECT * FROM sedes ORDER BY GLOSA_FISCALIA ASC")->fetchAll();
$data_inv_edificios = $pdo_inventario->query("SELECT * FROM edificios ORDER BY GLOSA_EDIFICIO ASC")->fetchAll();
$data_inv_divisiones = $pdo_inventario->query("SELECT * FROM divisiones ORDER BY GLOSA_DIVISION ASC")->fetchAll();

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/parametros_usuarios.css">';
?>

<div class="content-wrapper-inner">
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.8rem; color: var(--text-main);">Parámetros de Usuarios</h1>
        <p class="text-muted">Gestión de Usuarios, Software y Sedes.</p>
    </div>

    <?php if(isset($_GET['msg'])): 
        $status = $_GET['status'] ?? 'success';
    ?>
        <div class="alert-custom <?= ($status == 'error') ? 'alert-error' : 'alert-success'; ?> animate-up">
            <i class="fas <?= ($status == 'error') ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
            <?= htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="card-custom animate-up" style="animation-delay: 0.1s">
        <div class="card-header-custom">
            <span><i class="fas fa-users-cog mr-2"></i> Configuración de Acceso</span>
        </div>
        
        <form action="/SIUGI/src/parametros_usuarios" method="POST" enctype="multipart/form-data">
            <div class="card-body" style="padding: 2rem;">
                
                <div class="section-box solid">
                    <div class="section-title"><i class="fas fa-hand-pointer mr-2"></i> 1. Operación a Realizar</div>
                    <div class="grid-2">
                        <div class="input-group-custom">
                            <label>Acción</label>
                            <select id="selector_accion" name="accion" onchange="resetForms()" required>
                                <option value="agregar">Agregar Nuevo Elemento</option>
                                <option value="modificar">Modificar Existente</option>
                                <option value="anular">Anular (Eliminar) Existente</option>
                            </select>
                        </div>
                        <div class="input-group-custom">
                            <label>Entidad</label>
                            <select id="selector_entidad" name="tipo_entidad" onchange="mostrarFormulario()" required>
                                <option value="">Seleccione...</option>
                                <option value="usuario">Usuario de Sistema</option>
                                <option value="software">Licencia / Software</option>
                                <option value="sede">Sede (Fiscalía)</option>
                            </select>
                        </div>
                    </div>

                    <div id="grupo_buscador_avanzado" class="search-box-avanzada anim-container anim-hide">
                        <div class="search-title"><i class="fas fa-search mr-2"></i> Localizar el Elemento</div>
                        
                        <div id="filtros_dinamicos" class="grid-2 mb-3"></div>
                        
                        <div class="input-group-custom" style="border-top: 1px dashed #fde68a; padding-top: 1rem;">
                            <label style="color: #d97706;"><i class="fas fa-check-circle mr-1"></i> Seleccione el Elemento Final *</label>
                            <select id="buscador_elemento" name="id_elemento" onchange="cargarDatosElemento()" style="border-color: #fde68a; background-color: #fff; font-weight: bold;">
                                <option value="">Seleccione elemento...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="container_formularios" class="anim-container anim-hide">
                    <div class="section-box" style="background: #fdfbf6;">
                        <div class="section-title"><i class="fas fa-edit mr-2"></i> 2. Detalles de la Entidad</div>
                        <div id="campos_formulario">
                            
                            <div id="form_sede" class="entity-form anim-container anim-hide">
                                <div class="input-group-custom">
                                    <label>Nombre de la Sede / Fiscalía *</label>
                                    <input type="text" name="glosa_sede" id="input_glosa_sede" placeholder="Ej: Fiscalía Centro Norte">
                                </div>
                            </div>

                            <div id="form_software" class="entity-form anim-container anim-hide grid-2">
                                <div class="input-group-custom">
                                    <label>Nombre del Software / Licencia *</label>
                                    <input type="text" name="glosa_software" id="input_glosa_software">
                                </div>
                                <div class="input-group-custom">
                                    <label>Estado Inicial *</label>
                                    <select name="estado_software" id="input_estado_software">
                                        <option value="0">Activo</option>
                                        <option value="1">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div id="form_usuario" class="entity-form anim-container anim-hide">
                                
                                <div class="hierarchy-box">
                                    <div class="sub-title"><i class="fas fa-camera mr-1"></i> Fotografía de Perfil (Avatar)</div>
                                    <div style="display: flex; gap: 20px; align-items: center;">
                                        <div style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid var(--border); background: #eee; flex-shrink: 0;">
                                            <img id="avatar_preview" src="https://ui-avatars.com/api/?name=U&background=cbd5e1&color=fff&size=120" style="width: 100%; height: 100%; object-fit: cover;" 
                                            onerror="this.src='https://ui-avatars.com/api/?name=U&background=cbd5e1&color=fff&size=120'">
                                        </div>
                                        <div style="flex-grow: 1;">
                                            <div class="input-group-custom">
                                                <label>Subir o Actualizar Foto (Solo JPG/JPEG)</label>
                                                <input type="file" name="foto_usuario" id="foto_usuario" accept=".jpg, .jpeg" onchange="previewImagenSeleccionada(event)" style="padding: 6px;">
                                            </div>
                                            <div class="input-group-custom mt-2" id="box_eliminar_foto" style="display: none; flex-direction: row; align-items: center; gap: 8px;">
                                                <input type="checkbox" name="eliminar_foto" id="eliminar_foto" style="width: 18px; height: 18px; cursor: pointer;">
                                                <label for="eliminar_foto" style="margin-bottom: 0; color: var(--danger); cursor: pointer;">Eliminar foto actual al guardar cambios</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="hierarchy-box">
                                    <div class="sub-title"><i class="fas fa-id-card mr-1"></i> Identificación Personal</div>
                                    <div class="grid-4 mb-2">
                                        <div class="input-group-custom">
                                            <label>ID Usuario (Manual)</label>
                                            <input type="number" name="usr_id" id="usr_id" placeholder="ID del Usuario">
                                        </div>
                                        <div class="input-group-custom"><label>Nombre Completo *</label><input type="text" name="usr_nombre" id="usr_nombre"></div>
                                        <div class="input-group-custom"><label>RUT *</label><input type="text" name="usr_rut" id="usr_rut"></div>
                                        <div class="input-group-custom"><label>Usuario (Login) *</label><input type="text" name="usr_usuario" id="usr_usuario" onkeyup="actualizarRutaPreviewSiAgregando(this.value)"></div>
                                    </div>
                                </div>

                                <div class="hierarchy-box">
                                    <div class="sub-title"><i class="fas fa-address-book mr-1"></i> Contacto y Perfil</div>
                                    <div class="grid-3 mb-2">
                                        <div class="input-group-custom"><label>Correo Electrónico</label><input type="email" name="usr_correo" id="usr_correo"></div>
                                        <div class="input-group-custom"><label>Teléfono</label><input type="text" name="usr_telefono" id="usr_telefono"></div>
                                        <div class="input-group-custom"><label>Fecha Nacimiento</label><input type="date" name="usr_fec_nac" id="usr_fec_nac"></div>
                                    </div>
                                </div>

                                <div class="hierarchy-box">
                                    <div class="sub-title"><i class="fas fa-building mr-1"></i> Datos Institucionales</div>
                                    <div class="grid-3 mb-2">
                                        <div class="input-group-custom">
                                            <label>Sede (Cód Fiscalía) *</label>
                                            <select name="usr_sede" id="usr_sede">
                                                <option value="0">Seleccione Sede...</option>
                                                <?php foreach ($data_sedes as $s): ?>
                                                    <option value="<?= $s['ID_SEDE'] ?>"><?= htmlspecialchars($s['GLOSA_FISCALIA']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="input-group-custom"><label>Cargo</label><input type="text" name="usr_cargo" id="usr_cargo"></div>
                                        <div class="input-group-custom">
                                            <label>Unidad</label>
                                            <select name="usr_cod_unidad" id="usr_cod_unidad">
                                                <option value="0">Seleccione Unidad...</option>
                                                <?php foreach ($data_unidades as $u): ?>
                                                    <option value="<?= $u['cod_unidad'] ?>"><?= htmlspecialchars($u['gls_unidad']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid-2 mt-3">
                                        <div class="input-group-custom"><label>Inicio Funciones</label><input type="date" name="usr_fec_ini" id="usr_fec_ini"></div>
                                        <div class="input-group-custom"><label>Término Funciones</label><input type="date" name="usr_fec_fin" id="usr_fec_fin"></div>
                                    </div>
                                </div>

                                <div class="hierarchy-box">
                                    <div class="sub-title"><i class="fas fa-desktop mr-1"></i> Configuración de Sistema</div>
                                    <div class="grid-4">
                                        <div class="input-group-custom">
                                            <label>Tipo</label>
                                            <select name="usr_tipo" id="usr_tipo">
                                                <option value="">Seleccione...</option>
                                                <option value="1">Fiscal</option>
                                                <option value="2">Funcionario</option>
                                                <option value="3">Alumno</option>
                                            </select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>Estado Cuenta</label>
                                            <select name="usr_estado" id="usr_estado"><option value="0">Activo</option><option value="1">Inactivo</option></select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>Mostrar Intranet</label>
                                            <select name="usr_mostrar_intranet" id="usr_mostrar_intranet">
                                                <option value="">Seleccione...</option>
                                                <option value="0">Sí</option>
                                                <option value="1">No</option>
                                            </select>
                                        </div>
                                        <div class="input-group-custom"><label>IP Fija</label><input type="text" name="usr_ip" id="usr_ip"></div>
                                        <div class="input-group-custom"><label>Fiscal Func</label><input type="number" name="usr_fiscal_func" id="usr_fiscal_func"></div>
                                    </div>
                                </div>

                                <div id="panel_ubicacion_inventario" class="hierarchy-box mt-3" style="display:none; border-color: var(--success); background-color: #f0fdf4;">
                                    <div class="sub-title" style="color: var(--success); border-bottom-color: #bbf7d0;"><i class="fas fa-map-marker-alt mr-1"></i> Ubicación en Inventario (Opcional - Solo al Crear)</div>
                                    <div class="grid-3 mb-2">
                                        <div class="input-group-custom">
                                            <label>1. Sede (Inventario)</label>
                                            <select name="inv_sede" id="inv_sede" onchange="updateInvEdificios()">
                                                <option value="">Seleccione Sede...</option>
                                                <?php foreach ($data_inv_sedes as $s): ?>
                                                    <option value="<?= $s['ID_SEDE'] ?>"><?= htmlspecialchars($s['GLOSA_FISCALIA']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>2. Edificio</label>
                                            <select name="inv_edificio" id="inv_edificio" onchange="updateInvDivisiones()" disabled>
                                                <option value="">Seleccione Sede primero</option>
                                            </select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>3. División / Piso</label>
                                            <select name="inv_division" id="inv_division" disabled>
                                                <option value="">Seleccione Edificio primero</option>
                                            </select>
                                        </div>
                                    </div>
                                    <p class="text-muted" style="font-size: 0.75rem; margin-top: 0.5rem; margin-bottom: 0;">* Si completas estos datos, se creará automáticamente una ubicación en el Inventario para este usuario.</p>
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="btn_submit" class="btn-submit-custom btn-agregar mt-4">
                            <i class="fas fa-plus"></i> Guardar Registro
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const DB = {
        sede: <?= json_encode($data_sedes) ?>,
        software: <?= json_encode($data_software) ?>,
        usuario: <?= json_encode($data_usuarios) ?>
    };

    const DB_INV = {
        edificio: <?= json_encode($data_inv_edificios) ?>,
        division: <?= json_encode($data_inv_divisiones) ?>
    };
</script>
<script src="/SIUGI/public/assets/js/parametros_usuarios.js"></script>

<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>