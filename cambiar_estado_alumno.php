<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

if (!isset($_GET['curp'], $_GET['estado'])) {
    http_response_code(400);
    echo "Faltan parámetros";
    exit;
}

$curp = $conexion->real_escape_string($_GET['curp']);
$estado = $_GET['estado'] === '1' ? 1 : 0;

$sql = "UPDATE alumnos SET activo = $estado WHERE curp = '$curp'";

if ($conexion->query($sql)) {
    echo "Estado actualizado";
} else {
    http_response_code(500);
    echo "Error al actualizar: " . $conexion->error;
}
?>