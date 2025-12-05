<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'mesero') { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mesero - Restaurante</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="estilos.css">
</head>
<body class="bg">
  <header class="topbar">
    <h2>Panel Mesero</h2>
    <div class="user">Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?></div>
  </header>

  <main class="container">
    <section class="card form-card">
      <h3>Agregar Pedido (texto libre)</h3>
      <form id="pedidoForm">
        <label>Mesa</label>
        <input id="mesa" name="mesa" placeholder="Ej: 1 o 2A" required>

        <label>Pedido (describe lo que pide el cliente)</label>
        <textarea id="descripcion" name="descripcion" placeholder="Ej: 1 Almuerzo corriente sin arroz, 2 cafés" rows="4" required></textarea>

        <button class="btn-primary" type="submit">Agregar pedido</button>
      </form>
      <div id="msg" class="muted"></div>
    </section>

    <section class="card orders-card">
      <h3>Tus pedidos recientes</h3>
      <ul id="listaPedidos" class="list"></ul>
    </section>
  </main>

<script>
const wsUrl = 'ws://localhost:8080';
let ws;

function connectWS() {
  ws = new WebSocket(wsUrl);
  ws.onopen = () => console.log('WS conectado (mesero)');
  ws.onmessage = (ev) => {
    const data = JSON.parse(ev.data);
    // Si llega evento de nuevo pedido o eliminación, refrescamos la lista
    if (['nuevo_pedido','borrar_pedido','init'].includes(data.type)) {
      fetchPedidos();
    }
  };
  ws.onclose = () => setTimeout(connectWS, 2000);
}
connectWS();

async function fetchPedidos() {
  const res = await fetch('obtener_pedidos.php?limit=10');
  const json = await res.json();
  const list = document.getElementById('listaPedidos');
  list.innerHTML = '';
  json.forEach(p => {
    const li = document.createElement('li');
    li.innerHTML = `<strong>Mesa ${escapeHtml(p.mesa_nombre)}</strong> · ${escapeHtml(p.descripcion)} <span class="muted">(${p.fecha})</span>`;
    list.appendChild(li);
  });
}
fetchPedidos();

document.getElementById('pedidoForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const mesa = document.getElementById('mesa').value.trim();
  const descripcion = document.getElementById('descripcion').value.trim();
  if (!mesa || !descripcion) return;
  const form = new FormData();
  form.append('mesa', mesa);
  form.append('descripcion', descripcion);
  form.append('creado_por', '<?php echo htmlspecialchars($_SESSION['usuario']); ?>');

  const res = await fetch('agregar_pedido.php', { method: 'POST', body: form });
  const text = await res.text();
  if (text === 'OK') {
    document.getElementById('msg').textContent = 'Pedido agregado ✅';
    document.getElementById('pedidoForm').reset();
    fetchPedidos();
  } else {
    document.getElementById('msg').textContent = 'Error: ' + text;
  }
});

function escapeHtml(str) {
  return str.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}
</script>
</body>
</html>
