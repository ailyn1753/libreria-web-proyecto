<?php
require_once 'check_admin.php'; // Verifica que sea admin

// Recuperar mensajes y datos de sesión (si existen)
$form_data = $_SESSION['form_data_libro'] ?? [];
$validation_errors = $_SESSION['validation_errors_libro'] ?? [];
$error_message = $_SESSION['error_message_libro'] ?? null;
$success_message = $_SESSION['success_message_libro'] ?? null;

// Limpiar variables de sesión después de recuperarlas
unset($_SESSION['form_data_libro']);
unset($_SESSION['validation_errors_libro']);
unset($_SESSION['error_message_libro']);
unset($_SESSION['success_message_libro']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Libro - Librería Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4 mb-5">
         <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="admin_gestionar_usuarios.php">Gestionar Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registrar Libro</li>
            <li class="breadcrumb-item ms-auto"><a href="logout.php">Cerrar Sesión</a></li>
          </ol>
        </nav>
        <h2>Registrar Nuevo Libro (Admin: <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>)</h2>

        <?php
        // Mostrar mensajes de éxito o error
        if ($success_message) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
        }
        if ($error_message) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
        }
        if (!empty($validation_errors)) {
             echo '<div class="alert alert-warning" role="alert">Por favor, corrige los siguientes errores:<ul>';
             foreach ($validation_errors as $field => $error) {
                 echo '<li>' . htmlspecialchars(ucfirst($field)) . ': ' . htmlspecialchars($error) . '</li>';
             }
             echo '</ul></div>';
        }
        ?>

        <form action="procesar_registro_libro.php" method="POST" novalidate>
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control <?php echo isset($validation_errors['titulo']) ? 'is-invalid' : ''; ?>" id="titulo" name="titulo" required value="<?php echo htmlspecialchars($form_data['titulo'] ?? ''); ?>">
                <div class="invalid-feedback">
                    <?php echo htmlspecialchars($validation_errors['titulo'] ?? 'El título es requerido.'); ?>
                </div>
            </div>
             <div class="mb-3">
                <label for="autor" class="form-label">Autor</label>
                <input type="text" class="form-control <?php echo isset($validation_errors['autor']) ? 'is-invalid' : ''; ?>" id="autor" name="autor" required value="<?php echo htmlspecialchars($form_data['autor'] ?? ''); ?>">
                 <div class="invalid-feedback">
                    <?php echo htmlspecialchars($validation_errors['autor'] ?? 'El autor es requerido.'); ?>
                </div>
            </div>
            <div class="mb-3">
                <label for="precio" class="form-label">Precio</label>
                <input type="number" step="0.01" class="form-control <?php echo isset($validation_errors['precio']) ? 'is-invalid' : ''; ?>" id="precio" name="precio" required value="<?php echo htmlspecialchars($form_data['precio'] ?? ''); ?>" min="0">
                <div class="invalid-feedback">
                     <?php echo htmlspecialchars($validation_errors['precio'] ?? 'Ingresa un precio válido (ej: 10.99).'); ?>
                 </div>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad en Inventario</label>
                <input type="number" class="form-control <?php echo isset($validation_errors['cantidad']) ? 'is-invalid' : ''; ?>" id="cantidad" name="cantidad" required value="<?php echo htmlspecialchars($form_data['cantidad'] ?? '0'); ?>" min="0">
                 <div class="invalid-feedback">
                     <?php echo htmlspecialchars($validation_errors['cantidad'] ?? 'Ingresa una cantidad válida (entero >= 0).'); ?>
                 </div>
            </div>
             <div class="mb-3">
                <label for="isbn" class="form-label">ISBN <span class="text-muted">(Opcional, único)</span></label>
                <input type="text" class="form-control <?php echo isset($validation_errors['isbn']) ? 'is-invalid' : ''; ?>" id="isbn" name="isbn" value="<?php echo htmlspecialchars($form_data['isbn'] ?? ''); ?>" placeholder="Ej: 978-3-16-148410-0">
                 <div class="invalid-feedback">
                    <?php echo htmlspecialchars($validation_errors['isbn'] ?? 'Formato de ISBN inválido.'); ?>
                </div>
            </div>
            <div class="mb-3">
                 <label for="descripcion" class="form-label">Descripción <span class="text-muted">(Opcional)</span></label>
                 <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($form_data['descripcion'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-info">Registrar Libro</button>
            <a href="libros.php" class="btn btn-secondary">Ver Catálogo</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>