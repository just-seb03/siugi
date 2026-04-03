<?php

$id = isset($_REQUEST["id"]) ? (int)$_REQUEST["id"] : 0;
if ($id === 0) {
    die("ID de dispositivo no válido.");
}


require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();


$sql_disp = "SELECT
                UPPER(P.GLOSA_PROVEEDOR) GLOSA_PROVEEDOR,
                UPPER(c.GLOSA_CATEGORIA) GLOSA_CATEGORIA,
                UPPER(sc.GLOSA_SUBCATEGORIA) GLOSA_SUBCATEGORIA,
                UPPER(ec.GLOSA_ESTADO) GLOSA_ESTADO,
                UPPER(m.GLOSA_MARCA) GLOSA_MARCA,
                UPPER(D.MODELO) MODELO,
                UPPER(D.SERIE) SERIE,
                UPPER(D.IP) IP,
                UPPER(D.MAC) MAC,
                UPPER(D.NOMBRE_MAQUINA) NOMBRE_MAQUINA,
                UPPER(D.CODIGO_INVENTARIO) CODIGO_INVENTARIO,
                UPPER(D.OBSERVACION) OBSERVACION,
                D.FECHA_REGISTRO,    
                CONCAT(s.GLOSA_FISCALIA,' / ',e.GLOSA_EDIFICIO,' / ',d2.GLOSA_DIVISION,' / ',u.GLOSA_UBICACION) UBICACION
            FROM dispositivos D
            JOIN sub_categorias sc ON D.ID_SUBCAT = sc.ID_SUBCAT 
            JOIN categorias c ON c.ID_CAT = sc.ID_CAT 
            JOIN estado_cgu ec ON ec.ID_ESTADO_CGU = D.ID_ESTADO_CGU 
            JOIN marcas m ON m.ID_MARCA = D.ID_MARCA 
            JOIN proveedores p ON p.ID_PROV = D.ID_PROVEEDOR
            JOIN ubicaciones u ON u.ID_UBICACION = D.ID_UBICACION
            JOIN divisiones d2 ON d2.ID_DIVISION = u.DIVISION_UBICACION
            JOIN edificios e ON e.ID_EDIFICIO = u.EDIFICIO_UBICACION 
            JOIN sedes s ON s.ID_SEDE = u.FISCALIA_UBICACION 
            WHERE D.ID_DISP = :id";

$stmt_disp = $pdo_inv->prepare($sql_disp);
$stmt_disp->execute([':id' => $id]);
$m = $stmt_disp->fetch();

if (!$m) {
    die("Dispositivo no encontrado.");
}


$sql_hist = "SELECT 
                UPPER(r.TIPO) TIPO,
                DATE_FORMAT(r.FECHA_MOVIMIENTO,'%d/%m/%Y %H:%i') FECHA_MOVIMIENTO,
                r.OBSERVACION,
                (SELECT CONCAT(s.GLOSA_FISCALIA,' / ',e.GLOSA_EDIFICIO,' / ',d2.GLOSA_DIVISION,' / ',u.GLOSA_UBICACION)  
                FROM ubicaciones u 
                JOIN divisiones d2 ON d2.ID_DIVISION = u.DIVISION_UBICACION
                JOIN edificios e ON e.ID_EDIFICIO = u.EDIFICIO_UBICACION 
                JOIN sedes s ON s.ID_SEDE = u.FISCALIA_UBICACION 
                WHERE u.ID_UBICACION = r.ID_NUEVA_UBICACION) UBICACION_NUEVA,
                (SELECT CONCAT(s.GLOSA_FISCALIA,' / ',e.GLOSA_EDIFICIO,' / ',d2.GLOSA_DIVISION,' / ',u.GLOSA_UBICACION)  
                FROM ubicaciones u 
                JOIN divisiones d2 ON d2.ID_DIVISION = u.DIVISION_UBICACION
                JOIN edificios e ON e.ID_EDIFICIO = u.EDIFICIO_UBICACION 
                JOIN sedes s ON s.ID_SEDE = u.FISCALIA_UBICACION 
                WHERE u.ID_UBICACION = r.ID_ANTIGUA_UBICACION) UBICACION_ANTIGUA
             FROM registros r 
             JOIN dispositivos D ON D.ID_DISP = r.ID_DISPOSITIVO 
             WHERE r.ID_DISPOSITIVO = :id
             ORDER BY r.ID_REGISTRO DESC";

$stmt_hist = $pdo_inv->prepare($sql_hist);
$stmt_hist->execute([':id' => $id]);
$historial = $stmt_hist->fetchAll();


