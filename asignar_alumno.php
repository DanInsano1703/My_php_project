<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['asignar'])) {
        $curps = $_POST['curp'] ?? [];
        $tema_id = intval($_POST['tema_id']);

        if (is_array($curps) && count($curps) > 0) {
            foreach ($curps as $curp) {
                $curp = $conexion->real_escape_string($curp);

                $res = $conexion->query("SELECT id FROM alumnos_tema WHERE curp = '$curp' AND tema_id = $tema_id");
                if ($res && $res->num_rows > 0)
                    continue;

                $conexion->query("INSERT INTO alumnos_tema (curp, tema_id) VALUES ('$curp', $tema_id)");
                $alumnos_tema_id = $conexion->insert_id;

                $resSub = $conexion->query("SELECT id FROM subtemas WHERE tema_id = $tema_id");
                while ($rowSub = $resSub->fetch_assoc()) {
                    $conexion->query("INSERT INTO alumnos_subtema_progreso (alumnos_tema_id, subtema_id) VALUES ($alumnos_tema_id, {$rowSub['id']})");
                }
            }
            $msg = "<p style='color:#00ff7f; font-weight:700; font-size:1.4rem; text-align:center; margin:20px 0; text-shadow:0 0 8px #000000ff;'>Registro exitoso.</p>";
        } else {
            $msg = "<p style='color:#ff4d4d; font-weight:700; font-size:1.2rem; text-align:center; margin:20px 0; text-shadow:0 0 6px #ff4d4d;'>No se seleccionó ningún alumno.</p>";
        }
    }

    if (isset($_POST['desinscribir'])) {
        $curpDes = $conexion->real_escape_string($_POST['curp_desinscribir'] ?? '');
        $temasDes = $_POST['temas_desinscribir'] ?? [];

        if ($curpDes && is_array($temasDes) && count($temasDes) > 0) {
            foreach ($temasDes as $temaDes) {
                $temaDes = intval($temaDes);
                $resId = $conexion->query("SELECT id FROM alumnos_tema WHERE curp = '$curpDes' AND tema_id = $temaDes");
                if ($resId && $row = $resId->fetch_assoc()) {
                    $alumnos_tema_id = $row['id'];
                    $conexion->query("DELETE FROM alumnos_subtema_progreso WHERE alumnos_tema_id = $alumnos_tema_id");
                    $conexion->query("DELETE FROM alumnos_tema WHERE id = $alumnos_tema_id");
                }
            }
            $msg = "<p style='color:#00ff7f; font-weight:700; font-size:1.3rem; text-align:center; margin:20px 0; text-shadow:0 0 8px #00ff7f;'>Alumno desinscrito correctamente de los temas seleccionados.</p>";
        } else {
            $msg = "<p style='color:#ff4d4d; font-weight:700; font-size:1.2rem; text-align:center; margin:20px 0; text-shadow:0 0 6px #ff4d4d;'>Seleccione un alumno y al menos un tema para desinscribir.</p>";
        }
    }
}

$temas = $conexion->query("SELECT * FROM temas ORDER BY nombre");
$alumnos = $conexion->query("
    SELECT a.curp, a.nombre, a.apellidos, GROUP_CONCAT(t.nombre SEPARATOR ', ') AS temas_actuales
    FROM alumnos a
    LEFT JOIN alumnos_tema at ON a.curp = at.curp
    LEFT JOIN temas t ON at.tema_id = t.id
    WHERE a.activo = 1
    GROUP BY a.curp
    ORDER BY a.apellidos, a.nombre
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Asignar y Desinscribir Alumnos a Temas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body,
        html {
            margin: 0;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
            background: #f4f7f4;
            /* ligeramente más verdoso suave */

            color: #222;
            min-height: 100vh;
        }

        h2 {
            color: #000000ff;
            /* verde claro para buen contraste */
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
        }

        form {
            background: white;
            max-width: 650px;
            margin: 0 auto 40px auto;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(46, 125, 50, 0.25);
            box-sizing: border-box;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
            color: #000000ff;
            /* verde dollar fuerte */
        }

        select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #a5d6a7;
            /* verde claro para borde */
            font-size: 1rem;
            box-sizing: border-box;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
            line-height: 2.4em;
            font-weight: normal;
            color: #000;
        }

        select:focus {
            border-color: #2e7d32;
            /* verde dollar */
            box-shadow: 0 0 8px rgba(46, 125, 50, 0.6);
        }

        .temas-verde {
            color: #2e7d32;
            /* verde dollar */
            font-weight: 700;
            margin-top: -20px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-family: 'Poppins', sans-serif;
        }

        button[type="submit"] {
            width: 100%;
            padding: 14px 0;
            background: #2e7d32;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background: #1b5e20;
        }

        /* Checkbox container */
        #checkboxesTemas {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #a5d6a7;
            /* verde claro */
            padding: 10px;
            border-radius: 8px;
        }

        #checkboxesTemas label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2e7d32;
            /* verde dollar negrita para nombres de temas */
            cursor: pointer;
        }
    </style>
