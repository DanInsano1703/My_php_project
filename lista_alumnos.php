<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;

require_once __DIR__ . '/includes/bd.php';

// Consulta para obtener alumnos con temas con id:nombre
$sql = "SELECT 
            a.curp, 
            a.nombre, 
            a.apellidos, 
            TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad,
            a.telefono,
            a.mensualidad,
            a.nombre_tutor,
            GROUP_CONCAT(CONCAT(t.id, ':', t.nombre) SEPARATOR ' | ') AS temas_asignados,
            COUNT(t.id) AS total_temas
        FROM 
            alumnos a
        LEFT JOIN 
            alumnos_tema at ON a.curp = at.curp
        LEFT JOIN 
            temas t ON at.tema_id = t.id
        WHERE 
            a.activo = 1
        GROUP BY 
            a.curp
        ORDER BY 
            a.nombre, a.apellidos";

$resultado = $conexion->query($sql);

$totalColumns = 5;
if ($tipo === 'admin') {
    $totalColumns += 4;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Listado de Alumnos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            width: 100%;
            min-width: 900px;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2e7d32;
        }

        .full-width-container {
            max-width: 100vw;
            padding: 20px 30px;
            box-sizing: border-box;
        }

        /* Navbar */
        .navbar {
            background-color: var(--verde-dollar-soft) !important;
        }

        .navbar .navbar-nav .nav-link {
            color: var(--verde-dollar);
            font-weight: 600;
            transition: background-color 0.3s, color 0.3s;
        }

        .navbar .navbar-nav .nav-link:hover,
        .navbar .navbar-nav .nav-link.active {
            background-color: var(--verde-dollar);
            color: white !important;
            border-radius: 0.3rem;
        }

        /* Badges */
        .badge-clase,
        .badge.bg-secondary,
        .badge.bg-primary {
            background-color: var(--verde-dollar) !important;
            color: white !important;
        }

        /* Tooltip */
        [data-bs-toggle="tooltip"] {
            cursor: help;
            text-decoration: underline dotted var(--verde-dollar);
        }

        /* Card */
        .card {
            border-radius: 12px;
            box-shadow: 0 10px 25px var(--verde-dollar-soft);
            transition: box-shadow 0.3s ease;
            background: white;
            color: #2e7d32;
        }

        .card:hover {
            box-shadow: 0 15px 35px var(--verde-dollar-hover);
        }

        .card-header.bg-primary {
            background-color: var(--verde-dollar) !important;
            color: white;
            font-weight: 700;
            font-size: 1.4rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            text-align: center;
        }

        /* Tabla DataTables */
        table.dataTable {
            width: 100% !important;
            table-layout: auto !important;
            font-size: 0.9rem;
            color: var(--verde-dollar);
        }

        table.dataTable thead {
            background: var(--verde-dollar);
            color: white;
        }

        table.dataTable tbody tr:hover {
            background-color: var(--verde-dollar-soft);
        }

        thead tr.filters th input {
            font-size: 0.85rem;
            padding: 0.35rem 0.6rem;
            width: 100%;
            box-sizing: border-box;
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s;
        }

        thead tr.filters th input:focus {
            outline: none;
            border-color: var(--verde-dollar);
            box-shadow: 0 0 5px var(--verde-dollar-hover);
        }

        /* Column widths and styles */
        td.nombre-completo {
            min-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
            text-align: left !important;
        }

        td.curp,
        td.telefono {
            min-width: 140px;
            white-space: nowrap;
            text-align: left !important;
            font-family: monospace;
        }

        td.clases-inscritas {
            min-width: 220px;
            white-space: normal;
            text-align: left !important;
        }

        td.actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            white-space: nowrap;
        }

        /* Acción botones */
        td.actions a.btn-sm {
            font-size: 1.3rem;
            padding: 0.25rem 0.6rem;
            line-height: 1;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease, color 0.3s ease;
            color: var(--verde-dollar);
        }

        td.actions a.btn-sm:hover {
            transform: scale(1.15);
            filter: drop-shadow(0 2px 2px rgb(0 0 0 / 0.15));
            color: #1b5e20;
        }

        /* Badges total clases */
        .badge-primary {
            font-weight: 600;
            font-size: 0.9rem;
            background-color: var(--verde-dollar) !important;
            color: white !important;
        }

        /* Contador resultados */
        #contadorResultados {
            font-weight: 600;
            user-select: none;
            color: var(--verde-dollar);
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {

            body,
            html {
                min-width: 0;
            }

            td.nombre-completo {
                min-width: 180px;
                white-space: normal;
            }

            td.clases-inscritas {
                min-width: 150px;
            }
        }

        .badge.bg-secondary.rounded-pill {
            background-color: #145c14 !important;
            color: white !important;
        }

        .badge.bg-secondary.rounded-pill {
            background-color: #198754 !important;
            /* verde Bootstrap */
            color: white !important;
        }

        .badge.bg-secondary.rounded-pill,
        .badge.bg-primary {
            background-color: #198754 !important;
            /* verde Bootstrap */
            color: white !important;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>
      
   


    <div class="full-width-container mt-5 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary">
                <h4 class="mb-0" style="color: #0b3d0b; font-weight: bold;">Mis alumnos</h4>

            </div>
            <div class="card-body">
                <div class="mb-3 text-end">
                    <span id="contadorResultados" class="badge bg-secondary fs-6">Mostrando 0 alumnos</span>
                </div>

                <div class="table-responsive">
                    <table id="tablaAlumnos" class="table table-striped table-hover align-middle text-center">
                        <thead class="table-dark">
                            <tr>
                                <?php if ($tipo === 'admin'): ?>
                                    <th>CURP</th>
                                <?php endif; ?>
                                <th>Nombre Completo</th>
                                <th>Edad</th>
                                <?php if ($tipo === 'admin'): ?>
                                    <th>Teléfono</th>
                                <?php endif; ?>
                                <?php if ($tipo === 'admin'): ?>
                                    <th>Nombre Tutor</th>
                                <?php endif; ?>
                                <th>Temas Asignados</th>
                                <th>Total Temas</th>
                                <?php if ($tipo === 'admin'): ?>
                                    <th>Mensualidad ($)</th>
                                <?php endif; ?>
                                <th>Acciones</th>
                            </tr>
                            <tr class="filters bg-light">
                                <?php if ($tipo === 'admin'): ?>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Buscar CURP" />
                                    </th>
                                <?php endif; ?>
                                <th><input type="text" class="form-control form-control-sm"
                                        placeholder="Buscar Nombre Completo" /></th>
                                <th><input type="number" class="form-control form-control-sm" placeholder="Edad" /></th>
                                <?php if ($tipo === 'admin'): ?>
                                    <th><input type="text" class="form-control form-control-sm" placeholder="Teléfono" />
                                    </th>
                                <?php endif; ?>
                                <?php if ($tipo === 'admin'): ?>
                                    <th><input type="text" class="form-control form-control-sm"
                                            placeholder="Buscar Tutor" /></th>
                                <?php endif; ?>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Tema" /></th>
                                <th></th>
                                <?php if ($tipo === 'admin'): ?>
                                    <th><input type="number" step="0.01" class="form-control form-control-sm"
                                            placeholder="Mensualidad" /></th>
                                <?php endif; ?>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($alumno = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <?php if ($tipo === 'admin'): ?>
                                        <td><?= htmlspecialchars($alumno['curp']) ?></td>
                                    <?php endif; ?>
                                    <td><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></td>
                                    <td><?= $alumno['edad'] ?></td>
                                    <?php if ($tipo === 'admin'): ?>
                                        <td><?= htmlspecialchars($alumno['telefono']) ?></td>
                                    <?php endif; ?>
                                    <?php if ($tipo === 'admin'): ?>
                                        <td <?= empty($alumno['nombre_tutor']) ? 'data-bs-toggle="tooltip" data-bs-placement="top" title="Sin tutor registrado"' : '' ?>>
                                            <?= htmlspecialchars($alumno['nombre_tutor'] ?: '-') ?>
                                        </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if (!empty($alumno['temas_asignados'])):
                                            $temas = explode(' | ', $alumno['temas_asignados']);
                                            foreach ($temas as $tema) {
                                                list($temaId, $temaNombre) = explode(':', $tema);
                                                ?>
                                                <a href="progreso.php?tema_id=<?= $temaId ?>&curp=<?= urlencode($alumno['curp']) ?>"
                                                    class="badge bg-secondary rounded-pill text-decoration-none">
                                                    <?= htmlspecialchars($temaNombre) ?>
                                                </a>
                                                <?php
                                            }
                                        else: ?>
                                            <span class="text-muted fst-italic">Sin temas</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $alumno['total_temas'] > 0 ? 'primary' : 'secondary' ?>">
                                            <?= $alumno['total_temas'] ?>
                                        </span>
                                    </td>
                                    <?php if ($tipo === 'admin'): ?>
                                        <td>$<?= number_format($alumno['mensualidad'] ?? 0, 2) ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="panel_alumno.php?curp=<?= urlencode($alumno['curp']) ?>"
                                            class="btn btn-sm btn-primary" title="Ver Detalles">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <?php if ($tipo === 'admin'): ?>
                                            <a href="editar_alumno.php?curp=<?= urlencode($alumno['curp']) ?>"
                                                class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            var totalCols = <?= $totalColumns ?>;
            var noOrder = [], noSearch = [];
            noOrder.push(totalCols - 1);
            noSearch.push(totalCols - 1);
            if ("<?= $tipo ?>" === "admin") {
                noOrder.push(totalCols - 3);
                noSearch.push(totalCols - 3);
            }

            var table = $('#tablaAlumnos').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                columnDefs: [
                    { orderable: false, targets: noOrder },
                    { searchable: false, targets: noSearch }
                ],
                fixedHeader: true
            });

            function actualizarContador() {
                var count = table.rows({ filter: 'applied' }).count();
                $("#contadorResultados").text("Mostrando " + count + " alumno" + (count !== 1 ? "s" : ""));
            }
            actualizarContador();
            table.columns().every(function (index) {
                $('thead tr.filters th').eq(index).find('input').on('keyup change', function () {
                    table.column(index).search(this.value).draw();
                    actualizarContador();
                });
            });
            table.on('search.dt', actualizarContador);
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });
        });
    </script>
</body>

</html>