<?php

session_start();

/* SI YA INICIO SESION */

if(isset($_SESSION['user'])){

    header("Location: usuarios/index.php");

}else{

    header("Location: auth/login.php");

}

exit();