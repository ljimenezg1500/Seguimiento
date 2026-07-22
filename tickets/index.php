<?php

require_once("../config/db.php");
require_once("../includes/permisos.php");

/* =========================================
   PAGINACION Y FILTROS
========================================= */

$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if($pagina < 1) { $pagina = 1; }
$inicio = ($pagina - 1) * $limite;

// Filtro de Estatus desde el Sidebar
$estatusFiltro = isset($_GET['estatus']) ? $conexion->real_escape_string($_GET['estatus']) : '';
$where = "WHERE 1=1";
if(!empty($estatusFiltro)){
    // Usamos LIKE para evitar problemas con mayúsculas/minúsculas
    $where .= " AND LOWER(estatus) LIKE LOWER('%$estatusFiltro%')";
}

/* =========================================
   TOTAL TICKETS
========================================= */

$sqlTotal = "SELECT COUNT(*) total FROM tickets $where";
$resultTotal = $conexion->query($sqlTotal);
$totalTickets = $resultTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalTickets / $limite);

/* =========================================
   TICKETS
========================================= */

$sql = "SELECT * FROM tickets $where ORDER BY CAST(ticket_id AS UNSIGNED) DESC LIMIT $inicio, $limite";
$resultado = $conexion->query($sql);

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Tickets</title>

<!-- BOOTSTRAP -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- ICONOS -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

<!-- SWEET ALERT -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CSS -->

<link
rel="stylesheet"
href="../assets/css/style.css">

</head>

<body>

<!-- SIDEBAR -->

<?php include("../includes/sidebar.php"); ?>

<!-- CONTENT -->

<div class="content">

    <!-- HEADER -->

    <div class="page-header">

        <div>

            <h1 class="page-title">

                Tickets

            </h1>

            <p class="page-subtitle">

                Importación y visualización
                de tickets

            </p>

        </div>

    </div>

    <!-- SUBIR EXCEL -->

    <div class="ticket-upload-card mb-4">

        <div class="upload-icon">

        </div>

        <h3>

            Subir archivo Excel

        </h3>

        <p>

            Importa tickets automáticamente
            al sistema

        </p>

        <form
        id="formExcel"
        enctype="multipart/form-data">

            <input
            type="file"
            name="excel"
            class="form-control mb-4"
            accept=".xlsx,.xls"
            required>

            <button
            type="submit"
            class="btn-upload">

                <i class="bi bi-upload"></i>

                Importar Tickets

            </button>

        </form>

    </div>

    <!-- TABLA -->

    <div class="table-card">

        <div class="d-flex
        justify-content-between
        align-items-center
        mb-4">

            <h4>

                Últimos Tickets

            </h4>

            <span class="badge bg-primary p-2">

                Total:
                <?php echo $totalTickets; ?>

            </span>

        </div>

        <div>

            <table class="table table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Area</th>
            <th>Tipo</th>
            <th>Urgencia</th>
        </tr>
    </thead>
    <tbody>
        <?php
        while( $ticket = $resultado->fetch_assoc() ){
        ?>
        <tr>
            <td><?php echo $ticket['ticket_id']; ?></td>
            <td><?php echo $ticket['usuario']; ?></td>
            <td><?php echo $ticket['area']; ?></td>
            <td><?php echo $ticket['titulo']; ?></td>
            <td><?php echo $ticket['urgencia']; ?></td>
            
        </tr>
        <?php } ?>
    </tbody>
</table>

        </div>

        <!-- PAGINACION -->

        <div class="pagination-container">

            <?php if($pagina > 1){ ?>

            <a
            href="?pagina=<?php
            echo $pagina - 1;
            ?>"
            class="pagination-btn">

                ← Anterior

            </a>

            <?php } ?>

            <span class="pagination-info">

                Página
                <?php echo $pagina; ?>

                de

                <?php echo $totalPaginas; ?>

            </span>

            <?php if(
                $pagina < $totalPaginas
            ){ ?>

            <a
            href="?pagina=<?php
            echo $pagina + 1;
            ?>"
            class="pagination-btn">

                Siguiente →

            </a>

            <?php } ?>

        </div>

    </div>

</div>

<!-- JS -->

<script>

/* =========================================
   SUBIR EXCEL
========================================= */

document
.getElementById("formExcel")

.addEventListener(
"submit",

function(e){

    e.preventDefault();

    let formData =
    new FormData(this);

    fetch(
        "subir_excel.php",
        {

            method:"POST",

            body:formData

        }
    )

    .then(response => response.text())

    .then(data => {

        if(
            data.includes(
                "correctamente"
            )
        ){

            Swal.fire({

                icon:"success",

                title:
                "Importación exitosa",

                text:data

            })

            .then(()=>{

                location.reload();

            });

        }else{

            Swal.fire({

                icon:"error",

                title:"Error",

                text:data

            });

        }

    });

});

</script>

</body>
</html>