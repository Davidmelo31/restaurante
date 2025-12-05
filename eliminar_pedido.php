<?php
include "conexion.php";

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) { echo "Id inválido"; exit; }

// obtener datos para notificación
$res = $conexion->query("SELECT pedidos.id, mesas.nombre as mesa_nombre FROM pedidos JOIN mesas ON pedidos.mesa_id = mesas.id WHERE pedidos.id = $id LIMIT 1");
$row = $res->fetch_assoc();

if (!$row) { echo "No existe"; exit; }

$del = $conexion->prepare("DELETE FROM pedidos WHERE id = ?");
$del->bind_param("i", $id);
if (!$del->execute()) { echo "Error al borrar"; exit; }

// notificar
$payload = ['type' => 'borrar_pedido', 'pedido' => ['id' => $id, 'mesa_nombre' => $row['mesa_nombre']]];
$ch = curl_init('http://localhost:3000/notify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_exec($ch);
curl_close($ch);

echo "OK";
?>
