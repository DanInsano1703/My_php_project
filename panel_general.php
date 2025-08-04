<?php
session_start();
date_default_timezone_set('America/Mexico_City');
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');
$year = date('Y');
$month = date('n');
$nombreMes = ucfirst(strftime("%B", strtotime("$year-$month-01")));

// ==================================
// SELECCIÓN DEL MES A COMPARAR
// ==================================
$mesComparar = isset($_GET['mes_comparar']) && is_numeric($_GET['mes_comparar']) && $_GET['mes_comparar'] >= 1 && $_GET['mes_comparar'] <= 12
    ? intval($_GET['mes_comparar'])
    : ($month - 1 > 0 ? $month - 1 : 12);
$añoComparar = ($mesComparar > $month && $month != 1) ? $year - 1 : $year;
$nombreMesComparar = ucfirst(strftime("%B", strtotime("$añoComparar-$mesComparar-01")));

// ==================================
// TOTALES Y PAGOS
// ==================================
$totalAlumnos = $conexion->query("SELECT COUNT(*) AS total FROM alumnos WHERE activo=1")->fetch_assoc()['total'];
$totalClases = $conexion->query("SELECT COUNT(*) AS total FROM temas")->fetch_assoc()['total'];

// Pagos mes actual
$pagadosRes = $conexion->query("
    SELECT a.curp, a.nombre, a.apellidos
    FROM pagos_mensualidad p
    JOIN alumnos a ON a.curp = p.alumno_curp
    WHERE p.año = $year AND p.mes = $month AND a.activo=1
");
$alumnosPagaron = $pagadosRes->num_rows;

$sinPagarRes = $conexion->query("
    SELECT a.curp, a.nombre, a.apellidos
    FROM alumnos a
    WHERE a.activo=1 AND a.curp NOT IN (
        SELECT alumno_curp FROM pagos_mensualidad WHERE año = $year AND mes = $month
    )
");
$alumnosSinPagar = $sinPagarRes->num_rows;

// Dinero recaudado mes actual
$totalRecaudadoMes = $conexion->query("
    SELECT IFNULL(SUM(monto),0) AS total FROM pagos_mensualidad
    WHERE año=$year AND mes=$month
")->fetch_assoc()['total'];
$totalRecaudadoMesF = number_format($totalRecaudadoMes, 2);

// Dinero recaudado año actual
$totalRecaudadoAnio = $conexion->query("
    SELECT IFNULL(SUM(monto),0) AS total FROM pagos_mensualidad
    WHERE año=$year
")->fetch_assoc()['total'];
$totalRecaudadoAnioF = number_format($totalRecaudadoAnio, 2);

// Objetivo anual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevoObjetivo']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $nuevoObjetivo = floatval($_POST['nuevoObjetivo']);
    if ($nuevoObjetivo >= 0) {
        $_SESSION['objetivo_anual'] = $nuevoObjetivo;
        echo json_encode(['status' => 'ok']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Valor inválido']);
        exit;
    }
}
$objetivoAnual = $_SESSION['objetivo_anual'] ?? 2000.00;

// PAGOS MES COMPARAR
$pagosMesComparar = [];
$resPagosComp = $conexion->query("
    SELECT DATE(fecha_pago) AS fecha, SUM(monto) AS total
    FROM pagos_mensualidad
    WHERE año = $añoComparar AND mes = $mesComparar
    GROUP BY DATE(fecha_pago)
    ORDER BY DATE(fecha_pago)
");
while ($row = $resPagosComp->fetch_assoc()) {
    $pagosMesComparar[] = $row;
}

// Detalle pagos mes actual
$pagosDetalleRes = $conexion->query("
    SELECT p.alumno_curp, p.monto, p.fecha_pago, a.nombre, a.apellidos
    FROM pagos_mensualidad p
    JOIN alumnos a ON p.alumno_curp = a.curp
    WHERE p.año = $year AND p.mes = $month
    ORDER BY p.fecha_pago DESC
");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Panel General</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        /* Tu CSS aquí (igual al original) */
        /* ... */
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        h2,
        h4,
        h5 {
            font-weight: 700;
        }

        .card-stat {
            text-align: center;
            padding: 25px 15px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgb(0 0 0 / 0.1);
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }

        .card-stat:hover {
            transform: scale(1.07);
            box-shadow: 0 15px 25px rgb(0 0 0 / 0.15);
        }

        .card-stat h3 {
            font-size: 3rem;
            margin-bottom: 0.3rem;
            letter-spacing: 1.2px;
        }

        .card-stat p {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .cards-row {
            margin-bottom: 2.5rem;
        }

        .chart-card.position-relative {
            position: relative;
            height: 320px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgb(0 0 0 / 0.05);
            overflow: hidden;
            flex-direction: column;
        }

        #objetivoContainer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
            font-size: 20px;
            font-family: 'Poppins', sans-serif;
            color: #212529;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 6px;
            pointer-events: auto;
            white-space: nowrap;
            max-width: 85%;
        }

        #objetivoContainer>span {
            user-select: none;
            flex-shrink: 0;
        }

        #objetivoInput {
            width: 80px;
            font-weight: 700;
            font-size: 20px;
            border: none;
            border-bottom: 2px solid #0d6efd;
            background: transparent;
            color: #212529;
            text-align: right;
            outline: none;
            cursor: text;
            user-select: text;
            padding: 2px 5px;
            font-family: 'Poppins', sans-serif;
            flex-shrink: 0;
        }

        #objetivoInput:focus {
            border-color: #0a58ca;
            background: #e7f1ff;
        }

        #listaPagados,
        #listaSinPagar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgb(0 0 0 / 0.07);
            padding: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            transition: opacity 0.4s ease;
        }

        #listaPagados.d-none,
        #listaSinPagar.d-none {
            opacity: 0;
            height: 0;
            padding: 0 1.5rem;
            overflow: hidden;
            pointer-events: none;
        }

        #listaPagados::-webkit-scrollbar,
        #listaSinPagar::-webkit-scrollbar {
            width: 8px;
        }

        #listaPagados::-webkit-scrollbar-thumb,
        #listaSinPagar::-webkit-scrollbar-thumb {
            background-color: #6c757d;
            border-radius: 10px;
        }

        table#pagosDetalleTable {
            margin-top: 2rem;
        }

        table#pagosDetalleTable th,
        table#pagosDetalleTable td {
            vertical-align: middle;
        }

        .text-verde {
            color: green;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {
            .card-stat h3 {
                font-size: 2.2rem;
            }

            .card-stat p {
                font-size: 1rem;
            }
        }

        #objetivoContainer {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 600;
            font-size: 16px;
            /* Texto más pequeño */
            font-family: 'Poppins', sans-serif;
            color: #212529;
            user-select: none;
            display: flex;
            align-items: center;
            gap: 6px;
            pointer-events: auto;
            white-space: nowrap;
            max-width: 90%;
        }

        #objetivoContainer>span {
            user-select: none;
            flex-shrink: 0;
            font-size: 14px;
            /* Coincide con el tamaño general */
        }

        #objetivoInput {
            width: 70px;
            /* Un poco más estrecho */
            font-weight: 600;
            font-size: 14px;
            /* Texto más pequeño */
            border: none;
            border-bottom: 2px solid #0d6efd;
            background: transparent;
            color: #212529;
            text-align: right;
            outline: none;
            cursor: text;
            user-select: text;
            padding: 2px 5px;
            font-family: 'Poppins', sans-serif;
            flex-shrink: 0;
        }

        #objetivoInput:focus {
            border-color: #0a58ca;
            background: #e7f1ff;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <h2 class="mb-4 text-center" style="color: #000;">
            Reporte General - <?= htmlspecialchars($nombreMes) ?> <?= $year ?>
        </h2>

        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="card-stat bg-primary text-white shadow-sm" title="Total alumnos activos">
                    <h3><?= $totalAlumnos ?></h3>
                    <p>Total Alumnos</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card-stat bg-success text-white shadow-sm" role="button" onclick="mostrarLista('pagados')">
                    <h3><?= $alumnosPagaron ?></h3>
                    <p>Pagaron este mes</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card-stat bg-danger text-white shadow-sm" role="button" onclick="mostrarLista('sinPagar')">
                    <h3><?= $alumnosSinPagar ?></h3>
                    <p>Sin pagar</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card-stat bg-dark text-white shadow-sm">
                    <h3><?= $totalClases ?></h3>
                    <p>Total Clases</p>
                </div>
            </div>
        </div>

       <div class="row g-4 mt-4">
    <div class="col-lg-4">
        <div class="chart-card position-relative" 
            style="min-height: 480px; padding: 20px 25px; box-sizing: border-box; display: flex; flex-direction: column; margin-bottom: 15px;">
            <!-- Selector con flechas dentro de la tarjeta, arriba del título -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 12px; flex-shrink: 0;">
                <a href="?mes_comparar=<?= ($mesComparar == 1 ? 12 : $mesComparar - 1) ?>" class="btn btn-outline-primary btn-sm" title="Mes anterior">&lt;</a>
                <form method="get" class="m-0">
                    <select name="mes_comparar" class="form-select form-select-sm" onchange="this.form.submit()"
                        style="width:auto; display:inline-block; text-align:center;">
                        <?php for ($m = 1; $m <= 12; $m++):
                            $nombre = ucfirst(strftime("%B", strtotime("$year-$m-01")));
                            $anioMostrar = ($m > $month && $month != 1) ? $year - 1 : $year;
                            ?>
                            <option value="<?= $m ?>" <?= $m == $mesComparar ? 'selected' : '' ?>>
                                <?= $nombre ?> <?= $anioMostrar ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </form>
                <a href="?mes_comparar=<?= ($mesComparar == 12 ? 1 : $mesComparar + 1) ?>" class="btn btn-outline-primary btn-sm" title="Mes siguiente">&gt;</a>
            </div>

            <h5 class="mb-4 text-center text-secondary fw-bold" style="flex-shrink: 0;">📊 Pagos <?= $nombreMesComparar ?> <?= $añoComparar ?></h5>
            <canvas id="barChartAnterior" height="300" style="flex-grow: 1; margin-bottom: 8px;"></canvas>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-card position-relative" 
            style="min-height: 480px; padding: 20px 25px; box-sizing: border-box; display: flex; flex-direction: column; margin-bottom: 15px;">
            <h5 class="mb-4 text-center text-secondary fw-bold" style="flex-shrink: 0;">💰 Pagos del Mes</h5>
            <canvas id="doughnutChart" height="300" style="flex-grow: 1; margin-bottom: 8px;"></canvas>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-card position-relative" 
            style="min-height: 480px; padding: 20px 25px; box-sizing: border-box; display: flex; flex-direction: column; margin-bottom: 15px;">
            <h5 class="mb-4 text-center text-secondary fw-bold" style="flex-shrink: 0;">💵 Recaudación Anual</h5>
            <canvas id="doughnutYearChart" height="300" style="flex-grow: 1; margin-bottom: 8px;"></canvas>
            <div id="objetivoContainer" style="margin-top: 14px; flex-shrink: 0;">
                <span><?= $totalRecaudadoAnioF ?> MXN /</span>
                <input type="text" id="objetivoInput" value="<?= number_format($objetivoAnual, 2, '.', ',') ?>" maxlength="10" />
            </div>
        </div>
    </div>
