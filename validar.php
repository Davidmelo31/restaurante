<?php
session_start();
include "conexion.php";

$usuario = $_POST['usuario'] ?? '';
$clave = $_POST['clave'] ?? '';

$stmt = $conexion->prepare("SELECT username, rol FROM usuarios WHERE username=? AND password=? LIMIT 1");
$stmt->bind_param("ss", $usuario, $clave);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $_SESSION['rol'] = $row['rol'];
    $_SESSION['usuario'] = $row['username'];
    if ($row['rol'] === 'caja') header("Location: caja.php");
    else header("Location: mesero.php");
    exit;
} else {
    echo "<p>Usuario o contraseña incorrectos. <a href='login.php'>Volver</a></p>";
}
?>
