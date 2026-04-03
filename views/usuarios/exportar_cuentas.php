<?php


ob_start();
session_start();
$usuario_sesion = isset($_SESSION['user']) ? $_SESSION['user'] : 'Usuario Desconocido';


require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();

$pdo = $db->getCuentasConnection();
$pdo_info = $db->getInfoConnection();


require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf.php';
require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf-easytable-master/exfpdf.php';
require_once __DIR__ . '/../../public/assets/plugins/fpdf/fpdf-easytable-master/easyTable.php';

class PDF_Reporte extends exFPDF {
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }
}

$sedes = $pdo->query("SELECT ID_SEDE, GLOSA_FISCALIA FROM sedes ORDER BY GLOSA_FISCALIA ASC")->fetchAll();


$f_busqueda_usuario = isset($_GET['q_usr']) ? trim($_GET['q_usr']) : '';
$f_sw = $_GET['sw'] ?? '';
$f_sd_id = $_GET['sd'] ?? ''; 
$f_tipo_usr = $_GET['tipo_usr'] ?? '';
$f_usr = $_GET['usr'] ?? '';
$f_generica = $_GET['f_gen'] ?? '';
$f_glosa = isset($_GET['glosa']) ? trim($_GET['glosa']) : '';
$f_req = isset($_GET['req']) ? trim($_GET['req']) : '';
$f_bajas = isset($_GET['ver_bajas']) ? 1 : 0; 
$f_alerta_bajas = isset($_GET['alerta_bajas']) ? 1 : 0;
$f_proximos_terminos = isset($_GET['proximos_terminos']) ? 1 : 0;

$where = ["1=1"];
$params = [];
$filtros_aplicados = [];


if ($f_busqueda_usuario !== '') {
    $where[] = "USUARIO IN (SELECT id FROM informatica.usuarios WHERE nombre LIKE :like1 OR usuario LIKE :like2 OR rut LIKE :like3)";
    $like_term = '%' . $f_busqueda_usuario . '%';
    $params[':like1'] = $like_term;
    $params[':like2'] = $like_term;
    $params[':like3'] = $like_term;
    $filtros_aplicados[] = "Búsqueda Usuario: " . $f_busqueda_usuario;
}

if ($f_bajas) {
    $where[] = "ESTADO_CUENTA = 0"; 
    $filtros_aplicados[] = "Cuentas Anuladas";
} else {
    $where[] = "ESTADO_CUENTA IN (1, 3)"; 
    $filtros_aplicados[] = "Cuentas Activas/Pendientes";
}

if ($f_alerta_bajas) {
    $where[] = "USUARIO IN (SELECT id FROM informatica.usuarios WHERE estado = 1)";
    $filtros_aplicados[] = "Alerta: Usuarios de baja con cuentas";
}

if ($f_proximos_terminos) {
    $where[] = "USUARIO IN (SELECT id FROM informatica.usuarios WHERE fec_termino_funciones IS NOT NULL AND estado = 0)";
    $filtros_aplicados[] = "Alerta: Próximos términos de funciones";
}

if ($f_sw !== '') {
    $where[] = "SOFTWARE = :sw";
    $params[':sw'] = $f_sw;
    $filtros_aplicados[] = "Sistema: " . $f_sw;
}

if ($f_generica !== '') {
    $where[] = "ES_GENERICA = :gen";
    $params[':gen'] = (int)$f_generica;
    $filtros_aplicados[] = "Categoría: " . ($f_generica === '1' ? 'Genérica' : 'Personal');
}


if ($f_glosa !== '') {
    $where[] = "GLOSA_CUENTA = :glosa";
    $params[':glosa'] = $f_glosa;
    $filtros_aplicados[] = "Glosa Exacta: " . $f_glosa;
}

if ($f_req !== '') {
    $where[] = "REQUERIMIENTO_INICIO_CUENTA = :req";
    $params[':req'] = $f_req;
    $filtros_aplicados[] = "Req. Inicio: " . $f_req;
}

$f_sd_glosa = '';
if ($f_sd_id !== '') {
    foreach ($sedes as $s) {
        if ($s['ID_SEDE'] == $f_sd_id) {
            $f_sd_glosa = $s['GLOSA_FISCALIA'];
            break;
        }
    }
    if ($f_sd_glosa !== '') {
        $where[] = "SEDE = :sede_glosa";
        $params[':sede_glosa'] = $f_sd_glosa;
        $filtros_aplicados[] = "Sede: " . $f_sd_glosa;
    }
}

if ($f_tipo_usr !== '') {
    $stmt_u_filtro = $pdo_info->prepare("SELECT id FROM usuarios WHERE tipo_usuario = ?");
    $stmt_u_filtro->execute([$f_tipo_usr]);
    $u_ids = $stmt_u_filtro->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($u_ids)) {
        $where[] = "1=0";
    } else {

        $inQuery = implode(',', array_map(function($key) { return ":usr_tipo_$key"; }, array_keys($u_ids)));
        $where[] = "USUARIO IN ($inQuery)";
        foreach ($u_ids as $key => $val) {
            $params[":usr_tipo_$key"] = $val;
        }
    }
    $tipos_nombres = ['1' => 'Fiscal', '2' => 'Funcionario', '3' => 'Alumno en práctica'];
    $filtros_aplicados[] = "Tipo Usuario: " . ($tipos_nombres[$f_tipo_usr] ?? 'Desconocido');
}

