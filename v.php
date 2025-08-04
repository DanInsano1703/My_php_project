<?php
require_once __DIR__ . '/includes/bd.php';

date_default_timezone_set('America/Mexico_City');

$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
$dia_actual_nombre = $dias_semana[(int) date('N') - 1];

$hora_actual = date('H:i:s');

// Consulta para traer tema, horario y profesores
$sql = "
    SELECT 
        h.id AS horario_id,
        t.id AS tema_id,
        t.nombre AS tema_nombre,
        h.hora_inicio,
        h.hora_fin,
        GROUP_CONCAT(p.nombre ORDER BY p.nombre SEPARATOR ', ') AS profesores_nombres
    FROM horarios h
    JOIN temas t ON h.tema_id = t.id
    LEFT JOIN horario_profesores hp ON hp.horario_id = h.id
    LEFT JOIN profesores p ON p.id = hp.profesor_id
    WHERE h.dia_semana = ?
    GROUP BY h.id
    ORDER BY h.hora_inicio
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('s', $dia_actual_nombre);
$stmt->execute();
$result = $stmt->get_result();
$horarios_hoy = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Traer alumnos asignados a cada horario
$alumnos_por_horario = [];
if (!empty($horarios_hoy)) {
    $ids_horarios = array_column($horarios_hoy, 'horario_id');
    $placeholders = implode(',', array_fill(0, count($ids_horarios), '?'));
    $types = str_repeat('i', count($ids_horarios));

    $sqlAlumnos = "
    SELECT asig.horario_id, a.curp, CONCAT(a.nombre, ' ', a.apellidos) AS nombre_completo
    FROM alumno_sesion asig
    JOIN alumnos a ON a.curp = asig.alumno_curp
    WHERE asig.horario_id IN ($placeholders)
      AND a.activo = 1
    ORDER BY a.nombre, a.apellidos
";

    $stmtAlumnos = $conexion->prepare($sqlAlumnos);
    $stmtAlumnos->bind_param($types, ...$ids_horarios);
    $stmtAlumnos->execute();
    $resAlumnos = $stmtAlumnos->get_result();

    while ($row = $resAlumnos->fetch_assoc()) {
        $alumnos_por_horario[$row['horario_id']][] = $row;
    }
    $stmtAlumnos->close();
}

