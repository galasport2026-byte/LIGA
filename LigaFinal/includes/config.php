<?php
// ============================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// ============================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'liga_mfm');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/ligamfm');

date_default_timezone_set('America/Bogota');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CONEXIÓN PDO
// ============================================
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $conn = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================
function limpiar($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function esta_logueado() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function redirigir($url) {
    header("Location: $url");
    exit();
}

function obtener_config($conn) {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM edit");
    $cfg  = [];
    foreach ($stmt->fetchAll() as $row) {
        $cfg[$row['setting_key']] = $row['setting_value'];
    }
    return $cfg;
}
?>
