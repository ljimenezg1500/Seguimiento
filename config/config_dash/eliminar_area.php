<?php
include("../../config/db.php");
include("../../includes/permisos.php");

if(!esAdmin()){
    echo "No autorizado";
    exit();
}

$id = $_POST['id'];

$stmt = $conexion->prepare("DELETE FROM areas WHERE id=?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
    echo "Área eliminada correctamente del catálogo.";
} else {
    echo "Error al intentar eliminar el área.";
}
?>