<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro_usuario.php");
    exit;
}

$nombre = trim($_POST["nombre"] ?? "");
$email = trim($_POST["email"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";
$confirmar = $_POST["confirmar"] ?? "";

if (
    $nombre === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($contrasena) < 8 ||
    $contrasena !== $confirmar
) {
    header("Location: registro_usuario.php?mensaje=datos_invalidos");
    exit;
}

try {
    $consulta = $conexion->prepare(
        "SELECT ID FROM usuarios WHERE email = ?"
    );
    $consulta->execute([$email]);

    if ($consulta->fetch()) {
        header("Location: registro_usuario.php?mensaje=correo_existente");
        exit;
    }

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $insertar = $conexion->prepare(
        "INSERT INTO usuarios (nombre, email, contrasena)
         VALUES (?, ?, ?)"
    );

    $insertar->execute([$nombre, $email, $hash]);

    header("Location: login.php?registro=exitoso");
    exit;

} catch (PDOException $e) {
    error_log("Error al guardar usuario: " . $e->getMessage());
    header("Location: registro_usuario.php?mensaje=error");
    exit;
}
?>