
<?php
session_start();
require_once "conexion.php";

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $confirmar = $_POST["confirmar"] ?? "";

    if ($nombre === "" || $email === "" || $contrasena === "" || $confirmar === "") {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ingresa un correo electrónico válido.";
    } elseif (strlen($contrasena) < 8) {
        $error = "La contraseña debe tener al menos 8 caracteres.";
    } elseif ($contrasena !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } else {
        try {
            $consulta = $conexion->prepare(
                "SELECT ID FROM usuarios WHERE email = ?"
            );
            $consulta->execute([$email]);

            if ($consulta->fetch()) {
                $error = "Este correo ya está registrado.";
            } else {
                $hash = password_hash($contrasena, PASSWORD_DEFAULT);

                $insertar = $conexion->prepare(
                    "INSERT INTO usuarios (nombre, email, contrasena)
                     VALUES (?, ?, ?)"
                );
                $insertar->execute([$nombre, $email, $hash]);

                $mensaje = "Usuario registrado correctamente. Ya puedes iniciar sesión.";
            }
        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            $error = "No se pudo completar el registro. Revisa la estructura de la tabla usuarios.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de usuarios - Librería Mundo de Letras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h1 class="h3 text-center mb-4">Crear una cuenta</h1>

                    <?php if ($mensaje !== ""): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
                            <a href="login.php">Iniciar sesión</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($error !== ""): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="registro_usuario.php">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" id="nombre"
                                   name="nombre" maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email"
                                   name="email" maxlength="150" required>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="contrasena"
                                   name="contrasena" minlength="8" required>
                        </div>

                        <div class="mb-3">
                            <label for="confirmar" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="confirmar"
                                   name="confirmar" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Registrarme
                        </button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>