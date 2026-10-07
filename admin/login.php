<?php
session_start();

if (isset($_SESSION["usuario_id"])) {
    header("Location: panel.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso administrador | PreyNet</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            min-height: 100vh;
            background:
                linear-gradient(rgba(5, 18, 32, 0.78), rgba(5, 18, 32, 0.78)),
                url("../img/separador-final.jpg");
            background-size: cover;
            background-position: center;

            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .login-contenedor {
            width: 100%;
            max-width: 430px;
            padding: 42px;

            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
        }

        .login-contenedor h1 {
            color: #102542;
            font-size: 32px;
            margin-bottom: 12px;
            text-align: center;
        }

        .login-contenedor p {
            color: #5f6f80;
            font-size: 15px;
            line-height: 1.6;
            text-align: center;
            margin-bottom: 30px;
        }

        .grupo {
            margin-bottom: 20px;
        }

        .grupo label {
            display: block;
            margin-bottom: 8px;
            color: #102542;
            font-weight: 800;
            font-size: 14px;
        }

        .grupo input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd8e6;
            border-radius: 12px;
            font-size: 15px;
            outline: none;
        }

        .grupo input:focus {
            border-color: #f36b21;
            box-shadow: 0 0 0 4px rgba(243, 107, 33, 0.12);
        }

        .boton-login {
            width: 100%;
            padding: 15px 20px;
            border: none;
            border-radius: 40px;
            background-color: #f36b21;
            color: #ffffff;
            font-weight: 900;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .boton-login:hover {
            background-color: #102542;
            transform: translateY(-3px);
        }

        .volver {
            display: block;
            margin-top: 24px;
            text-align: center;
            color: #102542;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .volver:hover {
            color: #f36b21;
        }

        .mensaje-error {
            background: #ffe8e0;
            color: #a33a12;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="login-contenedor">
        <h1>Acceso administrador</h1>

        <p>
            Panel privado de gestión de PreyNet Sistemas.
        </p>

        <?php if (isset($_GET["error"])): ?>
            <div class="mensaje-error">
                Email o contraseña incorrectos.
            </div>
        <?php endif; ?>

        <form action="../acciones/procesar_login.php" method="POST">
            <div class="grupo">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" placeholder="admin@preynet.com" required>
            </div>

            <div class="grupo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Introduce la contraseña" required>
            </div>

            <button type="submit" class="boton-login">Entrar al panel</button>
        </form>

        <a href="../index.php" class="volver">Volver a la web</a>
    </div>

</body>
</html>