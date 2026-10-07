<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/*
|--------------------------------------------------------------------------
| FUNCIONES AUXILIARES
|--------------------------------------------------------------------------
*/

function nombreEstadoPedido($estado)
{
    switch ($estado) {

        case "creado":
            return "Creado";

        case "pendiente_pago":
            return "Pendiente de pago";

        case "pagado":
            return "Pagado";

        case "en_preparacion":
            return "En preparación";

        case "enviado":
            return "Enviado";

        case "cancelado":
            return "Cancelado";

        case "con_incidencia":
            return "Con incidencia";

        default:
            return ucfirst(
                str_replace("_", " ", $estado)
            );
    }
}


function claseEstadoPedido($estado)
{
    switch ($estado) {

        case "creado":
            return "estado-creado";

        case "pendiente_pago":
            return "estado-pendiente";

        case "pagado":
            return "estado-pagado";

        case "en_preparacion":
            return "estado-preparacion";

        case "enviado":
            return "estado-enviado";

        case "cancelado":
            return "estado-cancelado";

        case "con_incidencia":
            return "estado-incidencia";

        default:
            return "estado-otro";
    }
}


function nombreEstadoPago($estado)
{
    switch ($estado) {

        case "simulado_aprobado":
            return "Aprobado";

        case "pendiente":
            return "Pendiente";

        case "rechazado":
            return "Rechazado";

        default:
            return ucfirst(
                str_replace("_", " ", $estado)
            );
    }
}


/*
|--------------------------------------------------------------------------
| ACTUALIZAR ESTADO DEL PEDIDO
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["pedido_id"]) &&
    isset($_POST["estado"])
) {

    $pedido_id =
        intval($_POST["pedido_id"]);

    $nuevo_estado =
        trim($_POST["estado"]);


    $estados_permitidos = [
        "creado",
        "pendiente_pago",
        "pagado",
        "en_preparacion",
        "enviado",
        "cancelado",
        "con_incidencia"
    ];


    if (
        $pedido_id > 0 &&
        in_array(
            $nuevo_estado,
            $estados_permitidos,
            true
        )
    ) {

        $sql_actualizar =
            "UPDATE pedidos
             SET estado = ?
             WHERE id = ?";


        $stmt_actualizar =
            $conexion->prepare(
                $sql_actualizar
            );


        $stmt_actualizar->bind_param(
            "si",
            $nuevo_estado,
            $pedido_id
        );


        $stmt_actualizar->execute();

        $stmt_actualizar->close();
    }


    header(
        "Location: pedidos.php?ver=" .
        $pedido_id .
        "#detalle-pedido"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| PEDIDO SELECCIONADO
|--------------------------------------------------------------------------
*/

$pedido_seleccionado = null;

$lineas_pedido = [];

$pago_pedido = null;


if (isset($_GET["ver"])) {

    $pedido_id =
        intval($_GET["ver"]);


    if ($pedido_id > 0) {


        /*
        |--------------------------------------------------------------------------
        | DATOS DEL PEDIDO Y CLIENTE
        |--------------------------------------------------------------------------
        */

        $sql_detalle =
            "SELECT

                p.id,
                p.codigo,
                p.subtotal,
                p.descuento,
                p.envio,
                p.iva,
                p.total,
                p.estado,
                p.fecha_creacion,
                p.fecha_actualizacion,

                c.id AS cliente_id,
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


        $stmt_detalle =
            $conexion->prepare(
                $sql_detalle
            );


        $stmt_detalle->bind_param(
            "i",
            $pedido_id
        );


        $stmt_detalle->execute();


        $resultado_detalle =
            $stmt_detalle->get_result();


        if (
            $resultado_detalle->num_rows === 1
        ) {

            $pedido_seleccionado =
                $resultado_detalle->fetch_assoc();

        }


        $stmt_detalle->close();


        /*
        |--------------------------------------------------------------------------
        | LÍNEAS DEL PEDIDO
        |--------------------------------------------------------------------------
        */

        if ($pedido_seleccionado !== null) {

            $sql_lineas =
                "SELECT

                    producto_id,
                    nombre_producto,
                    cantidad,
                    precio_unitario,
                    iva_porcentaje,
                    total_linea

                 FROM lineas_pedido

                 WHERE pedido_id = ?

                 ORDER BY id ASC";


            $stmt_lineas =
                $conexion->prepare(
                    $sql_lineas
                );


            $stmt_lineas->bind_param(
                "i",
                $pedido_id
            );


            $stmt_lineas->execute();


            $resultado_lineas =
                $stmt_lineas->get_result();


            while (
                $linea =
                $resultado_lineas->fetch_assoc()
            ) {

                $lineas_pedido[] =
                    $linea;

            }


            $stmt_lineas->close();


            /*
            |--------------------------------------------------------------------------
            | PAGO
            |--------------------------------------------------------------------------
            */

            $sql_pago =
                "SELECT

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
                $conexion->prepare(
                    $sql_pago
                );


            $stmt_pago->bind_param(
                "i",
                $pedido_id
            );


            $stmt_pago->execute();


            $resultado_pago =
                $stmt_pago->get_result();


            if (
                $resultado_pago->num_rows === 1
            ) {

                $pago_pedido =
                    $resultado_pago->fetch_assoc();

            }


            $stmt_pago->close();

        }

    }

}


