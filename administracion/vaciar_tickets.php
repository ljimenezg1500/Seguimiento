<?php
include("../config/db.php");
include("../includes/permisos.php");

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Validación estricta: si no hay sesión o no es admin, cortamos la ejecución de inmediato
if(!isset($_SESSION['user']) || !esAdmin()){
    echo "No tienes autorización para realizar esta acción.";
    exit();
}

// Preparamos y ejecutamos el TRUNCATE
$sql = "TRUNCATE TABLE tickets";

if($conexion->query($sql)){
    echo "Todos los tickets han sido eliminados correctamente y la tabla ha sido reiniciada.";
} else {
    echo "Ocurrió un error al intentar vaciar la tabla: " . $conexion->error;
}
?>