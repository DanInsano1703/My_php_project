<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

// Obtener CURP del alumno
$curp = $_GET['curp'] ?? '';

if (!$curp) {
    die("CURP no proporcionado");
}

// Obtener datos del alumno
$stmt = $conexion->prepare("SELECT nombre, apellidos FROM alumnos WHERE curp = ?");
$stmt->bind_param("s", $curp);
$stmt->execute();
$resultadoAlumno = $stmt->get_result();
$alumno = $resultadoAlumno->fetch_assoc();
if (!$alumno) {
    die("Alumno no encontrado.");
}

// Obtener todas las clases
$clases = $conexion->query("SELECT id, nombre FROM clases ORDER BY nombre");

// Obtener clases ya asignadas al alumno
$stmt = $conexion->prepare("SELECT clase_id FROM alumno_clases WHERE alumno_curp = ?");
$stmt->bind_param("s", $curp);
$stmt->execute();
$resultadoClases = $stmt->get_result();
$clasesAsignadas = [];
while ($fila = $resultadoClases->fetch_assoc()) {
    $clasesAsignadas[] = $fila['clase_id'];
}

// Procesar asignación de clases
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clases'])) {
    $conexion->query("DELETE FROM alumno_clases WHERE alumno_curp = '$curp'");
    foreach ($_POST['clases'] as $claseId) {
        $stmt = $conexion->prepare("INSERT INTO alumno_clases (alumno_curp, clase_id) VALUES (?, ?)");
        $stmt->bind_param("si", $curp, $claseId);
        $stmt->execute();
    }
    header("Location: asignar_clases.php?curp=$curp&success=1");
    exit;
}

// Procesar creación de nueva clase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nueva_clase'])) {
    $nuevaClase = trim($_POST['nueva_clase']);
    if ($nuevaClase !== '') {
        $stmt = $conexion->prepare("INSERT IGNORE INTO clases (nombre) VALUES (?)");
        $stmt->bind_param("s", $nuevaClase);
        $stmt->execute();
        header("Location: asignar_clases.php?curp=$curp&newclass=1");
        exit;
    }
}

