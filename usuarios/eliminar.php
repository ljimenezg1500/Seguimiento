<?php

include("../config/db.php");

include("../includes/permisos.php");

if(!esAdmin()){

    echo "No autorizado";

    exit();

}

$id = $_POST['id'];

$stmt = $conexion->prepare(
    "DELETE FROM usuarios WHERE id=?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

echo "Usuario eliminado";