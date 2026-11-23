<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}
require 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

if (!isset($_GET['id'])) {
    header("Location: panel.php");
    exit;
}
$id_producto = intval($_GET['id']);

// Cargar información básica del producto
$stmt = $pdo->prepare("SELECT nombre, stock_actual, stock_minimo FROM producto WHERE id_producto = :id");
$stmt->execute([':id' => $id_producto]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado.");
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $cantidad = intval($_POST['cantidad']);
    $observacion = trim($_POST['observacion']);
    $id_usuario = $_SESSION['id_usuario'];

    // Validar reglas de negocio
    if ($cantidad <= 0) {
        $mensaje = "Error: La cantidad debe ser mayor a cero.";
        $tipo_mensaje = "danger";
    } elseif ($tipo === 'SALIDA' && $cantidad > $producto['stock_actual']) {
        $mensaje = "Error: La salida no puede dejar el stock bajo cero. Stock actual: " . $producto['stock_actual'];
        $tipo_mensaje = "danger";
    } else {
        try {
            // Iniciar transacción para asegurar la integridad de la base de datos
            $pdo->beginTransaction();

            // 1. Insertar el registro en la tabla movimiento
            $sql_mov = "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario) 
                        VALUES (:tipo, :cantidad, :observacion, :id_producto, :id_usuario)";
            $stmt_mov = $pdo->prepare($sql_mov);
            $stmt_mov->execute([
                ':tipo' => $tipo,
                ':cantidad' => $cantidad,
                ':observacion' => $observacion,
                ':id_producto' => $id_producto,
                ':id_usuario' => $id_usuario
            ]);

            // 2. Calcular y actualizar el nuevo stock en la tabla producto
            $nuevo_stock = ($tipo === 'ENTRADA') ? $producto['stock_actual'] + $cantidad : $producto['stock_actual'] - $cantidad;
            
            $sql_prod = "UPDATE producto SET stock_actual = :nuevo_stock WHERE id_producto = :id_producto";
            $stmt_prod = $pdo->prepare($sql_prod);
            $stmt_prod->execute([
                ':nuevo_stock' => $nuevo_stock,
                ':id_producto' => $id_producto
            ]);

            // Confirmar transacción
            $pdo->commit();
            
            $mensaje = "Movimiento registrado y stock actualizado exitosamente.";
            $tipo_mensaje = "success";
            $producto['stock_actual'] = $nuevo_stock; // Actualizar variable para reflejar en pantalla
            
        } catch (PDOException $e) {
            // Si algo falla, revertimos todos los cambios
            $pdo->rollBack();
            $mensaje = "Error en la base de datos: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TecnoStock - Movimiento de Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Registrar Movimiento</h5>
                    <a href="panel.php" class="btn btn-sm btn-dark">Volver</a>
                </div>
                <div class="card-body">
                    
                    <div class="mb-4 text-center">
                        <h5><?= htmlspecialchars($producto['nombre']) ?></h5>
                        <p class="mb-0 fs-5">Stock Actual: <strong><?= htmlspecialchars($producto['stock_actual']) ?></strong></p>
                    </div>

                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipo_mensaje ?>" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="movimiento.php?id=<?= $id_producto ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Movimiento *</label>
                                <select class="form-select" name="tipo" required>
                                    <option value="ENTRADA">Entrada (Sumar)</option>
                                    <option value="SALIDA">Salida (Restar)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cantidad *</label>
                                <input type="number" class="form-control" name="cantidad" min="1" step="1" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observación (Opcional)</label>
                            <input type="text" class="form-control" name="observacion" placeholder="Ej. Ingreso de nueva factura...">
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold">Registrar y Actualizar Stock</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>