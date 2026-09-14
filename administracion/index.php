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
            <h1 class="page-title text-dark"><i class="bi bi-shield-lock-fill"></i> Administración</h1>
            <p class="page-subtitle">Configuraciones personalizadas</p>
        </div>
    </div>

    
    

    <div class="row g-4">
        
        <!-- ZONA DE PELIGRO: VACIAR TICKETS -->
        <div class="col-md-6">
            <div class="corporate-card border-danger border-top border-3 shadow-sm h-100 d-flex flex-column">
                <h4 class="text-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> Vaciar Base de Datos</h4>
                <p class="text-muted">
                    Esta acción ejecutará un <strong>TRUNCATE</strong> en la tabla de tickets. Eliminará absolutamente todos los registros existentes y reiniciará el contador de IDs a cero. 
                    <br><br>
                    <em>Esta acción no se puede deshacer.</em>
                </p>
                <!-- Agregamos mt-auto para empujar el botón al fondo -->
                <button class="btn btn-danger w-100 mt-auto" onclick="vaciarTickets()">
                    <i class="bi bi-trash3-fill"></i> Eliminar TODOS los tickets
                </button>
            </div>
        </div>

        <!-- GESTIÓN DE USUARIOS -->
        <div class="col-md-6">
            <div class="corporate-card border-primary border-top border-3 shadow-sm h-100 d-flex flex-column">
                <h4 class="text-primary mb-3"><i class="bi bi-people-fill"></i> Gestión de Usuarios</h4>
                <p class="text-muted">
                    Administra los accesos al sistema. Puedes agregar nuevos usuarios, editar sus roles, contraseñas o eliminarlos permanentemente de la base de datos.
                </p>
                <!-- Agregamos mt-auto para empujar el botón al fondo -->
                <a href="/CRUD/administracion/usuarios/index.php" class="btn btn-primary w-100 mt-auto">
                    <i class="bi bi-person-gear"></i> Administrar Usuarios
                </a>
            </div>

            
        </div>

        



    </div>
    <!-- NUEVA FILA PARA LA BITÁCORA -->
    <div class="row mt-2">
        <div class="col-12">
            <div class="corporate-card border-warning border-top border-3 shadow-sm d-flex flex-column">
                <h4 class="mb-3" style="color: #f39c12;"><i class="bi bi-journal-text"></i> Bitácora de Auditoría</h4>
                <p class="text-muted">
                    Monitorea cada movimiento estratégico del sistema. Registra exactamente quién exporta o importa tickets, quién modifica permisos de usuarios, cambios de contraseña y más.
                </p>
                <a href="/CRUD/administracion/bitacora/index.php" class="btn btn-warning w-100 mt-auto fw-bold text-dark">
                    <i class="bi bi-eye-fill"></i> Revisar Historial Completo
                </a>
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