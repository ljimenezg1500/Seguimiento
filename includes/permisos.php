<?php

if(session_status() === PHP_SESSION_NONE){

    session_start();

}

/* VALIDAR LOGIN */

if(!isset($_SESSION['user'])){

    header("Location: ../auth/login.php");

    exit();

}

/* ROL */

$ROL =
$_SESSION['rol'];

/* FUNCIONES */

function esAdmin(){

    return $_SESSION['rol'] == 'admin';

}

function esSupervisor(){

    return $_SESSION['rol'] == 'supervisor';

}

function esUsuario(){

    return $_SESSION['rol'] == 'usuario';

}