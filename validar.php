<?php
session_start();

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

if ($usuario === 'Administrador' && $password === '1703') {
    $_SESSION['tipo_usuario'] = 'admin';
    header('Location: lista_alumnos.php');
    exit;
} elseif ($usuario === 'Docente' && $password === 'momj') {
    $_SESSION['tipo_usuario'] = 'docente';
    header('Location: lista_alumnos.php');
    exit;
} else {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Error de inicio de sesión</title>
        <style>
            body {
                background: #f8d7da;
                color: #842029;
                font-family: Arial, sans-serif;
                height: 100vh;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .error-box {
                background: #f5c2c7;
                padding: 20px 30px;
                border: 1px solid #f5aeb1;
                border-radius: 8px;
                text-align: center;
                box-shadow: 0 0 10px rgba(255, 0, 0, 0.2);
                max-width: 320px;
                width: 100%;
                position: relative;
                overflow: visible; /* permite que la imagen se salga */
            }
            .error-box a {
                display: inline-block;
                margin-top: 15px;
                text-decoration: none;
                color: #842029;
                font-weight: bold;
                border: 2px solid #842029;
                padding: 8px 15px;
                border-radius: 5px;
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            .error-box a:hover {
                background-color: #842029;
                color: white;
            }
            .error-box img {
                width: 140%;
                height: auto;
                margin-top: 15px;
                margin-left: -20%;
                border-radius: 6px;
                display: block;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <p><strong>Contraseña incorrecta.</strong></p>
            <a href="login.php">Intentar de nuevo</a>
            <img src="qiqi.png" alt="">
        </div>
    </body>
    </html>
    <?php
}
?>
