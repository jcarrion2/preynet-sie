<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";


/*
|--------------------------------------------------------------------------
| ACTUALIZAR ESTADO
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["incidencia_id"]) &&
    isset($_POST["estado"])
) {

    $incidencia_id =
        intval($_POST["incidencia_id"]);

    $estado =
        trim($_POST["estado"]);


    $estados_permitidos = [
        "abierta",
        "en_proceso",
        "resuelta"
    ];


    if (
        $incidencia_id > 0 &&
        in_array(
            $estado,
            $estados_permitidos,
            true
        )
    ) {

        $sql =
            "UPDATE incidencias
             SET estado = ?
             WHERE id = ?";


        $stmt =
            $conexion->prepare($sql);


        $stmt->bind_param(
            "si",
            $estado,
            $incidencia_id
        );


        $stmt->execute();

        $stmt->close();

    }


    header("Location: incidencias.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| INCIDENCIA SELECCIONADA
|--------------------------------------------------------------------------
*/

$incidencia_seleccionada = null;


if (isset($_GET["ver"])) {

    $incidencia_id =
        intval($_GET["ver"]);


    if ($incidencia_id > 0) {

        $sql_detalle =
            "SELECT
                i.*,

                p.codigo AS codigo_pedido

             FROM incidencias i

             LEFT JOIN pedidos p
                ON i.pedido_id = p.id

             WHERE i.id = ?";


        $stmt =
            $conexion->prepare(
                $sql_detalle
            );


        $stmt->bind_param(
            "i",
            $incidencia_id
        );


        $stmt->execute();


        $resultado =
            $stmt->get_result();


        if ($resultado->num_rows === 1) {

            $incidencia_seleccionada =
                $resultado->fetch_assoc();

        }


        $stmt->close();

    }

}


/*
|--------------------------------------------------------------------------
| LISTADO
|--------------------------------------------------------------------------
*/

$sql_incidencias =
    "SELECT
        i.id,
        i.codigo,
        i.nombre,
        i.email,
        i.asunto,
        i.estado,
        i.prioridad,
        i.fecha_creacion,

        p.codigo AS codigo_pedido

     FROM incidencias i

     LEFT JOIN pedidos p
        ON i.pedido_id = p.id

     ORDER BY i.id DESC";


