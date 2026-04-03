<?php


ob_start(); 
session_start();
$usuario_sesion = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : ($_SESSION['user'] ?? 'Usuario Desconocido');


require_once __DIR__ . '/../../config/db.php';
$db = new DatabaseConnection();
$pdo_inv = $db->getInvConnection();
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


$where = ["d.ELIMINADO = 0"];
$params = [];
$filtros_txt = [];
$mostrar_sede = true;
$mostrar_subcat = true;


if (!empty($_GET['usr'])) {
    $id_usr_filtro = (int)$_GET['usr'];
    $where[] = "u.ID_USUARIO_ASIGNADO = :id_usr_filtro";
    $params[':id_usr_filtro'] = $id_usr_filtro;
    

    $stmt_u_nom = $pdo_info->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt_u_nom->execute([$id_usr_filtro]);
    if ($row_u = $stmt_u_nom->fetch()) {
        $filtros_txt[] = "Usuario: " . $row_u['nombre'];
    }
}


if (isset($_GET['baja']) && $_GET['baja'] == '1') {
    $where[] = "est.GLOSA_ESTADO = 'Baja'";
    $filtros_txt[] = "Estado: SOLO BAJAS";
} elseif (!empty($_GET['estado_filtro'])) {
    $where[] = "est.GLOSA_ESTADO = :estado_filtro";
    $params[':estado_filtro'] = $_GET['estado_filtro'];
    $filtros_txt[] = "Estado: " . $_GET['estado_filtro'];
} else {
    $where[] = "est.GLOSA_ESTADO != 'Baja'";
}

if (!empty($_GET['sede'])) {
    $id_sede = (int)$_GET['sede'];
    $where[] = "s.ID_SEDE = :id_sede";
    $params[':id_sede'] = $id_sede;
    $stmt_s = $pdo_inv->prepare("SELECT GLOSA_FISCALIA FROM sedes WHERE ID_SEDE = ?");
    $stmt_s->execute([$id_sede]);
    if ($row_s = $stmt_s->fetch()) {
        $filtros_txt[] = "Sede: " . $row_s['GLOSA_FISCALIA'];
        $mostrar_sede = false;
    }
}

if (!empty($_GET['tipo'])) {
    $where[] = "sc.GLOSA_SUBCATEGORIA = :tipo";
    $params[':tipo'] = $_GET['tipo'];
    $filtros_txt[] = "Tipo: " . $_GET['tipo'];
    $mostrar_subcat = false;
}

if (!empty($_GET['serie_busqueda'])) {
    $where[] = "d.SERIE LIKE :serie";
    $params[':serie'] = '%' . $_GET['serie_busqueda'] . '%';
    $filtros_txt[] = "Serie: " . $_GET['serie_busqueda'];
}


$whereClause = implode(" AND ", $where);
$q = "SELECT s.GLOSA_FISCALIA, m.GLOSA_MARCA, d.MODELO, d.SERIE, d.FECHA_REGISTRO,
             d.CODIGO_INVENTARIO, divi.GLOSA_DIVISION, u.GLOSA_UBICACION, sc.GLOSA_SUBCATEGORIA
      FROM dispositivos d
      LEFT JOIN ubicaciones u ON d.ID_UBICACION = u.ID_UBICACION
      LEFT JOIN divisiones divi ON u.DIVISION_UBICACION = divi.ID_DIVISION
      LEFT JOIN edificios e ON u.EDIFICIO_UBICACION = e.ID_EDIFICIO
      LEFT JOIN sedes s ON e.ID_SEDE = s.ID_SEDE
      LEFT JOIN marcas m ON d.ID_MARCA = m.ID_MARCA
      LEFT JOIN sub_categorias sc ON d.ID_SUBCAT = sc.ID_SUBCAT
      LEFT JOIN estado_cgu est ON d.ID_ESTADO_CGU = est.ID_ESTADO_CGU
      WHERE $whereClause 
      ORDER BY s.GLOSA_FISCALIA, sc.GLOSA_SUBCATEGORIA, d.CODIGO_INVENTARIO DESC";

$stmt = $pdo_inv->prepare($q);
$stmt->execute($params);
$resultados_array = $stmt->fetchAll();


