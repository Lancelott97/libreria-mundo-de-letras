<?php
require_once "sesion.php";
exigirSesion();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registro_libro.php");
    exit;
}

$titulo = trim($_POST["titulo"] ?? "");
$autor = trim($_POST["autor"] ?? "");
$precioTexto = $_POST["precio"] ?? "";
$inventarioTexto = $_POST["cantidad_inventario"] ?? "";

$precioValido = filter_var(
    $precioTexto,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

$inventarioValido = filter_var(
    $inventarioTexto,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 0]]
);

if (
    $titulo === "" ||
    $autor === "" ||
    strlen($titulo) > 150 ||
    strlen($autor) > 100 ||
    $precioValido === false ||
    $inventarioValido === false
) {
    header("Location: registro_libro.php?mensaje=error");
    exit;
}

try {
    $insertar = $conexion->prepare(
        "INSERT INTO libros (titulo, autor, precio, cantidad_inventario)
         VALUES (?, ?, ?, ?)"
    );

    $insertar->execute([
        $titulo,
        $autor,
        $precioValido,
        $inventarioValido
    ]);

    header("Location: registro_libro.php?mensaje=ok");
    exit;

} catch (PDOException $e) {
    error_log("Error al guardar libro: " . $e->getMessage());
    header("Location: registro_libro.php?mensaje=error");
    exit;
}
?>