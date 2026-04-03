<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    $pdo_inv = $db->getInvConnection();

    $accion = $_POST['accion'];
    $tipo = $_POST['tipo_entidad'];
    $id_item = $_POST['id_elemento'] ?? null;

    function obtenerSiguienteID($pdo, $tabla, $columna) {
        $stmt = $pdo->query("SELECT MAX($columna) as max_id FROM $tabla");
        $max = $stmt->fetchColumn();
        return ($max ? $max + 1 : 1);
    }

    $id_usuario_asignado = empty($_POST['id_usuario_asignado']) ? null : (int)$_POST['id_usuario_asignado'];

    try {
        if ($accion === 'agregar') {
            switch ($tipo) {
                case 'sede':
                    $id = obtenerSiguienteID($pdo_inv, 'sedes', 'ID_SEDE');
                    $stmt = $pdo_inv->prepare("INSERT INTO sedes (ID_SEDE, GLOSA_FISCALIA) VALUES (?, ?)");
                    $stmt->execute([$id, $_POST['glosa_sede']]);
                    break;
                case 'edificio':
                    $id = obtenerSiguienteID($pdo_inv, 'edificios', 'ID_EDIFICIO');
                    $stmt = $pdo_inv->prepare("INSERT INTO edificios (ID_SEDE, ID_EDIFICIO, GLOSA_EDIFICIO) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['id_sede_edificio'], $id, $_POST['glosa_edificio']]);
                    break;
                case 'division':
                    $id = obtenerSiguienteID($pdo_inv, 'divisiones', 'ID_DIVISION');
                    $stmt = $pdo_inv->prepare("INSERT INTO divisiones (ID_EDIFICIO, ID_DIVISION, GLOSA_DIVISION) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['id_edificio_division'], $id, $_POST['glosa_division']]);
                    break;
                case 'ubicacion':
                    $id = obtenerSiguienteID($pdo_inv, 'ubicaciones', 'ID_UBICACION');
                    $stmt = $pdo_inv->prepare("INSERT INTO ubicaciones (ID_UBICACION, FISCALIA_UBICACION, EDIFICIO_UBICACION, DIVISION_UBICACION, GLOSA_UBICACION, TIPO, ID_USUARIO_ASIGNADO) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$id, $_POST['ubi_id_sede'], $_POST['ubi_id_edificio'], $_POST['ubi_id_division'], $_POST['glosa_ubicacion'], $_POST['tipo_ubicacion'], $id_usuario_asignado]);
                    break;
                case 'categoria':
                    $id = obtenerSiguienteID($pdo_inv, 'categorias', 'ID_CAT');
                    $stmt = $pdo_inv->prepare("INSERT INTO categorias (ID_CAT, GLOSA_CATEGORIA) VALUES (?, ?)");
                    $stmt->execute([$id, $_POST['glosa_categoria']]);
                    break;
                case 'subcategoria':
                    $id = obtenerSiguienteID($pdo_inv, 'sub_categorias', 'ID_SUBCAT');
                    $stmt = $pdo_inv->prepare("INSERT INTO sub_categorias (ID_CAT, ID_SUBCAT, GLOSA_SUBCATEGORIA) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['id_cat_padre'], $id, $_POST['glosa_subcategoria']]);
                    break;
                case 'marca':
                    $id = obtenerSiguienteID($pdo_inv, 'marcas', 'ID_MARCA');
                    $stmt = $pdo_inv->prepare("INSERT INTO marcas (ID_MARCA, GLOSA_MARCA) VALUES (?, ?)");
                    $stmt->execute([$id, $_POST['glosa_marca']]);
                    break;
                case 'proveedor':
                    $id = obtenerSiguienteID($pdo_inv, 'proveedores', 'ID_PROV');
                    $stmt = $pdo_inv->prepare("INSERT INTO proveedores (ID_PROV, GLOSA_PROVEEDOR) VALUES (?, ?)");
                    $stmt->execute([$id, $_POST['glosa_proveedor']]);
                    break;
            }
        } elseif ($accion === 'modificar') {
            switch ($tipo) {
                case 'sede':
                    $stmt = $pdo_inv->prepare("UPDATE sedes SET GLOSA_FISCALIA = ? WHERE ID_SEDE = ?");
                    $stmt->execute([$_POST['glosa_sede'], $id_item]);
                    break;
                case 'edificio':
                    $stmt = $pdo_inv->prepare("UPDATE edificios SET ID_SEDE = ?, GLOSA_EDIFICIO = ? WHERE ID_EDIFICIO = ?");
                    $stmt->execute([$_POST['id_sede_edificio'], $_POST['glosa_edificio'], $id_item]);
                    break;
                case 'division':
                    $stmt = $pdo_inv->prepare("UPDATE divisiones SET ID_EDIFICIO = ?, GLOSA_DIVISION = ? WHERE ID_DIVISION = ?");
                    $stmt->execute([$_POST['id_edificio_division'], $_POST['glosa_division'], $id_item]);
                    break;
                case 'ubicacion':
                    $stmt = $pdo_inv->prepare("UPDATE ubicaciones SET FISCALIA_UBICACION=?, EDIFICIO_UBICACION=?, DIVISION_UBICACION=?, GLOSA_UBICACION=?, TIPO=?, ID_USUARIO_ASIGNADO=? WHERE ID_UBICACION=?");
                    $stmt->execute([$_POST['ubi_id_sede'], $_POST['ubi_id_edificio'], $_POST['ubi_id_division'], $_POST['glosa_ubicacion'], $_POST['tipo_ubicacion'], $id_usuario_asignado, $id_item]);
                    break;
                case 'categoria':
                    $stmt = $pdo_inv->prepare("UPDATE categorias SET GLOSA_CATEGORIA = ? WHERE ID_CAT = ?");
                    $stmt->execute([$_POST['glosa_categoria'], $id_item]);
                    break;
                case 'subcategoria':
                    $stmt = $pdo_inv->prepare("UPDATE sub_categorias SET ID_CAT = ?, GLOSA_SUBCATEGORIA = ? WHERE ID_SUBCAT = ?");
                    $stmt->execute([$_POST['id_cat_padre'], $_POST['glosa_subcategoria'], $id_item]);
                    break;
                case 'marca':
                    $stmt = $pdo_inv->prepare("UPDATE marcas SET GLOSA_MARCA = ? WHERE ID_MARCA = ?");
                    $stmt->execute([$_POST['glosa_marca'], $id_item]);
                    break;
                case 'proveedor':
                    $stmt = $pdo_inv->prepare("UPDATE proveedores SET GLOSA_PROVEEDOR = ? WHERE ID_PROV = ?");
                    $stmt->execute([$_POST['glosa_proveedor'], $id_item]);
                    break;
            }
        } elseif ($accion === 'anular') {
            if ($tipo === 'ubicacion') {
                $stmt = $pdo_inv->prepare("DELETE FROM ubicaciones WHERE ID_UBICACION = ?");
                $stmt->execute([$id_item]);
            } else {
                $tablas = ['sede'=>'sedes', 'edificio'=>'edificios', 'division'=>'divisiones', 'proveedor'=>'proveedores', 'marca'=>'marcas', 'categoria'=>'categorias', 'subcategoria'=>'sub_categorias'];
                $id_cols = ['sede'=>'ID_SEDE', 'edificio'=>'ID_EDIFICIO', 'division'=>'ID_DIVISION', 'proveedor'=>'ID_PROV', 'marca'=>'ID_MARCA', 'categoria'=>'ID_CAT', 'subcategoria'=>'ID_SUBCAT'];
                $tabla_nombre = $tablas[$tipo];
                $columna_nombre = $id_cols[$tipo];
                $stmt = $pdo_inv->prepare("DELETE FROM $tabla_nombre WHERE $columna_nombre = ?");
                $stmt->execute([$id_item]);
            }
        }

        $msj_exito = ($accion === 'agregar') ? "agregado" : (($accion === 'modificar') ? "modificado" : "anulado");
        header("Location: /SIUGI/parametros_bienes?status=success&msg=Registro " . $msj_exito . " correctamente");
        exit;
        
    } catch (Exception $e) {
        header("Location: /SIUGI/parametros_bienes?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: /SIUGI/parametros_bienes");
    exit;
}