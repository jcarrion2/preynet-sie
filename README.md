# PreyNet

Prototipo académico de comercio electrónico desarrollado para la asignatura de Sistemas de Información Empresarial.

PreyNet simula una tienda online especializada en productos informáticos y tecnológicos. La aplicación permite recorrer un proceso completo de compra, desde la consulta del catálogo hasta la generación del pedido, incluyendo carrito, checkout, pago simulado, gestión de estados, atención postventa y registro de eventos de negocio.

## Aplicación desplegada

La aplicación está disponible públicamente en:

https://www.preynet.onl

## Tecnologías utilizadas

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- Sesiones PHP
- Apache
- XAMPP para el entorno local
- DonDominio para el despliegue público
- Git y GitHub para el control de versiones

## Funcionalidades principales

La aplicación incluye:

- Página principal de la tienda.
- Catálogo de 8 productos.
- Organización de productos por categorías.
- Ficha individual de producto.
- Gestión de stock.
- Carrito de compra.
- Modificación de cantidades del carrito.
- Checkout.
- Registro de datos del cliente.
- Cálculo de IVA.
- Cálculo de gastos de envío.
- Pago simulado.
- Generación de pedidos con identificador único.
- Líneas de pedido persistidas en base de datos.
- Estados del pedido.
- Panel de administración.
- Gestión de productos.
- Consulta de clientes.
- Consulta y gestión de pedidos.
- Gestión de incidencias.
- Registro de eventos de negocio.
- Seguimiento de ventas simuladas.

## Productos del catálogo

El catálogo contiene los siguientes productos de demostración:

1. PreyView M24 Full HD
2. PreyKey MK2
3. PreyMouse W5
4. PreyCore Gaming R5
5. PreyBook 15 Pro
6. PreyPhone 5G 128 GB
7. PreyMic USB Studio
8. PreyCam Full HD

Los productos, precios, stock, categorías e imágenes se almacenan de forma persistente.

## Estructura del proyecto

```text
preynet/
│
├── acciones/
│   ├── agregar_carrito.php
│   ├── logout.php
│   ├── procesar_checkout.php
│   ├── procesar_incidencia.php
│   ├── procesar_login.php
│   └── procesar_pago.php
│
├── admin/
│   ├── clientes.php
│   ├── crear_producto.php
│   ├── editar_producto.php
│   ├── eliminar_producto.php
│   ├── eventos_negocio.php
│   ├── incidencias.php
│   ├── login.php
│   ├── panel.php
│   ├── pedidos.php
│   └── productos.php
│
├── css/
│   └── estilos.css
│
├── img/
│
├── videos/
│   └── fondo-preynet.mp4
│
├── carrito.php
├── checkout.php
├── conexion.php
├── incidencia_confirmada.php
├── index.php
├── pago.php
├── pedido_confirmado.php
├── producto.php
└── soporte.php