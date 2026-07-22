<?php

include("../config/db.php");

include("../includes/permisos.php");

/* SOLO ADMIN */

if(!esAdmin()){

    echo "No autorizado";

    exit();

}

/* DATOS */

$nombre =
trim($_POST['nombre']);

$email =
trim($_POST['email']);

$password =
trim($_POST['password']);

$rol =
trim($_POST['rol']);

/* VALIDACIONES */

if(
    empty($nombre) ||
    empty($email) ||
    empty($password)
){

    echo "Completa todos los campos";

    exit();

}

/* HASH */

/* INSERT (Sin Hash) */

$stmt =
$conexion->prepare(

    "INSERT INTO usuarios
    (nombre,email,password,rol)

    VALUES(?,?,?,?)"

);

$stmt->bind_param(

    "ssss",

    $nombre,
    $email,
    $password, 
    $rol

);

if($stmt->execute()){

    echo "Usuario agregado correctamente";

}else{

    echo "Error al agregar usuario";

}