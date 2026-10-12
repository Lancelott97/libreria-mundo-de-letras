
<?php
session_start();

// Eliminar todas las variables de sesión.
$_SESSION = [];

// Eliminar la cookie de sesión si existe.
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}

// Destruir la sesión.
session_destroy();

// Volver al inicio de sesión.
header("Location: login.php");
exit;
?>