/*
|--------------------------------------------------------------------------
| LISTADO DE PEDIDOS
|--------------------------------------------------------------------------
*/

$sql_pedidos =
    "SELECT

        p.id,
        p.codigo,
        p.total,
        p.estado,
        p.fecha_creacion,

        c.nombre,
        c.apellidos,
        c.email

     FROM pedidos p

     INNER JOIN clientes c
        ON p.cliente_id = c.id

     ORDER BY p.id DESC";


$resultado_pedidos =
    $conexion->query(
        $sql_pedidos
    );


/*
|--------------------------------------------------------------------------
| MÉTODO DE PAGO AMIGABLE
|--------------------------------------------------------------------------
*/

$nombre_metodo_pago = "";


if ($pago_pedido !== null) {

    switch ($pago_pedido["metodo"]) {


        case "tarjeta_simulada":

            $nombre_metodo_pago =
                "Tarjeta simulada";

            break;


        case "paypal_simulado":

            $nombre_metodo_pago =
                "PayPal simulado";

            break;


        case "transferencia_simulada":

            $nombre_metodo_pago =
                "Transferencia simulada";

            break;


        default:

            $nombre_metodo_pago =
                $pago_pedido["metodo"];

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
        Pedidos | Administración PreyNet
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
            gap: 12px;
        }


        .header-acciones a {
            padding: 12px 22px;

            border-radius: 40px;

            font-size: 14px;
            font-weight: 900;
        }


        .boton-panel {
            background-color: white;
            color: #102542;
        }


        .boton-salir {
            background-color: #f36b21;
            color: white;
        }


        /* =========================================================
           CONTENIDO
        ========================================================= */

        .contenedor {
            max-width: 1300px;

            margin: 0 auto;

            padding: 60px 35px 90px;
        }


        .titulo {
            margin-bottom: 35px;
        }


        .titulo span {
            color: #f36b21;

            font-size: 13px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 2px;
        }


        .titulo h2 {
            margin-top: 10px;

            font-size: 44px;
        }


        .titulo p {
            margin-top: 10px;

            color: #647689;

            font-size: 16px;
        }


        /* =========================================================
           TABLA
        ========================================================= */

        .tabla-contenedor {
            overflow-x: auto;

            background-color: white;

            border:
                1px solid #dbe4ee;

            border-radius: 22px;

            box-shadow:
                0 15px 35px
                rgba(16, 37, 66, 0.07);
        }


        table {
            width: 100%;

            border-collapse: collapse;
        }


        thead {
            background-color: #102542;
            color: white;
        }


        th {
            padding: 18px 20px;

            text-align: left;

            font-size: 13px;
            font-weight: 900;
        }


        td {
            padding: 18px 20px;

            border-bottom:
                1px solid #e3e9ef;

            font-size: 14px;

            vertical-align: middle;
        }


        tbody tr:last-child td {
            border-bottom: none;
        }


        tbody tr:hover {
            background-color: #f9fbfd;
        }


        .codigo {
            font-weight: 900;
        }


        .cliente-email {
            margin-top: 4px;

            color: #8191a1;

            font-size: 12px;
        }


        /* =========================================================
           ESTADOS
        ========================================================= */

        .estado {
            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
        }


        .estado-creado {
            background-color: #edf1f5;
            color: #566879;
        }


        .estado-pendiente {
            background-color: #fff2df;
            color: #aa6415;
        }


        .estado-pagado {
            background-color: #e3f7eb;
            color: #177c42;
        }


        .estado-preparacion {
            background-color: #e8efff;
            color: #315bb5;
        }


        .estado-enviado {
            background-color: #e2f5f4;
            color: #11736f;
        }


        .estado-cancelado {
            background-color: #fde8e8;
            color: #b52b2b;
        }


        .estado-incidencia {
            background-color: #fff0e8;
            color: #bd531e;
        }


        .estado-otro {
            background-color: #edf1f5;
            color: #566879;
        }


        /* =========================================================
           BOTÓN VER
        ========================================================= */

        .boton-ver {
            display: inline-block;

            padding: 10px 17px;

            background-color: #102542;
            color: white;

            border-radius: 25px;

            font-size: 12px;
            font-weight: 900;
        }


        .boton-ver:hover {
            background-color: #f36b21;
        }


        /* =========================================================
           SIN PEDIDOS
        ========================================================= */

        .sin-pedidos {
            padding: 55px;

            text-align: center;

            color: #647689;
        }


        /* =========================================================
           DETALLE
        ========================================================= */

        .detalle {
            margin-top: 45px;

            scroll-margin-top: 30px;
        }


        .detalle-cabecera {
            margin-bottom: 22px;

            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;
        }


        .detalle-cabecera h2 {
            font-size: 30px;
        }


        .cerrar-detalle {
            padding: 10px 18px;

            background-color: #e5ebf1;
            color: #102542;

            border-radius: 25px;

            font-size: 12px;
            font-weight: 900;
        }


        /* =========================================================
           GRID DETALLE
        ========================================================= */

        .detalle-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 1.5fr)
                minmax(300px, 1fr);

            gap: 25px;
        }


        .bloque {
            padding: 28px;

            background-color: white;

            border:
                1px solid #dbe4ee;

            border-radius: 20px;

            box-shadow:
                0 12px 30px
                rgba(16, 37, 66, 0.06);
        }


        .bloque h3 {
            margin-bottom: 22px;

            font-size: 21px;
        }


        /* =========================================================
           PRODUCTOS DEL PEDIDO
        ========================================================= */

        .linea-producto {
            padding: 15px 0;

            border-bottom:
                1px solid #e4eaf0;
        }


        .linea-producto:last-child {
            border-bottom: none;
        }


        .linea-superior {
            display: flex;
            justify-content: space-between;

            gap: 20px;
        }


        .linea-superior strong {
            font-size: 14px;
        }


        .linea-detalle {
            margin-top: 6px;

            color: #77889a;

            font-size: 12px;
        }


        /* =========================================================
           CLIENTE
        ========================================================= */

        .datos-cliente {
            margin-top: 30px;

            padding-top: 25px;

            border-top:
                1px solid #e4eaf0;
        }


        .datos-cliente h4 {
            margin-bottom: 12px;

            font-size: 16px;
        }


        .datos-cliente p {
            margin-bottom: 6px;

            color: #607386;

            font-size: 13px;
            line-height: 1.5;
        }


        /* =========================================================
           RESUMEN
        ========================================================= */

        .resumen {
            background-color: #071827;
            color: white;
        }


        .fila {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 14px;

            color: #c4cfda;

            font-size: 13px;
        }


        .fila strong {
            color: white;
        }


        .total {
            display: flex;
            justify-content: space-between;

            gap: 20px;

            margin-top: 22px;
            padding-top: 20px;

            border-top:
                1px solid
                rgba(255, 255, 255, 0.16);

            font-size: 24px;
            font-weight: 900;
        }


        /* =========================================================
           ESTADO DEL PEDIDO
        ========================================================= */

        .gestion-estado {
            margin-top: 28px;
            padding-top: 23px;

            border-top:
                1px solid
                rgba(255, 255, 255, 0.16);
        }


        .gestion-estado h4 {
            margin-bottom: 14px;

            color: white;

            font-size: 16px;
        }


        .gestion-estado label {
            display: block;

            margin-bottom: 8px;

            color: #aebbc8;

            font-size: 12px;
            font-weight: 900;
        }


        .gestion-estado select {
            width: 100%;

            padding: 13px 14px;

            border: none;
            border-radius: 10px;

            background-color: white;
            color: #102542;

            font-size: 14px;

            outline: none;
        }


        .boton-actualizar {
            width: 100%;

            margin-top: 13px;

            padding: 14px;

            border: none;
            border-radius: 30px;

            background-color: #f36b21;
            color: white;

            font-size: 14px;
            font-weight: 900;

            cursor: pointer;
        }


        .boton-actualizar:hover {
            background-color: white;
            color: #102542;
        }


        .ultima-actualizacion {
            margin-top: 12px;

            color: #8fa0af;

            font-size: 11px;
        }


        /* =========================================================
           PAGO
        ========================================================= */

        .pago {
            margin-top: 28px;
            padding-top: 23px;

            border-top:
                1px solid
                rgba(255, 255, 255, 0.16);
        }


        .pago h4 {
            margin-bottom: 12px;

            font-size: 16px;
        }


        .pago p {
            margin-bottom: 7px;

            color: #aebbc8;

            font-size: 12px;
            line-height: 1.45;
        }


        .pago strong {
            color: white;
        }


        .referencia {
            color: white !important;

            font-weight: 900;

            word-break: break-word;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 850px) {

            .detalle-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 700px) {

            .admin-header {
                padding: 20px 22px;

                flex-direction: column;
                align-items: flex-start;
            }


            .contenedor {
                padding: 45px 22px;
            }


            .titulo h2 {
                font-size: 35px;
            }

        }

    </style>

