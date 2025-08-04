<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Navbar Gris Oscuro con Logo Izquierda</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

    .navbar {
      display: flex;
      align-items: center;
      padding: 0.5rem 2rem;
      background-color: #f5faf5;
      /* blanco con un leve toque verde */
      position: relative;
      z-index: 1000;
      user-select: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    /* Logo */
    .navbar-logo {
      margin-right: 2rem;
      flex-shrink: 0;
    }

    .navbar-logo img {
      display: block;
      width: 100px;
      height: auto;
      object-fit: contain;
      filter: drop-shadow(0 0 5px #2e7d32);
    }

    /* Menú centrado */
    .navbar-menu {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      gap: 1.2rem;
      flex-grow: 1;
      justify-content: center;
      align-items: center;
    }

    /* Links y botones dropdown */
    .navbar-menu a,
    .nav-dropdown > button.dropdown-toggle {
      color: #333333;
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.4rem 0.7rem;
      border-radius: 6px;
      background: none;
      border: none;
      cursor: pointer;
      transition: background 0.3s, box-shadow 0.3s, color 0.3s;
      font-size: 1rem;
    }

    .navbar-menu a:hover,
    .nav-dropdown > button.dropdown-toggle:hover,
    .navbar-menu a.active,
    .nav-dropdown > button.dropdown-toggle[aria-expanded="true"] {
      background: #2e7d32;
      box-shadow: 0 0 8px rgba(46, 125, 50, 0.6);
      color: #fff;
    }

    /* Submenu */
    .nav-dropdown {
      position: relative;
    }

    .submenu {
      position: absolute;
      top: 100%;
      left: 0;
      background-color: #e8f5e9;
      list-style: none;
      margin: 0;
      padding: 0.5rem 0;
      min-width: 180px;
      border-radius: 6px;
      border: 1px solid #a5d6a7;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
      z-index: 1000;
    }

    .submenu li a {
      display: block;
      padding: 0.6rem 1rem;
      color: #333;
      font-weight: 500;
      transition: background 0.3s, padding-left 0.2s, color 0.3s;
    }

    .submenu li a:hover {
      background: #2e7d32;
      color: #fff;
      padding-left: 1.2rem;
    }

    /* Ocultar submenu inicialmente */
    .submenu[hidden] {
      display: none;
    }

    .submenu.show {
      display: block;
    }

    /* Botón toggle para móvil */
    .navbar-toggle {
      background: none;
      border: none;
      color: #333;
      font-size: 1.8rem;
      cursor: pointer;
      margin-left: 2rem;
      display: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .navbar {
        flex-wrap: wrap;
        padding: 0.5rem 1rem;
      }

      .navbar-logo {
        margin-right: 0;
        margin-bottom: 0.5rem;
        width: 100%;
        display: flex;
        justify-content: center;
      }

      .navbar-menu {
        flex-grow: 0;
        width: 100%;
        flex-direction: column;
        gap: 0;
        display: none;
      }

      .navbar-menu.show {
        display: flex;
      }

      .navbar-toggle {
        display: inline-flex;
        margin-left: 0;
      }

      .submenu {
        position: static;
        background: #e8f5e9;
        box-shadow: none;
        border-radius: 0;
        border: none;
        padding-left: 1rem;
      }

      .submenu li a {
        padding: 0.7rem 1rem;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar" role="navigation" aria-label="Menú principal">
    <a href="#" class="navbar-logo" aria-label="Inicio">
      <img src="musiclogo.png" alt="Logo" width="100" height="auto" />
    </a>

    <button aria-label="Abrir menú" class="navbar-toggle" id="navbarToggle" aria-expanded="false"
      aria-controls="navbarMenu">
      <i class="bi bi-list"></i>
    </button>

    <ul class="navbar-menu" id="navbarMenu">
      <li><a href="lista_alumnos.php"><i class="bi bi-people-fill"></i> Mis alumnos</a></li>

      <?php if ($tipo === 'admin'): ?>
        <li><a href="pagos.php"><i class="bi bi-cash-stack"></i> Pagos</a></li>
      <?php endif; ?>

      <?php if ($tipo === 'admin'): ?>
        <li class="nav-dropdown">
          <button class="dropdown-toggle" aria-expanded="false" aria-controls="submenuGestionTemas" aria-haspopup="true">
            <i class="bi bi-person-fill"></i> Alumno <i class=""></i>
          </button>
          <ul class="submenu" id="submenuGestionTemas" hidden>
            <li><a href="Registro.php"><i class="bi bi-person-plus-fill"></i> Nuevo Alumno</a></li>
            <li><a href="bajas.php"><i class="bi bi-person-dash-fill"></i> Dar de Baja Alumno</a></li>
          </ul>
        </li>
      <?php endif; ?>

      <li class="nav-dropdown">
        <button class="dropdown-toggle" aria-expanded="false" aria-controls="submenuAsistencia" aria-haspopup="true">
          <i class="bi bi-clipboard-data"></i> Asistencia <i class=""></i>
        </button>
        <ul class="submenu" id="submenuAsistencia" hidden>
          <li><a href="pasar_lista.php"><i class="bi bi-clipboard-check"></i> Pasar Lista</a></li>
          <li><a href="ver_asistencia_diaria.php"><i class="bi bi-calendar-check"></i> Editar asistencia</a></li>
        </ul>
      </li>

      <li class="nav-dropdown">
        <button class="dropdown-toggle" aria-expanded="false" aria-controls="submenuGestionTemas2" aria-haspopup="true">
          <i class="bi bi-journal-bookmark-fill"></i> Temas y progresos <i class=""></i>
        </button>
        <ul class="submenu" id="submenuGestionTemas2" hidden>
          <?php if ($tipo === 'admin'): ?>
            <li><a href="crear_tema.php"><i class="bi bi-pencil-square"></i> Crear / Editar temas</a></li>
            <li><a href="asignar_alumno.php"><i class="bi bi-person-plus"></i> Asignar Alumnos</a></li>
          <?php endif; ?>
          <li><a href="progreso.php"><i class="bi bi-graph-up"></i> Ver progresos</a></li>
        </ul>
      </li>

         <?php if ($tipo === 'admin'): ?>
        <li><a href="editar_horarios.php"><i class="bi bi-calendar3"></i> Horarios y profesores</a></li>
      <?php endif; ?>

      <?php if ($tipo === 'admin'): ?>
        <li><a href="backup_manager.php"><i class="bi bi-hdd-fill"></i> Respaldo</a></li>
      <?php endif; ?>

      <?php if ($tipo === 'admin'): ?>
        <li><a href="panel_general.php" class="active"><i class="bi bi-bar-chart-line-fill"></i> Panel General</a></li>
      <?php endif; ?>

      <li><a href="logout.php" style="color: #e57373;"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
    </ul>
  </nav>

  <script>
    const toggleButton = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');

    toggleButton.addEventListener('click', () => {
      const isExpanded = toggleButton.getAttribute('aria-expanded') === 'true';
      toggleButton.setAttribute('aria-expanded', !isExpanded);
      menu.classList.toggle('show');
    });

    menu.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          menu.classList.remove('show');
          toggleButton.setAttribute('aria-expanded', 'false');
          // También cerrar submenu si abierto
          document.querySelectorAll('.submenu').forEach(submenu => {
            submenu.classList.remove('show');
            submenu.hidden = true;
          });
          document.querySelectorAll('.nav-dropdown > button.dropdown-toggle').forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
          });
        }
      });
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 768 && menu.classList.contains('show')) {
        menu.classList.remove('show');
        toggleButton.setAttribute('aria-expanded', 'false');
      }
      // En desktop asegurar que submenu esté oculto (no hidden ni show)
      if (window.innerWidth > 768) {
        document.querySelectorAll('.submenu').forEach(submenu => {
          submenu.classList.remove('show');
          submenu.hidden = true;
        });
        document.querySelectorAll('.nav-dropdown > button.dropdown-toggle').forEach(btn => {
          btn.setAttribute('aria-expanded', 'false');
        });
      }
    });

    // Abrir y cerrar submenu dropdown
    document.querySelectorAll('.nav-dropdown > button.dropdown-toggle').forEach(button => {
      button.addEventListener('click', e => {
        e.preventDefault();
        const submenu = button.nextElementSibling;
        const isExpanded = button.getAttribute('aria-expanded') === 'true';

        // Cerrar otros submenus abiertos (solo uno abierto a la vez)
        document.querySelectorAll('.submenu').forEach(other => {
          if (other !== submenu) {
            other.classList.remove('show');
            other.hidden = true;
          }
        });
        document.querySelectorAll('.nav-dropdown > button.dropdown-toggle').forEach(btn => {
          if (btn !== button) btn.setAttribute('aria-expanded', 'false');
        });

        if (isExpanded) {
          submenu.classList.remove('show');
          submenu.hidden = true;
          button.setAttribute('aria-expanded', 'false');
        } else {
          submenu.classList.add('show');
          submenu.hidden = false;
          button.setAttribute('aria-expanded', 'true');
        }
      });
    });

    // Cerrar submenu si haces clic fuera
    document.addEventListener('click', e => {
      const isClickInside = [...document.querySelectorAll('.nav-dropdown')].some(dropdown =>
        dropdown.contains(e.target)
      );
      if (!isClickInside) {
        document.querySelectorAll('.submenu').forEach(submenu => {
          submenu.classList.remove('show');
          submenu.hidden = true;
        });
        document.querySelectorAll('.nav-dropdown > button.dropdown-toggle').forEach(btn => {
          btn.setAttribute('aria-expanded', 'false');
        });
      }
    });
  </script>
</body>

</html>
