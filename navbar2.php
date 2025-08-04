<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Navbar Verde Dollar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #f4f7f4;
      /* verde muy suave para fondo */
      color: #333;
      padding-top: 56px;
      /* espacio para el nav fijo */
    }

    /* Navbar estilo verde dollar */
    nav.supernav {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background: #e8f5e9;
      /* verde muy claro */
      box-shadow: 0 2px 8px rgba(46, 125, 50, 0.15);
      padding: 0.5rem 1.5rem;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    ul.supernav-menu {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 1.5rem;
      align-items: center;
    }

    ul.supernav-menu li a {
      color: #000000ff;
      /* verde dollar oscuro */
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.4rem 0.7rem;
      border-radius: 6px;
      background: none;
      transition: background 0.3s, box-shadow 0.3s, color 0.3s;
      font-size: 1rem;
    }

    ul.supernav-menu li a i {
      font-size: 1.2rem;
    }

    ul.supernav-menu li a:hover,
    ul.supernav-menu li a:focus,
    ul.supernav-menu li a.active {
      background: #2e7d32;
      color: #fff;
      box-shadow: 0 0 8px rgba(46, 125, 50, 0.6);
      outline: none;
      transform: scale(1.05);
      transition: background 0.3s, box-shadow 0.3s, color 0.3s, transform 0.2s;
    }

    /* Responsive */
    @media (max-width: 768px) {
      nav.supernav {
        padding: 0.5rem 1rem;
      }

      ul.supernav-menu {
        flex-direction: column;
        gap: 0.8rem;
      }

      ul.supernav-menu li a {
        width: 100%;
        justify-content: center;
        padding: 0.6rem 0;
      }
    }
  </style>
</head>

<body>

  <nav class="supernav">
    <ul class="supernav-menu">
      <li><a href="lista_alumnos.php"><i class="bi bi-house-door-fill"></i> Volver al inicio</a></li>
      <?php if ($tipo === 'admin'): ?>
        <li><a href="crear_tema.php"><i class="bi bi-journal-plus"></i> Crear / Editar temas</a></li>
        <li><a href="asignar_alumno.php"><i class="bi bi-person-check-fill"></i> Asignar Alumnos</a></li>
      <?php endif; ?>
      <li><a href="progreso.php"><i class="bi bi-graph-up"></i> Ver progresos</a></li>
    </ul>
  </nav>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
</body>

</html>