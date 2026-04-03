<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_crear'])) {
    
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    $pdo_cuentas = $db->getCuentasConnection();

    $id_u           = (int)$_POST['id_usuario'];
    $software       = empty($_POST['software']) ? null : $_POST['software'];
    $sede           = empty($_POST['sede']) ? null : $_POST['sede'];
    $glosa           = trim($_POST['glosa']);
    $estado         = $_POST['estado'];
    $es_generica    = (int)$_POST['es_generica'];
    $req_inicio     = empty($_POST['req_inicio']) ? null : trim($_POST['req_inicio']);
    $req_termino    = empty($_POST['req_termino']) ? null : trim($_POST['req_termino']);
    $fecha_creacion = empty($_POST['fecha_creacion']) ? null : $_POST['fecha_creacion'];

    try {
        $sql = "INSERT INTO cuentas (ID_SOFTWARE, ID_SEDE, USUARIO, GLOSA_CUENTA, ESTADO_CUENTA, REQUERIMIENTO_INICIO_CUENTA, REQUERIMIENTO_TERMINO_CUENTA, FECHA_CREACION, ES_GENERICA) 
                VALUES (:software, :sede, :id_u, :glosa, :estado, :req_inicio, :req_termino, :fecha, :generica)";
        
        $stmt = $pdo_cuentas->prepare($sql);
        $stmt->execute([
            ':software'    => $software, 
            ':sede'        => $sede, 
            ':id_u'        => $id_u, 
            ':glosa'       => $glosa, 
            ':estado'      => $estado, 
            ':req_inicio'  => $req_inicio, 
            ':req_termino' => $req_termino, 
            ':fecha'       => $fecha_creacion, 
            ':generica'    => $es_generica
        ]);
        

        header("Location: /SIUGI/menu_usuario?id=" . $id_u . "&status=success&msg=cuenta_creada");
        exit();

    } catch (PDOException $e) {

        header("Location: /SIUGI/menu_usuario?id=" . $id_u . "&status=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
} else {

    header("Location: /SIUGI/index");
    exit();
}