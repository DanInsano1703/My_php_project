<?php
require_once __DIR__ . '/includes/bd.php';
$curp = $conexion->real_escape_string($_GET['curp'] ?? '');
$result = $conexion->query("
    SELECT t.id, t.nombre 
    FROM alumnos_tema at 
    JOIN temas t ON at.tema_id = t.id 
    WHERE at.curp = '$curp'
");

$temas = [];
while ($row = $result->fetch_assoc()) {
    $temas[] = $row;
}
header('Content-Type: application/json');
echo json_encode($temas);
