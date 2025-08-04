<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

// =======================
// Crear un nuevo tema
// =======================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre_tema']) && !isset($_POST['editar_tema_id'])) {
    $nombreTema = trim($conexion->real_escape_string($_POST['nombre_tema']));
    if ($nombreTema !== '') {
        $conexion->query("INSERT INTO temas (nombre) VALUES ('$nombreTema')");
        $temaId = $conexion->insert_id;

        $ordenSubtema = 1;
        if (!empty($_POST['subtemas'])) {
            foreach ($_POST['subtemas'] as $subtemaNombre) {
                $subtemaNombre = trim($conexion->real_escape_string($subtemaNombre));
                if ($subtemaNombre !== '') {
                    $conexion->query("INSERT INTO subtemas (tema_id, nombre, orden) VALUES ($temaId, '$subtemaNombre', $ordenSubtema)");
                    $subtemaId = $conexion->insert_id;

                    // Crear progreso para cada alumno inscrito
                    $alumnosTema = $conexion->query("SELECT id FROM alumnos_tema WHERE tema_id = $temaId");
                    while ($alumno = $alumnosTema->fetch_assoc()) {
                        $alumnosTemaId = intval($alumno['id']);
                        $conexion->query("INSERT INTO alumnos_subtema_progreso (alumnos_tema_id, subtema_id) VALUES ($alumnosTemaId, $subtemaId)");

                        $subsubtemas = $conexion->query("SELECT id FROM subsubtemas WHERE subtema_id = $subtemaId");
                        while ($sst = $subsubtemas->fetch_assoc()) {
                            $conexion->query("INSERT INTO alumnos_subsubtema_progreso (
                                alumnos_tema_id, subsubtema_id,
                                dia1, dia2, dia3, dia4, dia5, dia6, aprendido
                            ) VALUES (
                                $alumnosTemaId, {$sst['id']},
                                0, 0, 0, 0, 0, 0, 0
                            )");
                        }
                    }
                    $ordenSubtema++;
                }
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// =======================
// Actualizar un tema
// =======================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_tema_id'])) {
    $temaId = intval($_POST['editar_tema_id']);
    $nuevoNombreTema = $conexion->real_escape_string($_POST['nombre_tema']);
    $conexion->query("UPDATE temas SET nombre = '$nuevoNombreTema' WHERE id = $temaId");

    // Actualizar o eliminar subtemas
    if (isset($_POST['subtemas'])) {
        foreach ($_POST['subtemas'] as $idSubtema => $nombreSubtema) {
            $nombreSubtema = trim($conexion->real_escape_string($nombreSubtema));
            if ($nombreSubtema === '') {
                // Borrar subsubtemas y su progreso relacionados
                $conexion->query("DELETE FROM alumnos_subsubtema_progreso WHERE subsubtema_id IN (SELECT id FROM subsubtemas WHERE subtema_id = $idSubtema)");
                $conexion->query("DELETE FROM subsubtemas WHERE subtema_id = $idSubtema");
                // Borrar progreso subtema y el subtema
                $conexion->query("DELETE FROM alumnos_subtema_progreso WHERE subtema_id = $idSubtema");
                $conexion->query("DELETE FROM subtemas WHERE id = $idSubtema");
            } else {
                $conexion->query("UPDATE subtemas SET nombre = '$nombreSubtema' WHERE id = $idSubtema");
            }
        }
    }

    // Reordenar subtemas
    if (isset($_POST['orden_subtemas'])) {
        $pos = 1;
        foreach ($_POST['orden_subtemas'] as $idSubtema) {
            $conexion->query("UPDATE subtemas SET orden = $pos WHERE id = " . intval($idSubtema));
            $pos++;
        }
    }

    // Insertar nuevos subtemas
    if (isset($_POST['nuevos_subtemas'])) {
        $orden = $pos ?? 1;
        foreach ($_POST['nuevos_subtemas'] as $nuevoSubtema) {
            $nuevoSubtema = trim($conexion->real_escape_string($nuevoSubtema));
            if ($nuevoSubtema !== '') {
                $conexion->query("INSERT INTO subtemas (tema_id, nombre, orden) VALUES ($temaId, '$nuevoSubtema', $orden)");
                $nuevoSubtemaId = $conexion->insert_id;

                $alumnosTema = $conexion->query("SELECT id FROM alumnos_tema WHERE tema_id = $temaId");
                while ($alumno = $alumnosTema->fetch_assoc()) {
                    $alumnosTemaId = intval($alumno['id']);
                    $conexion->query("INSERT INTO alumnos_subtema_progreso (alumnos_tema_id, subtema_id) VALUES ($alumnosTemaId, $nuevoSubtemaId)");
                }
                $orden++;
            }
        }
    }

    // Actualizar o eliminar subsubtemas
    if (isset($_POST['subsubtemas'])) {
        foreach ($_POST['subsubtemas'] as $idSubsubtema => $nombreSubsubtema) {
            $nombreSubsubtema = trim($conexion->real_escape_string($nombreSubsubtema));
            if ($nombreSubsubtema === '') {
                // Borrar progreso subsubtema y subsubtema
                $conexion->query("DELETE FROM alumnos_subsubtema_progreso WHERE subsubtema_id = $idSubsubtema");
                $conexion->query("DELETE FROM subsubtemas WHERE id = $idSubsubtema");
            } else {
                $conexion->query("UPDATE subsubtemas SET nombre = '$nombreSubsubtema' WHERE id = $idSubsubtema");
            }
        }
    }

    // Reordenar subsubtemas
    if (isset($_POST['orden_subsubtemas'])) {
        foreach ($_POST['orden_subsubtemas'] as $subtemaId => $ids) {
            $pos = 1;
            foreach ($ids as $idSubsubtema) {
                $conexion->query("UPDATE subsubtemas SET orden = $pos WHERE id = " . intval($idSubsubtema));
                $pos++;
            }
        }
    }

    // Insertar nuevos subsubtemas
    if (isset($_POST['nuevos_subsubtemas'])) {
        foreach ($_POST['nuevos_subsubtemas'] as $subtemaId => $nuevos) {
            $orden = $conexion->query("SELECT COALESCE(MAX(orden),0)+1 FROM subsubtemas WHERE subtema_id = " . intval($subtemaId))->fetch_row()[0];
            foreach ($nuevos as $nuevoSubsubtema) {
                $nuevoSubsubtema = trim($conexion->real_escape_string($nuevoSubsubtema));
                if ($nuevoSubsubtema !== '') {
                    $conexion->query("INSERT INTO subsubtemas (subtema_id, nombre, orden) VALUES (" . intval($subtemaId) . ", '$nuevoSubsubtema', $orden)");
                    $nuevoSubsubtemaId = $conexion->insert_id;

                    $alumnosTema = $conexion->query("SELECT id FROM alumnos_tema WHERE tema_id = $temaId");
                    while ($alumno = $alumnosTema->fetch_assoc()) {
                        $alumnosTemaId = intval($alumno['id']);
                        // Insertar con 7 campos (6 días + aprendido) inicializados en 0
                        $conexion->query("INSERT INTO alumnos_subsubtema_progreso (
                            alumnos_tema_id, subsubtema_id,
                            dia1, dia2, dia3, dia4, dia5, dia6, aprendido
                        ) VALUES (
                            $alumnosTemaId, $nuevoSubsubtemaId,
                            0, 0, 0, 0, 0, 0, 0
                        )");
                    }
                    $orden++;
                }
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// =======================
// Eliminar tema
// =======================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrar_tema_id'])) {
    $temaId = intval($_POST['borrar_tema_id']);
    $hayAlumnos = $conexion->query("SELECT COUNT(*) FROM alumnos_tema WHERE tema_id = $temaId")->fetch_row()[0];
    if ($hayAlumnos > 0) {
        echo "<script>alert('No se puede eliminar el tema porque tiene alumnos inscritos.');</script>";
    } else {
        $conexion->query("DELETE FROM alumnos_subsubtema_progreso WHERE subsubtema_id IN (SELECT id FROM subsubtemas WHERE subtema_id IN (SELECT id FROM subtemas WHERE tema_id = $temaId))");
        $conexion->query("DELETE FROM subsubtemas WHERE subtema_id IN (SELECT id FROM subtemas WHERE tema_id = $temaId)");
        $conexion->query("DELETE FROM alumnos_subtema_progreso WHERE subtema_id IN (SELECT id FROM subtemas WHERE tema_id = $temaId)");
        $conexion->query("DELETE FROM subtemas WHERE tema_id = $temaId");
        $conexion->query("DELETE FROM temas WHERE id = $temaId");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$temas = $conexion->query("SELECT * FROM temas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Gestión de Temas - Verde Dólar</title>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        /* Reset y base */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fcf8;
            color: #2e5e2e;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 960px;
            margin: auto;
        }

        h1 {
            text-align: center;
            color: #2e5e2e;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .card {
            background: #ffffff;
            border: 1px solid #c9e2c9;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .card-header {
            background: #5fa75f;
            color: white;
            padding: 12px 20px;
            cursor: pointer;
            user-select: none;
            font-weight: 600;
            border-radius: 5px 5px 0 0;
        }

        .card.show .card-header {
            background: #4a8a4a;
        }

        .card-content {
            padding: 15px 20px;
            display: none;
            background: #f2faf2;
            border-top: 1px solid #c9e2c9;
            border-radius: 0 0 5px 5px;
        }

        .card.show .card-content {
            display: block;
        }

        /* Inputs */
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #9ccc9c;
            background: #ffffff;
            color: #2e5e2e;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #5fa75f;
        }

        /* Listas */
        .sortable-list {
            list-style: none;
            padding-left: 20px;
            margin: 10px 0;
        }

        .sortable-list>li {
            background: #e9f5e9;
            border: 1px solid #c9e2c9;
            border-radius: 5px;
            padding: 8px 12px;
            margin-bottom: 10px;
            cursor: grab;
        }

        .sortable-list>li:hover {
            background: #dcefdc;
        }

        /* Sub-subtemas */
        .sortable-list>li>.sortable-list>li {
            background: #f4faf4;
            border-left: 3px solid #5fa75f;
            padding: 6px 10px;
            margin-bottom: 8px;
            border-radius: 5px;
            font-size: 0.95rem;
        }

        .sortable-list>li>.sortable-list>li:hover {
            background: #e9f5e9;
        }

        /* Botones */
        button {
            background: #5fa75f;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 16px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 8px;
        }

        button:hover {
            background: #4a8a4a;
        }

        /* Botón flotante */
        #btnGuardarFijo {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: #4a8a4a;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            font-size: 26px;
            text-align: center;
            line-height: 55px;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        #btnGuardarFijo:hover {
            background: #3b6e3b;
        }

        /* Botón eliminar */
        form[method="POST"] button[style*="background:#e74c3c"] {
            background: #c94d4d !important;
        }

        form[method="POST"] button[style*="background:#e74c3c"]:hover {
            background: #a83a3a !important;
        }
    </style>
</head>

<body>
    <?php include 'navbar2.php'; ?>
    <div class="container">
        <h1>Gestión de Temas, Subtemas y Subsubtemas</h1>

        <!-- Crear Tema -->
        <form method="POST" class="card" style="padding: 20px;">
            <input type="text" name="nombre_tema" placeholder="Nombre del tema" required autocomplete="off" autofocus>
            <div id="subtemasCrear">
                <input type="text" name="subtemas[]" placeholder="Subtema 1" autocomplete="off">
            </div>
            <button type="button" onclick="agregarSubtema('subtemasCrear')" aria-label="Agregar Subtema">➕ Agregar
                Subtema</button><br><br>
            <button type="submit">Crear Tema</button>
        </form>

        <?php while ($tema = $temas->fetch_assoc()): ?>
            <?php $subtemas = $conexion->query("SELECT * FROM subtemas WHERE tema_id = {$tema['id']} ORDER BY orden"); ?>
            <div class="card">
                <div class="card-header" onclick="toggleCard(this)" role="button" aria-expanded="false" tabindex="0"
                    onkeypress="if(event.key==='Enter'){toggleCard(this);}"><?= htmlspecialchars($tema['nombre']) ?></div>
                <div class="card-content" aria-hidden="true">
                    <form method="POST" onsubmit="return confirm('¿Seguro que quieres guardar los cambios de este tema?');">
                        <input type="hidden" name="editar_tema_id" value="<?= $tema['id'] ?>">
                        <input type="text" name="nombre_tema" value="<?= htmlspecialchars($tema['nombre']) ?>" required
                            autocomplete="off">
                        <ul id="subtemasList<?= $tema['id'] ?>" class="sortable-list" aria-label="Lista de subtemas">
                            <?php while ($subtema = $subtemas->fetch_assoc()): ?>
                                <?php $subsubtemas = $conexion->query("SELECT * FROM subsubtemas WHERE subtema_id = {$subtema['id']} ORDER BY orden"); ?>
                                <li>
                                    <input type="hidden" name="orden_subtemas[]" value="<?= $subtema['id'] ?>">
                                    <input type="text" name="subtemas[<?= $subtema['id'] ?>]"
                                        value="<?= htmlspecialchars($subtema['nombre']) ?>" autocomplete="off"
                                        placeholder="Nombre Subtema">
                                    <ul id="subsubtemasList<?= $subtema['id'] ?>" class="sortable-list"
                                        aria-label="Lista de subsubtemas">
                                        <?php while ($sst = $subsubtemas->fetch_assoc()): ?>
                                            <li>
                                                <input type="hidden" name="orden_subsubtemas[<?= $subtema['id'] ?>][]"
                                                    value="<?= $sst['id'] ?>">
                                                <input type="text" name="subsubtemas[<?= $sst['id'] ?>]"
                                                    value="<?= htmlspecialchars($sst['nombre']) ?>" autocomplete="off"
                                                    placeholder="Nombre Subsubtema">
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                    <button type="button"
                                        onclick="agregarNuevoSubsubtema('subsubtemasList<?= $subtema['id'] ?>', <?= $subtema['id'] ?>)"
                                        aria-label="Agregar Subsubtema">➕ Nuevo Subsubtema</button>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <div id="nuevosSubtemas<?= $tema['id'] ?>"></div>
                        <button type="button" onclick="agregarNuevoSubtema('nuevosSubtemas<?= $tema['id'] ?>')"
                            aria-label="Agregar Nuevo Subtema">➕ Nuevo Subtema</button><br>
                        <button type="submit" style="
        background:#3498db;
        color:white;
        border:none;
        border-radius:5px;
        padding:8px 16px;
        font-size:1rem;
        cursor:pointer;
        margin-top:15px;
        transition:background 0.3s ease;
    " onmouseover="this.style.background='#2c80b4'" onmouseout="this.style.background='#3498db'">
                            💾 Guardar Cambios
                        </button>

                    </form>
                    <form method="POST"
                        onsubmit="return confirm('¿Seguro que quieres eliminar este tema y todo su contenido?');"
                        style="margin-top: 12px;">
                        <input type="hidden" name="borrar_tema_id" value="<?= $tema['id'] ?>">
                        <button type="submit" style="background:#e74c3c; color:white;">🗑️ Eliminar Tema</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <button id="btnGuardarFijo" title="Guardar Cambios" 
    style="
        display:none;
        position:fixed;
        bottom:25px;
        right:25px;
        background:#3498db;
        border-radius:50%;
        width:55px;
        height:55px;
        font-size:26px;
        text-align:center;
        line-height:55px;
        color:white;
        border:none;
        cursor:pointer;
        transition:background 0.3s ease;
    "
    onmouseover="this.style.background='#2c80b4'"
    onmouseout="this.style.background='#3498db'"
>
    💾
</button>


    <script>
        function agregarSubtema(id) {
            let cont = document.getElementById(id);
            let input = document.createElement("input");
            input.type = "text";
            input.name = "subtemas[]";
            input.placeholder = "Nuevo Subtema";
            input.autocomplete = "off";
            cont.appendChild(input);
        }
        function agregarNuevoSubtema(id) {
            let cont = document.getElementById(id);
            let input = document.createElement("input");
            input.type = "text";
            input.name = "nuevos_subtemas[]";
            input.placeholder = "Nuevo Subtema";
            input.autocomplete = "off";
            cont.appendChild(input);
        }
        function agregarNuevoSubsubtema(id, subtemaId) {
            let cont = document.getElementById(id);
            let input = document.createElement("input");
            input.type = "text";
            input.name = `nuevos_subsubtemas[${subtemaId}][]`;
            input.placeholder = "Nuevo Subsubtema";
            input.autocomplete = "off";
            cont.appendChild(input);
        }
        function toggleCard(header) {
            const card = header.parentElement;
            card.classList.toggle('show');
            const expanded = card.classList.contains('show');
            header.setAttribute('aria-expanded', expanded);
            card.querySelector('.card-content').setAttribute('aria-hidden', !expanded);
            // Mostrar o esconder botón guardar flotante según si hay alguna card abierta
            document.getElementById('btnGuardarFijo').style.display = document.querySelector('.card.show') ? 'block' : 'none';
        }
        // Guardar cambios del tema visible
        document.getElementById('btnGuardarFijo').addEventListener('click', () => {
            let f = document.querySelector('.card.show form');
            if (f) f.submit();
        });

        // Inicializar Sortable para todos los temas y subtemas
        <?php
        $temasForJS = $conexion->query("SELECT id FROM temas");
        while ($temaJS = $temasForJS->fetch_assoc()):
            ?>
            new Sortable(document.getElementById('subtemasList<?= $temaJS['id'] ?>'), {
                animation: 150,
                handle: 'input[type="text"]',
                ghostClass: 'sortable-ghost'
            });
            <?php
            $subtemasJS = $conexion->query("SELECT id FROM subtemas WHERE tema_id = {$temaJS['id']}");
            while ($stJS = $subtemasJS->fetch_assoc()):
                ?>
                new Sortable(document.getElementById('subsubtemasList<?= $stJS['id'] ?>'), {
                    animation: 150,
                    handle: 'input[type="text"]',
                    ghostClass: 'sortable-ghost'
                });
            <?php endwhile; endwhile; ?>
    </script>
</body>

</html>