<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_cuenta'])) {
    require_once __DIR__ . '/../config/db.php';
    $db = new DatabaseConnection();
    $pdo_cuentas = $db->getCuentasConnection();


    $id_cuenta      = (int)$_POST['id_cuenta'];
    $software       = empty($_POST['software']) ? null : $_POST['software'];
    $sede           = empty($_POST['sede']) ? null : $_POST['sede'];
    $usuario        = empty($_POST['usuario']) ? null : $_POST['usuario'];
    $glosa          = trim($_POST['glosa']);
    $estado         = $_POST['estado'];
    $es_generica    = (int)$_POST['es_generica']; 
    $req_inicio     = empty($_POST['req_inicio']) ? null : trim($_POST['req_inicio']);
    $req_termino    = empty($_POST['req_termino']) ? null : trim($_POST['req_termino']);
    $fecha_creacion = empty($_POST['fecha_creacion']) ? null : $_POST['fecha_creacion'];

    try {
        $sql_update = "UPDATE cuentas SET 
                        ID_SOFTWARE = :software, 
                        ID_SEDE = :sede, 
                        USUARIO = :usuario, 
                        GLOSA_CUENTA = :glosa, 
                        ESTADO_CUENTA = :estado, 
                        REQUERIMIENTO_INICIO_CUENTA = :req_inicio, 
                        REQUERIMIENTO_TERMINO_CUENTA = :req_termino, 
                        FECHA_CREACION = :fecha,
                        ES_GENERICA = :generica 
                       WHERE ID_CUENTA = :id";
                       
        $stmt = $pdo_cuentas->prepare($sql_update);
        $stmt->execute([
            ':software'   => $software,
            ':sede'       => $sede,
            ':usuario'    => $usuario,
            ':glosa'      => $glosa,
            ':estado'     => $estado,
            ':req_inicio' => $req_inicio,
            ':req_termino'=> $req_termino,
            ':fecha'      => $fecha_creacion,
            ':generica'   => $es_generica,
            ':id'         => $id_cuenta
        ]);

        $redirect_id = $usuario ? $usuario : 0;
        header("Location: /SIUGI/menu_usuario?id=" . $redirect_id . "&status=success&msg=updated");
        exit();

    } catch (Exception $e) {

        $separador = (strpos($url_retorno, '?') !== false) ? '&' : '?';
        header("Location: " . $url_retorno . $separador . "status=success");
        exit();
    }
} else {
    header("Location: /SIUGI/index");
    exit();
}