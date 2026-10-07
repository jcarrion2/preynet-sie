<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| COMPROBAR QUE HAYA PRODUCTOS EN EL CARRITO
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["carrito"]) ||
    !is_array($_SESSION["carrito"]) ||
    count($_SESSION["carrito"]) === 0
) {

    header("Location: carrito.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| OBTENER PRODUCTOS Y CALCULAR IMPORTES
|--------------------------------------------------------------------------
*/

$productos_checkout = [];

$subtotal = 0;
$unidades = 0;


foreach ($_SESSION["carrito"] as $producto_id => $cantidad) {

    $producto_id = intval($producto_id);
    $cantidad = intval($cantidad);

    if ($cantidad <= 0) {
        continue;
    }


    $sql = "SELECT id,
                   sku,
                   nombre,
                   precio,
                   stock
            FROM productos
            WHERE id = ?
            AND activo = 1";


    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "i",
        $producto_id
    );

    $stmt->execute();

    $resultado = $stmt->get_result();


    if ($resultado->num_rows === 1) {

        $producto =
            $resultado->fetch_assoc();


        /*
         * Ajustar al stock disponible.
         */

        if ($cantidad > $producto["stock"]) {

            $cantidad =
                intval($producto["stock"]);

            $_SESSION["carrito"][$producto_id] =
                $cantidad;

        }


        if ($cantidad <= 0) {
            continue;
        }


        $total_linea =
            floatval($producto["precio"])
            * $cantidad;


        $producto["cantidad"] =
            $cantidad;

        $producto["total_linea"] =
            $total_linea;


        $productos_checkout[] =
            $producto;


        $subtotal +=
            $total_linea;


        $unidades +=
            $cantidad;

    }

    $stmt->close();

}


/*
|--------------------------------------------------------------------------
| SI EL CARRITO SE HA QUEDADO VACÍO
|--------------------------------------------------------------------------
*/

if (count($productos_checkout) === 0) {

    header("Location: carrito.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| REGLAS DE NEGOCIO
|--------------------------------------------------------------------------
|
| - IVA: 21 %
| - Envío: 5,90 €
| - Envío gratuito a partir de 200 € de subtotal
|
*/

$porcentaje_iva = 21;

$iva =
    $subtotal
    * ($porcentaje_iva / 100);


if ($subtotal >= 200) {

    $envio = 0;

} else {

    $envio = 5.90;

}


$descuento = 0;


$total =
    $subtotal
    + $iva
    + $envio
    - $descuento;


/*
|--------------------------------------------------------------------------
| EVENTO: checkout.started
|--------------------------------------------------------------------------
*/

$tipo_evento =
    "checkout.started";

$session_id =
    session_id();


$metadata =
    json_encode(
        [
            "unidades" => $unidades,
            "subtotal" => round($subtotal, 2),
            "iva" => round($iva, 2),
            "envio" => round($envio, 2),
            "total" => round($total, 2)
        ],
        JSON_UNESCAPED_UNICODE
    );


$sql_evento =
    "INSERT INTO eventos
    (
        tipo,
        session_id,
        metadata
    )
    VALUES (?, ?, ?)";


$stmt_evento =
    $conexion->prepare($sql_evento);


$stmt_evento->bind_param(
    "sss",
    $tipo_evento,
    $session_id,
    $metadata
);


$stmt_evento->execute();

$stmt_evento->close();

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Checkout | PreyNet</title>


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
            max-width: 1300px;
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

        .volver-carrito {
            padding: 12px 20px;

            background-color: #f36b21;
            color: white;

            border-radius: 30px;

            font-size: 14px;
            font-weight: 900;
        }


        /* =========================================================
           CONTENIDO
        ========================================================= */

        .contenedor {
            max-width: 1250px;

            margin: 0 auto;
            padding: 70px 35px 100px;
        }

        .checkout-titulo span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .checkout-titulo h1 {
            margin-top: 10px;

            font-size: 52px;
            letter-spacing: -2px;
        }

        .checkout-titulo p {
            margin-top: 15px;

            color: #68798c;

            font-size: 16px;
        }


        /* =========================================================
           DISTRIBUCIÓN
        ========================================================= */

        .checkout-layout {
            margin-top: 45px;

            display: grid;

            grid-template-columns:
                minmax(0, 1.6fr)
                minmax(320px, 1fr);

            gap: 35px;

            align-items: start;
        }


        /* =========================================================
           FORMULARIO
        ========================================================= */

        .formulario {
            padding: 35px;

            background-color: white;

            border: 1px solid #dce5ee;
            border-radius: 22px;

            box-shadow:
                0 15px 35px rgba(16, 37, 66, 0.06);
        }

        .formulario h2 {
            margin-bottom: 28px;

            font-size: 26px;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 18px;
        }

        .grupo {
            margin-bottom: 20px;
        }

        .grupo label {
            display: block;

            margin-bottom: 8px;

            font-size: 13px;
            font-weight: 900;
        }

        .grupo input {
            width: 100%;

            padding: 14px 15px;

            border: 1px solid #cbd8e6;
            border-radius: 11px;

            background-color: #f9fbfd;
            color: #102542;

            font-size: 15px;

            outline: none;
        }

        .grupo input:focus {
            border-color: #f36b21;

            background-color: white;

            box-shadow:
                0 0 0 4px rgba(243, 107, 33, 0.1);
        }

        .nota-formulario {
            margin-top: 5px;

            color: #8392a2;

            font-size: 12px;
            line-height: 1.5;
        }

        .boton-continuar {
            width: 100%;

            margin-top: 15px;

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

        .boton-continuar:hover {
            background-color: #102542;
        }


        /* =========================================================
           RESUMEN
        ========================================================= */

        .resumen {
            position: sticky;
            top: 30px;

            padding: 32px;

            background-color: #071827;
            color: white;

            border-radius: 22px;
        }

        .resumen h2 {
            margin-bottom: 25px;

            font-size: 26px;
        }


        /* PRODUCTOS */

        .producto-resumen {
            padding: 14px 0;

            border-bottom:
                1px solid rgba(255, 255, 255, 0.1);
        }

        .producto-resumen-superior {
            display: flex;
            justify-content: space-between;

            gap: 20px;
        }

        .producto-resumen strong {
            font-size: 14px;
        }

        .producto-resumen span {
            color: #aebbc8;

            font-size: 12px;
        }


        /* TOTALES */

        .totales {
            margin-top: 25px;
        }

        .fila-total {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 14px;

            color: #c7d2dd;

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

            font-size: 25px;
            font-weight: 900;
        }

        .envio-gratis {
            color: #6fd398 !important;

            font-weight: 800;
        }

        .nota {
            margin-top: 22px;

            color: #94a4b5;

            font-size: 12px;
            line-height: 1.5;
        }


        /* =========================================================
           AVISO
        ========================================================= */

        .aviso {
            margin-top: 25px;

            padding: 15px 18px;

            background-color: #fff4ed;

            border-left: 4px solid #f36b21;
            border-radius: 8px;

            color: #74432a;

            font-size: 13px;
            line-height: 1.5;
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

        @media (max-width: 900px) {

            .checkout-layout {
                grid-template-columns: 1fr;
            }

            .resumen {
                position: static;
            }

        }


        @media (max-width: 650px) {

            .barra-superior {
                padding: 18px 22px;
            }

            .contenedor {
                padding: 50px 22px;
            }

            .fila {
                grid-template-columns: 1fr;
            }

            .checkout-titulo h1 {
                font-size: 40px;
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
            href="carrito.php"
            class="volver-carrito"
        >
            ← Volver al carrito
        </a>


    </div>

</header>


<main class="contenedor">


    <div class="checkout-titulo">

        <span>
            Finalizar compra
        </span>

        <h1>
            Checkout
        </h1>

        <p>
            Introduce los datos necesarios para generar
            el pedido de demostración.
        </p>

    </div>


    <div class="checkout-layout">


        <!-- =====================================================
             DATOS DEL CLIENTE
        ====================================================== -->

        <section>


            <form
                class="formulario"
                method="POST"
                action="acciones/procesar_checkout.php"
            >


                <h2>
                    Datos del cliente
                </h2>


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

                        <label for="apellidos">
                            Apellidos *
                        </label>

                        <input
                            type="text"
                            id="apellidos"
                            name="apellidos"
                            required
                        >

                    </div>


                </div>


                <div class="fila">


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


                    <div class="grupo">

                        <label for="telefono">
                            Teléfono
                        </label>

                        <input
                            type="tel"
                            id="telefono"
                            name="telefono"
                        >

                    </div>


                </div>


                <div class="grupo">

                    <label for="direccion">
                        Dirección *
                    </label>

                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        required
                    >

                </div>


                <div class="fila">


                    <div class="grupo">

                        <label for="codigo_postal">
                            Código postal *
                        </label>

                        <input
                            type="text"
                            id="codigo_postal"
                            name="codigo_postal"
                            maxlength="10"
                            required
                        >

                    </div>


                    <div class="grupo">

                        <label for="ciudad">
                            Ciudad *
                        </label>

                        <input
                            type="text"
                            id="ciudad"
                            name="ciudad"
                            required
                        >

                    </div>


                </div>


                <div class="grupo">

                    <label for="provincia">
                        Provincia *
                    </label>

                    <input
                        type="text"
                        id="provincia"
                        name="provincia"
                        required
                    >

                </div>


                <p class="nota-formulario">
                    Los datos introducidos se utilizarán
                    únicamente dentro de este prototipo académico.
                </p>


                <div class="aviso">

                    <strong>
                        Compra simulada:
                    </strong>

                    en ningún momento se solicitarán
                    datos bancarios reales ni se realizará
                    ningún cobro.

                </div>


                <button
                    class="boton-continuar"
                    type="submit"
                >
                    Continuar al pago simulado
                </button>


            </form>


        </section>


        <!-- =====================================================
             RESUMEN
        ====================================================== -->

        <aside class="resumen">


            <h2>
                Resumen del pedido
            </h2>


            <?php foreach ($productos_checkout as $producto): ?>


                <div class="producto-resumen">


                    <div class="producto-resumen-superior">


                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $producto["nombre"]
                            );
                            ?>

                        </strong>


                        <strong>

                            <?php
                            echo number_format(
                                $producto["total_linea"],
                                2,
                                ",",
                                "."
                            );
                            ?>

                            €

                        </strong>


                    </div>


                    <span>

                        <?php echo intval($producto["cantidad"]); ?>

                        ×

                        <?php
                        echo number_format(
                            $producto["precio"],
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
                            $subtotal,
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
                        IVA (21 %)
                    </span>

                    <strong>

                        <?php
                        echo number_format(
                            $iva,
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


                    <?php if ($envio == 0): ?>


                        <strong class="envio-gratis">
                            Gratis
                        </strong>


                    <?php else: ?>


                        <strong>

                            <?php
                            echo number_format(
                                $envio,
                                2,
                                ",",
                                "."
                            );
                            ?>

                            €

                        </strong>


                    <?php endif; ?>


                </div>


                <div class="fila-total">

                    <span>
                        Descuento
                    </span>

                    <strong>

                        <?php
                        echo number_format(
                            $descuento,
                            2,
                            ",",
                            "."
                        );
                        ?>

                        €

                    </strong>

                </div>


                <div class="total-final">

                    <span>
                        Total
                    </span>

                    <span>

                        <?php
                        echo number_format(
                            $total,
                            2,
                            ",",
                            "."
                        );
                        ?>

                        €

                    </span>

                </div>


            </div>


            <p class="nota">

                Envío gratuito para pedidos
                con subtotal igual o superior a 200 €.

            </p>


        </aside>


    </div>


</main>


<footer class="footer">

    Prototipo académico de comercio electrónico.
    No se realizan ventas ni pagos reales.

</footer>


</body>

</html>