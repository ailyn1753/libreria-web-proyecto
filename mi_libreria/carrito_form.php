<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=carrito");
    exit;
}
$user_id = $_SESSION['user_id'];

// Conectar a BD
$pdo = connectDB();

// Obtener libros disponibles para el dropdown/lista
$libros_disponibles = [];
try {
    $stmt_libros = $pdo->query("SELECT ID, titulo, autor, precio FROM LIBROS WHERE cantidad_inventario > 0 ORDER BY titulo ASC");
    $libros_disponibles = $stmt_libros->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching books: " . $e->getMessage());
    // Manejar error como sea apropiado
}

 // Obtener items actuales del carrito para este usuario desde la BD
$items_carrito = [];
$monto_total_carrito = 0;
try {
     $sql_carrito = "SELECT c.ID as CarritoID, c.cantidad, l.ID as LibroID, l.titulo, l.precio
                     FROM CARRITO c
                     JOIN LIBROS l ON c.ID_libro = l.ID
                     WHERE c.ID_usuario = :user_id";
     $stmt_carrito = $pdo->prepare($sql_carrito);
     $stmt_carrito->bindParam(':user_id', $user_id, PDO::PARAM_INT);
     $stmt_carrito->execute();
     $items_carrito = $stmt_carrito->fetchAll();

     // Calcular monto total
     foreach ($items_carrito as $item) {
         $monto_total_carrito += $item['precio'] * $item['cantidad'];
     }

} catch (PDOException $e) {
     error_log("Error fetching cart items: " . $e->getMessage());
     // Manejar error
}

$pdo = null; // Cerrar conexión
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Gestionar Carrito de Compras</h1>

        <div class="card mb-4">
             <div class="card-header">Agregar Libro al Carrito</div>
             <div class="card-body">
                 <form action="procesar_carrito.php" method="POST" id="addCartForm">
                     <input type="hidden" name="action" value="add">
                     <div class="row g-3 align-items-end">
                         <div class="col-md-6">
                             <label for="libroSelect" class="form-label">Seleccionar Libro:</label>
                             <select class="form-select" id="libroSelect" name="libro_id" required>
                                 <option value="">-- Elige un libro --</option>
                                 <?php foreach ($libros_disponibles as $libro): ?>
                                     <option value="<?php echo $libro['ID']; ?>">
                                         <?php echo htmlspecialchars($libro['titulo']) . " - " . htmlspecialchars($libro['autor']) . " ($" . number_format($libro['precio'], 2) . ")"; ?>
                                     </option>
                                 <?php endforeach; ?>
                             </select>
                              <div class="invalid-feedback">Debes seleccionar un libro.</div>
                         </div>
                         <div class="col-md-3">
                             <label for="cantidadAdd" class="form-label">Cantidad:</label>
                             <input type="number" class="form-control" id="cantidadAdd" name="cantidad" value="1" min="1" required>
                              <div class="invalid-feedback">La cantidad debe ser al menos 1.</div>
                         </div>
                         <div class="col-md-3">
                             <button type="submit" class="btn btn-primary w-100">Agregar al Carrito</button>
                         </div>
                     </div>
                 </form>
             </div>
        </div>

        <div class="card">
             <div class="card-header">Tu Carrito Actual</div>
             <div class="card-body">
                 <?php if (empty($items_carrito)): ?>
                     <p>Tu carrito está vacío.</p>
                 <?php else: ?>
                     <table class="table">
                         <thead>
                             <tr>
                                 <th>Libro</th>
                                 <th>Precio Unit.</th>
                                 <th>Cantidad</th>
                                 <th>Subtotal</th>
                                 <th>Acciones</th>
                             </tr>
                         </thead>
                         <tbody>
                             <?php foreach ($items_carrito as $item):
                                 $subtotal = $item['precio'] * $item['cantidad'];
                             ?>
                                 <tr>
                                     <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                     <td>$<?php echo number_format($item['precio'], 2); ?></td>
                                     <td>
                                         <form action="procesar_carrito.php" method="POST" class="d-inline-flex align-items-center updateCartForm" style="max-width: 150px;">
                                             <input type="hidden" name="action" value="update">
                                             <input type="hidden" name="carrito_id" value="<?php echo $item['CarritoID']; ?>">
                                             <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1" class="form-control form-control-sm me-2" required style="width: 70px;">
                                             <button type="submit" class="btn btn-sm btn-outline-secondary">Actualizar</button>
                                              <div class="invalid-feedback d-block"></div> </form>
                                     </td>
                                     <td>$<?php echo number_format($subtotal, 2); ?></td>
                                     <td>
                                          <form action="procesar_carrito.php" method="POST" class="d-inline deleteCartForm">
                                             <input type="hidden" name="action" value="delete">
                                             <input type="hidden" name="carrito_id" value="<?php echo $item['CarritoID']; ?>">
                                             <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                         </form>
                                     </td>
                                 </tr>
                             <?php endforeach; ?>
                         </tbody>
                         <tfoot>
                              <tr>
                                 <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                 <td colspan="2"><strong>$<?php echo number_format($monto_total_carrito, 2); ?></strong></td>
                              </tr>
                         </tfoot>
                     </table>
                      <div class="text-end mt-3">
                         <a href="checkout.php" class="btn btn-success">Proceder al Pago</a> </div>
                 <?php endif; ?>
             </div>
         </div>

          <p class="mt-4"><a href="index.php">Seguir comprando</a> | <a href="logout.php">Cerrar Sesión</a></p>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cartValidation.js"></script>
</body>
</html>