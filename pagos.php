<?php
session_start();
date_default_timezone_set('America/Mexico_City');
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

$año = isset($_GET['año']) ? intval($_GET['año']) : date('Y');
$mesFiltro = isset($_GET['mes']) ? intval($_GET['mes']) : 0;
$estadoPago = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
$mostrarInactivos = isset($_GET['mostrarInactivos']) ? ($_GET['mostrarInactivos'] == '1') : false;
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$alumnos = [];
$where = "WHERE 1=1 ";
if (!$mostrarInactivos) {
    $where .= "AND activo=1 ";
}
if ($busqueda !== '') {
    $busquedaSQL = $conexion->real_escape_string($busqueda);
    $where .= "AND (nombre LIKE '%$busquedaSQL%' OR apellidos LIKE '%$busquedaSQL%' OR curp LIKE '%$busquedaSQL%') ";
}

$sql = "SELECT curp, nombre, apellidos, activo FROM alumnos $where ORDER BY activo DESC, nombre, apellidos";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc())
    $alumnos[] = $row;

// obtener pagos con fecha y monto
$pagos = [];
$resPagos = $conexion->query("SELECT alumno_curp, mes, fecha_pago, monto FROM pagos_mensualidad WHERE año=$año");
while ($row = $resPagos->fetch_assoc()) {
    $pagos[$row['alumno_curp']][$row['mes']] = [
        'fecha' => $row['fecha_pago'],
        'monto' => $row['monto']
    ];
}

// ajax guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    $curp = $_POST['curp'];
    $mes = intval($_POST['mes']);
    $año = intval($_POST['año']);
    $checked = $_POST['checked'] === 'true';
    if ($checked) {
        $monto = floatval($_POST['monto']);
        $stmt = $conexion->prepare("INSERT INTO pagos_mensualidad (alumno_curp, año, mes, fecha_pago, monto) 
                                    VALUES (?, ?, ?, NOW(), ?)
                                    ON DUPLICATE KEY UPDATE fecha_pago=NOW(), monto=?");
        $stmt->bind_param("siidd", $curp, $año, $mes, $monto, $monto);
        $stmt->execute();
        $fecha = date('Y-m-d H:i:s');
        echo json_encode(['fecha' => $fecha, 'monto' => $monto]);
    } else {
        $stmt = $conexion->prepare("DELETE FROM pagos_mensualidad WHERE alumno_curp=? AND año=? AND mes=?");
        $stmt->bind_param("sii", $curp, $año, $mes);
        $stmt->execute();
        echo json_encode(['fecha' => '', 'monto' => '']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pagos Mensualidad <?= $año ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f4f7f4; /* verde muy suave para fondo */
    color: #333;
}

.container h2 {
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #2e7d32; /* verde dollar oscuro para títulos */
}

.table {
    border-collapse: collapse;
    width: 100%;
    background: #fff;
    border: 1px solid #a5d6a7; /* borde verde claro */
    border-radius: 8px;
    overflow: hidden;
}

.table thead {
    background: #2e7d32; /* verde dollar oscuro */
    color: #fff;
}

.table thead th {
    padding: 0.75rem;
    text-align: center;
    font-weight: 500;
    border-bottom: 2px solid #a5d6a7;
}

.table tbody td {
    padding: 0.6rem;
    border-bottom: 1px solid #a5d6a7;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr:hover {
    background: #dcedc8; /* verde muy suave para hover */
}

.table tbody tr.table-danger {
    background: #f8d7da;
    color: #721c24;
}

.pagado {
    background: #d1e7dd !important;
    border-radius: 4px;
}

.pago-fecha,
.pago-monto {
    display: block;
    font-size: 0.8rem;
    color: #2e7d32; /* verde dollar */
}

/* Checkbox moderno */
.pago-check {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #a5d6a7; /* borde verde claro */
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    vertical-align: middle;
    position: relative;
    background: #fff;
}

.pago-check:checked {
    background: #2e7d32; /* verde dollar oscuro */
    border-color: #2e7d32;
}

.pago-check:checked::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 7px;
    width: 5px;
    height: 10px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Checkbox para mostrar inactivos */
input[type="checkbox"]#mostrarInactivos {
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #a5d6a7;
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    background: #fff;
}

input[type="checkbox"]#mostrarInactivos:checked {
    background: #2e7d32;
    border-color: #2e7d32;
}

input[type="checkbox"]#mostrarInactivos:checked::after {
    content: "";
    position: absolute;
    top: 2px;
    left: 6px;
    width: 4px;
    height: 8px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Inputs y selects */
form input[type="text"],
form select {
    border: 1px solid #a5d6a7;
    border-radius: 5px;
    padding: 0.45rem 0.75rem;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    color: #2e7d32; /* texto verde dollar */
}

form input[type="text"]:focus,
form select:focus {
    border-color: #2e7d32;
    outline: none;
    box-shadow: 0 0 0 0.15rem rgba(46, 125, 50, 0.25);
}

    </style>
</head>

