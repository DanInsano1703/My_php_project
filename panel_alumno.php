<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

$curp = $_GET['curp'] ?? '';
if (!$curp)
    die("Alumno no especificado.");

// Obtener info alumno
$stmt = $conexion->prepare("SELECT * FROM alumnos WHERE curp = ?");
$stmt->bind_param("s", $curp);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();
if (!$alumno)
    die("Alumno no encontrado.");

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

// Obtener estado de pago del mes seleccionado
$stmtPago = $conexion->prepare("SELECT 1 FROM pagos_mensualidad WHERE alumno_curp = ? AND año = ? AND mes = ?");
$stmtPago->bind_param("sii", $curp, $year, $month);
$stmtPago->execute();
$pagoHoy = $stmtPago->get_result()->num_rows > 0;

// Obtener asistencia detallada por día y tema para el alumno en el mes
$sql = "
    SELECT asi.fecha, t.nombre AS tema_nombre, asi.estado
    FROM asistencia asi
    LEFT JOIN temas t ON asi.tema_id = t.id
    WHERE asi.alumno_curp = ? AND YEAR(asi.fecha) = ? AND MONTH(asi.fecha) = ?
    ORDER BY asi.fecha, t.nombre
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sii", $curp, $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$eventos = []; // Por fecha, arreglo de temas con estado
while ($row = $result->fetch_assoc()) {
    $fecha = $row['fecha'];
    $tema = $row['tema_nombre'] ?? 'Sin tema';
    $estado = $row['estado'];
    $eventos[$fecha][] = ['tema' => $tema, 'estado' => $estado];
}

function dias_del_mes($mes, $año)
{
    return cal_days_in_month(CAL_GREGORIAN, $mes, $año);
}
$dias_semana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
$primer_dia_mes = date('N', strtotime("$year-$month-01"));
$total_dias = dias_del_mes($month, $year);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Panel Alumno - Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .calendario {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .calendario th,
        .calendario td {
            border: 1px solid #ddd;
            width: 40px;
            height: 40px;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            border-radius: 6px;
            cursor: default;
            position: relative;
        }

        .calendario td.dia.registro {
            background: #0d6efd;
            color: white;
        }

        .calendario td.dia.sin_registro {
            background: #f8f9fa;
            color: #6c757d;
        }

        .color-box {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-right: 5px;
            vertical-align: middle;
        }

        .tooltip-inner {
            max-width: 250px;
            text-align: left;
            white-space: pre-line;
        }

        .estado-pago-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding: 2rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
        }

        .pagado {
            background-color: #198754;
        }

        .no-pagado {
            background-color: #dc3545;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="container mt-4 mb-5">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información del Alumno</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong>
                            <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></p>
                        <?php if ($tipo === 'admin'): ?>
                            <p><strong>CURP:</strong> <?= htmlspecialchars($alumno['curp']) ?></p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($alumno['telefono']) ?></p>
                            <?php if (!empty($alumno['nombre_tutor'])): ?>
                                <p><strong>Nombre del Tutor:</strong> <?= htmlspecialchars($alumno['nombre_tutor']) ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p><strong>Fecha Nacimiento:</strong> <?= htmlspecialchars($alumno['fecha_nacimiento']) ?></p>
                        <p><strong>Edad:</strong>
                            <?= date_diff(new DateTime($alumno['fecha_nacimiento']), new DateTime())->y ?> años</p>
                        <?php if ($tipo === 'admin'): ?>
                            <p><strong>Mensualidad:</strong> $<?= number_format($alumno['mensualidad'], 2) ?></p>
                        <?php endif; ?>
                        <p><strong>Estado:</strong>
                            <?= $alumno['activo'] ? '<span class="text-success">Activo</span>' : '<span class="text-danger">Inactivo</span>' ?>
                        </p>
                    </div>
                </div>
            </div>

            <?php if ($tipo === 'admin'): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white text-center">
                            <h5 class="mb-0">Estado de Pago del mes <?= $month ?>/<?= $year ?></h5>
                        </div>
                        <div class="estado-pago-card <?= $pagoHoy ? 'pagado' : 'no-pagado' ?>">
                            <?= $pagoHoy ? 'Pagado' : 'No Pagado' ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Asistencia: <?= $month ?>/<?= $year ?></h5>
                <div>
                    <a href="?curp=<?= urlencode($curp) ?>&year=<?= $year ?>&month=<?= max(1, $month - 1) ?>"
                        class="btn btn-sm btn-light">&lt; Mes Anterior</a>
                    <a href="?curp=<?= urlencode($curp) ?>&year=<?= $year ?>&month=<?= min(12, $month + 1) ?>"
                        class="btn btn-sm btn-light">Mes Siguiente &gt;</a>
                </div>
            </div>
            <div class="card-body p-3">
                <table class="calendario mb-3" id="calendarioAsistencia">
                    <thead>
                        <tr>
                            <?php foreach ($dias_semana as $d): ?>
                                <th><?= $d ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $col_start = $primer_dia_mes == 7 ? 7 : $primer_dia_mes;
                        $total_celdas = $total_dias + ($col_start - 1);
                        $filas = ceil($total_celdas / 7);
                        for ($fila = 0; $fila < $filas; $fila++): ?>
                            <tr>
                                <?php for ($col = 1; $col <= 7; $col++):
                                    $celda = $fila * 7 + $col;
                                    $dia_mes = $celda - ($col_start - 1);
                                    if ($dia_mes < 1 || $dia_mes > $total_dias):
                                        echo '<td class="dia sin_registro"></td>';
                                    else:
                                        $fecha = sprintf('%04d-%02d-%02d', $year, $month, $dia_mes);
                                        if (isset($eventos[$fecha])) {
                                            $tooltip_lines = [];
                                            foreach ($eventos[$fecha] as $evento) {
                                                $tooltip_lines[] = htmlspecialchars($evento['tema']) . ": " . htmlspecialchars($evento['estado']);
                                            }
                                            $tooltip = implode("\n", $tooltip_lines);
                                            $claseCss = 'registro';
                                        } else {
                                            $tooltip = 'Sin registro';
                                            $claseCss = 'sin_registro';
                                        }
                                        ?>
                                        <td class="dia <?= $claseCss ?>" title="<?= $tooltip ?>"><?= $dia_mes ?></td>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>

                <div>
                    <span class="color-box" style="background:#0d6efd;"></span> Día con registro
                    <span class="color-box"
                        style="background:#f8f9fa; border: 1px solid #ddd; margin-left: 15px;"></span> Sin registro
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('#calendarioAsistencia td.dia.registro'))
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el, { trigger: 'hover' })
        })
    </script>
</body>

</html>