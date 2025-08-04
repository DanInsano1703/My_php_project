<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

if (!isset($_GET['curp'])) {
    http_response_code(400);
    echo "CURP no proporcionada.";
    exit;
}

$curp = $conexion->real_escape_string($_GET['curp']);

// Tablas donde eliminar datos relacionados con el alumno en orden
$tablasEliminar = [
    'alumno_sesion' => "alumno_curp = '$curp'",
    'alumnos_subsubtema_progreso' => "alumnos_tema_id IN (SELECT id FROM alumnos_tema WHERE curp = '$curp')",
    'alumnos_subtema_progreso' => "alumnos_tema_id IN (SELECT id FROM alumnos_tema WHERE curp = '$curp')",
    'alumnos_tema' => "curp = '$curp'",
    'asistencia' => "alumno_curp = '$curp'",
    'pagos_mensualidad' => "alumno_curp = '$curp'",
    'alumno_clases' => "alumno_curp = '$curp'",
];

$conexion->begin_transaction();

try {
    foreach ($tablasEliminar as $tabla => $condicion) {
        $sql = "DELETE FROM $tabla WHERE $condicion";
        if (!$conexion->query($sql)) {
            throw new Exception("Error al eliminar datos en $tabla: " . $conexion->error);
        }
    }

    if (!$conexion->query("DELETE FROM alumnos WHERE curp = '$curp'")) {
        throw new Exception("Error al eliminar alumno: " . $conexion->error);
    }

    $conexion->commit();
    echo "Alumno con CURP $curp eliminado completamente de la base de datos.";

} catch (Exception $e) {
    $conexion->rollback();
    http_response_code(500);
    echo "Error durante la eliminación: " . $e->getMessage();
}
?>
