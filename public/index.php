<?php
/**
 * Front Controller
 * Entry Point Utama Aplikasi MVC
 * Sistem Informasi Fasilitas STMIK IKMI Cirebon
 */

// Muat file konfigurasi
require_once __DIR__ . '/../app/config/config.php';

// Pengaturan Error Disclosure
if (defined('APP_ENV') && APP_ENV === 'production') {
    ini_set('display_errors', '0');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Konfigurasi Cookie Sesi yang Aman (Anti-XSS & Anti-Fixation)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Muat helper
require_once __DIR__ . '/../app/helpers/helper.php';
require_once __DIR__ . '/../app/helpers/Auth.php';

// Muat core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/App.php';

// Autoload class dari app/models/ jika belum dimuat
spl_autoload_register(function ($class) {
    $modelFile = __DIR__ . '/../app/models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
});

// Inisialisasi Routing App
$app = new App();
