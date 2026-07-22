<?php

require_once("../config/db.php");

/* =========================================
   FILTROS (AHORA ACEPTAN MÚLTIPLES VALORES)
========================================= */

// 1. Atrapamos los filtros de la URL como arreglos (arrays)
$mesesFiltro = isset($_GET['mes']) ? $_GET['mes'] : ['todos'];
$areasFiltro = isset($_GET['area']) ? $_GET['area'] : ['todas'];

// Asegurarnos de que siempre sean arreglos por seguridad
if (!is_array($mesesFiltro)) $mesesFiltro = [$mesesFiltro];
if (!is_array($areasFiltro)) $areasFiltro = [$areasFiltro];

/* =========================================
   WHERE
========================================= */

// 2. Armamos las reglas de búsqueda (WHERE) dinámicas
$where = " WHERE 1=1 ";

// Filtro de múltiples Meses
if(!in_array('todos', $mesesFiltro) && count($mesesFiltro) > 0){
    // Convertimos a enteros para seguridad
    $mesesEscapados = array_map(function($m) {
        return (int)$m;
    }, $mesesFiltro);
    
    $where .= " AND MONTH(fecha_crea) IN (" . implode(',', $mesesEscapados) . ") ";
}

// Filtro de múltiples Áreas
if(!in_array('todas', $areasFiltro) && count($areasFiltro) > 0){
    // Escapamos los textos para evitar inyección SQL
    $areasEscapadas = array_map(function($a) use ($conexion) {
        return "'" . $conexion->real_escape_string($a) . "'";
    }, $areasFiltro);
    
    $where .= " AND area IN (" . implode(',', $areasEscapadas) . ") ";
}

/* =========================================
   TOTAL
========================================= */

$sqlTotal =
"SELECT COUNT(*) total
FROM tickets
$where";

$resultTotal = $conexion->query($sqlTotal);
$total = $resultTotal ? $resultTotal->fetch_assoc()['total'] : 0;

/* =========================================
   TOP USUARIOS
========================================= */

$sqlUsuarios =
"SELECT usuario,
COUNT(*) total
FROM tickets
$where
AND LOWER(estatus) LIKE '%cerrado%'
GROUP BY usuario
ORDER BY total DESC
LIMIT 10";

$resultUsuarios = $conexion->query($sqlUsuarios);
$usuarios = [];
$cantidades = [];