require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf.php';
require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf-easytable-master/exfpdf.php';
require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf-easytable-master/easyTable.php';

$pdf = new exFPDF();
$pdf->AddPage(); 


$pdf->SetFont('Arial', '', 10);


$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/assets/images/logo_fiscalia.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 10, 40);
}
$pdf->Ln(15);


$headerTable = new easyTable($pdf, '{140, 50}', 'width:190; align:C; border:0; font-family:Arial');
$headerTable->easyCell(utf8_decode("FICHA: " . $m['GLOSA_SUBCATEGORIA'] . " " . $m['MODELO']), 'font-size:14; font-style:B; align:L');
$headerTable->easyCell(utf8_decode("Fecha Emisión: " . date('d/m/Y H:i')), 'align:R; font-size:9; valign:M');
$headerTable->printRow();
$headerTable->endTable(5);


$table3 = new easyTable($pdf, '{40,150}', 'font-size:9; font-family:Arial; split-row:true; align:L; border:1; paddingY:1.5');


function printRowFicha($table, $label, $value) {
    $table->easyCell(utf8_decode($label), 'bgcolor:#046d8b; font-color:#fff; font-style:B');
    $table->easyCell(utf8_decode($value ?? ''));
    $table->printRow();
}

printRowFicha($table3, 'PROVEEDOR', $m['GLOSA_PROVEEDOR']);
printRowFicha($table3, 'CATEGORIA', $m['GLOSA_CATEGORIA']);
printRowFicha($table3, 'SUBCATEGORIA', $m['GLOSA_SUBCATEGORIA']);
printRowFicha($table3, 'ESTADO', $m['GLOSA_ESTADO']);
printRowFicha($table3, 'MARCA', $m['GLOSA_MARCA']);
printRowFicha($table3, 'MODELO', $m['MODELO']);
printRowFicha($table3, 'SERIE', $m['SERIE']);
printRowFicha($table3, 'IP', $m['IP']);
printRowFicha($table3, 'MAC', $m['MAC']);
printRowFicha($table3, 'NOMBRE MAQUINA', $m['NOMBRE_MAQUINA']);
printRowFicha($table3, 'COD. INVENTARIO', $m['CODIGO_INVENTARIO']);
printRowFicha($table3, 'UBICACION', $m['UBICACION']);
printRowFicha($table3, 'FECHA REGISTRO', !empty($m['FECHA_REGISTRO']) ? date('d/m/Y', strtotime(str_replace('/', '-', $m['FECHA_REGISTRO']))) : '');
printRowFicha($table3, 'OBSERVACION', $m['OBSERVACION']);
$table3->endTable(5);


$table1 = new easyTable($pdf, 1, 'width:190; font-family:Arial');
$table1->easyCell(utf8_decode("HISTORIAL DE MOVIMIENTOS"), 'font-size:11; align:C; font-style:B; bgcolor:#eeeeee; paddingY:2');
$table1->printRow(); 
$table1->endTable(3);


$table = new easyTable($pdf, '{30,20,50,50,35}', 'font-size:7; font-family:Arial; split-row:true; align:L; border:1; paddingY:1.5');
$table->rowStyle('bgcolor:#046d8b; font-color:#fff; font-style:B; align:C;');
$table->easyCell(utf8_decode('FECHA MOVIMIENTO'));
$table->easyCell(utf8_decode('TIPO'));
$table->easyCell(utf8_decode('NUEVA UBICACION'));
$table->easyCell(utf8_decode('ANTIGUA UBICACION'));
$table->easyCell(utf8_decode('OBSERVACION'));
$table->printRow();

if (count($historial) > 0) {
    foreach($historial as $m1) {
        $table->easyCell($m1['FECHA_MOVIMIENTO'], 'align:C;');
        $table->easyCell(utf8_decode($m1['TIPO']), 'align:L;');
        $table->easyCell(utf8_decode($m1['UBICACION_NUEVA']), 'align:L;'); 
        $table->easyCell(utf8_decode($m1['UBICACION_ANTIGUA']), 'align:L;');    
        $table->easyCell(utf8_decode($m1['OBSERVACION']), 'align:L;');   
        $table->printRow(); 
    }
} else {
    $table->easyCell(utf8_decode('Sin movimientos registrados.'), 'colspan:5; align:C; paddingY:5');
    $table->printRow();
}
$table->endTable();


if (ob_get_length()) ob_end_clean();
$pdf->Output('I', 'Ficha_Dispositivo_' . $id . '.pdf');
exit();