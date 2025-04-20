<?php
session_start();
require_once 'db_connect.php'; 

$libros = []; // Inicializar array para guardar los libros
$error_db = ''; 

try {
    $pdo = connectDB();

    // Consultar los libros disponibles (con inventario > 0)
    $sql = "SELECT ID, titulo, autor, precio FROM LIBROS WHERE cantidad_inventario > 0 ORDER BY titulo ASC";
    $stmt = $pdo->query($sql);

    // Recuperar todos los libros como un array asociativo
    $libros = $stmt->fetchAll();

} catch (PDOException $e) {
    // Manejar errores de la base de datos
    error_log("Error al obtener libros: " . $e->getMessage());
    $error_db = "No se pudieron cargar los libros en este momento. Intente más tarde.";
} finally {
    $pdo = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Libros - Librería Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Estilo para las tarjetas de libro */
        .card-libro {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
    </style>
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
                        <a class="nav-link" href="index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="libros.php">Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito_form.php">Carrito</a> </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                         <li class="nav-item">
                             <span class="nav-link">Hola, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
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
        <h2>Nuestro Catálogo de Libros</h2>

        <?php if ($error_db): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_db); ?>
            </div>
        <?php elseif (empty($libros)): ?>
            <div class="alert alert-info" role="alert">
                No hay libros disponibles en este momento.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($libros as $libro): ?>
                    <div class="col">
                        <div class="card card-libro shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($libro['titulo']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($libro['autor']); ?></h6>
                                <p class="card-text">Precio: <strong>$<?php echo number_format($libro['precio'], 0, ',', '.'); ?></strong></p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <form action="procesar_carrito.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="libro_id" value="<?php echo $libro['ID']; ?>">
                                    <div class="input-group">
                                         <input type="number" name="cantidad" class="form-control" value="1" min="1" aria-label="Cantidad">
                                         <button type="submit" class="btn btn-primary">Agregar al Carrito</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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