if($resultUsuarios){
    while($fila = $resultUsuarios->fetch_assoc()){
        $usuarios[] = $fila['usuario'];
        $cantidades[] = $fila['total'];
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

$resultUrgencias = $conexion->query($sqlUrgencias);
$urgencias = [];
$cantidadUrgencias = [];

if($resultUrgencias){
    while($fila = $resultUrgencias->fetch_assoc()){
        $urgencias[] = $fila['urgencia'];
        $cantidadUrgencias[] = $fila['total'];
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
        $areasTiempo[] = empty($fila['area']) ? 'Sin Área' : $fila['area'];
        $totalMinutosArea[] = round($fila['total_minutos']);
        $promedioMinutosArea[] = round($fila['promedio_minutos']);
    }
}

/* =========================================
   PROMEDIO GLOBAL DE RESPUESTA
========================================= */
$sqlPromedioGlobal = "
SELECT AVG(TIME_TO_SEC(STR_TO_DATE(tiempo_efectivo, '%H:%i'))) / 60 as promedio
FROM tickets
$where
AND tiempo_efectivo IS NOT NULL 
AND tiempo_efectivo != ''";

$resultPromedioGlobal = $conexion->query($sqlPromedioGlobal);
$promedioGlobal = $resultPromedioGlobal ? round($resultPromedioGlobal->fetch_assoc()['promedio']) : 0;

/* =========================================
   TICKETS POR ÁREA
========================================= */
$sqlTicketsArea = "
SELECT area, COUNT(*) as total
FROM tickets
$where
GROUP BY area
ORDER BY total DESC";

$resultTicketsArea = $conexion->query($sqlTicketsArea);
$nombresAreas = [];
$cantidadesAreas = [];

if($resultTicketsArea){
    while($fila = $resultTicketsArea->fetch_assoc()){
        $nombresAreas[] = empty($fila['area']) ? 'Sin Área' : $fila['area'];
        $cantidadesAreas[] = $fila['total'];
    }
}

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
            <h1 class="page-title">Estadisticas</h1>
            <p class="page-subtitle">Filtros</p>
        </div>
    </div>

    <!-- FILTROS -->

    <div class="corporate-card mb-4">

        <form method="GET" class="row g-3">

            <!-- MESES CON CHECKBOXES -->
            <div class="col-md-5">
                <label class="form-label text-muted small">Meses</label>
                <div class="border rounded p-2 bg-white shadow-sm" style="max-height: 160px; overflow-y: auto;">
                    
                    <!-- Opción Todos -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="mes[]" value="todos" id="mes_todos" <?php echo in_array('todos', $mesesFiltro) ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="mes_todos">Todos los meses</label>
                    </div>
                    <hr class="my-1">
                    
                    <?php
                    $mesesLista = [
                        "01" => "Enero", "02" => "Febrero", "03" => "Marzo",
                        "04" => "Abril", "05" => "Mayo", "06" => "Junio",
                        "07" => "Julio", "08" => "Agosto", "09" => "Septiembre",
                        "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre"
                    ];
                    foreach($mesesLista as $numero => $nombre){
                        $checked = (in_array($numero, $mesesFiltro) || in_array((int)$numero, $mesesFiltro)) ? 'checked' : '';
                    ?>
                    <div class="form-check">
                        <input class="form-check-input check-mes" type="checkbox" name="mes[]" value="<?php echo $numero; ?>" id="mes_<?php echo $numero; ?>" <?php echo $checked; ?>>
                        <label class="form-check-label" for="mes_<?php echo $numero; ?>"><?php echo $nombre; ?></label>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- ÁREAS CON CHECKBOXES -->
            <div class="col-md-5">
                <label class="form-label text-muted small">Áreas</label>
                <div class="border rounded p-2 bg-white shadow-sm" style="max-height: 160px; overflow-y: auto;">
                    
                    <!-- Opción Todas -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="area[]" value="todas" id="area_todas" <?php echo in_array('todas', $areasFiltro) ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="area_todas">Todas las áreas</label>
                    </div>
                    <hr class="my-1">

                    <?php
                    // Consultamos las áreas desde la base de datos
                    $sqlAreasCat = "SELECT nombre FROM areas ORDER BY nombre ASC";
                    $resultAreasCat = $conexion->query($sqlAreasCat);

                    if($resultAreasCat){
                        $i = 0;
                        while($areaCat = $resultAreasCat->fetch_assoc()){
                            $nombreArea = $areaCat['nombre'];
                            $checked = (in_array($nombreArea, $areasFiltro)) ? 'checked' : '';
                            $i++;
                            echo "
                            <div class='form-check'>
                                <input class='form-check-input check-area' type='checkbox' name='area[]' value='{$nombreArea}' id='area_{$i}' {$checked}>
                                <label class='form-check-label' for='area_{$i}'>{$nombreArea}</label>
                            </div>";
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="col-md-2 d-flex flex-column justify-content-end gap-2">
                <button type="submit" class="btn-corporate w-100" style="height: 40px;">
                    <i class="bi bi-funnel-fill"></i> Filtrar
                </button>
                
                <a href="exportar_excel.php?<?php echo http_build_query(['mes' => $mesesFiltro, 'area' => $areasFiltro]); ?>" class="btn-corporate w-100 text-center text-decoration-none d-flex align-items-center justify-content-center" style="background: #2ed573; height: 40px;">
                    <i class="bi bi-file-earmark-excel-fill me-2"></i> Exportar
                </a>
            </div>

        </form>
    </div>

    <!-- INDICADORES GLOBALES (KPIs) -->
    <div class="row g-4 mb-4 text-center">
        <!-- Tarjeta de Tickets Encontrados -->
        <div class="col-md-6">
            <div class="corporate-card h-100">
                <h2 class="big-number text-dark"><?php echo $total; ?></h2>
                <p class="m-0">Tickets encontrados</p>
            </div>
        </div>
        
        <!-- Tarjeta de Promedio de Respuesta -->
        <div class="col-md-6">
            <div class="corporate-card h-100">
                <?php 
                $colorClase = "";
                if($promedioGlobal < 90){
                    $colorClase = "text-success"; // Verde
                } elseif($promedioGlobal < 120){
                    $colorClase = "text-warning"; // Amarillo
                } else {
                    $colorClase = "text-danger";  // Rojo
                }
                ?>
                <h2 class="big-number <?php echo $colorClase; ?>">
                    <?php echo $promedioGlobal; ?> 
                </h2>
                <p class="m-0">Promedio global de respuesta</p>
            </div>
        </div>
    </div>

    <!-- NUEVA GRÁFICA: TICKETS POR ÁREA -->
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="corporate-card">
                <h4 class="mb-4">Áreas con más tickets</h4>
                <canvas id="ticketsAreaChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>

    <!-- GRAFICAS 2 COLUMNAS -->
    <div class="row g-4">
        <!-- USUARIOS -->
        <div class="col-lg-7">
            <div class="corporate-card">
                <h4 class="mb-4">Top usuarios</h4>
                <canvas id="usuariosChart"></canvas>
            </div>
        </div>

        <!-- URGENCIAS -->
        <div class="col-lg-5">
            <div class="corporate-card">
                <h4 class="mb-4">Urgencias</h4>
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
/* ========================================
   GRÁFICA 1: TOP USUARIOS (CON DEGRADADO)
======================================== */
const ctxUsuarios = document.getElementById('usuariosChart').getContext('2d');
const gradientUsuarios = ctxUsuarios.createLinearGradient(0, 0, 0, 400);
gradientUsuarios.addColorStop(0, '#3498db'); 
gradientUsuarios.addColorStop(1, '#2c3e50'); 

new Chart(ctxUsuarios, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($usuarios); ?>,
        datasets: [{
            label: 'Total de Tickets',
            data: <?php echo json_encode($cantidades); ?>,
            backgroundColor: gradientUsuarios, 
            borderRadius: 6, 
            borderWidth: 0
        }]
    },
    options: {
        plugins: {
            legend: { display: false } 
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

/* ========================================
   GRÁFICA: URGENCIAS (DOUGHNUT)
======================================== */
new Chart(document.getElementById('urgenciasChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($urgencias); ?>,
        datasets: [{
            data: <?php echo json_encode($cantidadUrgencias); ?>,
            backgroundColor: [
                '#7b241c', 
                '#958a29', 
                '#1e8449', 
                '#7f8c8d', 
                '#2c3e50'  
            ],
            borderWidth: 0, 
            hoverOffset: 6
        }]
    },
    options: {
        cutout: '75%', 
        plugins: {
            legend: { 
                position: 'bottom'
            }
        }
    }
});

/* ========================================
   GRÁFICA: TIEMPO TOTAL INVERTIDO
======================================== */
new Chart(document.getElementById('tiempoTotalChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($areasTiempo); ?>,
        datasets: [{
            data: <?php echo json_encode($totalMinutosArea); ?>,
            backgroundColor: [
                '#2c3e50', '#2980b9', '#3498db', '#1abc9c', '#16a085', '#7f8c8d', '#bdc3c7', '#34495e', '#95a5a6'  
            ],
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        cutout: '75%',
        plugins: {
            legend: { 
                position: 'right' 
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.raw + ' min';
                    }
                }
            }
        }
    }
});

/* ========================================
   GRÁFICA: TIEMPO PROMEDIO (HORIZONTAL)
======================================== */
const ctxPromedio = document.getElementById('tiempoPromedioChart').getContext('2d');
const gradientPromedio = ctxPromedio.createLinearGradient(0, 0, 400, 0); 
gradientPromedio.addColorStop(0, '#3498db');
gradientPromedio.addColorStop(1, '#2c3e50');

new Chart(ctxPromedio, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($areasTiempo); ?>,
        datasets: [{
            label: 'Minutos Promedio',
            data: <?php echo json_encode($promedioMinutosArea); ?>,
            backgroundColor: gradientPromedio,
            borderRadius: 6,
            borderWidth: 0
        }]
    },
    options: {
        indexAxis: 'y', 
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
});

/* ========================================
   GRÁFICA: TICKETS POR ÁREA (CON DEGRADADO)
======================================== */
const ctxAreas = document.getElementById('ticketsAreaChart').getContext('2d');
const gradientAreas = ctxAreas.createLinearGradient(0, 0, 0, 400);
gradientAreas.addColorStop(0, '#00b4db'); 
gradientAreas.addColorStop(1, '#0083b0'); 

new Chart(ctxAreas, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($nombresAreas); ?>,
        datasets: [{
            label: 'Total de Tickets',
            data: <?php echo json_encode($cantidadesAreas); ?>,
            backgroundColor: gradientAreas, 
            borderRadius: 6,
            borderWidth: 0
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
/* ========================================
   LÓGICA INTELIGENTE PARA LOS CHECKBOXES
======================================== */
document.addEventListener("DOMContentLoaded", function() {
    
    // Lógica para MESES
    const checkMesTodos = document.getElementById('mes_todos');
    const checksMeses = document.querySelectorAll('.check-mes');
    
    checkMesTodos.addEventListener('change', function() {
        if(this.checked) {
            checksMeses.forEach(c => c.checked = false); // Desmarca los demás
        }
    });
    
    checksMeses.forEach(check => {
        check.addEventListener('change', function() {
            if(this.checked) {
                checkMesTodos.checked = false; // Desmarca "Todos"
            }
        });
    });

    // Lógica para ÁREAS
    const checkAreaTodas = document.getElementById('area_todas');
    const checksAreas = document.querySelectorAll('.check-area');
    
    checkAreaTodas.addEventListener('change', function() {
        if(this.checked) {
            checksAreas.forEach(c => c.checked = false); // Desmarca las demás
        }
    });
    
    checksAreas.forEach(check => {
        check.addEventListener('change', function() {
            if(this.checked) {
                checkAreaTodas.checked = false; // Desmarca "Todas"
            }
        });
    });

});

</script>
</body>
</html>