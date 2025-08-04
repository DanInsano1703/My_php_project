<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

// Función para calcular edad
function calcularEdad($fecha)
{
  $nacimiento = new DateTime($fecha);
  $hoy = new DateTime();
  return $hoy->diff($nacimiento)->y;
}

// Obtener temas para el formulario
$temasDisponibles = [];
$resTemas = $conexion->query("SELECT id, nombre FROM temas");
if ($resTemas) {
  $temasDisponibles = $resTemas->fetch_all(MYSQLI_ASSOC);
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $curp = strtoupper(trim($_POST['curp']));
  $nombre = trim($_POST['nombre']);
  $apellidos = trim($_POST['apellidos']);
  $nombre_tutor = trim($_POST['nombre_tutor'] ?? '');
  $fecha_nacimiento = $_POST['fecha_nacimiento'];
  $telefono = trim($_POST['telefono']);
  $mensualidad = floatval($_POST['mensualidad']);
  $edad = calcularEdad($fecha_nacimiento);
  $temasSeleccionados = $_POST['temas'] ?? [];

  // Validar CURP
  if (!preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]{2}$/', $curp)) {
    $error = "CURP inválida. Debe tener 18 caracteres alfanuméricos.";
  } else {
    // Insertar alumno
    $sql = "INSERT INTO alumnos (curp, nombre, apellidos, nombre_tutor, fecha_nacimiento, telefono, mensualidad) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssd", $curp, $nombre, $apellidos, $nombre_tutor, $fecha_nacimiento, $telefono, $mensualidad);

    if ($stmt->execute()) {
      // Insertar relación con temas y subtemas si hay
      if (!empty($temasSeleccionados)) {
        $sqlTema = "INSERT INTO alumnos_tema (curp, tema_id) VALUES (?, ?)";
        $stmtTema = $conexion->prepare($sqlTema);

        $sqlSubtema = "SELECT id FROM subtemas WHERE tema_id = ?";
        $stmtSubtema = $conexion->prepare($sqlSubtema);

        $sqlProgreso = "INSERT INTO alumnos_subtema_progreso (alumnos_tema_id, subtema_id) VALUES (?, ?)";
        $stmtProgreso = $conexion->prepare($sqlProgreso);

        foreach ($temasSeleccionados as $tema_id) {
          $tema_id = intval($tema_id);
          $stmtTema->bind_param("si", $curp, $tema_id);
          $stmtTema->execute();
          $alumnos_tema_id = $conexion->insert_id;

          // Insertar progreso en subtemas de este tema
          $stmtSubtema->bind_param("i", $tema_id);
          $stmtSubtema->execute();
          $resultSubtema = $stmtSubtema->get_result();
          while ($subtema = $resultSubtema->fetch_assoc()) {
            $subtema_id = $subtema['id'];
            $stmtProgreso->bind_param("ii", $alumnos_tema_id, $subtema_id);
            $stmtProgreso->execute();
          }
        }
      }

      $exito = "¡Alumno registrado, temas asignados y progreso inicial creado correctamente!";
      $_POST = []; // Limpiar formulario
    } else {
      $error = "Error: " . $conexion->error;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Registro de Alumnos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
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

  <main>
    <div class="container mt-5">
      <h2
        style="color: #000000; font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 2rem; text-align: center; position: relative; padding-bottom: 0.5rem;">
        Nuevo Alumno
      </h2>

      <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif (isset($exito)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
      <?php endif; ?>

      <form method="post" novalidate id="registroForm">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="curp" class="form-label">CURP</label>
            <input type="text" class="form-control" id="curp" name="curp"
              value="<?= htmlspecialchars($_POST['curp'] ?? '') ?>" pattern="[A-Za-z0-9]{18}" maxlength="18"
              title="18 caracteres alfanuméricos" required placeholder="Ejemplo: ABCD123456HDFRRT09" autocomplete="off"
              autofocus>
          </div>

          <div class="col-md-6">
            <label for="nombre" class="form-label">Nombre(s)</label>
            <input type="text" class="form-control" id="nombre" name="nombre"
              value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required placeholder="Nombre(s)">
          </div>

          <div class="col-md-6">
            <label for="apellidos" class="form-label">Apellidos</label>
            <input type="text" class="form-control" id="apellidos" name="apellidos"
              value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>" required placeholder="Apellidos">
          </div>

          <div class="col-md-6">
            <label for="nombre_tutor" class="form-label">Nombre del Padre/Tutor</label>
            <input type="text" class="form-control" id="nombre_tutor" name="nombre_tutor"
              value="<?= htmlspecialchars($_POST['nombre_tutor'] ?? '') ?>" placeholder="(Opcional)">
          </div>

          <div class="col-md-3">
            <label for="fecha_nacimiento" class="form-label">F. Nacimiento</label>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
              value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>"
              onchange="document.getElementById('edad').value = calcularEdad(this.value)" required>
          </div>

          <div class="col-md-3">
            <label for="edad" class="form-label">Edad</label>
            <input type="text" class="form-control" id="edad" readonly
              value="<?= isset($_POST['fecha_nacimiento']) ? calcularEdad($_POST['fecha_nacimiento']) . ' años' : '' ?>">
          </div>

          <div class="col-md-6">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="telefono" name="telefono"
              value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" placeholder="(Opcional)">
          </div>

          <div class="col-md-6">
            <label for="mensualidad" class="form-label">Mensualidad ($)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="mensualidad" name="mensualidad"
              value="<?= htmlspecialchars($_POST['mensualidad'] ?? '') ?>" required placeholder="Ej. 150.00">
          </div>

          <div class="col-md-12">
            <label class="form-label">Asignar Temas</label>
            <div class="p-3 rounded" style="background:#fff;">
              <div class="row">
                <?php foreach ($temasDisponibles as $tema): ?>
                  <div class="col-md-4">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="temas[]" value="<?= $tema['id'] ?>"
                        id="tema<?= $tema['id'] ?>" <?= (isset($_POST['temas']) && in_array($tema['id'], $_POST['temas'])) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="tema<?= $tema['id'] ?>">
                        <?= htmlspecialchars($tema['nombre']) ?>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <small class="text-muted">Puedes seleccionar uno o varios temas.</small>
            </div>
          </div>

        </div>

        <div class="mt-4 d-flex justify-content-center flex-wrap gap-3">
          <button type="submit" class="btn btn-primary me-3">Registrar</button>
          <button type="reset" class="btn btn-outline-secondary" onclick="resetEdad()">Limpiar</button>
        </div>
      </form>
    </div>
  </main>

  <br>
  <br>

  <script>
    function calcularEdad(fecha) {
      if (!fecha) return '';
      const nacimiento = new Date(fecha);
      const hoy = new Date();
      let edad = hoy.getFullYear() - nacimiento.getFullYear();
      const mes = hoy.getMonth() - nacimiento.getMonth();
      if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
      }
      return edad + " años";
    }
    function resetEdad() {
      document.getElementById('edad').value = '';
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>