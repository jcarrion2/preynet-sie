<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| COMPROBAR CÓDIGO
|--------------------------------------------------------------------------
*/

$codigo =
    trim($_GET["codigo"] ?? "");


if (
    $codigo === "" ||
    !isset($_SESSION["ultima_incidencia"]) ||
    $_SESSION["ultima_incidencia"] !== $codigo
) {

    header("Location: soporte.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| BUSCAR INCIDENCIA
|--------------------------------------------------------------------------
*/

$sql =
    "SELECT
        codigo,
        asunto,
        estado,
        prioridad,
        fecha_creacion
     FROM incidencias
     WHERE codigo = ?";


$stmt =
    $conexion->prepare($sql);


$stmt->bind_param(
    "s",
    $codigo
);


$stmt->execute();


$resultado =
    $stmt->get_result();


if ($resultado->num_rows !== 1) {

    header("Location: soporte.php");
    exit();

}


$incidencia =
    $resultado->fetch_assoc();


$stmt->close();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Incidencia registrada | PreyNet
    </title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background-color: #f4f7fb;
            color: #102542;
        }

        a {
            text-decoration: none;
        }


        .cabecera {
            background-color: #071827;
        }

        .barra-superior {
            max-width: 1200px;

            margin: 0 auto;

            padding: 20px 40px;
        }

        .logo {
            color: white;

            font-size: 30px;
            font-weight: 900;
        }

        .logo span {
            color: #f36b21;
        }


        .contenedor {
            max-width: 800px;

            margin: 0 auto;

            padding: 100px 30px;
        }


        .confirmacion {
            padding: 55px;

            background-color: white;

            border:
                1px solid #dbe4ee;

            border-radius: 24px;

            box-shadow:
                0 18px 45px
                rgba(16, 37, 66, 0.08);

            text-align: center;
        }


        .check {
            width: 80px;
            height: 80px;

            margin:
                0 auto
                25px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background-color: #e8f8ee;
            color: #159447;

            font-size: 42px;
            font-weight: 900;
        }


        .etiqueta {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }


        h1 {
            margin-top: 10px;

            font-size: 42px;
        }


        .texto {
            margin-top: 18px;

            color: #66788a;

            font-size: 16px;
            line-height: 1.6;
        }


        .codigo {
            margin: 30px 0;

            padding: 22px;

            background-color: #071827;
            color: white;

            border-radius: 15px;
        }


        .codigo span {
            display: block;

            margin-bottom: 8px;

            color: #aebbc8;

            font-size: 12px;
        }


        .codigo strong {
            font-size: 22px;
        }


        .datos {
            margin-bottom: 30px;

            padding: 20px;

            background-color: #f7f9fc;

            border-radius: 14px;

            text-align: left;
        }


        .datos p {
            margin-bottom: 9px;

            color: #5d6e80;

            font-size: 14px;
        }


        .botones {
            display: flex;
            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;
        }


        .boton-principal {
            padding: 14px 25px;

            background-color: #f36b21;
            color: white;

            border-radius: 30px;

            font-weight: 900;
        }


        .boton-secundario {
            padding: 14px 25px;

            background-color: #e5ebf1;
            color: #102542;

            border-radius: 30px;

            font-weight: 900;
        }


        @media (max-width: 600px) {

            .confirmacion {
                padding: 35px 22px;
            }

            h1 {
                font-size: 34px;
            }

        }

    </style>

</head>


<body>


<header class="cabecera">

    <div class="barra-superior">

        <a
            href="index.php"
            class="logo"
        >
            PREY<span>NET</span>
        </a>

    </div>

</header>


<main class="contenedor">


    <section class="confirmacion">


        <div class="check">
            ✓
        </div>


        <div class="etiqueta">
            Solicitud recibida
        </div>


        <h1>
            Incidencia registrada
        </h1>


        <p class="texto">
            La solicitud se ha registrado correctamente
            en el sistema de atención postventa de PreyNet.
        </p>


        <div class="codigo">

            <span>
                Código de incidencia
            </span>

            <strong>

                <?php
                echo htmlspecialchars(
                    $incidencia["codigo"]
                );
                ?>

            </strong>

        </div>


        <div class="datos">

            <p>

                <strong>
                    Asunto:
                </strong>

                <?php
                echo htmlspecialchars(
                    $incidencia["asunto"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Estado:
                </strong>

                Abierta

            </p>


            <p>

                <strong>
                    Prioridad:
                </strong>

                <?php
                echo ucfirst(
                    htmlspecialchars(
                        $incidencia["prioridad"]
                    )
                );
                ?>

            </p>

        </div>


        <div class="botones">


            <a
                href="index.php"
                class="boton-principal"
            >
                Volver a la tienda
            </a>


            <a
                href="soporte.php"
                class="boton-secundario"
            >
                Nueva incidencia
            </a>


        </div>


    </section>


</main>


</body>

</html>