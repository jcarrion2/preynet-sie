<?php

session_start();

include "../conexion.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/*
|--------------------------------------------------------------------------
| SOLO ACEPTAMOS POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../checkout.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| COMPROBAR CARRITO
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["carrito"]) ||
    !is_array($_SESSION["carrito"]) ||
    count($_SESSION["carrito"]) === 0
) {
    header("Location: ../carrito.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| DATOS DEL CLIENTE
|--------------------------------------------------------------------------
*/

$nombre = trim($_POST["nombre"] ?? "");
$apellidos = trim($_POST["apellidos"] ?? "");
$email = trim($_POST["email"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$direccion = trim($_POST["direccion"] ?? "");
$codigo_postal = trim($_POST["codigo_postal"] ?? "");
$ciudad = trim($_POST["ciudad"] ?? "");
$provincia = trim($_POST["provincia"] ?? "");


/*
|--------------------------------------------------------------------------
| VALIDACIÓN
|--------------------------------------------------------------------------
*/

if (
    $nombre === "" ||
    $apellidos === "" ||
    $email === "" ||
    $direccion === "" ||
    $codigo_postal === "" ||
    $ciudad === "" ||
    $provincia === ""
) {
    die("Faltan datos obligatorios del cliente.");
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("El correo electrónico no es válido.");
}


/*
|--------------------------------------------------------------------------
| RECALCULAR CARRITO DESDE LA BASE DE DATOS
|--------------------------------------------------------------------------
|
| Nunca confiamos en importes enviados desde el navegador.
|
*/

$productos_pedido = [];

$subtotal = 0;


foreach ($_SESSION["carrito"] as $producto_id => $cantidad) {

    $producto_id = intval($producto_id);
    $cantidad = intval($cantidad);

    if ($cantidad <= 0) {
        continue;
    }


    $sql_producto = "SELECT id,
                            sku,
                            nombre,
                            precio,
                            stock
                     FROM productos
                     WHERE id = ?
                     AND activo = 1";


    $stmt_producto =
        $conexion->prepare($sql_producto);


    $stmt_producto->bind_param(
        "i",
        $producto_id
    );


    $stmt_producto->execute();


    $resultado_producto =
        $stmt_producto->get_result();


    if ($resultado_producto->num_rows !== 1) {

        $stmt_producto->close();
        continue;

    }


    $producto =
        $resultado_producto->fetch_assoc();


    $stmt_producto->close();


    /*
     * Verificamos stock.
     */

    if ($cantidad > intval($producto["stock"])) {

        die(
            "No hay suficiente stock disponible para " .
            htmlspecialchars($producto["nombre"])
        );

    }


    $total_linea =
        floatval($producto["precio"])
        * $cantidad;


    $producto["cantidad"] =
        $cantidad;

    $producto["total_linea"] =
        $total_linea;


    $productos_pedido[] =
        $producto;


    $subtotal +=
        $total_linea;

}


/*
|--------------------------------------------------------------------------
| COMPROBAR QUE SIGA HABIENDO PRODUCTOS
|--------------------------------------------------------------------------
*/

if (count($productos_pedido) === 0) {

    header("Location: ../carrito.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| CÁLCULOS DEL PEDIDO
|--------------------------------------------------------------------------
*/

$iva = $subtotal * 0.21;

$envio =
    ($subtotal >= 200)
    ? 0
    : 5.90;

$descuento = 0;

$total =
    $subtotal
    + $iva
    + $envio
    - $descuento;


/*
|--------------------------------------------------------------------------
| CREAR PEDIDO DENTRO DE UNA TRANSACCIÓN
|--------------------------------------------------------------------------
*/

try {

    $conexion->begin_transaction();


    /*
    |--------------------------------------------------------------------------
    | 1. CREAR CLIENTE
    |--------------------------------------------------------------------------
    */

    $sql_cliente =
        "INSERT INTO clientes
        (
            nombre,
            apellidos,
            email,
            telefono,
            direccion,
            codigo_postal,
            ciudad,
            provincia
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt_cliente =
        $conexion->prepare($sql_cliente);


    $stmt_cliente->bind_param(
        "ssssssss",
        $nombre,
        $apellidos,
        $email,
        $telefono,
        $direccion,
        $codigo_postal,
        $ciudad,
        $provincia
    );


    $stmt_cliente->execute();


    $cliente_id =
        $conexion->insert_id;


    $stmt_cliente->close();


    /*
    |--------------------------------------------------------------------------
    | 2. GENERAR CÓDIGO ÚNICO DE PEDIDO
    |--------------------------------------------------------------------------
    */

    $codigo_pedido =
        "PN-" .
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
    | 3. CREAR PEDIDO
    |--------------------------------------------------------------------------
    */

    $estado =
        "pendiente_pago";


    $sql_pedido =
        "INSERT INTO pedidos
        (
            codigo,
            cliente_id,
            subtotal,
            descuento,
            envio,
            iva,
            total,
            estado
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";


    $stmt_pedido =
        $conexion->prepare($sql_pedido);


    $stmt_pedido->bind_param(
        "siddddds",
        $codigo_pedido,
        $cliente_id,
        $subtotal,
        $descuento,
        $envio,
        $iva,
        $total,
        $estado
    );


    $stmt_pedido->execute();


    $pedido_id =
        $conexion->insert_id;


    $stmt_pedido->close();


    /*
    |--------------------------------------------------------------------------
    | 4. CREAR LÍNEAS DE PEDIDO
    |--------------------------------------------------------------------------
    */

    $sql_linea =
        "INSERT INTO lineas_pedido
        (
            pedido_id,
            producto_id,
            nombre_producto,
            cantidad,
            precio_unitario,
            iva_porcentaje,
            total_linea
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)";


    $stmt_linea =
        $conexion->prepare($sql_linea);


    foreach ($productos_pedido as $producto) {

        $producto_id =
            intval($producto["id"]);

        $nombre_producto =
            $producto["nombre"];

        $cantidad =
            intval($producto["cantidad"]);

        $precio_unitario =
            floatval($producto["precio"]);

        $iva_porcentaje =
            21.00;

        $total_linea =
            floatval($producto["total_linea"]);


        $stmt_linea->bind_param(
            "iisiddd",
            $pedido_id,
            $producto_id,
            $nombre_producto,
            $cantidad,
            $precio_unitario,
            $iva_porcentaje,
            $total_linea
        );


        $stmt_linea->execute();

    }


    $stmt_linea->close();


    /*
    |--------------------------------------------------------------------------
    | 5. EVENTO order.created
    |--------------------------------------------------------------------------
    */

    $tipo_evento =
        "order.created";

    $session_id =
        session_id();


    $metadata =
        json_encode(
            [
                "codigo_pedido" => $codigo_pedido,
                "subtotal" => round($subtotal, 2),
                "iva" => round($iva, 2),
                "envio" => round($envio, 2),
                "total" => round($total, 2),
                "estado" => $estado
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
    | CONFIRMAR TODO
    |--------------------------------------------------------------------------
    */

    $conexion->commit();


    /*
    |--------------------------------------------------------------------------
    | GUARDAMOS EL PEDIDO ACTUAL EN SESIÓN
    |--------------------------------------------------------------------------
    */

    $_SESSION["pedido_actual"] =
        $pedido_id;


    /*
    |--------------------------------------------------------------------------
    | IR AL PAGO SIMULADO
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../pago.php?id=" .
        $pedido_id
    );

    exit();


} catch (Throwable $error) {

    $conexion->rollback();

    die(
        "No se ha podido generar el pedido. " .
        "Inténtalo de nuevo."
    );

}
?>