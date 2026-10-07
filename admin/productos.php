<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include "../conexion.php";

$sql = "SELECT * FROM productos ORDER BY id DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de productos | PreyNet</title>

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
            max-width: 1250px;
            margin: 0 auto;
            padding: 60px 35px;
        }

        .titulo-zona {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 35px;
        }

        .titulo-zona h2 {
            font-size: 42px;
            font-weight: 900;
        }

        .boton-nuevo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            border-radius: 40px;
            background-color: #f36b21;
            color: white;
            text-decoration: none;
            font-weight: 900;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .boton-nuevo:hover {
            background-color: #102542;
            transform: translateY(-3px);
        }

        .tabla-contenedor {
            background-color: white;
            border-radius: 22px;
            border: 1px solid #dbe4ee;
            box-shadow: 0 18px 40px rgba(16, 37, 66, 0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #102542;
            color: white;
        }

        th, td {
            padding: 18px 20px;
            text-align: left;
            font-size: 15px;
            border-bottom: 1px solid #e4eaf0;
        }

        th {
            font-weight: 900;
        }

        td {
            color: #4f6072;
        }

        .acciones {
            display: flex;
            gap: 10px;
        }

        .btn-editar,
        .btn-eliminar {
            padding: 9px 14px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
        }

        .btn-editar {
            background-color: #102542;
            color: white;
        }

        .btn-eliminar {
            background-color: #ffe1d5;
            color: #b33b10;
        }

        .sin-productos {
            padding: 35px;
            text-align: center;
            color: #5f6f80;
            font-size: 17px;
        }

        @media (max-width: 850px) {
            .admin-header {
                padding: 22px 25px;
                flex-direction: column;
                align-items: flex-start;
            }

            .titulo-zona {
                flex-direction: column;
                align-items: flex-start;
            }

            .tabla-contenedor {
                overflow-x: auto;
            }

            table {
                min-width: 900px;
            }

            .titulo-zona h2 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1>Gestión de productos</h1>

        <nav>
            <a href="panel.php">Panel</a>
            <a href="../acciones/logout.php">Cerrar sesión</a>
        </nav>
    </header>

    <main class="contenedor">

        <div class="titulo-zona">
            <h2>Productos</h2>
            <a href="crear_producto.php" class="boton-nuevo">Añadir producto</a>
        </div>

        <div class="tabla-contenedor">
            <?php if ($resultado && $resultado->num_rows > 0): ?>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Fecha creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($producto = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $producto["id"]; ?></td>
                                <td><?php echo htmlspecialchars($producto["nombre"]); ?></td>
                                <td><?php echo htmlspecialchars($producto["categoria"]); ?></td>
                                <td><?php echo number_format($producto["precio"], 2, ",", "."); ?> €</td>
                                <td><?php echo $producto["stock"]; ?></td>
                                <td><?php echo $producto["fecha_creacion"]; ?></td>
                                <td>
                                    <div class="acciones">
                                        <a class="btn-editar" href="editar_producto.php?id=<?php echo $producto["id"]; ?>">Editar</a>
                                        <a class="btn-eliminar" href="eliminar_producto.php?id=<?php echo $producto["id"]; ?>" onclick="return confirm('¿Seguro que quieres eliminar este producto?');">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php else: ?>

                <div class="sin-productos">
                    Todavía no hay productos registrados.
                </div>

            <?php endif; ?>
        </div>

    </main>

</body>
</html>