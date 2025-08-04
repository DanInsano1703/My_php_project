<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

function escape($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}

// Obtener temas
$temas = $conexion->query("SELECT id, nombre FROM temas ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
$tema_id = $_REQUEST['tema_id'] ?? null;
if (!$tema_id && count($temas) > 0) {
  $tema_id = $temas[0]['id'];
}

// Obtener sesiones para el tema seleccionado
$stmt = $conexion->prepare("
    SELECT h.id, h.dia_semana, h.hora_inicio, h.hora_fin 
    FROM horarios h 
    WHERE h.tema_id = ? 
    ORDER BY FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'), h.hora_inicio
");
$stmt->bind_param('i', $tema_id);
$stmt->execute();
$sesiones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener profesores asignados por sesión
$profesor_por_sesion = [];
$res = $conexion->query("
    SELECT hp.horario_id, p.nombre AS profesor_nombre
    FROM horario_profesores hp
    JOIN profesores p ON hp.profesor_id = p.id
");
while ($row = $res->fetch_assoc()) {
  $horario_id = $row['horario_id'];
  if (!isset($profesor_por_sesion[$horario_id])) {
    $profesor_por_sesion[$horario_id] = [];
  }
  $profesor_por_sesion[$horario_id][] = $row['profesor_nombre'];
}

// Obtener alumnos inscritos en el tema
$stmt = $conexion->prepare("
    SELECT a.curp, CONCAT(a.nombre, ' ', a.apellidos) AS nombre_completo 
    FROM alumnos_tema at 
    JOIN alumnos a ON at.curp = a.curp 
    WHERE at.tema_id = ? AND a.activo = 1
    ORDER BY a.nombre, a.apellidos
");
$stmt->bind_param('i', $tema_id);
$stmt->execute();
$alumnos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener alumnos asignados por sesión
$asignados_por_sesion = [];
$stmt = $conexion->prepare("SELECT alumno_curp FROM alumno_sesion WHERE horario_id = ?");
foreach ($sesiones as $sesion) {
  $stmt->bind_param('i', $sesion['id']);
  $stmt->execute();
  $res = $stmt->get_result();
  $asignados = [];
  while ($row = $res->fetch_assoc()) {
    $asignados[] = $row['alumno_curp'];
  }
  $asignados_por_sesion[$sesion['id']] = $asignados;
}
$stmt->close();

// Procesar asignaciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignaciones'])) {
  $asignaciones = $_POST['asignaciones'];

  $conexion->begin_transaction();
  try {
    // Eliminar todas las asignaciones existentes para este tema
    $idsSesiones = array_column($sesiones, 'id');
    if (count($idsSesiones) > 0) {
      $idsPlaceholder = implode(',', array_fill(0, count($idsSesiones), '?'));
      $stmt_del = $conexion->prepare("DELETE FROM alumno_sesion WHERE horario_id IN ($idsPlaceholder)");
      $stmt_del->bind_param(str_repeat('i', count($idsSesiones)), ...$idsSesiones);
      $stmt_del->execute();
      $stmt_del->close();
    }

    // Insertar nuevas asignaciones
    $stmt_ins = $conexion->prepare("INSERT INTO alumno_sesion (alumno_curp, horario_id) VALUES (?, ?)");
    foreach ($asignaciones as $horario_id => $curps_string) {
      if (!empty($curps_string)) {
        $curps = explode(',', $curps_string);
        foreach ($curps as $curp) {
          $curp = trim($curp);
          if (!empty($curp)) {
            $stmt_ins->bind_param('si', $curp, $horario_id);
            $stmt_ins->execute();
          }
        }
      }
    }
    $stmt_ins->close();
    $conexion->commit();

    header("Location: " . $_SERVER['PHP_SELF'] . "?tema_id=" . intval($tema_id) . "&guardado=1");
    exit;
  } catch (Exception $e) {
    $conexion->rollback();
    $mensaje_error = "Error al guardar asignaciones: " . $e->getMessage();
  }
}

$mensaje_guardado = isset($_GET['guardado']) ? "Las asignaciones se han guardado correctamente." : "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Asignar alumnos a sesiones</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      color: #333;
      margin: 0;
      padding: 20px;
    }

    .contenedor-principal {
      max-width: 1200px;
      margin: auto;
    }

    h1,
    h2 {
      color: #2c3e50;
      margin-bottom: 15px;
    }

    h3 {
      margin: 8px 0;
    }

    .selector-tema {
      margin-bottom: 25px;
    }

    .selector-tema select {
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .contenedor-drag-drop {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .panel {
      flex: 1;
      min-width: 320px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .sesion {
      border: 2px dashed #3498db;
      border-radius: 8px;
      padding: 12px;
      margin-bottom: 15px;
      background: #ecf6fb;
      transition: background 0.3s;
    }

    .sesion.dragover {
      background: #d6eaf8;
    }

    .profesor-asignado {
      font-size: 0.9em;
      color: #555;
      margin-top: 4px;
      margin-bottom: 8px;
    }

    .alumnos-asignados,
    #alumnos-disponibles {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 8px;
    }

    .alumno-chip,
    .alumno-item {
      background: #3498db;
      color: white;
      padding: 6px 12px;
      border-radius: 20px;
      cursor: grab;
      user-select: none;
      display: flex;
      align-items: center;
      transition: background 0.3s;
    }

    .alumno-chip .remove {
      margin-left: 8px;
      cursor: pointer;
      font-weight: bold;
    }

    .alumno-chip:hover,
    .alumno-item:hover {
      background: #2980b9;
    }

    .alumno-item:active {
      cursor: grabbing;
    }

    .contador {
      margin-top: 8px;
      font-size: 0.9em;
      color: #666;
    }

    .sin-contenido {
      text-align: center;
      color: #888;
      font-style: italic;
      padding: 15px 0;
    }

    .mensaje.exito {
      background: #d4edda;
      color: #155724;
      padding: 10px;
      border-left: 5px solid #28a745;
      margin-bottom: 15px;
      border-radius: 4px;
    }

    .mensaje.error {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-left: 5px solid #dc3545;
      margin-bottom: 15px;
      border-radius: 4px;
    }

    .btn-guardar {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 25px;
      background: #27ae60;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn-guardar:hover {
      background: #1e8449;
    }

    .btn-desasignar-todo {
      background: #e74c3c;
      color: #fff;
      border: none;
      padding: 10px 18px;
      font-size: 15px;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 20px;
      transition: background 0.3s;
    }

    .btn-desasignar-todo:hover {
      background: #c0392b;
    }
     .panel {
    max-height: 600px;
    overflow-y: auto;
    padding-right: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
  }
  </style>
  <script>
    // Variables globales
    let contadorAlumnos = {};

    // Permitir soltar
    function allowDrop(ev) {
      ev.preventDefault();
      ev.currentTarget.classList.add('dragover');
    }

    function dragLeave(ev) {
      ev.currentTarget.classList.remove('dragover');
    }

    // Empezar a arrastrar alumno
    function drag(ev) {
      ev.dataTransfer.setData("curp", ev.target.getAttribute('data-curp'));
      ev.dataTransfer.setData("nombre", ev.target.textContent.trim());
      ev.dataTransfer.effectAllowed = "move";
      ev.target.classList.add('dragging');
    }

    function dragEnd(ev) {
      ev.target.classList.remove('dragging');
      // Remover clase dragover de todas las sesiones
      document.querySelectorAll('.sesion').forEach(s => s.classList.remove('dragover'));
    }

    // Soltar alumno en sesión
    function drop(ev) {
      ev.preventDefault();
      ev.currentTarget.classList.remove('dragover');

      const curp = ev.dataTransfer.getData("curp");
      const nombre = ev.dataTransfer.getData("nombre");
      const sesionDiv = ev.currentTarget;
      const container = sesionDiv.querySelector('.alumnos-asignados');

      if (!container) return;

      // Verificar si ya está asignado
      if ([...container.children].some(chip => chip.getAttribute('data-curp') === curp)) {
        alert("Este alumno ya está asignado a esta sesión.");
        return;
      }

      // Crear chip del alumno
      const chip = crearChipAlumno(curp, nombre);
      container.appendChild(chip);

      // Remover de disponibles
      const alumnoElemento = document.querySelector(`#alumnos-disponibles .alumno-item[data-curp='${curp}']`);
      if (alumnoElemento) {
        alumnoElemento.remove();
      }

      // Actualizar input oculto
      actualizarInputOculto(sesionDiv);
      actualizarContadores();
    }

    // Crear chip de alumno
    function crearChipAlumno(curp, nombre) {
      const chip = document.createElement('span');
      chip.className = 'alumno-chip';
      chip.setAttribute('data-curp', curp);
      chip.textContent = nombre;

      const btnRemove = document.createElement('span');
      btnRemove.textContent = '×';
      btnRemove.className = 'remove';
      btnRemove.title = "Quitar alumno de esta sesión";
      btnRemove.onclick = function (e) {
        e.stopPropagation();
        quitarAlumno(chip);
      };

      chip.appendChild(btnRemove);
      return chip;
    }

    // Quitar alumno de sesión
    function quitarAlumno(chip) {
      const sesionDiv = chip.closest('.sesion');
      const curp = chip.getAttribute('data-curp');
      const nombre = chip.textContent.replace('×', '').trim();

      // Crear elemento en disponibles
      const disponibles = document.getElementById('alumnos-disponibles');
      const nuevoAlumno = document.createElement('div');
      nuevoAlumno.className = 'alumno-item';
      nuevoAlumno.setAttribute('draggable', 'true');
      nuevoAlumno.setAttribute('data-curp', curp);
      nuevoAlumno.textContent = nombre;
      nuevoAlumno.ondragstart = drag;
      nuevoAlumno.ondragend = dragEnd;

      disponibles.appendChild(nuevoAlumno);

      // Remover chip
      chip.remove();

      // Actualizar input oculto
      actualizarInputOculto(sesionDiv);
      actualizarContadores();
    }

    // Actualizar input oculto
    function actualizarInputOculto(sesionDiv) {
      const container = sesionDiv.querySelector('.alumnos-asignados');
      const hiddenInput = sesionDiv.querySelector('input[type=hidden]');

      if (!container || !hiddenInput) return;

      const curps = [...container.children].map(chip => chip.getAttribute('data-curp')).filter(c => c);
      hiddenInput.value = curps.join(',');
    }

    // Actualizar contadores
    function actualizarContadores() {
      // Contar alumnos disponibles
      const disponibles = document.querySelectorAll('#alumnos-disponibles .alumno-item').length;
      const contadorDisponibles = document.getElementById('contador-disponibles');
      if (contadorDisponibles) {
        contadorDisponibles.textContent = `${disponibles} alumno${disponibles !== 1 ? 's' : ''} disponible${disponibles !== 1 ? 's' : ''}`;
      }

      // Contar alumnos por sesión
      document.querySelectorAll('.sesion').forEach(sesion => {
        const asignados = sesion.querySelectorAll('.alumno-chip').length;
        const contador = sesion.querySelector('.contador');
        if (contador) {
          contador.textContent = `${asignados} alumno${asignados !== 1 ? 's' : ''} asignado${asignados !== 1 ? 's' : ''}`;
        }
      });
    }

    // Cambiar tema
    function cambiarTema(select) {
      window.location.href = "?tema_id=" + select.value;
    }

    // Inicialización
    window.onload = function () {
      // Configurar eventos de drag and drop
      document.querySelectorAll('.alumno-item').forEach(item => {
        item.ondragstart = drag;
        item.ondragend = dragEnd;
      });

      document.querySelectorAll('.sesion').forEach(sesion => {
        sesion.ondragover = allowDrop;
        sesion.ondragleave = dragLeave;
        sesion.ondrop = drop;

        // Agregar contador a cada sesión
        const contador = document.createElement('div');
        contador.className = 'contador';
        sesion.appendChild(contador);
      });

      // Agregar contador a disponibles
      const disponibles = document.getElementById('alumnos-disponibles');
      const contador = document.createElement('div');
      contador.className = 'contador';
      contador.id = 'contador-disponibles';
      disponibles.appendChild(contador);

      // Actualizar contadores iniciales
      actualizarContadores();

      // Inicializar inputs ocultos
      document.querySelectorAll('.sesion').forEach(sesionDiv => {
        actualizarInputOculto(sesionDiv);
      });
    };

    function desasignarTodos() {
      // Encontrar todas las sesiones
      document.querySelectorAll('.sesion').forEach(sesionDiv => {
        const container = sesionDiv.querySelector('.alumnos-asignados');
        if (!container) return;

        // Quitar todos los alumnos de esta sesión
        [...container.children].forEach(chip => {
          const curp = chip.getAttribute('data-curp');
          const nombre = chip.textContent.replace('×', '').trim();

          // Regresar a disponibles
          const disponibles = document.getElementById('alumnos-disponibles');
          const nuevoAlumno = document.createElement('div');
          nuevoAlumno.className = 'alumno-item';
          nuevoAlumno.setAttribute('draggable', 'true');
          nuevoAlumno.setAttribute('data-curp', curp);
          nuevoAlumno.textContent = nombre;
          nuevoAlumno.ondragstart = drag;
          nuevoAlumno.ondragend = dragEnd;

          disponibles.appendChild(nuevoAlumno);

          // Remover del contenedor
          chip.remove();
        });

        // Actualizar input oculto
        actualizarInputOculto(sesionDiv);
      });

      actualizarContadores();
    }

  </script>
</head>
<?php include 'navbar3.php'; ?>

<body>
  <br>
  <div class="contenedor-principal">
    <h1><i class="bi bi-people"></i> Asignar Alumnos a Sesiones</h1>

    <?php if ($mensaje_guardado): ?>
      <div class="mensaje exito"><i class="bi bi-check-circle"></i> <?= escape($mensaje_guardado) ?></div>
    <?php endif; ?>
    <?php if (isset($mensaje_error)): ?>
      <div class="mensaje error"><i class="bi bi-exclamation-triangle"></i> <?= escape($mensaje_error) ?></div>
    <?php endif; ?>

    <div class="selector-tema">
      <label for="selector-tema">Selecciona un tema:</label>
      <select id="selector-tema" onchange="location.href='?tema_id='+this.value">
        <?php foreach ($temas as $tema): ?>
          <option value="<?= escape($tema['id']) ?>" <?= $tema['id'] == $tema_id ? 'selected' : '' ?>>
            <?= escape($tema['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="button" class="btn-desasignar-todo" onclick="desasignarTodos()">
      <i class="bi bi-trash"></i> Remover alumnos de todas las sesiones de este tema
    </button>

    <form method="POST">
      <div class="contenedor-drag-drop">
        <div class="panel">
          <h2><i class="bi bi-calendar-event"></i> Sesiones del Tema</h2>
          <?php if (count($sesiones) === 0): ?>
            <div class="sin-contenido"><i class="bi bi-info-circle"></i> No hay sesiones.</div>
          <?php else: ?>
            <?php foreach ($sesiones as $sesion): ?>
              <div class="sesion">
                <h3>
                  <i class="bi bi-clock"></i>
                  <?= escape($sesion['dia_semana']) ?>
                  <?= date("g:i A", strtotime($sesion['hora_inicio'])) ?> -
                  <?= date("g:i A", strtotime($sesion['hora_fin'])) ?>

                  <div class="profesor-asignado">
                    <i class="bi bi-person-badge"></i>
                    <?php if (!empty($profesor_por_sesion[$sesion['id']])): ?>
                      <?= escape(implode(', ', $profesor_por_sesion[$sesion['id']])) ?>
                    <?php else: ?>
                      <em>Sin profesor asignado</em>
                    <?php endif; ?>
                  </div>
                  <div class="alumnos-asignados">
                    <?php
                    $curpsAsignados = $asignados_por_sesion[$sesion['id']] ?? [];
                    foreach ($curpsAsignados as $curpAsignado):
                      foreach ($alumnos as $alumno) {
                        if ($alumno['curp'] === $curpAsignado) {
                          ?>
                          <span class="alumno-chip" data-curp="<?= escape($curpAsignado) ?>">
                            <?= escape($alumno['nombre_completo']) ?>
                            <span class="remove" onclick="quitarAlumno(this.parentElement)">×</span>
                          </span>
                          <?php
                          break;
                        }
                      }
                    endforeach;
                    ?>
                  </div>
                  <input type="hidden" name="asignaciones[<?= escape($sesion['id']) ?>]" value="">
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="panel">
          <h2><i class="bi bi-person-lines-fill"></i> Alumnos por asignar</h2>
          <div id="alumnos-disponibles">
            <?php
            $curpsAsignados = [];
            foreach ($asignados_por_sesion as $asignados) {
              $curpsAsignados = array_merge($curpsAsignados, $asignados);
            }
            foreach ($alumnos as $alumno):
              if (!in_array($alumno['curp'], $curpsAsignados)):
                ?>
                <div class="alumno-item" draggable="true" data-curp="<?= escape($alumno['curp']) ?>">
                  <?= escape($alumno['nombre_completo']) ?>
                </div>
              <?php endif; endforeach; ?>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-guardar"><i class="bi bi-save"></i> Guardar Asignaciones</button>
    </form>
  </div>
</body>

</html>