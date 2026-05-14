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

                Bienvenido nuevamente
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

    <div class="activity-box mt-5">

        <h3 class="mb-4">

            Actividad reciente

        </h3>

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>Evento</th>
                        
                    <th>Fecha</th>

                    <th>Estado</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td>

                        Inicio de sesión

                    </td>

                    <td>

                        <?php
                        echo $fecha;
                        ?>

                    </td>

                    <td>

                        <span class="badge bg-success">

                            Correcto

                        </span>

                    </td>

                </tr>

                <tr>

                    <td>

                        Dashboard cargado

                    </td>

                    <td>

                        <?php
                        echo $fecha;
                        ?>

                    </td>

                    <td>

                        <span class="badge bg-primary">

                            Activo

                        </span>

                    </td>

                </tr>


                

            </tbody>

        </table>

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