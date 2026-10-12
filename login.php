
<?php
session_start();
require_once "conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $contrasena === "") {
        $error = "Ingresa un correo válido y tu contraseña.";
    } else {
        $sql = "SELECT ID, nombre, contrasena
                FROM USUARIOS
                WHERE email = :email
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->execute(["email" => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contrasena, $usuario["contrasena"])) {
            session_regenerate_id(true);
            $_SESSION["usuario_id"] = $usuario["ID"];
            $_SESSION["usuario_nombre"] = $usuario["nombre"];

            header("Location: carrito.php");
            exit;
        } else {
            $error = "Correo electrónico o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - Librería Mundo de Letras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card mx-auto shadow" style="max-width: 450px;">
        <div class="card-body p-4">
            <h2 class="text-center mb-4">Mundo de Letras</h2>
            <h5 class="text-center mb-4">Iniciar sesión</h5>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="email"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" id="contrasena"
                           class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Iniciar sesión
                </button>
            </form>

            <p class="text-center mt-3">
                ¿No tienes cuenta?
                <a href="registro_usuario.php">Regístrate aquí</a>
            </p>

            <p class="text-center">
                <a href="index.php">Volver al inicio</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
