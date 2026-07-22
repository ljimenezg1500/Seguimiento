<?php
include("../config/db.php");
/* PROTEGER SESION */

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* TOTAL USUARIOS */

$sqlUsuarios =
"SELECT COUNT(*) as total FROM usuarios";

$resultUsuarios =
$conexion->query($sqlUsuarios);

$totalUsuarios =
$resultUsuarios->fetch_assoc()['total'];

/* FECHA ACTUAL */

$fecha = date("d/m/Y H:i:s");

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<!-- BOOTSTRAP -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- ICONOS -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

<!-- CSS -->
<link
rel="stylesheet"
href="../assets/css/style.css">
</head>

<body>
<!-- SIDEBAR -->
<?php include("../includes/sidebar.php"); ?>
<!-- CONTENIDO -->
<div class="content">
    <!-- HEADER -->
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                Reportes de Tickets
            </h1>
            <p class="dashboard-subtitle">
                Bienvenido 
                <strong>
                    <?php
                    echo $_SESSION['user'];
                    ?>
                </strong>
            </p>
        </div>
    </div>

    <!-- TARJETAS -->
    <div class="row g-4">
        <!-- USUARIOS -->
        <div class="col-md-4">
            <div class="dashboard-card users-card">
                <div>
                    <h5>
                        Usuarios
                    </h5>
                    <h2>
                        <?php
                        echo $totalUsuarios;
                        ?>
                    </h2>

                </div>
                <i class="bi bi-people-fill"></i>

            </div>

        </div>

        <!-- FECHA -->

        <div class="col-md-4">

            <div class="dashboard-card date-card">

                <div>

                    <h5>

                        Fecha

                    </h5>

                    <h2 id="fecha"></h2>

                </div>

                <i class="bi bi-calendar-event-fill"></i>

            </div>

        </div>

        <!-- HORA -->

        <div class="col-md-4">

            <div class="dashboard-card time-card">

                <div>

                    <h5>

                        Hora

                    </h5>

                   <h2 id="hora"></h2>

                </div>

                <i class="bi bi-clock-fill"></i>

            </div>

        </div>

    </div>

    <!-- ACTIVIDAD -->

    <div class="corporate-card">
    <h4 class="mb-4">Actividad Reciente</h4>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Fecha y Hora</th>
                    <th>Estatus del Perfil</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consultamos los últimos 5 inicios de sesión uniéndolo con el estatus del usuario
                $sqlActividad = "SELECT h.nombre_usuario, h.fecha_hora, u.estatus 
                                 FROM historial_accesos h 
                                 LEFT JOIN usuarios u ON h.nombre_usuario = u.nombre 
                                 ORDER BY h.fecha_hora DESC LIMIT 5";
                
                $resultActividad = $conexion->query($sqlActividad);

                if($resultActividad && $resultActividad->num_rows > 0){
                    while($row = $resultActividad->fetch_assoc()){
                        
                        // Formateamos la fecha para que se vea bonita
                        $fecha = date("d/m/Y h:i A", strtotime($row['fecha_hora']));
                        
                        // Asignamos el color del badge dependiendo del estatus
                        $estatus = $row['estatus'] ?? 'Desconocido';
                        $badgeClass = "bg-secondary"; // Por defecto
                        
                        if($estatus == 'Activo'){
                            $badgeClass = "bg-success";
                        } elseif($estatus == 'Suspendido'){
                            $badgeClass = "bg-warning text-dark";
                        } elseif($estatus == 'Inactivo'){
                            $badgeClass = "bg-danger";
                        }
                ?>
                <tr>
                    <td>
                        <i class="bi bi-person-circle text-primary me-2"></i>
                        <strong><?php echo $row['nombre_usuario']; ?></strong>
                    </td>
                    <td class="text-muted small"><?php echo $fecha; ?></td>
                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $estatus; ?></span></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='3' class='text-center text-muted'>No hay actividad reciente registrada.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<script>

function actualizarFechaHora() {

    const ahora = new Date();

    // FECHA

    const fecha = ahora.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });

    // HORA

    const hora = ahora.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    document.getElementById("fecha").innerHTML = fecha;

    document.getElementById("hora").innerHTML = hora;
}

// ACTUALIZA AL INSTANTE

actualizarFechaHora();

// ACTUALIZA CADA SEGUNDO

setInterval(actualizarFechaHora, 1000);

</script>
</body>
</html>