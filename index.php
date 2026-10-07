<?php

session_start();

include "conexion.php";


/*
|--------------------------------------------------------------------------
| PRODUCTOS DE LA TIENDA
|--------------------------------------------------------------------------
*/

$sql_productos = "
    SELECT *
    FROM productos
    WHERE activo = 1
    ORDER BY id ASC
";

$resultado_productos =
    $conexion->query($sql_productos);


/*
|--------------------------------------------------------------------------
| UNIDADES DEL CARRITO
|--------------------------------------------------------------------------
*/

$unidades_carrito = 0;

if (
    isset($_SESSION["carrito"]) &&
    is_array($_SESSION["carrito"])
) {

    foreach (
        $_SESSION["carrito"]
        as $cantidad
    ) {

        $unidades_carrito +=
            intval($cantidad);

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
        PreyNet | Tienda de informática
    </title>

    <link
        rel="stylesheet"
        href="css/estilos.css"
    >

</head>


<body>

<!-- =========================================================
     CABECERA
========================================================= -->

<header class="cabecera">


    <div class="barra-superior">


        <a
            href="#inicio"
            class="logo-preynet"
        >
            PREY<span>NET</span>
        </a>


        <nav class="menu-principal">

            <ul>

                <li>
                    <a href="#inicio">
                        Inicio
                    </a>
                </li>

                <li>
                    <a href="#tienda">
                        Tienda
                    </a>
                </li>

                <li>
                    <a href="#soporte">
                        Soporte
                    </a>
                </li>

                <li>
                    <a href="#contacto">
                        Contacto
                    </a>
                </li>

            </ul>

        </nav>


        <a
            href="carrito.php"
            class="boton-carrito"
        >

            Carrito

            <span class="contador-carrito">
                <?php echo $unidades_carrito; ?>
            </span>

        </a>


    </div>


</header>


<main>


<!-- =========================================================
     HERO
========================================================= -->

<section
    id="inicio"
    class="hero"
>


    <video
        class="video-fondo"
        autoplay
        muted
        loop
        playsinline
    >

        <source
            src="videos/fondo-preynet.mp4"
            type="video/mp4"
        >

    </video>


    <div class="capa-hero"></div>


    <div class="contenido-hero">


        <span class="etiqueta-hero">
            Tecnología para tu día a día
        </span>


        <h1>
            Informática y tecnología en un solo lugar
        </h1>


        <p>
            Descubre nuestra selección de ordenadores,
            portátiles, monitores, periféricos,
            dispositivos móviles y accesorios tecnológicos.
        </p>


        <a
            href="#tienda"
            class="boton-hero"
        >
            Ver productos
        </a>


    </div>


</section>


<!-- =========================================================
     INTRODUCCIÓN
========================================================= -->

<section
    id="tienda"
    class="tienda-intro"
>


    <div class="tienda-intro-contenido">


        <span>
            Catálogo PreyNet
        </span>


        <h2>
            Encuentra el equipo que necesitas
        </h2>


        <p>
            Nuestro catálogo de demostración incluye
            diferentes categorías de productos informáticos.
            Los productos, precios y existencias se gestionan
            desde el panel de administración y se almacenan
            en la base de datos de PreyNet.
        </p>


    </div>


</section>


<!-- =========================================================
     CATÁLOGO DINÁMICO
========================================================= -->

<section class="catalogo">


    <div class="catalogo-contenedor">


        <div class="productos-grid">


            <?php
            if (
                $resultado_productos &&
                $resultado_productos->num_rows > 0
            ):
            ?>


                <?php
                while (
                    $producto =
                    $resultado_productos->fetch_assoc()
                ):
                ?>


                    <article class="tarjeta-producto">


                        <div class="producto-imagen">


                            <?php
                            if (
                                !empty(
                                    $producto["imagen"]
                                )
                            ):
                            ?>


                                <img
                                    src="<?php echo htmlspecialchars($producto["imagen"]); ?>"
                                    alt="<?php echo htmlspecialchars($producto["nombre"]); ?>"
                                >


                            <?php else: ?>


                                <div class="producto-sin-imagen">


                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $producto["categoria"]
                                        );
                                        ?>

                                    </strong>


                                    <span>
                                        Imagen pendiente
                                    </span>


                                </div>


                            <?php endif; ?>


                        </div>


                        <div class="producto-contenido">


                            <span class="producto-categoria">

                                <?php
                                echo htmlspecialchars(
                                    $producto["categoria"]
                                );
                                ?>

                            </span>


                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $producto["nombre"]
                                );
                                ?>

                            </h3>


                            <p class="producto-descripcion">

                                <?php
                                echo htmlspecialchars(
                                    $producto["descripcion"]
                                );
                                ?>

                            </p>


                            <div class="producto-precio">

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


                            <div class="producto-stock">

                                Stock disponible:

                                <?php
                                echo intval(
                                    $producto["stock"]
                                );
                                ?>

                            </div>


                            <div class="producto-acciones">


                                <a
                                    class="boton-ver-producto"
                                    href="producto.php?id=<?php echo intval($producto["id"]); ?>"
                                >
                                    Ver producto
                                </a>


                            </div>


                        </div>


                    </article>


                <?php endwhile; ?>


            <?php else: ?>


                <div class="sin-productos">


                    <h3>
                        No hay productos disponibles
                    </h3>


                    <p>
                        El catálogo todavía no contiene
                        productos activos.
                    </p>


                </div>


            <?php endif; ?>


        </div>


    </div>


