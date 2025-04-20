<?php
// --- Configuración de la Base de Datos ---
define('DB_HOST', 'localhost'); 
define('DB_NAME', 'libreria');
define('DB_USER', 'root'); 
define('DB_PASS', ''); 
define('DB_CHARSET', 'utf8mb4');

// --- Función para Conectar usando PDO ---
function connectDB() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
         error_log("Error de conexión a BD: " . $e->getMessage());
         throw new PDOException("Error de conexión con la base de datos. Por favor, intente más tarde.", (int)$e->getCode());
    }
}
?>