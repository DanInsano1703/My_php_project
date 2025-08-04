<?php
session_start();
require_once __DIR__ . '/includes/bd.php';
$tipo = $_SESSION['tipo_usuario'] ?? null;


error_reporting(E_ALL);
ini_set('display_errors', 1);



$temaId = isset($_GET['tema_id']) ? intval($_GET['tema_id']) : 0;
$curp = isset($_GET['curp']) ? trim($_GET['curp']) : null;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['datos_json'])) {
    header('Content-Type: application/json');
    $data = json_decode($_POST['datos_json'], true);
    $errores = [];

    foreach ($data['subtemas'] as $id => $campos) {
        $valor = intval($campos['aprendido']['valor'] ?? 0);
        $comentario = $conexion->real_escape_string($campos['aprendido']['comentario'] ?? '');
        $fecha = $valor ? "'" . date('Y-m-d H:i:s') . "'" : "NULL";

        $sql = "UPDATE alumnos_subtema_progreso SET 
                aprendido = $valor,
                aprendido_comentario = '$comentario',
                aprendido_fecha = $fecha
                WHERE id = $id";
        
        if (!$conexion->query($sql)) {
            $errores[] = "Error al actualizar subtema ID $id: " . $conexion->error;
        }
    }


    foreach ($data['subsubtemas'] as $id => $campos) {
        $updates = [];
        foreach ($campos as $campo => $info) {
            $valor = intval($info['valor'] ?? 0);
            $comentario = $conexion->real_escape_string($info['comentario'] ?? '');
            $fecha = $valor ? "'" . date('Y-m-d H:i:s') . "'" : "NULL";

            $updates[] = "$campo = $valor";
            $updates[] = "{$campo}_comentario = '$comentario'";
            $updates[] = "{$campo}_fecha = $fecha";
        }

        $sql = "UPDATE alumnos_subsubtema_progreso SET " . implode(", ", $updates) . " WHERE id = $id";
        if (!$conexion->query($sql)) {
          
        }
    }

    echo json_encode($errores ? ['error' => implode("; ", $errores)] : ['success' => true]);
    exit;
}


$temasRes = $conexion->query("SELECT * FROM temas ORDER BY nombre");
if (!$temasRes) {
    die("Error al obtener temas: " . $conexion->error);
}


