<?php
// Esta función atrapa la acción y la guarda directamente en la base de datos
function registrarBitacora($conexion, $usuario, $accion, $detalles = '') {
    $sql = "INSERT INTO bitacora_auditoria (usuario, accion, detalles) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    if($stmt){
        $stmt->bind_param("sss", $usuario, $accion, $detalles);
        $stmt->execute();
        $stmt->close();
    }
}
?>