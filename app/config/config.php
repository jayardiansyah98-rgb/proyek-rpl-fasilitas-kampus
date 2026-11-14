<?php
/**
 * Konfigurasi Utama Aplikasi & Database MySQL
 * Sistem Informasi Pendataan Fasilitas Setiap Ruangan di Kampus STMIK IKMI Cirebon
 */

// Direktori Path Sistem
define('ROOT_PATH', str_replace('\\', '/', dirname(dirname(__DIR__))));
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads');

// Muat environment variables dari .env jika file tersedia
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$envKey, $envVal] = explode('=', $line, 2);
            $envKey = trim($envKey);
            $envVal = trim($envVal);
            // Hapus tanda kutip ganda atau tunggal pembungkus
            if ((str_starts_with($envVal, '"') && str_ends_with($envVal, '"')) ||
                (str_starts_with($envVal, "'") && str_ends_with($envVal, "'"))) {
                $envVal = substr($envVal, 1, -1);
            }
            if (!array_key_exists($envKey, $_SERVER) && !array_key_exists($envKey, $_ENV)) {
                putenv("{$envKey}={$envVal}");
                $_ENV[$envKey] = $envVal;
                $_SERVER[$envKey] = $envVal;
            }
        }
    }
}

// Konfigurasi Database MySQL
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_fasilitas_ikmi');
define('DB_CHARSET', 'utf8mb4');

// Konfigurasi Aplikasi
define('APP_NAME', 'Sistem Informasi Fasilitas STMIK IKMI Cirebon');
define('APP_VERSION', '1.0.0');

// Deteksi Otomatis Base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (php_sapi_name() === 'cli' || empty($_SERVER['SCRIPT_NAME'])) {
    $baseUrl = 'http://localhost/Fasilitas%20Kampus/public';
} else {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $scriptDir = ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir;
    $baseUrl = rtrim($protocol . $hostName . $scriptDir, '/');
}

define('BASE_URL', $baseUrl);

// Controller & Method Default
define('DEFAULT_CONTROLLER', 'HomeController');
define('DEFAULT_METHOD', 'index');

// Konfigurasi Sesi & Keamanan
define('SESSION_TIMEOUT', 7200); // 2 Jam Masa Aktif Sesi (Inactivity Timeout)
define('APP_ENV', getenv('APP_ENV') ?: 'development'); // 'development' atau 'production'

// Konfigurasi Layanan AI Assistant (Aman via Environment / Abstraction Layer)
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: 'rule_engine'); // 'rule_engine' (default offline), 'gemini', 'openai'
define('AI_API_KEY', getenv('AI_API_KEY') ?: '');
define('AI_MODEL', getenv('AI_MODEL') ?: 'gemini-1.5-flash');

