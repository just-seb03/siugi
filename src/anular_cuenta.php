<?php

session_start();


if (isset($_GET['id_cuenta']) && isset($_GET['id_usuario'])) {
    
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    $pdo_cuentas = $db->getCuentasConnection();

    $id_anular = (int)$_GET['id_cuenta'];
    $id_usuario = (int)$_GET['id_usuario'];

    try {

        $stmt = $pdo_cuentas->prepare("UPDATE cuentas SET ESTADO_CUENTA = 2 WHERE ID_CUENTA = :id_cuenta");
        $stmt->execute([':id_cuenta' => $id_anular]);

        header("Location: /SIUGI/menu_usuario?id=" . $id_usuario . "&status=success&msg=cuenta_anulada");
        exit();

    } catch (PDOException $e) {

        header("Location: /SIUGI/menu_usuario?id=" . $id_usuario . "&status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {

    header("Location: /SIUGI/index");
    exit();
}