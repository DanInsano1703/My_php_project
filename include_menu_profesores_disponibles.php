
  <style>
  #profesores-list {
    background: #fafafaff; /* tono un poco más oscuro del azul original para fondo */
    padding: 1.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 38, 99, 0.5);
    max-width: 320px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  #profesores-list h3 {
    color: #002663ff;
    font-weight: 700;
    font-size: 1.6rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid #002663ff;
    padding-bottom: 0.3rem;
    letter-spacing: 0.03em;
  }

  .profesor-item {
    background: #3092d3ff;
    color: white;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    margin-bottom: 0.8rem;
    cursor: grab;
    box-shadow: 0 3px 6px rgba(0, 38, 99, 0.4);
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    user-select: none;
  }

  .profesor-item:active {
    cursor: grabbing;
    box-shadow: 0 6px 12px rgba(0, 38, 99, 0.7);
    background-color: #0042a8ff;
  }

  .profesor-item:hover {
    background-color: #0042a8ff;
    box-shadow: 0 5px 10px rgba(0, 38, 99, 0.7);
  }

  /* Separador elegante */
  hr {
    border: none;
    height: 1px;
    background: linear-gradient(to right, transparent, #002663ff, transparent);
    margin: 1rem 0 1.5rem;
  }

  /* Botón estilizado */
  #profesores-list li {
    list-style: none;
  }

  #profesores-list a {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.4rem;
    background-color: #002663ff;
    color: #f0f0f0;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 0 4px 8px rgba(0, 38, 99, 0.5);
    transition: background-color 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease;
    border: none;
    cursor: pointer;
    user-select: none;
  }

  #profesores-list a:hover,
  #profesores-list a:focus {
    background-color: #161616ff;
    box-shadow: 0 8px 16px rgba(39, 97, 41, 0.8);
    transform: translateY(-2px);
    outline: none;
  }

  #profesores-list a i {
    font-size: 1.3rem;
  }
</style>
<br>
<br>
<br>
<br>
<br>
<br>
<div id="profesores-list">
  <h3>Mis profesores</h3>
  <h4>(Arrastra para asignar)</h4>
  <?php foreach ($profesores as $prof): ?>
    <div class="profesor-item" draggable="true" data-id="<?= $prof['id'] ?>">
      <?= escape($prof['nombre']) ?>
    </div>
  <?php endforeach; ?>

  <hr>

  <li>
    <a href="crear_profesores.php" 
       aria-label="Crear nuevo profesor">
      <i class="bi bi-person-badge-fill"></i> Nuevo profesor
    </a>
  </li>
</div>
