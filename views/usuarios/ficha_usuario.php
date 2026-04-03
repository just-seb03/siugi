<?php

$id = isset($_REQUEST["id"]) ? (int)$_REQUEST["id"] : 0;
if ($id === 0) {
    die("ID de usuario no válido.");
}


require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo_info = $db->getInfoConnection();
$pdo_cuentas = $db->getCuentasConnection();


$stmt_user = $pdo_info->prepare("SELECT Nombre, usuario, Rut, fec_nacimiento, Cod_Fiscalia, Cargo, correo_electronico, telefono, ip, tipo_usuario, fec_inicio_funciones, fec_termino_funciones, estado FROM usuarios WHERE id = ?");
$stmt_user->execute([$id]);
$u = $stmt_user->fetch();

if (!$u) {
    die("Usuario no encontrado en la base de datos.");
}


$tipo_str = 'Desconocido';
if ($u['tipo_usuario'] == 1) $tipo_str = 'Fiscal';
elseif ($u['tipo_usuario'] == 2) $tipo_str = 'Funcionario';
elseif ($u['tipo_usuario'] == 3) $tipo_str = 'Alumno en práctica';

$estado_str = ($u['estado'] == 0) ? 'Activo' : 'Inactivo';
$fecha_nacimiento = !empty($u['fec_nacimiento']) ? date('d/m/Y', strtotime($u['fec_nacimiento'])) : 'N/A';
$fecha_inicio = !empty($u['fec_inicio_funciones']) ? date('d/m/Y', strtotime($u['fec_inicio_funciones'])) : 'N/A';
$fecha_termino = !empty($u['fec_termino_funciones']) ? date('d/m/Y', strtotime($u['fec_termino_funciones'])) : 'N/A';

$stmt_sede = $pdo_cuentas->prepare("SELECT GLOSA_FISCALIA FROM sedes WHERE ID_SEDE = ?");
$stmt_sede->execute([$u['Cod_Fiscalia']]);
$sede_str = $stmt_sede->fetchColumn() ?: 'Sede Desconocida (' . $u['Cod_Fiscalia'] . ')';


require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf.php';
require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf-easytable-master/exfpdf.php';
require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf-easytable-master/easyTable.php';

$pdf = new exFPDF();
$pdf->AddPage(); 
$pdf->SetFont('arial', '', 8);


$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/assets/images/logo_fiscalia.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 10, 40);
}
$pdf->Ln(15);


$headerTable = new easyTable($pdf, '{130, 60}', 'width:190; align:C; border:0');
$headerTable->easyCell(utf8_decode("FICHA DE USUARIO: " . strtoupper($u['Nombre'])), 'font-size:14; font-style:B; align:L');
$headerTable->easyCell(utf8_decode("Fecha Emisión: " . date('d/m/Y H:i')), 'align:R; font-size:9; valign:M');
$headerTable->printRow();
$headerTable->endTable(5);


$table3 = new easyTable($pdf, '{45,145}', 'font-size:9; split-row:true; align:L; border:1');

$datos_usuario = [
    'USUARIO'            => $u['usuario'],
    'RUT'                => $u['Rut'],
    'FECHA NACIMIENTO'   => $fecha_nacimiento,
    'ESTADO'             => $estado_str,
    'TIPO DE PERFIL'     => $tipo_str,
    'SEDE / FISCALIA'    => $sede_str,
    'CARGO'              => $u['Cargo'],
    'CORREO ELECTRONICO' => $u['correo_electronico'],
    'TELEFONO'           => $u['telefono'],
    'DIRECCION IP'       => $u['ip'],
    'FECHA INICIO'       => $fecha_inicio,
    'FECHA TERMINO'      => $fecha_termino
];

foreach($datos_usuario as $label => $val) {
    $table3->easyCell(utf8_decode($label), 'bgcolor:#046d8b; font-color:#fff; font-style:B;');
    

    $estilo_valor = '';
    if (in_array($label, ['USUARIO', 'TIPO DE PERFIL', 'SEDE / FISCALIA', 'CARGO'])) {
        $estilo_valor = 'font-style:B; font-size:11;'; 
    }
    
    $table3->easyCell(utf8_decode($val ?? 'N/A'), $estilo_valor);
    $table3->printRow();
}
$table3->endTable(8);


$table2 = new easyTable($pdf, 1);
$table2->easyCell(utf8_decode("HISTORIAL DE CUENTAS ASIGNADAS"), 'font-size:11; align:C; font-style:B;');
$table2->printRow(); 
$table2->endTable(3);


$table_ctas = new easyTable($pdf, '{10,25,35,20,20,45,20,15}', 'width:190; font-size:6.5; split-row:true; align:L; border:1; paddingY:1.5');
$table_ctas->rowStyle('bgcolor:#046d8b; font-color:#fff; font-style:B; align:C;');
$table_ctas->easyCell('ID');
$table_ctas->easyCell('SOFTWARE');
$table_ctas->easyCell('SEDE CUENTA');
$table_ctas->easyCell('REQ. INI');
$table_ctas->easyCell('REQ. TER');
$table_ctas->easyCell('CUENTA (GLOSA)');
$table_ctas->easyCell(utf8_decode('CREACIÓN'));
$table_ctas->easyCell('ESTADO');
$table_ctas->printRow();


$stmt_cuentas = $pdo_cuentas->prepare("SELECT * FROM vista_cuentas_detalle WHERE USUARIO = ? AND ESTADO_CUENTA !=2 ORDER BY ESTADO_CUENTA DESC, FECHA_CREACION ASC");
$stmt_cuentas->execute([$id]);
$cuentas = $stmt_cuentas->fetchAll();

if (count($cuentas) > 0) {
    foreach ($cuentas as $cta) {
        if ($cta['ESTADO_CUENTA'] == 1) $estado_cta = 'Alta';
        elseif ($cta['ESTADO_CUENTA'] == 3) $estado_cta = 'Pendiente';
        else $estado_cta = 'Baja';

        $fecha_cta = $cta['FECHA_CREACION'] ? date('d/m/Y', strtotime($cta['FECHA_CREACION'])) : 'N/A';
        $req_ini = !empty($cta['REQUERIMIENTO_INICIO_CUENTA']) ? $cta['REQUERIMIENTO_INICIO_CUENTA'] : '-';
        $req_ter = !empty($cta['REQUERIMIENTO_TERMINO_CUENTA']) ? $cta['REQUERIMIENTO_TERMINO_CUENTA'] : '-';

        $table_ctas->easyCell($cta['ID_CUENTA'], 'align:C;');
        $table_ctas->easyCell(utf8_decode($cta['SOFTWARE'] ?? 'N/A'));
        $table_ctas->easyCell(utf8_decode($cta['SEDE'] ?? 'N/A'));
        $table_ctas->easyCell(utf8_decode($req_ini), 'align:C;');
        $table_ctas->easyCell(utf8_decode($req_ter), 'align:C;');
        $table_ctas->easyCell(utf8_decode($cta['GLOSA_CUENTA'] ?? '-'));
        $table_ctas->easyCell($fecha_cta, 'align:C;');
        $table_ctas->easyCell(utf8_decode($estado_cta), 'align:C;');
        $table_ctas->printRow();
    }
} else {
    $table_ctas->rowStyle('align:C;');
    $table_ctas->easyCell(utf8_decode('El usuario no registra cuentas en el sistema.'), 'colspan:8; paddingY:3;');
    $table_ctas->printRow();
}
$table_ctas->endTable();


ob_clean();
$pdf->Output('I', 'Ficha_Usuario_' . $u['usuario'] . '.pdf');
exit();