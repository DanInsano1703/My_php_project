<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

$tema_id = $_POST['tema_id'] ?? null;
$step = $_POST['step'] ?? 'seleccionar_tema';

// Obtener lista de temas
$temas = $conexion->query("SELECT id, nombre FROM temas ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

// Obtener lista de profesores
$profesores = $conexion->query("SELECT id, nombre FROM profesores ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

function escape($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

// Función para obtener profesores asignados a un horario
function getProfesoresAsignados($conexion, $horario_id)
{
    $stmt = $conexion->prepare("SELECT p.id, p.nombre FROM horario_profesores hp JOIN profesores p ON hp.profesor_id = p.id WHERE hp.horario_id = ?");
    $stmt->bind_param('i', $horario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profesores = [];
    while ($row = $result->fetch_assoc()) {
        $profesores[] = $row;
    }
    $stmt->close();
    return $profesores;
}

if ($step === 'guardar_horarios') {

    if (!$tema_id || empty($_POST['horarios'])) {
        echo "<p style='color:#c0392b; font-weight:700;'>Datos incompletos para guardar.</p>";
        echo '<a href="?" class="back-link">Regresar</a>';
        exit;
    }

    $horarios = $_POST['horarios'];
    $errors = [];

    $conexion->begin_transaction();

    try {
        $stmt_update = $conexion->prepare("UPDATE horarios SET hora_inicio = ?, hora_fin = ? WHERE id = ? AND tema_id = ?");
        $stmt_insert = $conexion->prepare("INSERT INTO horarios (tema_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");
        $stmt_delete = $conexion->prepare("DELETE FROM horarios WHERE id = ? AND tema_id = ?");
        $stmt_asignar = $conexion->prepare("INSERT INTO horario_profesores (horario_id, profesor_id) VALUES (?, ?)");

        foreach ($horarios as $dia => $sesiones) {
            if (!in_array($dia, $dias_semana))
                continue;

            foreach ($sesiones as $idx => $sesion) {
                $inicio = trim($sesion['inicio'] ?? '');
                $fin = trim($sesion['fin'] ?? '');
                $id = intval($sesion['id'] ?? 0);
                $borrar = isset($sesion['borrar']) && $sesion['borrar'] == '1';
                $nuevo = isset($sesion['nuevo']) && $sesion['nuevo'] == '1';
                $profesores_ids = $sesion['profesores_ids'] ?? '';
                $profesores_ids = array_filter(array_map('intval', explode(',', $profesores_ids)));

                if (!empty($inicio) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $inicio)) {
                    $errors[] = "Formato de hora inicio inválido para $dia: $inicio";
                    continue;
                }
                if (!empty($fin) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $fin)) {
                    $errors[] = "Formato de hora fin inválido para $dia: $fin";
                    continue;
                }

                if (!empty($inicio) && strlen($inicio) > 5)
                    $inicio = substr($inicio, 0, 5);
                if (!empty($fin) && strlen($fin) > 5)
                    $fin = substr($fin, 0, 5);

                if (!empty($inicio) && !empty($fin) && $inicio >= $fin) {
                    $errors[] = "La hora de fin debe ser mayor que la de inicio para $dia";
                    continue;
                }

                if ($borrar && $id > 0) {
                    // Borrar horario y sus asignaciones de profesores
                    $stmt_delete->bind_param('ii', $id, $tema_id);
                    if (!$stmt_delete->execute()) {
                        $errors[] = "Error al borrar horario para $dia";
                    }
                } elseif (!$borrar && !empty($inicio) && !empty($fin)) {
                    if ($nuevo || $id == 0) {
                        $stmt_insert->bind_param('isss', $tema_id, $dia, $inicio, $fin);
                        if (!$stmt_insert->execute()) {
                            $errors[] = "Error al insertar horario para $dia: " . $conexion->error;
                            continue;
                        }
                        $horario_id = $stmt_insert->insert_id;
                    } else {
                        $stmt_update->bind_param('ssii', $inicio, $fin, $id, $tema_id);
                        if (!$stmt_update->execute()) {
                            $errors[] = "Error al actualizar horario para $dia: " . $conexion->error;
                            continue;
                        }
                        $horario_id = $id;
                    }

                    // Guardar asignaciones de profesores
                    // Primero eliminar asignaciones previas
                    $conexion->query("DELETE FROM horario_profesores WHERE horario_id = $horario_id");

                    // Insertar nuevas asignaciones
                    foreach ($profesores_ids as $prof_id) {
                        $stmt_asignar->bind_param('ii', $horario_id, $prof_id);
                        if (!$stmt_asignar->execute()) {
                            $errors[] = "Error al asignar profesor ID $prof_id para horario ID $horario_id";
                        }
                    }
                }
            }
        }

        $stmt_update->close();
        $stmt_insert->close();
        $stmt_delete->close();
        $stmt_asignar->close();

        if (empty($errors)) {
            $conexion->commit();

            $step = 'mostrar_horarios';
        } else {
            $conexion->rollback();
            echo '<p style="background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:12px; border-radius:8px; font-weight:700;">Errores encontrados:</p>';
            echo '<ul>';
            foreach ($errors as $error) {
                echo '<li>' . escape($error) . '</li>';
            }
            echo '</ul>';
            $step = 'mostrar_horarios';
        }

    } catch (Exception $e) {
        $conexion->rollback();
        echo '<p style="background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:12px; border-radius:8px; font-weight:700;">Error en la base de datos: ' . escape($e->getMessage()) . '</p>';
    }
}

if ($step === 'mostrar_horarios' && $tema_id) {

    $stmt = $conexion->prepare("SELECT id, dia_semana, hora_inicio, hora_fin FROM horarios WHERE tema_id = ? 
        ORDER BY FIELD(dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'), hora_inicio");
    $stmt->bind_param('i', $tema_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $horarios = [];
    while ($row = $result->fetch_assoc()) {
        $horarios[$row['dia_semana']][] = $row;
    }
    $stmt->close();

    $nombre_tema = '';
    foreach ($temas as $t) {
        if ($t['id'] == $tema_id) {
            $nombre_tema = $t['nombre'];
            break;
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8" />
        <title>Editar Horarios</title>
        <style>
            /* Layout general */
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                height: 100vh;
                overflow: hidden;
                display: flex;
                background: #f9f9f9;
            }

            /* Contenedor principal */
            #main-container {
                display: flex;
                flex: 1;
                overflow: hidden;
            }

            /* Editor horarios a la izquierda */
            #editor-container {
                flex: 1;
                padding: 20px 30px;
                overflow-y: auto;
                background: white;
                box-sizing: border-box;
            }

            h2 {
                color: #2c3e50;
                margin-top: 0;
                margin-bottom: 20px;
            }

            form {
                max-width: 900px;
                margin: 0 auto 30px auto;
            }

            fieldset {
                margin-bottom: 25px;
                padding: 15px 20px;
                border-radius: 8px;
                border: 1px solid #ddd;
                background: #fafafa;
            }

            legend {
                font-weight: 700;
                color: #34495e;
                font-size: 1.1em;
                padding: 0 5px;
            }

            .sesion-row {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 12px;
                flex-wrap: wrap;
            }

            .sesion-row input[type="time"] {
                width: 110px;
                padding: 6px 8px;
                border-radius: 5px;
                border: 1px solid #ccc;
                font-size: 14px;
            }

            .sesion-row label input[type="checkbox"] {
                margin-right: 5px;
                transform: scale(1.2);
                cursor: pointer;
            }

            .sesion-dropzone {
                border: 2px dashed #ccc;
                min-height: 36px;
                padding: 6px 8px;
                border-radius: 6px;
                flex-grow: 1;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
                background: #fff;
                transition: background-color 0.3s, border-color 0.3s;
                cursor: pointer;
            }

            .sesion-dropzone.over {
                border-color: #2980b9;
                background: #d0e7ff;
            }

            .profesor-asignado {
                background: #3092d3ff;
                color: white;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 13px;
                display: inline-flex;
                align-items: center;
                user-select: none;
            }

            .btn-remove-profesor {
                margin-left: 8px;
                cursor: pointer;
                color: #e74c3c;
                font-weight: bold;
                user-select: none;
                font-size: 16px;
                line-height: 1;
            }

            button {
                cursor: pointer;
                padding: 10px 18px;
                border: none;
                background-color: #2980b9;
                color: white;
                border-radius: 8px;
                font-size: 16px;
                transition: background-color 0.2s;
                user-select: none;
                margin-top: 10px;
            }

            button:hover {
                background-color: #1c5980;
            }

            /* Menú profesores a la derecha */
            #profesores-list {
                position: sticky;
                top: 20px;
                width: 250px;
                max-height: calc(100vh - 40px);
                overflow-y: auto;
                background: #f0f0f0;
                border-left: 1px solid #ccc;
                padding: 15px 20px;
                box-sizing: border-box;
                font-size: 14px;
                z-index: 1000;
                user-select: none;
            }

            #profesores-list h3 {
                margin-top: 0;
                font-size: 18px;
                margin-bottom: 15px;
                text-align: center;
                color: #2c3e50;
            }

            .profesor-item {
                background: #3498db;
                color: white;
                margin-bottom: 10px;
                padding: 8px 12px;
                border-radius: 6px;
                cursor: grab;
                user-select: none;
                transition: background-color 0.3s;
            }

            .profesor-item:active {
                cursor: grabbing;
                background-color: #2a80b9;
            }
            
        </style>
    </head>

    <body>

     
           <?php include 'v.php'; ?>


        <div id="main-container">

            <div id="editor-container">
                <?php include 'navbar3.php'; ?>
                <br><br>
                <br>
                <br>
                <h1 style="
    text-align: center;
    color: #2c3e50;
    margin-top: 0;
    margin-bottom: 25px;
    font-weight: 700;
    display: inline-block;
    border-bottom: 4px solid #2980b9;
    padding-bottom: 6px;
">
                    Editar clases
                </h1>


                <form method="POST" style="
    display: flex;
    align-items: center;
    gap: 14px;
    background: #ffffff;
    padding: 12px 18px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
    margin-bottom: 25px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: none;
">
                    <input type="hidden" name="step" value="mostrar_horarios">
                    <label for="tema_id" style="
        font-weight: 600;
        color: #34495e;
        font-size: 1rem;
        min-width: 150px;
    ">Selecciona el tema:</label>
                    <select name="tema_id" id="tema_id" required onchange="this.form.submit()" style="
        flex: 0 1 300px;
        padding: 8px 12px;
        font-size: 1rem;
        color: #34495e;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #ffffff;
        transition: border-color 0.3s, box-shadow 0.3s;
    ">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($temas as $tema): ?>
                            <option value="<?= escape($tema['id']) ?>" <?= ($tema_id == $tema['id']) ? 'selected' : '' ?>>
                                <?= escape($tema['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>


                <h2 style="text-align: center; color: #2c3e50; margin-top: 0; margin-bottom: 20px; font-weight: 700;">
                    <?= escape($nombre_tema) ?>
                </h2>


                <form method="POST" novalidate>
                    <input type="hidden" name="step" value="guardar_horarios">
                    <input type="hidden" name="tema_id" value="<?= escape($tema_id) ?>">

                    <?php foreach ($dias_semana as $dia): ?>
                        <fieldset>
                            <legend><?= escape($dia) ?></legend>

                            <?php
                            $sesiones = $horarios[$dia] ?? [];
                            foreach ($sesiones as $idx => $sesion):
                                $profes_asignados = getProfesoresAsignados($conexion, $sesion['id']);
                                ?>
                                <div class="sesion-row" data-dia="<?= escape($dia) ?>" data-idx="<?= $idx ?>">
                                    <input type="hidden" name="horarios[<?= escape($dia) ?>][<?= $idx ?>][id]"
                                        value="<?= $sesion['id'] ?>">
                                    <label>Inicio:</label>
                                    <input type="time" name="horarios[<?= escape($dia) ?>][<?= $idx ?>][inicio]"
                                        value="<?= $sesion['hora_inicio'] ?>" required>
                                    <label>Fin:</label>
                                    <input type="time" name="horarios[<?= escape($dia) ?>][<?= $idx ?>][fin]"
                                        value="<?= $sesion['hora_fin'] ?>" required>
                                    <label><input type="checkbox" name="horarios[<?= escape($dia) ?>][<?= $idx ?>][borrar]"
                                            value="1">
                                        Borrar</label>
                                    <div class="sesion-dropzone" data-dia="<?= escape($dia) ?>" data-idx="<?= $idx ?>"
                                        ondragover="allowDrop(event)" ondrop="drop(event)">
                                        <?php foreach ($profes_asignados as $prof): ?>
                                            <span class="profesor-asignado" data-id="<?= $prof['id'] ?>">
                                                <?= escape($prof['nombre']) ?>
                                                <span class="btn-remove-profesor" onclick="removeProfesor(event)">✕</span>
                                            </span>
                                        <?php endforeach; ?>


                                        <input type="hidden" name="horarios[<?= escape($dia) ?>][<?= $idx ?>][profesores_ids]"
                                            value="<?= implode(',', array_column($profes_asignados, 'id')) ?>" />


                                    </div>

                                </div>
                            <?php endforeach; ?>

                            <div id="nuevas_sesiones_<?= escape($dia) ?>"></div>
                            <button type="button" class="btn-add-session" onclick="agregarSesion('<?= escape($dia) ?>')">Agregar
                                sesión</button>
                        </fieldset>
                    <?php endforeach; ?>

                 <button type="submit" style="
  position: fixed;
  bottom: 30px;
  left: 30px;
  background-color: #2e7d32;
  color: white;
  font-size: 1.2rem;
  font-weight: bold;
  padding: 14px 28px;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  box-shadow: 0 8px 20px rgba(46,125,50,0.4);
  transition: all 0.3s ease;
  z-index: 9999;
"
onmouseover="this.style.backgroundColor='#276129'; this.style.boxShadow='0 10px 25px rgba(46,125,50,0.6)'; this.style.transform='scale(1.05)';"
onmouseout="this.style.backgroundColor='#2e7d32'; this.style.boxShadow='0 8px 20px rgba(46,125,50,0.4)'; this.style.transform='scale(1)';"
>
  Guardar cambios
</button>

                    <br>
                    <hr>
                </form>

            </div>

            <style>
    #profesores-list {
    background: #f2f4f8; /* tono más claro, elegante */
    padding: 2rem;
    border-radius: 0px;
    box-shadow: 0 8px 20px rgba(0, 38, 99, 0.15);
    max-width: 360px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin-top: 65px; /* ajustado como pediste */
    transition: box-shadow 0.3s ease;
}

#profesores-list:hover {
    box-shadow: 0 10px 28px rgba(0, 38, 99, 0.25);
}

#profesores-list h3 {
    color: #002663;
    font-weight: 700;
    font-size: 1.8rem;
    margin-bottom: 0.8rem;
    border-bottom: 2px solid #002663;
    padding-bottom: 0.4rem;
    letter-spacing: 0.02em;
}

#profesores-list h4 {
    color: #3b3b3b;
    font-size: 1rem;
    margin-bottom: 1.5rem;
    font-weight: 500;
    opacity: 0.8;
}

.profesor-item {
    background: #3092d3;
    color: #fff;
    padding: 0.7rem 1.2rem;
    border-radius: 5px;
    margin-bottom: 0.75rem;
    cursor: grab;
    box-shadow: 0 3px 8px rgba(0, 38, 99, 0.25);
    transition: background-color 0.3s, box-shadow 0.3s, transform 0.2s;
    user-select: none;
}

.profesor-item:active {
    cursor: grabbing;
    box-shadow: 0 6px 14px rgba(0, 38, 99, 0.35);
    background-color: #0042a8;
    transform: scale(0.98);
}

.profesor-item:hover {
    background-color: #0042a8;
    box-shadow: 0 6px 14px rgba(0, 38, 99, 0.35);
}

hr {
    border: none;
    height: 1px;
    background: linear-gradient(to right, transparent, #002663, transparent);
    margin: 1.5rem 0;
}

#profesores-list li {
    list-style: none;
}

#profesores-list a {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.7rem 1.5rem;
    background-color: #002663;
    color: #f9f9f9;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 5px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(0, 38, 99, 0.4);
    transition: all 0.25s ease;
    cursor: pointer;
    user-select: none;
}

