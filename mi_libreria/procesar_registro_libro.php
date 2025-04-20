<?php
require_once 'check_admin.php';
require_once 'db_connect.php';

// Variables para guardar datos del formulario y errores
$form_data = [];
$validation_errors = [];
$success_message = null;
$error_message = null;

// Recuperar datos de sesión si venimos de un error
if (isset($_SESSION['form_data_libro'])) {
    $form_data = $_SESSION['form_data_libro'];
    unset($_SESSION['form_data_libro']);
}
if (isset($_SESSION['validation_errors_libro'])) {
    $validation_errors = $_SESSION['validation_errors_libro'];
    unset($_SESSION['validation_errors_libro']);
}
if (isset($_SESSION['error_message_libro'])) {
    $error_message = $_SESSION['error_message_libro'];
    unset($_SESSION['error_message_libro']);
}
if (isset($_SESSION['success_message_libro'])) {
    $success_message = $_SESSION['success_message_libro'];
    unset($_SESSION['success_message_libro']);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recuperar y limpiar datos del POST
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $cantidad = trim($_POST['cantidad'] ?? '');
    // Recuperar campos opcionales si los añades al formulario
    $descripcion = trim($_POST['descripcion'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');

    $form_data = $_POST; // Guardar para rellenar en caso de error

    // --- Validación del lado del servidor ---
    if (empty($titulo)) {
        $validation_errors['titulo'] = "El título es requerido.";
    }
    if (empty($autor)) {
        $validation_errors['autor'] = "El autor es requerido.";
    }
    if (empty($precio) || !is_numeric($precio) || $precio < 0) {
        $validation_errors['precio'] = "El precio debe ser un número válido y no negativo.";
    }
    if (empty($cantidad) || !filter_var($cantidad, FILTER_VALIDATE_INT) || $cantidad < 0) {
        $validation_errors['cantidad'] = "La cantidad debe ser un número entero no negativo.";
    }
     // Validación opcional para ISBN
     if (!empty($isbn) && !preg_match('/^[0-9-]{10,17}$/', $isbn)) { 
         $validation_errors['isbn'] = "El ISBN no tiene un formato válido.";
     }


    // --- Procesamiento si no hay errores de validación ---
    if (empty($validation_errors)) {
        try {
            $pdo = connectDB();

            if (!empty($isbn)) {
                 $sql_check_isbn = "SELECT ID FROM LIBROS WHERE isbn = :isbn LIMIT 1";
                 $stmt_check_isbn = $pdo->prepare($sql_check_isbn);
                 $stmt_check_isbn->bindParam(':isbn', $isbn, PDO::PARAM_STR);
                 $stmt_check_isbn->execute();
                 if ($stmt_check_isbn->fetch()) {
                     $_SESSION['error_message_libro'] = "Error: Ya existe un libro con ese ISBN.";
                     $_SESSION['form_data_libro'] = $form_data;
                     header('Location: registro_libro.php');
                     exit;
                 }
            }


            // Preparar la sentencia SQL para insertar
            $sql_insert = "INSERT INTO LIBROS (titulo, autor, precio, cantidad_inventario, descripcion, isbn)
                           VALUES (:titulo, :autor, :precio, :cantidad, :descripcion, :isbn)";
            $stmt_insert = $pdo->prepare($sql_insert);

            // Bind de parámetros
            $stmt_insert->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt_insert->bindParam(':autor', $autor, PDO::PARAM_STR);
            $stmt_insert->bindParam(':precio', $precio); // PDO detecta el tipo
            $stmt_insert->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt_insert->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt_insert->bindParam(':isbn', $isbn, PDO::PARAM_STR); // Asegúrate de que sea NULL si está vacío


            // Ejecutar la inserción
            $stmt_insert->execute();

            // Éxito
            $_SESSION['success_message_libro'] = "¡Libro '" . htmlspecialchars($titulo) . "' registrado exitosamente!";
            header('Location: registro_libro.php');
            exit;

        } catch (PDOException $e) {
            error_log("Error DB Registro Libro: " . $e->getMessage());
            $_SESSION['error_message_libro'] = "Ocurrió un error al registrar el libro (DB). Intente más tarde.";
            $_SESSION['form_data_libro'] = $form_data;
            header('Location: registro_libro.php');
            exit;
        } finally {
            $pdo = null; // Cerrar la conexión
        }
    } else {
        // Si hay errores de validación, guardar datos y errores en sesión y redirigir
        $_SESSION['form_data_libro'] = $form_data;
        $_SESSION['validation_errors_libro'] = $validation_errors;
        $_SESSION['error_message_libro'] = "Por favor, corrige los errores indicados.";
        header('Location: registro_libro.php');
        exit;
    }
} else {
    header('Location: registro_libro.php');
    exit;
}
?>