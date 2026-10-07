<?php

/*
|--------------------------------------------------------------------------
| CONEXIÓN A BASE DE DATOS
|--------------------------------------------------------------------------
| PreyNet utiliza una configuración para entorno local (XAMPP)
| y otra para el servidor de producción en DonDominio.
|--------------------------------------------------------------------------
*/

$host_actual = $_SERVER["HTTP_HOST"] ?? "";

$es_local =
    $host_actual === "localhost" ||
    $host_actual === "127.0.0.1" ||
    str_starts_with($host_actual, "localhost:") ||
    str_starts_with($host_actual, "127.0.0.1:");



/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN LOCAL - XAMPP
|--------------------------------------------------------------------------
*/

if ($es_local) {

    $servidor = "localhost";
    $usuario = "root";
    $contrasena = "";
    $base_datos = "preynet_sie";

}


/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN PRODUCCIÓN - DONDOMINIO
|--------------------------------------------------------------------------
*/

else {

    $servidor = "bbdd.preynet.onl";
    $usuario = "ddb279687";
    $contrasena = "CONFIGURAR_CONTRASENA_DONDOMINIO";
    $base_datos = "ddb279687";

}



/*
|--------------------------------------------------------------------------
| CREAR CONEXIÓN
|--------------------------------------------------------------------------
*/

$conexion = new mysqli(
    $servidor,
    $usuario,
    $contrasena,
    $base_datos
);



/*
|--------------------------------------------------------------------------
| COMPROBAR CONEXIÓN
|--------------------------------------------------------------------------
*/

if ($conexion->connect_error) {

    die(
        "No se ha podido conectar con la base de datos."
    );

}



/*
|--------------------------------------------------------------------------
| CODIFICACIÓN
|--------------------------------------------------------------------------
*/

$conexion->set_charset("utf8mb4");