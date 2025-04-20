<?php
require_once 'check_admin.php';
require_once 'db_connect.php';
$usuarios = [];
$error_db = '';

try {
    $pdo = connectDB();
    $sql = "SELECT ID, nombre, email FROM USUARIOS ORDER BY nombre ASC";
    $stmt = $pdo->query($sql);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
    $error_db = "No se pudieron cargar los usuarios.";
} finally {
    $pdo = null;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Gestionar Usuarios (Admin: <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>)</h1>
         <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gestionar Usuarios</li>
            <li class="breadcrumb-item"><a href="registro_libro.php">Registrar Libro</a></li>
            <li class="breadcrumb-item ms-auto"><a href="logout.php">Cerrar Sesión</a></li>
          </ol>
        </nav>

        <?php // Mostrar mensaje de error general si existe (ej. de check_admin)
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }

        if ($error_db) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($error_db) . '</div>';
        } elseif (empty($usuarios)) {
             echo '<div class="alert alert-info">No hay usuarios registrados (o solo administradores).</div>';
        } else {
        ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['ID']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <form action="procesar_eliminar_usuario.php" method="POST" style="display: inline;" onsubmit="return confirmarEliminacion('<?php echo htmlspecialchars(addslashes($usuario['nombre'])); ?>');">
                                    <input type="hidden" name="user_id" value="<?php echo $usuario['ID']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función JavaScript para confirmar la eliminación
        function confirmarEliminacion(nombreUsuario) {
            return confirm(`¿Estás seguro de que deseas eliminar al usuario "${nombreUsuario}"?\n¡Esta acción no se puede deshacer!`);
        }
    </script>

    <footer class="bg-light text-center text-lg-start mt-5 fixed-bottom">
       <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            Admin Panel - Librería Online | Fecha: <?php echo date("d-m-Y"); ?>
             | Hora Chile: <?php
                date_default_timezone_set('America/Santiago');
                echo date("H:i:s");
            ?>
        </div>
    </footer>

</body>
</html>