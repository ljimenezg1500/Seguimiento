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

$archivo =
$_FILES['excel'];

/* EXTENSION */

$extension =
pathinfo(
    $archivo['name'],
    PATHINFO_EXTENSION
);

/* VALIDAR */

if(
    $extension != "xlsx"
    &&
    $extension != "xls"
){

    echo "Archivo inválido";

    exit();

}

/* GUARDAR */

$nombreNuevo =
time() .
"_" .
basename($archivo['name']);

$ruta =
"uploads/" .
$nombreNuevo;

move_uploaded_file(
    $archivo['tmp_name'],
    $ruta
);

/* LEER EXCEL */

$documento =
IOFactory::load($ruta);

$hoja =
$documento->getActiveSheet();

$filas =
$hoja->toArray();

/* CONTADOR */

$importados = 0;

/* RECORRER */

foreach($filas as $index => $fila){

    /* SALTAR HEADER */

    if($index == 0){

        continue;

    }

    /* COLUMNAS */

    $ticket_id =
    $fila[0];

    $titulo =
    $fila[1];

    $estatus =
    $fila[2];

    $urgencia =
    $fila[3];

    $usuario =
    $fila[4];

    $fecha_crea =
    !empty($fila[5])
    ?
    date(
        'Y-m-d',
        strtotime($fila[5])
    )
    :
    null;

    $hora_crea =
    $fila[6];

    $fecha_cierre =
    !empty($fila[7])
    ?
    date(
        'Y-m-d',
        strtotime($fila[7])
    )
    :
    null;

    $hora_cierre =
    $fila[8];

    $tiempo_efectivo =
    $fila[9];

    /* INSERT */

    $stmt =
    $conexion->prepare(

        "INSERT INTO tickets(

            ticket_id,
            titulo,
            estatus,
            urgencia,
            usuario,
            fecha_crea,
            hora_crea,
            fecha_cierre,
            hora_cierre,
            tiempo_efectivo

        )

        VALUES(?,?,?,?,?,?,?,?,?,?)"

    );

    $stmt->bind_param(

        "ssssssssss",

        $ticket_id,
        $titulo,
        $estatus,
        $urgencia,
        $usuario,
        $fecha_crea,
        $hora_crea,
        $fecha_cierre,
        $hora_cierre,
        $tiempo_efectivo

    );

    $stmt->execute();

    $importados++;

}

echo
"$importados tickets importados correctamente";