if ($f_usr !== '') {
    $where[] = "USUARIO = :usr_directo";
    $params[':usr_directo'] = $f_usr;
    $filtros_aplicados[] = "ID Usuario: " . $f_usr;
}


$whereClause = implode(" AND ", $where);
$sqlData = "SELECT * FROM vista_cuentas_detalle WHERE $whereClause ORDER BY SOFTWARE ASC, SEDE ASC, FECHA_CREACION ASC, GLOSA_CUENTA ASC";
$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($params);
$resultados = $stmtData->fetchAll();


$contadores = [];
$contar_por_sede = ($f_sw !== '' && $f_sd_id === '');

foreach ($resultados as $r) {
    if ($contar_por_sede) {
        $clave = empty($r['SEDE']) ? 'Desconocida' : $r['SEDE'];
    } else {
        $clave = empty($r['SOFTWARE']) ? 'Desconocido' : $r['SOFTWARE'];
    }
    
    if (!isset($contadores[$clave])) {
        $contadores[$clave] = 0;
    }
    $contadores[$clave]++;
}

$str_contadores = [];
foreach ($contadores as $k => $total) {
    $str_contadores[] = "$k ($total)";
}
$texto_contadores = implode(' | ', $str_contadores);
$etiqueta_contador = $contar_por_sede ? "Total por Sede: " : "Total por Sistema: ";

$stmtRut = $pdo_info->prepare("SELECT rut, estado FROM usuarios WHERE id = ? LIMIT 1");


$pdf = new PDF_Reporte('P', 'mm', 'A4'); 
$pdf->AliasNbPages(); 
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/assets/images/logo_fiscalia.png';
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 10, 40);
}
$pdf->Ln(12);


$headerTable = new easyTable($pdf, '{120, 70}', 'width:190; align:C; border:0');
$headerTable->easyCell(utf8_decode("REPORTE DE CUENTAS DE USUARIO"), 'font-size:14; font-style:B; align:L');
$headerTable->easyCell(utf8_decode("Fecha Emisión: " . date('d/m/Y H:i')), 'align:R; font-size:9; valign:M');
$headerTable->printRow();
$headerTable->easyCell(utf8_decode("Reporte generado por: " . strtoupper($usuario_sesion)), 'align:L; font-size:9; colspan:2');
$headerTable->printRow();

if (!empty($texto_contadores)) {
    $headerTable->easyCell(utf8_decode($etiqueta_contador . $texto_contadores), 'colspan:2; align:L; font-size:8; font-style:B; font-color:#333333');
    $headerTable->printRow();
}

$txt_filtros = empty($filtros_aplicados) ? "Ninguno (Reporte General)" : implode(' | ', $filtros_aplicados);
$headerTable->easyCell(utf8_decode("Filtros aplicados: " . $txt_filtros), 'colspan:2; align:L; font-size:8; font-style:I; font-color:#505050');
$headerTable->printRow();
$headerTable->endTable(5);


$table = new easyTable($pdf, '{7, 18, 22, 14, 14, 16, 30, 48, 21}', 'width:190; border:1; font-size:6; align:C; valign:M; splitRows:true; paddingY:1.5');

$table->rowStyle('bgcolor:#4f46e5; font-color:#ffffff; font-style:B; headerRow:1');
$table->easyCell(utf8_decode('N°'));
$table->easyCell('SOFTWARE');
$table->easyCell('SEDE');
$table->easyCell('REQ. INI');
$table->easyCell('REQ. TER');
$table->easyCell('RUT');
$table->easyCell('USUARIO');
$table->easyCell('CUENTA');
$table->easyCell(utf8_decode('CREACIÓN'));
$table->printRow();

$cont = 1;
foreach ($resultados as $row) {
    $rut_valor = 'N/A';
    $usr_estado = 0;
    
    if (!empty($row['USUARIO'])) {
        $stmtRut->execute([$row['USUARIO']]);
        $resRut = $stmtRut->fetch();
        if ($resRut) {
            $rut_valor = $resRut['rut'];
            $usr_estado = $resRut['estado'];
        }
    }

    $nombre_usr = empty($row['NOMBRE_USUARIO']) ? '*Baja*' : $row['NOMBRE_USUARIO'];
    if ($usr_estado == 1 && !empty($row['NOMBRE_USUARIO'])) {
        $nombre_usr .= " (Baja)";
    }

    $req_inicio = !empty($row['REQUERIMIENTO_INICIO_CUENTA']) ? $row['REQUERIMIENTO_INICIO_CUENTA'] : '-';
    $req_termino = !empty($row['REQUERIMIENTO_TERMINO_CUENTA']) ? $row['REQUERIMIENTO_TERMINO_CUENTA'] : '-';
    $fecha_creacion = !empty($row['FECHA_CREACION']) ? date('d/m/Y', strtotime($row['FECHA_CREACION'])) : 'N/A';

    $table->easyCell($cont);
    $table->easyCell(utf8_decode($row['SOFTWARE']));
    $table->easyCell(utf8_decode($row['SEDE']), 'align:L');
    $table->easyCell(utf8_decode($req_inicio));
    $table->easyCell(utf8_decode($req_termino));
    $table->easyCell(utf8_decode($rut_valor));
    $table->easyCell(utf8_decode($nombre_usr), 'align:L');
    $table->easyCell(utf8_decode($row['GLOSA_CUENTA']), 'align:L');
    $table->easyCell($fecha_creacion);
    $table->printRow();
    $cont++;
}

$table->endTable();


ob_clean();
$pdf->Output('I', 'Reporte_Cuentas.pdf');
exit();