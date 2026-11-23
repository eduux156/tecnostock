<?php
session_start();
require 'conexion.php'; // Llamamos a tu conexión

$error = '';

// Si el usuario envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    if (empty($correo) || empty($clave)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            // Consulta preparada con PDO para evitar Inyección SQL
            $stmt = $pdo->prepare("SELECT id_usuario, nombre, clave, estado FROM usuario WHERE correo = :correo LIMIT 1");
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe, está activo (estado = 1) y si la clave coincide
            if ($usuario && $usuario['estado'] == 1 && password_verify($clave, $usuario['clave'])) {
                // Credenciales correctas: guardamos datos en la sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre'];
                
                // Redirigir al panel principal (que crearemos luego)
                header("Location: panel.php");
                exit;
            } else {
                $error = 'Correo o contraseña incorrectos, o usuario inactivo.';
            }
        } catch (PDOException $e) {
            $error = 'Error al validar el usuario: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock - Iniciar Sesión</title>
    <!-- Incluyendo Bootstrap por CDN como pide el caso -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <h3 class="text-center mb-4">Acceso a TecnoStock</h3>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <div class="mb-3">
            <label for="correo" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" required>
        </div>
        <div class="mb-3">
            <label for="clave" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="clave" name="clave" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
    </form>
</div>

</body>
</html>