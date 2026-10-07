<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| COMPROBAR PEDIDO
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$pedido_id = intval($_GET["id"]);


/*
|--------------------------------------------------------------------------
| SEGURIDAD BÁSICA
|--------------------------------------------------------------------------
|
| Solo permitimos visualizar el pedido que se acaba de crear
| desde esta misma sesión.
|
*/

if (
    !isset($_SESSION["pedido_actual"]) ||
    intval($_SESSION["pedido_actual"]) !== $pedido_id
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
                    c.email
               FROM pedidos p
               INNER JOIN clientes c
                    ON p.cliente_id = c.id
               WHERE p.id = ?";

$stmt_pedido = $conexion->prepare($sql_pedido);

$stmt_pedido->bind_param(
    "i",
    $pedido_id
);

$stmt_pedido->execute();

$resultado_pedido = $stmt_pedido->get_result();


if ($resultado_pedido->num_rows !== 1) {

    header("Location: index.php");
    exit();

}

$pedido = $resultado_pedido->fetch_assoc();

$stmt_pedido->close();


/*
|--------------------------------------------------------------------------
| SOLO PEDIDOS PENDIENTES DE PAGO
|--------------------------------------------------------------------------
*/

if ($pedido["estado"] !== "pendiente_pago") {

    header(
        "Location: pedido_confirmado.php?id=" .
        $pedido_id
    );

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

$stmt_lineas = $conexion->prepare($sql_lineas);

$stmt_lineas->bind_param(
    "i",
    $pedido_id
);

$stmt_lineas->execute();

$resultado_lineas = $stmt_lineas->get_result();

$lineas = [];

while ($linea = $resultado_lineas->fetch_assoc()) {
    $lineas[] = $linea;
}

$stmt_lineas->close();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pago simulado | PreyNet</title>


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
        }

        .logo {
            color: white;

            font-size: 30px;
            font-weight: 900;
        }

        .logo span {
            color: #f36b21;
        }

        .pedido-codigo {
            color: #c8d3de;

            font-size: 13px;
            font-weight: 700;
        }


        /* =========================================================
           CONTENIDO
        ========================================================= */

        .contenedor {
            max-width: 1150px;
            margin: 0 auto;
            padding: 70px 35px 100px;
        }

        .titulo span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .titulo h1 {
            margin-top: 10px;

            font-size: 52px;
            letter-spacing: -2px;
        }

        .titulo p {
            margin-top: 15px;

            max-width: 700px;

            color: #68798c;

            font-size: 16px;
            line-height: 1.6;
        }


        /* =========================================================
           LAYOUT
        ========================================================= */

        .pago-layout {
            margin-top: 45px;

            display: grid;

            grid-template-columns:
                minmax(0, 1.4fr)
                minmax(320px, 1fr);

            gap: 35px;

            align-items: start;
        }


        /* =========================================================
           PAGO
        ========================================================= */

        .pago {
            padding: 38px;

            background-color: white;

            border: 1px solid #dce5ee;
            border-radius: 22px;

            box-shadow:
                0 15px 35px rgba(16, 37, 66, 0.06);
        }

        .pago h2 {
            font-size: 27px;

            margin-bottom: 25px;
        }

        .aviso {
            padding: 18px 20px;

            margin-bottom: 30px;

            background-color: #fff4ed;

            border-left: 4px solid #f36b21;
            border-radius: 9px;

            color: #75442b;

            font-size: 14px;
            line-height: 1.6;
        }


        /* =========================================================
           MÉTODOS
        ========================================================= */

        .metodos {
            display: flex;
            flex-direction: column;

            gap: 15px;
        }

        .metodo {
            position: relative;
        }

        .metodo input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .metodo label {
            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 20px;

            border: 2px solid #dce5ee;
            border-radius: 15px;

            cursor: pointer;

            transition: all 0.2s ease;
        }

        .metodo label:hover {
            border-color: #f36b21;
        }

        .metodo input:checked + label {
            border-color: #f36b21;

            background-color: #fff7f2;
        }

        .metodo-info strong {
            display: block;

            font-size: 16px;
        }

        .metodo-info span {
            display: block;

            margin-top: 5px;

            color: #758698;

            font-size: 13px;
        }

        .metodo-icono {
            font-size: 25px;
        }


        /* =========================================================
           BOTÓN
        ========================================================= */

        .boton-pagar {
            width: 100%;

            margin-top: 30px;

            padding: 17px;

            border: none;
            border-radius: 35px;

            background-color: #f36b21;
            color: white;

            font-size: 15px;
            font-weight: 900;

            cursor: pointer;

            transition: all 0.2s ease;
        }

        .boton-pagar:hover {
            background-color: #102542;
        }

        .texto-boton {
            margin-top: 13px;

            color: #8190a0;

            font-size: 12px;
            line-height: 1.5;

            text-align: center;
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
            margin-bottom: 8px;

            font-size: 25px;
        }

        .cliente {
            margin-bottom: 25px;

            color: #9eacba;

            font-size: 13px;
            line-height: 1.5;
        }

        .linea {
            padding: 14px 0;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.1);
        }

        .linea-superior {
            display: flex;
            justify-content: space-between;

            gap: 20px;
        }

        .linea strong {
            font-size: 13px;
        }

        .linea span {
            display: block;

            margin-top: 5px;

            color: #aebbc8;

            font-size: 12px;
        }


        /* =========================================================
           TOTALES
        ========================================================= */

        .totales {
            margin-top: 25px;
        }

        .fila-total {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 13px;

            color: #c6d0da;

            font-size: 14px;
        }

        .fila-total strong {
            color: white;
        }

        .total-final {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-top: 22px;
            padding-top: 22px;

            border-top:
                1px solid rgba(255, 255, 255, 0.15);

            font-size: 26px;
            font-weight: 900;
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

            .pago-layout {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .barra-superior {
                padding: 18px 22px;
            }

            .contenedor {
                padding: 50px 22px;
            }

            .titulo h1 {
                font-size: 40px;
            }

            .pago,
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

        <div class="pedido-codigo">

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


    <div class="titulo">

        <span>
            Último paso
        </span>

        <h1>
            Pago simulado
        </h1>

        <p>
            Selecciona un método de pago de demostración.
            No se solicitarán números de tarjeta,
            credenciales bancarias ni ningún dato financiero real.
        </p>

    </div>


    <div class="pago-layout">


        <!-- =====================================================
             PAGO
        ====================================================== -->

        <section class="pago">


            <h2>
                Método de pago
            </h2>


            <div class="aviso">

                <strong>
                    Entorno académico.
                </strong>

                Esta operación es completamente simulada.
                El botón inferior únicamente registrará
                un pago ficticio asociado al pedido.

            </div>


            <form
                action="acciones/procesar_pago.php"
                method="POST"
            >


                <input
                    type="hidden"
                    name="pedido_id"
                    value="<?php echo intval($pedido["id"]); ?>"
                >


                <div class="metodos">


                    <div class="metodo">

                        <input
                            type="radio"
                            id="tarjeta"
                            name="metodo"
                            value="tarjeta_simulada"
                            checked
                        >

                        <label for="tarjeta">

                            <div class="metodo-info">

                                <strong>
                                    Tarjeta simulada
                                </strong>

                                <span>
                                    No requiere introducir
                                    ningún dato bancario.
                                </span>

                            </div>

                            <div class="metodo-icono">
                                💳
                            </div>

                        </label>

                    </div>


                    <div class="metodo">

                        <input
                            type="radio"
                            id="paypal"
                            name="metodo"
                            value="paypal_simulado"
                        >

                        <label for="paypal">

                            <div class="metodo-info">

                                <strong>
                                    PayPal simulado
                                </strong>

                                <span>
                                    No conecta con servicios externos.
                                </span>

                            </div>

                            <div class="metodo-icono">
                                P
                            </div>

                        </label>

                    </div>


                    <div class="metodo">

                        <input
                            type="radio"
                            id="transferencia"
                            name="metodo"
                            value="transferencia_simulada"
                        >

                        <label for="transferencia">

                            <div class="metodo-info">

                                <strong>
                                    Transferencia simulada
                                </strong>

                                <span>
                                    No se muestran cuentas
                                    ni datos bancarios.
                                </span>

                            </div>

                            <div class="metodo-icono">
                                ↔
                            </div>

                        </label>

                    </div>


                </div>


                <button
                    type="submit"
                    class="boton-pagar"
                >
                    Simular pago de

                    <?php
                    echo number_format(
                        $pedido["total"],
                        2,
                        ",",
                        "."
                    );
                    ?>

                    €
                </button>


                <p class="texto-boton">
                    Al continuar se marcará el pedido
                    como pagado únicamente dentro
                    del prototipo académico.
                </p>


            </form>


        </section>


        <!-- =====================================================
             RESUMEN
        ====================================================== -->

        <aside class="resumen">


            <h2>
                Resumen
            </h2>


            <div class="cliente">

                <?php
                echo htmlspecialchars(
                    $pedido["nombre"] .
                    " " .
                    $pedido["apellidos"]
                );
                ?>

                <br>

                <?php
                echo htmlspecialchars(
                    $pedido["email"]
                );
                ?>

            </div>


            <?php foreach ($lineas as $linea): ?>


                <div class="linea">


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


                    <span>

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

                    </span>


                </div>


            <?php endforeach; ?>


            <div class="totales">


                <div class="fila-total">

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


                <div class="fila-total">

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


                <div class="fila-total">

                    <span>
                        Envío
                    </span>

                    <strong>

                        <?php
                        if (floatval($pedido["envio"]) == 0) {

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


                <div class="total-final">

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


            </div>


        </aside>


    </div>


</main>


<footer class="footer">

    Pago totalmente simulado.
    No se procesan datos bancarios reales.

</footer>


</body>

</html>