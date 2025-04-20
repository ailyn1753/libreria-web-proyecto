<?php
session_start();

// Recuperar el ID del pedido de la sesión
$pedido_id = $_SESSION['pedido_confirmado_id'] ?? null;

// Limpiar la variable de sesión para que no se muestre si se recarga la página
unset($_SESSION['pedido_confirmado_id']);

// Redirigir si no hay ID de pedido (acceso directo a la página)
if (!$pedido_id) {
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - Librería Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
<body>
    <div class="container mt-5 text-center">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">¡Gracias por tu compra!</h4>
            <?php if ($pedido_id): ?>
                 <p>Tu pedido con el número <strong>#<?php echo htmlspecialchars($pedido_id); ?></strong> ha sido recibido y está siendo procesado.</p>
            <?php else: ?>
                 <p>Tu pedido ha sido recibido y está siendo procesado.</p>
            <?php endif; ?>
            <hr>
            <p class="mb-0">Recibirás una notificación por correo electrónico (si aplica) con los detalles y seguimiento.</p>
        </div>
        <a href="index.php" class="btn btn-primary mt-3">Volver a la página principal</a>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>