<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

$dias_semana = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
$step = $_POST['step'] ?? 'seleccionar_tema';

// Obtener temas para selects
$temas = $conexion->query("SELECT id, nombre FROM temas ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Creador de Horarios - Academia Música</title>
    <style>
        /* Reset y base */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f8fa;
            color: #333;
            max-width: 720px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        h2 {
            color: #34495e;
            margin-bottom: 15px;
        }
        form {
            background: #fff;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgb(0 0 0 / 0.05);
            margin-bottom: 30px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
        }
        select, input[type="number"], input[type="time"] {
            width: 100%;
            max-width: 300px;
            padding: 8px 10px;
            border-radius: 5px;
            border: 1.5px solid #bbb;
            transition: border-color 0.3s;
            font-size: 1rem;
        }
        select:focus, input[type="number"]:focus, input[type="time"]:focus {
            border-color: #2980b9;
            outline: none;
        }
        .checkbox-group {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .checkbox-group label {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn {
            margin-top: 25px;
            background-color: #2980b9;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 7px;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.25s ease;
            width: 100%;
            max-width: 320px;
        }
        .btn:hover {
            background-color: #1f5980;
        }
        fieldset {
            border: 1px solid #bbb;
            padding: 15px 20px 25px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            background-color: #fafafa;
        }
        legend {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2980b9;
            padding: 0 8px;
        }
        .session-row {
            margin-bottom: 15px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .session-row label {
            margin: 0;
            white-space: nowrap;
        }
        .session-row input[type="time"] {
            width: 130px;
        }
        .message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        a.back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #2980b9;
            font-weight: 600;
            transition: color 0.3s;
        }
        a.back-link:hover {
            color: #1f5980;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #842029;
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
    </style>
</head>
<body>

<h1>Crear horarios</h1>

<?php

if ($step === 'seleccionar_tema'):
?>

    <form method="POST" novalidate>
        <input type="hidden" name="step" value="definir_sesiones">

        <label for="tema_id">Selecciona el tema:</label>
        <select name="tema_id" id="tema_id" required>
            <option value="">-- Seleccionar --</option>
            <?php foreach ($temas as $tema): ?>
                <option value="<?= escape($tema['id']) ?>"><?= escape($tema['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Días en que se impartirá el tema:</label>
        <div class="checkbox-group">
            <?php foreach ($dias_semana as $dia): ?>
                <label>
                    <input type="checkbox" name="dias[]" value="<?= escape($dia) ?>">
                    <?= escape($dia) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn">Siguiente</button>
    </form>

<?php
elseif ($step === 'definir_sesiones'):

    $tema_id = $_POST['tema_id'] ?? null;
    $dias = $_POST['dias'] ?? [];

    if (!$tema_id || empty($dias)):
        echo '<div class="error">Debe seleccionar un tema y al menos un día.</div>';
        echo '<a href="" class="back-link">Regresar</a>';
        exit;
    endif;
?>

    <form method="POST" novalidate>
        <input type="hidden" name="step" value="definir_horarios">
        <input type="hidden" name="tema_id" value="<?= escape($tema_id) ?>">

        <h2>¿Cuántas sesiones habrá por día para el tema seleccionado?</h2>

        <?php foreach ($dias as $dia): ?>
            <label for="sesiones_<?= escape($dia) ?>">
                Sesiones para <?= escape($dia) ?>:
            </label>
            <input type="number" name="sesiones[<?= escape($dia) ?>]" id="sesiones_<?= escape($dia) ?>" min="1" max="10" value="1" required>
        <?php endforeach; ?>

        <button type="submit" class="btn">Siguiente</button>
    </form>

<?php
elseif ($step === 'definir_horarios'):

    $tema_id = $_POST['tema_id'] ?? null;
    $sesiones = $_POST['sesiones'] ?? [];

    if (!$tema_id || empty($sesiones)):
        echo '<div class="error">Faltan datos para continuar.</div>';
        echo '<a href="" class="back-link">Regresar</a>';
        exit;
    endif;
?>

    <form method="POST" novalidate>
        <input type="hidden" name="step" value="guardar_horarios">
        <input type="hidden" name="tema_id" value="<?= escape($tema_id) ?>">

        <h2>Define los horarios para cada sesión por día</h2>

        <?php foreach ($sesiones as $dia => $cantidad): ?>
            <fieldset>
                <legend><?= escape($dia) ?> (<?= intval($cantidad) ?> sesión<?= intval($cantidad) !== 1 ? 'es' : '' ?>)</legend>

                <?php for ($i = 1; $i <= $cantidad; $i++): ?>
                    <div class="session-row">
                        <label for="inicio_<?= escape($dia) ?>_<?= $i ?>">Inicio <?= $i ?>:</label>
                        <input type="time" name="horarios[<?= escape($dia) ?>][inicio][]" id="inicio_<?= escape($dia) ?>_<?= $i ?>" required>

                        <label for="fin_<?= escape($dia) ?>_<?= $i ?>">Fin <?= $i ?>:</label>
                        <input type="time" name="horarios[<?= escape($dia) ?>][fin][]" id="fin_<?= escape($dia) ?>_<?= $i ?>" required>
                    </div>
                <?php endfor; ?>
            </fieldset>
        <?php endforeach; ?>

        <button type="submit" class="btn">Guardar horarios</button>
    </form>

<?php
elseif ($step === 'guardar_horarios'):

    $tema_id = $_POST['tema_id'] ?? null;
    $horarios = $_POST['horarios'] ?? [];

    if (!$tema_id || empty($horarios)):
        echo '<div class="error">Datos incompletos para guardar.</div>';
        echo '<a href="" class="back-link">Regresar</a>';
        exit;
    endif;

    $dias = array_keys($horarios);
    $dias_en_sql = "'" . implode("','", array_map([$conexion, 'real_escape_string'], $dias)) . "'";

    // Limpiar horarios antiguos para este tema y días seleccionados
    $sql_delete = "DELETE FROM horarios WHERE tema_id = ? AND dia_semana IN ($dias_en_sql)";
    $stmt = $conexion->prepare($sql_delete);
    $stmt->bind_param('i', $tema_id);
    $stmt->execute();

    // Insertar nuevos horarios
    $stmt_insert = $conexion->prepare("INSERT INTO horarios (tema_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)");

    foreach ($horarios as $dia => $sesiones) {
        $inicios = $sesiones['inicio'] ?? [];
        $fines = $sesiones['fin'] ?? [];

        foreach ($inicios as $i => $inicio) {
            $fin = $fines[$i] ?? null;

            if ($inicio && $fin) {
                $stmt_insert->bind_param('isss', $tema_id, $dia, $inicio, $fin);
                $stmt_insert->execute();
            }
        }
    }

    $stmt->close();
    $stmt_insert->close();

    echo '<div class="message">Horarios guardados correctamente.</div>';
    echo '<a href="" class="back-link">Crear más horarios</a>';

endif;

?>

</body>
</html>
