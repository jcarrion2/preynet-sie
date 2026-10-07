<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";

$mensaje_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sku = strtoupper(trim($_POST["sku"]));
    $nombre = trim($_POST["nombre"]);
    $categoria = trim($_POST["categoria"]);
    $descripcion = trim($_POST["descripcion"]);
    $precio = floatval($_POST["precio"]);
    $stock = intval($_POST["stock"]);
    $imagen = trim($_POST["imagen"]);

    if ($sku == "" || $nombre == "" || $categoria == "" || $precio <= 0 || $stock < 0) {

        $mensaje_error = "Por favor, completa correctamente los campos obligatorios.";

    } else {

        $sql = "INSERT INTO productos 
                (sku, nombre, categoria, descripcion, precio, stock, imagen)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "ssssdis",
            $sku,
            $nombre,
            $categoria,
            $descripcion,
            $precio,
            $stock,
            $imagen
        );

        if ($stmt->execute()) {

            header("Location: productos.php");
            exit();

        } else {

            if ($stmt->errno == 1062) {
                $mensaje_error = "Ya existe un producto con ese SKU.";
            } else {
                $mensaje_error = "Error al guardar el producto.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Añadir producto | PreyNet</title>

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

        .admin-header {
            width: 100%;
            padding: 24px 50px;
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

        .admin-header nav {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .admin-header a {
            color: white;
            background-color: #f36b21;
            padding: 12px 22px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
            transition: all 0.3s ease;
        }

        .admin-header a:hover {
            background-color: white;
            color: #102542;
        }

        .contenedor {
            max-width: 850px;
            margin: 0 auto;
            padding: 60px 35px;
        }

        .contenedor h2 {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 30px;
        }

        .formulario {
            padding: 42px;
            background-color: white;
            border-radius: 22px;
            border: 1px solid #dbe4ee;
            box-shadow: 0 18px 40px rgba(16, 37, 66, 0.08);
        }

        .grupo {
            margin-bottom: 22px;
        }

        .grupo label {
            display: block;
            margin-bottom: 9px;
            font-size: 14px;
            font-weight: 900;
            color: #102542;
        }

        .grupo input,
        .grupo select,
        .grupo textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #cbd8e6;
            border-radius: 12px;
            font-size: 15px;
            font-family: Arial, Helvetica, sans-serif;
            color: #102542;
            background-color: #f9fbfd;
            outline: none;
        }

        .grupo input:focus,
        .grupo select:focus,
        .grupo textarea:focus {
            border-color: #f36b21;
            box-shadow: 0 0 0 4px rgba(243, 107, 33, 0.12);
            background-color: white;
        }

        .grupo textarea {
            resize: vertical;
        }

        .fila {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .botones {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-top: 30px;
        }

        .boton-guardar {
            padding: 15px 34px;
            border: none;
            border-radius: 40px;
            background-color: #f36b21;
            color: white;
            font-weight: 900;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .boton-guardar:hover {
            background-color: #102542;
            transform: translateY(-3px);
        }

        .boton-cancelar {
            padding: 15px 30px;
            border-radius: 40px;
            background-color: #e8edf3;
            color: #102542;
            text-decoration: none;
            font-weight: 900;
            font-size: 15px;
        }

        .mensaje-error {
            margin-bottom: 24px;
            padding: 14px 18px;
            background-color: #ffe8e0;
            color: #a33a12;
            border-radius: 12px;
            font-weight: 800;
        }

        .ayuda {
            margin-top: 7px;
            font-size: 13px;
            color: #6b7787;
            line-height: 1.5;
        }

        @media (max-width: 700px) {

            .admin-header {
                padding: 22px 25px;
                flex-direction: column;
                align-items: flex-start;
            }

            .contenedor {
                padding: 45px 25px;
            }

            .contenedor h2 {
                font-size: 32px;
            }

            .formulario {
                padding: 28px;
            }

            .fila {
                grid-template-columns: 1fr;
            }

            .botones {
                flex-direction: column;
                align-items: stretch;
            }

            .boton-guardar,
            .boton-cancelar {
                width: 100%;
                text-align: center;
            }
        }

    </style>
</head>

<body>

<header class="admin-header">

    <h1>Añadir producto</h1>

    <nav>
        <a href="productos.php">Productos</a>
        <a href="panel.php">Panel</a>
        <a href="../acciones/logout.php">Cerrar sesión</a>
    </nav>

</header>


<main class="contenedor">

    <h2>Nuevo producto</h2>

    <form class="formulario" method="POST">

        <?php if ($mensaje_error != ""): ?>

            <div class="mensaje-error">
                <?php echo $mensaje_error; ?>
            </div>

        <?php endif; ?>


        <!-- SKU -->

        <div class="grupo">

            <label for="sku">
                SKU *
            </label>

            <input
                type="text"
                id="sku"
                name="sku"
                required
                placeholder="Ej: MON-002"
            >

            <p class="ayuda">
                Código único del producto. Por ejemplo: MON-002, TEC-002 o RAT-002.
            </p>

        </div>


        <!-- NOMBRE -->

        <div class="grupo">

            <label for="nombre">
                Nombre del producto *
            </label>

            <input
                type="text"
                id="nombre"
                name="nombre"
                required
                placeholder="Ej: Monitor PreyView 27"
            >

        </div>


        <!-- CATEGORÍA -->

        <div class="grupo">

            <label for="categoria">
                Categoría *
            </label>

            <select
                id="categoria"
                name="categoria"
                required
            >

                <option value="">
                    Seleccione una categoría
                </option>

                <option value="Monitores">
                    Monitores
                </option>

                <option value="Teclados">
                    Teclados
                </option>

                <option value="Ratones">
                    Ratones
                </option>

                <option value="Torres PC">
                    Torres PC
                </option>

                <option value="Portátiles">
                    Portátiles
                </option>

                <option value="Móviles">
                    Móviles
                </option>

                <option value="Micrófonos">
                    Micrófonos
                </option>

                <option value="Webcams">
                    Webcams
                </option>

            </select>

        </div>


        <!-- DESCRIPCIÓN -->

        <div class="grupo">

            <label for="descripcion">
                Descripción
            </label>

            <textarea
                id="descripcion"
                name="descripcion"
                rows="5"
                placeholder="Describe brevemente el producto..."
            ></textarea>

        </div>


        <!-- PRECIO Y STOCK -->

        <div class="fila">

            <div class="grupo">

                <label for="precio">
                    Precio *
                </label>

                <input
                    type="number"
                    id="precio"
                    name="precio"
                    min="0.01"
                    step="0.01"
                    required
                    placeholder="Ej: 799.99"
                >

            </div>


            <div class="grupo">

                <label for="stock">
                    Stock *
                </label>

                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    required
                    placeholder="Ej: 10"
                >

            </div>

        </div>


        <!-- IMAGEN -->

        <div class="grupo">

            <label for="imagen">
                Imagen
            </label>

            <input
                type="text"
                id="imagen"
                name="imagen"
                placeholder="Ej: img/productos/monitor.jpg"
            >

            <p class="ayuda">
                Puedes dejarlo vacío por ahora. Más adelante añadiremos las imágenes definitivas de los productos.
            </p>

        </div>


        <!-- BOTONES -->

        <div class="botones">

            <button
                type="submit"
                class="boton-guardar"
            >
                Guardar producto
            </button>

            <a
                href="productos.php"
                class="boton-cancelar"
            >
                Cancelar
            </a>

        </div>

    </form>

</main>

</body>
</html>