$contadores = [];
$contar_por_sede = (!$mostrar_subcat && $mostrar_sede);
foreach ($resultados_array as $row) {
    $clave = $contar_por_sede ? ($row['GLOSA_FISCALIA'] ?? 'N/A') : ($row['GLOSA_SUBCATEGORIA'] ?? 'N/A');
    if (!isset($contadores[$clave])) $contadores[$clave] = 0;
    $contadores[$clave]++;
}
$str_contadores = [];
foreach ($contadores as $k => $total) { $str_contadores[] = "$k ($total)"; }
$texto_contadores = implode(' | ', $str_contadores);


$cols = ['7']; $titulos = ['N°'];
if ($mostrar_sede) { $cols[] = '28'; $titulos[] = 'SEDE'; }
if ($mostrar_subcat) { $cols[] = '22'; $titulos[] = 'TIPO'; }
$cols[] = '35'; $titulos[] = 'MARCA/MOD.';
$cols[] = '30'; $titulos[] = 'SERIE';
$cols[] = '15'; $titulos[] = 'CÓD.';
$cols[] = '35'; $titulos[] = 'UBICACIÓN';
$cols[] = '18'; $titulos[] = 'FECHA';
$ancho_final = '{' . implode(', ', $cols) . '}';


$pdf = new PDF_Reporte('P', 'mm', 'A4');
$pdf->AliasNbPages(); 
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10); 

$logo_path = $_SERVER['DOCUMENT_ROOT'] . '/SIUGI/public/assets/images/logo_fiscalia.png';
if (file_exists($logo_path)) { $pdf->Image($logo_path, 10, 10, 40); }
$pdf->Ln(12);

$headerTable = new easyTable($pdf, '{100, 90}', 'width:190; align:C; border:0; font-family:Arial');
$headerTable->easyCell(utf8_decode("REPORTE DE INVENTARIO"), 'font-size:14; font-style:B; align:L');
$headerTable->easyCell(utf8_decode("Fecha: " . date('d/m/Y H:i')), 'align:R; font-size:9');
$headerTable->printRow();
$headerTable->easyCell(utf8_decode("Reporta: " . strtoupper($usuario_sesion)), 'align:L; font-size:9; colspan:2');
$headerTable->printRow();

if (!empty($texto_contadores)) {
    $headerTable->easyCell(utf8_decode(($contar_por_sede ? "Por Sede: " : "Por Tipo: ") . $texto_contadores), 'colspan:2; align:L; font-size:8; font-style:B; font-color:#333333');
    $headerTable->printRow();
}

$txt_f = empty($filtros_txt) ? "General" : implode(' | ', $filtros_txt);
$headerTable->easyCell(utf8_decode("Filtros: " . $txt_f), 'colspan:2; align:L; font-size:8; font-style:I; font-color:#505050');
$headerTable->printRow();
$headerTable->endTable(5);

$table = new easyTable($pdf, $ancho_final, 'width:190; border:1; font-size:6; font-family:Arial; align:C; valign:M; splitRows:true; paddingY:1.5');
$table->rowStyle('bgcolor:#28a745; font-color:#ffffff; font-style:B; headerRow:1');
foreach ($titulos as $t) { $table->easyCell(utf8_decode($t)); }
$table->printRow();

$cont = 1; 
foreach ($resultados_array as $row) {
    $table->easyCell($cont);
    if ($mostrar_sede) $table->easyCell(utf8_decode($row['GLOSA_FISCALIA'] ?? ''));
    if ($mostrar_subcat) $table->easyCell(utf8_decode($row['GLOSA_SUBCATEGORIA'] ?? ''));
    $table->easyCell(utf8_decode(($row['GLOSA_MARCA'] ?? '') . " " . ($row['MODELO'] ?? '')));
    $table->easyCell(utf8_decode($row['SERIE'] ?? ''));
    $table->easyCell(utf8_decode($row['CODIGO_INVENTARIO'] ?? ' '));
    $table->easyCell(utf8_decode(($row['GLOSA_DIVISION'] ?? '') . "\n" . ($row['GLOSA_UBICACION'] ?? '')), 'align:L');
    
    $fecha_formateada = !empty($row['FECHA_REGISTRO']) ? date('d/m/Y', strtotime(str_replace('/', '-', $row['FECHA_REGISTRO']))) : '';
    $table->easyCell(utf8_decode($fecha_formateada));
    
    $table->printRow();
    $cont++;
}

$table->endTable();
ob_end_clean();
$pdf->Output('I', 'Reporte_Inventario.pdf');
exit();