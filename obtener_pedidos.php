<?php
include "conexion.php";
$limit = intval($_GET['limit'] ?? 0);
$sql = "SELECT pedidos.id, pedidos.descripcion, pedidos.fecha, pedidos.creado_por, mesas.nombre as mesa_nombre
        FROM pedidos
        JOIN mesas ON pedidos.mesa_id = mesas.id
        ORDER BY pedidos.fecha DESC";
if ($limit > 0) $sql .= " LIMIT $limit";

$res = $conexion->query($sql);
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($out);
?>
