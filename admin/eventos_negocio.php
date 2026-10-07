<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";


/*
|--------------------------------------------------------------------------
| FUNCIONES AUXILIARES
|--------------------------------------------------------------------------
*/

function nombreEvento($tipo)
{
    switch ($tipo) {

        case "product.viewed":
            return "Producto visualizado";

        case "cart.item_added":
            return "Producto añadido al carrito";

        case "cart.item_removed":
            return "Producto eliminado del carrito";

        case "cart.quantity_updated":
            return "Cantidad del carrito modificada";

        case "checkout.started":
            return "Checkout iniciado";

        case "order.created":
            return "Pedido creado";

        case "payment.simulated":
            return "Pago simulado";

        case "support.ticket_created":
            return "Incidencia creada";

        default:
            return $tipo;
    }
}


function categoriaEvento($tipo)
{
    if (
        strpos($tipo, "product.") === 0 ||
        strpos($tipo, "cart.") === 0
    ) {
        return "interaccion";
    }

    if (
        strpos($tipo, "checkout.") === 0 ||
        strpos($tipo, "order.") === 0 ||
        strpos($tipo, "payment.") === 0
    ) {
        return "conversion";
    }

    if (
        strpos($tipo, "support.") === 0
    ) {
        return "soporte";
    }

    return "otro";
}


function etiquetaCategoria($categoria)
{
    switch ($categoria) {

        case "interaccion":
            return "Interacción";

        case "conversion":
            return "Conversión";

        case "soporte":
            return "Soporte";

        default:
            return "Sistema";
    }
}


/*
|--------------------------------------------------------------------------
| FILTRO
|--------------------------------------------------------------------------
*/

$filtro = trim($_GET["tipo"] ?? "todos");

$filtros_permitidos = [
    "todos",
    "interaccion",
    "conversion",
    "soporte"
];

if (
    !in_array(
        $filtro,
        $filtros_permitidos,
        true
    )
) {
    $filtro = "todos";
}


/*
|--------------------------------------------------------------------------
| INDICADORES
|--------------------------------------------------------------------------
*/

$total_eventos = 0;
$total_interacciones = 0;
$total_conversiones = 0;
$total_soporte = 0;


/* TOTAL */

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM eventos"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_eventos = intval($fila["total"]);
}


/* INTERACCIONES */

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM eventos
     WHERE tipo LIKE 'product.%'
        OR tipo LIKE 'cart.%'"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_interacciones = intval($fila["total"]);
}


/* CONVERSIONES */

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM eventos
     WHERE tipo LIKE 'checkout.%'
        OR tipo LIKE 'order.%'
        OR tipo LIKE 'payment.%'"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_conversiones = intval($fila["total"]);
}


/* SOPORTE */

$resultado = $conexion->query(
    "SELECT COUNT(*) AS total
     FROM eventos
     WHERE tipo LIKE 'support.%'"
);

if ($resultado) {
    $fila = $resultado->fetch_assoc();
    $total_soporte = intval($fila["total"]);
}


/*
|--------------------------------------------------------------------------
| EVENTO SELECCIONADO
|--------------------------------------------------------------------------
*/

$evento_seleccionado = null;

