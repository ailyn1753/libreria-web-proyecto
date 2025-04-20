<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Librería Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>    
        body, html { height: 100%; }
        .main-container { min-height: calc(100vh - 112px); display: flex; align-items: center; } */
    </style>
</head>
<body>

    <header class="bg-primary text-white text-center p-3">
        <h1>Librería Online</h1>
        </nav>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Iniciar Sesión</h2>

                        <?php
                        // Mostrar mensaje de error si existe (enviado desde procesar_login.php)
                        if (isset($_SESSION['error_login'])) {
                            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_login']) . '</div>';
                            unset($_SESSION['error_login']); // Limpiar el mensaje después de mostrarlo
                        }
                        // Mensaje de éxito de registro
                        if (isset($_SESSION['success_registro'])) {
                            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_registro']) . '</div>';
                            unset($_SESSION['success_registro']);
                        }
                        ?>

                        <form id="loginForm" action="procesar_login.php" method="POST" novalidate>

                            <div class="mb-3">
                                <label for="emailLogin" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="emailLogin" name="email" required placeholder="tu@email.com">
                                <div class="invalid-feedback" id="emailError">
                                    Por favor, ingresa un correo válido.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="passwordLogin" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="passwordLogin" name="password" required placeholder="Tu contraseña">
                                 <div class="invalid-feedback" id="passwordError">
                                    La contraseña es requerida (mínimo 8 caracteres).
                                </div>
                                <div id="passwordStrength" class="form-text mt-1"></div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                                <label class="form-check-label" for="rememberMe">Recordarme</label>
                            </div>

                            <div class="d-grid"> <button type="submit" class="btn btn-primary btn-lg">Ingresar</button>
                            </div>

                             <p class="mt-4 text-center">
                                ¿No tienes cuenta? <a href="registro_usuario.php">Regístrate aquí</a>
                            </p>
                             </form>
                    </div> </div> </div> </div> </div> <footer class="bg-light text-center text-lg-start mt-auto">
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
    <script src="js/loginValidation.js"></script>
</body>
</html>