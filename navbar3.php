<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Navbar Verde Animado</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
   

    nav {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: #e8f5e9;
      box-shadow: 0 2px 8px rgba(46, 125, 50, 0.15);
      padding: 0.7rem 1.5rem;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    nav ul {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }

    nav a {
      color: #2e7d32;
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    nav a:hover {
      background-color: #c8e6c9;
      transform: translateY(-2px);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    nav i {
      font-size: 1.2rem;
      transition: transform 0.3s ease;
    }

    nav a:hover i {
      transform: scale(1.1);
    }
  </style>
</head>

<body>

  <nav role="navigation" aria-label="Menú principal">
    <ul>
      <li>
        <a href="lista_alumnos.php">
          <i class="bi bi-house-door-fill"></i> Inicio
        </a>
      </li>
      <li>
        <a href="editar_horarios.php">
          <i class="bi bi-clock-fill"></i> Horarios
        </a>
      </li>
      <li>
        <a href="asignar_alumnos_a_sesiones.php">
          <i class="bi bi-person-check-fill"></i> Asignar alumnos
        </a>
      </li>
      <li>
        <a href="asignacion_general.php">
          <i class="bi bi-person-plus-fill"></i> Asignación general
        </a>
      </li>
    </ul>
  </nav>

</body>

</html>
