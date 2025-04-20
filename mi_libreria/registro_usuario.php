<?php
session_start();

// Recuperar datos del formulario y errores de la sesión (si existen)
$form_data = $_SESSION['form_data'] ?? [];
$validation_errors = $_SESSION['validation_errors'] ?? [];
$error_registro = $_SESSION['error_registro'] ?? null;

// Limpiar variables de sesión después de recuperarlas
unset($_SESSION['form_data']);
unset($_SESSION['validation_errors']);
unset($_SESSION['error_registro']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Librería Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <header class="bg-primary text-white text-center p-3">
        <h1>Librería Online</h1>
        </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7"> <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Crear una Cuenta</h2>

                        <?php
                        // Mostrar error general de registro si existe
                        if ($error_registro) {
                            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_registro) . '</div>';
                        }
                        // Mostrar errores de validación específicos si existen
                        if (!empty($validation_errors)) {
                            echo '<div class="alert alert-warning" role="alert">Por favor, corrige los siguientes errores:<ul>';
                            foreach ($validation_errors as $error) {
                                echo '<li>' . htmlspecialchars($error) . '</li>';
                            }
                            echo '</ul></div>';
                        }
                        ?>

                        <form id="registerForm" action="procesar_registro_usuario.php" method="POST" novalidate>

                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" class="form-control <?php echo isset($validation_errors['nombre']) ? 'is-invalid' : ''; ?>" id="nombre" name="nombre" required placeholder="Juan Pérez" value="<?php echo htmlspecialchars($form_data['nombre'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                    <?php echo htmlspecialchars($validation_errors['nombre'] ?? 'El nombre completo es requerido.'); ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control <?php echo isset($validation_errors['email']) ? 'is-invalid' : ''; 
                                ?>" id="email" name="email" required placeholder="tu@email.com" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                                <div class="invalid-feedback">
                                     <?php echo htmlspecialchars($validation_errors['email'] ?? 'Ingresa un correo electrónico válido.'); ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control <?php echo isset($validation_errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required placeholder="Mínimo 8 caracteres">
                                <div class="invalid-feedback">
                                     <?php echo htmlspecialchars($validation_errors['password'] ?? 'La contraseña debe tener al menos 8 caracteres.'); ?>
                                </div>
                                </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección <span class="text-muted">(Opcional)</span></label>
                                <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Calle Falsa 123, Comuna" value="<?php echo htmlspecialchars($form_data['direccion'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono <span class="text-muted">(Opcional)</span></label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="+56912345678" value="<?php echo htmlspecialchars($form_data['telefono'] ?? ''); ?>">
                            </div>

                             <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-success btn-lg">Registrarse</button>
                            </div>

                            <p class="mt-4 text-center">
                                ¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión aquí</a>
                            </p>

                        </form> </div> </div> </div> </div> </div> <footer class="bg-light text-center text-lg-start mt-auto">
       <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            © <?php echo date("Y"); ?> Librería Online - Programación Web II - IACC
            | Hora: <?php
                date_default_timezone_set('America/Santiago'); // Establecer zona horaria de Chile
                echo date("H:i:s"); // Hora actual Chile
            ?>
             | Fecha: <?php echo date("d-m-Y"); ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>