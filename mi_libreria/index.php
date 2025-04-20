<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librería Online - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

    <header class="bg-primary text-white text-center p-3">
        <h1>Librería Online</h1>
    </header>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
       <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Librería</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros.php">Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito_form.php">Carrito</a> </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                         <li class="nav-item">
                             <span class="nav-link">Hola, <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></span>
                         </li>
                         <li class="nav-item">
                            <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                         </li>
                    <?php else: ?>
                        <li class="nav-item">
                           <a href="login.php" class="nav-link">Iniciar Sesión</a>
                         </li>
                         <li class="nav-item">
                            <a class="nav-link" href="registro_usuario.php">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        <h2>Bienvenido a nuestra Librería Online</h2>
        <p>Explora nuestro catálogo y encuentra tus próximas lecturas.</p>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Novedades</h5>
                        <p class="card-text">Descubre los últimos libros añadidos a nuestro catálogo.</p>
                        <a href="libros.php" class="btn btn-info">Ver Novedades</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ofertas Especiales</h5>
                        <p class="card-text">Aprovecha descuentos exclusivos en libros seleccionados.</p>
                        <a href="#" class="btn btn-warning">Ver Ofertas</a> </div>
                </div>
            </div>
             <div class="col-md-4">
                 <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Los Más Vendidos</h5>
                        <p class="card-text">Explora los títulos preferidos por nuestros clientes.</p>
                        <a href="#" class="btn btn-success">Ver Más Vendidos</a> </div>
                </div>
            </div>
        </div>

    </main>

    <footer class="bg-light text-center text-lg-start mt-5">
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