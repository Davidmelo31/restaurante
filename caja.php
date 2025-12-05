<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'caja') { header("Location: login.php"); exit; }
include "conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Caja - Restaurante</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="estilos.css">
</head>
<body class="bg">
  <header class="topbar">
    <h2>Panel de Caja</h2>
    <div class="user">Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?></div>
  </header>

  <main class="container">
    <section class="card mesas-grid">
      <?php
      $res = $conexion->query("SELECT * FROM mesas ORDER BY id ASC");
      while ($m = $res->fetch_assoc()) {
        echo "<article class='mesa-card' data-mesa-id='{$m['id']}' data-mesa-nombre='".htmlspecialchars($m['nombre'])."'>
                <h4>Mesa {$m['nombre']}</h4>
                <div class='ped-list' id='ped_{$m['id']}'>
                  <div class='muted'>Cargando...</div>
                </div>
              </article>";
      }
      ?>
    </section>

    <section class="card actions-card">
      <h3>Acciones</h3>
      <button class="btn-ghost" onclick="fetchAllPedidos()">Actualizar ahora</button>
    </section>
  </main>

<script>
const wsUrl = 'ws://localhost:8080';
let ws;

function connectWS() {
  ws = new WebSocket(wsUrl);
  ws.onopen = () => console.log('WS conectado (caja)');
  ws.onmessage = (ev) => {
    const data = JSON.parse(ev.data);
    // manejar los tipos: nuevo_pedido, borrar_pedido, init
    if (data.type === 'nuevo_pedido' || data.type === 'borrar_pedido' || data.type === 'init') {
      fetchAllPedidos();
    }
  };
  ws.onclose = () => setTimeout(connectWS, 2000);
}
connectWS();

async function fetchAllPedidos(){
  const res = await fetch('obtener_pedidos.php');
  const json = await res.json();

  // agrupar por mesa
  const grupos = {};
  json.forEach(p => {
    if (!grupos[p.mesa_nombre]) grupos[p.mesa_nombre] = [];
    grupos[p.mesa_nombre].push(p);
  });

  document.querySelectorAll('.mesa-card').forEach(card => {
    const id = card.getAttribute('data-mesa-id');
    const nombre = card.getAttribute('data-mesa-nombre');
    const cont = card.querySelector('.ped-list');
    cont.innerHTML = '';
    const arr = grupos[nombre] || [];
    if (arr.length === 0) {
      cont.innerHTML = '<div class="muted">Sin pedidos</div>';
    } else {
      arr.forEach(p => {
        const div = document.createElement('div');
        div.className = 'ped-item';
        div.innerHTML = `<div><strong>${escapeHtml(p.descripcion)}</strong><div class='muted small'>${p.fecha} • ${escapeHtml(p.creado_por || '')}</div></div>
                         <div><button class="btn-danger" onclick="borrarPedido(${p.id})">Borrar</button></div>`;
        cont.appendChild(div);
      });
    }
  });
}
fetchAllPedidos();

async function borrarPedido(id){
  if (!confirm('¿Borrar pedido?')) return;
  const form = new FormData();
  form.append('id', id);
  const res = await fetch('eliminar_pedido.php', { method: 'POST', body: form });
  const txt = await res.text();
  if (txt === 'OK') {
    fetchAllPedidos();
  } else alert('Error: ' + txt);
}

function escapeHtml(str) {
  return (str||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}
</script>
</body>
</html>
