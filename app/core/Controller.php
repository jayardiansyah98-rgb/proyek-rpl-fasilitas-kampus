<?php
/**
 * Base Controller
 * Menyediakan method view renderer, model loader, dan helpers
 */

class Controller {
    /**
     * Memuat file view dengan passing data
     */
    public function view($view, $data = []) {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($viewFile)) {
            // Ekstrak data menjadi variabel lokal di view
            extract($data);
            require_once $viewFile;
        } else {
            die("View file tidak ditemukan: " . htmlspecialchars($view));
        }
    }

    /**
     * Memuat dan menginstansiasi class model
     */
    public function model($model) {
        $modelFile = __DIR__ . '/../models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Model file tidak ditemukan: " . htmlspecialchars($model));
        }
    }

    /**
     * Helper: Format waktu relatif (Baru saja, 5 mnt lalu, dll)
     */
    public function timeAgoView(string $datetime): string {
        $time = time() - strtotime($datetime);
        if ($time < 60)      return 'Baru saja';
        if ($time < 3600)    return floor($time / 60) . ' mnt lalu';
        if ($time < 86400)   return floor($time / 3600) . ' jam lalu';
        if ($time < 604800)  return floor($time / 86400) . ' hari lalu';
        return date('d M Y', strtotime($datetime));
    }
}
