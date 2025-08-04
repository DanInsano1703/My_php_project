<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
date_default_timezone_set('America/Mexico_City');
require_once __DIR__ . '/includes/bd.php';

$fecha = $_GET['fecha'] ?? date('Y-m-d');
$tema_id = isset($_GET['tema_id']) ? intval($_GET['tema_id']) : 0;

// Obtener lista de temas
$resTemas = $conexion->query("SELECT id, nombre FROM temas ORDER BY nombre");
$temas = [];
while ($row = $resTemas->fetch_assoc()) {
    $temas[$row['id']] = $row['nombre'];
}

// Obtener temas con lista pasada en la fecha actual
$temasConLista = [];
$stmt = $conexion->prepare("SELECT DISTINCT tema_id FROM asistencia WHERE fecha = ?");
$stmt->bind_param("s", $fecha);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    $temasConLista[] = intval($r['tema_id']);
}
$stmt->close();

// Si no hay tema seleccionado pero hay lista hoy, cargar el primero con lista hoy
if ($tema_id === 0 && !empty($temasConLista)) {
    $tema_id = $temasConLista[0];
}

// Guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asistencia'], $_POST['tema_id'], $_POST['fecha'])) {
    $tema_id = intval($_POST['tema_id']);
    $fecha = $_POST['fecha'];

    foreach ($_POST['asistencia'] as $curp => $estado) {
        if (!in_array($estado, ['asistio', 'justifico', 'falto'])) continue;

        $sql = "
            INSERT INTO asistencia (alumno_curp, tema_id, fecha, estado)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE estado = VALUES(estado), registrado_en = CURRENT_TIMESTAMP
        ";
        $stmtUpd = $conexion->prepare($sql);
        $stmtUpd->bind_param("siss", $curp, $tema_id, $fecha, $estado);
        $stmtUpd->execute();
        $stmtUpd->close();
    }

    header("Location: {$_SERVER['PHP_SELF']}?tema_id=$tema_id&fecha=$fecha&success=1");
    exit;
}

// Obtener asistencia para esa fecha y tema
$asistencias = [];
if ($tema_id > 0) {
    $sql = "
        SELECT a.curp, CONCAT(a.nombre, ' ', a.apellidos) AS nombre_completo, asi.estado, a.activo
        FROM asistencia asi
        INNER JOIN alumnos a ON asi.alumno_curp = a.curp
        WHERE asi.tema_id = ? AND asi.fecha = ?
        ORDER BY a.nombre, a.apellidos
    ";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("is", $tema_id, $fecha);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $asistencias[$row['curp']] = [
            'nombre' => $row['nombre_completo'],
            'estado' => $row['estado'],
            'activo' => $row['activo']
        ];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Asistencia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
 body { background: #f8f9fa; }

h2 {
  text-align: center;
  margin-bottom: 30px;
  color: black;
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

/* Verde dollar para asistió */
.status-radio.status-asistio label {
  border-color: #198754;
}

/* Amarillo para justificó */
.status-radio.status-justifico label {
  border-color: #ffc107;
}

/* Rojo para faltó */
.status-radio.status-falto label {
  border-color: #dc3545;
}

.status-radio input[type="radio"]:checked + label {
  background: currentColor;
  border-color: currentColor;
}

.status-radio.status-asistio input:checked + label {
  color: #198754;
}

.status-radio.status-justifico input:checked + label {
  color: #ffc107;
}

.status-radio.status-falto input:checked + label {
  color: #dc3545;
}

.tema-opcion {
  margin: 2px;
  padding: 5px 12px;
  border-radius: 20px;
  background: #eee;
  display: inline-block;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  color: #333;
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

    <h2>Editar Asistencia</h2>

    <div class="section-box mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Clase (Tema)</label><br>
                <?php foreach ($temas as $id => $nombre): ?>
                    <a href="?tema_id=<?= $id ?>&fecha=<?= htmlspecialchars($fecha) ?>"
                       class="tema-opcion <?= ($tema_id==$id?'activo':'') ?> <?= in_array($id, $temasConLista)?'lista-pasada':'' ?>">
                        <?= htmlspecialchars($nombre) ?> <?= in_array($id, $temasConLista)?'✅':'' ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>" class="form-control" onchange="this.form.submit()">
                <input type="hidden" name="tema_id" value="<?= $tema_id ?>">
            </div>
        </form>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">Asistencia actualizada correctamente.</div>
    <?php endif; ?>

    <?php if ($tema_id > 0): ?>
        <div class="section-box">
            <?php if (empty($asistencias)): ?>
                <div class="alert alert-info text-center">No hay asistencia registrada para esta fecha y clase.</div>
            <?php else: ?>
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="tema_id" value="<?= $tema_id ?>">
                    <input type="hidden" name="fecha" value="<?= htmlspecialchars($fecha) ?>">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-4">Alumno</th>
                                    <th>Asistió</th>
                                    <th>Justificó</th>
                                    <th>Faltó</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asistencias as $curp => $info): ?>
                                    <tr class="<?= $info['activo'] ? '' : 'text-muted' ?>">
                                        <td class="text-start ps-4" <?= $info['activo'] ? '' : 'title="Alumno dado de baja"' ?>>
                                            <?= $info['activo'] 
                                                ? htmlspecialchars($info['nombre'])
                                                : '<del>'.htmlspecialchars($info['nombre']).' 🚫</del>' ?>
                                        </td>
                                        <td class="status-radio status-asistio">
                                            <input type="radio" name="asistencia[<?= $curp ?>]" id="a_<?= $curp ?>" value="asistio" <?= $info['estado']=='asistio'?'checked':'' ?>>
                                            <label for="a_<?= $curp ?>"></label>
                                        </td>
                                        <td class="status-radio status-justifico">
                                            <input type="radio" name="asistencia[<?= $curp ?>]" id="j_<?= $curp ?>" value="justifico" <?= $info['estado']=='justifico'?'checked':'' ?>>
                                            <label for="j_<?= $curp ?>"></label>
                                        </td>
                                        <td class="status-radio status-falto">
                                            <input type="radio" name="asistencia[<?= $curp ?>]" id="f_<?= $curp ?>" value="falto" <?= $info['estado']=='falto'?'checked':'' ?>>
                                            <label for="f_<?= $curp ?>"></label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-success mt-4 px-5 mx-auto d-block">Guardar Cambios</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
