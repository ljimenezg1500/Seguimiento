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

<style>

/* ===== BODY ===== */

body{

    margin:0;
    padding:0;

    height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    font-family:'Segoe UI', sans-serif;

    background:
    linear-gradient(
        135deg,
        #243ca7db,
        #030205dc
    );

    overflow:hidden;

}

/* ===== FONDO CIRCULOS ===== */

.bg-circle{

    position:absolute;

    border-radius:50%;

    background:rgba(255,255,255,0.1);

    animation:float 6s infinite ease-in-out;

}

.circle1{

    width:300px;
    height:300px;

    top:-100px;
    left:-100px;

}

.circle2{

    width:200px;
    height:200px;

    bottom:-50px;
    right:-50px;

}

/* ===== ANIMACION ===== */

@keyframes float{

    0%{
        transform:translateY(0px);
    }

    50%{
        transform:translateY(20px);
    }

    100%{
        transform:translateY(0px);
    }

}

/* ===== CARD LOGIN ===== */

.login-card{

    position:relative;

    z-index:10;

    width:400px;

    padding:40px;

    border-radius:25px;

    background:rgba(255,255,255,0.15);

    backdrop-filter:blur(20px);

    box-shadow:
    0 15px 40px rgba(0,0,0,0.3);

    border:
    1px solid rgba(255,255,255,0.2);

}

/* ===== TITULO ===== */

.login-title{

    text-align:center;

    color:white;

    font-size:35px;

    font-weight:700;

    margin-bottom:10px;

}

.login-subtitle{

    text-align:center;

    color:#eaeaea;

    margin-bottom:30px;

}

/* ===== INPUTS ===== */

.input-group{

    margin-bottom:20px;

}

.input-group-text{

    border:none !important;

    background:rgba(255,255,255,0.2);

    color:white;

    border-radius:12px 0 0 12px;

}

.form-control{

    border:none !important;

    background:rgba(255,255,255,0.2) !important;

    color:white !important;

    padding:14px !important;

    border-radius:0 12px 12px 0 !important;

}

/* PLACEHOLDER */

.form-control::placeholder{

    color:#e0e0e0;

}

/* FOCUS */

.form-control:focus{

    box-shadow:
    0 0 15px rgba(255,255,255,0.3) !important;

}

/* ===== BOTON ===== */

.btn-login{

    width:100%;

    padding:14px;

    border:none;

    border-radius:14px;

    background:white;

    color:#5a67d8;

    font-size:18px;

    font-weight:700;

    transition:0.3s;

}

/* HOVER */

.btn-login:hover{

    transform:translateY(-3px);

    box-shadow:
    0 10px 20px rgba(0,0,0,0.2);

    background:#f1f2f6;

}

/* ===== ERROR ===== */

.error-box{

    background:#ff4757;

    color:white;

    padding:12px;

    border-radius:12px;

    margin-bottom:20px;

    text-align:center;

}

/* ===== FOOTER ===== */

.footer-text{

    text-align:center;

    color:#ddd;

    margin-top:20px;

    font-size:14px;

}

</style>

</head>

<body>

<!-- CIRCULOS -->

<div class="bg-circle circle1"></div>

<div class="bg-circle circle2"></div>

<!-- LOGIN -->

<div class="login-card">

    <h1 class="login-title">
        CRUD SYSTEM
    </h1>

    <p class="login-subtitle">
        Acceso seguro al sistema
    </p>

    <?php if(isset($error)){ ?>

        <div class="error-box">

            <?php echo $error; ?>

        </div>

    <?php } ?>

    <form method="POST" id="loginForm">

        <!-- USUARIO -->

        <div class="input-group">

            <span class="input-group-text">

                <i class="bi bi-person-fill"></i>

            </span>

            <input
            type="text"
            name="nombre"
            class="form-control"
            placeholder="Usuario"
            required>

        </div>

        <!-- PASSWORD -->

        <div class="input-group">

            <span class="input-group-text">

                <i class="bi bi-lock-fill"></i>

            </span>

            <input
            type="password"
            name="password"
            id="password"
            class="form-control"
            placeholder="Contraseña"
            required>

            <!-- OJO -->

            <span
            class="input-group-text"
            style="cursor:pointer"
            onclick="togglePassword()">

                <i
                class="bi bi-eye-fill"
                id="eyeIcon">
                </i>

            </span>

        </div>

        <!-- RECORDARME -->

        <div class="form-check mb-4">

            <input
            class="form-check-input"
            type="checkbox"
            id="remember">

            <label
            class="form-check-label text-white">

                Recordarme

            </label>

        </div>

        <!-- BOTON -->

        <button
        class="btn-login"
        id="loginBtn">

            <span id="btnText">

                <i class="bi bi-box-arrow-in-right"></i>
                Ingresar

            </span>

            <!-- LOADER -->

            <span
            id="loader"
            style="display:none;">

                <span
                class="spinner-border spinner-border-sm">
                </span>

                Cargando...

            </span>

        </button>

    </form>

    <div class="footer-text">

        CRUD AJAX · PHP · MySQL

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