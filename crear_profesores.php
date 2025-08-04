<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

$mensaje = '';

// Eliminar profesor
if (isset($_GET['eliminar_id'])) {
    $idEliminar = intval($_GET['eliminar_id']);
    $stmtDel = $conexion->prepare("DELETE FROM profesores WHERE id = ?");
    $stmtDel->bind_param('i', $idEliminar);
    if ($stmtDel->execute()) {
        $mensaje = '<p style="color:#27ae60; font-weight:bold; margin-bottom:15px;">Profesor eliminado correctamente.</p>';
    } else {
        $mensaje = '<p style="color:#c0392b; font-weight:bold; margin-bottom:15px;">Error al eliminar profesor: ' . htmlspecialchars($conexion->error) . '</p>';
    }
    $stmtDel->close();
}

// Crear nuevo profesor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $mensaje = '<p style="color:#c0392b; font-weight:bold; margin-bottom:15px;">El nombre del profesor es obligatorio.</p>';
    } else {
        $stmt = $conexion->prepare("INSERT INTO profesores (nombre) VALUES (?)");
        $stmt->bind_param('s', $nombre);

        if ($stmt->execute()) {
            $mensaje = '<p style="color:#27ae60; font-weight:bold; margin-bottom:15px;">Profesor creado correctamente.</p>';
        } else {
            $mensaje = '<p style="color:#c0392b; font-weight:bold; margin-bottom:15px;">Error al crear profesor: ' . htmlspecialchars($conexion->error) . '</p>';
        }

        $stmt->close();
    }
}

// Obtener lista de profesores actuales
$profesores = $conexion->query("SELECT id, nombre FROM profesores ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestión de Profesores</title>
</head>

<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; background: #f9f9f9; padding: 20px; border-radius: 8px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);">

    <?php include 'navbar3.php'; ?>
    <br>
    <br>
    <br>

    <h1 style="color: #2c3e50; text-align: center;">Crear Nuevo Profesor</h1>

    <div><?= $mensaje ?></div>

    <form method="POST" action="" style="margin-bottom: 30px;">
        <input
            type="text"
            name="nombre"
            placeholder="Nombre completo del profesor"
            required
            style="width: calc(100% - 22px); padding: 10px; font-size: 16px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 15px;"
        >
        <button
            type="submit"
            style="background-color: #2980b9; color: white; border: none; padding: 10px 18px; border-radius: 6px; font-size: 16px; cursor: pointer; transition: background-color 0.2s;"
            onmouseover="this.style.backgroundColor='#1c5980';"
            onmouseout="this.style.backgroundColor='#2980b9';"
        >
            Crear Profesor
        </button>
    </form>

    <h2 style="margin-top: 0;">Profesores Registrados</h2>

    <?php if (count($profesores) > 0): ?>
        <ul style="list-style-type: none; padding-left: 0;">
            <?php foreach ($profesores as $profesor): ?>
                <li
                    style="background: #3498db; color: white; padding: 10px 14px; margin-bottom: 8px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;"
                >
                    <?= escape($profesor['nombre']) ?>
                    <a
                        href="?eliminar_id=<?= $profesor['id'] ?>"
                        class="btn-eliminar"
                        onclick="return confirm('¿Seguro que deseas eliminar a <?= escape(addslashes($profesor['nombre'])) ?>?');"
                        style="background-color: #2980b9; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 14px; cursor: pointer; text-decoration: none; transition: background-color 0.2s;"
                        onmouseover="this.style.backgroundColor='#1c5980';"
                        onmouseout="this.style.backgroundColor='#2980b9';"
                    >
                        Eliminar
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay profesores registrados.</p>
    <?php endif; ?>

</body>
</html>
