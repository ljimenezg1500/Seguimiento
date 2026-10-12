<?php
require_once("../config/db.php");
require_once("../includes/permisos.php");

// Proteger sesión
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* =========================================
   1. LLENAR EL SELECTOR DE RESOLUTORES
========================================= */
$sqlResolutores = "SELECT DISTINCT usuario FROM tickets WHERE usuario IS NOT NULL AND usuario != '' ORDER BY usuario ASC";
$resultResolutores = $conexion->query($sqlResolutores);

/* =========================================
   2. LÓGICA DEL MOTOR DE BÚSQUEDA
========================================= */
$resultados = null;
$mensajeBusqueda = "Utiliza los filtros de la parte superior para buscar tickets.";

if (isset($_GET['ticket_id']) && !empty($_GET['ticket_id'])) {
    $idBusqueda = $conexion->real_escape_string(trim($_GET['ticket_id']));
    $sql = "SELECT * FROM tickets WHERE ticket_id = '$idBusqueda' ORDER BY id DESC";
    $resultados = $conexion->query($sql);
    $mensajeBusqueda = "Resultados para el Ticket ID: <strong>$idBusqueda</strong>";

} elseif (isset($_GET['palabra']) && !empty($_GET['palabra'])) {
    $palabra = $conexion->real_escape_string(trim($_GET['palabra']));
    // Busca la palabra en el título o en el área
    $sql = "SELECT * FROM tickets WHERE titulo LIKE '%$palabra%' OR area LIKE '%$palabra%' ORDER BY id DESC";
    $resultados = $conexion->query($sql);
    $mensajeBusqueda = "Resultados para la palabra clave: <strong>$palabra</strong>";

} elseif (isset($_GET['resolutor']) && !empty($_GET['resolutor'])) {
    $resolutor = $conexion->real_escape_string(trim($_GET['resolutor']));
    $sql = "SELECT * FROM tickets WHERE usuario = '$resolutor' ORDER BY fecha_cierre DESC, id DESC";
    $resultados = $conexion->query($sql);
    $mensajeBusqueda = "Tickets levantado por: <strong>$resolutor</strong>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="corporate-body">

<?php include("../includes/sidebar.php"); ?>

<div class="content">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="bi bi-search text-primary"></i> Detalles y Consultas</h1>
            <p class="page-subtitle">Motor de búsqueda avanzada para tickets</p>
        </div>
        <?php if($resultados): ?>
        <div>
            <a href="detalles.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Limpiar Búsqueda</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- PANEL DE BÚSQUEDA (3 HERRAMIENTAS) -->
    <div class="row g-4 mb-4">
        
        <!-- 1. BUSCAR POR ID -->
        <div class="col-md-4">
            <div class="corporate-card h-100 shadow-sm border-top border-primary border-3">
                <h5 class="mb-3 text-primary"><i class="bi bi-hash"></i> Número de Ticket</h5>
                <form method="GET" action="detalles.php">
                    <div class="input-group">
                        <input type="text" name="ticket_id" class="form-control" placeholder="Ej. 1334" required>
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. BUSCAR POR PALABRA CLAVE -->
        <div class="col-md-4">
            <div class="corporate-card h-100 shadow-sm border-top border-success border-3">
                <h5 class="mb-3 text-success"><i class="bi bi-body-text"></i> Palabra Clave</h5>
                <form method="GET" action="detalles.php">
                    <div class="input-group">
                        <input type="text" name="palabra" class="form-control" placeholder="Ej. Error, Adobe, Servidor..." required>
                        <button class="btn btn-success" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 3. BUSCAR POR RESOLUTOR -->
        <div class="col-md-4">
            <div class="corporate-card h-100 shadow-sm border-top border-warning border-3">
                <h5 class="mb-3 text-warning"><i class="bi bi-person-badge"></i> Usuario </h5>
                <form method="GET" action="detalles.php">
                    <div class="input-group">
                        <select name="resolutor" class="form-select" required>
                            <option value="" selected disabled>Selecciona una persona...</option>
                            <?php 
                            if($resultResolutores){
                                while($rowUser = $resultResolutores->fetch_assoc()){
                                    echo "<option value='{$rowUser['usuario']}'>{$rowUser['usuario']}</option>";
                                }
                            }
                            ?>
                        </select>
                        <button class="btn btn-warning text-dark fw-bold" type="submit">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- ÁREA DE RESULTADOS -->
    <div class="corporate-card shadow-sm">
        <h4 class="mb-3">Resultados de Búsqueda</h4>
        <p class="text-muted mb-4"><?php echo $mensajeBusqueda; ?></p>
        
        <?php if($resultados): ?>
            <?php if($resultados->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket ID</th>
                                <th>Título del Ticket</th>
                                <th>Área</th>
                                <th>Usuario</th>
                                <th>Fecha Cierre</th>
                                <th>Tiempo Invertido</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($ticket = $resultados->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($ticket['ticket_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($ticket['area']); ?></span></td>
                                <td><i class="bi bi-person-circle text-primary"></i> <?php echo htmlspecialchars($ticket['usuario']); ?></td>
                                <td>
                                    <?php 
                                    if(!empty($ticket['fecha_cierre']) && $ticket['fecha_cierre'] != '0000-00-00'){
                                        echo date('d/m/Y', strtotime($ticket['fecha_cierre']));
                                    } else {
                                        echo "<span class='text-muted'>Sin cierre</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $tiempo = $ticket['tiempo_efectivo'];
                                    if(!empty($tiempo) && $tiempo != '00:00'){
                                        // Extraer horas y minutos de HH:MM
                                        list($h, $m) = explode(':', $tiempo);
                                        $minutosTotales = ($h * 60) + $m;
                                        
                                        if($minutosTotales >= 60){
                                            $horas = floor($minutosTotales / 60);
                                            $minRest = $minutosTotales % 60;
                                            echo "<span class='fw-bold text-danger'>" . $horas . ":" . str_pad($minRest, 2, "0", STR_PAD_LEFT) . " hrs</span>";
                                        } else {
                                            echo "<span class='fw-bold text-success'>" . $minutosTotales . " min</span>";
                                        }
                                    } else {
                                        echo "<span class='text-muted'>N/A</span>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2"></i>
                    No se encontraron tickets que coincidan con tu búsqueda.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>