<?php

include("../config/db.php");

include("../includes/permisos.php");

/* SOLO ADMIN O SUPERVISOR */

if(
    !esAdmin()
    &&
    !esSupervisor()
){

    echo "No autorizado";

    exit();

}

/* DATOS */

$id =
$_POST['id'];

/* OBTENER USUARIO */

$buscar =
$conexion->prepare(
    "SELECT rol FROM usuarios WHERE id=?"
);

$buscar->bind_param("i", $id);

$buscar->execute();

$result =
$buscar->get_result();

$usuarioActual =
$result->fetch_assoc();

/* SUPERVISOR NO EDITA ADMINS */

if(
    esSupervisor()
    &&
    $usuarioActual['rol'] == 'admin'
){

    echo "No puedes modificar administradores";

    exit();

}

$nombre =
trim($_POST['nombre']);

$email =
trim($_POST['email']);

$rol =
trim($_POST['rol']);

/* PASSWORD NUEVO */

/* PASSWORD NUEVO */

if(!empty($_POST['password'])){

    $password = trim($_POST['password']);

    $stmt =
    $conexion->prepare(

        "UPDATE usuarios

        SET
        nombre=?,
        email=?,
        password=?,
        rol=?

        WHERE id=?"

    );

    $stmt->bind_param(

        "ssssi",

        $nombre,
        $email,
        $password,
        $rol,
        $id

    );

}else{

    $stmt =
    $conexion->prepare(

        "UPDATE usuarios

        SET
        nombre=?,
        email=?,
        rol=?

        WHERE id=?"

    );

    $stmt->bind_param(

        "sssi",

        $nombre,
        $email,
        $rol,
        $id

    );

}

$stmt->execute();

echo "Usuario actualizado";