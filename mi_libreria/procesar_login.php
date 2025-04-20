<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password_ingresada = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($email) && !empty($password_ingresada)) {
        try {
            $pdo = connectDB();

            // Consultar la BD por el email, incluyendo el rol
            $sql = "SELECT ID, nombre, email, contraseña, rol FROM USUARIOS WHERE email = :email LIMIT 1"; // Añadido 'rol'
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password_ingresada, $usuario['contraseña'])) {
                session_regenerate_id(true);

                // Guardar datos del usuario en la sesión
                $_SESSION['user_id'] = $usuario['ID'];
                $_SESSION['user_nombre'] = $usuario['nombre'];
                $_SESSION['user_email'] = $usuario['email'];
                $_SESSION['user_rol'] = $usuario['rol']; // <-- Guardar el rol

                // Redirigir a la página principal
                header("Location: index.php");
                exit;

            } else {
                $_SESSION['error_login'] = "Correo electrónico o contraseña incorrectos.";
                header("Location: login.php");
                exit;
            }

        } catch (PDOException $e) {
            error_log("Error DB Login: " . $e->getMessage());
            $_SESSION['error_login'] = "Error al intentar iniciar sesión (DB). Intente más tarde.";
            header("Location: login.php");
            exit;
        } finally {
             $pdo = null;
        }

    } else {
        $_SESSION['error_login'] = "Por favor, completa ambos campos.";
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>