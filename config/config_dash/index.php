<?php
include("../../config/db.php");
include("../../includes/permisos.php");

/* PROTEGER SESION */
if(!isset($_SESSION['user'])){
    header("Location: ../../auth/login.php");
    exit();
}

/* OBTENER DATOS DEL USUARIO ACTUAL PARA SU PERFIL */
$nombreSesion = $_SESSION['user'];
$stmtUser = $conexion->prepare("SELECT * FROM usuarios WHERE nombre=?");
$stmtUser->bind_param("s", $nombreSesion);
$stmtUser->execute();
$datosUsuario = $stmtUser->get_result()->fetch_assoc();

/* OBTENER ÁREAS (SOLO SI ES ADMIN PARA AHORRAR MEMORIA) */
if(esAdmin()){
    $sqlAreas = "SELECT * FROM areas ORDER BY nombre ASC";
    $resultAreas = $conexion->query($sqlAreas);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/CRUD/assets/chivas.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ICONOS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SWEETALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<!-- SIDEBAR -->
<?php include("../../includes/sidebar.php"); ?>

<!-- CONTENIDO -->
<div class="content">
    
    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Configuración</h1>
            <p class="page-subtitle">Administra tu cuenta y los parámetros del sistema</p>
        </div>
    </div>

    <!-- PESTAÑAS (NAVTABS) -->
    <ul class="nav nav-tabs" id="configTabs" role="tablist">
        
        <!-- PESTAÑA 1: MI PERFIL (Para todos) -->
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="perfil-tab" data-bs-toggle="tab" data-bs-target="#perfil" type="button" role="tab">
                <i class="bi bi-person-lines-fill"></i> Mi Perfil
            </button>
        </li>
        
        <!-- PESTAÑA 2: CATÁLOGOS (Solo Admin) -->
        <?php if(esAdmin()){ ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="catalogos-tab" data-bs-toggle="tab" data-bs-target="#catalogos" type="button" role="tab">
                <i class="bi bi-tags-fill"></i> Catálogos del Sistema
            </button>
        </li>
        <?php } ?>
        
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content" id="configTabsContent">
        
        <!-- ==========================================
             CONTENIDO PESTAÑA 1: MI PERFIL 
        =========================================== -->
        <div class="tab-pane fade show active mt-4" id="perfil" role="tabpanel">
            <div class="row g-4">
                
                <!-- DATOS PERSONALES -->
                <div class="col-md-6">
                    <div class="corporate-card h-100">
                        <h4 class="mb-4">Datos Personales</h4>
                        <form id="formPerfil">
                            <input type="hidden" id="perfilId" value="<?php echo $datosUsuario['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Nombre de Usuario</label>
                                <input type="text" id="perfilNombre" class="form-control" value="<?php echo $datosUsuario['nombre']; ?>" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" id="perfilEmail" class="form-control" value="<?php echo $datosUsuario['email']; ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Actualizar Datos
                            </button>
                        </form>
                    </div>
                </div>

                <!-- CAMBIAR CONTRASEÑA -->
                <div class="col-md-6">
                    <div class="corporate-card h-100">
                        <h4 class="mb-4">Seguridad</h4>
                        <form id="formPassword">
                            <input type="hidden" id="passId" value="<?php echo $datosUsuario['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Contraseña Actual</label>
                                <input type="password" id="passActual" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" id="passNueva" class="form-control" required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Confirmar Nueva Contraseña</label>
                                <input type="password" id="passConfirma" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-shield-lock"></i> Cambiar Contraseña
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==========================================
             CONTENIDO PESTAÑA 2: CATÁLOGOS (Admin) 
        =========================================== -->
        <?php if(esAdmin()){ ?>
        <div class="tab-pane fade mt-4" id="catalogos" role="tabpanel">
            <div class="row g-4">
                
                <!-- NUEVA ÁREA -->
                <div class="col-md-4">
                    <div class="corporate-card">
                        <h4 class="mb-4">Agregar Área</h4>
                        <form id="formArea">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Área</label>
                                <input type="text" id="nombreArea" class="form-control" placeholder="Ej. RECURSOS HUMANOS" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save"></i> Guardar Área
                            </button>
                        </form>
                    </div>
                </div>

                <!-- TABLA ÁREAS -->
                <div class="col-md-8">
                    <div class="table-card">
                        <h4 class="mb-4">Catálogo de Áreas</h4>
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre del Área</th>
                                    <th width="150">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($area = $resultAreas->fetch_assoc()){ ?>
                                <tr>
                                    <td><?php echo $area['id']; ?></td>
                                    <td><strong><?php echo $area['nombre']; ?></strong></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="eliminarArea(<?php echo $area['id']; ?>)">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <?php } ?>

    </div> <!-- Fin Tab Content -->

</div> <!-- Fin Content -->

<!-- BOOTSTRAP JS (Necesario para que funcionen las pestañas) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- SCRIPTS DE FUNCIONALIDAD -->
<script>
/* =======================================
   LÓGICA DE ÁREAS (Catálogos)
======================================= */
const formArea = document.getElementById('formArea');
if(formArea){
    formArea.addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData();
        formData.append('nombre', document.getElementById('nombreArea').value);
        
        fetch('guardar_area.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => {
            Swal.fire({
                icon: data.includes('correctamente') ? 'success' : 'error',
                title: data.includes('correctamente') ? 'Éxito' : 'Error',
                text: data
            }).then(() => {
                if(data.includes('correctamente')) location.reload();
            });
        });
    });
}

