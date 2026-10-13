
<?php
require_once "sesion.php";
exigirSesion();

$mensaje = $_GET["mensaje"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar libro - Librería Mundo de Letras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow mx-auto" style="max-width: 600px;">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">Registrar un libro</h1>

            <?php if ($mensaje === "ok"): ?>
                <div class="alert alert-success">
                    El libro se registró correctamente.
                </div>
            <?php elseif ($mensaje === "error"): ?>
                <div class="alert alert-danger">
                    No se pudo registrar el libro. Revisa los datos.
                </div>
            <?php endif; ?>

            <form action="guardar_libro.php" method="POST">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título del libro</label>
                    <input type="text" class="form-control" id="titulo"
                           name="titulo" maxlength="150" required>
                </div>

                <div class="mb-3">
                    <label for="autor" class="form-label">Autor</label>
                    <input type="text" class="form-control" id="autor"
                           name="autor" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label for="precio" class="form-label">Precio en pesos chilenos</label>
                    <input type="number" class="form-control" id="precio"
                           name="precio" min="1" step="1" required>
                </div>

                <div class="mb-3">
                    <label for="cantidad_inventario" class="form-label">
                        Cantidad disponible
                    </label>
                    <input type="number" class="form-control"
                           id="cantidad_inventario"
                           name="cantidad_inventario"
                           min="0" step="1" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Guardar libro
                </button>
            </form>

            <a href="index.php" class="btn btn-secondary w-100 mt-2">
                Volver al inicio
            </a>
        </div>
    </div>
</div>

</body>
</html>
```