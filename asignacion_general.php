<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

function escape($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

// Obtener todos los temas con sus sesiones
$temas = $conexion->query("
    SELECT t.id AS tema_id, t.nombre AS tema_nombre, 
           h.id AS sesion_id, h.dia_semana, h.hora_inicio, h.hora_fin
    FROM temas t
    LEFT JOIN horarios h ON t.id = h.tema_id
    ORDER BY t.nombre, FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'), h.hora_inicio
")->fetch_all(MYSQLI_ASSOC);

// Organizar sesiones por tema
$sesiones_por_tema = [];
foreach ($temas as $row) {
    if (!isset($sesiones_por_tema[$row['tema_id']])) {
        $sesiones_por_tema[$row['tema_id']] = [
            'nombre' => $row['tema_nombre'],
            'sesiones' => []
        ];
    }
    if ($row['sesion_id']) {
        $sesiones_por_tema[$row['tema_id']]['sesiones'][] = [
            'id' => $row['sesion_id'],
            'dia_semana' => $row['dia_semana'],
            'hora_inicio' => $row['hora_inicio'],
            'hora_fin' => $row['hora_fin']
        ];
    }
}

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

// Obtener todos los alumnos activos
$alumnos = $conexion->query("
    SELECT curp, CONCAT(nombre, ' ', apellidos) AS nombre_completo 
    FROM alumnos 
    WHERE activo = 1
    ORDER BY nombre, apellidos
")->fetch_all(MYSQLI_ASSOC);
// Después de obtener los alumnos, obtén sus temas inscritos
$temas_por_alumno = [];
$res = $conexion->query("
    SELECT at.curp, GROUP_CONCAT(t.nombre SEPARATOR ', ') AS temas
    FROM alumnos_tema at
    JOIN temas t ON at.tema_id = t.id
    GROUP BY at.curp
");

while ($row = $res->fetch_assoc()) {
    $temas_por_alumno[$row['curp']] = $row['temas'];
}
// Obtener alumnos asignados por sesión
$asignados_por_sesion = [];
$stmt = $conexion->prepare("SELECT alumno_curp FROM alumno_sesion WHERE horario_id = ?");
foreach ($temas as $tema) {
    if ($tema['sesion_id']) {
        $stmt->bind_param('i', $tema['sesion_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $asignados = [];
        while ($row = $res->fetch_assoc()) {
            $asignados[] = $row['alumno_curp'];
        }
        $asignados_por_sesion[$tema['sesion_id']] = $asignados;
    }
}
$stmt->close();

// Procesar asignaciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignaciones'])) {
    $asignaciones = $_POST['asignaciones'];

    // Obtener todos los IDs de sesión
    $idsSesiones = array_filter(array_column($temas, 'sesion_id'));

    $conexion->begin_transaction();
    try {
        // Eliminar todas las asignaciones existentes
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

        header("Location: " . $_SERVER['PHP_SELF'] . "?guardado=1");
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        .contenedor-principal {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .contenedor-drag-drop {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .panel {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
            max-height: 80vh;
            overflow-y: auto;
        }

        .tema-acordeon {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .tema-header {
            padding: 10px 15px;
            background: #e9ecef;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tema-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .tema-contenido {
            padding: 10px 15px;
            background: white;
        }

        .sesion {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }

        .sesion h3 {
            font-size: 1rem;
            margin: 0 0 10px 0;
            color: #333;
        }

        .profesor-asignado {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .alumnos-asignados {
            min-height: 40px;
            padding: 5px;
            border: 1px dashed #ccc;
            border-radius: 4px;
            margin-top: 10px;
        }

        .alumno-chip {
            display: inline-block;
            background: #e3f2fd;
            padding: 3px 8px;
            margin: 2px;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .alumno-chip .remove {
            margin-left: 5px;
            cursor: pointer;
            color: #666;
        }

        .alumno-chip .remove:hover {
            color: #d32f2f;
        }

        #alumnos-disponibles {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .alumno-item {
            padding: 5px 10px;
            background: #e8f5e9;
            border-radius: 15px;
            cursor: move;
            font-size: 0.9rem;
            border: 1px solid #c8e6c9;
        }

        .alumno-item:hover {
            background: #c8e6c9;
        }

        .contador {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }

        .btn-guardar {
            margin-top: 20px;
            padding: 10px 20px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-guardar:hover {
            background: #388e3c;
        }

        .btn-desasignar-todo {
            margin: 15px 0;
            padding: 8px 15px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-desasignar-todo:hover {
            background: #d32f2f;
        }

        .mensaje {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .exito {
            background: #dff0d8;
            color: #3c763d;
        }

        .error {
            background: #f2dede;
            color: #a94442;
        }

        .dragover {
            background: #fffde7 !important;
            border-color: #ffd600 !important;
        }

        .dragging {
            opacity: 0.5;
        }

        .busqueda-container {
            margin-bottom: 15px;
        }

        #busqueda-alumnos {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .seleccion-multiple {
            margin: 10px 0;
        }

        .tooltip-temas {
            position: fixed;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            pointer-events: none;
            z-index: 1000;
            max-width: 300px;
            display: none;
        }

        .badge-temas {
            margin-left: 5px;
            color: #4a6ea9;
            font-size: 0.8em;
            cursor: help;
        }

        .alumno-item:hover .badge-temas {
            color: #2c4b8b;
        }

        .alerta-importante {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 1rem;
            box-shadow: 0 4px 10px rgba(133, 100, 4, 0.1);
        }
    </style>
</head>
<?php include 'navbar3.php'; ?>

<body>
    <br>
    <br>
    <br>
    <br>
    <div class="contenedor-principal">
        <h1><i class="bi bi-people"></i> Asignación general de Alumnos</h1>

        <?php if ($mensaje_guardado): ?>
            <div class="mensaje exito"><i class="bi bi-check-circle"></i> <?= escape($mensaje_guardado) ?></div>
        <?php endif; ?>
        <?php if (isset($mensaje_error)): ?>
            <div class="mensaje error"><i class="bi bi-exclamation-triangle"></i> <?= escape($mensaje_error) ?></div>
        <?php endif; ?>

        <button type="button" class="btn-desasignar-todo" onclick="desasignarTodos()">
            <i class="bi bi-trash"></i> Remover alumnos de todas las sesiones
        </button>





        <!-- <div class="seleccion-multiple">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodosAlumnos()">
                <i class="bi bi-check-all"></i> Seleccionar todos los alumnos
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deseleccionarTodosAlumnos()">
                <i class="bi bi-x-circle"></i> Deseleccionar todos
            </button>
        </div>-->

        <div class="alerta-asignacion">

            <p class="alerta-importante">
                ⚠️ No añadas alumnos a temas en los que no están previamente asignados.
            </p>


            <form method="POST">
                <div class="contenedor-drag-drop">
                    <div class="panel">
                        <h2><i class="bi bi-calendar-event"></i> Temas y sesiones</h2>

                        <?php if (empty($sesiones_por_tema)): ?>
                            <div class="alert alert-info">No hay sesiones disponibles.</div>
                        <?php else: ?>
                            <?php foreach ($sesiones_por_tema as $tema_id => $tema): ?>
                                <div class="tema-acordeon">
                                    <div class="tema-header" onclick="toggleAcordeon(this)">
                                        <h3><?= escape($tema['nombre']) ?></h3>
                                        <i class="bi bi-chevron-down"></i>
                                    </div>
                                    <div class="tema-contenido">
                                        <?php if (empty($tema['sesiones'])): ?>
                                            <div class="alert alert-warning">Este tema no tiene sesiones.</div>
                                        <?php else: ?>
                                            <?php foreach ($tema['sesiones'] as $sesion): ?>
                                                <div class="sesion" data-sesion-id="<?= escape($sesion['id']) ?>">
                                                    <h3>
                                                        <i class="bi bi-clock"></i>
                                                        <?= escape($sesion['dia_semana']) ?>
                                                        <?= date("g:i A", strtotime($sesion['hora_inicio'])) ?> -
                                                        <?= date("g:i A", strtotime($sesion['hora_fin'])) ?>
                                                    </h3>

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
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="panel">
                        <h2><i class="bi bi-person-lines-fill"></i> Todos los Alumnos</h2>

                        <div class="busqueda-container">
                            <input type="text" id="busqueda-alumnos" placeholder="Buscar alumno..."
                                onkeyup="filtrarAlumnos()">
                        </div>

                        <div id="alumnos-disponibles">
                            <?php foreach ($alumnos as $alumno):
                                $temas_alumno = $temas_por_alumno[$alumno['curp']] ?? 'Sin temas asignados';
                                $tooltip_text = !empty($temas_por_alumno[$alumno['curp']]) ? 'Inscrito en: ' . $temas_alumno : $temas_alumno;
                                ?>
                                <div class="alumno-item" draggable="true" data-curp="<?= escape($alumno['curp']) ?>"
                                    data-temas="<?= escape($temas_alumno) ?>" title="<?= escape($tooltip_text) ?>">
                                    <?= escape($alumno['nombre_completo']) ?>
                                    <?php if (!empty($temas_por_alumno[$alumno['curp']])): ?>
                                        <span class="badge-temas" title="<?= escape($tooltip_text) ?>">
                                            <i class="bi bi-bookmark-check"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                       
                    <button type="submit" class="btn-guardar"><i class="bi bi-save"></i> Guardar
                        Asignaciones</button>
            </form>
        </div>

        <script>
            // Variables globales
            let alumnosSeleccionados = new Set();
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
                const curp = ev.target.getAttribute('data-curp');
                ev.dataTransfer.setData("curp", curp);
                ev.dataTransfer.setData("nombre", ev.target.textContent.trim());
                ev.dataTransfer.effectAllowed = "move";
                ev.target.classList.add('dragging');

                // Si es un alumno seleccionado, arrastramos todos
                if (alumnosSeleccionados.has(curp)) {
                    ev.dataTransfer.setData("multi", "true");
                    const nombres = [];
                    document.querySelectorAll('#alumnos-disponibles .alumno-item.selected').forEach(el => {
                        nombres.push(el.textContent.trim());
                    });
                    ev.dataTransfer.setData("nombres", JSON.stringify(nombres));
                }
            }

            function dragEnd(ev) {
                ev.target.classList.remove('dragging');
                document.querySelectorAll('.sesion').forEach(s => s.classList.remove('dragover'));
            }

            // Soltar alumno en sesión
            function drop(ev) {
                ev.preventDefault();
                ev.currentTarget.classList.remove('dragover');

                const sesionDiv = ev.currentTarget;
                const container = sesionDiv.querySelector('.alumnos-asignados');
                if (!container) return;

                const isMulti = ev.dataTransfer.getData("multi") === "true";

                if (isMulti) {
                    // Asignación múltiple
                    const curps = [];
                    const nombres = JSON.parse(ev.dataTransfer.getData("nombres"));

                    document.querySelectorAll('#alumnos-disponibles .alumno-item.selected').forEach(el => {
                        const curp = el.getAttribute('data-curp');
                        const nombre = el.textContent.trim();

                        // Verificar si ya está asignado
                        if (![...container.children].some(chip => chip.getAttribute('data-curp') === curp)) {
                            // Crear chip del alumno
                            const chip = crearChipAlumno(curp, nombre);
                            container.appendChild(chip);
                            curps.push(curp);

                            // Remover de disponibles
                            el.remove();
                        }
                    });

                    // Actualizar input oculto
                    actualizarInputOculto(sesionDiv);
                    actualizarContadores();
                    deseleccionarTodosAlumnos();
                } else {
                    // Asignación simple
                    const curp = ev.dataTransfer.getData("curp");
                    const nombre = ev.dataTransfer.getData("nombre");

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

            // Desasignar todos los alumnos
            function desasignarTodos() {
                if (!confirm('¿Estás seguro de que deseas remover a todos los alumnos de todas las sesiones?')) {
                    return;
                }

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

            // Filtrar alumnos por búsqueda
            function filtrarAlumnos() {
                const busqueda = document.getElementById('busqueda-alumnos').value.toLowerCase();
                document.querySelectorAll('#alumnos-disponibles .alumno-item').forEach(alumno => {
                    const nombre = alumno.textContent.toLowerCase();
                    if (nombre.includes(busqueda)) {
                        alumno.style.display = 'block';
                    } else {
                        alumno.style.display = 'none';
                    }
                });
            }

            // Seleccionar/deseleccionar alumno
            function toggleSeleccionAlumno(element) {
                const curp = element.getAttribute('data-curp');
                if (alumnosSeleccionados.has(curp)) {
                    alumnosSeleccionados.delete(curp);
                    element.classList.remove('selected');
                } else {
                    alumnosSeleccionados.add(curp);
                    element.classList.add('selected');
                }
            }

            // Seleccionar todos los alumnos
            function seleccionarTodosAlumnos() {
                alumnosSeleccionados.clear();
                document.querySelectorAll('#alumnos-disponibles .alumno-item').forEach(el => {
                    const curp = el.getAttribute('data-curp');
                    alumnosSeleccionados.add(curp);
                    el.classList.add('selected');
                });
            }

            // Deseleccionar todos los alumnos
            function deseleccionarTodosAlumnos() {
                alumnosSeleccionados.clear();
                document.querySelectorAll('#alumnos-disponibles .alumno-item').forEach(el => {
                    el.classList.remove('selected');
                });
            }

            // Toggle acordeón
            function toggleAcordeon(header) {
                const content = header.nextElementSibling;
                const icon = header.querySelector('i');

                if (content.style.display === 'none') {
                    content.style.display = 'block';
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                } else {
                    content.style.display = 'none';
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            }

            // Inicialización
            window.onload = function () {
                // Configurar eventos de drag and drop
                document.querySelectorAll('.alumno-item').forEach(item => {
                    item.ondragstart = drag;
                    item.ondragend = dragEnd;
                    item.onclick = function (e) {
                        if (e.target === this) {
                            toggleSeleccionAlumno(this);
                        }
                    };
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

                // Inicializar acordeones
                document.querySelectorAll('.tema-contenido').forEach(content => {
                    content.style.display = 'none';
                });
            };

            // En la función drag (modificar)
            function drag(ev) {
                const curp = ev.target.getAttribute('data-curp');
                const nombre = ev.target.textContent.trim();
                ev.dataTransfer.setData("curp", curp);
                ev.dataTransfer.setData("nombre", nombre);
                ev.dataTransfer.effectAllowed = "move";
                ev.target.classList.add('dragging');

                // Mostrar tooltip con los temas
                const temas = ev.target.getAttribute('data-temas');
                if (temas) {
                    const tooltip = document.getElementById('tooltip-temas');
                    tooltip.textContent = `📋 ${temas}`;
                    tooltip.style.display = 'block';
                    tooltip.style.left = `${ev.pageX + 15}px`;
                    tooltip.style.top = `${ev.pageY + 15}px`;
                }
            }

            // Nueva función para mover el tooltip
            document.addEventListener('dragover', function (e) {
                const tooltip = document.getElementById('tooltip-temas');
                if (tooltip.style.display === 'block') {
                    tooltip.style.left = `${e.pageX + 15}px`;
                    tooltip.style.top = `${e.pageY + 15}px`;
                }
            });

            // Ocultar tooltip al finalizar el drag
            document.addEventListener('dragend', function () {
                document.getElementById('tooltip-temas').style.display = 'none';
            });
        </script>
        <div id="tooltip-temas" class="tooltip-temas"></div>
</body>

</html>