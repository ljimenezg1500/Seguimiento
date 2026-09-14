<?php
require '../vendor/autoload.php';
include("../config/db.php");
include("../includes/permisos.php");
include("../includes/log_bitacora.php"); // SENSOR DE AUDITORÍA

// Asegurarnos de que la sesión esté iniciada
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Clases para Gráficas
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Layout;

/* =========================================
   RECIBIR FILTROS (COMO ARREGLOS)
========================================= */
$mesesFiltro = isset($_GET['mes']) ? $_GET['mes'] : ['todos'];
$areasFiltro = isset($_GET['area']) ? $_GET['area'] : ['todas'];

if (!is_array($mesesFiltro)) $mesesFiltro = [$mesesFiltro];
if (!is_array($areasFiltro)) $areasFiltro = [$areasFiltro];

/* =========================================
   CONSTRUIR WHERE CON IN()
========================================= */
$where = " WHERE 1=1 ";

if(!in_array('todos', $mesesFiltro) && count($mesesFiltro) > 0){
    $mesesEscapados = array_map(function($m) { return (int)$m; }, $mesesFiltro);
    $where .= " AND MONTH(fecha_crea) IN (" . implode(',', $mesesEscapados) . ") ";
}

if(!in_array('todas', $areasFiltro) && count($areasFiltro) > 0){
    $areasEscapadas = array_map(function($a) use ($conexion) { return "'" . $conexion->real_escape_string($a) . "'"; }, $areasFiltro);
    $where .= " AND area IN (" . implode(',', $areasEscapadas) . ") ";
}

/* =========================================
   REGISTRAR EN BITÁCORA
========================================= */
if(isset($_SESSION['user'])){
    $nombresMeses = implode(", ", $mesesFiltro);
    $nombresAreas = implode(", ", $areasFiltro);
    $detalles = "Descargó reporte estadístico completo. Filtros -> Meses: [$nombresMeses] | Áreas: [$nombresAreas]";
    registrarBitacora($conexion, $_SESSION['user'], 'Exportar Excel', $detalles);
}

// Inicializar el documento Excel
$spreadsheet = new Spreadsheet();

// ESTILO GENERAL PARA ENCABEZADOS
$estiloEncabezado = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3742FA']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

/* =========================================
   HOJA 1: USUARIOS CON MAYOR ÍNDICE
========================================= */
$hoja1 = $spreadsheet->getActiveSheet();
$hoja1->setTitle('Uso por Usuarios');

$hoja1->setCellValue('A1', 'Mes');
$hoja1->setCellValue('B1', 'Área');
$hoja1->setCellValue('C1', 'Usuario');
$hoja1->setCellValue('D1', 'Total de Tickets');
$hoja1->getStyle('A1:D1')->applyFromArray($estiloEncabezado);

$sqlUsuarios = "SELECT MONTH(fecha_crea) as numero_mes, usuario, area, COUNT(*) as total 
                FROM tickets $where GROUP BY MONTH(fecha_crea), usuario, area ORDER BY numero_mes DESC, total DESC";
$resultUsuarios = $conexion->query($sqlUsuarios);
$fila = 2;
$mesesNombres = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];

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
foreach (range('A', 'D') as $col) { $hoja1->getColumnDimension($col)->setAutoSize(true); }

if ($fila > 2) {
    $labels1 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Uso por Usuarios'!\$D\$1", null, 1)];
    $categories1 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Uso por Usuarios'!\$C\$2:\$C\$" . ($fila - 1), null, $fila - 2)];
    $values1 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Uso por Usuarios'!\$D\$2:\$D\$" . ($fila - 1), null, $fila - 2)];
    $series1 = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_STANDARD, range(0, count($values1) - 1), $labels1, $categories1, $values1);
    $series1->setPlotDirection(DataSeries::DIRECTION_COL);
    $chart1 = new Chart('chart_usuarios', new Title('Top Usuarios (Tickets Generados)'), new Legend(Legend::POSITION_RIGHT, null, false), new PlotArea(null, [$series1]), true, DataSeries::EMPTY_AS_GAP, null, null);
    $chart1->setTopLeftPosition('F2'); $chart1->setBottomRightPosition('N18');
    $hoja1->addChart($chart1);
}

/* =========================================
   HOJA 2: FRECUENCIA DE URGENCIA
========================================= */
$hoja2 = $spreadsheet->createSheet();
$hoja2->setTitle('Frecuencia por Tipo');

$hoja2->setCellValue('A1', 'Urgencia / Tipo');
$hoja2->setCellValue('B1', 'Cantidad Total');
$hoja2->getStyle('A1:B1')->applyFromArray($estiloEncabezado);

