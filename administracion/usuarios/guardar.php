<?php
include("../../config/db.php");
include("../../includes/permisos.php");
include("../../includes/log_bitacora.php");

if(!esAdmin()){
    echo "No autorizado";
    exit();
}

$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$rol = trim($_POST['rol']);

if(empty($nombre) || empty($email) || empty($password)){
    echo "Completa todos los campos";
    exit();
}

$stmt = $conexion->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES(?,?,?,?)");
$stmt->bind_param("ssss", $nombre, $email, $password, $rol);

if($stmt->execute()){
    registrarBitacora($conexion, $_SESSION['user'], 'Crear Usuario', "Creó al usuario: $nombre con el rol: $rol");
    echo "Usuario agregado correctamente";
}else{
    echo "Error al agregar usuario";
}