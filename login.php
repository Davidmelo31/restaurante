<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login - Restaurante</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="estilos.css">
</head>
<body class="bg">
  <main class="card center-card">
    <h1 class="title">Ingreso al Sistema</h1>
    <form action="validar.php" method="POST" class="form">
      <input name="usuario" placeholder="Usuario" required>
      <input type="password" name="clave" placeholder="Contraseña" required>
      <button class="btn-primary" type="submit">Entrar</button>
    </form>
    <p class="muted">Usuarios demo: mesero1/1234 - caja1/1234</p>
  </main>
</body>
</html>