$resultado_incidencias =
    $conexion->query(
        $sql_incidencias
    );

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
        Incidencias | Administración PreyNet
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


        /* CABECERA */

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


        /* CONTENIDO */

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


        /* TABLA */

        .tabla-contenedor {
            overflow-x: auto;

            background-color: white;

            border: 1px solid #dbe4ee;
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

        .email {
            margin-top: 4px;

            color: #8191a1;

            font-size: 12px;
        }


        /* ESTADOS */

        .estado {
            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;
        }

        .estado-abierta {
            background-color: #fff2df;
            color: #aa6415;
        }

        .estado-proceso {
            background-color: #e8efff;
            color: #315bb5;
        }

        .estado-resuelta {
            background-color: #e3f7eb;
            color: #177c42;
        }


        /* PRIORIDAD */

        .prioridad {
            font-weight: 900;
        }

        .prioridad-alta {
            color: #c43b22;
        }

        .prioridad-media {
            color: #ad6a14;
        }

        .prioridad-baja {
            color: #248350;
        }


        /* BOTÓN */

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


        /* DETALLE */

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


        /* GRID */

        .detalle-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 1.4fr)
                minmax(300px, 1fr);

            gap: 25px;
        }

        .bloque {
            padding: 28px;

            background-color: white;

            border: 1px solid #dbe4ee;
            border-radius: 20px;

            box-shadow:
                0 12px 30px
                rgba(16, 37, 66, 0.06);
        }

        .bloque h3 {
            margin-bottom: 20px;

            font-size: 21px;
        }


        /* DATOS */

        .dato {
            margin-bottom: 18px;
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
            line-height: 1.6;
        }

        .mensaje {
            padding: 18px;

            background-color: #f7f9fc;

            border-radius: 12px;

            color: #526577;

            line-height: 1.7;

            font-size: 14px;
        }


        /* GESTIÓN */

        .gestion {
            background-color: #071827;
            color: white;
        }

        .gestion h3 {
            color: white;
        }

        .gestion .dato span {
            color: #9dacba;
        }

        .gestion .dato p,
        .gestion .dato strong {
            color: white;
        }

        .gestion form {
            margin-top: 25px;
        }

        .gestion label {
            display: block;

            margin-bottom: 8px;

            color: #cbd5df;

            font-size: 12px;
            font-weight: 900;
        }

        .gestion select {
            width: 100%;

            margin-bottom: 15px;

            padding: 13px;

            border: none;
            border-radius: 10px;

            font-size: 14px;
        }

        .boton-actualizar {
            width: 100%;

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


        /* SIN DATOS */

        .sin-datos {
            padding: 55px;

            text-align: center;

            color: #647689;
        }


        /* RESPONSIVE */

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
        Incidencias PreyNet
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


    <section class="titulo">

        <span>
            Atención postventa
        </span>

        <h2>
            Incidencias
        </h2>

        <p>
            Consulta y gestiona las solicitudes
            recibidas desde el formulario de soporte.
        </p>

    </section>


    <!-- LISTADO -->

    <section class="tabla-contenedor">


        <?php
        if (
            $resultado_incidencias &&
            $resultado_incidencias->num_rows > 0
        ):
        ?>


            <table>

                <thead>

                    <tr>

                        <th>
                            Código
                        </th>

                        <th>
                            Cliente
                        </th>

                        <th>
                            Pedido
                        </th>

                        <th>
                            Asunto
                        </th>

                        <th>
                            Prioridad
                        </th>

                        <th>
                            Estado
                        </th>

                        <th>
                            Acción
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php
                    while (
                        $incidencia =
                        $resultado_incidencias->fetch_assoc()
                    ):
                    ?>


                        <tr>


                            <td class="codigo">

                                <?php
                                echo htmlspecialchars(
                                    $incidencia["codigo"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $incidencia["nombre"]
                                );
                                ?>


                                <div class="email">

                                    <?php
                                    echo htmlspecialchars(
                                        $incidencia["email"]
                                    );
                                    ?>

                                </div>

                            </td>


                            <td>

                                <?php
                                echo
                                    !empty(
                                        $incidencia["codigo_pedido"]
                                    )
                                    ? htmlspecialchars(
                                        $incidencia["codigo_pedido"]
                                    )
                                    : "Sin pedido";
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $incidencia["asunto"]
                                );
                                ?>

                            </td>


                            <td>

                                <span
                                    class="
                                        prioridad
                                        prioridad-<?php echo htmlspecialchars($incidencia["prioridad"]); ?>
                                    "
                                >

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars(
                                            $incidencia["prioridad"]
                                        )
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>


                                <?php if ($incidencia["estado"] === "abierta"): ?>

                                    <span
                                        class="
                                            estado
                                            estado-abierta
                                        "
                                    >
                                        Abierta
                                    </span>


                                <?php elseif ($incidencia["estado"] === "en_proceso"): ?>

                                    <span
                                        class="
                                            estado
                                            estado-proceso
                                        "
                                    >
                                        En proceso
                                    </span>


                                <?php else: ?>

                                    <span
                                        class="
                                            estado
                                            estado-resuelta
                                        "
                                    >
                                        Resuelta
                                    </span>

                                <?php endif; ?>


                            </td>


                            <td>

                                <a
                                    href="incidencias.php?ver=<?php echo intval($incidencia["id"]); ?>"
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
                Todavía no hay incidencias registradas.
            </div>


        <?php endif; ?>


    </section>


    <!-- DETALLE -->

    <?php if ($incidencia_seleccionada !== null): ?>


        <section class="detalle">


            <div class="detalle-cabecera">


                <h2>

                    <?php
                    echo htmlspecialchars(
                        $incidencia_seleccionada["codigo"]
                    );
                    ?>

                </h2>


                <a
                    href="incidencias.php"
                    class="cerrar-detalle"
                >
                    Cerrar detalle
                </a>


            </div>


            <div class="detalle-grid">


                <div class="bloque">


                    <h3>
                        Detalle de la incidencia
                    </h3>


                    <div class="dato">

                        <span>
                            Cliente
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $incidencia_seleccionada["nombre"]
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="dato">

                        <span>
                            Email
                        </span>

                        <p>

                            <?php
                            echo htmlspecialchars(
                                $incidencia_seleccionada["email"]
                            );
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Pedido relacionado
                        </span>

                        <p>

                            <?php
                            echo
                                !empty(
                                    $incidencia_seleccionada["codigo_pedido"]
                                )
                                ? htmlspecialchars(
                                    $incidencia_seleccionada["codigo_pedido"]
                                )
                                : "Sin pedido asociado";
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Asunto
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $incidencia_seleccionada["asunto"]
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="dato">

                        <span>
                            Mensaje
                        </span>

                        <div class="mensaje">

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $incidencia_seleccionada["mensaje"]
                                )
                            );
                            ?>

                        </div>

                    </div>


                </div>


                <!-- GESTIÓN -->

                <aside class="bloque gestion">


                    <h3>
                        Gestión
                    </h3>


                    <div class="dato">

                        <span>
                            Prioridad
                        </span>

                        <strong>

                            <?php
                            echo ucfirst(
                                htmlspecialchars(
                                    $incidencia_seleccionada["prioridad"]
                                )
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="dato">

                        <span>
                            Fecha de creación
                        </span>

                        <p>

                            <?php
                            echo date(
                                "d/m/Y H:i",
                                strtotime(
                                    $incidencia_seleccionada["fecha_creacion"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                    <div class="dato">

                        <span>
                            Última actualización
                        </span>

                        <p>

                            <?php
                            echo date(
                                "d/m/Y H:i",
                                strtotime(
                                    $incidencia_seleccionada["fecha_actualizacion"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                    <form method="POST">


                        <input
                            type="hidden"
                            name="incidencia_id"
                            value="<?php echo intval($incidencia_seleccionada["id"]); ?>"
                        >


                        <label for="estado">
                            Estado de la incidencia
                        </label>


                        <select
                            id="estado"
                            name="estado"
                        >

                            <option
                                value="abierta"
                                <?php
                                if (
                                    $incidencia_seleccionada["estado"] === "abierta"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                Abierta
                            </option>


                            <option
                                value="en_proceso"
                                <?php
                                if (
                                    $incidencia_seleccionada["estado"] === "en_proceso"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                En proceso
                            </option>


                            <option
                                value="resuelta"
                                <?php
                                if (
                                    $incidencia_seleccionada["estado"] === "resuelta"
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                Resuelta
                            </option>


                        </select>


                        <button
                            type="submit"
                            class="boton-actualizar"
                        >
                            Actualizar estado
                        </button>


                    </form>


                </aside>


            </div>


        </section>


    <?php endif; ?>


</main>


</body>

</html>