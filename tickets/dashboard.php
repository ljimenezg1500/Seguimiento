<?php

require_once("../config/db.php");

/* =========================================
   FILTROS
========================================= */

// 1. Atrapamos los filtros de la URL (si no hay, ponemos un valor por defecto)
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$usuarioFiltro = isset($_GET['usuario']) ? $_GET['usuario'] : '';
$areaFiltro = isset($_GET['area']) ? $_GET['area'] : 'todas'; // <-- NUEVO: Atrapamos el área

/* =========================================
   WHERE
========================================= */

// 2. Armamos las reglas de búsqueda (WHERE)
$where = " WHERE 1=1 ";

if($mes != 'todos'){
    $where .= " AND MONTH(fecha_crea) = '$mes' ";
}
if(!empty($usuarioFiltro)){
    $where .= " AND usuario = '$usuarioFiltro' ";
}
if($areaFiltro != 'todas'){
    // <-- NUEVO: Si no eligió "todas", filtramos por el área específica en la tabla de tickets
    $where .= " AND area = '$areaFiltro' "; 
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
   TIEMPO EFECTIVO POR ÁREA
========================================= */

// Convertimos HH:MM a minutos para poder graficarlo
$sqlTiempos = "
SELECT 
    area, 
    SUM(TIME_TO_SEC(STR_TO_DATE(tiempo_efectivo, '%H:%i'))) / 60 as total_minutos,
    AVG(TIME_TO_SEC(STR_TO_DATE(tiempo_efectivo, '%H:%i'))) / 60 as promedio_minutos
FROM tickets
$where
AND tiempo_efectivo IS NOT NULL 
AND tiempo_efectivo != ''
GROUP BY area
ORDER BY total_minutos DESC
";

$resultTiempos = $conexion->query($sqlTiempos);

$areasTiempo = [];
$totalMinutosArea = [];
$promedioMinutosArea = [];

if($resultTiempos){
    while($fila = $resultTiempos->fetch_assoc()){
        // Si el área viene vacía, le ponemos un nombre por defecto
        $areasTiempo[] = empty($fila['area']) ? 'Sin Área' : $fila['area'];
        
        // Redondeamos los minutos a números enteros
        $totalMinutosArea[] = round($fila['total_minutos']);
        $promedioMinutosArea[] = round($fila['promedio_minutos']);
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

                Estadisticas

            </h1>

            <p class="page-subtitle">

                Filtros

            </p>

        </div>

    </div>

    <!-- FILTROS -->

    <div class="corporate-card mb-4">

        <form method="GET" class="row g-3">

            <!-- MES -->

            <div class="col-md-3">
        <label class="form-label">Mes</label>
        <select name="mes" class="form-select">

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

            <div class="col-md-3">
                <label class="form-label">Usuario</label>
                <select name="usuario" class="form-select">

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

            <div class="col-md-3">
        <label class="form-label">Área</label>
        <select name="area" class="form-select">
            <option value="todas" <?php if($areaFiltro == 'todas') echo 'selected'; ?>>Todas las áreas</option>
            <option value="KOF" <?php if($areaFiltro == 'KOF') echo 'selected'; ?>>KOF</option>
            <option value="KFC" <?php if($areaFiltro == 'KFC') echo 'selected'; ?>>KFC</option>
            <option value="REVENUE" <?php if($areaFiltro == 'REVENUE') echo 'selected'; ?>>REVENUE</option>
            <option value="ADMIN Y FINANZAS" <?php if($areaFiltro == 'ADMIN Y FINANZAS') echo 'selected'; ?>>ADMIN Y FINANZAS</option>
            <option value="GOBIERNO" <?php if($areaFiltro == 'GOBIERNO') echo 'selected'; ?>>GOBIERNO</option>
            <option value="DIGITAL" <?php if($areaFiltro == 'DIGITAL') echo 'selected'; ?>>DIGITAL</option>
            <option value="IP" <?php if($areaFiltro == 'IP') echo 'selected'; ?>>IP</option>
            <option value="EJECUCION IMPECABLE" <?php if($areaFiltro == 'EJECUCION IMPECABLE') echo 'selected'; ?>>EJECUCUCION IMPECABLE</option>
            <option value="INNOVACION" <?php if($areaFiltro == 'INNOVACION') echo 'selected'; ?>>INNOVACION</option>
            <option value="SISTEMAS" <?php if($areaFiltro == 'SISTEMAS') echo 'selected'; ?>>SISTEMAS</option>
            </select>
    </div>

            <!-- BOTON -->

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn-corporate w-50">
                    <i class="bi bi-funnel-fill"></i> Filtrar
                </button>
                
                <button type="submit" formaction="exportar_excel.php" class="btn-corporate w-50 text-center" style="background: #2ed573; color: white;">
                    <i class="bi bi-file-earmark-excel-fill"></i> Exportar
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

    <div class="row g-4 mt-2">
        
        <div class="col-lg-6">
            <div class="corporate-card">
                <h4 class="mb-4">Tiempo Total Invertido (Minutos)</h4>
                <canvas id="tiempoTotalChart"></canvas>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="corporate-card">
                <h4 class="mb-4">Tiempo Promedio por Ticket (Min)</h4>
                <canvas id="tiempoPromedioChart"></canvas>
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

/* TIEMPO TOTAL INVERTIDO (DOUGHNUT) */
new Chart(
    document.getElementById('tiempoTotalChart'),
    {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($areasTiempo); ?>,
            datasets: [{
                data: <?php echo json_encode($totalMinutosArea); ?>,
                backgroundColor: [
                    '#3742fa', '#2ed573', '#ff4757', '#ffa502', '#5352ed', 
                    '#ff7f50', '#2f3542', '#1e90ff', '#eccc68'
                ]
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' min';
                        }
                    }
                }
            }
        }
    }
);

/* TIEMPO PROMEDIO POR ÁREA (BARRA HORIZONTAL) */
new Chart(
    document.getElementById('tiempoPromedioChart'),
    {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($areasTiempo); ?>,
            datasets: [{
                label: 'Minutos Promedio',
                data: <?php echo json_encode($promedioMinutosArea); ?>,
                backgroundColor: '#1e90ff',
                borderRadius: 5
            }]
        },
        options: {
            indexAxis: 'y', // Esto hace que la gráfica de barras sea horizontal
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' min prom/ticket';
                        }
                    }
                }
            }
        }
    }
);

</script>

</body>
</html>