<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    $pdo_inv = $db->getInvConnection();

    $id_disp = isset($_POST['id_disp']) ? (int)$_POST['id_disp'] : 0;
    
    $url_retorno = $_POST['url_retorno'] ?? '/SIUGI/consultar_bienes';
    $nueva_ubi = isset($_POST['id_ubicacion']) ? (int)$_POST['id_ubicacion'] : 0;
    $id_usuario_sesion = $_SESSION['userId'] ?? 0;
    $observacion = trim($_POST['observacion'] ?? '');

    try {
        $pdo_inv->beginTransaction();

        $stmt_curr = $pdo_inv->prepare("SELECT * FROM dispositivos WHERE ID_DISP = :id");
        $stmt_curr->execute([':id' => $id_disp]);
        $dispositivo_actual = $stmt_curr->fetch();
        
        if (!$dispositivo_actual) {
            throw new Exception("El dispositivo no existe en la base de datos.");
        }
        
        $antigua_ubi_id = $dispositivo_actual['ID_UBICACION'];
        

        $nombre_imagen = $dispositivo_actual['IMAGEN']; 

        if (isset($_FILES['imagen_dispositivo']) && $_FILES['imagen_dispositivo']['error'] === UPLOAD_ERR_OK) {
            $dir_img = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/img_dispositivos/';
            
            if (!is_dir($dir_img)) {
                @mkdir($dir_img, 0775, true);
            }
            
            $ext = strtolower(pathinfo($_FILES['imagen_dispositivo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $nombre_archivo = basename($_FILES['imagen_dispositivo']['name']);
                $ruta_destino = $dir_img . $nombre_archivo;
                

                if (!file_exists($ruta_destino)) {
                    $tmp_name = $_FILES['imagen_dispositivo']['tmp_name'];
                    if (copy($tmp_name, $ruta_destino)) {
                        @unlink($tmp_name); 
                        
                        $ruta_windows = str_replace('/', '\\', $ruta_destino);
                        @exec("icacls \"$ruta_windows\" /q /c /reset");
                    }
                }
                

                $nombre_imagen = $nombre_archivo;
            }
        }

        $solo_cambio_ubicacion = (
            $antigua_ubi_id != $nueva_ubi && 
            $dispositivo_actual['ID_SUBCAT'] == $_POST['id_subcat'] &&
            $dispositivo_actual['ID_MARCA'] == $_POST['id_marca'] &&
            $dispositivo_actual['MODELO'] == $_POST['modelo'] &&
            $dispositivo_actual['SERIE'] == $_POST['serie'] &&
            $dispositivo_actual['CODIGO_INVENTARIO'] == $_POST['codigo_inventario'] &&
            $dispositivo_actual['IMAGEN'] == $nombre_imagen 
        );

        $tipo_registro = $solo_cambio_ubicacion ? 'Movimiento' : 'Edicion';

        $sql_reg = "INSERT INTO registros (ID_DISPOSITIVO, TIPO, ID_NUEVA_UBICACION, ID_ANTIGUA_UBICACION, FECHA_MOVIMIENTO, ID_USUARIO_REGISTRO, OBSERVACION) 
                    VALUES (:id_disp, :tipo, :nueva_ubi, :antigua_ubi, NOW(), :id_usr, :obs)";
        $stmt_reg = $pdo_inv->prepare($sql_reg);
        $stmt_reg->execute([
            ':id_disp' => $id_disp,
            ':tipo' => $tipo_registro,
            ':nueva_ubi' => $nueva_ubi,
            ':antigua_ubi' => $antigua_ubi_id,
            ':id_usr' => $id_usuario_sesion,
            ':obs' => $observacion
        ]);
        
        $sql_upd = "UPDATE dispositivos SET 
                    ID_SUBCAT = :id_subcat, ID_UBICACION = :id_ubi, ID_ESTADO_CGU = :id_estado, 
                    ID_MARCA = :id_marca, MODELO = :modelo, SERIE = :serie, 
                    CODIGO_INVENTARIO = :cod_inv, IP = :ip, MAC = :mac, 
                    NOMBRE_MAQUINA = :nom_maq, CLAVE_ACCESO = :clave, ID_PROVEEDOR = :id_prov, 
                    OBSERVACION = :obs, IMAGEN = :imagen 
                    WHERE ID_DISP = :id_disp";

        $stmt_upd = $pdo_inv->prepare($sql_upd);
        $stmt_upd->execute([
            ':id_subcat' => $_POST['id_subcat'],
            ':id_ubi' => $nueva_ubi,
            ':id_estado' => $_POST['id_estado_cgu'],
            ':id_marca' => $_POST['id_marca'],
            ':modelo' => $_POST['modelo'],
            ':serie' => $_POST['serie'],
            ':cod_inv' => empty($_POST['codigo_inventario']) ? null : $_POST['codigo_inventario'],
            ':ip' => $_POST['ip'],
            ':mac' => $_POST['mac'],
            ':nom_maq' => $_POST['nombre_maquina'],
            ':clave' => $_POST['clave_acceso'],
            ':id_prov' => $_POST['id_proveedor'],
            ':obs' => $observacion,
            ':imagen' => $nombre_imagen,
            ':id_disp' => $id_disp
        ]);

        $pdo_inv->commit();
        
        $separador = (strpos($url_retorno, '?') !== false) ? '&' : '?';
        $timestamp = time(); 
        header("Location: " . $url_retorno . $separador . "status=success&v=" . $timestamp);
        exit();

    } catch (Exception $e) {
        if (isset($pdo_inv) && $pdo_inv->inTransaction()) {
            $pdo_inv->rollBack();
        }

        $timestamp = time();
        header("Location: /SIUGI/editar_bien?id=" . $id_disp . "&status=error&msg=" . urlencode($e->getMessage()) . "&v=" . $timestamp);
        exit();
    }
} else {
    header("Location: /SIUGI/index");
    exit();
}