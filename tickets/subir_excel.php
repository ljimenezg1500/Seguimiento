<?php

require '../vendor/autoload.php';
include("../config/db.php");

include("../includes/log_bitacora.php");
if(session_status() === PHP_SESSION_NONE){ session_start(); }

use PhpOffice\PhpSpreadsheet\IOFactory;

/* VALIDAR */

if(!isset($_FILES['excel'])){
    echo "No se recibió archivo";
    exit();
}

/* ARCHIVO */

$archivo = $_FILES['excel'];

/* EXTENSION */

$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

/* VALIDAR */

if($extension != "xlsx" && $extension != "xls"){
    echo "Archivo inválido";
    exit();
}

/* GUARDAR */

$nombreNuevo = time() . "_" . basename($archivo['name']);
$ruta = "uploads/" . $nombreNuevo;

move_uploaded_file($archivo['tmp_name'], $ruta);

/* LEER EXCEL */

$documento = IOFactory::load($ruta);
$hoja = $documento->getActiveSheet();
$filas = $hoja->toArray();



/* CONTADORES */

$importados = 0;
$duplicados = 0;

/* RECORRER */

foreach($filas as $index => $fila){

    /* SALTAR HEADER */
    if($index == 0){
        continue;
    }

    /* COLUMNAS */
    $ticket_id = isset($fila[0]) ? trim($fila[0]) : '';
    
    // ---> SOLUCIÓN: Si el ID del ticket está vacío, saltamos esta "fila fantasma"
    if($ticket_id === ''){
        continue;
    }

    $titulo = $fila[1];
    $estatus = $fila[2];
    $urgencia = $fila[3];
    $usuario = $fila[4];
    
    $fecha_crea = !empty($fila[5]) ? date('Y-m-d', strtotime($fila[5])) : null;
    $hora_crea = $fila[6];
    $fecha_cierre = !empty($fila[7]) ? date('Y-m-d', strtotime($fila[7])) : null;
    $hora_cierre = $fila[8];
    $tiempo_efectivo = $fila[9];
    $area = isset($fila[10]) ? trim($fila[10]) : 'Sin Asignar';
    /* VALIDAR SI EL TICKET YA EXISTE */
    $checkStmt = $conexion->prepare("SELECT id FROM tickets WHERE ticket_id = ?");
    $checkStmt->bind_param("s", $ticket_id);
    $checkStmt->execute();
    $checkStmt->store_result();

if($checkStmt->num_rows == 0){
        /* NO EXISTE -> INSERT */
        $stmt = $conexion->prepare(
            "INSERT INTO tickets(
                ticket_id, titulo, estatus, urgencia, usuario,
                fecha_crea, hora_crea, fecha_cierre, hora_cierre, tiempo_efectivo, area
            )
            VALUES(?,?,?,?,?,?,?,?,?,?,?)"
        );

        $stmt->bind_param(
            "sssssssssss", 
            $ticket_id, $titulo, $estatus, $urgencia, $usuario,
            $fecha_crea, $hora_crea, $fecha_cierre, $hora_cierre, $tiempo_efectivo, $area
        );
        

        $stmt->execute();
        $stmt->close();
        
        $importados++;
    } else {
        /* YA EXISTE -> OMITIR */
        $duplicados++;
    }
    
    $checkStmt->close();
}

/* REGISTRAR EN BITÁCORA */
if(isset($_SESSION['user']) && $importados > 0){
    $detalles = "Importó $importados tickets. Omitió $duplicados duplicados.";
    registrarBitacora($conexion, $_SESSION['user'], 'Importar Tickets', $detalles);
}

// Retornamos el mensaje para SweetAlert (SIN la llave extra debajo de esto)
echo "$importados tickets importados correctamente. Se omitieron $duplicados tickets duplicados.";

?>