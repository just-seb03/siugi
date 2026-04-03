<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_init'])) {
    
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    $pdo_cuentas = $db->getCuentasConnection();

    $id_u = (int)$_POST['id_usuario'];
    $sede_u = $_POST['sede_usuario'];
    
    try {
        if (isset($_POST['sw_faltantes']) && is_array($_POST['sw_faltantes'])) {
            $fecha = date('Y-m-d');
            

            $sql = "INSERT INTO cuentas (ID_SOFTWARE, ID_SEDE, USUARIO, GLOSA_CUENTA, ESTADO_CUENTA, FECHA_CREACION, ES_GENERICA) 
                    VALUES (:id_sw, :sede, :id_u, ' ', 3, :fecha, 0)";
            $stmt_ins = $pdo_cuentas->prepare($sql);
            

            foreach ($_POST['sw_faltantes'] as $id_sw) {
                $stmt_ins->execute([
                    ':id_sw' => $id_sw, 
                    ':sede'  => $sede_u, 
                    ':id_u'  => $id_u, 
                    ':fecha' => $fecha
                ]);
            }
        }
        

        header("Location: /SIUGI/menu_usuario?id=" . $id_u . "&status=success&msg=cuentas_asignadas");
        exit();

    } catch (PDOException $e) {

        header("Location: /SIUGI/menu_usuario?id=" . $id_u . "&status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {

    header("Location: /SIUGI/index");
    exit();
}