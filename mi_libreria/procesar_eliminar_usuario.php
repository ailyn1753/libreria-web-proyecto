<?php
require_once 'check_admin.php';
require_once 'db_connect.php';
// Verificar que se recibió un ID de usuario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {

    $user_id_to_delete = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);

    // Validar que el ID sea un entero válido
    if ($user_id_to_delete === false || $user_id_to_delete <= 0) {
        $_SESSION['message'] = 'ID de usuario inválido.';
        $_SESSION['message_type'] = 'warning';
        header('Location: admin_gestionar_usuarios.php');
        exit;
    }

    try {
        $pdo = connectDB();

        // Preparar y ejecutar la sentencia DELETE para el usuario
        $sql_delete_user = "DELETE FROM USUARIOS WHERE ID = :user_id";
        $stmt_delete_user = $pdo->prepare($sql_delete_user);
        $stmt_delete_user->bindParam(':user_id', $user_id_to_delete, PDO::PARAM_INT);
        $stmt_delete_user->execute();

        // Verificar si se eliminó alguna fila
        if ($stmt_delete_user->rowCount() > 0) {
            $_SESSION['message'] = 'Usuario eliminado correctamente.';
            $_SESSION['message_type'] = 'success';
        } else {
            // Esto puede pasar si el ID no existía (quizás ya fue borrado)
            $_SESSION['message'] = 'No se encontró el usuario para eliminar (ID: ' . $user_id_to_delete . ').';
            $_SESSION['message_type'] = 'warning';
        }

    } catch (PDOException $e) {
        error_log("Error al eliminar usuario: " . $e->getMessage());
        // Verificar si es un error de restricción de clave foránea (si el usuario tiene pedidos, etc.)
        if ($e->getCode() == '23000') { // Código SQLSTATE para violación de integridad
             $_SESSION['message'] = 'Error: No se puede eliminar el usuario porque tiene datos asociados (ej. pedidos). Considere desactivar la cuenta en lugar de eliminarla.';
        } else {
             $_SESSION['message'] = 'Ocurrió un error en la base de datos al intentar eliminar el usuario.';
        }
        $_SESSION['message_type'] = 'danger';

    } finally {
        $pdo = null;
    }

} else {
    // Si no se accedió vía POST o falta el user_id
    $_SESSION['message'] = 'Solicitud inválida.';
    $_SESSION['message_type'] = 'danger';
}

// Redirigir de vuelta a la página de gestión de usuarios
header('Location: admin_gestionar_usuarios.php');
exit;
?>