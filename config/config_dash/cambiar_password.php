<?php
include("../../config/db.php");

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user'])){
    echo "No autorizado";
    exit();
}

$id = $_POST['id'];
$passActual = trim($_POST['passActual']);
$passNueva = trim($_POST['passNueva']);

// 1. Obtener la contraseña actual de la base de datos
$stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// 2. Comparamos la contraseña actual (texto plano, sin hash)
if($passActual === $usuario['password']){
    
    // 3. Si coincide, guardamos la nueva
    $stmtUpdate = $conexion->prepare("UPDATE usuarios SET password=? WHERE id=?");
    $stmtUpdate->bind_param("si", $passNueva, $id);
    
    if($stmtUpdate->execute()){
        echo "Contraseña actualizada correctamente.";
    } else {
        echo "Error al guardar la nueva contraseña.";
    }

} else {
    echo "La contraseña actual es incorrecta.";
}
?>