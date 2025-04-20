<?php
// recheck_admin.php
// Colocar al inicio de CADA script que requiera permisos de administrador

// Asegurarse de que la sesión esté iniciada (si no lo está ya)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado Y si su rol es 'admin'
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    // Si no cumple, guardar mensaje de error y redirigir
    $_SESSION['error_message'] = "Acceso denegado. Se requieren permisos de administrador.";
    // Redirigir a login o a una página de acceso denegado
    header('Location: login.php');
    exit; // Detener la ejecución del script original
}

// Si el script continúa después de este punto, el usuario es un administrador validado.
?>