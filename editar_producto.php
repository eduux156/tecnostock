<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}
require 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// 1. Cargar los datos actuales del producto si recibimos la ID
if (isset($_GET['id'])) {
    $id_producto = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = :id");
    $stmt->execute([':id' => $id_producto]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        die("Producto no encontrado.");
    }
}

// 2. Procesar la actualización si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock_actual = intval($_POST['stock_actual']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $id_categoria = intval($_POST['id_categoria']);

    // Validaciones para evitar negativos
    if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0) {
        $mensaje = "Error: El precio y el stock no pueden ser negativos.";
        $tipo_mensaje = "danger";
        $producto = $_POST; // Mantiene lo que escribiste en pantalla si hay error
    } else {
        try {
            $sql = "UPDATE producto SET nombre = :nombre, descripcion = :descripcion, 
                    precio = :precio, stock_actual = :stock_actual, stock_minimo = :stock_minimo, 
                    id_categoria = :id_categoria WHERE id_producto = :id_producto";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':precio' => $precio,
                ':stock_actual' => $stock_actual,
                ':stock_minimo' => $stock_minimo,
                ':id_categoria' => $id_categoria,
                ':id_producto' => $id_producto
            ]);
            $mensaje = "Producto actualizado con éxito.";
            $tipo_mensaje = "success";
            $producto = $_POST; 
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar en la base de datos: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}

// Cargar las categorías para el menú desplegable
$stmt_cat = $pdo->query("SELECT id_categoria, nombre FROM categoria WHERE estado = 1");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TecnoStock - Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Editar Producto</h5>
                    <a href="panel.php" class="btn btn-sm btn-outline-light">Volver al Inventario</a>
                </div>
                <div class="card-body">
                    
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipo_mensaje ?>" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="editar_producto.php?id=<?= $producto['id_producto'] ?>">
                        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Código del Producto (No modificable)</label>
                                <!-- El código es de solo lectura según las reglas -->
                                <input type="text" class="form-control" value="<?= htmlspecialchars($producto['codigo'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" name="id_categoria" required>
                                <option value="">Seleccione una categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>" <?= (isset($producto['id_categoria']) && $producto['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Precio ($) *</label>
                                <input type="number" class="form-control" name="precio" min="0" step="1" value="<?= htmlspecialchars($producto['precio'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Actual *</label>
                                <input type="number" class="form-control" name="stock_actual" min="0" value="<?= htmlspecialchars($producto['stock_actual'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Mínimo *</label>
                                <input type="number" class="form-control" name="stock_minimo" min="0" value="<?= htmlspecialchars($producto['stock_minimo'] ?? '') ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Actualizar Producto</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>