</head>


<body>


<header class="admin-header">


    <h1>
        Pedidos PreyNet
    </h1>


    <div class="header-acciones">


        <a
            href="panel.php"
            class="boton-panel"
        >
            Panel
        </a>


        <a
            href="../acciones/logout.php"
            class="boton-salir"
        >
            Cerrar sesión
        </a>


    </div>


</header>


<main class="contenedor">


    <!-- =========================================================
         TÍTULO
    ========================================================== -->

    <section class="titulo">


        <span>
            Gestión comercial
        </span>


        <h2>
            Pedidos
        </h2>


        <p>
            Consulta y gestiona los pedidos realizados
            a través de la tienda online.
        </p>


    </section>


    <!-- =========================================================
         LISTADO
    ========================================================== -->

    <section class="tabla-contenedor">


        <?php
        if (
            $resultado_pedidos &&
            $resultado_pedidos->num_rows > 0
        ):
        ?>


            <table>


                <thead>


                    <tr>


                        <th>
                            Pedido
                        </th>


                        <th>
                            Cliente
                        </th>


                        <th>
                            Fecha
                        </th>


                        <th>
                            Estado
                        </th>


                        <th>
                            Total
                        </th>


                        <th>
                            Acción
                        </th>


                    </tr>


                </thead>


                <tbody>


                    <?php
                    while (
                        $pedido =
                        $resultado_pedidos->fetch_assoc()
                    ):
                    ?>


                        <tr>


                            <td class="codigo">

                                <?php
                                echo htmlspecialchars(
                                    $pedido["codigo"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $pedido["nombre"] .
                                    " " .
                                    $pedido["apellidos"]
                                );
                                ?>


                                <div class="cliente-email">

                                    <?php
                                    echo htmlspecialchars(
                                        $pedido["email"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d/m/Y H:i",
                                    strtotime(
                                        $pedido["fecha_creacion"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <span
                                    class="
                                        estado
                                        <?php
                                        echo htmlspecialchars(
                                            claseEstadoPedido(
                                                $pedido["estado"]
                                            )
                                        );
                                        ?>
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        nombreEstadoPedido(
                                            $pedido["estado"]
                                        )
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <strong>

                                    <?php
                                    echo number_format(
                                        $pedido["total"],
                                        2,
                                        ",",
                                        "."
                                    );
                                    ?>

                                    €

                                </strong>

                            </td>


                            <td>

                                <a
                                    href="pedidos.php?ver=<?php echo intval($pedido["id"]); ?>#detalle-pedido"
                                    class="boton-ver"
                                >
                                    Ver detalle
                                </a>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="sin-pedidos">

                Todavía no hay pedidos registrados.

            </div>


        <?php endif; ?>


    </section>


    <!-- =========================================================
         DETALLE DEL PEDIDO
    ========================================================== -->

    <?php if ($pedido_seleccionado !== null): ?>


        <section
            class="detalle"
            id="detalle-pedido"
        >


            <div class="detalle-cabecera">


                <h2>

                    Pedido

                    <?php
                    echo htmlspecialchars(
                        $pedido_seleccionado["codigo"]
                    );
                    ?>

                </h2>


                <a
                    href="pedidos.php"
                    class="cerrar-detalle"
                >
                    Cerrar detalle
                </a>


            </div>


            <div class="detalle-grid">


                <!-- =================================================
                     PRODUCTOS Y CLIENTE
                ================================================== -->

                <div class="bloque">


                    <h3>
                        Productos
                    </h3>


                    <?php foreach ($lineas_pedido as $linea): ?>


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


                            <div class="linea-detalle">

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


                        <h4>
                            Cliente
                        </h4>


                        <p>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $pedido_seleccionado["nombre"] .
                                    " " .
                                    $pedido_seleccionado["apellidos"]
                                );
                                ?>

                            </strong>

                        </p>


                        <p>

                            <?php
                            echo htmlspecialchars(
                                $pedido_seleccionado["email"]
                            );
                            ?>

                        </p>


                        <?php
                        if (
                            !empty(
                                $pedido_seleccionado["telefono"]
                            )
                        ):
                        ?>

                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $pedido_seleccionado["telefono"]
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                        <p>

                            <?php
                            echo htmlspecialchars(
                                $pedido_seleccionado["direccion"]
                            );
                            ?>

                        </p>


                        <p>

                            <?php
                            echo htmlspecialchars(
                                $pedido_seleccionado["codigo_postal"] .
                                " " .
                                $pedido_seleccionado["ciudad"] .
                                ", " .
                                $pedido_seleccionado["provincia"]
                            );
                            ?>

                        </p>


                    </div>


                </div>


                <!-- =================================================
                     RESUMEN
                ================================================== -->

                <aside class="bloque resumen">


                    <h3>
                        Resumen económico
                    </h3>


                    <div class="fila">

                        <span>
                            Subtotal
                        </span>

                        <strong>

                            <?php
                            echo number_format(
                                $pedido_seleccionado["subtotal"],
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
                                $pedido_seleccionado["iva"],
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
                                floatval(
                                    $pedido_seleccionado["envio"]
                                ) == 0
                            ) {

                                echo "Gratis";

                            } else {

                                echo number_format(
                                    $pedido_seleccionado["envio"],
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
                                $pedido_seleccionado["descuento"],
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
                                $pedido_seleccionado["total"],
                                2,
                                ",",
                                "."
                            );
                            ?>

                            €

                        </span>

                    </div>


                    <!-- =================================================
                         ESTADO DEL PEDIDO
                    ================================================== -->

                    <div class="gestion-estado">


                        <h4>
                            Gestión del pedido
                        </h4>


                        <form method="POST">


                            <input
                                type="hidden"
                                name="pedido_id"
                                value="<?php echo intval($pedido_seleccionado["id"]); ?>"
                            >


                            <label for="estado">
                                Estado del pedido
                            </label>


                            <select
                                id="estado"
                                name="estado"
                            >


                                <option
                                    value="creado"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "creado"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Creado
                                </option>


                                <option
                                    value="pendiente_pago"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "pendiente_pago"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Pendiente de pago
                                </option>


                                <option
                                    value="pagado"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "pagado"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Pagado
                                </option>


                                <option
                                    value="en_preparacion"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "en_preparacion"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    En preparación
                                </option>


                                <option
                                    value="enviado"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "enviado"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Enviado
                                </option>


                                <option
                                    value="con_incidencia"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "con_incidencia"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Con incidencia
                                </option>


                                <option
                                    value="cancelado"
                                    <?php
                                    if (
                                        $pedido_seleccionado["estado"] === "cancelado"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Cancelado
                                </option>


                            </select>


                            <button
                                type="submit"
                                class="boton-actualizar"
                            >
                                Actualizar estado
                            </button>


                        </form>


                        <div class="ultima-actualizacion">

                            Última actualización:

                            <?php
                            echo date(
                                "d/m/Y H:i",
                                strtotime(
                                    $pedido_seleccionado["fecha_actualizacion"]
                                )
                            );
                            ?>

                        </div>


                    </div>


                    <!-- =================================================
                         PAGO
                    ================================================== -->

                    <?php if ($pago_pedido !== null): ?>


                        <div class="pago">


                            <h4>
                                Pago
                            </h4>


                            <p>

                                Método:

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $nombre_metodo_pago
                                    );
                                    ?>

                                </strong>

                            </p>


                            <p>

                                Estado:

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        nombreEstadoPago(
                                            $pago_pedido["estado"]
                                        )
                                    );
                                    ?>

                                </strong>

                            </p>


                            <p>
                                Referencia:
                            </p>


                            <p class="referencia">

                                <?php
                                echo htmlspecialchars(
                                    $pago_pedido["referencia"]
                                );
                                ?>

                            </p>


                        </div>


                    <?php endif; ?>


                </aside>


            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>