function formato12Horas($hora24)
{
    $dateObj = DateTime::createFromFormat('H:i:s', $hora24);
    if (!$dateObj)
        return $hora24;
    return $dateObj->format('h:i A');
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Clases hoy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Contenedor exclusivo para aislar estilos */
        #clases-hoy-container {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0px 30px 20px 10px;
            max-width: 350px;
            color: #343a40;
            user-select: none;
            font-size: 90%;
        }

        #clases-hoy-container>div {
            max-width: 432px;
            width: 100%;
            height: 700px;
            overflow-y: auto;
            margin: 18px 0;
            padding: 16px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        #clases-hoy-container h1 {
            color: #343a40;
            margin: 0 0 14px 0;
            font-size: 1.17rem;
            text-align: left;
        }

        #clases-hoy-container #hora-actual {
            background: #343a40;
            color: white;
            padding: 5px 11px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.765rem;
            margin-bottom: 14px;
            width: fit-content;
            text-align: left;
        }

        #clases-hoy-container table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 4px;
            overflow: hidden;
        }

        #clases-hoy-container th {
            background: #343a40;
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 9px 12.6px;
        }

        #clases-hoy-container td {
            padding: 12px 14px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }

        #clases-hoy-container .estado-icono {
            font-size: 0.81rem;
            margin-right: 7.2px;
            vertical-align: middle;
        }

        #clases-hoy-container .horario-info {
            font-size: 0.72rem;
            color: #495057;
            margin-top: 3px;
            display: flex;
            align-items: center;
        }

        #clases-hoy-container .horario-info i {
            margin-right: 5px;
        }

        #clases-hoy-container .profesores-nombres {
            font-size: 0.72rem;
            color: #004085;
            font-weight: bold;
            margin-top: 3.6px;
            display: flex;
            align-items: center;
        }

        #clases-hoy-container .profesores-nombres i {
            margin-right: 5px;
        }

        #clases-hoy-container .alumnos-info {
            font-size: 0.72rem;
            color: #155724;
            margin-top: 3.6px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        #clases-hoy-container .alumnos-info i {
            margin-right: 5px;
        }

        #clases-hoy-container .alumnos-info.sin-alumnos {
            color: #6c757d;
        }

        #clases-hoy-container p {
            text-align: center;
            font-size: 0.9rem;
            color: #495057;
        }

        /* Contenedor de alumnos desplegable */
        #clases-hoy-container .lista-alumnos-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out;
            background: #e9ecef;
            border-radius: 5.4px;
            margin-top: 5px;
        }

        #clases-hoy-container .lista-alumnos-container.expanded {
            max-height: 200px;
            transition: max-height 0.4s ease-in;
        }

        #clases-hoy-container .lista-alumnos {
            max-height: 160px;
            overflow-y: auto;
            padding: 8px 12px;
            font-size: 0.81rem;
            color: #212529;
        }

        #clases-hoy-container .lista-alumnos ul {
            margin: 0;
            padding-left: 18px;
        }

        #clases-hoy-container .lista-alumnos li {
            margin-bottom: 3.6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Scroll personalizado */
        #clases-hoy-container .lista-alumnos::-webkit-scrollbar {
            width: 4px;
        }

        #clases-hoy-container .lista-alumnos::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }

        #clases-hoy-container .lista-alumnos::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 2px;
        }

        #clases-hoy-container .lista-alumnos::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Estado de la clase */
        .estado-clase {
            font-size: 0.72rem;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 8px;
            font-weight: 500;
        }

        .en-curso {
            background: #cce5ff;
            color: #004085;
            border-left: 3px solid #004085;
        }

        .finalizada {
            background: #d4edda;
            color: #155724;
        }

        .por-ver {
            background: #e2e3e5;
            color: #383d41;
        }
        #clases-hoy-container .alumnos-info {
    font-size: 0.72rem;
    color: #155724;
    margin-top: 3.6px;
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 600; /* Texto más grueso */
    padding: 3px 8px; /* Espacio interno como botón */
    border-radius: 4px; /* Bordes redondeados */
    background-color: #e8f5e9; /* Fondo verde claro */
    transition: all 0.2s ease; /* Transición suave */
    width: fit-content; /* Que ocupe solo el espacio necesario */
}

#clases-hoy-container .alumnos-info:hover {
    background-color: #d4edda; /* Color más oscuro al pasar mouse */
    box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* Sombra sutil */
}

#clases-hoy-container .alumnos-info.sin-alumnos {
    color: #6c757d;
    background-color: #e9ecef; /* Fondo gris para sin alumnos */
}

#clases-hoy-container .alumnos-info.sin-alumnos:hover {
    background-color: #dee2e6;
}
    </style>
</head>

