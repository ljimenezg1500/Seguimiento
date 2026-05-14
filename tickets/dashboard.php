<?php

require_once("../config/db.php");

/* =========================================
   FILTROS
========================================= */

$mes =
isset($_GET['mes'])
?
$_GET['mes']
:
date('m');

$usuarioFiltro =
isset($_GET['usuario'])
?
$_GET['usuario']
:
'';

/* =========================================
   WHERE
========================================= */

$where = " WHERE 1=1 ";

/* FILTRO MES */

if($mes != 'todos'){

    $where .=
    " AND MONTH(fecha_crea) = '$mes' ";

}

/* FILTRO USUARIO */

if(!empty($usuarioFiltro)){

    $where .=
    " AND usuario = '$usuarioFiltro' ";

}

/* =========================================
   TOTAL
========================================= */

$sqlTotal =

"SELECT COUNT(*) total
FROM tickets
$where";

$resultTotal =
$conexion->query($sqlTotal);

$total =
$resultTotal
?
$resultTotal->fetch_assoc()['total']
:
0;

/* =========================================
   TOP USUARIOS
========================================= */

$sqlUsuarios =

"SELECT usuario,
COUNT(*) total

FROM tickets

$where

AND LOWER(estatus)
LIKE '%cerrado%'

GROUP BY usuario

ORDER BY total DESC

LIMIT 10";

$resultUsuarios =
$conexion->query($sqlUsuarios);

$usuarios = [];
$cantidades = [];

if($resultUsuarios){

    while(
        $fila =
        $resultUsuarios->fetch_assoc()
    ){

        $usuarios[] =
        $fila['usuario'];

        $cantidades[] =
        $fila['total'];

    }

}

/* =========================================
   URGENCIAS
========================================= */

$sqlUrgencias =

"SELECT urgencia,
COUNT(*) total

FROM tickets

$where

GROUP BY urgencia";

$resultUrgencias =
$conexion->query($sqlUrgencias);

$urgencias = [];
$cantidadUrgencias = [];

if($resultUrgencias){

    while(
        $fila =
        $resultUrgencias->fetch_assoc()
    ){

        $urgencias[] =
        $fila['urgencia'];

        $cantidadUrgencias[] =
        $fila['total'];

    }

}

/* =========================================
   USUARIOS FILTRO
========================================= */

$sqlFiltro =

"SELECT DISTINCT usuario
FROM tickets
ORDER BY usuario ASC";

$usuariosFiltro =
$conexion->query($sqlFiltro);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard Tickets</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

<link
rel="stylesheet"
href="../assets/css/style.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="corporate-body">

<?php include("../includes/sidebar.php"); ?>

<div class="content">

    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1 class="page-title">

                Dashboard Tickets

            </h1>

            <p class="page-subtitle">

                Métricas empresariales

            </p>

        </div>

    </div>

    <!-- FILTROS -->

    <div class="corporate-card mb-4">

        <form
        method="GET"
        class="row g-3">

            <!-- MES -->

            <div class="col-md-4">

                <label class="form-label">

                    Mes

                </label>

                <select
name="mes"
class="form-select">

    <option value="todos">

        Todos los meses

    </option>

    <?php

    $meses = [

        "01" => "Enero",
        "02" => "Febrero",
        "03" => "Marzo",
        "04" => "Abril",
        "05" => "Mayo",
        "06" => "Junio",
        "07" => "Julio",
        "08" => "Agosto",
        "09" => "Septiembre",
        "10" => "Octubre",
        "11" => "Noviembre",
        "12" => "Diciembre"

    ];

    foreach($meses as $numero => $nombre){

    ?>

    <option
    value="<?php echo $numero; ?>"

    <?php
    if($mes == $numero)
    echo 'selected';
    ?>>

        <?php echo $nombre; ?>

    </option>

    <?php } ?>

</select>

            </div>

            <!-- USUARIO -->

            <div class="col-md-4">

                <label class="form-label">

                    Usuario

                </label>

                <select
                name="usuario"
                class="form-select">

                    <option value="">

                        Todos los usuarios

                    </option>

                    <?php
                    while(
                        $u =
                        $usuariosFiltro
                        ->fetch_assoc()
                    ){
                    ?>

                    <option
                    value="<?php
                    echo $u['usuario'];
                    ?>">

                        <?php
                        echo $u['usuario'];
                        ?>

                    </option>

                    <?php } ?>

                </select>

            </div>

            <!-- BOTON -->

            <div class="col-md-4 d-flex align-items-end">

                <button
                class="btn-corporate w-100">

                    Aplicar filtros

                </button>

            </div>

        </form>

    </div>

    <!-- TOTAL -->

    <div class="corporate-card mb-5 text-center">

        <h2 class="big-number">

            <?php echo $total; ?>

        </h2>

        <p>

            Tickets encontrados

        </p>

    </div>

    <!-- GRAFICAS -->

    <div class="row g-4">

        <!-- USUARIOS -->

        <div class="col-lg-7">

            <div class="corporate-card">

                <h4 class="mb-4">

                    Top usuarios

                </h4>

                <canvas id="usuariosChart"></canvas>

            </div>

        </div>

        <!-- URGENCIAS -->

        <div class="col-lg-5">

            <div class="corporate-card">

                <h4 class="mb-4">

                    Urgencias

                </h4>

                <canvas id="urgenciasChart"></canvas>

            </div>

        </div>

    </div>

</div>

<script>

/* USUARIOS */

new Chart(

document.getElementById(
'usuariosChart'
),

{

    type:'bar',

    data:{

        labels:
        <?php echo json_encode($usuarios); ?>,

        datasets:[{

            data:
            <?php echo json_encode($cantidades); ?>,

            borderRadius:10

        }]

    }

});

/* URGENCIAS */

new Chart(

document.getElementById(
'urgenciasChart'
),

{

    type:'doughnut',

    data:{

        labels:
        <?php echo json_encode($urgencias); ?>,

        datasets:[{

            data:
            <?php
            echo json_encode(
                $cantidadUrgencias
            );
            ?>

        }]

    }

});

</script>

</body>
</html>