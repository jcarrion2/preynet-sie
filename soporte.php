<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| PEDIDOS DISPONIBLES
|--------------------------------------------------------------------------
|
| Mostramos los pedidos existentes para poder relacionar
| voluntariamente una incidencia con un pedido.
|
*/

$sql_pedidos = "SELECT
                    p.id,
                    p.codigo,
                    c.nombre,
                    c.apellidos,
                    c.email
                FROM pedidos p
                INNER JOIN clientes c
                    ON p.cliente_id = c.id
                ORDER BY p.id DESC";

$resultado_pedidos =
    $conexion->query($sql_pedidos);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Soporte | PreyNet</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f7fb;
            color: #102542;
        }

        a {
            text-decoration: none;
        }


        /* CABECERA */

        .cabecera {
            background-color: #071827;
        }

        .barra-superior {
            max-width: 1250px;
            margin: 0 auto;
            padding: 20px 40px;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            color: white;
            font-size: 30px;
            font-weight: 900;
        }

        .logo span {
            color: #f36b21;
        }

        .volver {
            padding: 12px 21px;

            background-color: #f36b21;
            color: white;

            border-radius: 30px;

            font-size: 14px;
            font-weight: 900;
        }


        /* CONTENIDO */

        .contenedor {
            max-width: 1050px;
            margin: 0 auto;
            padding: 70px 35px 100px;
        }

        .titulo {
            text-align: center;
            margin-bottom: 45px;
        }

        .titulo span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .titulo h1 {
            margin-top: 10px;

            font-size: 50px;
            letter-spacing: -2px;
        }

        .titulo p {
            max-width: 700px;

            margin: 16px auto 0;

            color: #68798c;

            font-size: 16px;
            line-height: 1.6;
        }


        /* FORMULARIO */

        .formulario {
            max-width: 800px;
            margin: 0 auto;

            padding: 40px;

            background-color: white;

            border: 1px solid #dbe4ee;
            border-radius: 22px;

            box-shadow:
                0 15px 35px rgba(16, 37, 66, 0.07);
        }

        .formulario h2 {
            margin-bottom: 28px;
            font-size: 27px;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .grupo {
            margin-bottom: 21px;
        }

        .grupo label {
            display: block;

            margin-bottom: 8px;

            font-size: 13px;
            font-weight: 900;
        }

        .grupo input,
        .grupo select,
        .grupo textarea {
            width: 100%;

            padding: 14px 15px;

            border: 1px solid #cbd8e6;
            border-radius: 11px;

            background-color: #f9fbfd;
            color: #102542;

            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;

            outline: none;
        }

        .grupo textarea {
            resize: vertical;
            min-height: 150px;
        }

        .grupo input:focus,
        .grupo select:focus,
        .grupo textarea:focus {
            border-color: #f36b21;

            background-color: white;

            box-shadow:
                0 0 0 4px rgba(243, 107, 33, 0.1);
        }

        .ayuda {
            margin-top: 7px;

            color: #8392a2;

            font-size: 12px;
            line-height: 1.5;
        }


        /* AVISO */

        .aviso {
            margin-bottom: 25px;

            padding: 16px 18px;

            background-color: #fff4ed;

            border-left: 4px solid #f36b21;
            border-radius: 9px;

            color: #74432a;

            font-size: 13px;
            line-height: 1.55;
        }


        /* BOTÓN */

        .boton-enviar {
            width: 100%;

            padding: 16px;

            border: none;
            border-radius: 35px;

            background-color: #f36b21;
            color: white;

            font-size: 15px;
            font-weight: 900;

            cursor: pointer;
        }

        .boton-enviar:hover {
            background-color: #102542;
        }


        /* FOOTER */

        .footer {
            padding: 35px;

            background-color: #071827;
            color: #aebbc8;

            text-align: center;

            font-size: 13px;
        }


        @media (max-width: 650px) {

            .barra-superior {
                padding: 18px 22px;
            }

            .contenedor {
                padding: 50px 22px;
            }

            .titulo h1 {
                font-size: 40px;
            }

            .fila {
                grid-template-columns: 1fr;
            }

            .formulario {
                padding: 27px;
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

        <a
            href="index.php"
            class="volver"
        >
            Volver a la tienda
        </a>

    </div>

</header>


<main class="contenedor">


    <section class="titulo">

        <span>
            Atención postventa
        </span>

        <h1>
            Soporte
        </h1>

        <p>
            Registra una consulta o incidencia relacionada
            con tu experiencia de compra en PreyNet.
        </p>

    </section>


    <form
        class="formulario"
        method="POST"
        action="acciones/procesar_incidencia.php"
    >


        <h2>
            Nueva incidencia
        </h2>


        <div class="aviso">

            Este formulario forma parte del prototipo académico.
            No se utilizarán los datos para comunicaciones reales.

        </div>


        <div class="fila">


            <div class="grupo">

                <label for="nombre">
                    Nombre *
                </label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    required
                >

            </div>


            <div class="grupo">

                <label for="email">
                    Correo electrónico *
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >

            </div>


        </div>


        <div class="grupo">

            <label for="pedido_id">
                Pedido relacionado
            </label>

            <select
                id="pedido_id"
                name="pedido_id"
            >

                <option value="">
                    No está relacionada con un pedido
                </option>


                <?php
                if ($resultado_pedidos):
                ?>

                    <?php
                    while (
                        $pedido =
                        $resultado_pedidos->fetch_assoc()
                    ):
                    ?>

                        <option
                            value="<?php echo intval($pedido["id"]); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $pedido["codigo"] .
                                " - " .
                                $pedido["nombre"] .
                                " " .
                                $pedido["apellidos"]
                            );
                            ?>

                        </option>

                    <?php endwhile; ?>

                <?php endif; ?>


            </select>

            <p class="ayuda">
                Si la consulta está relacionada con una compra,
                selecciona el pedido correspondiente.
            </p>

        </div>


        <div class="grupo">

            <label for="asunto">
                Asunto *
            </label>

            <input
                type="text"
                id="asunto"
                name="asunto"
                maxlength="180"
                required
                placeholder="Ej: Problema con mi pedido"
            >

        </div>


        <div class="grupo">

            <label for="mensaje">
                Mensaje *
            </label>

            <textarea
                id="mensaje"
                name="mensaje"
                required
                placeholder="Describe brevemente tu consulta o incidencia..."
            ></textarea>

        </div>


        <div class="grupo">

            <label for="prioridad">
                Prioridad
            </label>

            <select
                id="prioridad"
                name="prioridad"
            >

                <option value="baja">
                    Baja
                </option>

                <option
                    value="media"
                    selected
                >
                    Media
                </option>

                <option value="alta">
                    Alta
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="boton-enviar"
        >
            Enviar incidencia
        </button>


    </form>


</main>


<footer class="footer">

    PreyNet · Prototipo académico de comercio electrónico.

</footer>


</body>

</html>