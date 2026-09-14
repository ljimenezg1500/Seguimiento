<?php
include("../../config/db.php");
include("../../includes/permisos.php");
include("../../includes/log_bitacora.php"); // <-- AQUÍ AGREGAMOS EL SENSOR

// Asegurarnos de que la sesión esté iniciada para poder saber quién hace el cambio
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!esAdmin() && !esSupervisor()){
    echo "No autorizado";
    exit();
}

$id = $_POST['id'];
$buscar = $conexion->prepare("SELECT rol FROM usuarios WHERE id=?");
$buscar->bind_param("i", $id);
$buscar->execute();
$usuarioActual = $buscar->get_result()->fetch_assoc();

if(esSupervisor() && $usuarioActual['rol'] == 'admin'){
    echo "No puedes modificar administradores";
    exit();
}

$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$rol = trim($_POST['rol']);

if(!empty($_POST['password'])){
    $password = trim($_POST['password']);
    $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");
    $stmt->bind_param("ssssi", $nombre, $email, $password, $rol, $id);
} else {
    $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
    $stmt->bind_param("sssi", $nombre, $email, $rol, $id);
}

// <-- AQUÍ CAMBIAMOS EL FINAL PARA REGISTRAR LA ACCIÓN -->
if($stmt->execute()){
    // Evaluamos si cambió la contraseña o solo los datos para ser más precisos en la bitácora
    $mensajeLog = empty($_POST['password']) ? "Modificó datos del usuario: $nombre" : "Modificó datos y contraseña del usuario: $nombre";
    
    // Disparamos el sensor
    if(isset($_SESSION['user'])){
        registrarBitacora($conexion, $_SESSION['user'], 'Modificar Usuario', $mensajeLog);
    }
    
    echo "Usuario actualizado";
} else {
    echo "Error al actualizar usuario";
}
?>