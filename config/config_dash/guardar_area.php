<?php
include("../../config/db.php");
include("../../includes/permisos.php");

if(!esAdmin()){
    echo "No autorizado";
    exit();
}

$nombre = trim($_POST['nombre']);

// Convertimos a mayúsculas para mantener el estándar del sistema
$nombre = strtoupper($nombre); 

if(empty($nombre)){
    echo "El nombre del área no puede estar vacío.";
    exit();
}

// Preparamos la consulta
$stmt = $conexion->prepare("INSERT INTO areas (nombre) VALUES (?)");
$stmt->bind_param("s", $nombre);

if($stmt->execute()){
    echo "Área guardada correctamente.";
} else {
    // Si marca error, probablemente es porque el área ya existe (por nuestra regla UNIQUE en SQL)
    echo "Error: Posiblemente esta área ya existe en el sistema.";
}
?>