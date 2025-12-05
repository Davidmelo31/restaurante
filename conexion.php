<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "restaurante";

$conexion = new mysqli($host, $user, $pass, $db);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");
?>
