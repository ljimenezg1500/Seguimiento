<?php
$conexion = new mysqli("localhost", "root", "", "crud");

if ($conexion->connect_error) {
    die("Error de conexión");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

