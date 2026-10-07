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
    header("Location: ../index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| DATOS RECIBIDOS
|--------------------------------------------------------------------------
*/

$pedido_id = intval($_POST["pedido_id"] ?? 0);

$metodo = trim($_POST["metodo"] ?? "");


/*
|--------------------------------------------------------------------------
| VALIDACIONES BÁSICAS
|--------------------------------------------------------------------------
*/

if ($pedido_id <= 0 || $metodo === "") {
    header("Location: ../index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| COMPROBAR QUE EL PEDIDO PERTENECE A ESTA SESIÓN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["pedido_actual"]) ||
    intval($_SESSION["pedido_actual"]) !== $pedido_id
) {
    header("Location: ../index.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| MÉTODOS PERMITIDOS
|--------------------------------------------------------------------------
*/

$metodos_permitidos = [
    "tarjeta_simulada",
    "paypal_simulado",
    "transferencia_simulada"
];

if (!in_array($metodo, $metodos_permitidos, true)) {
    die("Método de pago no válido.");
}


/*
|--------------------------------------------------------------------------
| TRANSACCIÓN
|--------------------------------------------------------------------------
*/

try {

    $conexion->begin_transaction();


    /*
    |--------------------------------------------------------------------------
    | 1. OBTENER Y BLOQUEAR PEDIDO
    |--------------------------------------------------------------------------
    */

    $sql_pedido = "SELECT
                        id,
                        codigo,
                        total,
                        estado,
                        cliente_id
                   FROM pedidos
                   WHERE id = ?
                   FOR UPDATE";


    $stmt_pedido =
        $conexion->prepare($sql_pedido);


    $stmt_pedido->bind_param(
        "i",
        $pedido_id
    );


    $stmt_pedido->execute();


    $resultado_pedido =
        $stmt_pedido->get_result();


    if ($resultado_pedido->num_rows !== 1) {

        throw new Exception(
            "El pedido no existe."
        );

    }


    $pedido =
        $resultado_pedido->fetch_assoc();


    $stmt_pedido->close();


    /*
    |--------------------------------------------------------------------------
    | EVITAR PAGAR DOS VECES
    |--------------------------------------------------------------------------
    */

    if ($pedido["estado"] !== "pendiente_pago") {

        throw new Exception(
            "Este pedido ya ha sido procesado."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | 2. OBTENER LÍNEAS DEL PEDIDO
    |--------------------------------------------------------------------------
    */

    $sql_lineas = "SELECT
                        producto_id,
                        cantidad
                   FROM lineas_pedido
                   WHERE pedido_id = ?";


    $stmt_lineas =
        $conexion->prepare($sql_lineas);


    $stmt_lineas->bind_param(
        "i",
        $pedido_id
    );


    $stmt_lineas->execute();


    $resultado_lineas =
        $stmt_lineas->get_result();


    $lineas = [];


    while ($linea = $resultado_lineas->fetch_assoc()) {

        $lineas[] = $linea;

    }


    $stmt_lineas->close();


    /*
    |--------------------------------------------------------------------------
    | 3. DESCONTAR STOCK
    |--------------------------------------------------------------------------
    */

    $sql_stock = "UPDATE productos
                  SET stock = stock - ?
                  WHERE id = ?
                  AND stock >= ?";


    $stmt_stock =
        $conexion->prepare($sql_stock);


    foreach ($lineas as $linea) {

        $producto_id =
            intval($linea["producto_id"]);

        $cantidad =
            intval($linea["cantidad"]);


        $stmt_stock->bind_param(
            "iii",
            $cantidad,
            $producto_id,
            $cantidad
        );


        $stmt_stock->execute();


        /*
         * Si no se actualizó ninguna fila,
         * no había suficiente stock.
         */

        if ($stmt_stock->affected_rows !== 1) {

            throw new Exception(
                "No hay suficiente stock para completar el pedido."
            );

        }

    }


    $stmt_stock->close();


    /*
    |--------------------------------------------------------------------------
    | 4. CREAR REFERENCIA DEL PAGO
    |--------------------------------------------------------------------------
    */

    $referencia =
        "PAY-" .
        date("Ymd-His") .
        "-" .
        strtoupper(
            substr(
                bin2hex(random_bytes(3)),
                0,
                6
            )
        );


    /*
    |--------------------------------------------------------------------------
    | 5. REGISTRAR PAGO SIMULADO
    |--------------------------------------------------------------------------
    */

    $importe =
        floatval($pedido["total"]);

    $estado_pago =
        "simulado_aprobado";


    $sql_pago = "INSERT INTO pagos
                 (
                     pedido_id,
                     metodo,
                     importe,
                     estado,
                     referencia
                 )
                 VALUES (?, ?, ?, ?, ?)";


    $stmt_pago =
        $conexion->prepare($sql_pago);


    $stmt_pago->bind_param(
        "isdss",
        $pedido_id,
        $metodo,
        $importe,
        $estado_pago,
        $referencia
    );


    $stmt_pago->execute();

    $pago_id =
        $conexion->insert_id;

    $stmt_pago->close();


    /*
    |--------------------------------------------------------------------------
    | 6. ACTUALIZAR ESTADO DEL PEDIDO
    |--------------------------------------------------------------------------
    */

    $nuevo_estado =
        "pagado";


    $sql_actualizar_pedido =
        "UPDATE pedidos
         SET estado = ?
         WHERE id = ?";


    $stmt_actualizar =
        $conexion->prepare(
            $sql_actualizar_pedido
        );


    $stmt_actualizar->bind_param(
        "si",
        $nuevo_estado,
        $pedido_id
    );


    $stmt_actualizar->execute();

    $stmt_actualizar->close();


    /*
    |--------------------------------------------------------------------------
    | 7. EVENTO payment.simulated
    |--------------------------------------------------------------------------
    */

    $tipo_evento =
        "payment.simulated";

    $session_id =
        session_id();


    $metadata =
        json_encode(
            [
                "codigo_pedido" =>
                    $pedido["codigo"],

                "metodo" =>
                    $metodo,

                "importe" =>
                    round($importe, 2),

                "referencia" =>
                    $referencia,

                "estado" =>
                    $estado_pago
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
        $conexion->prepare($sql_evento);


    $cliente_id =
        intval($pedido["cliente_id"]);


    $stmt_evento->bind_param(
        "ssiis",
        $tipo_evento,
        $session_id,
        $cliente_id,
        $pedido_id,
        $metadata
    );


    $stmt_evento->execute();

    $stmt_evento->close();


    /*
    |--------------------------------------------------------------------------
    | 8. CONFIRMAR TRANSACCIÓN
    |--------------------------------------------------------------------------
    */

    $conexion->commit();


    /*
    |--------------------------------------------------------------------------
    | 9. LIMPIAR CARRITO
    |--------------------------------------------------------------------------
    */

    $_SESSION["carrito"] = [];


    /*
    |--------------------------------------------------------------------------
    | GUARDAR PEDIDO CONFIRMADO
    |--------------------------------------------------------------------------
    */

    $_SESSION["pedido_confirmado"] =
        $pedido_id;


    unset(
        $_SESSION["pedido_actual"]
    );


    /*
    |--------------------------------------------------------------------------
    | REDIRECCIÓN
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../pedido_confirmado.php?id=" .
        $pedido_id
    );

    exit();


} catch (Throwable $error) {

    $conexion->rollback();

    die(
        "No se ha podido completar el pago simulado: " .
        htmlspecialchars($error->getMessage())
    );

}
?>