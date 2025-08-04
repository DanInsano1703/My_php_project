<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['curp'], $data['fecha'], $data['estado'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$curp = $data['curp'];
$fecha = $data['fecha'];
$estado = $data['estado'];

if (!in_array($estado, ['asistio', 'justifico', 'falto'])) {
    echo json_encode(['success' => false, 'error' => 'Estado inválido']);
    exit;
}

// Verificar que la fecha sea válida y no en futuro (opcional)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['success' => false, 'error' => 'Fecha inválida']);
    exit;
}
if ($fecha > date('Y-m-d')) {
    echo json_encode(['success' => false, 'error' => 'No se puede registrar asistencia futura']);
    exit;
}

// Aquí podrías querer verificar que el alumno exista

// Guardar o actualizar registro de asistencia
// Si no sabes la clase_id, y solo importa alumno+fecha, pon clase_id NULL o 0
// O ajusta según tu lógica real. Aquí uso NULL.

$stmt = $conexion->prepare("SELECT id FROM clases LIMIT 1");
$stmt->execute();
$clase = $stmt->get_result()->fetch_assoc();
$clase_id = $clase['id'] ?? 0;

$stmt = $conexion->prepare("INSERT INTO asistencia (alumno_curp, clase_id, fecha, estado) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE estado=VALUES(estado), registrado_en=NOW()");
$stmt->bind_param("siss", $curp, $clase_id, $fecha, $estado);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'estado' => $estado]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar']);
}
