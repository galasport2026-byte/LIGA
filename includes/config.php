<?php
// ============================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// Railway usa: MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD
// Localmente usa: DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
// ============================================
define('DB_HOST', getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('MYSQLPORT')     ?: getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'liga_mfm');
define('DB_USER', getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '');
define('SITE_URL', getenv('SITE_URL')     ?: 'http://localhost/ligamfm');

date_default_timezone_set('America/Bogota');

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ============================================
// CONEXIÓN PDO
// ============================================
try {
    $dsn = "mysql:host=" . DB_HOST
         . ";port="      . DB_PORT
         . ";dbname="    . DB_NAME
         . ";charset=utf8mb4";

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
    // En producción no mostrar detalles del error
    $esLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
    $detalle = $esLocal ? $e->getMessage() : 'Revisa la configuración del servidor.';
    die(json_encode(['error' => 'Error de conexión: ' . $detalle]));
}

// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Limpia una cadena para salida HTML segura.
 */
function limpiar($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica si el administrador está logueado.
 */
function esta_logueado() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Redirige a una URL y termina la ejecución.
 */
function redirigir($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Obtiene toda la configuración del sitio desde la tabla `edit`.
 */
function obtener_config($conn) {
    try {
        $stmt = $conn->query("SELECT setting_key, setting_value FROM edit");
        $cfg  = [];
        foreach ($stmt->fetchAll() as $row) {
            $cfg[$row['setting_key']] = $row['setting_value'];
        }
        return $cfg;
    } catch (PDOException $e) {
        return []; // Si falla, devuelve array vacío para no romper el sitio
    }
}
?>
