<?php
session_start();
require 'conexion.php';

// Validar que exista sesión activa
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Aplicar borrado lógico si viene un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_producto = $_GET['id'];

    $sql = "UPDATE producto SET estado = 0 WHERE id_producto = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_producto]);
}

// Volver al panel principal
header("Location: panel.php");
exit();
?>