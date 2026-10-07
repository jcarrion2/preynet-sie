<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";


/*
|--------------------------------------------------------------------------
| CLIENTE SELECCIONADO
|--------------------------------------------------------------------------
*/

$cliente_seleccionado = null;
$pedidos_cliente = [];


if (isset($_GET["ver"])) {

    $cliente_id = intval($_GET["ver"]);

    if ($cliente_id > 0) {

        /*
        |--------------------------------------------------------------------------
        | DATOS DEL CLIENTE
        |--------------------------------------------------------------------------
        */

        $sql_cliente = "SELECT
                            id,
                            nombre,
                            apellidos,
                            email,
                            telefono,
                            direccion,
                            codigo_postal,
                            ciudad,
                            provincia,
                            fecha_creacion
                        FROM clientes
                        WHERE id = ?";

        $stmt_cliente =
            $conexion->prepare($sql_cliente);

        $stmt_cliente->bind_param(
            "i",
            $cliente_id
        );

        $stmt_cliente->execute();

        $resultado_cliente =
            $stmt_cliente->get_result();


        if ($resultado_cliente->num_rows === 1) {

            $cliente_seleccionado =
                $resultado_cliente->fetch_assoc();

        }

        $stmt_cliente->close();


        /*
        |--------------------------------------------------------------------------
        | PEDIDOS DEL CLIENTE
        |--------------------------------------------------------------------------
        */

        if ($cliente_seleccionado !== null) {

            $sql_pedidos_cliente = "SELECT
                                        id,
                                        codigo,
                                        total,
                                        estado,
                                        fecha_creacion
                                    FROM pedidos
                                    WHERE cliente_id = ?
                                    ORDER BY id DESC";

            $stmt_pedidos =
                $conexion->prepare($sql_pedidos_cliente);

            $stmt_pedidos->bind_param(
                "i",
                $cliente_id
            );

            $stmt_pedidos->execute();

            $resultado_pedidos =
                $stmt_pedidos->get_result();


            while (
                $pedido =
                $resultado_pedidos->fetch_assoc()
            ) {

                $pedidos_cliente[] = $pedido;

            }

            $stmt_pedidos->close();

        }

    }

}


/*
|--------------------------------------------------------------------------
| LISTADO DE CLIENTES
|--------------------------------------------------------------------------
*/

$sql_clientes = "SELECT
                    c.id,
                    c.nombre,
                    c.apellidos,
                    c.email,
                    c.telefono,
                    c.ciudad,
                    c.provincia,
                    c.fecha_creacion,

                    COUNT(p.id) AS total_pedidos,

                    COALESCE(
                        SUM(
                            CASE
                                WHEN p.estado = 'pagado'
                                THEN p.total
                                ELSE 0
                            END
                        ),
                        0
                    ) AS total_compras

                 FROM clientes c

                 LEFT JOIN pedidos p
                    ON p.cliente_id = c.id

                 GROUP BY
                    c.id,
                    c.nombre,
                    c.apellidos,
                    c.email,
                    c.telefono,
                    c.ciudad,
                    c.provincia,
                    c.fecha_creacion

                 ORDER BY c.id DESC";


