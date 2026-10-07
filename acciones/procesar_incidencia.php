<?php

session_start();

include "../conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/*
|--------------------------------------------------------------------------
| SOLO POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../soporte.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| RECOGER DATOS
|--------------------------------------------------------------------------
*/

$nombre =
    trim($_POST["nombre"] ?? "");

$email =
    trim($_POST["email"] ?? "");

$pedido_id =
    intval($_POST["pedido_id"] ?? 0);

$asunto =
    trim($_POST["asunto"] ?? "");

$mensaje =
    trim($_POST["mensaje"] ?? "");

$prioridad =
    trim($_POST["prioridad"] ?? "media");


/*
|--------------------------------------------------------------------------
| VALIDAR CAMPOS
|--------------------------------------------------------------------------
*/

if (
    $nombre === "" ||
    $email === "" ||
    $asunto === "" ||
    $mensaje === ""
) {

    die("Faltan datos obligatorios.");

}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die("El correo electrónico no es válido.");

}


/*
|--------------------------------------------------------------------------
| VALIDAR PRIORIDAD
|--------------------------------------------------------------------------
*/

$prioridades_permitidas = [
    "baja",
    "media",
    "alta"
];


if (
    !in_array(
        $prioridad,
        $prioridades_permitidas,
        true
    )
) {

    $prioridad = "media";

}


/*
|--------------------------------------------------------------------------
| BUSCAR CLIENTE SI HAY PEDIDO
|--------------------------------------------------------------------------
*/

$cliente_id = null;
$pedido_id_db = null;


if ($pedido_id > 0) {

    $sql_pedido =
        "SELECT
            cliente_id
         FROM pedidos
         WHERE id = ?";


    $stmt_pedido =
        $conexion->prepare($sql_pedido);


    $stmt_pedido->bind_param(
        "i",
        $pedido_id
    );


    $stmt_pedido->execute();


    $resultado_pedido =
        $stmt_pedido->get_result();


    if ($resultado_pedido->num_rows === 1) {

        $pedido =
            $resultado_pedido->fetch_assoc();


        $pedido_id_db =
            $pedido_id;


        $cliente_id =
            intval(
                $pedido["cliente_id"]
            );

    }


    $stmt_pedido->close();

}


/*
|--------------------------------------------------------------------------
| GENERAR CÓDIGO DE INCIDENCIA
|--------------------------------------------------------------------------
*/

$codigo =
    "INC-" .
    date("Ymd") .
    "-" .
    strtoupper(
        substr(
            bin2hex(
                random_bytes(3)
            ),
            0,
            6
        )
    );


/*
|--------------------------------------------------------------------------
| ESTADO INICIAL
|--------------------------------------------------------------------------
*/

$estado =
    "abierta";


/*
|--------------------------------------------------------------------------
| GUARDAR INCIDENCIA
|--------------------------------------------------------------------------
*/

try {

    $conexion->begin_transaction();


    $sql_incidencia =
        "INSERT INTO incidencias
        (
            codigo,
            pedido_id,
            cliente_id,
            nombre,
            email,
            asunto,
            mensaje,
            estado,
            prioridad
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt_incidencia =
        $conexion->prepare(
            $sql_incidencia
        );


    $stmt_incidencia->bind_param(
        "siissssss",
        $codigo,
        $pedido_id_db,
        $cliente_id,
        $nombre,
        $email,
        $asunto,
        $mensaje,
        $estado,
        $prioridad
    );


    $stmt_incidencia->execute();


    $incidencia_id =
        $conexion->insert_id;


    $stmt_incidencia->close();


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR EVENTO DE NEGOCIO
    |--------------------------------------------------------------------------
    */

    $tipo_evento =
        "support.ticket_created";

    $session_id =
        session_id();


    $metadata =
        json_encode(
            [
                "incidencia_id" =>
                    $incidencia_id,

                "codigo" =>
                    $codigo,

                "asunto" =>
                    $asunto,

                "prioridad" =>
                    $prioridad,

                "estado" =>
                    $estado
            ],
            JSON_UNESCAPED_UNICODE
        );


    $sql_evento =
        "INSERT INTO eventos
        (
            tipo,
            session_id,
            cliente_id,
            pedido_id,
            metadata
        )
        VALUES (?, ?, ?, ?, ?)";


    $stmt_evento =
        $conexion->prepare(
            $sql_evento
        );


    $stmt_evento->bind_param(
        "ssiis",
        $tipo_evento,
        $session_id,
        $cliente_id,
        $pedido_id_db,
        $metadata
    );


    $stmt_evento->execute();

    $stmt_evento->close();


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR
    |--------------------------------------------------------------------------
    */

    $conexion->commit();


    /*
    |--------------------------------------------------------------------------
    | GUARDAR INCIDENCIA EN SESIÓN
    |--------------------------------------------------------------------------
    */

    $_SESSION["ultima_incidencia"] =
        $codigo;


    /*
    |--------------------------------------------------------------------------
    | REDIRECCIÓN
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../incidencia_confirmada.php?codigo=" .
        urlencode($codigo)
    );

    exit();


} catch (Throwable $error) {

    $conexion->rollback();


    die(
        "No se ha podido registrar la incidencia: " .
        htmlspecialchars(
            $error->getMessage()
        )
    );

}

?>