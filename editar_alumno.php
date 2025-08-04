<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;

require_once __DIR__ . '/includes/bd.php';

$curp = isset($_GET['curp']) ? trim($_GET['curp']) : '';

if (empty($curp)) {
  header("Location: lista_alumnos.php");
  exit;
}

$stmt = $conexion->prepare("SELECT * FROM alumnos WHERE curp = ?");
$stmt->bind_param("s", $curp);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();

if (!$alumno) {
  die("Alumno no encontrado");
}

// Obtener temas disponibles
$temasDisponibles = [];
$resTemas = $conexion->query("SELECT id, nombre FROM temas");
if ($resTemas) {
  $temasDisponibles = $resTemas->fetch_all(MYSQLI_ASSOC);
}

// Obtener temas ya asignados al alumno
$temasAsignados = [];
$resAsignados = $conexion->prepare("SELECT tema_id FROM alumnos_tema WHERE curp = ?");
$resAsignados->bind_param("s", $curp);
$resAsignados->execute();
$resAsignados = $resAsignados->get_result();
while ($row = $resAsignados->fetch_assoc()) {
  $temasAsignados[] = $row['tema_id'];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nombre = trim($_POST['nombre']);
  $apellidos = trim($_POST['apellidos']);
  $nombre_tutor = trim($_POST['nombre_tutor'] ?? '');
  $fecha_nacimiento = $_POST['fecha_nacimiento'];
  $telefono = trim($_POST['telefono']);
  $mensualidad = floatval($_POST['mensualidad']);
  $temasSeleccionados = $_POST['temas'] ?? [];

  $update_stmt = $conexion->prepare("UPDATE alumnos SET 
                                      nombre = ?, 
                                      apellidos = ?, 
                                      nombre_tutor = ?,
                                      fecha_nacimiento = ?, 
                                      telefono = ?, 
                                      mensualidad = ?
                                      WHERE curp = ?");
  $update_stmt->bind_param("ssssdss", $nombre, $apellidos, $nombre_tutor, $fecha_nacimiento, $telefono, $mensualidad, $curp);

  if ($update_stmt->execute()) {
    // Actualizar los temas asignados
    $conexion->query("DELETE FROM alumnos_tema WHERE curp = '$curp'");

    if (!empty($temasSeleccionados)) {
      $stmtTema = $conexion->prepare("INSERT INTO alumnos_tema (curp, tema_id) VALUES (?, ?)");
      $stmtSubtemas = $conexion->prepare("SELECT id FROM subtemas WHERE tema_id = ?");
      $stmtProgreso = $conexion->prepare("INSERT INTO alumnos_subtema_progreso (alumnos_tema_id, subtema_id) VALUES (?, ?)");

      foreach ($temasSeleccionados as $tema_id) {
        $tema_id = intval($tema_id);
        $stmtTema->bind_param("si", $curp, $tema_id);
        $stmtTema->execute();
        $alumnos_tema_id = $conexion->insert_id;

        // Insertar progreso inicial para los subtemas de este tema
        $stmtSubtemas->bind_param("i", $tema_id);
        $stmtSubtemas->execute();
        $resSubtemas = $stmtSubtemas->get_result();
        while ($sub = $resSubtemas->fetch_assoc()) {
          $subtema_id = $sub['id'];
          $stmtProgreso->bind_param("ii", $alumnos_tema_id, $subtema_id);
          $stmtProgreso->execute();
        }
      }
    }

    $exito = "¡Datos actualizados correctamente!";
    // Actualizamos local
    $alumno = array_merge($alumno, [
      'nombre' => $nombre,
      'apellidos' => $apellidos,
      'nombre_tutor' => $nombre_tutor,
      'fecha_nacimiento' => $fecha_nacimiento,
      'telefono' => $telefono,
      'mensualidad' => $mensualidad
    ]);
    $temasAsignados = $temasSeleccionados;
  } else {
    $error = "Error al actualizar: " . $conexion->error;
  }
}

function calcularEdad($fecha)
{
  $nacimiento = new DateTime($fecha);
  $hoy = new DateTime();
  return $hoy->diff($nacimiento)->y;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Alumno</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    /* Igual a tu diseño verde dólar anterior */
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #ffffffff 0%, #ffffffff 100%);
      min-height: 100vh;
      margin: 0;
      display: flex;
      flex-direction: column;
      color: #f0f0f0;
    }

    main {
      flex-grow: 1;
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 2rem;
      padding: 2rem 1rem;
      max-width: 900px;
      margin: 0 auto;
    }

    form {
      background: #fff;
      border-radius: 16px;
      padding: 2.5rem 3rem;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
      width: 100%;
      max-width: 600px;
      position: relative;
      overflow: hidden;
      transition: box-shadow 0.3s ease;
      color: #222;
    }

    form:focus-within {
      box-shadow: 0 15px 50px rgba(25, 135, 84, 0.6);
    }

    label {
      font-weight: 600;
      color: #333;
    }

    input.form-control {
      border-radius: 10px;
      border: 2px solid #bbb;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      color: #333;
    }

    input.form-control:focus {
      border-color: #198754;
      box-shadow: 0 0 8px rgba(25, 135, 84, 0.6);
      outline: none;
    }

    .btn-primary {
      background: #198754;
      border: none;
      font-weight: 700;
      padding: 0.6rem 2rem;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(25, 135, 84, 0.4);
      transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.15s ease;
    }

    .btn-primary:hover {
      background: #157347;
      box-shadow: 0 8px 25px rgba(21, 115, 71, 0.7);
      transform: scale(1.05);
    }

    .btn-outline-secondary {
      border-radius: 12px;
      font-weight: 600;
      color: #198754;
      border-color: #198754;
      padding: 0.6rem 1.6rem;
    }

    .btn-outline-secondary:hover {
      background: #198754;
      color: white;
      border-color: #198754;
      box-shadow: 0 5px 15px rgba(25, 135, 84, 0.5);
    }

    .alert {
      border-radius: 12px;
      font-weight: 600;
      padding: 0.9rem 1.2rem;
      margin-bottom: 1.5rem;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }

    .alert-success {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <main class="container mt-5">
    <h2 class="mb-4" style="font-weight: 700; color: black;">Editar Información del Alumno</h2>
    <p class="text-muted">CURP: <strong><?= htmlspecialchars($alumno['curp']) ?></strong></p>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif (isset($exito)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="row g-3">
        <div class="col-md-6"><label for="nombre" class="form-label">Nombre(s)</label><input type="text" id="nombre"
            name="nombre" class="form-control" value="<?= htmlspecialchars($alumno['nombre']) ?>" required></div>
        <div class="col-md-6"><label for="apellidos" class="form-label">Apellidos</label><input type="text"
            id="apellidos" name="apellidos" class="form-control" value="<?= htmlspecialchars($alumno['apellidos']) ?>"
            required></div>
        <div class="col-md-6"><label for="nombre_tutor" class="form-label">Nombre del Padre/Tutor</label><input
            type="text" id="nombre_tutor" name="nombre_tutor" class="form-control"
            value="<?= htmlspecialchars($alumno['nombre_tutor']) ?>"></div>
        <div class="col-md-6"><label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label><input
            type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
            value="<?= htmlspecialchars($alumno['fecha_nacimiento']) ?>"
            onchange="document.getElementById('edad-actual').textContent = calcularEdad(this.value) + ' años'" required>
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-end"><label class="form-label">Edad Actual</label>
          <div class="form-control-plaintext" id="edad-actual"><?= calcularEdad($alumno['fecha_nacimiento']) ?> años
          </div>
        </div>
        <div class="col-md-6"><label for="telefono" class="form-label">Teléfono</label><input type="tel" id="telefono"
            name="telefono" class="form-control" value="<?= htmlspecialchars($alumno['telefono']) ?>"></div>
        <div class="col-md-6"><label for="mensualidad" class="form-label">Mensualidad ($)</label><input type="number"
            step="0.01" min="0" id="mensualidad" name="mensualidad" class="form-control"
            value="<?= htmlspecialchars($alumno['mensualidad']) ?>" required></div>
        <div class="col-md-12"><label class="form-label">Temas Asignados</label>
          <div class="p-3 rounded" style="background:#fff;">
            <div class="row">
              <?php foreach ($temasDisponibles as $tema): ?>
                <div class="col-md-4">
                  <div class="form-check"><input class="form-check-input" type="checkbox" name="temas[]"
                      value="<?= $tema['id'] ?>" id="tema<?= $tema['id'] ?>" <?= in_array($tema['id'], $temasAsignados) ? 'checked' : '' ?>><label class="form-check-label"
                      for="tema<?= $tema['id'] ?>"><?= htmlspecialchars($tema['nombre']) ?></label></div>
                </div>
              <?php endforeach; ?>
            </div><small class="text-muted">Puedes marcar o desmarcar los temas asignados a este alumno.</small>
          </div>
        </div>
      </div>
      <div class="mt-4 d-flex justify-content-between flex-wrap gap-3"><button type="submit" class="btn btn-primary"><i
            class="bi bi-save"></i> Guardar Cambios</button><a href="lista_alumnos.php"
          class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Listado</a></div>
    </form>
  </main>

  <script>
    function calcularEdad(fecha) {
      if (!fecha) return '';
      const nacimiento = new Date(fecha);
      const hoy = new Date();
      let edad = hoy.getFullYear() - nacimiento.getFullYear();
      const mes = hoy.getMonth() - nacimiento.getMonth();
      if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) { edad--; }
      return edad;
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>