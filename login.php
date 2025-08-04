<?php
session_start();

$pass_file = __DIR__ . '/passwords.json';

// Inicializar si no existe
if (!file_exists($pass_file)) {
  file_put_contents($pass_file, json_encode(['admin' => '1703', 'docente' => 'momj']));
}

// Cargar contraseñas
$pass_data = json_decode(file_get_contents($pass_file), true);
$admin_pass = $pass_data['admin'];
$docente_pass = $pass_data['docente'];

// Manejar logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  session_destroy();
  header('Location: login.php');
  exit;
}

$vista = $_GET['vista'] ?? 'seleccion';
$usuario_seleccionado = $_GET['usuario'] ?? null;
$error = '';
$mensaje = '';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
  $usuario = $_POST['login_usuario'];
  $pass_ingresado = $_POST['login_password'];

  if ($usuario === 'Administrador' && $pass_ingresado === $admin_pass) {
    $_SESSION['tipo_usuario'] = 'admin';
    $_SESSION['usuario_actual'] = 'Administrador';
    header('Location: login.php?vista=loggedin');
    exit;
  } elseif ($usuario === 'Docente' && $pass_ingresado === $docente_pass) {
    $_SESSION['tipo_usuario'] = 'docente';
    $_SESSION['usuario_actual'] = 'Docente';
    header('Location: login.php?vista=loggedin');
    exit;
  } else {
    $error = 'Usuario o contraseña incorrectos.';
    $vista = 'password';
    $usuario_seleccionado = $usuario;
  }
}

// Auth para cambiar contraseñas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_pass'])) {
  $pass_actual = $_POST['auth_pass'];
  if ($pass_actual === $admin_pass) {
    $_SESSION['auth_cambiar'] = true;
    header('Location: login.php?vista=cambiar_pass');
    exit;
  } else {
    $error = 'Contraseña actual incorrecta.';
    $vista = 'cambiar_auth';
  }
}

// Cambiar contraseñas
if (
  $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_admin']) && isset($_POST['new_docente'])
  && isset($_POST['repeat_admin']) && isset($_POST['repeat_docente'])
) {

  if (!isset($_SESSION['auth_cambiar']) || $_SESSION['auth_cambiar'] !== true) {
    header('Location: login.php?vista=cambiar_auth');
    exit;
  }

  $new_admin = trim($_POST['new_admin']);
  $repeat_admin = trim($_POST['repeat_admin']);
  $new_docente = trim($_POST['new_docente']);
  $repeat_docente = trim($_POST['repeat_docente']);

  if ($new_admin === '' || $new_docente === '' || $repeat_admin === '' || $repeat_docente === '') {
    $error = 'Todos los campos deben llenarse.';
    $vista = 'cambiar_pass';
  } elseif ($new_admin !== $repeat_admin) {
    $error = 'La nueva contraseña de Administrador no coincide.';
    $vista = 'cambiar_pass';
  } elseif ($new_docente !== $repeat_docente) {
    $error = 'La nueva contraseña de Docente no coincide.';
    $vista = 'cambiar_pass';
  } else {
    // Guardar en el archivo
    file_put_contents($pass_file, json_encode(['admin' => $new_admin, 'docente' => $new_docente]));
    unset($_SESSION['auth_cambiar']);
    $mensaje = 'Contraseñas actualizadas correctamente.';
    $vista = 'loggedin';

    // Recargar contraseñas actuales
    $admin_pass = $new_admin;
    $docente_pass = $new_docente;
  }
}

