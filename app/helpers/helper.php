<?php
/**
 * Helper Functions
 * Fungsi bantuan umum untuk URL, sanitasi, redirect, dan flash message
 */

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $cleanPath = ltrim($path, '/');
        return BASE_URL . ($cleanPath ? '/' . $cleanPath : '');
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect($path = '') {
        $url = (filter_var($path, FILTER_VALIDATE_URL)) ? $path : base_url($path);
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('flash')) {
    function flash($name = '', $message = '', $class = 'alert alert-success') {
        if (!empty($name)) {
            if (!empty($message) && empty($_SESSION[$name])) {
                $_SESSION[$name] = $message;
                $_SESSION[$name . '_class'] = $class;
            } elseif (empty($message) && !empty($_SESSION[$name])) {
                $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'alert alert-success';
                echo '<div class="' . $class . ' alert-dismissible fade show" role="alert">';
                echo $_SESSION[$name];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION[$name]);
                unset($_SESSION[$name . '_class']);
            }
        }
    }
}

/**
 * Mendapatkan token CSRF sesi saat ini, atau menghasilkan token baru jika belum ada
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Merender hidden input tag untuk token CSRF
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

/**
 * Memvalidasi apakah request POST/GET/Header memiliki CSRF token yang valid
 */
if (!function_exists('csrf_validate')) {
    function csrf_validate($token = null) {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($sessionToken)) {
            return false;
        }

        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        }

        if (empty($token) || !is_string($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}

