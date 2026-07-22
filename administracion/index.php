<?php
include("../config/db.php");
include("../includes/permisos.php");

/* PROTEGER SESION */
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit();
}

/* SUPER SEGURIDAD: SOLO ADMINS */
if(!esAdmin()){
    echo "<h1>Acceso Denegado</h1><p>No tienes los privilegios necesarios para ver esta página.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración del Sistema</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ICONOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SWEETALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- SIDEBAR -->
<?php include("../includes/sidebar.php"); ?>

<!-- CONTENIDO -->
<div class="content">
    
    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title text-danger"><i class="bi bi-shield-lock-fill"></i> Administración Avanzada</h1>
            <p class="page-subtitle">Zona de peligro: Modificación directa a la base de datos</p>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- ZONA DE PELIGRO: VACIAR TICKETS -->
        <div class="col-md-6">
            <div class="corporate-card border-danger border-top border-3 shadow-sm h-100">
                <h4 class="text-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> Vaciar Base de Datos</h4>
                <p class="text-muted">
                    Esta acción ejecutará un <strong>TRUNCATE</strong> en la tabla de tickets. Eliminará absolutamente todos los registros existentes y reiniciará el contador de IDs a cero. 
                    <br><br>
                    <em>Esta acción no se puede deshacer.</em>
                </p>
                <button class="btn btn-danger w-100 mt-2" onclick="vaciarTickets()">
                    <i class="bi bi-trash3-fill"></i> Eliminar TODOS los tickets
                </button>
            </div>
        </div>

        <!-- AQUÍ AGREGAREMOS MÁS FUNCIONES DESPUÉS -->
        <div class="col-md-6">
            <div class="corporate-card shadow-sm h-100 d-flex align-items-center justify-content-center">
                <p class="text-muted m-0"><i class="bi bi-tools"></i> Más herramientas de administración próximamente...</p>
            </div>
        </div>

    </div>
</div>

<script>
/* ========================================
   FUNCIÓN: VACIAR TICKETS (TRUNCATE)
======================================== */
function vaciarTickets(){
    Swal.fire({
        title: '¡ADVERTENCIA CRÍTICA!',
        text: "Estás a punto de borrar todos los tickets del sistema de forma permanente. ¿Estás absolutamente seguro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, borrar TODO',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            
            // Doble confirmación por seguridad
            Swal.fire({
                title: 'Última oportunidad',
                text: "Escribe la palabra CONFIRMAR para proceder con el borrado:",
                input: 'text',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ejecutar Truncate'
            }).then((secondResult) => {
                if (secondResult.isConfirmed) {
                    if(secondResult.value === 'CONFIRMAR'){
                        
                        // Si escribió confirmar, ejecutamos el PHP
                        fetch('vaciar_tickets.php', { method: 'POST' })
                        .then(response => response.text())
                        .then(data => {
                            Swal.fire({
                                icon: data.includes('correctamente') ? 'success' : 'error',
                                title: data.includes('correctamente') ? 'Sistema Limpio' : 'Error',
                                text: data
                            }).then(() => location.reload());
                        });

                    } else {
                        Swal.fire('Cancelado', 'La palabra de seguridad fue incorrecta. No se borró nada.', 'info');
                    }
                }
            });
        }
    });
}
</script>

</body>
</html>