
<!DOCTYPE html>
<html lang="es">

<head>
    <style>
        h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: #2e7d32;
            /* verde oscuro */
            text-align: center;
            margin-bottom: 1.5rem;
        }

        form {
            max-width: 360px;
            margin: 0 auto;
            background: #e8f5e9;
            /* verde muy claro */
            padding: 2rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            font-family: 'Poppins', sans-serif;
        }

        select[name="tema_id"] {
            padding: 0.6rem 0.8rem;
            border: 2px solid #a5d6a7;
            /* verde claro */
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            color: #2e7d32;
            background-color: #fff;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        select[name="tema_id"]:focus {
            outline: none;
            border-color: #2e7d32;
            /* verde oscuro */
            box-shadow: 0 0 6px rgba(46, 125, 50, 0.5);
        }

        button[type="submit"] {
            padding: 0.65rem 1rem;
            background-color: #2e7d32;
            /* verde oscuro */
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        button[type="submit"]:hover,
        button[type="submit"]:focus {
            background-color: #276129;
            /* verde más oscuro */
            box-shadow: 0 0 10px rgba(39, 97, 41, 0.7);
            outline: none;
        }
    </style>
</head>

<body>
    <?php include 'navbar3.php'; ?>
    <h1>Seleccione un tema</h1>
    <form method="POST">
        <input type="hidden" name="step" value="mostrar_horarios">
        <select name="tema_id" required>
            <option value="">-- Seleccionar tema --</option>
            <?php foreach ($temas as $tema): ?>
                <option value="<?= escape($tema['id']) ?>"><?= escape($tema['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Editar horarios</button>
    </form>
</body>

</html>