// Procesar eliminación de clase
if (isset($_GET['delclase'])) {
    $delClaseId = intval($_GET['delclase']);
    $conexion->query("DELETE FROM clases WHERE id = $delClaseId");
    header("Location: asignar_clases.php?curp=$curp&delclass=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Clases</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General body */
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        h2, h4, h5 {
            font-weight: 700;
        }
        /* Card statistic style without hover scale */
        .card-stat {
            text-align: center;
            padding: 25px 15px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgb(0 0 0 / 0.1);
            cursor: default; /* quitar cursor pointer */
            transition: box-shadow 0.3s ease; /* solo transición sombra */
            user-select: none;
        }
        .card-stat:hover {
            box-shadow: 0 10px 20px rgb(0 0 0 / 0.1); /* sin cambio */
            transform: none;
        }
        .card-stat h3 {
            font-size: 3rem;
            margin-bottom: 0.3rem;
            letter-spacing: 1.2px;
        }
        .card-stat p {
            font-size: 1.1rem;
            font-weight: 600;
        }
        /* Cards container */
        .cards-row {
            margin-bottom: 2.5rem;
        }
        /* Card containers for charts */
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgb(0 0 0 / 0.05);
            transition: box-shadow 0.3s ease;
        }
        .chart-card:hover {
            box-shadow: 0 15px 40px rgb(0 0 0 / 0.1);
        }
        /* Center text inside doughnut */
        .chart-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 700;
            text-align: center;
            color: #212529;
            pointer-events: none;
            user-select: none;
        }
        /* List styles */
        #listaPagados, #listaSinPagar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgb(0 0 0 / 0.07);
            padding: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            transition: opacity 0.4s ease;
        }
        #listaPagados.d-none, #listaSinPagar.d-none {
            opacity: 0;
            height: 0;
            padding: 0 1.5rem;
            overflow: hidden;
            pointer-events: none;
        }
        /* Scrollbar styling for lists */
        #listaPagados::-webkit-scrollbar,
        #listaSinPagar::-webkit-scrollbar {
            width: 8px;
        }
        #listaPagados::-webkit-scrollbar-thumb,
        #listaSinPagar::-webkit-scrollbar-thumb {
            background-color: #6c757d;
            border-radius: 10px;
        }
        /* Responsive tweaks */
        @media (max-width: 767.98px) {
            .card-stat h3 {
                font-size: 2.2rem;
            }
            .card-stat p {
                font-size: 1rem;
            }
        }

        /* Ajustes para tu formulario de clases */
        .form-check-label {
            cursor: pointer;
            user-select: none;
        }

        /* Efecto hover para botones */
        .btn {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .btn:hover {
            transform: scale(1.07);
            box-shadow: 0 15px 25px rgb(0 0 0 / 0.15);
        }

        /* Para que el botón guardar clases sea más visible y ancho */
        form > button[type="submit"] {
            font-weight: 600;
            font-size: 1.2rem;
        }

        /* Checkbox más grande y visible */
.form-check-input {
    transform: scale(1.5); /* Ajusta el tamaño */
    margin-right: 0.4rem; /* Menor espacio a la derecha */
    vertical-align: middle; /* Centrar verticalmente */
    cursor: pointer;
}

/* Etiqueta alineada verticalmente y con cursor pointer */
.form-check-label {
    cursor: pointer;
    user-select: none;
    line-height: 1.5;
    vertical-align: middle;
}
.form-check {
    display: flex;
    align-items: center;
    gap: 0.99rem; /* espacio entre checkbox y texto */
}

.form-check-input {
    transform: scale(1.5);
    margin-right: 0; /* ya no hace falta porque usamos gap */
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
    user-select: none;
    line-height: 1.5;
}

    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5">

    <h2 class="mb-4 text-center">Asignar Clases a <?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success text-center">¡Clases actualizadas correctamente!</div>
    <?php elseif (isset($_GET['newclass'])): ?>
        <div class="alert alert-success text-center">¡Clase creada exitosamente!</div>
    <?php elseif (isset($_GET['delclass'])): ?>
        <div class="alert alert-warning text-center">Clase eliminada.</div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5 card-stat">
        <div class="card-body">
            <form method="POST" novalidate>
                <h4 class="mb-3">Selecciona las clases a asignar:</h4>
                <div class="row">
                    <?php foreach ($clases as $clase): ?>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="clases[]" 
                                       value="<?= $clase['id'] ?>"
                                       id="clase_<?= $clase['id'] ?>"
                                       <?= in_array($clase['id'], $clasesAsignadas) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="clase_<?= $clase['id'] ?>">
                                    <?= htmlspecialchars($clase['nombre']) ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary mt-3 w-100">Asignar clases a este alumno</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm card-stat">
                <div class="card-body">
                    <h4 class="mb-3">Crear Nueva Clase</h4>
                    <form method="POST" class="row g-2" novalidate>
                        <div class="col-8">
                            <input type="text" name="nueva_clase" class="form-control" placeholder="Nombre de la nueva clase" required>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-success w-100">Agregar Clase</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm card-stat">
                <div class="card-body">
                    <h4 class="mb-3">Clases Existentes</h4>
                    <ul class="list-group">
                        <?php
                        $clasesExistentes = $conexion->query("SELECT id, nombre FROM clases ORDER BY nombre");
                        while ($claseExistente = $clasesExistentes->fetch_assoc()):
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($claseExistente['nombre']) ?>
                                <a href="?curp=<?= urlencode($curp) ?>&delclase=<?= $claseExistente['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('¿Seguro que deseas eliminar esta clase?')">
                                    Eliminar
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="lista_alumnos.php" class="btn btn-secondary btn-lg">← Volver al Listado</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
