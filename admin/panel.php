<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";


/*
|--------------------------------------------------------------------------
| INDICADORES DEL PANEL
|--------------------------------------------------------------------------
*/

$total_productos = 0;
$total_pedidos = 0;
$total_clientes = 0;
$total_eventos = 0;


/*
|--------------------------------------------------------------------------
| PRODUCTOS
|--------------------------------------------------------------------------
*/

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM productos"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_productos = intval($fila["total"]);
}


/*
|--------------------------------------------------------------------------
| PEDIDOS
|--------------------------------------------------------------------------
*/

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM pedidos"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_pedidos = intval($fila["total"]);
}


/*
|--------------------------------------------------------------------------
| CLIENTES
|--------------------------------------------------------------------------
*/

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM clientes"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_clientes = intval($fila["total"]);
}


/*
|--------------------------------------------------------------------------
| EVENTOS DE NEGOCIO
|--------------------------------------------------------------------------
*/

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM eventos"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_eventos = intval($fila["total"]);
}


/*
|--------------------------------------------------------------------------
| PAGOS APROBADOS Y VENTAS SIMULADAS
|--------------------------------------------------------------------------
|
| El estado del pedido representa su situación logística: pagado,
| en preparación, enviado, etc. Una venta sigue siendo válida aunque
| el pedido avance a otro estado, por lo que las ventas se calculan
| a partir de los pagos simulados aprobados.
|
*/

$pedidos_pagados = 0;
$ventas_simuladas = 0;

$resultado = $conexion->query(
    "SELECT
        COUNT(*) AS pedidos_pagados,
        COALESCE(SUM(p.total), 0) AS ventas_simuladas
     FROM pedidos p
     WHERE EXISTS (
        SELECT 1
        FROM pagos pg
        WHERE pg.pedido_id = p.id
          AND pg.estado = 'simulado_aprobado'
     )"
);