// Cerrar sesión
if (isset($_GET['action']) && $_GET['action'] === 'logout_sistema') {
  session_destroy();
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Sistema</title>
  <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body,
html {
  margin: 0;
  height: 100%;
  font-family: 'Poppins', sans-serif;
  background: #f1f9f1; /* blanco ligeramente verdoso */
  display: flex;
  justify-content: center;
  align-items: center;
  color: #2e4d2e; /* verde oscuro para texto */
}

.wrapper {
  display: flex;
  background: #ffffffcc; /* blanco translúcido para el panel */
  border-radius: 12px;
  box-shadow: 0 12px 40px rgba(0, 77, 0, 0.2);
  overflow: hidden;
  width: 800px;
  max-width: 95%;
  animation: fadeSlideIn 0.7s ease forwards;
}

.logo-side {
  flex: 1;
  background: #d5e8d5; /* verde pastel suave */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
}

.logo-side img {
  max-width: 100%;
  height: auto;
  border-radius: 10px;
  filter: drop-shadow(0 0 3px #7cae7c);
}

.container {
  flex: 1;
  padding: 40px;
  text-align: center;
}

@media (max-width: 768px) {
  .wrapper {
    flex-direction: column;
  }

  .logo-side,
  .container {
    flex: unset;
    width: 100%;
  }
}

@keyframes fadeSlideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

h2 {
  margin-bottom: 30px;
  color: #2e4d2e;
  font-weight: 700;
  font-size: 1.8rem;
  text-shadow: 0 0 4px #b2d8b2;
}

.btn {
  border: none;
  color: #ffffff;
  font-weight: 700;
  padding: 15px 0;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  margin-bottom: 20px;
  font-size: 1.1rem;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
  box-shadow: 0 2px 5px rgba(46, 77, 46, 0.4);
}

.btn.admin {
  background: #5a9e5a;
}

.btn.admin:hover {
  background: #77ba77;
  box-shadow: 0 0 10px #b7deb7;
}

.btn.docente {
  background: #6bad6b;
}

.btn.docente:hover {
  background: #88c788;
  box-shadow: 0 0 10px #c8eac8;
}

.btn.iniciar {
  background: #7eb97e;
}

.btn.iniciar:hover {
  background: #9ed29e;
  box-shadow: 0 0 10px #d8f2d8;
}

label {
  font-weight: 600;
  color: #3a633a;
  font-size: 0.9rem;
  display: block;
  text-align: left;
  margin-bottom: 6px;
}

input[type="password"] {
  width: 100%;
  padding: 12px 15px;
  margin-bottom: 20px;
  border-radius: 8px;
  border: 2px solid #9ccc9c;
  background: #f6fcf6;
  color: #2e4d2e;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  box-sizing: border-box;
}

input[type="password"]:focus {
  border-color: #6ea86e;
  box-shadow: 0 0 8px #bde3bd;
  outline: none;
}

.back-btn {
  background: transparent;
  border: none;
  color: #4a7a4a;
  font-weight: 600;
  cursor: pointer;
  margin-top: 10px;
  font-size: 0.95rem;
}

.back-btn:hover {
  text-decoration: underline;
  color: #3a633a;
}

.error-msg {
  background: #b04141;
  color: #fef3f3;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 700;
  margin-bottom: 20px;
  text-align: center;
}

.success-msg {
  background: #4b944b;
  color: #eaf6ea;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 700;
  margin-bottom: 20px;
  text-align: center;
}

.logout-link {
  margin-top: 15px;
  display: inline-block;
  color: #4a7a4a;
  font-weight: 600;
  cursor: pointer;
  text-decoration: underline;
  font-size: 0.9rem;
}

.logout-link:hover {
  color: #3a633a;
}


  </style>
</head>

<body>

  <div class="wrapper">
    <div class="logo-side">
      <img src="musiclogo.png" alt="Logo Academia" />
    </div>

    <div class="container" role="main" aria-live="polite">

      <?php if ($vista === 'seleccion'): ?>
        <h2>Selecciona el usuario</h2>
        <?php if ($error): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <button class="btn admin" onclick="window.location='?vista=password&usuario=Administrador'">Entrar como
          Administrador</button>
        <button class="btn docente" onclick="window.location='?vista=password&usuario=Docente'">Entrar como
          Docente</button>

      <?php elseif ($vista === 'password' && $usuario_seleccionado): ?>
        <h2>Contraseña para <?= htmlspecialchars($usuario_seleccionado) ?></h2>
        <?php if ($error): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" action="">
          <input type="hidden" name="login_usuario" value="<?= htmlspecialchars($usuario_seleccionado) ?>" />
          <label for="login_password">Contraseña:</label>
          <input type="password" id="login_password" name="login_password" required autocomplete="current-password"
            autofocus />
          <button type="submit"
            class="btn <?= $usuario_seleccionado === 'Administrador' ? 'admin' : 'docente' ?>">Entrar</button>
        </form>
        <button class="back-btn" onclick="window.location='?vista=seleccion'">← Volver a selección</button>

      <?php elseif ($vista === 'loggedin'): ?>
        <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_actual'] ?? '') ?></h2>
        <?php if ($mensaje): ?>
          <div class="success-msg"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
        <p>Has iniciado sesión como <strong><?= htmlspecialchars($_SESSION['tipo_usuario']) ?></strong>.</p>
        <button class="btn iniciar" onclick="window.location='lista_alumnos.php'">Iniciar</button>
        <?php if ($_SESSION['tipo_usuario'] === 'admin'): ?>
          <button class="btn docente" style="margin-top: 10px;" onclick="window.location='?vista=cambiar_auth'">Cambiar
            contraseñas</button>
        <?php endif; ?>
        <a href="?action=logout_sistema" class="logout-link">Cerrar sesión</a>

      <?php elseif ($vista === 'cambiar_auth'): ?>
        <h2>Autentícate para cambiar contraseñas</h2>
        <?php if ($error): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" action="">
          <label for="auth_pass">Contraseña actual de Admin:</label>
          <input type="password" id="auth_pass" name="auth_pass" required autofocus autocomplete="current-password" />
          <button type="submit" class="btn docente">Verificar</button>
        </form>
        <button class="back-btn" onclick="window.location='?vista=loggedin'">← Volver</button>

      <?php elseif ($vista === 'cambiar_pass'): ?>
        <h2>Cambiar contraseñas</h2>
        <?php if ($error): ?>
          <div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" action="">
          <label for="new_admin">Nueva contraseña para Administrador:</label>
          <input type="password" id="new_admin" name="new_admin" required autocomplete="new-password" />
          <label for="repeat_admin">Repetir nueva contraseña para Administrador:</label>
          <input type="password" id="repeat_admin" name="repeat_admin" required autocomplete="new-password" />
          <label for="new_docente">Nueva contraseña para Docente:</label>
          <input type="password" id="new_docente" name="new_docente" required autocomplete="new-password" />
          <label for="repeat_docente">Repetir nueva contraseña para Docente:</label>
          <input type="password" id="repeat_docente" name="repeat_docente" required autocomplete="new-password" />
          <button type="submit" class="btn docente">Guardar cambios</button>
        </form>
        <button class="back-btn" onclick="window.location='?vista=loggedin'">← Volver</button>

      <?php else: ?>
        <h2>Error</h2>
        <p>Vista desconocida.</p>
        <button class="back-btn" onclick="window.location='?vista=seleccion'">Volver</button>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>