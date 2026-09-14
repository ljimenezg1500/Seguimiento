<?php
include("../../config/db.php");
include("../../includes/permisos.php");
include("../../includes/log_bitacora.php"); // <-- AQUÍ AGREGAMOS EL SENSOR

// Asegurarnos de que la sesión esté iniciada
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!esAdmin()){
    echo "No autorizado";
    exit();
}

$id = $_POST['id'];

// 1. Buscamos el nombre del usuario ANTES de borrarlo para que la bitácora sea más clara
$buscar = $conexion->prepare("SELECT nombre FROM usuarios WHERE id=?");
$buscar->bind_param("i", $id);
$buscar->execute();
$resultado = $buscar->get_result();
$nombreUsuarioEliminado = "ID " . $id; // Valor por defecto por si algo falla
if($resultado->num_rows > 0){
    $fila = $resultado->fetch_assoc();
    $nombreUsuarioEliminado = $fila['nombre'];
}
$buscar->close();

// 2. Preparamos el borrado
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id=?");
$stmt->bind_param("i", $id);

// 3. Ejecutamos y registramos en la bitácora
if($stmt->execute()){
    
    // Disparamos el sensor
    if(isset($_SESSION['user'])){
        registrarBitacora($conexion, $_SESSION['user'], 'Eliminar Usuario', "Eliminó definitivamente al usuario: $nombreUsuarioEliminado");
    }
    
    echo "Usuario eliminado";
} else {
    echo "Error al eliminar usuario";
}
?>