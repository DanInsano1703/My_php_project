<?php
session_start();
require_once __DIR__ . '/includes/bd.php';

$data = json_decode(file_get_contents("php://input"), true);
$curp = $data['curp'] ?? '';
$fecha = $data['fecha'] ?? '';
$estado = $data['estado'] ?? '';

header('Content-Type: application/json');

if (!$curp || !$fecha || !$estado) {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
    exit;
}

if ($estado === 'borrar') {
    $stmt = $conexion->prepare("DELETE FROM asistencia WHERE alumno_curp = ? AND fecha = ?");
    $stmt->bind_param("ss", $curp, $fecha);
    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Error al borrar: '.$conexion->error]);
        exit;
    }
    echo json_encode(['ok' => true, 'estado' => 'sin_registro', 'titulo' => 'Sin registro']);
    exit;
}

// Primero, buscamos si existe el registro (por alumno + fecha)
$stmt = $conexion->prepare("SELECT id FROM asistencia WHERE alumno_curp = ? AND fecha = ?");
$stmt->bind_param("ss", $curp, $fecha);
$stmt->execute();
$resultado = $stmt->get_result();
$existe = $resultado->fetch_assoc();

if ($existe) {
    $stmt = $conexion->prepare("UPDATE asistencia SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $existe['id']);
    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Error al actualizar: '.$conexion->error]);
        exit;
    }
    $titulo = ($estado === 'asistio' ? 'Asistió' : ($estado === 'falto' ? 'Faltó' : ($estado === 'justifico' ? 'Justificó' : '')));
    echo json_encode(['ok' => true, 'estado' => $estado, 'titulo' => $titulo]);
    exit;
} else {
    // Obtener clase_id del alumno
    $stmt2 = $conexion->prepare("SELECT clase_id FROM alumno_clases WHERE alumno_curp = ? LIMIT 1");
    $stmt2->bind_param("s", $curp);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();
    $clase_id = $res2['clase_id'] ?? null;

    if (!$clase_id) {
        echo json_encode(['ok' => false, 'error' => 'No se encontró clase para el alumno']);
        exit;
    }

    $stmt = $conexion->prepare("INSERT INTO asistencia (alumno_curp, fecha, estado, clase_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $curp, $fecha, $estado, $clase_id);
    if (!$stmt->execute()) {
        echo json_encode(['ok' => false, 'error' => 'Error al insertar: '.$conexion->error]);
        exit;
    }
    $titulo = ($estado === 'asistio' ? 'Asistió' : ($estado === 'falto' ? 'Faltó' : ($estado === 'justifico' ? 'Justificó' : '')));
    echo json_encode(['ok' => true, 'estado' => $estado, 'titulo' => $titulo]);
    exit;
}
