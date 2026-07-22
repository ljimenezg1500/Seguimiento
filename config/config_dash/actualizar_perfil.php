<?php
include("../../config/db.php");

// Verificamos si la sesión ya está iniciada
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user'])){
    echo "No autorizado";
    exit();
}

$id = $_POST['id'];
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);

if(empty($nombre) || empty($email)){
    echo "Los campos no pueden estar vacíos.";
    exit();
}

// Preparamos la actualización
$stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, email=? WHERE id=?");
$stmt->bind_param("ssi", $nombre, $email, $id);

if($stmt->execute()){
    // ¡Truco importante! Actualizamos la variable de sesión con el nuevo nombre
    $_SESSION['user'] = $nombre;
    echo "Perfil actualizado correctamente.";
} else {
    echo "Error al actualizar el perfil.";
}
?>