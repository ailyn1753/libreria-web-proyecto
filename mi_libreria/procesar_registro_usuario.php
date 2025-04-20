<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Recuperación CORRECTA de datos del formulario ---
    $nombre = trim($_POST['nombre'] ?? '');
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : ''; // Guardar en minúsculas y quitar espacios
    $password = trim($_POST['password'] ?? ''); // Contraseña en texto plano
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    // --- Validación básica del lado del servidor (Ejemplo) ---
    $errores = [];
    if (empty($nombre)) {
        $errores['nombre'] = "El nombre completo es requerido.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = "El correo electrónico no es válido.";
    }
    // Validación de contraseña
    if (empty($password) || strlen($password) < 8) {
        $errores['password'] = "La contraseña es requerida y debe tener al menos 8 caracteres.";
    }

    // --- Procesamiento ---
    if (empty($errores)) {
        try {
            $pdo = connectDB();

            // --- Verificar si el email ya existe ---
            $sql_check = "SELECT ID FROM USUARIOS WHERE email = :email LIMIT 1";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_check->execute();

            if ($stmt_check->fetch()) {
                // Email ya registrado
                $_SESSION['error_registro'] = "El correo electrónico ya está registrado.";
                $_SESSION['form_data'] = $_POST; // Guardar datos para rellenar
                header('Location: registro_usuario.php');
                exit;
            }

            // --- ¡Importante! Hashear la contraseña ---
            if(!empty($password)){
                 $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            } else {
                 throw new Exception("Intento de registrar con contraseña vacía.");
            }

            // --- Insertar el nuevo usuario usando Prepared Statements ---
            $sql_insert = "INSERT INTO USUARIOS (nombre, email, contraseña, direccion, telefono) VALUES (:nombre, :email, :password, :direccion, :telefono)";
            $stmt_insert = $pdo->prepare($sql_insert);

            // Bind de parámetros CORRECTO
            $stmt_insert->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt_insert->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_insert->bindParam(':password', $hashed_password, PDO::PARAM_STR); // Usar el HASH
            $stmt_insert->bindParam(':direccion', $direccion, PDO::PARAM_STR);
            $stmt_insert->bindParam(':telefono', $telefono, PDO::PARAM_STR);

            // Ejecutar la inserción
            $stmt_insert->execute();

            // Registro exitoso
            $_SESSION['success_registro'] = "¡Usuario registrado exitosamente! Ahora puedes iniciar sesión.";
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {
            error_log("Error DB Registro Usuario: " . $e->getMessage());
            $_SESSION['error_registro'] = "Ocurrió un error al registrar el usuario (DB). Intente más tarde.";
            $_SESSION['form_data'] = $_POST;
            header('Location: registro_usuario.php');
            exit;
        } catch (Exception $e) {
             error_log("Error Registro Usuario: " . $e->getMessage());
             $_SESSION['error_registro'] = "Ocurrió un error inesperado durante el registro.";
             $_SESSION['form_data'] = $_POST; // Guardar datos para rellenar
             header('Location: registro_usuario.php');
             exit;
        } finally {
             $pdo = null; // Cerrar la conexión
        }
    } else {
        // Si hay errores de validación, redirigir de vuelta con errores y datos
        $_SESSION['form_data'] = $_POST;
        $_SESSION['validation_errors'] = $errores;
        $_SESSION['error_registro'] = "Por favor, corrige los errores indicados.";
        header('Location: registro_usuario.php');
        exit;
    }
} else {
    header('Location: registro_usuario.php');
    exit;
}
?>