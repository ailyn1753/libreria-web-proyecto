<?php
session_start();
require_once 'db_connect.php';

// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Debes iniciar sesión para proceder al pago.";
    header("Location: login.php?redirect=checkout"); // Guardamos a dónde ir después del login
    exit;
}
$user_id = $_SESSION['user_id'];

// Variables
$items_carrito = [];
$monto_total_carrito = 0;
$error_checkout = null;
$pdo = null; // Inicializar fuera del try

try {
    $pdo = connectDB();

    // 2. Obtener items del carrito y datos del libro (precio actual, stock)
    $sql_carrito = "SELECT
                        c.ID as CarritoID, c.cantidad,
                        l.ID as LibroID, l.titulo, l.precio as precio_unitario, l.cantidad_inventario as stock_actual
                    FROM CARRITO c
                    JOIN LIBROS l ON c.ID_libro = l.ID
                    WHERE c.ID_usuario = :user_id";
    $stmt_carrito = $pdo->prepare($sql_carrito);
    $stmt_carrito->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt_carrito->execute();
    $items_carrito = $stmt_carrito->fetchAll();

    // 3. Verificar si el carrito está vacío
    if (empty($items_carrito)) {
        $_SESSION['cart_feedback'] = ['success' => false, 'message' => 'Tu carrito está vacío. No puedes proceder al pago.'];
        header('Location: carrito_form.php');
        exit;
    }

    // Calcular monto total y verificar stock preliminarmente
    foreach ($items_carrito as $item) {
        if ($item['cantidad'] > $item['stock_actual']) {
            // No hay suficiente stock para este item
            $error_checkout = "Lo sentimos, no hay suficiente stock para el libro '" . htmlspecialchars($item['titulo']) . "'. Stock disponible: " . $item['stock_actual'] . ". Por favor, ajusta la cantidad en tu carrito.";
            // Romper el bucle, no se puede continuar
            break;
        }
        $monto_total_carrito += $item['precio_unitario'] * $item['cantidad'];
    }

    // --- PROCESAMIENTO DEL FORMULARIO DE CHECKOUT (CUANDO SE ENVÍA) ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error_checkout)) { // Solo procesar si no hubo error de stock inicial

        // Simulación simple: Asumimos que la dirección está en el perfil o no se requiere
        $direccion_envio = "Dirección del perfil o predeterminada"; // Obtener de $_POST si hubiera un form
        $metodo_pago = $_POST['metodo_pago'] ?? 'No especificado'; // Obtener del form

        // --- INICIO DE TRANSACCIÓN ---
        $pdo->beginTransaction();

        try {
            // --- PASO 1: Re-verificar stock y bloquear filas ---
            $stock_suficiente = true;
            foreach ($items_carrito as $item) {
                $sql_check_stock = "SELECT cantidad_inventario FROM LIBROS WHERE ID = :libro_id FOR UPDATE"; // FOR UPDATE bloquea la fila
                $stmt_check_stock = $pdo->prepare($sql_check_stock);
                $stmt_check_stock->execute([':libro_id' => $item['LibroID']]);
                $stock_actual_locked = $stmt_check_stock->fetchColumn();

                if ($item['cantidad'] > $stock_actual_locked) {
                    $stock_suficiente = false;
                    $error_checkout = "Stock insuficiente para '" . htmlspecialchars($item['titulo']) . "' al momento de confirmar. Intenta de nuevo.";
                    break; // Salir del bucle
                }
            }

            if (!$stock_suficiente) {
                 // Si el stock falló DENTRO de la transacción, revertir
                 $pdo->rollBack();
                 // El error ya está en $error_checkout
            } else {
                 // --- PASO 2: Crear el Pedido ---
                 $sql_insert_pedido = "INSERT INTO PEDIDOS (ID_usuario, monto_total, estado, direccion_envio, metodo_pago)
                                       VALUES (:user_id, :monto_total, :estado, :direccion, :metodo_pago)";
                 $stmt_insert_pedido = $pdo->prepare($sql_insert_pedido);
                 $stmt_insert_pedido->execute([
                     ':user_id' => $user_id,
                     ':monto_total' => $monto_total_carrito,
                     ':estado' => 'procesando', // Estado inicial
                     ':direccion' => $direccion_envio,
                     ':metodo_pago' => $metodo_pago
                 ]);
                 $pedido_id = $pdo->lastInsertId(); // Obtener ID del pedido recién creado

                 // --- PASO 3: Crear Detalles del Pedido y Actualizar Inventario ---
                 $sql_insert_detalle = "INSERT INTO DETALLES_PEDIDO (ID_pedido, ID_libro, cantidad, precio_unitario)
                                        VALUES (:pedido_id, :libro_id, :cantidad, :precio)";
                 $stmt_insert_detalle = $pdo->prepare($sql_insert_detalle);

                 $sql_update_stock = "UPDATE LIBROS SET cantidad_inventario = cantidad_inventario - :cantidad
                                      WHERE ID = :libro_id";
                 $stmt_update_stock = $pdo->prepare($sql_update_stock);

                 foreach ($items_carrito as $item) {
                     // Insertar detalle
                     $stmt_insert_detalle->execute([
                         ':pedido_id' => $pedido_id,
                         ':libro_id' => $item['LibroID'],
                         ':cantidad' => $item['cantidad'],
                         ':precio' => $item['precio_unitario'] // Guardar precio al momento de la compra
                     ]);

                     // Actualizar stock
                     $stmt_update_stock->execute([
                         ':cantidad' => $item['cantidad'],
                         ':libro_id' => $item['LibroID']
                     ]);
                 }

                 // --- PASO 4: Vaciar el carrito ---
                 $sql_delete_carrito = "DELETE FROM CARRITO WHERE ID_usuario = :user_id";
                 $stmt_delete_carrito = $pdo->prepare($sql_delete_carrito);
                 $stmt_delete_carrito->execute([':user_id' => $user_id]);

                 // --- PASO 5: Confirmar Transacción ---
                 $pdo->commit();

                 // --- PASO 6: Redirigir a página de confirmación ---
                 $_SESSION['pedido_confirmado_id'] = $pedido_id;
                 header("Location: confirmacion_pedido.php");
                 exit;
            } // Fin del else ($stock_suficiente)

        } catch (PDOException $e) {
            // Si hubo un error DURANTE la transacción, revertir
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error DB Checkout: " . $e->getMessage());
            $error_checkout = "Ocurrió un error al procesar tu pedido. Por favor, inténtalo de nuevo.";
        } // Fin del try/catch interno de la transacción

    } // Fin del if ($_SERVER["REQUEST_METHOD"] == "POST")


} catch (PDOException $e) {
    error_log("Error DB Checkout (conexión/obtener carrito): " . $e->getMessage());
    $error_checkout = "No se pudo cargar la información del carrito. Intenta más tarde.";
    // No mostrar el checkout si falla la carga inicial
    $items_carrito = []; // Vaciar para que no se muestre la tabla
} finally {
    $pdo = null; // Asegurar que la conexión se cierre
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Librería Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body>
    <div class="container mt-4 mb-5">
        <h1>Finalizar Compra</h1>

        <?php if ($error_checkout): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_checkout); ?>
                <?php if (strpos($error_checkout, 'ajusta la cantidad') !== false): ?>
                     <a href="carrito_form.php" class="alert-link">Volver al carrito</a>.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($items_carrito) && empty($error_checkout)): ?>
            <div class="row">
                <div class="col-md-8">
                    <h2>Resumen del Pedido</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Precio Unit.</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_carrito as $item):
                                $subtotal = $item['precio_unitario'] * $item['cantidad'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                    <td>$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
                                    <td><?php echo $item['cantidad']; ?></td>
                                    <td>$<?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$<?php echo number_format($monto_total_carrito, 0, ',', '.'); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="col-md-4">
                     <h2>Confirmar y Pagar</h2>
                     <div class="card">
                         <div class="card-body">
                             <h5 class="card-title">Información de Pago (Simulado)</h5>
                             <form action="checkout.php" method="POST">
                                 <div class="mb-3">
                                     <label for="metodo_pago" class="form-label">Método de Pago:</label>
                                     <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                                         <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                         <option value="Webpay Simulado">Webpay Simulado</option>
                                         <option value="Efectivo (Retiro)">Efectivo (Retiro en tienda)</option>
                                     </select>
                                 </div>
                                 <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg" <?php echo $error_checkout ? 'disabled' : ''; ?>>Confirmar Pedido</button>
                                 </div>
                                  <p class="form-text mt-2">Al confirmar, se verificará el stock final y se procesará tu pedido.</p>
                             </form>
                         </div>
                     </div>
                </div>
            </div>
        <?php elseif (empty($error_checkout)): ?>
             <div class="alert alert-info">No hay items en tu carrito para procesar. <a href="libros.php">Ver libros</a>.</div>
        <?php endif; ?>

         <p class="mt-4"><a href="carrito_form.php">Volver al carrito</a> | <a href="index.php">Seguir comprando</a></p>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>