if (isset($_GET["ver"])) {

    $evento_id = intval($_GET["ver"]);

    if ($evento_id > 0) {

        $sql_detalle =
            "SELECT
                e.id,
                e.tipo,
                e.session_id,
                e.cliente_id,
                e.pedido_id,
                e.metadata,
                e.fecha_creacion,

                c.nombre,
                c.apellidos,
                c.email,

                p.codigo AS codigo_pedido

             FROM eventos e

             LEFT JOIN clientes c
                ON e.cliente_id = c.id

             LEFT JOIN pedidos p
                ON e.pedido_id = p.id

             WHERE e.id = ?";

        $stmt = $conexion->prepare($sql_detalle);

        $stmt->bind_param(
            "i",
            $evento_id
        );

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $evento_seleccionado =
                $resultado->fetch_assoc();
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| LISTADO DE EVENTOS
|--------------------------------------------------------------------------
*/

$where = "";

if ($filtro === "interaccion") {

    $where =
        "WHERE
            e.tipo LIKE 'product.%'
            OR e.tipo LIKE 'cart.%'";

} elseif ($filtro === "conversion") {

    $where =
        "WHERE
            e.tipo LIKE 'checkout.%'
            OR e.tipo LIKE 'order.%'
            OR e.tipo LIKE 'payment.%'";

} elseif ($filtro === "soporte") {

    $where =
        "WHERE
            e.tipo LIKE 'support.%'";
}


$sql_eventos =
    "SELECT
        e.id,
        e.tipo,
        e.session_id,
        e.cliente_id,
        e.pedido_id,
        e.metadata,
        e.fecha_creacion,

        c.nombre,
        c.apellidos,

        p.codigo AS codigo_pedido

     FROM eventos e

     LEFT JOIN clientes c
        ON e.cliente_id = c.id

     LEFT JOIN pedidos p
        ON e.pedido_id = p.id

     $where

     ORDER BY e.id DESC

     LIMIT 200";


$resultado_eventos =
    $conexion->query($sql_eventos);

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
        Eventos de negocio | Administración PreyNet
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
            max-width: 1350px;

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
            max-width: 800px;

            margin-top: 10px;

            color: #647689;

            font-size: 16px;
            line-height: 1.6;
        }


        /* =========================================================
           INDICADORES
        ========================================================= */

        .indicadores {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 38px;
        }

        .indicador {
            padding: 24px;

            background-color: white;

            border:
                1px solid #dbe4ee;

            border-radius: 18px;

            box-shadow:
                0 10px 25px
                rgba(16, 37, 66, 0.05);
        }

        .indicador span {
            display: block;

            color: #76889a;

            font-size: 11px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .indicador strong {
            display: block;

            margin-top: 8px;

            color: #102542;

            font-size: 31px;
        }

        .indicador-destacado strong {
            color: #f36b21;
        }


        /* =========================================================
           FILTROS
        ========================================================= */

        .filtros {
            display: flex;

            gap: 10px;

            margin-bottom: 22px;

            flex-wrap: wrap;
        }

        .filtro {
            padding: 10px 17px;

            background-color: white;
            color: #102542;

            border:
                1px solid #d8e1ea;

            border-radius: 25px;

            font-size: 12px;
            font-weight: 900;
        }

        .filtro:hover {
            border-color: #f36b21;
        }

        .filtro-activo {
            background-color: #102542;
            color: white;

            border-color: #102542;
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
            padding: 17px 18px;

            text-align: left;

            font-size: 12px;
            font-weight: 900;
        }

        td {
            padding: 17px 18px;

            border-bottom:
                1px solid #e3e9ef;

            font-size: 13px;

            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background-color: #f9fbfd;
        }

        .evento-nombre {
            font-weight: 900;
        }

        .evento-tipo {
            margin-top: 4px;

            color: #8a98a7;

            font-family:
                Consolas,
                monospace;

            font-size: 11px;
        }

        .dato-secundario {
            margin-top: 4px;

            color: #8695a5;

            font-size: 11px;
        }


        /* =========================================================
           CATEGORÍAS
        ========================================================= */

        .categoria {
            display: inline-block;

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
        }

        .categoria-interaccion {
            background-color: #edf2ff;
            color: #405ea8;
        }

        .categoria-conversion {
            background-color: #e3f7eb;
            color: #177c42;
        }

        .categoria-soporte {
            background-color: #fff0e8;
            color: #bd531e;
        }

        .categoria-otro {
            background-color: #edf1f5;
            color: #657585;
        }


        /* =========================================================
           BOTÓN VER
        ========================================================= */

        .boton-ver {
            display: inline-block;

            padding: 9px 15px;

            background-color: #102542;
            color: white;

            border-radius: 23px;

            font-size: 11px;
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

            scroll-margin-top: 30px;
        }

        .detalle-cabecera {
            margin-bottom: 22px;

            display: flex;
            align-items: center;
            justify-content: space-between;

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
           DATOS DEL EVENTO
        ========================================================= */

        .dato {
            margin-bottom: 19px;
        }

        .dato span {
            display: block;

            margin-bottom: 5px;

            color: #8493a3;

            font-size: 11px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dato p,
        .dato strong {
            color: #102542;

            font-size: 14px;
            line-height: 1.55;
        }

        .session {
            word-break: break-all;
        }


        /* =========================================================
           METADATA
        ========================================================= */

        .metadata {
            background-color: #071827;
            color: white;
        }

        .metadata h3 {
            color: white;
        }

        .metadata-vacia {
            color: #aab7c4;

            font-size: 14px;
        }

        .metadata-fila {
            padding: 12px 0;

            border-bottom:
                1px solid
                rgba(255,255,255,0.12);
        }

        .metadata-fila:last-child {
            border-bottom: none;
        }

        .metadata-clave {
            display: block;

            margin-bottom: 5px;

            color: #9fadb9;

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metadata-valor {
            color: white;

            font-size: 13px;
            line-height: 1.5;

            word-break: break-word;
        }


        /* =========================================================
           SIN DATOS
        ========================================================= */

        .sin-datos {
            padding: 55px;

            color: #647689;

            text-align: center;
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1000px) {

            .indicadores {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

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

            .indicadores {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<header class="admin-header">

    <h1>
        Eventos de negocio
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
            Analítica y trazabilidad
        </span>

        <h2>
            Eventos de negocio
        </h2>

        <p>
            Consulta las principales acciones registradas
            durante la navegación, el proceso de compra
            y la atención postventa de PreyNet.
        </p>

    </section>


    <!-- =========================================================
         INDICADORES
    ========================================================== -->

    <section class="indicadores">


        <div class="indicador">

            <span>
                Total eventos
            </span>

            <strong>
                <?php echo $total_eventos; ?>
            </strong>

        </div>


        <div class="indicador">

            <span>
                Interacciones
            </span>

            <strong>
                <?php echo $total_interacciones; ?>
            </strong>

        </div>


        <div class="indicador indicador-destacado">

            <span>
                Conversión
            </span>

            <strong>
                <?php echo $total_conversiones; ?>
            </strong>

        </div>


        <div class="indicador">

            <span>
                Soporte
            </span>

            <strong>
                <?php echo $total_soporte; ?>
            </strong>

        </div>


    </section>


    <!-- =========================================================
         FILTROS
    ========================================================== -->

    <div class="filtros">


        <a
            href="eventos_negocio.php"
            class="
                filtro
                <?php
                echo
                    $filtro === "todos"
                    ? "filtro-activo"
                    : "";
                ?>
            "
        >
            Todos
        </a>


        <a
            href="eventos_negocio.php?tipo=interaccion"
            class="
                filtro
                <?php
                echo
                    $filtro === "interaccion"
                    ? "filtro-activo"
                    : "";
                ?>
            "
        >
            Interacciones
        </a>


        <a
            href="eventos_negocio.php?tipo=conversion"
            class="
                filtro
                <?php
                echo
                    $filtro === "conversion"
                    ? "filtro-activo"
                    : "";
                ?>
            "
        >
            Conversión
        </a>


        <a
            href="eventos_negocio.php?tipo=soporte"
            class="
                filtro
                <?php
                echo
                    $filtro === "soporte"
                    ? "filtro-activo"
                    : "";
                ?>
            "
        >
            Soporte
        </a>


    </div>


    <!-- =========================================================
         TABLA
    ========================================================== -->

    <section class="tabla-contenedor">


        <?php
        if (
            $resultado_eventos &&
            $resultado_eventos->num_rows > 0
        ):
        ?>


            <table>


                <thead>

                    <tr>

                        <th>
                            Evento
                        </th>

                        <th>
                            Categoría
                        </th>

                        <th>
                            Cliente
                        </th>

                        <th>
                            Pedido
                        </th>

                        <th>
                            Fecha
                        </th>

                        <th>
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php
                while (
                    $evento =
                    $resultado_eventos->fetch_assoc()
                ):

                    $categoria =
                        categoriaEvento(
                            $evento["tipo"]
                        );
                ?>


                    <tr>


                        <td>

                            <div class="evento-nombre">

                                <?php
                                echo htmlspecialchars(
                                    nombreEvento(
                                        $evento["tipo"]
                                    )
                                );
                                ?>

                            </div>


                            <div class="evento-tipo">

                                <?php
                                echo htmlspecialchars(
                                    $evento["tipo"]
                                );
                                ?>

                            </div>

                        </td>


                        <td>

                            <span
                                class="
                                    categoria
                                    categoria-<?php echo htmlspecialchars($categoria); ?>
                                "
                            >

                                <?php
                                echo htmlspecialchars(
                                    etiquetaCategoria(
                                        $categoria
                                    )
                                );
                                ?>

                            </span>

                        </td>


                        <td>

                            <?php
                            if (
                                !empty(
                                    $evento["nombre"]
                                )
                            ):
                            ?>

                                <?php
                                echo htmlspecialchars(
                                    $evento["nombre"] .
                                    " " .
                                    $evento["apellidos"]
                                );
                                ?>

                            <?php else: ?>

                                <span class="dato-secundario">
                                    Sesión anónima
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php
                            if (
                                !empty(
                                    $evento["codigo_pedido"]
                                )
                            ):
                            ?>

                                <?php
                                echo htmlspecialchars(
                                    $evento["codigo_pedido"]
                                );
                                ?>

                            <?php else: ?>

                                <span class="dato-secundario">
                                    —
                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php
                            echo date(
                                "d/m/Y H:i:s",
                                strtotime(
                                    $evento["fecha_creacion"]
                                )
                            );
                            ?>

                        </td>


                        <td>

                            <a
                                href="eventos_negocio.php?<?php
                                    if ($filtro !== "todos") {
                                        echo "tipo=" . urlencode($filtro) . "&";
                                    }
                                ?>ver=<?php echo intval($evento["id"]); ?>#detalle-evento"
                                class="boton-ver"
                            >
                                Ver
                            </a>

                        </td>


                    </tr>


                <?php endwhile; ?>


                </tbody>


            </table>


        <?php else: ?>


            <div class="sin-datos">
                No hay eventos para este filtro.
            </div>


        <?php endif; ?>


    </section>


    <!-- =========================================================
         DETALLE
    ========================================================== -->

    <?php if ($evento_seleccionado !== null): ?>


        <?php

        $metadata_array = [];

        if (
            !empty(
                $evento_seleccionado["metadata"]
            )
        ) {

            $metadata_decodificada =
                json_decode(
                    $evento_seleccionado["metadata"],
                    true
                );


            if (
                is_array(
                    $metadata_decodificada
                )
            ) {

                $metadata_array =
                    $metadata_decodificada;

            }

        }

        ?>


        <section
            class="detalle"
            id="detalle-evento"
        >


            <div class="detalle-cabecera">


                <h2>

                    <?php
                    echo htmlspecialchars(
                        nombreEvento(
                            $evento_seleccionado["tipo"]
                        )
                    );
                    ?>

                </h2>


                <a
                    href="eventos_negocio.php<?php
                        echo
                            $filtro !== "todos"
                            ? "?tipo=" . urlencode($filtro)
                            : "";
                    ?>"
                    class="cerrar-detalle"
                >
                    Cerrar detalle
                </a>


            </div>


            <div class="detalle-grid">


                <!-- DATOS -->

                <div class="bloque">


                    <h3>
                        Información del evento
                    </h3>


                    <div class="dato">

                        <span>
                            Tipo técnico
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $evento_seleccionado["tipo"]
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="dato">

                        <span>
                            Categoría
                        </span>

                        <p>

                            <?php

                            $categoria_detalle =
                                categoriaEvento(
                                    $evento_seleccionado["tipo"]
                                );

                            echo htmlspecialchars(
                                etiquetaCategoria(
                                    $categoria_detalle
                                )
                            );

                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Fecha
                        </span>

                        <p>

                            <?php
                            echo date(
                                "d/m/Y H:i:s",
                                strtotime(
                                    $evento_seleccionado["fecha_creacion"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Cliente
                        </span>

                        <p>

                            <?php

                            if (
                                !empty(
                                    $evento_seleccionado["nombre"]
                                )
                            ) {

                                echo htmlspecialchars(
                                    $evento_seleccionado["nombre"] .
                                    " " .
                                    $evento_seleccionado["apellidos"]
                                );

                                if (
                                    !empty(
                                        $evento_seleccionado["email"]
                                    )
                                ) {

                                    echo "<br>";

                                    echo htmlspecialchars(
                                        $evento_seleccionado["email"]
                                    );

                                }

                            } else {

                                echo "Sesión anónima";

                            }

                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Pedido
                        </span>

                        <p>

                            <?php

                            if (
                                !empty(
                                    $evento_seleccionado["codigo_pedido"]
                                )
                            ) {

                                echo htmlspecialchars(
                                    $evento_seleccionado["codigo_pedido"]
                                );

                            } else {

                                echo "Sin pedido asociado";

                            }

                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Sesión
                        </span>

                        <p class="session">

                            <?php
                            echo htmlspecialchars(
                                $evento_seleccionado["session_id"]
                            );
                            ?>

                        </p>

                    </div>


                </div>


                <!-- METADATA -->

                <aside class="bloque metadata">


                    <h3>
                        Datos asociados
                    </h3>


                    <?php
                    if (
                        count(
                            $metadata_array
                        ) > 0
                    ):
                    ?>


                        <?php
                        foreach (
                            $metadata_array
                            as $clave => $valor
                        ):
                        ?>


                            <div class="metadata-fila">


                                <span class="metadata-clave">

                                    <?php
                                    echo htmlspecialchars(
                                        str_replace(
                                            "_",
                                            " ",
                                            $clave
                                        )
                                    );
                                    ?>

                                </span>


                                <div class="metadata-valor">

                                    <?php

                                    if (
                                        is_array($valor)
                                    ) {

                                        echo htmlspecialchars(
                                            json_encode(
                                                $valor,
                                                JSON_UNESCAPED_UNICODE
                                            )
                                        );

                                    } elseif (
                                        is_bool($valor)
                                    ) {

                                        echo
                                            $valor
                                            ? "Sí"
                                            : "No";

                                    } else {

                                        echo htmlspecialchars(
                                            (string) $valor
                                        );

                                    }

                                    ?>

                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <p class="metadata-vacia">
                            Este evento no contiene información adicional.
                        </p>


                    <?php endif; ?>


                </aside>


            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>