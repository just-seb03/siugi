<?php 
// views/mantenedores/parametros_bienes.php

require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo_inv = $db->getInvConnection();
$pdo_info = $db->getInfoConnection();

// Extracción de datos con PDO
$data_sedes = $pdo_inv->query("SELECT * FROM sedes ORDER BY GLOSA_FISCALIA")->fetchAll();
$data_edificios = $pdo_inv->query("SELECT * FROM edificios ORDER BY GLOSA_EDIFICIO")->fetchAll();
$data_divisiones = $pdo_inv->query("SELECT * FROM divisiones ORDER BY GLOSA_DIVISION")->fetchAll();
$data_categorias = $pdo_inv->query("SELECT * FROM categorias ORDER BY GLOSA_CATEGORIA")->fetchAll();
$data_marcas = $pdo_inv->query("SELECT * FROM marcas ORDER BY GLOSA_MARCA")->fetchAll();
$data_proveedores = $pdo_inv->query("SELECT * FROM proveedores ORDER BY GLOSA_PROVEEDOR")->fetchAll();
$data_subcats = $pdo_inv->query("SELECT * FROM sub_categorias ORDER BY GLOSA_SUBCATEGORIA")->fetchAll();
$data_ubicaciones = $pdo_inv->query("SELECT * FROM ubicaciones ORDER BY GLOSA_UBICACION")->fetchAll();

$usuarios_res = $pdo_info->query("SELECT id, nombre, rut FROM usuarios ORDER BY nombre ASC")->fetchAll();

include __DIR__ . '/../../templates/layout_top.php';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/parametros_bienes.css">';
?>