$resultado_clientes =
    $conexion->query($sql_clientes);

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
        Clientes | Administración PreyNet
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

            border: 1px solid #dbe4ee;
            border-radius: 22px;

            box-shadow:
                0 15px 35px rgba(16, 37, 66, 0.07);
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

            border-bottom: 1px solid #e3e9ef;

            font-size: 14px;

            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: #f9fbfd;
        }

        .nombre-cliente {
            font-weight: 900;
        }

        .email {
            margin-top: 4px;

            color: #8191a1;

            font-size: 12px;
        }


        /* =========================================================
           BOTÓN
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
           DETALLE
        ========================================================= */

        .detalle {
            margin-top: 45px;
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
           GRID
        ========================================================= */

        .detalle-grid {
            display: grid;

            grid-template-columns:
                minmax(300px, 1fr)
                minmax(0, 1.5fr);

            gap: 25px;
        }

        .bloque {
            padding: 28px;

            background-color: white;

            border: 1px solid #dbe4ee;
            border-radius: 20px;

            box-shadow:
                0 12px 30px rgba(16, 37, 66, 0.06);
        }

        .bloque h3 {
            margin-bottom: 22px;

            font-size: 21px;
        }


        /* =========================================================
           DATOS CLIENTE
        ========================================================= */

        .dato {
            margin-bottom: 17px;
        }

        .dato span {
            display: block;

            margin-bottom: 4px;

            color: #8493a3;

            font-size: 11px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dato strong,
        .dato p {
            color: #102542;

            font-size: 14px;
            line-height: 1.5;
        }


        /* =========================================================
           PEDIDOS CLIENTE
        ========================================================= */

        .pedido-cliente {
            padding: 17px 0;

            border-bottom: 1px solid #e4eaf0;
        }

        .pedido-cliente:first-of-type {
            padding-top: 0;
        }

        .pedido-cliente:last-child {
            border-bottom: none;
        }

        .pedido-superior {
            display: flex;
            justify-content: space-between;

            gap: 20px;
        }

        .pedido-codigo {
            font-weight: 900;
        }

        .pedido-total {
            font-weight: 900;
        }

        .pedido-info {
            margin-top: 7px;

            display: flex;
            align-items: center;

            gap: 12px;

            color: #78899a;

            font-size: 12px;
        }


        /* =========================================================
           ESTADO
        ========================================================= */

        .estado {
            display: inline-block;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
        }

        .estado-pagado {
            background-color: #e3f7eb;
            color: #177c42;
        }

        .estado-pendiente {
            background-color: #fff2df;
            color: #aa6415;
        }


        /* =========================================================
           SIN CLIENTES
        ========================================================= */

        .sin-datos {
            padding: 55px;

            text-align: center;

            color: #647689;
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
        Clientes PreyNet
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
            Clientes
        </h2>

        <p>
            Consulta los clientes registrados
            durante el proceso de compra.
        </p>

    </section>


    <!-- =========================================================
         LISTADO
    ========================================================== -->

    <section class="tabla-contenedor">


        <?php
        if (
            $resultado_clientes &&
            $resultado_clientes->num_rows > 0
        ):
        ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            Cliente
                        </th>

                        <th>
                            Localidad
                        </th>

                        <th>
                            Pedidos
                        </th>

                        <th>
                            Compras
                        </th>

                        <th>
                            Registro
                        </th>

                        <th>
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php
                    while (
                        $cliente =
                        $resultado_clientes->fetch_assoc()
                    ):
                    ?>


                        <tr>


                            <td>

                                <div class="nombre-cliente">

                                    <?php
                                    echo htmlspecialchars(
                                        $cliente["nombre"] .
                                        " " .
                                        $cliente["apellidos"]
                                    );
                                    ?>

                                </div>


                                <div class="email">

                                    <?php
                                    echo htmlspecialchars(
                                        $cliente["email"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $cliente["ciudad"] .
                                    ", " .
                                    $cliente["provincia"]
                                );
                                ?>

                            </td>


                            <td>

                                <strong>

                                    <?php
                                    echo intval(
                                        $cliente["total_pedidos"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                <strong>

                                    <?php
                                    echo number_format(
                                        $cliente["total_compras"],
                                        2,
                                        ",",
                                        "."
                                    );
                                    ?>

                                    €

                                </strong>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d/m/Y H:i",
                                    strtotime(
                                        $cliente["fecha_creacion"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <a
                                    href="clientes.php?ver=<?php echo intval($cliente["id"]); ?>"
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


            <div class="sin-datos">
                Todavía no hay clientes registrados.
            </div>


        <?php endif; ?>


    </section>


    <!-- =========================================================
         DETALLE CLIENTE
    ========================================================== -->

    <?php if ($cliente_seleccionado !== null): ?>


        <section class="detalle">


            <div class="detalle-cabecera">


                <h2>

                    <?php
                    echo htmlspecialchars(
                        $cliente_seleccionado["nombre"] .
                        " " .
                        $cliente_seleccionado["apellidos"]
                    );
                    ?>

                </h2>


                <a
                    href="clientes.php"
                    class="cerrar-detalle"
                >
                    Cerrar detalle
                </a>


            </div>


            <div class="detalle-grid">


                <!-- =================================================
                     DATOS
                ================================================== -->

                <div class="bloque">


                    <h3>
                        Datos del cliente
                    </h3>


                    <div class="dato">

                        <span>
                            Email
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $cliente_seleccionado["email"]
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="dato">

                        <span>
                            Teléfono
                        </span>

                        <p>

                            <?php
                            echo !empty(
                                $cliente_seleccionado["telefono"]
                            )
                                ? htmlspecialchars(
                                    $cliente_seleccionado["telefono"]
                                )
                                : "No indicado";
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Dirección
                        </span>

                        <p>

                            <?php
                            echo htmlspecialchars(
                                $cliente_seleccionado["direccion"]
                            );
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Localidad
                        </span>

                        <p>

                            <?php
                            echo htmlspecialchars(
                                $cliente_seleccionado["codigo_postal"] .
                                " " .
                                $cliente_seleccionado["ciudad"] .
                                ", " .
                                $cliente_seleccionado["provincia"]
                            );
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Fecha de alta
                        </span>

                        <p>

                            <?php
                            echo date(
                                "d/m/Y H:i",
                                strtotime(
                                    $cliente_seleccionado["fecha_creacion"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                </div>


                <!-- =================================================
                     PEDIDOS
                ================================================== -->

                <div class="bloque">


                    <h3>
                        Historial de pedidos
                    </h3>


                    <?php if (count($pedidos_cliente) > 0): ?>


                        <?php foreach ($pedidos_cliente as $pedido): ?>


                            <div class="pedido-cliente">


                                <div class="pedido-superior">


                                    <div class="pedido-codigo">

                                        <?php
                                        echo htmlspecialchars(
                                            $pedido["codigo"]
                                        );
                                        ?>

                                    </div>


                                    <div class="pedido-total">

                                        <?php
                                        echo number_format(
                                            $pedido["total"],
                                            2,
                                            ",",
                                            "."
                                        );
                                        ?>

                                        €

                                    </div>


                                </div>


                                <div class="pedido-info">


                                    <span>

                                        <?php
                                        echo date(
                                            "d/m/Y H:i",
                                            strtotime(
                                                $pedido["fecha_creacion"]
                                            )
                                        );
                                        ?>

                                    </span>


                                    <?php
                                    if (
                                        $pedido["estado"] === "pagado"
                                    ):
                                    ?>

                                        <span
                                            class="
                                                estado
                                                estado-pagado
                                            "
                                        >
                                            Pagado
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="
                                                estado
                                                estado-pendiente
                                            "
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $pedido["estado"]
                                            );
                                            ?>

                                        </span>

                                    <?php endif; ?>


                                    <a
                                        href="pedidos.php?ver=<?php echo intval($pedido["id"]); ?>"
                                    >
                                        Ver pedido
                                    </a>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="sin-datos">
                            Este cliente no tiene pedidos.
                        </div>


                    <?php endif; ?>


                </div>


            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>