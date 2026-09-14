<?php
include("../config/db.php");
include("../includes/permisos.php");
include("../includes/log_bitacora.php");

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
    /* REGISTRAR EN BITÁCORA */
    registrarBitacora($conexion, $_SESSION['user'], 'Vaciar Base de Datos', 'Ejecutó un TRUNCATE. Borró todos los tickets del sistema de forma permanente.');
    
    echo "Todos los tickets han sido eliminados correctamente y la tabla ha sido reiniciada.";
} else {
    echo "Ocurrió un error al intentar vaciar la tabla: " . $conexion->error;
}
?>