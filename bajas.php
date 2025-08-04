<?php
session_start();
$tipo = $_SESSION['tipo_usuario'] ?? null;
require_once __DIR__ . '/includes/bd.php';

// Obtener todos los alumnos con su estado
$alumnos = $conexion->query("SELECT curp, nombre, apellidos, activo FROM alumnos ORDER BY nombre, apellidos");
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Panel de Baja/Reactivación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
      :root {
  --primary-color: #3498db;
  --secondary-color: #2c3e50;
  --success-color: #2ecc71;
  --danger-color: #e74c3c;
  --warning-color: #f39c12;
  --info-color: #1abc9c;
  --light-color: #ecf0f1;
  --dark-color: #34495e;
  --white: #ffffff;
  --gray-light: #f8f9fa;
  --gray-medium: #e9ecef;
  --gray-dark: #6c757d;
  --border-radius: 0.375rem;
  --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  --transition: all 0.3s ease;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
  color: #212529;
  background-color: #f5f5f5;
  padding-bottom: 2rem;
}

main {
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 1.5rem;
}

h2 {
  color: var(--secondary-color);
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--primary-color);
  font-weight: 600;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
  box-shadow: var(--box-shadow);
  border-radius: var(--border-radius);
  background-color: var(--white);
  margin-bottom: 2rem;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background-color: var(--primary-color);
  color: var(--white);
}

th, td {
  padding: 1rem;
  text-align: left;
  vertical-align: middle;
}

th {
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

tbody tr {
  border-bottom: 1px solid var(--gray-medium);
  transition: var(--transition);
}

tbody tr:last-child {
  border-bottom: none;
}

tbody tr:hover {
  background-color: var(--gray-light);
}

tbody tr.inactive {
  background-color: rgba(231, 76, 60, 0.05);
}

tbody tr.inactive:hover {
  background-color: rgba(231, 76, 60, 0.08);
}

.badge {
  display: inline-block;
  padding: 0.35em 0.65em;
  font-size: 0.75em;
  font-weight: 700;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 50rem;
}

.badge.active {
  background-color: var(--success-color);
  color: var(--white);
}

.badge.inactive {
  background-color: var(--danger-color);
  color: var(--white);
}

.btn {
  display: inline-block;
  font-weight: 400;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  user-select: none;
  border: 1px solid transparent;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  line-height: 1.5;
  border-radius: var(--border-radius);
  transition: var(--transition);
  cursor: pointer;
  margin-right: 0.5rem;
  margin-bottom: 0.5rem;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.btn:active {
  transform: translateY(0);
  box-shadow: none;
}

.btn-secondary {
  color: var(--white);
  background-color: var(--gray-dark);
  border-color: var(--gray-dark);
}

.btn-secondary:hover {
  background-color: #5a6268;
  border-color: #545b62;
}

.btn-success {
  color: var(--white);
  background-color: var(--success-color);
  border-color: var(--success-color);
}

.btn-success:hover {
  background-color: #218838;
  border-color: #1e7e34;
}

.btn-danger {
  color: var(--white);
  background-color: var(--danger-color);
  border-color: var(--danger-color);
}

.btn-danger:hover {
  background-color: #c82333;
  border-color: #bd2130;
}

.btn-dark {
  color: var(--white);
  background-color: var(--secondary-color);
  border-color: var(--secondary-color);
}

.btn-dark:hover {
  background-color: #1d2b38;
  border-color: #1a252f;
}

@media (max-width: 768px) {
  th, td {
    padding: 0.75rem 0.5rem;
  }
  
  .btn {
    display: block;
    width: 100%;
    margin-right: 0;
  }
  
  .btn + .btn {
    margin-top: 0.5rem;
  }
}

/* Animaciones para feedback visual */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

tbody tr {
  animation: fadeIn 0.3s ease forwards;
}

tbody tr:nth-child(odd) {
  animation-delay: 0.05s;
}

tbody tr:nth-child(even) {
  animation-delay: 0.1s;
} 
  </style>
</head>

<body>

  <?php include 'navbar.php'; ?>

  <main>
    <h2>Dar de Baja o Reactivar Alumno</h2>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>CURP</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($alumno = $alumnos->fetch_assoc()): ?>
            <tr class="<?= !$alumno['activo'] ? 'inactive' : '' ?>">
              <td><?= htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']) ?></td>
              <td><?= htmlspecialchars($alumno['curp']) ?></td>
              <td>
                <?php if ($alumno['activo']): ?>
                  <span class="badge active">Activo</span>
                <?php else: ?>
                  <span class="badge inactive">Inactivo</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($alumno['activo']): ?>
                  <button class="btn btn-danger btn-toggle-estado" data-curp="<?= htmlspecialchars($alumno['curp']) ?>"
                    data-estado="0">
                    Desactivar
                  </button>
                <?php else: ?>
                  <button class="btn btn-success btn-toggle-estado" data-curp="<?= htmlspecialchars($alumno['curp']) ?>"
                    data-estado="1">
                    Reactivar
                  </button>
                <?php endif; ?>
                <button class="btn btn-dark btn-baja-absoluta" data-curp="<?= htmlspecialchars($alumno['curp']) ?>">
                  Baja Absoluta
                </button>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <a href="lista_alumnos.php" class="btn btn-secondary">← Volver al Inicio</a>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Evento para activar/desactivar alumno
      document.querySelectorAll('.btn-toggle-estado').forEach(btn => {
        btn.addEventListener('click', () => {
          const curp = btn.getAttribute('data-curp');
          const estado = btn.getAttribute('data-estado');

          if (!confirm(estado == "1" ?
            "¿Deseas reactivar este alumno?" :
            "¿Seguro que deseas desactivar este alumno?")) return;

          fetch(`cambiar_estado_alumno.php?curp=${encodeURIComponent(curp)}&estado=${estado}`)
            .then(response => response.text())
            .then(() => {
              const tr = btn.closest('tr');
              if (estado == "1") {
                tr.classList.remove('inactive');
                tr.querySelector('td:nth-child(3)').innerHTML = '<span class="badge active">Activo</span>';
                btn.textContent = "Dar de Baja";
                btn.className = "btn btn-danger btn-toggle-estado";
                btn.setAttribute('data-estado', '0');
              } else {
                tr.classList.add('inactive');
                tr.querySelector('td:nth-child(3)').innerHTML = '<span class="badge inactive">Inactivo</span>';
                btn.textContent = "Reactivar";
                btn.className = "btn btn-success btn-toggle-estado";
                btn.setAttribute('data-estado', '1');
              }
            });
        });
      });

      // Evento para baja absoluta
      document.querySelectorAll('.btn-baja-absoluta').forEach(btn => {
        btn.addEventListener('click', () => {
          const curp = btn.getAttribute('data-curp');

          if (!confirm("¡Esta acción eliminará TODO rastro del alumno de la base de datos!\n¿Seguro que deseas continuar?")) return;

          fetch(`baja_absoluta_alumno.php?curp=${encodeURIComponent(curp)}`)
            .then(response => response.text())
            .then(respuesta => {
              alert(respuesta);
              // Recargar la página para reflejar cambios
              location.reload();
            })
            .catch(() => alert("Error al eliminar el alumno."));
        });
      });
    });
  </script>

</body>

</html>