<div class="content-wrapper-inner">
    <div class="mb-4 animate-up">
        <h1 class="font-weight-bold m-0" style="font-size: 1.8rem; color: var(--text-main);">Parámetros de Inventario</h1>
        <p class="text-muted">Gestión estructurada de jerarquías, categorías y atributos.</p>
    </div>

    <?php if(isset($_GET['msg'])): 
        // CORRECCIÓN: Usamos 'status' para detectar el error/éxito
        $status = $_GET['status'] ?? 'success';
    ?>
        <div class="alert-custom <?= ($status =='error')?'alert-error':'alert-success'; ?> animate-up">
            <i class="fas <?= ($status =='error')?'fa-exclamation-circle':'fa-check-circle'; ?>"></i>
            <?= htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="card-custom animate-up" style="animation-delay: 0.1s">
        <div class="card-header-custom">
            <span><i class="fas fa-cogs mr-2"></i> Configuración de Inventario</span>
        </div>
        
        <form action="/SIUGI/src/parametros_bienes" method="POST">
            <div class="card-body" style="padding: 2rem;">
                
                <div class="section-box solid">
                    <div class="section-title"><i class="fas fa-hand-pointer mr-2"></i> 1. Iniciar Operación</div>
                    <div class="grid-2">
                        <div class="input-group-custom">
                            <label>Acción a Realizar</label>
                            <select id="selector_accion" name="accion" onchange="resetForms()" required>
                                <option value="agregar">Agregar Nuevo Elemento</option>
                                <option value="modificar">Modificar Existente</option>
                                <option value="anular">Anular (Eliminar) Existente</option>
                            </select>
                        </div>
                        <div class="input-group-custom">
                            <label>Tipo de Parámetro</label>
                            <select id="selector_entidad" name="tipo_entidad" onchange="mostrarFormulario()" required>
                                <option value="">Seleccione...</option>
                                <optgroup label="Jerarquía Física">
                                    <option value="sede">Sede (Fiscalía)</option>
                                    <option value="edificio">Edificio</option>
                                    <option value="division">División / Piso</option>
                                    <option value="ubicacion">Ubicación Exacta</option>
                                </optgroup>
                                <optgroup label="Jerarquía de Equipos">
                                    <option value="categoria">Categoría</option>
                                    <option value="subcategoria">Subcategoría</option>
                                </optgroup>
                                <optgroup label="Atributos">
                                    <option value="marca">Marca</option>
                                    <option value="proveedor">Proveedor</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div id="grupo_buscador_avanzado" class="search-box-avanzada anim-container anim-hide">
                        <div class="search-title"><i class="fas fa-search mr-2"></i> Localizar el Elemento</div>
                        
                        <div id="filtros_dinamicos" class="grid-3 mb-3"></div>
                        
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
                        <div class="section-title"><i class="fas fa-edit mr-2"></i> 2. Detalles del Parámetro</div>
                        
                        <div>
                            <div id="form_sede" class="entity-form anim-container anim-hide">
                                <div class="input-group-custom">
                                    <label>Nombre de la Sede / Fiscalía *</label>
                                    <input type="text" name="glosa_sede" id="input_glosa_sede" placeholder="Ej: Fiscalía Nacional">
                                </div>
                            </div>

                            <div id="form_edificio" class="entity-form anim-container anim-hide">
                                <div class="hierarchy-box">
                                    <div class="hierarchy-title"><i class="fas fa-link mr-2"></i> Dependencia Obligatoria</div>
                                    <div class="input-group-custom">
                                        <label>Pertenece a la Sede *</label>
                                        <select name="id_sede_edificio" id="edi_sel_sede"></select>
                                    </div>
                                </div>
                                <div class="input-group-custom">
                                    <label>Nombre del Edificio *</label>
                                    <input type="text" name="glosa_edificio" id="input_glosa_edificio" placeholder="Ej: Edificio Principal">
                                </div>
                            </div>

                            <div id="form_division" class="entity-form anim-container anim-hide">
                                <div class="hierarchy-box">
                                    <div class="hierarchy-title"><i class="fas fa-link mr-2"></i> Ruta de Dependencia</div>
                                    <div class="grid-2">
                                        <div class="input-group-custom">
                                            <label>Paso 1: Seleccionar Sede</label>
                                            <select id="div_sel_sede" onchange="updateEdificiosForm('div')"></select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>Paso 2: Pertenece al Edificio *</label>
                                            <select name="id_edificio_division" id="div_sel_edificio" disabled><option value="">Seleccione Sede primero</option></select>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group-custom">
                                    <label>Nombre de la División / Piso *</label>
                                    <input type="text" name="glosa_division" id="input_glosa_division" placeholder="Ej: Piso 3 - Recursos Humanos">
                                </div>
                            </div>

                            <div id="form_ubicacion" class="entity-form anim-container anim-hide">
                                <div class="hierarchy-box">
                                    <div class="hierarchy-title"><i class="fas fa-link mr-2"></i> Ruta de Dependencia</div>
                                    <div class="grid-3">
                                        <div class="input-group-custom">
                                            <label>Paso 1: Sede</label>
                                            <select name="ubi_id_sede" id="ubi_sel_sede" onchange="updateEdificiosForm('ubi')"></select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>Paso 2: Edificio</label>
                                            <select name="ubi_id_edificio" id="ubi_sel_edificio" onchange="updateDivisionesForm('ubi')" disabled><option value="">Seleccione Sede primero</option></select>
                                        </div>
                                        <div class="input-group-custom">
                                            <label>Paso 3: Pertenece a División *</label>
                                            <select name="ubi_id_division" id="ubi_sel_division" disabled><option value="">Seleccione Edificio primero</option></select>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid-2">
                                    <div class="input-group-custom">
                                        <label>Nombre de la Ubicación Exacta *</label>
                                        <input type="text" name="glosa_ubicacion" id="input_glosa_ubicacion" placeholder="Ej: Oficina 304">
                                    </div>
                                    <div class="input-group-custom">
                                        <label>Tipo (Oficina, Pasillo, Bodega, etc.)</label>
                                        <input type="text" name="tipo_ubicacion" id="input_tipo_ubicacion" placeholder="Ej: Oficina">
                                    </div>
                                    
                                    <div class="input-group-custom full-width">
                                        <label>Usuario Asignado a la Ubicación</label>
                                        <select name="id_usuario_asignado" id="ubi_sel_usuario">
                                            <option value="">Sin Asignar</option>
                                            <?php foreach($usuarios_res as $u): ?>
                                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre'] . ' (' . $u['rut'] . ')') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="form_subcategoria" class="entity-form anim-container anim-hide">
                                <div class="hierarchy-box">
                                    <div class="hierarchy-title"><i class="fas fa-tags mr-2"></i> Categoría Principal</div>
                                    <div class="input-group-custom">
                                        <label>Pertenece a la Categoría *</label>
                                        <select name="id_cat_padre" id="sub_sel_cat"></select>
                                    </div>
                                </div>
                                <div class="input-group-custom">
                                    <label>Nombre de la Subcategoría *</label>
                                    <input type="text" name="glosa_subcategoria" id="input_glosa_subcat" placeholder="Ej: Notebook">
                                </div>
                            </div>

                            <div id="form_marca" class="entity-form anim-container anim-hide">
                                <div class="input-group-custom"><label>Nombre de la Marca *</label><input type="text" name="glosa_marca" id="input_glosa_marca" placeholder="Ej: Lenovo"></div>
                            </div>
                            <div id="form_proveedor" class="entity-form anim-container anim-hide">
                                <div class="input-group-custom"><label>Nombre del Proveedor *</label><input type="text" name="glosa_proveedor" id="input_glosa_proveedor" placeholder="Ej: PC Factory"></div>
                            </div>
                            <div id="form_categoria" class="entity-form anim-container anim-hide">
                                <div class="input-group-custom"><label>Nombre de la Categoría General *</label><input type="text" name="glosa_categoria" id="input_glosa_categoria" placeholder="Ej: Computación"></div>
                            </div>

                        </div>

                        <div id="alerta_dependencias" class="alert-custom alert-error anim-container anim-hide" style="margin-top: 1.5rem; margin-bottom: 0;">
                            <i class="fas fa-ban" style="font-size: 1.2rem;"></i>
                            <span><strong>No es posible eliminar:</strong> Este registro tiene dependencias asociadas (edificios, divisiones o subcategorías). Debe eliminar las dependencias primero.</span>
                        </div>

                        <div id="alerta_bienes" class="alert-custom anim-container anim-hide" style="margin-top: 1.5rem; margin-bottom: 0; background-color: var(--warning-bg); color: #b45309; border-color: #fde68a;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                            <span><strong>Aviso:</strong> Si este parámetro tiene bienes asociados en el inventario, el sistema no permitirá su eliminación.</span>
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
        sede: <?= json_encode($data_sedes); ?>,
        edificio: <?= json_encode($data_edificios); ?>,
        division: <?= json_encode($data_divisiones); ?>,
        categoria: <?= json_encode($data_categorias); ?>,
        subcategoria: <?= json_encode($data_subcats); ?>,
        marca: <?= json_encode($data_marcas); ?>,
        proveedor: <?= json_encode($data_proveedores); ?>,
        ubicacion: <?= json_encode($data_ubicaciones); ?>
    };
</script>
<script src="/SIUGI/public/assets/js/parametros_bienes.js"></script>

<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>