function eliminarArea(id){
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Si eliminas esta área desaparecerá de los selectores.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            let formData = new FormData();
            formData.append('id', id);
            fetch('eliminar_area.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => {
                Swal.fire('Eliminado', data, 'success').then(() => location.reload());
            });
        }
    });
}

/* =======================================
   LÓGICA DE PERFIL
======================================= */

/* GUARDAR DATOS PERSONALES */
document.getElementById('formPerfil').addEventListener('submit', function(e){
    e.preventDefault();
    
    let formData = new FormData();
    formData.append('id', document.getElementById('perfilId').value);
    formData.append('nombre', document.getElementById('perfilNombre').value);
    formData.append('email', document.getElementById('perfilEmail').value);
    
    fetch('actualizar_perfil.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => {
        Swal.fire({
            icon: data.includes('correctamente') ? 'success' : 'error',
            title: data.includes('correctamente') ? 'Éxito' : 'Error',
            text: data
        }).then(() => {
            if(data.includes('correctamente')) location.reload();
        });
    });
});

/* CAMBIAR CONTRASEÑA */
document.getElementById('formPassword').addEventListener('submit', function(e){
    e.preventDefault();
    
    let passNueva = document.getElementById('passNueva').value;
    let passConfirma = document.getElementById('passConfirma').value;
    
    // Validación rápida en el navegador antes de enviar a PHP
    if(passNueva !== passConfirma){
        Swal.fire('Error', 'Las contraseñas nuevas no coinciden.', 'error');
        return;
    }

    let formData = new FormData();
    formData.append('id', document.getElementById('passId').value);
    formData.append('passActual', document.getElementById('passActual').value);
    formData.append('passNueva', passNueva);
    
    fetch('cambiar_password.php', { method: 'POST', body: formData })
    .then(response => response.text())
    .then(data => {
        Swal.fire({
            icon: data.includes('correctamente') ? 'success' : 'error',
            title: data.includes('correctamente') ? 'Éxito' : 'Error',
            text: data
        }).then(() => {
            if(data.includes('correctamente')){
                // Limpiamos las cajitas de texto si se guardó bien
                document.getElementById('formPassword').reset(); 
            }
        });
    });
});

</script>

</body>
</html>