$alumnosData = [];
if ($temaId) {

    $temaId = intval($temaId);
    if ($curp) {
        $curp = $conexion->real_escape_string($curp);
    }

  
    $subtemasSql = "
        SELECT p.id, p.aprendido, p.aprendido_comentario, p.aprendido_fecha,
               s.nombre AS subtema_nombre, s.id AS subtema_id,
               a.nombre AS alumno_nombre, a.apellidos AS alumno_apellidos,
               at.id AS alumnos_tema_id
        FROM alumnos_subtema_progreso p
        JOIN subtemas s ON p.subtema_id = s.id
        JOIN alumnos_tema at ON p.alumnos_tema_id = at.id
        JOIN alumnos a ON at.curp = a.curp
        WHERE at.tema_id = $temaId
          AND a.activo = 1
          " . ($curp ? "AND a.curp = '$curp'" : "") . "
        ORDER BY a.apellidos, a.nombre, s.orden
    ";
    
    $subtemasResult = $conexion->query($subtemasSql);
    if (!$subtemasResult) {
        die("Error en consulta de subtemas: " . $conexion->error);
    }
    $subtemas = $subtemasResult->fetch_all(MYSQLI_ASSOC);


    $subsubtemasSql = "
        SELECT 
            ss.id AS subsubtema_id, 
            ss.nombre AS subsubtema_nombre, 
            ss.subtema_id,
            p.id AS progreso_id,
            p.dia1, p.dia1_comentario, p.dia1_fecha,
            p.dia2, p.dia2_comentario, p.dia2_fecha,
            p.dia3, p.dia3_comentario, p.dia3_fecha,
            p.dia4, p.dia4_comentario, p.dia4_fecha,
            p.dia5, p.dia5_comentario, p.dia5_fecha,
            p.dia6, p.dia6_comentario, p.dia6_fecha,
            p.aprendido, p.aprendido_comentario, p.aprendido_fecha,
            at.id AS alumnos_tema_id, 
            a.nombre AS alumno_nombre, 
            a.apellidos AS alumno_apellidos
        FROM subsubtemas ss
        JOIN subtemas st ON ss.subtema_id = st.id
        JOIN alumnos_tema at ON at.tema_id = st.tema_id
        JOIN alumnos a ON at.curp = a.curp
        LEFT JOIN alumnos_subsubtema_progreso p ON p.alumnos_tema_id = at.id AND p.subsubtema_id = ss.id
        WHERE st.tema_id = $temaId
          AND a.activo = 1
          " . ($curp ? "AND a.curp = '$curp'" : "") . "
        ORDER BY a.apellidos, a.nombre, ss.orden
    ";
    
    $subsubtemasResult = $conexion->query($subsubtemasSql);
    if (!$subsubtemasResult) {
        die("Error en consulta de subsubtemas: " . $conexion->error);
    }
    $subsubtemas = $subsubtemasResult->fetch_all(MYSQLI_ASSOC);

 
    foreach ($subtemas as $st) {
        $key = $st['alumno_nombre'] . ' ' . $st['alumno_apellidos'];
        if (!isset($alumnosData[$key])) {
            $alumnosData[$key] = [
                'subtemas' => [],
                'subsubtemas' => []
            ];
        }
        $alumnosData[$key]['subtemas'][] = $st;
    }

    foreach ($subsubtemas as $ss) {
        $key = $ss['alumno_nombre'] . ' ' . $ss['alumno_apellidos'];
        if (!isset($alumnosData[$key])) {
            $alumnosData[$key] = [
                'subtemas' => [],
                'subsubtemas' => []
            ];
        }
        if (!isset($alumnosData[$key]['subsubtemas'][$ss['subtema_id']])) {
            $alumnosData[$key]['subsubtemas'][$ss['subtema_id']] = [];
        }
        $alumnosData[$key]['subsubtemas'][$ss['subtema_id']][] = $ss;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Progreso</title>
    <style>
 
:root {
  --primary-color: #036b1a;
  --primary-light: #c2f0d2;
  --primary-lighter: #e6f9ed;
  --success-color: #067d00;
  --error-color: #b80000;
  --text-color: #093d0b;
  --border-radius: 6px;
  --transition: all 0.3s ease;
}

body, html {
  font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
  background-color: #f5fdf8;
  color: var(--text-color);
  margin: 0;
  padding: 10px;
  line-height: 1.5;
}


#formProg {
  max-width: 500px;
  margin: 0 auto;
  padding-bottom: 60px;
}

h2 {
  text-align: center;
  color: var(--primary-color);
  margin: 10px 0 15px;
  font-size: 1.3rem;
  font-weight: 600;
  position: relative;
  padding-bottom: 8px;
}

h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 25%;
  width: 50%;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--primary-color), transparent);
}

