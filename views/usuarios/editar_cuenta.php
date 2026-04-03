<?php


require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo = $db->getCuentasConnection();
$pdo_info = $db->getInfoConnection();

$id_editar = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['edit']) ? (int)$_GET['edit'] : 0);

if ($id_editar === 0) {
    die("No se especificó ninguna cuenta para editar.");
}

$stmt_cta = $pdo->prepare("SELECT * FROM cuentas WHERE ID_CUENTA = ?");
$stmt_cta->execute([$id_editar]);
$cuenta = $stmt_cta->fetch();

if (!$cuenta) {
    die("La cuenta solicitada no existe.");
}


$softwares = $pdo->query("SELECT ID_SOFTWARE, GLOSA_SOFTWARE FROM software ORDER BY GLOSA_SOFTWARE ASC")->fetchAll();
$sedes     = $pdo->query("SELECT ID_SEDE, GLOSA_FISCALIA FROM sedes ORDER BY GLOSA_FISCALIA ASC")->fetchAll();
$usuarios  = $pdo_info->query("SELECT id, Nombre FROM usuarios ORDER BY Nombre ASC")->fetchAll();

include __DIR__ . '/../../templates/layout_top.php';

echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
echo '<link rel="stylesheet" href="/SIUGI/public/assets/css/editar_cuenta.css">';
?>

<div class="content-wrapper-inner" style="padding: 1.5rem; background: transparent !important;">
    <div class="content-header mb-4 animate-up">
        <div class="container-fluid d-flex justify-content-between align-items-end flex-wrap" style="gap: 1rem;">
            <div>
                <h1 class="m-0 font-weight-bold" style="color: var(--text-main); font-size: 1.75rem; letter-spacing: -0.025em;">Editar Cuenta</h1>
            </div>
            <a href="javascript:history.back()" class="btn-custom btn-secondary-custom interactive-btn">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <section class="content mt-3">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <div class="card card-custom card-custom-1">
                        <div class="card-header-custom">
                            <h5 class="card-title-text">
                                <i class="fas fa-edit mr-2" style="color: var(--text-muted);"></i> 
                                ID Cuenta #<?= htmlspecialchars($cuenta['ID_CUENTA']) ?> | <?= htmlspecialchars($cuenta['GLOSA_CUENTA'] ?? 'Sin Glosa') ?>
                            </h5>
                        </div>
                        
                        <div class="card-body" style="padding: 2.5rem;">
                            <form method="POST" action="/SIUGI/src/editar_cuenta">
                                <input type="hidden" name="id_cuenta" value="<?= $cuenta['ID_CUENTA'] ?>">

                                <div class="row">
                                    
                                    <div class="col-md-6 pr-lg-4 mb-4 mb-md-0">
                                        <div class="form-column-title">
                                            <i class="fas fa-list-ul"></i> Parámetros de Selección
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="software">Software / Sistema <span class="text-danger">*</span></label>
                                            <select name="software" id="software" class="form-control" required>
                                                <option value="">Seleccione un software...</option>
                                                <?php foreach ($softwares as $sw): ?>
                                                    <option value="<?= $sw['ID_SOFTWARE'] ?>" <?= $cuenta['ID_SOFTWARE'] == $sw['ID_SOFTWARE'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($sw['GLOSA_SOFTWARE']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group mb-4">
                                            <label for="sede">Sede designada <span class="text-danger">*</span></label>
                                            <select name="sede" id="sede" class="form-control" required>
                                                <option value="">Seleccione una sede...</option>
                                                <?php foreach ($sedes as $sd): ?>
                                                    <option value="<?= $sd['ID_SEDE'] ?>" <?= $cuenta['ID_SEDE'] == $sd['ID_SEDE'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($sd['GLOSA_FISCALIA']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="usuario">Usuario Asignado <span class="text-danger">*</span></label>
                                            <select name="usuario" id="usuario" class="form-control select2" required>
                                                <option value="">Buscar usuario...</option>
                                                <?php foreach ($usuarios as $u): ?>
                                                    <option value="<?= $u['id'] ?>" <?= $cuenta['USUARIO'] == $u['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($u['Nombre']) ?> (ID: <?= $u['id'] ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="es_generica">Tipo de Cuenta</label>
                                            <select name="es_generica" id="es_generica" class="form-control">
                                                <option value="0" <?= $cuenta['ES_GENERICA'] == 0 ? 'selected' : '' ?>>Personal</option>
                                                <option value="1" <?= $cuenta['ES_GENERICA'] == 1 ? 'selected' : '' ?>>Genérica</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="estado">Estado de la Cuenta</label>
                                            <select name="estado" id="estado" class="form-control">
                                                <option value="1" <?= $cuenta['ESTADO_CUENTA'] == 1 ? 'selected' : '' ?>>Alta</option>
                                                <option value="3" <?= $cuenta['ESTADO_CUENTA'] == 3 ? 'selected' : '' ?>>Pendiente</option>
                                                <option value="0" <?= $cuenta['ESTADO_CUENTA'] == 0 ? 'selected' : '' ?>>Baja</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6 pl-lg-4 col-derecha">
                                        <div class="form-column-title">
                                            <i class="fas fa-keyboard"></i> Datos de Ingreso
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="glosa">Cuenta (Glosa)</label>
                                            <input type="text" name="glosa" id="glosa" class="form-control" value="<?= htmlspecialchars($cuenta['GLOSA_CUENTA'] ?? '') ?>" placeholder="Ej: nombre.apellido">
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="req_inicio">Requerimiento de Inicio</label>
                                            <input type="text" name="req_inicio" id="req_inicio" class="form-control" value="<?= htmlspecialchars($cuenta['REQUERIMIENTO_INICIO_CUENTA'] ?? '') ?>" placeholder="N° o código de requerimiento">
                                        </div>

                                        <div class="form-group mb-4">
                                            <label for="req_termino">Requerimiento de Término</label>
                                            <input type="text" name="req_termino" id="req_termino" class="form-control" value="<?= htmlspecialchars($cuenta['REQUERIMIENTO_TERMINO_CUENTA'] ?? '') ?>" placeholder="N° o código de requerimiento">
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="fecha_creacion">Fecha de Creación</label>
                                            <input type="date" name="fecha_creacion" id="fecha_creacion" class="form-control" value="<?= $cuenta['FECHA_CREACION'] ? date('Y-m-d', strtotime($cuenta['FECHA_CREACION'])) : '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <hr class="mt-4 mb-4" style="border-color: var(--border);">

                                <div class="d-flex justify-content-end align-items-center gap-3" style="gap: 1rem;">
                                    <a href="javascript:history.back()" class="text-muted" style="font-weight: 600; text-decoration: none; font-size: 0.85rem; margin-right: 1rem;">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="interactive-btn btn-action-primary">
                                        <i class="fas fa-save mr-2"></i> Guardar Cambios
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="/SIUGI/public/assets/js/editar_cuenta.js"></script>

<?php include __DIR__ . '/../../templates/layout_bottom.php'; ?>