<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| COMPROBAR PRODUCTO
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"])) {
    header("Location: index.php#tienda");
    exit();
}

$id_producto = intval($_GET["id"]);


/*
|--------------------------------------------------------------------------
| OBTENER PRODUCTO
|--------------------------------------------------------------------------
*/

$sql = "SELECT *
        FROM productos
        WHERE id = ?
        AND activo = 1";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id_producto
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows !== 1) {

    header("Location: index.php#tienda");
    exit();

}

$producto = $resultado->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| REGISTRAR EVENTO: product.viewed
|--------------------------------------------------------------------------
*/

$tipo_evento = "product.viewed";

$session_id = session_id();

$metadata = json_encode(
    [
        "sku" => $producto["sku"],
        "nombre" => $producto["nombre"],
        "categoria" => $producto["categoria"]
    ],
    JSON_UNESCAPED_UNICODE
);


$sql_evento = "INSERT INTO eventos
               (
                   tipo,
                   session_id,
                   producto_id,
                   metadata
               )
               VALUES (?, ?, ?, ?)";

$stmt_evento = $conexion->prepare($sql_evento);

$stmt_evento->bind_param(
    "ssis",
    $tipo_evento,
    $session_id,
    $id_producto,
    $metadata
);

$stmt_evento->execute();

$stmt_evento->close();


/*
|--------------------------------------------------------------------------
| CONTADOR DEL CARRITO
|--------------------------------------------------------------------------
*/

$unidades_carrito = 0;

if (
    isset($_SESSION["carrito"]) &&
    is_array($_SESSION["carrito"])
) {

    foreach ($_SESSION["carrito"] as $cantidad) {

        $unidades_carrito += intval($cantidad);

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

    <title>
        <?php echo htmlspecialchars($producto["nombre"]); ?>
        | PreyNet
    </title>


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
            align-items: center;
        }

        .navegacion a {
            color: white;
            font-size: 14px;
            font-weight: 800;
        }

        .navegacion a:hover {
            color: #f36b21;
        }

        .carrito {
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


        /* PRODUCTO */

        .producto {
            max-width: 1200px;

            margin: 70px auto;

            padding: 0 35px;

            display: grid;
            grid-template-columns: 1fr 1fr;

            gap: 65px;
            align-items: center;
        }

        .producto-imagen {
            min-height: 500px;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #eaf0f6,
                    #ffffff
                );

            border: 1px solid #dce5ee;
            border-radius: 25px;
        }

        .producto-imagen img {
            width: 100%;
            height: 100%;

            object-fit: cover;
        }

        .sin-imagen {
            text-align: center;
            color: #91a2b4;
        }

        .sin-imagen strong {
            display: block;

            margin-bottom: 10px;

            font-size: 25px;
        }

        .producto-info {
            padding: 20px 0;
        }

        .categoria {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .producto-info h1 {
            margin-top: 12px;

            font-size: 48px;
            line-height: 1.05;

            letter-spacing: -2px;
        }

        .sku {
            margin-top: 12px;

            color: #8190a0;

            font-size: 13px;
        }

        .descripcion {
            margin-top: 28px;

            color: #5c6f82;

            font-size: 17px;
            line-height: 1.7;
        }

        .precio {
            margin-top: 30px;

            font-size: 39px;
            font-weight: 900;
        }

        .stock {
            margin-top: 8px;

            color: #506579;

            font-size: 14px;
        }


        /* FORMULARIO */

        .formulario-carrito {
            margin-top: 35px;

            display: flex;
            gap: 15px;
            align-items: center;
        }

        .cantidad {
            width: 100px;

            padding: 15px;

            border: 1px solid #cbd8e6;
            border-radius: 12px;

            font-size: 16px;
            text-align: center;
        }

        .boton-carrito {
            padding: 16px 30px;

            border: none;
            border-radius: 40px;

            background-color: #f36b21;
            color: white;

            font-size: 15px;
            font-weight: 900;

            cursor: pointer;

            transition: all 0.25s ease;
        }

        .boton-carrito:hover {
            background-color: #102542;
            transform: translateY(-2px);
        }

        .volver {
            display: inline-block;

            margin-top: 30px;

            color: #102542;

            font-size: 14px;
            font-weight: 800;
        }

        .volver:hover {
            color: #f36b21;
        }


        /* FOOTER */

        .footer {
            margin-top: 100px;

            padding: 35px;

            background-color: #071827;
            color: #aebbc8;

            text-align: center;

            font-size: 13px;
        }


        @media (max-width: 850px) {

            .producto {
                grid-template-columns: 1fr;
            }

            .producto-imagen {
                min-height: 350px;
            }

            .barra-superior {
                padding: 18px 22px;
            }

            .navegacion {
                display: none;
            }

            .producto-info h1 {
                font-size: 38px;
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
            class="carrito"
        >

            Carrito

            <span class="contador">
                <?php echo $unidades_carrito; ?>
            </span>

        </a>


    </div>

</header>


<main class="producto">


    <!-- IMAGEN -->

    <div class="producto-imagen">


        <?php if (!empty($producto["imagen"])): ?>


            <img
                src="<?php echo htmlspecialchars($producto["imagen"]); ?>"
                alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
            >


        <?php else: ?>


            <div class="sin-imagen">

                <strong>
                    <?php echo htmlspecialchars($producto["categoria"]); ?>
                </strong>

                <span>
                    Imagen del producto pendiente
                </span>

            </div>


        <?php endif; ?>


    </div>


    <!-- INFORMACIÓN -->

    <div class="producto-info">


        <span class="categoria">

            <?php
            echo htmlspecialchars(
                $producto["categoria"]
            );
            ?>

        </span>


        <h1>

            <?php
            echo htmlspecialchars(
                $producto["nombre"]
            );
            ?>

        </h1>


        <div class="sku">

            SKU:

            <?php
            echo htmlspecialchars(
                $producto["sku"]
            );
            ?>

        </div>


        <p class="descripcion">

            <?php
            echo htmlspecialchars(
                $producto["descripcion"]
            );
            ?>

        </p>


        <div class="precio">

            <?php
            echo number_format(
                $producto["precio"],
                2,
                ",",
                "."
            );
            ?>

            €

        </div>


        <div class="stock">

            <?php if ($producto["stock"] > 0): ?>

                Stock disponible:
                <?php echo intval($producto["stock"]); ?>

            <?php else: ?>

                Producto sin stock

            <?php endif; ?>

        </div>


        <?php if ($producto["stock"] > 0): ?>


            <form
                class="formulario-carrito"
                action="acciones/agregar_carrito.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="producto_id"
                    value="<?php echo intval($producto["id"]); ?>"
                >


                <input
                    class="cantidad"
                    type="number"
                    name="cantidad"
                    value="1"
                    min="1"
                    max="<?php echo intval($producto["stock"]); ?>"
                    required
                >


                <button
                    class="boton-carrito"
                    type="submit"
                >
                    Añadir al carrito
                </button>


            </form>


        <?php endif; ?>


        <a
            class="volver"
            href="index.php#tienda"
        >
            ← Volver al catálogo
        </a>


    </div>


</main>


<footer class="footer">

    Prototipo académico de comercio electrónico.
    No se realizan ventas ni pagos reales.

</footer>


</body>

</html>