</section>


<!-- =========================================================
     VENTAJAS
========================================================= -->

<section class="ventajas">


    <div class="ventajas-contenedor">


        <article class="ventaja">


            <h3>
                Catálogo actualizado
            </h3>


            <p>
                Los productos, precios y existencias
                proceden directamente de la base de datos.
            </p>


        </article>


        <article class="ventaja">


            <h3>
                Compra simulada
            </h3>


            <p>
                El proceso de checkout y pago se utiliza
                exclusivamente con fines académicos.
            </p>


        </article>


        <article class="ventaja">


            <h3>
                Atención postventa
            </h3>


            <p>
                El prototipo permite registrar incidencias
                relacionadas con pedidos realizados.
            </p>


        </article>


    </div>


</section>


<!-- =========================================================
     SOPORTE
========================================================= -->

<section
    id="soporte"
    class="soporte"
>


    <div class="soporte-contenedor">


        <h2>
            ¿Necesitas ayuda?
        </h2>


        <p>
            PreyNet dispone de un servicio de soporte
            para registrar consultas o incidencias
            relacionadas con los pedidos de demostración.
        </p>


        <a
            href="soporte.php"
            class="boton-soporte"
        >
            Contactar con soporte
        </a>


    </div>


</section>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer
    id="contacto"
    class="footer"
>


    <div class="footer-contenedor">


        <div class="footer-superior">


            <div class="footer-marca">


                <h2>
                    PREY<span>NET</span>
                </h2>


                <p>
                    Prototipo académico de canal digital
                    de venta de material informático
                    desarrollado con PHP y MySQL.
                </p>


            </div>


            <div class="footer-columna">


                <h3>
                    Navegación
                </h3>


                <a href="#inicio">
                    Inicio
                </a>


                <a href="#tienda">
                    Tienda
                </a>


                <a href="carrito.php">
                    Carrito
                </a>


                <a href="soporte.php">
                    Soporte
                </a>


            </div>


            <div class="footer-columna">


                <h3>
                    Información
                </h3>


                <p>
                    Proyecto académico
                </p>


                <p>
                    Murcia, España
                </p>


                <p>
                    2026
                </p>


            </div>


        </div>


        <div class="footer-copy">

            © 2026 PreyNet.
            Proyecto académico de Sistemas de Información Empresarial.

        </div>


    </div>


</footer>


</body>

</html>