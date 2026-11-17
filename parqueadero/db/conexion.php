<?php

date_default_timezone_set('America/Bogota');

$host     = "localhost";
$usuario  = "root";
$password = "";
$bd       = "parqueadero";

$conn = mysqli_connect($host, $usuario, $password, $bd);

if(!$conn){
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
mysqli_query($conn, "SET time_zone='-05:00'");

require_once __DIR__ . '/funciones.php';