if ($resultado) {

    $fila = $resultado->fetch_assoc();

    $pedidos_pagados =
        intval($fila["pedidos_pagados"]);

    $ventas_simuladas =
        floatval($fila["ventas_simuladas"]);
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
        Panel de administración | PreyNet
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


        /* =========================================================
           CABECERA
        ========================================================= */

        .admin-header {
            width: 100%;

            padding: 22px 50px;

            background-color: #071827;
            color: white;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 30px;
        }

        .admin-header h1 {
            font-size: 26px;
            font-weight: 900;
        }

        .header-acciones {
            display: flex;
            align-items: center;

            gap: 12px;
        }

        .header-acciones a {
            padding: 12px 22px;

            border-radius: 40px;

            font-size: 14px;
            font-weight: 800;

            transition: all 0.25s ease;
        }

        .boton-tienda {
            background-color: white;
            color: #102542;
        }

        .boton-tienda:hover {
            background-color: #dfe7ef;
        }

        .boton-salir {
            background-color: #f36b21;
            color: white;
        }

        .boton-salir:hover {
            background-color: white;
            color: #102542;
        }


        /* =========================================================
           CONTENEDOR
        ========================================================= */

        .admin-contenedor {
            max-width: 1250px;

            margin: 0 auto;

            padding: 65px 35px 90px;
        }


        /* =========================================================
           BIENVENIDA
        ========================================================= */

        .bienvenida {
            margin-bottom: 38px;
        }

        .bienvenida span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .bienvenida h2 {
            margin-top: 10px;

            font-size: 44px;

            color: #102542;

            letter-spacing: -1px;
        }

        .bienvenida p {
            max-width: 760px;

            margin-top: 12px;

            font-size: 17px;
            line-height: 1.7;

            color: #4f6072;
        }


        /* =========================================================
           INDICADORES
        ========================================================= */

        .indicadores {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 50px;
        }

        .indicador {
            padding: 25px;

            background-color: white;

            border: 1px solid #dbe4ee;
            border-radius: 18px;

            box-shadow:
                0 10px 28px
                rgba(16, 37, 66, 0.06);
        }

        .indicador-titulo {
            color: #718295;

            font-size: 12px;
            font-weight: 800;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .indicador-valor {
            margin-top: 10px;

            font-size: 31px;
            font-weight: 900;

            color: #102542;
        }

        .indicador-destacado {
            color: #f36b21;
        }

        .indicador-texto {
            margin-top: 5px;

            color: #8a98a6;

            font-size: 12px;
        }


        /* =========================================================
           TÍTULO SECCIONES
        ========================================================= */

        .titulo-secciones {
            margin-bottom: 24px;
        }

        .titulo-secciones h2 {
            font-size: 28px;
        }

        .titulo-secciones p {
            margin-top: 7px;

            color: #66788a;

            font-size: 14px;
        }


        /* =========================================================
           TARJETAS
        ========================================================= */

        .panel-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 24px;
        }

        .panel-card {
            position: relative;

            min-height: 245px;

            padding: 32px;

            background-color: white;

            border-radius: 22px;
            border: 1px solid #dbe4ee;

            box-shadow:
                0 16px 38px
                rgba(16, 37, 66, 0.07);

            color: #102542;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .panel-card:hover {
            transform: translateY(-6px);

            box-shadow:
                0 26px 55px
                rgba(16, 37, 66, 0.13);
        }


        /* ICONO */

        .panel-icono {
            width: 55px;
            height: 55px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 22px;

            background-color: #102542;
            color: white;

            border-radius: 14px;

            font-size: 14px;
            font-weight: 900;
        }

        .panel-card:hover .panel-icono {
            background-color: #f36b21;
        }


        /* TEXTO */

        .panel-card h3 {
            margin-bottom: 10px;

            font-size: 23px;
        }

        .panel-card p {
            color: #5f6f80;

            font-size: 14px;
            line-height: 1.65;
        }


        /* CONTADOR */

        .contador-card {
            position: absolute;

            top: 28px;
            right: 28px;

            min-width: 34px;
            height: 34px;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 0 9px;

            background-color: #fff0e8;
            color: #d85518;

            border-radius: 20px;

            font-size: 13px;
            font-weight: 900;
        }


        /* =========================================================
           NOTA
        ========================================================= */

        .nota-panel {
            margin-top: 45px;

            padding: 18px 22px;

            background-color: #fff4ed;

            border-left: 4px solid #f36b21;
            border-radius: 10px;

            color: #71432c;

            font-size: 13px;
            line-height: 1.55;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1000px) {

            .indicadores {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .panel-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 700px) {

            .admin-header {
                padding: 22px 25px;

                flex-direction: column;
                align-items: flex-start;
            }

            .header-acciones {
                width: 100%;
            }

            .admin-contenedor {
                padding: 45px 22px 70px;
            }

            .bienvenida h2 {
                font-size: 34px;
            }

            .indicadores {
                grid-template-columns: 1fr;
            }

            .panel-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     CABECERA
========================================================= -->

<header class="admin-header">

    <h1>
        Administración PreyNet
    </h1>

    <div class="header-acciones">

        <a
            href="../index.php"
            class="boton-tienda"
        >
            Ver tienda
        </a>

        <a
            href="../acciones/logout.php"
            class="boton-salir"
        >
            Cerrar sesión
        </a>

    </div>

</header>


<!-- =========================================================
     CONTENIDO
========================================================= -->

<main class="admin-contenedor">

    <!-- BIENVENIDA -->

    <section class="bienvenida">

        <span>
            Back-office
        </span>

        <h2>

            Bienvenido,

            <?php
            echo htmlspecialchars(
                $_SESSION["usuario_nombre"]
            );
            ?>

        </h2>

        <p>
            Desde este panel puedes gestionar la actividad
            del canal digital de venta de PreyNet:
            productos, pedidos, clientes, incidencias
            y eventos de negocio.
        </p>

    </section>


    <!-- =====================================================
         INDICADORES
    ====================================================== -->

    <section class="indicadores">

        <div class="indicador">

            <div class="indicador-titulo">
                Productos
            </div>

            <div class="indicador-valor">
                <?php echo $total_productos; ?>
            </div>

            <div class="indicador-texto">
                Productos registrados
            </div>

        </div>


        <div class="indicador">

            <div class="indicador-titulo">
                Pedidos
            </div>

            <div class="indicador-valor">
                <?php echo $total_pedidos; ?>
            </div>

            <div class="indicador-texto">

                <?php
                echo $pedidos_pagados;
                ?>

                pedidos con pago aprobado

            </div>

        </div>


        <div class="indicador">

            <div class="indicador-titulo">
                Clientes
            </div>

            <div class="indicador-valor">
                <?php echo $total_clientes; ?>
            </div>

            <div class="indicador-texto">
                Clientes registrados
            </div>

        </div>


        <div class="indicador">

            <div class="indicador-titulo">
                Ventas simuladas
            </div>

            <div
                class="
                    indicador-valor
                    indicador-destacado
                "
            >

                <?php
                echo number_format(
                    $ventas_simuladas,
                    2,
                    ",",
                    "."
                );
                ?>

                €

            </div>

            <div class="indicador-texto">
                Total de pagos simulados aprobados
            </div>

        </div>

    </section>


    <!-- =====================================================
         SECCIONES
    ====================================================== -->

    <div class="titulo-secciones">

        <h2>
            Gestión de la tienda
        </h2>

        <p>
            Selecciona el área que quieres consultar
            o administrar.
        </p>

    </div>


    <section class="panel-grid">

        <!-- PRODUCTOS -->

        <a
            href="productos.php"
            class="panel-card"
        >

            <div class="panel-icono">
                PR
            </div>

            <div class="contador-card">

                <?php
                echo $total_productos;
                ?>

            </div>

            <h3>
                Productos
            </h3>

            <p>
                Crear, editar, eliminar y consultar
                los productos disponibles en el catálogo,
                sus precios y el stock actual.
            </p>

        </a>


        <!-- PEDIDOS -->

        <a
            href="pedidos.php"
            class="panel-card"
        >

            <div class="panel-icono">
                PE
            </div>

            <div class="contador-card">

                <?php
                echo $total_pedidos;
                ?>

            </div>

            <h3>
                Pedidos
            </h3>

            <p>
                Consultar los pedidos realizados,
                su importe, estado, cliente y detalle
                de los productos adquiridos.
            </p>

        </a>


        <!-- CLIENTES -->

        <a
            href="clientes.php"
            class="panel-card"
        >

            <div class="panel-icono">
                CL
            </div>

            <div class="contador-card">

                <?php
                echo $total_clientes;
                ?>

            </div>

            <h3>
                Clientes
            </h3>

            <p>
                Consultar los clientes registrados
                durante el proceso de compra y
                los pedidos asociados a cada uno.
            </p>

        </a>


        <!-- INCIDENCIAS -->

        <a
            href="incidencias.php"
            class="panel-card"
        >

            <div class="panel-icono">
                IN
            </div>

            <h3>
                Incidencias
            </h3>

            <p>
                Gestionar consultas y solicitudes
                de atención postventa relacionadas
                con los pedidos realizados.
            </p>

        </a>


        <!-- EVENTOS -->

        <a
            href="eventos_negocio.php"
            class="panel-card"
        >

            <div class="panel-icono">
                EV
            </div>

            <div class="contador-card">

                <?php
                echo $total_eventos;
                ?>

            </div>

            <h3>
                Eventos de negocio
            </h3>

            <p>
                Consultar la trazabilidad del canal:
                visualizaciones, productos añadidos,
                checkout, pedidos y pagos simulados.
            </p>

        </a>

    </section>


    <!-- =====================================================
         NOTA
    ====================================================== -->

    <div class="nota-panel">

        <strong>
            Entorno académico:
        </strong>

        los pedidos, pagos y cifras económicas mostrados
        en este panel corresponden exclusivamente
        al prototipo de PreyNet.

    </div>

</main>


</body>

</html>