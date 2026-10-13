<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigirSesion(): void
{
    if (empty($_SESSION["usuario_id"])) {
        header("Location: login.php");
        exit;
    }
}
?>
```