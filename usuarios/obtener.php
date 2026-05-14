<?php

header('Content-Type: application/json');

include("../config/db.php");

include("../includes/permisos.php");

/* OBTENER USUARIOS */

$sql =
"SELECT * FROM usuarios ORDER BY id DESC";

$resultado =
$conexion->query($sql);

$usuarios = [];

while($fila = $resultado->fetch_assoc()){

    /* SUPERVISOR NO VE BOTONES ADMIN */

    if(
        esSupervisor()
        &&
        $fila['rol'] == 'admin'
    ){

        $fila['editable'] = false;

    }else{

        $fila['editable'] = true;

    }

    $usuarios[] = $fila;

}




echo json_encode($usuarios);