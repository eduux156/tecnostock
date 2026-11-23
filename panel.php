<?php
session_start();

// Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

require 'conexion.php';

// Obtener el término de búsqueda si existe
$buscar = $_GET['buscar'] ?? '';

// Obtener los productos y el nombre de su categoría
try {
    $query = "SELECT p.*, c.nombre AS categoria_nombre 
              FROM producto p 
              LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
              WHERE p.estado = 1";
              
    // Si hay búsqueda, agregamos los filtros
    if (!empty($buscar)) {
        $query .= " AND (p.nombre LIKE :buscar OR p.codigo LIKE :buscar OR c.nombre LIKE :buscar)";
    }
    
    $stmt = $pdo->prepare($query);
    
    // Ejecutar con o sin el parámetro de búsqueda
    if (!empty($buscar)) {
        $stmt->execute([':buscar' => "%$buscar%"]);
    } else {
        $stmt->execute();
    }
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar los productos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Barra de navegación -->
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">TecnoStock</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Hola, <?= htmlspecialchars($_SESSION['nombre_usuario']) ?></span>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Listado de Productos</h2>
        <a href="nuevo_producto.php" class="btn btn-success">+ Nuevo Producto</a>
    </div>

    <!-- Buscador -->
    <form method="GET" action="panel.php" class="d-flex mb-3">
        <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por código, nombre o categoría..." value="<?= htmlspecialchars($buscar) ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
        <?php if (!empty($buscar)): ?>
            <a href="panel.php" class="btn btn-outline-secondary ms-2">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla de inventario -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['codigo']) ?></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                                <td>
                                    <!-- Lógica para la alerta de stock bajo -->
                                    <?php if ($p['stock_actual'] < $p['stock_minimo']): ?>
                                        <span class="badge bg-danger fs-6">
                                            <?= htmlspecialchars($p['stock_actual']) ?> (¡Bajo!)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success fs-6">
                                            <?= htmlspecialchars($p['stock_actual']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="editar_producto.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="movimiento.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-warning">Stock</a>
                                    <a href="desactivar_producto.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que deseas desactivar este producto? Ya no aparecerá en el listado.');">Desactivar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay productos registrados o que coincidan con la búsqueda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>