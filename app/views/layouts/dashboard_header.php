<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="base-url" content="<?= rtrim(base_url(), '/') ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= htmlspecialchars($title ?? 'Dashboard - Fasilitas STMIK IKMI') ?></title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom Style -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

    <style>
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background-color: #0f172a;
            color: #94a3b8;
            flex-shrink: 0;
            transition: all 0.3s ease;
            z-index: 1040;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            font-size: 0.885rem;
            padding: 0.65rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }
        .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #0d47a1;
            font-weight: 600;
        }
        .sidebar-heading {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 1rem 1rem 0.4rem;
        }
        .content-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background-color: #f8fafc;
        }
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #0d47a1;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: -260px;
            }
            .sidebar.show {
                left: 0;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }
    </style>
</head>
<body class="bg-light">
<div class="dashboard-wrapper">