<body>

    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-3 text-center">Pagos <?= $año ?></h2>

        <form method="GET" class="row g-2 mb-3 justify-content-center align-items-center">
            <div class="col-md-2">
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>"
                    placeholder="Buscar nombre o CURP" class="form-control">
            </div>
            <div class="col-md-2">
                <select name="año" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y') + 1; $y >= 2023; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $año ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="mes" class="form-select" onchange="this.form.submit()">
                    <option value="0" <?= $mesFiltro == 0 ? 'selected' : '' ?>>Todos</option>
                    <?php foreach (['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $i => $m): ?>
                        <option value="<?= $i + 1 ?>" <?= ($i + 1) == $mesFiltro ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="estado" class="form-select" onchange="this.form.submit()">
                    <option value="todos" <?= $estadoPago == 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="pagaron" <?= $estadoPago == 'pagaron' ? 'selected' : '' ?>>Pagaron</option>
                    <option value="nopagaron" <?= $estadoPago == 'nopagaron' ? 'selected' : '' ?>>No pagaron</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <input type="checkbox" id="mostrarInactivos" name="mostrarInactivos" value="1" <?= $mostrarInactivos ? 'checked' : '' ?> onchange="this.form.submit()">
                <label for="mostrarInactivos" class="ms-2 mb-0" style="color: black; font-weight: bold;">
                    Mostrar inactivos
                </label>

            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th class="text-start">Alumno</th>
                        <th>CURP</th>
                        <?php foreach (['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $m): ?>
                            <th><?= $m ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alumnos as $al):
                        $mostrar = true;
                        if ($mesFiltro > 0) {
                            $pagoHecho = isset($pagos[$al['curp']][$mesFiltro]);
                            if ($estadoPago == 'pagaron' && !$pagoHecho)
                                $mostrar = false;
                            if ($estadoPago == 'nopagaron' && $pagoHecho)
                                $mostrar = false;
                        }
                        ?>
                        <?php if ($mostrar): ?>
                            <tr class="<?= !$al['activo'] ? 'table-danger' : '' ?>">
                                <td class="text-start"><?= htmlspecialchars($al['nombre'] . ' ' . $al['apellidos']) ?>
                                    <?php if (!$al['activo']): ?><small class="text-danger">(Inactivo)</small><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($al['curp']) ?></td>
                                <?php for ($i = 1; $i <= 12; $i++):
                                    $pagado = isset($pagos[$al['curp']][$i]);
                                    $fecha = $pagado ? $pagos[$al['curp']][$i]['fecha'] : '';
                                    $monto = $pagado ? $pagos[$al['curp']][$i]['monto'] : '';
                                    ?>
                                    <td class="<?= $pagado ? 'pagado' : '' ?>">
                                        <input type="checkbox" class="pago-check" data-curp="<?= htmlspecialchars($al['curp']) ?>"
                                            data-mes="<?= $i ?>" data-año="<?= $año ?>" <?= $pagado ? 'checked' : '' ?>>
                                        <span class="pago-fecha"><?= $fecha ?></span>
                                        <?php if ($monto): ?>
                                            <span class="pago-monto"><strong>MXN $<?= number_format($monto, 2) ?></strong></span>
                                        <?php endif; ?>

                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endif; endforeach; ?>
                    <?php if (empty($alumnos)): ?>
                        <tr>
                            <td colspan="14" class="text-center text-muted">No se encontraron alumnos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        function formatearFechaLocal(fechaStr) {
            if (!fechaStr) return '';
            let partes = fechaStr.split(/[- :]/);
            let dt = new Date(partes[0], (partes[1] - 1), partes[2], partes[3] || 0, partes[4] || 0, partes[5] || 0);
            if (isNaN(dt)) return '';
            const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            return `${dias[dt.getDay()]} ${dt.getDate()} de ${meses[dt.getMonth()]} ${dt.getFullYear()}`;
        }

        $(function () {
            $(document).on('change', '.pago-check', function () {
                let c = $(this);
                let td = c.closest('td');
                if (c.is(':checked')) {
                    let monto = prompt("Ingresa el monto pagado:");
                    if (monto === null || monto.trim() === '') {
                        alert("No se ingresó monto. Acción cancelada.");
                        c.prop('checked', false);
                        return;
                    }
                    monto = parseFloat(monto);
                    if (isNaN(monto) || monto <= 0) {
                        alert("Monto inválido. Acción cancelada.");
                        c.prop('checked', false);
                        return;
                    }

                    $.post('', {
                        ajax: 1,
                        curp: c.data('curp'),
                        mes: c.data('mes'),
                        año: c.data('año'),
                        checked: true,
                        monto: monto
                    }, function (resp) {
                        let data = JSON.parse(resp);
                        td.addClass('pagado');
                        td.find('.pago-fecha').text(formatearFechaLocal(data.fecha));
                        if (td.find('.pago-monto').length) {
                            td.find('.pago-monto').text(`$${parseFloat(data.monto).toFixed(2)}`);
                        } else {
                            td.append(`<span class="pago-monto">$${parseFloat(data.monto).toFixed(2)}</span>`);
                        }
                    });
                } else {
                    if (!confirm("¿Seguro que deseas desmarcar? Si confirmas, la fecha original se perderá y no podrá recuperarse.")) {
                        c.prop('checked', true);
                        return;
                    }
                    let password = prompt("Ingresa la contraseña para confirmar esta acción:");
                    if (password !== "1703") {
                        alert("Contraseña incorrecta. Acción cancelada.");
                        c.prop('checked', true);
                        return;
                    }
                    $.post('', {
                        ajax: 1,
                        curp: c.data('curp'),
                        mes: c.data('mes'),
                        año: c.data('año'),
                        checked: false
                    }, function () {
                        td.removeClass('pagado');
                        td.find('.pago-fecha').text('');
                        td.find('.pago-monto').remove();
                    });
                }
            });

            $('.pago-fecha').each(function () {
                let fechaOriginal = $(this).text().trim();
                if (fechaOriginal) {
                    $(this).text(formatearFechaLocal(fechaOriginal));
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>