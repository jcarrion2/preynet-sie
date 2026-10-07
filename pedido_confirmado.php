<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| COMPROBAR ID DEL PEDIDO
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$pedido_id = intval($_GET["id"]);


/*
|--------------------------------------------------------------------------
| COMPROBAR QUE EL PEDIDO PERTENECE A ESTA SESIÓN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["pedido_confirmado"]) ||
    intval($_SESSION["pedido_confirmado"]) !== $pedido_id
) {
    header("Location: index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| OBTENER PEDIDO + CLIENTE
|--------------------------------------------------------------------------
*/

$sql_pedido = "SELECT
                    p.id,
                    p.codigo,
                    p.subtotal,
                    p.descuento,
                    p.envio,
                    p.iva,
                    p.total,
                    p.estado,
                    p.fecha_creacion,

                    c.nombre,
                    c.apellidos,
                    c.email,
                    c.telefono,
                    c.direccion,
                    c.codigo_postal,
                    c.ciudad,
                    c.provincia

               FROM pedidos p

               INNER JOIN clientes c
                    ON p.cliente_id = c.id

               WHERE p.id = ?";


$stmt_pedido =
    $conexion->prepare($sql_pedido);


$stmt_pedido->bind_param(
    "i",
    $pedido_id
);


$stmt_pedido->execute();


$resultado_pedido =
    $stmt_pedido->get_result();


if ($resultado_pedido->num_rows !== 1) {

    header("Location: index.php");
    exit();

}


$pedido =
    $resultado_pedido->fetch_assoc();


$stmt_pedido->close();


/*
|--------------------------------------------------------------------------
| COMPROBAR QUE ESTÁ PAGADO
|--------------------------------------------------------------------------
*/

