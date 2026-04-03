<?php
session_start();

require_once __DIR__ . '/../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();

$id_usuario_sesion = $_SESSION['userId'] ?? 1;

if (isset($_GET['id'])) {
    $id_disp = (int)$_GET['id'];
    
    try {
        $pdo_inv->beginTransaction();

        $stmt_info = $pdo_inv->prepare("SELECT ID_UBICACION FROM dispositivos WHERE ID_DISP = :id");
        $stmt_info->execute([':id' => $id_disp]);
        $row = $stmt_info->fetch();
        
        $ubicacion_actual = $row ? $row['ID_UBICACION'] : 0;

        $tipo = "Anulacion";
        $obs = "Dispositivo Anulado del Registro";
        
        $sql_reg = "INSERT INTO registros (ID_DISPOSITIVO, TIPO, ID_NUEVA_UBICACION, ID_ANTIGUA_UBICACION, FECHA_MOVIMIENTO, ID_USUARIO_REGISTRO, OBSERVACION) 
                    VALUES (:id_disp, :tipo, :nueva_ubi, :antigua_ubi, NOW(), :id_usr, :obs)";
        $stmt_reg = $pdo_inv->prepare($sql_reg);
        $stmt_reg->execute([
            ':id_disp'     => $id_disp,
            ':tipo'        => $tipo,
            ':nueva_ubi'   => $ubicacion_actual,
            ':antigua_ubi' => $ubicacion_actual,
            ':id_usr'      => $id_usuario_sesion,
            ':obs'         => $obs
        ]);

        $sql_update = "UPDATE dispositivos SET ELIMINADO = 1, FECHA_ELIMINACION = NOW() WHERE ID_DISP = :id";
        $stmt_update = $pdo_inv->prepare($sql_update);
        $stmt_update->execute([':id' => $id_disp]);
        
        $pdo_inv->commit();

        $params = $_GET;
        unset($params['id']);
        $query_string = http_build_query($params);
        
        header("Location: /SIUGI/consultar_bienes?msg=deleted&" . $query_string);
        exit();

    } catch (Exception $e) {
        $pdo_inv->rollBack();
        die("Error de Sistema: " . $e->getMessage());
    }
} else {
    header("Location: /SIUGI/consultar_bienes");
    exit();
}