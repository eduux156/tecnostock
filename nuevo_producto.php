<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}
require 'conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock_actual = intval($_POST['stock_actual']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $id_categoria = intval($_POST['id_categoria']);

    // Validación en el servidor (PHP)
    if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0) {
        $mensaje = "Error: El precio y el stock no pueden ser negativos.";
        $tipo_mensaje = "danger";
    } else {
        try {
            // Consulta preparada para evitar inyección SQL
            $sql = "INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria) 
                    VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, :id_categoria)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':precio' => $precio,
                ':stock_actual' => $stock_actual,
                ':stock_minimo' => $stock_minimo,
                ':id_categoria' => $id_categoria
            ]);
            $mensaje = "Producto registrado con éxito.";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            // Manejo de error si el código ya existe (Regla de negocio)
            if ($e->getCode() == 23000) { 
                $mensaje = "Error: El código de producto '$codigo' ya está en uso.";
            } else {
                $mensaje = "Error en la base de datos: " . $e->getMessage();
            }
            $tipo_mensaje = "danger";
        }
    }
}

// Obtener las categorías para llenar el menú desplegable
$stmt_cat = $pdo->query("SELECT id_categoria, nombre FROM categoria WHERE estado = 1");
$categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TecnoStock - Nuevo Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Registrar Nuevo Producto</h5>
                    <a href="panel.php" class="btn btn-sm btn-outline-light">Volver al Inventario</a>
                </div>
                <div class="card-body">
                    
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipo_mensaje ?>" role="alert">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario con validación JS en el evento onsubmit -->
                    <form method="POST" action="nuevo_producto.php" onsubmit="return validarFormulario()">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Código del Producto *</label>
                                <input type="text" class="form-control" name="codigo" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" name="id_categoria" required>
                                <option value="">Seleccione una categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="2"></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Precio ($) *</label>
                                <input type="number" class="form-control" id="precio" name="precio" min="0" step="1" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Inicial *</label>
                                <input type="number" class="form-control" id="stock_actual" name="stock_actual" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Mínimo *</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Guardar Producto</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación en el cliente (JavaScript)
function validarFormulario() {
    let precio = document.getElementById('precio').value;
    let stock_actual = document.getElementById('stock_actual').value;
    let stock_minimo = document.getElementById('stock_minimo').value;

    if (precio < 0 || stock_actual < 0 || stock_minimo < 0) {
        alert("El precio y el stock no pueden ser números negativos.");
        return false; // Detiene el envío del formulario
    }
    return true; // Permite el envío
}
</script>

</body>
</html>