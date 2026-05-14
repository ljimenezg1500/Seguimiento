<?php

include("../config/db.php");

include("../includes/permisos.php");



/* PROTEGER SESION */

if(!isset($_SESSION['user'])){

    header("Location: ../auth/login.php");

    exit();

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

<title>Usuarios</title>

<!-- BOOTSTRAP -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- ICONOS -->

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

<!-- SWEETALERT -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <div class="page-header">

        <div>

            <h1 class="page-title">

                Usuarios

            </h1>

            <p class="page-subtitle">

                Administración de usuarios del sistema

            </p>

        </div>

        <!-- BOTON NUEVO -->

        <?php if(esAdmin()){ ?>

        <button
            class="btn btn-add"
            onclick="abrirModalNuevo()">

                <i class="bi bi-plus-circle-fill"></i>

                Nuevo Usuario

        </button>

        <?php } ?>


    
        </button>

    </div>

    <!-- CARD TABLA -->

    <div class="table-card">

        <div class="table-responsive">

            <table
            class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Nombre</th>

                        <th>Email</th>

                        <th>Rol</th>

                        <th width="220">

                            Acciones

                        </th>

                    </tr>

                </thead>

                <!-- AJAX -->

                <tbody id="tablaUsuarios">

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- MODAL -->

<div
class="modal fade"
id="usuarioModal"
tabindex="-1">

<div
class="modal-dialog modal-dialog-centered">

<div class="modal-content">

    <!-- HEADER -->

    <div class="modal-header">

        <h5
        class="modal-title"
        id="tituloModal">

            Nuevo Usuario

        </h5>

        <button
        type="button"
        class="btn-close"
        data-bs-dismiss="modal">
        </button>

    </div>

    <!-- BODY -->

    <div class="modal-body">

        <form id="formUsuario">

            <!-- ID -->

            <input
            type="hidden"
            id="idUsuario">

            <!-- NOMBRE -->

            <div class="mb-3">

                <label>

                    Nombre

                </label>

                <input
                type="text"
                id="nombre"
                class="form-control">

            </div>

            <!-- EMAIL -->

            <div class="mb-3">

                <label>

                    Email

                </label>

                <input
                type="email"
                id="email"
                class="form-control">

            </div>

            <!-- PASSWORD -->

            <div class="mb-3">

                <label>

                    Contraseña

                </label>

                <input
                type="password"
                id="password"
                class="form-control">

            </div>

            <!-- ROL -->

            <div class="mb-3">

                <label>

                    Rol

                </label>

                <select
                id="rol"
                class="form-control">

                    <option value="usuario">

                        Usuario

                    </option>

                    <option value="admin">

                        Admin

                    </option>

                    <option value="supervisor">

                        Supervisor

                    </option>

                </select>

            </div>

        </form>

    </div>

    <!-- FOOTER -->

    <div class="modal-footer">

        <button
        type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal">

            Cancelar

        </button>

        <button
        type="button"
        class="btn btn-primary"
        onclick="guardarUsuario()">

            Guardar

        </button>

    </div>

</div>
</div>
</div>

<!-- BOOTSTRAP -->

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>

<!-- JS -->

<script
src="../assets/js/app.js">
</script>

</body>
</html>