<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/services/AIService.php';

$ai = new AIService();
$userId = 1;

$questions = [
    'Ada berapa jumlah ruangan di kampus?',
    'Ada berapa ruangan yang tersedia?',
    'Berapa total fasilitas yang terdata?',
    'Ada berapa jenis fasilitas?',
    'Berapa ruangan yang sedang diperbaiki?',
    'Tolong jelaskan daftar ruangan yang tersedia.'
];

echo "=== HASIL PENGUJIAN PHP AISERVICE ===" . PHP_EOL . PHP_EOL;

foreach ($questions as $q) {
    $res = $ai->generateResponse($q, $userId);
    echo "Pertanyaan : " . $q . PHP_EOL;
    echo "Intent     : " . $res['intent'] . PHP_EOL;
    echo "Jawaban    : " . PHP_EOL . $res['response'] . PHP_EOL;
    echo "--------------------------------------------------------" . PHP_EOL;
}
