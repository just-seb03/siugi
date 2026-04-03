<?php
session_start();

require_once __DIR__ . '/../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();

$id_usuario_sesion = $_SESSION['userId'] ?? 0;

if (empty($id_usuario_sesion)) {
    die("Error: No tiene permisos para realizar esta acción o la sesión ha expirado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $es_masivo = intval($_POST['es_masivo'] ?? 0);
    $estado_alta = 0; 

    try {
        if ($es_masivo === 1) {
            $cantidad = intval($_POST['cantidad']);
            
            $sql = "INSERT INTO dispositivos (ID_SUBCAT, ID_UBICACION, FECHA_REGISTRO, ID_ESTADO_CGU, ID_MARCA, MODELO, ID_PROVEEDOR, ID_USUARIO_REGISTRO) 
                    VALUES (?, 2, CURDATE(), ?, ?, ?, ?, ?)";
            
            $stmt = $pdo_inv->prepare($sql);
            
            for ($i = 0; $i < $cantidad; $i++) { 
                $stmt->execute([
                    $_POST['id_subcat'], 
                    $estado_alta, 
                    $_POST['id_marca'], 
                    $_POST['modelo'], 
                    $_POST['id_proveedor'], 
                    $id_usuario_sesion
                ]); 
            }
        } else {
            $sql = "INSERT INTO dispositivos (ID_SUBCAT, ID_UBICACION, FECHA_REGISTRO, ID_ESTADO_CGU, ID_MARCA, MODELO, SERIE, CODIGO_INVENTARIO, IP, MAC, NOMBRE_MAQUINA, CLAVE_ACCESO, ID_PROVEEDOR, OBSERVACION, ID_USUARIO_REGISTRO) 
                    VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo_inv->prepare($sql);
            $stmt->execute([
                $_POST['id_subcat'],
                $_POST['id_ubicacion'],
                $estado_alta,
                $_POST['id_marca'],
                $_POST['modelo'],
                $_POST['serie'],
                empty($_POST['codigo_inventario']) ? null : $_POST['codigo_inventario'],
                $_POST['ip'],
                $_POST['mac'],
                $_POST['nombre_maquina'],
                $_POST['clave_acceso'],
                $_POST['id_proveedor'],
                $_POST['observacion'],
                $id_usuario_sesion
            ]);
        }

        header("Location: /SIUGI/consultar_bienes?msg=success");
        exit();

    } catch (PDOException $e) {
        die("Error de Base de Datos: " . $e->getMessage());
    }
}