#profesores-list a:hover,
#profesores-list a:focus {
    background-color: #1e1e1e;
    box-shadow: 0 8px 18px rgba(0, 38, 99, 0.5);
    transform: translateY(-3px);
    outline: none;
}

#profesores-list a i {
    font-size: 1.4rem;
}

            </style>

            <div id="profesores-list">
                <h3>Mis profesores</h3>
                <h4>(Arrastra para asignar)</h4>
                <?php foreach ($profesores as $prof): ?>
                    <div class="profesor-item" draggable="true" data-id="<?= $prof['id'] ?>">
                        <?= escape($prof['nombre']) ?>
                    </div>
                <?php endforeach; ?>

                <hr>

                <li>
                    <a href="crear_profesores.php" aria-label="Crear nuevo profesor">
                        <i class="bi bi-person-badge-fill"></i> Nuevo profesor
                    </a>
                </li>
            </div>


            <script>
                let contadorSesiones = {};
                <?php foreach ($dias_semana as $dia): ?>
                    contadorSesiones['<?= $dia ?>'] = <?= count($horarios[$dia] ?? []) ?>;
                <?php endforeach; ?>

                // Drag & Drop profesores
                const profesoresList = document.querySelectorAll('.profesor-item');
                profesoresList.forEach(prof => {
                    prof.addEventListener('dragstart', ev => {
                        ev.dataTransfer.setData('text/plain', prof.dataset.id);
                    });
                });

                function allowDrop(ev) {
                    ev.preventDefault();
                    ev.currentTarget.classList.add('over');
                }

                function drop(ev) {
                    ev.preventDefault();
                    ev.currentTarget.classList.remove('over');

                    const profId = ev.dataTransfer.getData('text/plain');
                    const dropzone = ev.currentTarget;

                    if ([...dropzone.querySelectorAll('.profesor-asignado')].some(el => el.dataset.id === profId)) {
                        return;
                    }

                    const profElem = document.querySelector(`.profesor-item[data-id='${profId}']`);
                    if (!profElem) return;

                    const nombreProf = profElem.textContent.trim();

                    const span = document.createElement('span');
                    span.classList.add('profesor-asignado');
                    span.dataset.id = profId;
                    span.textContent = nombreProf;

                    const btnRemove = document.createElement('span');
                    btnRemove.className = 'btn-remove-profesor';
                    btnRemove.textContent = '✕';
                    btnRemove.onclick = removeProfesor;

                    span.appendChild(btnRemove);
                    dropzone.insertBefore(span, dropzone.querySelector('input[type="hidden"]'));

                    // Actualizar campo hidden
                    actualizarProfesoresIds(dropzone);
                }

                function removeProfesor(ev) {
                    ev.stopPropagation();
                    const span = ev.currentTarget.parentElement;
                    const dropzone = span.parentElement;
                    dropzone.removeChild(span);
                    actualizarProfesoresIds(dropzone);
                }

                function actualizarProfesoresIds(dropzone) {
                    const ids = [...dropzone.querySelectorAll('.profesor-asignado')].map(el => el.dataset.id);
                    const inputHidden = dropzone.querySelector('input[type="hidden"]');
                    inputHidden.value = ids.join(',');
                }

                // Agregar nueva sesión para un día
                function agregarSesion(dia) {
                    const contenedor = document.getElementById('nuevas_sesiones_' + dia);
                    const idx = contadorSesiones[dia]++;
                    const div = document.createElement('div');
                    div.className = 'sesion-row';
                    div.dataset.dia = dia;
                    div.dataset.idx = idx;
                    div.innerHTML = `
                    <input type="hidden" name="horarios[${dia}][${idx}][id]" value="0">
                    <input type="hidden" name="horarios[${dia}][${idx}][nuevo]" value="1">
                    <label>Inicio:</label>
                    <input type="time" name="horarios[${dia}][${idx}][inicio]" required>
                    <label>Fin:</label>
                    <input type="time" name="horarios[${dia}][${idx}][fin]" required>
                    <label><input type="checkbox" name="horarios[${dia}][${idx}][borrar]" value="1"> Borrar</label>
                    <div class="sesion-dropzone" data-dia="${dia}" data-idx="${idx}" ondragover="allowDrop(event)" ondrop="drop(event)">
                        <input type="hidden" name="horarios[${dia}][${idx}][profesores_ids]" value="">
                    </div>
                `;
                    contenedor.appendChild(div);
                }
            </script>
    </body>

    </html>

    <?php
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Editar Horarios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #f4f7f4;
      color: #333;
      padding-top: 80px;
    }

    h1 {
      font-weight: 700;
      color: #2e7d32;
      text-align: center;
      margin-bottom: 2rem;
      font-size: 1.8rem;
    }

    form {
      max-width: 400px;
      margin: 0 auto;
      background: #e8f5e9;
      padding: 2rem 1.5rem;
      border-radius: 16px;
      box-shadow: 0 6px 16px rgba(46, 125, 50, 0.25);
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      animation: fadeIn 0.6s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    select[name="tema_id"] {
      padding: 0.75rem;
      border: 2px solid #a5d6a7;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 500;
      color: #2e7d32;
      background-color: #fff;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    select[name="tema_id"]:focus {
      outline: none;
      border-color: #2e7d32;
      box-shadow: 0 0 8px rgba(46, 125, 50, 0.4);
    }

    button[type="submit"] {
      padding: 0.75rem 1.2rem;
      background-color: #2e7d32;
      color: #fff;
      font-weight: 700;
      font-size: 1rem;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    button[type="submit"]:hover,
    button[type="submit"]:focus {
      background-color: #276129;
      box-shadow: 0 0 12px rgba(39, 97, 41, 0.6);
      outline: none;
    }

    nav {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: #e8f5e9;
      box-shadow: 0 2px 8px rgba(46, 125, 50, 0.15);
      padding: 0.7rem 1.5rem;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    nav ul {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }

    nav a {
      color: #2e7d32;
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    nav a:hover {
      background-color: #c8e6c9;
      transform: translateY(-2px);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    nav i {
      font-size: 1.2rem;
      transition: transform 0.3s ease;
    }

    nav a:hover i {
      transform: scale(1.1);
    }

    @media (max-width: 480px) {
      form {
        padding: 1.5rem 1rem;
      }

      nav ul {
        gap: 1rem;
        flex-wrap: wrap;
      }
    }
  </style>
</head>

<body>
  <?php include 'navbar3.php'; ?>

  <h1>Selecciona un tema para editar</h1>

  <form method="POST">
    <input type="hidden" name="step" value="mostrar_horarios">
    <select name="tema_id" required>
      <option value="">-- Selecciona un tema --</option>
      <?php foreach ($temas as $tema): ?>
        <option value="<?= escape($tema['id']) ?>"><?= escape($tema['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit">Editar horarios</button>
  </form>

</body>
</html>