<body>
    <div id="clases-hoy-container">
        <div>
            <br>
            <br>
            <br>
            <br>
            <h1>Clases hoy: <?= htmlspecialchars($dia_actual_nombre) ?></h1>
            <div id="hora-actual">--:--:--</div>

            <?php if (empty($horarios_hoy)): ?>
                <p>No hay horarios definidos para hoy, descansa o asigna el material :D.</p>
            <?php else: ?>
                <table aria-label="Tabla de horarios para hoy">
                    <thead>
                        <tr>
                            <th>Sesión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horarios_hoy as $h):
                            $inicio = $h['hora_inicio'];
                            $fin = $h['hora_fin'];
                            $horario_formateado = formato12Horas($inicio).' - '.formato12Horas($fin);
                            
                            // Determinar estado
                            if ($hora_actual >= $inicio && $hora_actual <= $fin) {
                                $estado_clase = 'en-curso';
                                $estado_texto = 'En curso';
                                $icono = 'bi-play-circle-fill';
                                $icono_color = '#004085';
                            } elseif ($hora_actual > $fin) {
                                $estado_clase = 'finalizada';
                                $estado_texto = 'Finalizada';
                                $icono = 'bi-check-circle-fill';
                                $icono_color = '#155724';
                            } else {
                                $estado_clase = 'por-ver';
                                $estado_texto = 'Por ver';
                                $icono = 'bi-clock';
                                $icono_color = '#6c757d';
                            }
                            
                            $tiene_alumnos = !empty($alumnos_por_horario[$h['horario_id']]);
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: flex-start;">
                                        <i class="bi <?= $icono ?>" style="color:<?= $icono_color ?>; font-size: 1.1rem; margin-right: 8px;"></i>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 500; color: #212529;">
                                                <?= htmlspecialchars($h['tema_nombre']) ?>
                                                <span class="estado-clase <?= $estado_clase ?>"><?= $estado_texto ?></span>
                                            </div>
                                            
                                            <div class="horario-info">
                                                <i class="bi bi-clock" style="color: #6c757d;"></i>
                                                <?= $horario_formateado ?>
                                            </div>
                                            
                                            <?php if (!empty($h['profesores_nombres'])): ?>
                                                <div class="profesores-nombres">
                                                    <i class="bi bi-person-fill" style="color: #004085;"></i>
                                                    <?= htmlspecialchars($h['profesores_nombres']) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="alumnos-info <?= !$tiene_alumnos ? 'sin-alumnos' : '' ?>" 
                                                onclick="<?= $tiene_alumnos ? 'toggleAlumnos('.$h['horario_id'].')' : '' ?>">
                                                <i class="bi bi-people-fill"></i>
                                                <?= $tiene_alumnos ? 
                                                    count($alumnos_por_horario[$h['horario_id']]).' alumno(s)' : 
                                                    'Sin alumnos asignados' ?>
                                            </div>
                                            
                                            <?php if ($tiene_alumnos): ?>
                                                <div id="lista-alumnos-<?= $h['horario_id'] ?>" class="lista-alumnos-container">
                                                    <div class="lista-alumnos">
                                                        <ul>
                                                            <?php foreach ($alumnos_por_horario[$h['horario_id']] as $alumno): ?>
                                                                <li>• <?= htmlspecialchars($alumno['nombre_completo']) ?></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function actualizarHoraPanel() {
            const now = new Date();
            let horas = now.getHours();
            let minutos = now.getMinutes();
            let segundos = now.getSeconds();
            let ampm = horas >= 12 ? 'PM' : 'AM';

            horas = horas % 12;
            horas = horas ? horas : 12;
            minutos = minutos < 10 ? '0' + minutos : minutos;
            segundos = segundos < 10 ? '0' + segundos : segundos;

            const horaFormateada = `${horas}:${minutos}:${segundos} ${ampm}`;
            document.getElementById('hora-actual').textContent = horaFormateada;
        }
        setInterval(actualizarHoraPanel, 1000);
        actualizarHoraPanel();

        function toggleAlumnos(id) {
            const container = document.getElementById('lista-alumnos-' + id);
            container.classList.toggle('expanded');
            
            // Opcional: Cambiar el ícono o texto al expandir/contraer
            const alumnosInfo = container.previousElementSibling;
            if (container.classList.contains('expanded')) {
                alumnosInfo.innerHTML = alumnosInfo.innerHTML.replace('bi-people-fill', 'bi-people-x-fill');
            } else {
                alumnosInfo.innerHTML = alumnosInfo.innerHTML.replace('bi-people-x-fill', 'bi-people-fill');
            }
        }
    </script>
</body>

</html>