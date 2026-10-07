<?php

session_start();

include "../conexion.php";


/*
|--------------------------------------------------------------------------
| COMPROBAR DATOS RECIBIDOS
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST["producto_id"]) ||
    !isset($_POST["cantidad"])
) {
    header("Location: ../index.php#tienda");
    exit();
}


$producto_id = intval($_POST["producto_id"]);
$cantidad = intval($_POST["cantidad"]);


/*
|--------------------------------------------------------------------------
| VALIDAR CANTIDAD
|--------------------------------------------------------------------------
*/

if ($cantidad < 1) {
    $cantidad = 1;
}


/*
|--------------------------------------------------------------------------
| BUSCAR PRODUCTO
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, sku, nombre, precio, stock
        FROM productos
        WHERE id = ?
        AND activo = 1";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $producto_id
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows !== 1) {

    header("Location: ../index.php#tienda");
    exit();

}


$producto = $resultado->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| COMPROBAR STOCK
|--------------------------------------------------------------------------
*/

if ($producto["stock"] <= 0) {

    header(
        "Location: ../producto.php?id=" .
        $producto_id .
        "&error=sin_stock"
    );

    exit();

}


if ($cantidad > $producto["stock"]) {

    $cantidad = intval($producto["stock"]);

}


/*
|--------------------------------------------------------------------------
| CREAR CARRITO SI NO EXISTE
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["carrito"])) {

    $_SESSION["carrito"] = [];

}


/*
|--------------------------------------------------------------------------
| AÑADIR PRODUCTO
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["carrito"][$producto_id])) {

    $nueva_cantidad =
        $_SESSION["carrito"][$producto_id]
        + $cantidad;


    /*
     * Nunca permitimos que el carrito
     * supere el stock disponible.
     */

    if ($nueva_cantidad > $producto["stock"]) {

        $nueva_cantidad =
            intval($producto["stock"]);

    }


    $_SESSION["carrito"][$producto_id] =
        $nueva_cantidad;

} else {

    $_SESSION["carrito"][$producto_id] =
        $cantidad;

}


/*
|--------------------------------------------------------------------------
| EVENTO: cart.item_added
|--------------------------------------------------------------------------
*/

$tipo_evento = "cart.item_added";

$session_id = session_id();


$metadata = json_encode(
    [
        "sku" => $producto["sku"],
        "nombre" => $producto["nombre"],
        "cantidad_anadida" => $cantidad,
        "cantidad_carrito" =>
            $_SESSION["carrito"][$producto_id],
        "precio_unitario" =>
            floatval($producto["precio"])
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


$stmt_evento =
    $conexion->prepare($sql_evento);


$stmt_evento->bind_param(
    "ssis",
    $tipo_evento,
    $session_id,
    $producto_id,
    $metadata
);


$stmt_evento->execute();

$stmt_evento->close();


/*
|--------------------------------------------------------------------------
| VOLVER A LA FICHA
|--------------------------------------------------------------------------
*/

header(
    "Location: ../producto.php?id=" .
    $producto_id .
    "&agregado=1"
);

exit();

?>