if ($pedido["estado"] !== "pagado") {

    header("Location: index.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| OBTENER LÍNEAS DEL PEDIDO
|--------------------------------------------------------------------------
*/

$sql_lineas = "SELECT
                    nombre_producto,
                    cantidad,
                    precio_unitario,
                    total_linea

               FROM lineas_pedido

               WHERE pedido_id = ?

               ORDER BY id ASC";


$stmt_lineas =
    $conexion->prepare($sql_lineas);


$stmt_lineas->bind_param(
    "i",
    $pedido_id
);


$stmt_lineas->execute();


$resultado_lineas =
    $stmt_lineas->get_result();


$lineas = [];


while (
    $linea =
    $resultado_lineas->fetch_assoc()
) {

    $lineas[] = $linea;

}


$stmt_lineas->close();


/*
|--------------------------------------------------------------------------
| OBTENER PAGO SIMULADO
|--------------------------------------------------------------------------
*/

$sql_pago = "SELECT
                  metodo,
                  importe,
                  estado,
                  referencia,
                  fecha_creacion

             FROM pagos

             WHERE pedido_id = ?

             ORDER BY id DESC

             LIMIT 1";


$stmt_pago =
    $conexion->prepare($sql_pago);


$stmt_pago->bind_param(
    "i",
    $pedido_id
);


$stmt_pago->execute();


$resultado_pago =
    $stmt_pago->get_result();


$pago = null;


if ($resultado_pago->num_rows === 1) {

    $pago =
        $resultado_pago->fetch_assoc();

}


$stmt_pago->close();


/*
|--------------------------------------------------------------------------
| NOMBRE AMIGABLE DEL MÉTODO
|--------------------------------------------------------------------------
*/

$nombre_metodo =
    "Pago simulado";


if ($pago !== null) {

    switch ($pago["metodo"]) {

        case "tarjeta_simulada":

            $nombre_metodo =
                "Tarjeta simulada";

            break;


        case "paypal_simulado":

            $nombre_metodo =
                "PayPal simulado";

            break;


        case "transferencia_simulada":

            $nombre_metodo =
                "Transferencia simulada";

            break;

    }

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pedido confirmado | PreyNet</title>


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


        /* =========================================================
           CABECERA
        ========================================================= */

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

            gap: 30px;
        }

        .logo {
            color: white;

            font-size: 30px;
            font-weight: 900;
        }

        .logo span {
            color: #f36b21;
        }

        .codigo-cabecera {
            color: #c8d3de;

            font-size: 13px;
            font-weight: 700;
        }


        /* =========================================================
           CONTENIDO
        ========================================================= */

        .contenedor {
            max-width: 1100px;

            margin: 0 auto;

            padding: 70px 35px 100px;
        }


        /* =========================================================
           CONFIRMACIÓN
        ========================================================= */

        .confirmacion {
            text-align: center;

            margin-bottom: 50px;
        }

        .check {
            width: 82px;
            height: 82px;

            margin: 0 auto 25px;

            display: flex;
            align-items: center;
            justify-content: center;

            background-color: #e9f8ef;
            color: #159447;

            border-radius: 50%;

            font-size: 42px;
            font-weight: 900;
        }

        .confirmacion span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .confirmacion h1 {
            margin-top: 10px;

            font-size: 52px;
            letter-spacing: -2px;
        }

        .confirmacion p {
            max-width: 680px;

            margin: 18px auto 0;

            color: #68798c;

            font-size: 17px;
            line-height: 1.65;
        }


        /* =========================================================
           PEDIDO
        ========================================================= */

        .pedido {
            display: grid;

            grid-template-columns:
                minmax(0, 1.5fr)
                minmax(300px, 1fr);

            gap: 30px;
        }


        /* =========================================================
           BLOQUES
        ========================================================= */

        .bloque {
            padding: 32px;

            background-color: white;

            border: 1px solid #dce5ee;
            border-radius: 22px;

            box-shadow:
                0 15px 35px rgba(16, 37, 66, 0.06);
        }

        .bloque h2 {
            margin-bottom: 25px;

            font-size: 25px;
        }


        /* =========================================================
           PRODUCTOS
        ========================================================= */

        .linea-producto {
            padding: 17px 0;

            border-bottom:
                1px solid #e4eaf0;
        }

        .linea-producto:first-of-type {
            padding-top: 0;
        }

        .linea-producto:last-of-type {
            border-bottom: none;
        }

        .linea-superior {
            display: flex;
            justify-content: space-between;

            gap: 20px;
        }

        .linea-superior strong {
            font-size: 15px;
        }

        .detalle-linea {
            margin-top: 6px;

            color: #78899a;

            font-size: 13px;
        }


        /* =========================================================
           DATOS DEL CLIENTE
        ========================================================= */

        .datos-cliente {
            margin-top: 30px;

            padding-top: 27px;

            border-top: 1px solid #e4eaf0;
        }

        .datos-cliente h3 {
            margin-bottom: 13px;

            font-size: 17px;
        }

        .datos-cliente p {
            margin-bottom: 6px;

            color: #66798c;

            font-size: 14px;
            line-height: 1.5;
        }


        /* =========================================================
           RESUMEN
        ========================================================= */

        .resumen {
            padding: 32px;

            background-color: #071827;
            color: white;

            border-radius: 22px;
        }

        .resumen h2 {
            margin-bottom: 25px;

            font-size: 25px;
        }

        .fila {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 15px;

            color: #c5d0db;

            font-size: 14px;
        }

        .fila strong {
            color: white;
        }

        .total {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-top: 24px;
            padding-top: 22px;

            border-top:
                1px solid rgba(255, 255, 255, 0.16);

            font-size: 26px;
            font-weight: 900;
        }


        /* =========================================================
           PAGO
        ========================================================= */

        .pago-info {
            margin-top: 28px;

            padding-top: 24px;

            border-top:
                1px solid rgba(255, 255, 255, 0.16);
        }

        .pago-info h3 {
            margin-bottom: 12px;

            font-size: 16px;
        }

        .pago-info p {
            margin-bottom: 7px;

            color: #aebbc8;

            font-size: 12px;
            line-height: 1.5;
        }

        .referencia {
            color: white !important;

            font-weight: 800;

            word-break: break-word;
        }


        /* =========================================================
           BOTONES
        ========================================================= */

        .acciones {
            margin-top: 40px;

            display: flex;
            justify-content: center;

            gap: 15px;

            flex-wrap: wrap;
        }

        .boton-principal {
            padding: 15px 28px;

            background-color: #f36b21;
            color: white;

            border-radius: 35px;

            font-size: 14px;
            font-weight: 900;
        }

        .boton-principal:hover {
            background-color: #102542;
        }

        .boton-secundario {
            padding: 15px 28px;

            background-color: #e7edf3;
            color: #102542;

            border-radius: 35px;

            font-size: 14px;
            font-weight: 900;
        }


        /* =========================================================
           AVISO
        ========================================================= */

        .aviso {
            max-width: 800px;

            margin: 35px auto 0;

            padding: 16px 20px;

            background-color: #fff4ed;

            border-left: 4px solid #f36b21;
            border-radius: 9px;

            color: #75442b;

            font-size: 13px;
            line-height: 1.55;
        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .footer {
            padding: 35px;

            background-color: #071827;
            color: #aebbc8;

            text-align: center;

            font-size: 13px;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 850px) {

            .pedido {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 600px) {

            .barra-superior {
                padding: 18px 22px;

                flex-direction: column;

                align-items: flex-start;
            }

            .contenedor {
                padding: 50px 22px;
            }

            .confirmacion h1 {
                font-size: 40px;
            }

            .bloque,
            .resumen {
                padding: 25px;
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


        <div class="codigo-cabecera">

            Pedido:

            <?php
            echo htmlspecialchars(
                $pedido["codigo"]
            );
            ?>

        </div>


    </div>

</header>


<main class="contenedor">


    <!-- =========================================================
         CONFIRMACIÓN
    ========================================================== -->

    <section class="confirmacion">


        <div class="check">
            ✓
        </div>


        <span>
            Operación completada
        </span>


        <h1>
            Pedido confirmado
        </h1>


        <p>

            Gracias,

            <?php
            echo htmlspecialchars(
                $pedido["nombre"]
            );
            ?>.

            El pedido se ha registrado correctamente
            y el pago ha sido simulado con éxito.

        </p>


    </section>


    <!-- =========================================================
         INFORMACIÓN DEL PEDIDO
    ========================================================== -->

    <div class="pedido">


        <!-- PRODUCTOS Y CLIENTE -->

        <section class="bloque">


            <h2>
                Detalle del pedido
            </h2>


            <?php foreach ($lineas as $linea): ?>


                <div class="linea-producto">


                    <div class="linea-superior">


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $linea["nombre_producto"]
                            );
                            ?>

                        </strong>


                        <strong>

                            <?php
                            echo number_format(
                                $linea["total_linea"],
                                2,
                                ",",
                                "."
                            );
                            ?>

                            €

                        </strong>


                    </div>


                    <div class="detalle-linea">

                        <?php
                        echo intval(
                            $linea["cantidad"]
                        );
                        ?>

                        ×

                        <?php
                        echo number_format(
                            $linea["precio_unitario"],
                            2,
                            ",",
                            "."
                        );
                        ?>

                        €

                    </div>


                </div>


            <?php endforeach; ?>


            <!-- CLIENTE -->

            <div class="datos-cliente">


                <h3>
                    Datos del cliente
                </h3>


                <p>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $pedido["nombre"] .
                            " " .
                            $pedido["apellidos"]
                        );
                        ?>
                    </strong>

                </p>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $pedido["email"]
                    );
                    ?>

                </p>


                <?php if (!empty($pedido["telefono"])): ?>

                    <p>

                        <?php
                        echo htmlspecialchars(
                            $pedido["telefono"]
                        );
                        ?>

                    </p>

                <?php endif; ?>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $pedido["direccion"]
                    );
                    ?>

                </p>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $pedido["codigo_postal"] .
                        " " .
                        $pedido["ciudad"] .
                        ", " .
                        $pedido["provincia"]
                    );
                    ?>

                </p>


            </div>


        </section>


        <!-- RESUMEN -->

        <aside class="resumen">


            <h2>
                Resumen
            </h2>


            <div class="fila">

                <span>
                    Subtotal
                </span>

                <strong>

                    <?php
                    echo number_format(
                        $pedido["subtotal"],
                        2,
                        ",",
                        "."
                    );
                    ?>

                    €

                </strong>

            </div>


            <div class="fila">

                <span>
                    IVA
                </span>

                <strong>

                    <?php
                    echo number_format(
                        $pedido["iva"],
                        2,
                        ",",
                        "."
                    );
                    ?>

                    €

                </strong>

            </div>


            <div class="fila">

                <span>
                    Envío
                </span>

                <strong>

                    <?php
                    if (
                        floatval($pedido["envio"]) == 0
                    ) {

                        echo "Gratis";

                    } else {

                        echo number_format(
                            $pedido["envio"],
                            2,
                            ",",
                            "."
                        ) . " €";

                    }
                    ?>

                </strong>

            </div>


            <div class="fila">

                <span>
                    Descuento
                </span>

                <strong>

                    <?php
                    echo number_format(
                        $pedido["descuento"],
                        2,
                        ",",
                        "."
                    );
                    ?>

                    €

                </strong>

            </div>


            <div class="total">

                <span>
                    Total
                </span>

                <span>

                    <?php
                    echo number_format(
                        $pedido["total"],
                        2,
                        ",",
                        "."
                    );
                    ?>

                    €

                </span>

            </div>


            <!-- PAGO -->

            <?php if ($pago !== null): ?>


                <div class="pago-info">


                    <h3>
                        Pago simulado
                    </h3>


                    <p>
                        Método:
                        <?php
                        echo htmlspecialchars(
                            $nombre_metodo
                        );
                        ?>
                    </p>


                    <p>
                        Estado:
                        Aprobado
                    </p>


                    <p>
                        Referencia:
                    </p>


                    <p class="referencia">

                        <?php
                        echo htmlspecialchars(
                            $pago["referencia"]
                        );
                        ?>

                    </p>


                </div>


            <?php endif; ?>


        </aside>


    </div>


    <!-- =========================================================
         AVISO
    ========================================================== -->

    <div class="aviso">

        <strong>
            Importante:
        </strong>

        este pedido y su pago forman parte de un prototipo
        académico. No se ha realizado ningún cobro real.

    </div>


    <!-- =========================================================
         ACCIONES
    ========================================================== -->

    <div class="acciones">


        <a
            href="index.php#tienda"
            class="boton-principal"
        >
            Volver a la tienda
        </a>


        <a
            href="index.php#soporte"
            class="boton-secundario"
        >
            Soporte
        </a>


    </div>


</main>


<footer class="footer">

    PreyNet · Prototipo académico de comercio electrónico.

</footer>


</body>

</html>