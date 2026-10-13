<?php
session_start();
require_once __DIR__ . "/conexion.php";

// Verificar que el usuario haya iniciado sesión.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$mensaje = "";
$error = "";

// Token de seguridad para los formularios.
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

try {
    // Procesar las operaciones del carrito.
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $token = $_POST["csrf_token"] ?? "";

        if (!is_string($token) ||
            !hash_equals($_SESSION["csrf_token"], $token)) {
            throw new RuntimeException(
                "Formulario vencido. Recarga la página e inténtalo nuevamente."
            );
        }

        $accion = $_POST["accion"] ?? "";

        // AGREGAR LIBRO
        if ($accion === "agregar") {
            $libro_id = filter_var(
                $_POST["libro_id"] ?? null,
                FILTER_VALIDATE_INT
            );
            $cantidad = filter_var(
                $_POST["cantidad"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$libro_id || !$cantidad || $cantidad < 1) {
                throw new RuntimeException(
                    "Selecciona un libro y una cantidad válida."
                );
            }

            $conexion->beginTransaction();

            $stmt = $conexion->prepare(
                "SELECT ID, titulo, precio, cantidad_inventario
                 FROM LIBROS WHERE ID = ? FOR UPDATE"
            );
            $stmt->execute([$libro_id]);
            $libro = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$libro) {
                throw new RuntimeException(
                    "El libro seleccionado no existe."
                );
            }

            $stmt = $conexion->prepare(
                "SELECT ID, cantidad
                 FROM CARRITO
                 WHERE ID_usuario = ? AND ID_libro = ?"
            );
            $stmt->execute([$usuario_id, $libro_id]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);

            $cantidad_final = $cantidad +
                (int)($existente["cantidad"] ?? 0);

            if ($cantidad_final > (int)$libro["cantidad_inventario"]) {
                throw new RuntimeException(
                    "No hay suficiente inventario. Disponibles: " .
                    (int)$libro["cantidad_inventario"]
                );
            }

            $subtotal = (float)$libro["precio"] * $cantidad_final;

            if ($existente) {
                $stmt = $conexion->prepare(
                    "UPDATE CARRITO
                     SET cantidad = ?, monto_total = ?
                     WHERE ID = ? AND ID_usuario = ?"
                );
                $stmt->execute([
                    $cantidad_final,
                    $subtotal,
                    $existente["ID"],
                    $usuario_id
                ]);
            } else {
                $stmt = $conexion->prepare(
                    "INSERT INTO CARRITO
                    (ID_usuario, ID_libro, cantidad, monto_total)
                    VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([
                    $usuario_id,
                    $libro_id,
                    $cantidad_final,
                    $subtotal
                ]);
            }

            $conexion->commit();
            $mensaje = "Libro agregado correctamente al carrito.";
        }

        // ACTUALIZAR CANTIDAD
        elseif ($accion === "actualizar") {
            $carrito_id = filter_var(
                $_POST["carrito_id"] ?? null,
                FILTER_VALIDATE_INT
            );
            $cantidad = filter_var(
                $_POST["cantidad"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$carrito_id || !$cantidad || $cantidad < 1) {
                throw new RuntimeException(
                    "Ingresa una cantidad válida."
                );
            }

            $conexion->beginTransaction();

            $stmt = $conexion->prepare(
                "SELECT C.ID_libro, L.precio, L.cantidad_inventario
                 FROM CARRITO C
                 INNER JOIN LIBROS L ON L.ID = C.ID_libro
                 WHERE C.ID = ? AND C.ID_usuario = ?
                 FOR UPDATE"
            );
            $stmt->execute([$carrito_id, $usuario_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new RuntimeException(
                    "No se encontró el producto en tu carrito."
                );
            }

            if ($cantidad > (int)$item["cantidad_inventario"]) {
                throw new RuntimeException(
                    "La cantidad supera el inventario disponible."
                );
            }

            $subtotal = (float)$item["precio"] * $cantidad;

            $stmt = $conexion->prepare(
                "UPDATE CARRITO
                 SET cantidad = ?, monto_total = ?
                 WHERE ID = ? AND ID_usuario = ?"
            );
            $stmt->execute([
                $cantidad,
                $subtotal,
                $carrito_id,
                $usuario_id
            ]);

            $conexion->commit();
            $mensaje = "Cantidad actualizada correctamente.";
        }

        // ELIMINAR PRODUCTO
        elseif ($accion === "eliminar") {
            $carrito_id = filter_var(
                $_POST["carrito_id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$carrito_id) {
                throw new RuntimeException("Producto no válido.");
            }

            $stmt = $conexion->prepare(
                "DELETE FROM CARRITO
                 WHERE ID = ? AND ID_usuario = ?"
            );
            $stmt->execute([$carrito_id, $usuario_id]);

            $mensaje = $stmt->rowCount() > 0
                ? "Producto eliminado del carrito."
                : "El producto ya no estaba en el carrito.";
        }

        // CONFIRMAR PEDIDO
        elseif ($accion === "comprar") {
            $conexion->beginTransaction();

            $stmt = $conexion->prepare(
                "SELECT C.ID_libro, C.cantidad,
                        L.titulo, L.precio, L.cantidad_inventario
                 FROM CARRITO C
                 INNER JOIN LIBROS L ON L.ID = C.ID_libro
                 WHERE C.ID_usuario = ?
                 ORDER BY L.ID
                 FOR UPDATE"
            );
            $stmt->execute([$usuario_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$items) {
                throw new RuntimeException(
                    "No puedes confirmar un carrito vacío."
                );
            }

            $total_pedido = 0;

            foreach ($items as $item) {
                if ((int)$item["cantidad"] >
                    (int)$item["cantidad_inventario"]) {
                    throw new RuntimeException(
                        "Inventario insuficiente para: " .
                        $item["titulo"]
                    );
                }

                $total_pedido +=
                    (float)$item["precio"] * (int)$item["cantidad"];
            }

            $stmt = $conexion->prepare(
                "INSERT INTO PEDIDOS (ID_usuario, monto_total)
                 VALUES (?, ?)"
            );
            $stmt->execute([$usuario_id, $total_pedido]);

            $pedido_id = (int)$conexion->lastInsertId();

            foreach ($items as $item) {
                $precio = (float)$item["precio"];
                $cantidad = (int)$item["cantidad"];
                $subtotal = $precio * $cantidad;

                $stmt = $conexion->prepare(
                    "INSERT INTO DETALLE_PEDIDO
                    (ID_pedido, ID_libro, titulo_libro,
                     precio_unitario, cantidad, subtotal)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $pedido_id,
                    $item["ID_libro"],
                    $item["titulo"],
                    $precio,
                    $cantidad,
                    $subtotal
                ]);

                $stmt = $conexion->prepare(
                    "UPDATE LIBROS
                     SET cantidad_inventario =
                         cantidad_inventario - ?
                     WHERE ID = ?
                       AND cantidad_inventario >= ?"
                );
                $stmt->execute([
                    $cantidad,
                    $item["ID_libro"],
                    $cantidad
                ]);

                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException(
                        "No se pudo actualizar el inventario."
                    );
                }
            }

            $stmt = $conexion->prepare(
                "DELETE FROM CARRITO WHERE ID_usuario = ?"
            );
            $stmt->execute([$usuario_id]);

            $conexion->commit();

            $mensaje = "Pedido N.º " . $pedido_id .
                " registrado correctamente. Total: $" .
                number_format($total_pedido, 0, ",", ".");
        } else {
            throw new RuntimeException(
                "La operación solicitada no es válida."
            );
        }
    }
} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    if ($e instanceof RuntimeException) {
        $error = $e->getMessage();
    } else {
        error_log("Error en carrito.php: " . $e->getMessage());
        $error = "Ocurrió un error al procesar la operación. " .
                 "Revisa la configuración de la base de datos.";
    }
}

// Cargar libros disponibles.
try {
    $stmt = $conexion->query(
        "SELECT ID, titulo, autor, precio, cantidad_inventario
         FROM LIBROS
         WHERE cantidad_inventario > 0
         ORDER BY titulo"
    );
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cargar productos del carrito del usuario.
    $stmt = $conexion->prepare(
        "SELECT C.ID, C.ID_libro, C.cantidad,
                L.titulo, L.autor, L.precio,
                L.cantidad_inventario
         FROM CARRITO C
         INNER JOIN LIBROS L ON L.ID = C.ID_libro
         WHERE C.ID_usuario = ?
         ORDER BY C.ID DESC"
    );
    $stmt->execute([$usuario_id]);
    $carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_carrito = 0;

    foreach ($carrito as $item) {
        $total_carrito +=
            (float)$item["precio"] * (int)$item["cantidad"];
    }
} catch (Throwable $e) {
    error_log("Error al consultar carrito: " . $e->getMessage());
    $libros = [];
    $carrito = [];
    $total_carrito = 0;
    $error = "No se pudieron cargar los libros. Comprueba que las tablas existan.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrito | Librería Mundo de Letras</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="carrito.php">
            Librería Mundo de Letras
        </a>

        <span class="navbar-text text-white">
            Bienvenido,
            <?= escapar($_SESSION["usuario_nombre"] ?? "Cliente") ?>
        </span>
    </div>
</nav>

<main class="container py-4">
    <h1 class="mb-4">Mi carrito de compras</h1>

    <?php if ($mensaje !== ""): ?>
        <div class="alert alert-success">
            <?= escapar($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert alert-danger">
            <?= escapar($error) ?>
        </div>
    <?php endif; ?>

    <section class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            Seleccionar libros
        </div>

        <div class="card-body">
            <?php if (count($libros) > 0): ?>
                <form method="POST" action="carrito.php"
                      id="formAgregar">

                    <input type="hidden" name="csrf_token"
                           value="<?= escapar($_SESSION["csrf_token"]) ?>">
                    <input type="hidden" name="accion" value="agregar">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="libro_id" class="form-label">
                                Libro disponible
                            </label>

                            <select name="libro_id" id="libro_id"
                                    class="form-select" required>
                                <option value="">-- Selecciona un libro --</option>

                                <?php foreach ($libros as $libro): ?>
                                    <option
                                        value="<?= (int)$libro["ID"] ?>"
                                        data-precio="<?= escapar($libro["precio"]) ?>"
                                        data-stock="<?= (int)$libro["cantidad_inventario"] ?>">

                                        <?= escapar($libro["titulo"]) ?>
                                        - <?= escapar($libro["autor"]) ?>
                                        | $<?= number_format(
                                            (float)$libro["precio"], 0, ",", "."
                                        ) ?>
                                        | Stock: <?= (int)$libro["cantidad_inventario"] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="cantidad" class="form-label">
                                Cantidad
                            </label>
                            <input type="number" name="cantidad"
                                   id="cantidad" class="form-control"
                                   min="1" value="1" required>
                        </div>

                        <div class="col-md-2">
                            <button type="submit"
                                    class="btn btn-primary w-100">
                                Agregar
                            </button>
                        </div>
                    </div>

                    <p class="mt-3 mb-0">
                        Subtotal estimado:
                        <strong id="totalEstimado">$0</strong>
                    </p>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mb-0">
                    No hay libros con inventario disponible.
                    Registra libros desde el formulario de administración
                    o revisa la tabla LIBROS en phpMyAdmin.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card shadow-sm">
        <div class="card-header fw-bold">
            Productos agregados
        </div>

        <div class="card-body">
            <?php if (count($carrito) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Libro</th>
                                <th>Precio unitario</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($carrito as $item): ?>
                            <tr>
                                <td>
                                    <?= escapar($item["titulo"]) ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= escapar($item["autor"]) ?>
                                    </small>
                                </td>

                                <td>
                                    $<?= number_format(
                                        (float)$item["precio"], 0, ",", "."
                                    ) ?>
                                </td>

                                <td>
                                    <form method="POST" action="carrito.php"
                                          class="formActualizar d-flex gap-2">

                                        <input type="hidden" name="csrf_token"
                                               value="<?= escapar($_SESSION["csrf_token"]) ?>">
                                        <input type="hidden" name="accion"
                                               value="actualizar">
                                        <input type="hidden" name="carrito_id"
                                               value="<?= (int)$item["ID"] ?>">

                                        <input type="number" name="cantidad"
                                               class="form-control cantidad-carrito"
                                               min="1"
                                               max="<?= (int)$item["cantidad_inventario"] ?>"
                                               value="<?= (int)$item["cantidad"] ?>"
                                               style="width: 85px" required>

                                        <button type="submit"
                                                class="btn btn-outline-primary btn-sm">
                                            Actualizar
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    $<?= number_format(
                                        (float)$item["precio"] *
                                        (int)$item["cantidad"],
                                        0, ",", "."
                                    ) ?>
                                </td>

                                <td>
                                    <form method="POST" action="carrito.php"
                                          class="formEliminar">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= escapar($_SESSION["csrf_token"]) ?>">
                                        <input type="hidden" name="accion"
                                               value="eliminar">
                                        <input type="hidden" name="carrito_id"
                                               value="<?= (int)$item["ID"] ?>">

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">
                                    TOTAL
                                </th>
                                <th colspan="2">
                                    $<?= number_format(
                                        $total_carrito, 0, ",", "."
                                    ) ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <form method="POST" action="carrito.php"
                      id="formComprar">
                    <input type="hidden" name="csrf_token"
                           value="<?= escapar($_SESSION["csrf_token"]) ?>">
                    <input type="hidden" name="accion" value="comprar">

                    <button type="submit" class="btn btn-success">
                        Confirmar pedido
                    </button>
                </form>
            <?php else: ?>
                <p class="mb-0">
                    Tu carrito está vacío. Selecciona un libro y pulsa
                    <strong>Agregar</strong> para comenzar.
                </p>
            <?php endif; ?>
        </div>
    </section>

    <div class="mt-4">
        <a href="login.php" class="btn btn-outline-secondary">
            Volver al inicio de sesión
        </a>
    </div>
</main>

<script src="carrito.js"></script>
</body>
</html>