h3 {
  color: var(--primary-color);
  background: var(--primary-light);
  padding: 8px 12px;
  border-radius: var(--border-radius);
  cursor: pointer;
  margin: 12px 0 6px;
  font-size: 1rem;
  border-left: 4px solid var(--primary-color);
  transition: var(--transition);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

h3:hover {
  background: #a8e8c1;
  transform: translateX(3px);
}

h3::after {
  content: '▼';
  font-size: 0.8rem;
  transition: var(--transition);
}

h3.active::after {
  transform: rotate(180deg);
}

.subtema {
  background: var(--primary-lighter);
  padding: 8px 10px;
  margin: 6px 0 6px 8px;
  border-radius: var(--border-radius);
  border-left: 3px solid var(--success-color);
  transition: var(--transition);
}

.subtema:hover {
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.subtema h4 {
  margin: 0 0 6px 0;
  font-size: 0.95rem;
  color: #045d15;
}

.subsubtemas {
  margin: 6px 0 6px 12px;
}

.subsubtema {
  padding: 8px 0;
  border-bottom: 1px solid rgba(96, 163, 118, 0.3);
  margin-bottom: 6px;
  transition: var(--transition);
}

.subsubtema:hover {
  border-bottom-color: var(--primary-color);
}

.subsubtema:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.subsubtema h5 {
  margin: 0 0 5px 0;
  font-size: 0.9rem;
  color: #036b1a;
}


.dia-checkbox {
  display: flex;
  align-items: center;
  margin: 4px 0;
  font-size: 0.85rem;
  position: relative;
  padding: 3px 0;
}

.dia-checkbox input[type="checkbox"] {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  border: 2px solid #06a723;
  border-radius: 4px;
  margin-right: 8px;
  cursor: pointer;
  position: relative;
  transition: var(--transition);
}

.dia-checkbox input[type="checkbox"]:checked {
  background-color: #06a723;
}

.dia-checkbox input[type="checkbox"]:checked::after {
  content: '✓';
  position: absolute;
  color: white;
  font-size: 12px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.dia-checkbox input[type="checkbox"]:hover {
  transform: scale(1.05);
  box-shadow: 0 0 0 2px rgba(6, 167, 35, 0.2);
}

.dia-checkbox label {
  cursor: pointer;
  transition: var(--transition);
}

.dia-checkbox:hover label {
  color: var(--primary-color);
}


.tooltip {
  position: relative;
  display: inline-flex;
  margin-left: 6px;
  cursor: pointer;
  transition: var(--transition);
}

.tooltip:hover {
  transform: scale(1.1);
}

.tooltip::before {
  content: '💬';
  font-size: 0.8rem;
}

.tooltip .tooltiptext {
  visibility: hidden;
  width: 180px;
  background-color: var(--primary-color);
  color: #fff;
  text-align: center;
  border-radius: var(--border-radius);
  padding: 6px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: opacity 0.2s, transform 0.2s;
  font-size: 0.8rem;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}

.tooltip:hover .tooltiptext {
  visibility: visible;
  opacity: 1;
  transform: translateX(-50%) translateY(-5px);
}


.fecha {
  font-size: 0.7rem;
  color: var(--success-color);
  margin-left: 6px;
  display: inline-block;
  opacity: 0.8;
}


.guardar {
  position: fixed;
  bottom: 15px;
  right: 15px;
  background: var(--primary-color);
  color: #fff;
  padding: 10px 20px;
  font-size: 0.9rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
  z-index: 100;
  transition: var(--transition);
  display: flex;
  align-items: center;
}

.guardar::before {
  content: '💾';
  margin-right: 6px;
}

.guardar:hover {
  background: #049c29;
  transform: translateY(-3px) scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.guardar:active {
  transform: translateY(-1px);
}


select {
  width: 100%;
  max-width: 220px;
  padding: 8px 12px;
  margin: 0 auto 15px;
  display: block;
  font-size: 0.9rem;
  border: 2px solid var(--primary-color);
  border-radius: var(--border-radius);
  background-color: var(--primary-lighter);
  color: var(--text-color);
  cursor: pointer;
  transition: var(--transition);
}

select:hover {
  border-color: #049c29;
  box-shadow: 0 0 0 2px rgba(6, 167, 35, 0.2);
}

select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(6, 167, 35, 0.3);
}


#msg {
  text-align: center;
  margin: 10px auto;
  font-size: 0.9rem;
  max-width: 90%;
  padding: 8px 12px;
  border-radius: var(--border-radius);
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

#msg.success {
  color: var(--success-color);
  background-color: rgba(6, 125, 0, 0.1);
}

#msg.error {
  color: var(--error-color);
  background-color: rgba(184, 0, 0, 0.1);
}


.contenido {
  display: none;
  margin-left: 5px;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from { 
    opacity: 0;
    max-height: 0;
    overflow: hidden;
  }
  to { 
    opacity: 1;
    max-height: 1000px;
  }
}


@keyframes pulse {
  0% { opacity: 0.6; }
  50% { opacity: 1; }
  100% { opacity: 0.6; }
}

.loading {
  animation: pulse 1.5s infinite;
}
.dias-container {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin: 8px 0;
}

.dia-checkbox {
  margin: 0px;
}
    </style>
</head>
<body>
    <?php include 'navbar2.php'; ?>
    
    <h2>Seguimiento de Progreso</h2>
    
    <form method="GET">
        <select name="tema_id" onchange="this.form.submit()">
            <option value="">-- Selecciona un tema --</option>
            <?php while ($tema = $temasRes->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($tema['id']) ?>" <?= $temaId == $tema['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tema['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($temaId): ?>
        <div id="msg"></div>
        
        <form id="formProgreso">
            <?php foreach ($alumnosData as $alumnoNombre => $data): ?>
                <div class="alumno">
                    <h3 onclick="toggleContenido(this)"><?= htmlspecialchars($alumnoNombre) ?></h3>
                    <div class="contenido">
                        <?php foreach ($data['subtemas'] as $subtema): ?>
                            <div class="subtema">
                                <h4><?= htmlspecialchars($subtema['subtema_nombre']) ?></h4>
                                
                             
                                <div class="dia-checkbox">
                                    <input type="checkbox" id="st_<?= $subtema['id'] ?>_aprendido" 
                                         <?= $subtema['aprendido'] ? 'checked' : '' ?>
                                         data-id="<?= $subtema['id'] ?>" 
                                         data-campo="aprendido"
                                         onclick="pedirComentario(this)">
                                    <label for="st_<?= $subtema['id'] ?>_aprendido">Aprendido</label>
                                    <?php if (!empty($subtema['aprendido_comentario'])): ?>
                                        <span class="tooltip">💬
                                            <span class="tooltiptext"><?= htmlspecialchars($subtema['aprendido_comentario']) ?></span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($subtema['aprendido_fecha'])): ?>
                                        <span class="fecha">(<?= date('d/m/Y', strtotime($subtema['aprendido_fecha'])) ?>)</span>
                                    <?php endif; ?>
                                </div>
                                
                         
                                <?php if (!empty($data['subsubtemas'][$subtema['subtema_id']])): ?>
                                    <?php foreach ($data['subsubtemas'][$subtema['subtema_id']] as $subsubtema): ?>
                                        <div class="subsubtema">
                                            <h5><?= htmlspecialchars($subsubtema['subsubtema_nombre']) ?></h5>
                                            
                                            <div class="dias-container">
                                                <!-- Checkboxes para los 6 días -->
                                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                                    <div class="dia-checkbox">
                                                        <input type="checkbox" 
                                                               id="ss_<?= $subsubtema['progreso_id'] ?>_dia<?= $i ?>"
                                                               <?= $subsubtema["dia$i"] ? 'checked' : '' ?>
                                                               data-id="<?= $subsubtema['progreso_id'] ?>"
                                                               data-campo="dia<?= $i ?>"
                                                               onclick="pedirComentario(this)">
                                                        <label for="ss_<?= $subsubtema['progreso_id'] ?>_dia<?= $i ?>">Día <?= $i ?></label>
                                                        <?php if (!empty($subsubtema["dia{$i}_comentario"])): ?>
                                                            <span class="tooltip">💬
                                                                <span class="tooltiptext"><?= htmlspecialchars($subsubtema["dia{$i}_comentario"]) ?></span>
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($subsubtema["dia{$i}_fecha"])): ?>
                                                            <span class="fecha">(<?= date('d/m/Y', strtotime($subsubtema["dia{$i}_fecha"])) ?>)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endfor; ?>
                                                
                                              
                                                <div class="dia-checkbox">
                                                    <input type="checkbox" 
                                                           id="ss_<?= $subsubtema['progreso_id'] ?>_aprendido"
                                                           <?= $subsubtema['aprendido'] ? 'checked' : '' ?>
                                                           data-id="<?= $subsubtema['progreso_id'] ?>"
                                                           data-campo="aprendido"
                                                           onclick="pedirComentario(this)">
                                                    <label for="ss_<?= $subsubtema['progreso_id'] ?>_aprendido">Aprendido</label>
                                                    <?php if (!empty($subsubtema['aprendido_comentario'])): ?>
                                                        <span class="tooltip">💬
                                                            <span class="tooltiptext"><?= htmlspecialchars($subsubtema['aprendido_comentario']) ?></span>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($subsubtema['aprendido_fecha'])): ?>
                                                        <span class="fecha">(<?= date('d/m/Y', strtotime($subsubtema['aprendido_fecha'])) ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="button" class="guardar" onclick="guardarProgreso()">Guardar Cambios</button>
        </form>
    <?php endif; ?>

    <script>
        function toggleContenido(element) {
            const contenido = element.nextElementSibling;
            contenido.style.display = contenido.style.display === 'none' ? 'block' : 'none';
        }
        
        function pedirComentario(checkbox) {
            const container = checkbox.closest('.dia-checkbox');
            const id = checkbox.dataset.id;
            const campo = checkbox.dataset.campo;
            
      
            let tooltip = container.querySelector('.tooltip');
            let comentarioActual = '';
            
            if (tooltip) {
                comentarioActual = tooltip.querySelector('.tooltiptext').textContent;
            }
            
            const nuevoComentario = prompt(`Ingrese comentario para ${campo}:`, comentarioActual);
            
            if (nuevoComentario !== null) {
                if (!tooltip) {
                  
                    tooltip = document.createElement('span');
                    tooltip.className = 'tooltip';
                    tooltip.innerHTML = '💬 <span class="tooltiptext"></span>';
                    container.appendChild(tooltip);
                }
                
            
                tooltip.querySelector('.tooltiptext').textContent = nuevoComentario;
                
             
                if (!checkbox.checked && nuevoComentario !== '') {
                    checkbox.checked = true;
                }
            } else {
           
                checkbox.checked = !checkbox.checked;
            }
        }
        
        async function guardarProgreso() {
            const msg = document.getElementById('msg');
            msg.textContent = "Guardando cambios...";
            msg.className = "";
            
         
            const subtemas = {};
            document.querySelectorAll('input[data-id][data-campo][id^="st_"]').forEach(input => {
                const id = input.dataset.id;
                const campo = input.dataset.campo;
                const tooltip = input.closest('.dia-checkbox').querySelector('.tooltip');
                
                if (!subtemas[id]) subtemas[id] = {};
                subtemas[id][campo] = {
                    valor: input.checked ? 1 : 0,
                    comentario: tooltip ? tooltip.querySelector('.tooltiptext').textContent : ''
                };
            });
            
        
            const subsubtemas = {};
            document.querySelectorAll('input[data-id][data-campo][id^="ss_"]').forEach(input => {
                const id = input.dataset.id;
                const campo = input.dataset.campo;
                const tooltip = input.closest('.dia-checkbox').querySelector('.tooltip');
                
                if (!subsubtemas[id]) subsubtemas[id] = {};
                subsubtemas[id][campo] = {
                    valor: input.checked ? 1 : 0,
                    comentario: tooltip ? tooltip.querySelector('.tooltiptext').textContent : ''
                };
            });
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'datos_json': JSON.stringify({
                            subtemas: subtemas,
                            subsubtemas: subsubtemas
                        })
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    msg.textContent = "Cambios guardados correctamente";
                    msg.className = "success";
                } else {
                    msg.textContent = "Error: " + (result.error || 'Error desconocido');
                    msg.className = "error";
                }
            } catch (error) {
                msg.textContent = "Error al conectar con el servidor";
                msg.className = "error";
                console.error(error);
            }
        }
    </script>
</body>
</html>