<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Pendataan Fasilitas Setiap Ruangan di Kampus STMIK IKMI Cirebon Berbasis Web dengan AI">
    <title><?= htmlspecialchars($title ?? 'Sistem Informasi Fasilitas STMIK IKMI Cirebon') ?></title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=' . (file_exists(ROOT_PATH . '/public/assets/css/style.css') ? filemtime(ROOT_PATH . '/public/assets/css/style.css') : '2.0')) ?>">
</head>
<body class="d-flex flex-column min-vh-100">
