<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/includes/bd.php';

$fechaHoy = date('Y-m-d');

// Obtener todos los temas (clases)
$result = $conexion->query("SELECT id, nombre FROM temas ORDER BY nombre");
$temas = [];
while ($row = $result->fetch_assoc()) {
    $temas[$row['id']] = $row['nombre'];
}

// Obtener ids de temas con lista pasada HOY
$stmt = $conexion->prepare("
    SELECT DISTINCT tema_id 
    FROM asistencia
    WHERE fecha = ?
");
$stmt->bind_param("s", $fechaHoy);
$stmt->execute();
$res = $stmt->get_result();
$temasConListaHoy = [];
while ($r = $res->fetch_assoc()) {
    $temasConListaHoy[] = intval($r['tema_id']);
}
$stmt->close();

$temaSeleccionado = isset($_GET['tema_id']) ? intval($_GET['tema_id']) : 0;

// Guardar lista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema_id'], $_POST['asistencia'])) {
    $tema_id = intval($_POST['tema_id']);
    $fecha = $_POST['fecha'] ?? '';

    if ($fecha !== $fechaHoy) {
        die("Solo se puede pasar lista para la fecha de hoy.");
    }

    foreach ($_POST['asistencia'] as $curp => $estado) {
        if (!in_array($estado, ['asistio', 'justifico', 'falto'])) continue;

        $stmtIns = $conexion->prepare("
            INSERT INTO asistencia (alumno_curp, tema_id, fecha, estado)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE estado = VALUES(estado), registrado_en = CURRENT_TIMESTAMP
        ");
        $stmtIns->bind_param("siss", $curp, $tema_id, $fecha, $estado);
        $stmtIns->execute();
        $stmtIns->close();
    }

    header("Location: pasar_lista.php?tema_id=$tema_id&success=1");
    exit;
}

// Obtener alumnos asignados al tema seleccionado
$alumnos = [];
$asistenciaHoy = [];
if ($temaSeleccionado > 0) {
    $stmt = $conexion->prepare("
        SELECT a.curp, a.nombre, a.apellidos
        FROM alumnos_tema at
        INNER JOIN alumnos a ON at.curp = a.curp
        WHERE at.tema_id = ? AND a.activo = 1
        ORDER BY a.nombre, a.apellidos
    ");
    $stmt->bind_param("i", $temaSeleccionado);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) {
        $alumnos[$fila['curp']] = $fila;
    }
    $stmt->close();

    if (!empty($alumnos)) {
        $placeholders = implode(',', array_fill(0, count($alumnos), '?'));
        $tipos = str_repeat('s', count($alumnos));
        $params = array_merge([$temaSeleccionado, $fechaHoy], array_keys($alumnos));

        $sql = "
            SELECT alumno_curp, estado 
            FROM asistencia
            WHERE tema_id = ? AND fecha = ? AND alumno_curp IN ($placeholders)
        ";

        $stmt2 = $conexion->prepare($sql);
        $refs = [];
        $tiposBind = 'is' . $tipos;
        foreach ($params as $k => $v) $refs[$k] = &$params[$k];
        call_user_func_array([$stmt2, 'bind_param'], array_merge([$tiposBind], $refs));

        $stmt2->execute();
        $resAsist = $stmt2->get_result();
        while ($r = $resAsist->fetch_assoc()) {
            $asistenciaHoy[$r['alumno_curp']] = $r['estado'];
        }
        $stmt2->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Pasar Lista - Academia Música</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
body {
  background: #f8f9fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #0f3d1e;
}
h2 {
  text-align: center;
  margin-bottom: 30px;
  color: #198754;
  font-weight: bold;
}
.section-box {
  background: white;
  border-radius: 12px;
  box-shadow: 0 0 8px rgba(0,0,0,0.1);
  padding: 20px;
  margin-bottom: 25px;
}
.status-radio input[type="radio"] {
  display: none;
}
.status-radio label {
  display: inline-block;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 2px solid #ccc;
  cursor: pointer;
  transition: 0.3s;
}
.status-radio.status-asistio label { border-color: #198754; }
.status-radio.status-justifico label { border-color: #ffc107; }
.status-radio.status-falto label { border-color: #dc3545; }
.status-radio input[type="radio"]:checked + label {
  background: currentColor;
  border-color: currentColor;
}
.status-radio.status-asistio input:checked + label { color: #198754; }
.status-radio.status-justifico input:checked + label { color: #ffc107; }
.status-radio.status-falto input:checked + label { color: #dc3545; }
.tema-opcion {
  margin: 3px;
  padding: 6px 14px;
  border-radius: 20px;
  background: #eee;
  display: inline-block;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  color: #333;
  transition: background 0.3s, color 0.3s;
}
.tema-opcion.lista-pasada {
  box-shadow: 0 0 5px 1px #198754;
}
.tema-opcion.activo {
  background: #198754;
  color: white;
}
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main class="container py-4">
    <h2>Pasar Lista - <?= date('d/m/Y') ?></h2>

    <div class="mb-4 section-box">
        <strong>Temas (clases):</strong><br>
        <?php foreach ($temas as $id => $nombre): ?>
            <a href="?tema_id=<?= $id ?>"
               class="tema-opcion <?= ($id === $temaSeleccionado ? 'activo' : '') ?> <?= in_array($id, $temasConListaHoy) ? 'lista-pasada' : '' ?>">
                <?= htmlspecialchars($nombre) ?> <?= in_array($id, $temasConListaHoy) ? '✅' : '' ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">Asistencia guardada correctamente.</div>
    <?php endif; ?>

    <?php if ($temaSeleccionado > 0): ?>
        <div class="section-box">
            <?php if (empty($alumnos)): ?>
                <div class="alert alert-info text-center">No hay alumnos activos asignados a este tema.</div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="tema_id" value="<?= $temaSeleccionado ?>">
                    <input type="hidden" name="fecha" value="<?= $fechaHoy ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-4">Alumno</th>
                                    <th>Asistió</th>
                                    <th>Justificó</th>
                                    <th>Faltó</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumnos as $curp => $alumno):
                                    $estado = $asistenciaHoy[$curp] ?? 'falto'; ?>
                                    <tr>
                                        <td class="text-start ps-4"><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></td>
                                        <td class="status-radio status-asistio">
                                            <input type="radio" name="asistencia[<?= $curp ?>]" id="a_<?= $curp ?>" value="asistio" <?= $estado==='asistio'?'checked':'' ?>>
                                            <label for="a_<?= $curp ?>"></label>
                                        </td>
                                        <td class="status-radio status-justifico">
                                            <input type="radio" name="asistencia[<?= $curp ?>]" id="j_<?= $curp ?>" value="justifico" <?= $estado==='justifico'?'checked':'' ?>>
                                            <label for="j_<?= $curp ?>"></label>
                                        </td>
                                        <td class="status-radio status-falto">
                                            <input type="radio" name="asistencia[<?= $curp ?>]" id="f_<?= $curp ?>" value="falto" <?= $estado==='falto'?'checked':'' ?>>
                                            <label for="f_<?= $curp ?>"></label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success mt-4 px-5 d-block mx-auto">Guardar Asistencia</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