$sqlUrgencias = "SELECT urgencia, COUNT(*) as total FROM tickets $where GROUP BY urgencia ORDER BY total DESC";
$resultUrgencias = $conexion->query($sqlUrgencias);
$fila2 = 2;
if ($resultUrgencias) {
    while ($row = $resultUrgencias->fetch_assoc()) {
        $hoja2->setCellValue('A' . $fila2, $row['urgencia']);
        $hoja2->setCellValue('B' . $fila2, $row['total']);
        $fila2++;
    }
}
foreach (range('A', 'B') as $col) { $hoja2->getColumnDimension($col)->setAutoSize(true); }

if ($fila2 > 2) {
    $labels2 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Frecuencia por Tipo'!\$B\$1", null, 1)];
    $categories2 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Frecuencia por Tipo'!\$A\$2:\$A\$" . ($fila2 - 1), null, $fila2 - 2)];
    $values2 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Frecuencia por Tipo'!\$B\$2:\$B\$" . ($fila2 - 1), null, $fila2 - 2)];
    $series2 = new DataSeries(DataSeries::TYPE_PIECHART, DataSeries::GROUPING_STANDARD, range(0, count($values2) - 1), $labels2, $categories2, $values2);
    $layout2 = new Layout(); $layout2->setShowVal(true); $layout2->setShowPercent(true);
    $chart2 = new Chart('chart_urgencias', new Title('Distribución por Urgencia'), new Legend(Legend::POSITION_RIGHT, null, false), new PlotArea($layout2, [$series2]), true, DataSeries::EMPTY_AS_GAP, null, null);
    $chart2->setTopLeftPosition('D2'); $chart2->setBottomRightPosition('K16');
    $hoja2->addChart($chart2);
}

/* =========================================
   HOJA 3: TIEMPOS PROMEDIO POR URGENCIA
========================================= */
$hoja3 = $spreadsheet->createSheet();
$hoja3->setTitle('Tiempos de Respuesta');

$hoja3->setCellValue('A1', 'Urgencia');
$hoja3->setCellValue('B1', 'Minutos Promedio');
$hoja3->setCellValue('C1', 'Tiempo Formateado');
$hoja3->getStyle('A1:C1')->applyFromArray($estiloEncabezado);

$sqlTiemposUrgencia = "
    SELECT urgencia, AVG(TIME_TO_SEC(STR_TO_DATE(tiempo_efectivo, '%H:%i'))) / 60 as promedio
    FROM tickets $where 
    AND tiempo_efectivo IS NOT NULL 
    AND tiempo_efectivo != ''
    AND tiempo_efectivo != '00:00'
    AND LOWER(estatus) LIKE '%cerrado%'
    GROUP BY urgencia 
    ORDER BY promedio DESC
";
$resultTiemposUrgencia = $conexion->query($sqlTiemposUrgencia);
$fila3 = 2;

if ($resultTiemposUrgencia) {
    while ($row = $resultTiemposUrgencia->fetch_assoc()) {
        $minutos = round($row['promedio']);
        
        if($minutos >= 60){
            $horas = floor($minutos / 60);
            $minutosRestantes = $minutos % 60;
            $tiempoTexto = $horas . ":" . str_pad($minutosRestantes, 2, "0", STR_PAD_LEFT) . " hrs";
        } else {
            $tiempoTexto = $minutos . " min";
        }

        $hoja3->setCellValue('A' . $fila3, $row['urgencia']);
        $hoja3->setCellValue('B' . $fila3, $minutos);
        $hoja3->setCellValue('C' . $fila3, $tiempoTexto);
        $fila3++;
    }
}
foreach (range('A', 'C') as $col) { $hoja3->getColumnDimension($col)->setAutoSize(true); }

if ($fila3 > 2) {
    $labels3 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Tiempos de Respuesta'!\$B\$1", null, 1)];
    $categories3 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Tiempos de Respuesta'!\$A\$2:\$A\$" . ($fila3 - 1), null, $fila3 - 2)];
    $values3 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Tiempos de Respuesta'!\$B\$2:\$B\$" . ($fila3 - 1), null, $fila3 - 2)];
    
    // TYPE_BARCHART y DIRECTION_BAR para que las barras sean horizontales
    $series3 = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_STANDARD, range(0, count($values3) - 1), $labels3, $categories3, $values3);
    $series3->setPlotDirection(DataSeries::DIRECTION_BAR);
    
    $chart3 = new Chart('chart_tiempos', new Title('Promedio de Respuesta por Urgencia (Minutos)'), new Legend(Legend::POSITION_RIGHT, null, false), new PlotArea(null, [$series3]), true, DataSeries::EMPTY_AS_GAP, null, null);
    $chart3->setTopLeftPosition('E2'); 
    $chart3->setBottomRightPosition('M16');
    $hoja3->addChart($chart3);
}

// Activar de nuevo la primera hoja para que abra ordenado
$spreadsheet->setActiveSheetIndex(0);

/* =========================================
   DESCARGAR ARCHIVO
========================================= */
$nombreArchivo = 'Estadisticas_Reportes_' . date('Y-m-d_H-i') . '.xlsx';

if (ob_get_length()) { ob_end_clean(); }

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->setIncludeCharts(true); 
$writer->save('php://output');
exit();