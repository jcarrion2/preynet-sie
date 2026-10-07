<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| ASEGURAR QUE EL CARRITO EXISTA
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["carrito"])) {
    $_SESSION["carrito"] = [];
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR O ELIMINAR PRODUCTOS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $accion = $_POST["accion"] ?? "";
    $producto_id = intval($_POST["producto_id"] ?? 0);

    if ($producto_id > 0) {

        /*
        |--------------------------------------------------------------------------
        | ELIMINAR PRODUCTO
        |--------------------------------------------------------------------------
        */

        if ($accion === "eliminar") {

            unset($_SESSION["carrito"][$producto_id]);

        }


        /*
        |--------------------------------------------------------------------------
        | ACTUALIZAR CANTIDAD
        |--------------------------------------------------------------------------
        */

        if ($accion === "actualizar") {

            $cantidad = intval($_POST["cantidad"] ?? 1);

            if ($cantidad < 1) {
                $cantidad = 1;
            }


            /*
             * Comprobamos el stock real antes
             * de actualizar la sesión.
             */

            $sql_stock = "SELECT stock
                          FROM productos
                          WHERE id = ?
                          AND activo = 1";

            $stmt_stock = $conexion->prepare($sql_stock);

            $stmt_stock->bind_param(
                "i",
                $producto_id
            );

            $stmt_stock->execute();

            $resultado_stock = $stmt_stock->get_result();


            if ($resultado_stock->num_rows === 1) {

                $datos_stock = $resultado_stock->fetch_assoc();

                $stock_disponible =
                    intval($datos_stock["stock"]);


                if ($cantidad > $stock_disponible) {

                    $cantidad =
                        $stock_disponible;

                }


                if ($cantidad > 0) {

                    $_SESSION["carrito"][$producto_id] =
                        $cantidad;

                }

            }

            $stmt_stock->close();

        }

    }


    header("Location: carrito.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| OBTENER LOS PRODUCTOS DEL CARRITO
|--------------------------------------------------------------------------
*/

$productos_carrito = [];

$subtotal = 0;

$unidades_carrito = 0;


foreach ($_SESSION["carrito"] as $producto_id => $cantidad) {

    $producto_id = intval($producto_id);

    $cantidad = intval($cantidad);


    if ($cantidad <= 0) {
        continue;
    }


    $sql = "SELECT id,
                   sku,
                   nombre,
                   categoria,
                   descripcion,
                   precio,
                   stock,
                   imagen
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
         * Si por cualquier motivo la cantidad
         * supera el stock, la ajustamos.
         */

        if ($cantidad > $producto["stock"]) {

            $cantidad =
                intval($producto["stock"]);

            $_SESSION["carrito"][$producto_id] =
                $cantidad;

        }


        $total_linea =
            floatval($producto["precio"])
            * $cantidad;


        $producto["cantidad"] =
            $cantidad;

        $producto["total_linea"] =
            $total_linea;


        $productos_carrito[] =
            $producto;


        $subtotal +=
            $total_linea;


        $unidades_carrito +=
            $cantidad;

    }

    $stmt->close();

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

    <title>Carrito | PreyNet</title>


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

        .navegacion {
            display: flex;
            gap: 25px;
        }

        .navegacion a {
            color: white;

            font-size: 14px;
            font-weight: 800;
        }

        .navegacion a:hover {
            color: #f36b21;
        }

        .carrito-header {
            display: flex;
            align-items: center;
            gap: 9px;

            padding: 12px 19px;

            background-color: #f36b21;
            color: white;

            border-radius: 30px;

            font-weight: 900;
        }

        .contador {
            display: flex;
            align-items: center;
            justify-content: center;

            min-width: 24px;
            height: 24px;

            background-color: white;
            color: #102542;

            border-radius: 20px;

            font-size: 12px;
        }


        /* =========================================================
           CONTENIDO
        ========================================================= */

        .contenedor {
            max-width: 1250px;

            margin: 0 auto;
            padding: 70px 35px 100px;
        }

        .cabecera-carrito {
            margin-bottom: 40px;
        }

        .cabecera-carrito span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .cabecera-carrito h1 {
            margin-top: 10px;

            font-size: 52px;
            letter-spacing: -2px;
        }


        /* =========================================================
           DISTRIBUCIÓN
        ========================================================= */

        .carrito-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 2fr)
                minmax(300px, 1fr);

            gap: 35px;

            align-items: start;
        }


        /* =========================================================
           PRODUCTOS
        ========================================================= */

        .lista-productos {
            display: flex;
            flex-direction: column;

            gap: 18px;
        }

        .producto-carrito {
            display: grid;

            grid-template-columns:
                120px
                1fr
                auto;

            gap: 22px;

            align-items: center;

            padding: 22px;

            background-color: white;

            border: 1px solid #dce5ee;
            border-radius: 20px;

            box-shadow:
                0 12px 30px rgba(16, 37, 66, 0.06);
        }


        /* =========================================================
           IMAGEN
        ========================================================= */

        .imagen-producto {
            width: 120px;
            height: 110px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            background-color: #eef3f8;

            border-radius: 14px;

            color: #91a2b4;

            text-align: center;

            font-size: 12px;
        }

        .imagen-producto img {
            width: 100%;
            height: 100%;

            object-fit: cover;
        }


        /* =========================================================
           DATOS PRODUCTO
        ========================================================= */

        .categoria {
            color: #f36b21;

            font-size: 11px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .datos-producto h2 {
            margin-top: 5px;

            font-size: 21px;
        }

        .sku {
            margin-top: 7px;

            color: #8594a4;

            font-size: 12px;
        }

        .precio-unitario {
            margin-top: 12px;

            font-size: 17px;
            font-weight: 900;
        }


        /* =========================================================
           ACCIONES
        ========================================================= */

        .acciones-producto {
            min-width: 190px;
        }

        .form-cantidad {
            display: flex;
            gap: 8px;
            align-items: center;
        }


        /*
        |--------------------------------------------------------------------------
        | BOTONES + Y -
        |--------------------------------------------------------------------------
        */

        .boton-cantidad {
            width: 42px;
            height: 42px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: none;
            border-radius: 50%;

            background-color: #102542;
            color: white;

            font-size: 22px;
            font-weight: 900;

            cursor: pointer;

            transition: all 0.2s ease;
        }

        .boton-cantidad:hover {
            background-color: #f36b21;
            transform: scale(1.06);
        }

        .boton-cantidad:disabled {
            opacity: 0.5;
            cursor: default;
        }


        /*
        |--------------------------------------------------------------------------
        | CAMPO DE CANTIDAD
        |--------------------------------------------------------------------------
        */

        .input-cantidad {
            width: 65px;
            height: 42px;

            padding: 8px;

            border: 1px solid #cbd8e6;
            border-radius: 10px;

            text-align: center;

            font-size: 16px;
            font-weight: 700;

            color: #102542;
            background-color: white;
        }


        /*
         * Ocultamos las flechas propias
         * del input number en Chrome/Edge.
         */

        .input-cantidad::-webkit-inner-spin-button,
        .input-cantidad::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .input-cantidad {
            -moz-appearance: textfield;
        }


        /* =========================================================
           TOTAL LÍNEA
        ========================================================= */

        .total-linea {
            margin-top: 15px;

            font-size: 21px;
            font-weight: 900;
        }


        /* =========================================================
           ELIMINAR
        ========================================================= */

        .form-eliminar {
            margin-top: 12px;
        }

        .boton-eliminar {
            border: none;
            background: none;

            color: #c34c20;

            font-size: 12px;
            font-weight: 800;

            cursor: pointer;
        }

        .boton-eliminar:hover {
            text-decoration: underline;
        }


        /* =========================================================
           RESUMEN
        ========================================================= */

        .resumen {
            position: sticky;
            top: 30px;

            padding: 30px;

            background-color: #071827;
            color: white;

            border-radius: 22px;
        }

        .resumen h2 {
            margin-bottom: 25px;

            font-size: 25px;
        }

        .fila-resumen {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 18px;

            color: #c8d3de;

            font-size: 14px;
        }

        .total-resumen {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-top: 25px;
            padding-top: 22px;

            border-top:
                1px solid rgba(255, 255, 255, 0.15);

            font-size: 24px;
            font-weight: 900;
        }

        .nota {
            margin-top: 20px;

            color: #9eacba;

            font-size: 12px;
            line-height: 1.5;
        }

        .boton-checkout {
            display: block;

            margin-top: 28px;

            padding: 16px;

            background-color: #f36b21;
            color: white;

            border-radius: 35px;

            text-align: center;

            font-weight: 900;
        }

        .boton-checkout:hover {
            background-color: white;
            color: #102542;
        }

        .seguir-comprando {
            display: block;

            margin-top: 15px;

            color: #c8d3de;

            text-align: center;

            font-size: 13px;
            font-weight: 800;
        }


        /* =========================================================
           CARRITO VACÍO
        ========================================================= */

        .carrito-vacio {
            padding: 60px 30px;

            background-color: white;

            border-radius: 22px;

            text-align: center;
        }

        .carrito-vacio h2 {
            font-size: 30px;
        }

        .carrito-vacio p {
            margin-top: 12px;

            color: #697b8e;
        }

        .carrito-vacio a {
            display: inline-block;

            margin-top: 25px;

            padding: 14px 26px;

            background-color: #f36b21;
            color: white;

            border-radius: 30px;

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

        @media (max-width: 900px) {

            .carrito-layout {
                grid-template-columns: 1fr;
            }

            .resumen {
                position: static;
            }

        }


        @media (max-width: 700px) {

            .barra-superior {
                padding: 18px 22px;
            }

            .navegacion {
                display: none;
            }

            .contenedor {
                padding: 50px 22px;
            }

            .producto-carrito {
                grid-template-columns: 1fr;
            }

            .imagen-producto {
                width: 100%;
                height: 200px;
            }

            .acciones-producto {
                min-width: 0;
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


        <nav class="navegacion">

            <a href="index.php">
                Inicio
            </a>

            <a href="index.php#tienda">
                Tienda
            </a>

            <a href="index.php#soporte">
                Soporte
            </a>

        </nav>


        <a
            href="carrito.php"
            class="carrito-header"
        >

            Carrito

            <span class="contador">
                <?php echo $unidades_carrito; ?>
            </span>

        </a>


    </div>

</header>


<main class="contenedor">


    <div class="cabecera-carrito">

        <span>
            Tu selección
        </span>

        <h1>
            Carrito de compra
        </h1>

    </div>


    <?php if (count($productos_carrito) > 0): ?>


        <div class="carrito-layout">


            <!-- PRODUCTOS -->

            <section class="lista-productos">


                <?php foreach ($productos_carrito as $producto): ?>


                    <article class="producto-carrito">


                        <!-- IMAGEN -->

                        <div class="imagen-producto">


                            <?php if (!empty($producto["imagen"])): ?>


                                <img
                                    src="<?php echo htmlspecialchars($producto["imagen"]); ?>"
                                    alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                                >


                            <?php else: ?>


                                <?php
                                echo htmlspecialchars(
                                    $producto["categoria"]
                                );
                                ?>


                            <?php endif; ?>


                        </div>


                        <!-- INFORMACIÓN -->

                        <div class="datos-producto">


                            <span class="categoria">

                                <?php
                                echo htmlspecialchars(
                                    $producto["categoria"]
                                );
                                ?>

                            </span>


                            <h2>

                                <?php
                                echo htmlspecialchars(
                                    $producto["nombre"]
                                );
                                ?>

                            </h2>


                            <div class="sku">

                                SKU:

                                <?php
                                echo htmlspecialchars(
                                    $producto["sku"]
                                );
                                ?>

                            </div>


                            <div class="precio-unitario">

                                <?php
                                echo number_format(
                                    $producto["precio"],
                                    2,
                                    ",",
                                    "."
                                );
                                ?>

                                € / unidad

                            </div>


                        </div>


                        <!-- ACCIONES -->

                        <div class="acciones-producto">


                            <!-- CANTIDAD -->

                            <form
                                class="form-cantidad"
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="actualizar"
                                >

                                <input
                                    type="hidden"
                                    name="producto_id"
                                    value="<?php echo intval($producto["id"]); ?>"
                                >


                                <!-- RESTAR -->

                                <button
                                    type="button"
                                    class="boton-cantidad"
                                    onclick="cambiarCantidad(this, -1)"
                                    aria-label="Restar una unidad"
                                >
                                    −
                                </button>


                                <!-- CANTIDAD -->

                                <input
                                    class="input-cantidad"
                                    type="number"
                                    name="cantidad"
                                    min="1"
                                    max="<?php echo intval($producto["stock"]); ?>"
                                    value="<?php echo intval($producto["cantidad"]); ?>"
                                    onchange="actualizarCantidad(this)"
                                    required
                                >


                                <!-- SUMAR -->

                                <button
                                    type="button"
                                    class="boton-cantidad"
                                    onclick="cambiarCantidad(this, 1)"
                                    aria-label="Añadir una unidad"
                                >
                                    +
                                </button>


                            </form>


                            <!-- TOTAL DE ESTA LÍNEA -->

                            <div class="total-linea">

                                <?php
                                echo number_format(
                                    $producto["total_linea"],
                                    2,
                                    ",",
                                    "."
                                );
                                ?>

                                €

                            </div>


                            <!-- ELIMINAR -->

                            <form
                                class="form-eliminar"
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="accion"
                                    value="eliminar"
                                >

                                <input
                                    type="hidden"
                                    name="producto_id"
                                    value="<?php echo intval($producto["id"]); ?>"
                                >


                                <button
                                    class="boton-eliminar"
                                    type="submit"
                                >
                                    Eliminar del carrito
                                </button>


                            </form>


                        </div>


                    </article>


                <?php endforeach; ?>


            </section>


            <!-- =====================================================
                 RESUMEN
            ====================================================== -->

            <aside class="resumen">


                <h2>
                    Resumen
                </h2>


                <div class="fila-resumen">

                    <span>
                        Unidades
                    </span>

                    <strong>
                        <?php echo $unidades_carrito; ?>
                    </strong>

                </div>


                <div class="fila-resumen">

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


                <div class="total-resumen">

                    <span>
                        Total provisional
                    </span>

                    <span>

                        <?php
                        echo number_format(
                            $subtotal,
                            2,
                            ",",
                            "."
                        );
                        ?>

                        €

                    </span>

                </div>


                <p class="nota">
                    Los impuestos, descuentos y gastos
                    de envío se calcularán durante
                    el checkout.
                </p>


                <a
                    href="checkout.php"
                    class="boton-checkout"
                >
                    Continuar al checkout
                </a>


                <a
                    href="index.php#tienda"
                    class="seguir-comprando"
                >
                    Seguir comprando
                </a>


            </aside>


        </div>


    <?php else: ?>


        <!-- =========================================================
             CARRITO VACÍO
        ========================================================= -->

        <div class="carrito-vacio">

            <h2>
                Tu carrito está vacío
            </h2>

            <p>
                Añade algún producto del catálogo
                para iniciar una compra simulada.
            </p>

            <a href="index.php#tienda">
                Ver productos
            </a>

        </div>


    <?php endif; ?>


</main>


<footer class="footer">

    Prototipo académico de comercio electrónico.
    No se realizan ventas ni pagos reales.

</footer>


<script>

/*
|--------------------------------------------------------------------------
| CAMBIAR CANTIDAD CON + Y -
|--------------------------------------------------------------------------
*/

function cambiarCantidad(boton, cambio) {

    const formulario =
        boton.closest("form");

    const input =
        formulario.querySelector(
            'input[name="cantidad"]'
        );


    let cantidad =
        parseInt(input.value) || 1;

    const minimo =
        parseInt(input.min) || 1;

    const maximo =
        parseInt(input.max) || 999999;


    cantidad += cambio;


    /*
     * No permitimos bajar de 1.
     * Para eliminar un producto se utiliza
     * el botón "Eliminar del carrito".
     */

    if (cantidad < minimo) {
        cantidad = minimo;
    }


    /*
     * No permitimos superar el stock.
     */

    if (cantidad > maximo) {
        cantidad = maximo;
    }


    /*
     * Si realmente ha cambiado,
     * enviamos automáticamente el formulario.
     */

    if (parseInt(input.value) !== cantidad) {

        input.value = cantidad;

        bloquearFormulario(formulario);

        formulario.submit();

    }

}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR SI EL USUARIO ESCRIBE LA CANTIDAD
|--------------------------------------------------------------------------
*/

function actualizarCantidad(input) {

    const formulario =
        input.closest("form");


    let cantidad =
        parseInt(input.value);

    const minimo =
        parseInt(input.min) || 1;

    const maximo =
        parseInt(input.max) || 999999;


    if (
        isNaN(cantidad) ||
        cantidad < minimo
    ) {

        cantidad = minimo;

    }


    if (cantidad > maximo) {

        cantidad = maximo;

    }


    input.value = cantidad;


    bloquearFormulario(formulario);

    formulario.submit();

}


/*
|--------------------------------------------------------------------------
| EVITAR DOBLE CLIC MIENTRAS SE ACTUALIZA
|--------------------------------------------------------------------------
*/

function bloquearFormulario(formulario) {

    const botones =
        formulario.querySelectorAll("button");


    botones.forEach(function(boton) {

        boton.disabled = true;

    });

}

</script>


</body>

</html>