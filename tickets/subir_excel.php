<?php

require '../vendor/autoload.php';
include("../config/db.php");

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
    $ticket_id = $fila[0];
    $titulo = $fila[1];
    $estatus = $fila[2];
    $urgencia = $fila[3];
    $usuario = $fila[4];
    $nombre_corto = $fila[5];
    $fecha_crea = !empty($fila[6]) ? date('Y-m-d', strtotime($fila[6])) : null;
    $hora_crea = $fila[7];
    $fecha_cierre = !empty($fila[8]) ? date('Y-m-d', strtotime($fila[8])) : null;
    $hora_cierre = $fila[9];
    $tiempo_efectivo = $fila[10];
    $area = isset($fila[11]) ? trim($fila[11]) : 'Sin Asignar';
    /* VALIDAR SI EL TICKET YA EXISTE */
    $checkStmt = $conexion->prepare("SELECT id FROM tickets WHERE ticket_id = ?");
    $checkStmt->bind_param("s", $ticket_id);
    $checkStmt->execute();
    $checkStmt->store_result();

if($checkStmt->num_rows == 0){
        /* NO EXISTE -> INSERT */
        $stmt = $conexion->prepare(
            "INSERT INTO tickets(
                ticket_id, titulo, estatus, urgencia, usuario, nombre_corto,
                fecha_crea, hora_crea, fecha_cierre, hora_cierre, tiempo_efectivo, area
            )
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)"
        );

        $stmt->bind_param(
            "ssssssssssss", // Agregamos una 's' más
            $ticket_id, $titulo, $estatus, $urgencia, $usuario, $nombre_corto,
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

// Retornamos el mensaje para SweetAlert (SIN la llave extra debajo de esto)
echo "$importados tickets importados correctamente. Se omitieron $duplicados tickets duplicados.";

?>