<?php
include("../../config/db.php");
include("../../includes/permisos.php");

/* PROTEGER SESION */
if(!isset($_SESSION['user'])){
    header("Location: ../../auth/login.php");
    exit();
}

/* SOLO ADMIN */
if(!esAdmin()){
    echo "<h1>Acceso Denegado</h1>";
    exit();
}

// Consultamos los últimos 200 movimientos
$sql = "SELECT * FROM bitacora_auditoria ORDER BY fecha_hora DESC LIMIT 200";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora de Auditoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php include("../../includes/sidebar.php"); ?>

<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title" style="color: #f39c12;"><i class="bi bi-journal-text"></i> Bitácora de Auditoría</h1>
            <p class="page-subtitle">Registro histórico de todas las acciones de los usuarios en el sistema</p>
        </div>
    </div>

    <div class="table-card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="180">Fecha y Hora</th>
                        <th width="150">Usuario</th>
                        <th width="200">Acción</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($resultado && $resultado->num_rows > 0){
                        while($row = $resultado->fetch_assoc()){ 
                            $fecha = date("d/m/Y h:i A", strtotime($row['fecha_hora']));
                            
                            // Colores para el tipo de acción
                            $badgeColor = "bg-secondary";
                            if(stripos($row['accion'], 'Importar') !== false) $badgeColor = "bg-success";
                            if(stripos($row['accion'], 'Exportar') !== false) $badgeColor = "bg-primary";
                            if(stripos($row['accion'], 'Modificar') !== false || stripos($row['accion'], 'Contraseña') !== false) $badgeColor = "bg-warning text-dark";
                            if(stripos($row['accion'], 'Eliminar') !== false || stripos($row['accion'], 'Vaciar') !== false) $badgeColor = "bg-danger";
                    ?>
                    <tr>
                        <td class="text-muted small"><i class="bi bi-clock"></i> <?php echo $fecha; ?></td>
                        <td><strong><i class="bi bi-person-circle text-primary"></i> <?php echo htmlspecialchars($row['usuario']); ?></strong></td>
                        <td><span class="badge <?php echo $badgeColor; ?>"><?php echo htmlspecialchars($row['accion']); ?></span></td>
                        <td class="text-muted"><?php echo htmlspecialchars($row['detalles']); ?></td>
                    </tr>
                    <?php 
                        } 
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Aún no hay registros en la bitácora.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>