</div>


        <!-- Listas -->
        <div class="mt-5">
            <div id="listaPagados" class="d-none">
                <h4 class="mb-3 text-success">Alumnos que ya pagaron este mes:</h4>
                <ul class="list-group shadow-sm">
                    <?php $pagadosRes->data_seek(0);
                    while ($row = $pagadosRes->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) ?>
                            <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($row['curp']) ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <div id="listaSinPagar" class="d-none">
                <h4 class="mb-3 text-danger">Alumnos que aún no han pagado:</h4>
                <ul class="list-group shadow-sm">
                    <?php $sinPagarRes->data_seek(0);
                    while ($row = $sinPagarRes->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) ?>
                            <span class="badge bg-danger rounded-pill"><?= htmlspecialchars($row['curp']) ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <!-- Tabla de pagos mes actual -->
        <div class="mt-5">
            <h4 class="mb-3" style="color: black; font-weight: bold;">
                Detalle de pagos del mes (<?= htmlspecialchars($nombreMes) ?> <?= $year ?>)
            </h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="pagosDetalleTable">
                    <thead class="table-dark">
                        <tr>
                            <th>CURP</th>
                            <th>Nombre Completo</th>
                            <th>Monto Pagado (MXN)</th>
                            <th>Fecha de Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $pagosDetalleRes->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['alumno_curp']) ?></td>
                                <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) ?></td>
                                <td style="color: green; font-weight: bold;"><?= number_format($row['monto'], 2) ?></td>
                                <td><?= htmlspecialchars(strftime('%A %d de %B %Y, %H:%M', strtotime($row['fecha_pago']))) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const fechasAnt = <?= json_encode(array_column($pagosMesComparar, 'fecha')) ?>;
        const montosAnt = <?= json_encode(array_map('floatval', array_column($pagosMesComparar, 'total'))) ?>;
        new Chart(document.getElementById('barChartAnterior'), {
            type: 'bar',
            data: {
                labels: fechasAnt,
                datasets: [{
                    label: 'MXN',
                    data: montosAnt,
                    backgroundColor: '#0d6efd',
                    borderRadius: 6
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        const dineroData = [<?= $alumnosPagaron ?>, <?= $alumnosSinPagar ?>];
        const centerMoney = {
            id: 'centerMoney',
            beforeDraw(chart) {
                const { ctx, width, height } = chart;
                ctx.save();
                ctx.font = '600 20px "Poppins", sans-serif';
                ctx.fillStyle = '#212529';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('<?= $totalRecaudadoMesF ?> MXN', width / 2, height / 2);
                ctx.restore();
            }
        };
        new Chart(document.getElementById('doughnutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pagaron', 'Sin pagar'],
                datasets: [{
                    data: dineroData,
                    backgroundColor: ['#198754', '#dc3545']
                }]
            },
            options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } },
            plugins: [centerMoney]
        });

        let objetivoAnual = parseFloat(<?= json_encode($objetivoAnual) ?>);
        let totalRecaudadoAnio = parseFloat(<?= json_encode($totalRecaudadoAnio) ?>);
        const ctxYear = document.getElementById('doughnutYearChart').getContext('2d');
        let doughnutYearChart = new Chart(ctxYear, {
            type: 'doughnut',
            data: {
                labels: ['Recaudado', 'Restante'],
                datasets: [{
                    data: [totalRecaudadoAnio, Math.max(objetivoAnual - totalRecaudadoAnio, 0)],
                    backgroundColor: ['#0d6efd', '#dee2e6']
                }]
            },
            options: { cutout: '75%', plugins: { legend: { position: 'bottom' } } }
        });

        function actualizarGraficoAnual(nuevoObjetivo) {
            objetivoAnual = nuevoObjetivo;
            const restante = Math.max(objetivoAnual - totalRecaudadoAnio, 0);
            doughnutYearChart.data.datasets[0].data = [totalRecaudadoAnio, restante];
            doughnutYearChart.update();
            document.getElementById('objetivoInput').value = nuevoObjetivo.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        }

        document.getElementById('objetivoInput').addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9.]/g, '');
        });
        document.getElementById('objetivoInput').addEventListener('blur', (e) => {
            let nuevoVal = parseFloat(e.target.value.replace(/,/g, ''));
            if (isNaN(nuevoVal) || nuevoVal <= 0) {
                e.target.value = objetivoAnual.toLocaleString('es-MX', { minimumFractionDigits: 2 });
                return;
            }
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'nuevoObjetivo=' + encodeURIComponent(nuevoVal)
            }).then(resp => resp.json())
                .then(data => {
                    if (data.status === 'ok') actualizarGraficoAnual(nuevoVal);
                    else alert('Error al guardar el objetivo.');
                }).catch(() => alert('Error al conectar con el servidor.'));
        });

        function mostrarLista(tipo) {
            document.getElementById('listaPagados').classList.add('d-none');
            document.getElementById('listaSinPagar').classList.add('d-none');
            document.getElementById(tipo === 'pagados' ? 'listaPagados' : 'listaSinPagar').classList.remove('d-none');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>