<?php
require '../vendor/autoload.php';
include("../config/db.php");
include("../includes/permisos.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Recibir el filtro del mes (si existe)
$mesFiltro = isset($_GET['mes']) ? $_GET['mes'] : 'todos';
$areaFiltro = isset($_GET['area']) ? $_GET['area'] : 'todas';

$where = " WHERE 1=1 ";
if ($mesFiltro != 'todos') {
    $mesEscapado = $conexion->real_escape_string($mesFiltro);
    $where .= " AND MONTH(fecha_crea) = '$mesEscapado' ";
}

if ($areaFiltro != 'todas') {
    $areaEscapada = $conexion->real_escape_string($areaFiltro);
    $where .= " AND area = '$areaEscapada' ";
}

// Inicializar el documento Excel
$spreadsheet = new Spreadsheet();

/* =========================================
   HOJA 1: USUARIOS CON MAYOR ÍNDICE POR MES
========================================= */
$hoja1 = $spreadsheet->getActiveSheet();
$hoja1->setTitle('Uso por Usuarios');

// Encabezados
$hoja1->setCellValue('A1', 'Mes');
$hoja1->setCellValue('B1', 'Área');
$hoja1->setCellValue('C1', 'Usuario');
$hoja1->setCellValue('D1', 'Total de Tickets');

// Estilo de encabezados
$estiloEncabezado = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3742FA']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$hoja1->getStyle('A1:D1')->applyFromArray($estiloEncabezado);

// Consulta SQL
$sqlUsuarios = "
    SELECT MONTH(fecha_crea) as numero_mes, usuario, COUNT(*) as total 
    FROM tickets 
    $where 
    GROUP BY MONTH(fecha_crea), usuario, area, usuario
    ORDER BY numero_mes DESC, total DESC
";
$resultUsuarios = $conexion->query($sqlUsuarios);

$fila = 2;
$mesesNombres = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

if ($resultUsuarios) {
    while ($row = $resultUsuarios->fetch_assoc()) {
        $nombreMes = isset($mesesNombres[$row['numero_mes']]) ? $mesesNombres[$row['numero_mes']] : 'Desconocido';
        $hoja1->setCellValue('A' . $fila, $nombreMes);
        $hoja1->setCellValue('B' . $fila, $row['area']);
        $hoja1->setCellValue('C' . $fila, $row['usuario']);
        $hoja1->setCellValue('D' . $fila, $row['total']);
        $fila++;
    }
}
// Autoajustar columnas
foreach (range('A', 'D') as $col) {
    $hoja1->getColumnDimension($col)->setAutoSize(true);
}

/* =========================================
   HOJA 2: FRECUENCIA DE TIPOS DE TICKETS (URGENCIA)
========================================= */
$hoja2 = $spreadsheet->createSheet();
$hoja2->setTitle('Frecuencia por Tipo');

// Encabezados
$hoja2->setCellValue('A1', 'Urgencia / Tipo');
$hoja2->setCellValue('B1', 'Cantidad Total');

$hoja2->getStyle('A1:B1')->applyFromArray($estiloEncabezado);

// Consulta SQL
$sqlUrgencias = "
    SELECT urgencia, COUNT(*) as total 
    FROM tickets 
    $where 
    GROUP BY urgencia 
    ORDER BY total DESC
";
$resultUrgencias = $conexion->query($sqlUrgencias);

$fila = 2;
if ($resultUrgencias) {
    while ($row = $resultUrgencias->fetch_assoc()) {
        $hoja2->setCellValue('A' . $fila, $row['urgencia']);
        $hoja2->setCellValue('B' . $fila, $row['total']);
        $fila++;
    }
}
// Autoajustar columnas
foreach (range('A', 'B') as $col) {
    $hoja2->getColumnDimension($col)->setAutoSize(true);
}

// Activar de nuevo la primera hoja
$spreadsheet->setActiveSheetIndex(0);

/* =========================================
   DESCARGAR ARCHIVO
========================================= */
$nombreArchivo = 'Estadisticas_Reportes_' . date('Y-m-d_H-i') . '.xlsx';

if (ob_get_length()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