</head>

<body>
    <?php include 'navbar2.php'; ?>

    <h2>Asignar Alumnos a un Tema</h2>
    <?php if (!empty($msg))
        echo "<div class='msg'>$msg</div>"; ?>
    <form method="POST" id="formAsignar">
        <label for="alumnos">Seleccione uno o varios alumnos (Ctrl+clic o Shift+clic):</label>
        <select name="curp[]" id="alumnos" multiple size="15" required>
            <?php while ($al = $alumnos->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($al['curp']) ?>">
                    <?= htmlspecialchars($al['nombre'] . ' ' . $al['apellidos'] . ' (' . $al['curp'] . ')') ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Mostrar temas inscritos fuera del select -->
        <div id="temasInscritos" class="temas-verde"></div>

        <label for="tema">Seleccione el tema:</label>
        <select name="tema_id" id="tema" required>
            <option value="">Seleccione un tema</option>
            <?php
            $temas = $conexion->query("SELECT * FROM temas ORDER BY nombre");
            while ($t = $temas->fetch_assoc()): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="asignar">Asignar seleccionados</button>
    </form>


    <script>
        // Muestra los temas inscritos en asignar al seleccionar alumno(s)
        const selectAlumnos = document.getElementById('alumnos');
        const temasInscritosDiv = document.getElementById('temasInscritos');

        // Mapa curp -> temas (preparado en PHP)
        const alumnoTemasMap = {
            <?php
            $alumnos->data_seek(0);
            $arr = [];
            while ($al = $alumnos->fetch_assoc()) {
                $temas = htmlspecialchars($al['temas_actuales']);
                $curp = htmlspecialchars($al['curp']);
                $arr[] = "'$curp': '$temas'";
            }
            echo implode(",\n", $arr);
            ?>
        };

        function actualizarTemasInscritos() {
            const selected = Array.from(selectAlumnos.selectedOptions).map(opt => opt.value);
            if (selected.length === 0) {
                temasInscritosDiv.textContent = '';
                return;
            }
            // Mostrar temas únicos combinados de todos los seleccionados
            let temasSet = new Set();
            selected.forEach(curp => {
                if (alumnoTemasMap[curp]) {
                    alumnoTemasMap[curp].split(',').forEach(t => {
                        if (t.trim()) temasSet.add(t.trim());
                    });
                }
            });
            if (temasSet.size === 0) {
                temasInscritosDiv.textContent = 'Ningún tema inscrito';
            } else {
                temasInscritosDiv.textContent = Array.from(temasSet).join(', ');
            }
        }
        selectAlumnos.addEventListener('change', actualizarTemasInscritos);

        // Para el form desinscribir, carga temas con checkbox con AJAX
        const selectAlumnoDes = document.getElementById('alumno_desinscribir');
        const divTemas = document.getElementById('temasAlumno');
        const contCheckboxes = document.getElementById('checkboxesTemas');

        selectAlumnoDes.addEventListener('change', function () {
            const curp = this.value;
            contCheckboxes.innerHTML = '';
            divTemas.style.display = 'none';

            if (!curp) return;

            fetch('get_temas_alumno.php?curp=' + encodeURIComponent(curp))
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        contCheckboxes.innerHTML = '<p>El alumno no está inscrito en ningún tema.</p>';
                    } else {
                        data.forEach(tema => {
                            const label = document.createElement('label');
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'temas_desinscribir[]';
                            checkbox.value = tema.id;
                            checkbox.checked = true;
                            label.appendChild(checkbox);
                            label.appendChild(document.createTextNode(' ' + tema.nombre));
                            contCheckboxes.appendChild(label);
                        });
                    }
                    divTemas.style.display = 'block';
                })
                .catch(() => {
                    contCheckboxes.innerHTML = '<p>Error al cargar temas.</p>';
                    divTemas.style.display = 'block';
                });
        });

        // Inicializa la lista temas inscritos vacía
        actualizarTemasInscritos();
    </script>
</body>

</html>