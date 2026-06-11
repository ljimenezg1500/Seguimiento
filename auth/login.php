<?php

include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['nombre']);

    $password = trim($_POST['password']);

    $stmt = $conexion->prepare(
        "SELECT * FROM usuarios WHERE nombre=?"
    );

    $stmt->bind_param("s", $nombre);

    $stmt->execute();

    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){

        $usuario = $resultado->fetch_assoc();

        if(password_verify(
            $password,
            $usuario['password']
        )){

            session_regenerate_id(true);

            $_SESSION['user'] =
            $usuario['nombre'];

            $_SESSION['rol'] =
            $usuario['rol'];

            header(
                "Location: ../dashboard/index.php"
            );

            exit();

        }else{

            $error =
            "Contraseña incorrecta";

        }

    }else{

        $error =
        "Usuario no encontrado";

    }

}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Login</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
rel="stylesheet">

<link
rel="stylesheet"
href="../assets/css/login.css">

</head>

<body>

<!-- LOGIN -->

<div class="login-container">

    <!-- LEFT -->

    <div class="login-left">

        <div class="logo-box">

            <i class="bi bi-shield-lock-fill"></i>

        </div>

        <h1 class="system-title">

            Ticket Management System

        </h1>

        <p class="system-text">

            Plataforma corporativa para administración
            de tickets, usuarios y monitoreo interno
            del sistema.

        </p>

        <div class="system-badge">

            <i class="bi bi-check-circle-fill"></i>

            Acceso seguro 

        </div>

    </div>

    <!-- RIGHT -->

    <div class="login-right">

        <div class="login-header">

            <h2>

                Iniciar sesión

            </h2>

            <p>

                Ingresa tus credenciales para continuar

            </p>

        </div>

        <?php if(isset($error)){ ?>

            <div class="error-box">

                <?php echo $error; ?>

            </div>

        <?php } ?>

        <form method="POST" id="loginForm">

            <!-- USER -->

            <label class="form-label">

                Usuario

            </label>

            <div class="input-custom">

                <i class="bi bi-person-fill"></i>

                <input
                type="text"
                name="nombre"
                placeholder="Ingresa tu usuario"
                required>

            </div>

            <!-- PASSWORD -->

            <label class="form-label">

                Contraseña

            </label>

            <div class="input-custom">

                <i class="bi bi-lock-fill"></i>

                <input
                type="password"
                name="password"
                id="password"
                placeholder="Ingresa tu contraseña"
                required>

                <span
                class="password-toggle"
                onclick="togglePassword()">

                    <i
                    class="bi bi-eye-fill"
                    id="eyeIcon">
                    </i>

                </span>

            </div>

            <!-- CHECK -->

            <div class="form-check">

                <input
                class="form-check-input"
                type="checkbox"
                id="remember">

                <label
                class="form-check-label">

                    Mantener sesión iniciada

                </label>

            </div>

            <!-- BTN -->

            <button
            type="submit"
            class="btn-login">

                <span id="btnText">

                    <i class="bi bi-box-arrow-in-right"></i>
                    Acceder al sistema

                </span>

                <span
                id="loader"
                style="display:none;">

                    <span
                    class="spinner-border spinner-border-sm">
                    </span>

                    Validando...

                </span>

            </button>

        </form>

        <div class="login-footer">

            SistemasDOVER

        </div>

    </div>

</div>

<!-- SCRIPT -->

<script>

/* ========================================
   MOSTRAR PASSWORD
======================================== */

function togglePassword(){

    const password =
    document.getElementById("password");

    const eye =
    document.getElementById("eyeIcon");

    if(password.type === "password"){

        password.type = "text";

        eye.classList.remove("bi-eye-fill");

        eye.classList.add("bi-eye-slash-fill");

    }else{

        password.type = "password";

        eye.classList.remove("bi-eye-slash-fill");

        eye.classList.add("bi-eye-fill");

    }

}

/* ========================================
   LOADER LOGIN
======================================== */

document
.getElementById("loginForm")

.addEventListener("submit", function(){

    document
    .getElementById("btnText")
    .style.display = "none";

    document
    .getElementById("loader")
    .style.display = "inline-block";

});

/* ========================================
   DARK MODE AUTOMATICO
======================================== */

const hour = new Date().getHours();

if(hour >= 19 || hour <= 6){

    document.body.style.background =
    "linear-gradient(135deg,#1e272e,#485460)";

}

/* ========================================
   RECORDAR USUARIO
======================================== */

const remember =
document.getElementById("remember");

/* GUARDAR */

remember.addEventListener("change", () => {

    if(remember.checked){

        localStorage.setItem(
            "rememberUser",
            "true"
        );

    }else{

        localStorage.removeItem(
            "rememberUser"
        );

    }

});

/* LEER */

window.onload = () => {

    if(localStorage.getItem("rememberUser")){

        remember.checked = true;

    }

}

</script>

</body>
</html>