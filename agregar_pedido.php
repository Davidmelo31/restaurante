<?php
include "conexion.php";

$mesa_input = $_POST['mesa'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$creado_por = $_POST['creado_por'] ?? '';

if (trim($mesa_input) === '' || trim($descripcion) === '') {
    echo "Faltan datos";
    exit;
}

// buscar mesa por nombre; si no existe, crearla
$stmt = $conexion->prepare("SELECT id, nombre FROM mesas WHERE nombre = ? LIMIT 1");
$stmt->bind_param("s", $mesa_input);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $mesa = $res->fetch_assoc();
    $mesa_id = $mesa['id'];
    $mesa_nombre = $mesa['nombre'];
} else {
    // crear mesa nueva
    $ins = $conexion->prepare("INSERT INTO mesas (nombre) VALUES (?)");
    $ins->bind_param("s", $mesa_input);
    $ins->execute();
    $mesa_id = $ins->insert_id;
    $mesa_nombre = $mesa_input;
}

// insertar pedido
$ins2 = $conexion->prepare("INSERT INTO pedidos (mesa_id, producto_id, descripcion, creado_por) VALUES (?, NULL, ?, ?)");
$ins2->bind_param("iss", $mesa_id, $descripcion, $creado_por);
if (!$ins2->execute()) {
    echo "Error al insertar";
    exit;
}
$pedido_id = $ins2->insert_id;

// Notificar al WS server HTTP
$payload = [
    'type' => 'nuevo_pedido',
    'pedido' => [
        'id' => $pedido_id,
        'mesa_id' => $mesa_id,
        'mesa_nombre' => $mesa_nombre,
        'descripcion' => $descripcion,
        'creado_por' => $creado_por,
        'fecha' => date('Y-m-d H:i:s')
    ]
];

$ch = curl_init('http://localhost:3000/notify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_exec($ch);
curl_close($ch);

echo "OK";
?>
