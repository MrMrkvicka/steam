<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Steam Databáze') ?></title>
    <!-- Bootstrap 5 Dark/Steam Styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts Outfit & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --steam-bg-darker: #171a21;
            --steam-bg-dark: #1b2838;
            --steam-bg-card: #16202d;
            --steam-blue: #66c0f4;
            --steam-blue-hover: #417a9b;
            --steam-light: #c7d5e0;
            --steam-green: #5c7e10;
        }
        
        body {
            background-color: var(--steam-bg-dark);
            color: var(--steam-light);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            color: #ffffff;
        }

        .navbar {
            background-color: var(--steam-bg-darker) !important;
            border-bottom: 2px solid var(--steam-blue);
        }

        .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
            color: #ffffff !important;
        }

        .btn-steam-blue {
            background-color: var(--steam-blue);
            color: var(--steam-bg-darker);
            font-weight: 600;
            border: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-steam-blue:hover {
            background-color: #ffffff;
            color: var(--steam-bg-darker);
            transform: translateY(-1px);
        }

        .btn-steam-outline {
            border: 1px solid var(--steam-blue);
            color: var(--steam-blue);
            font-weight: 600;
            background: transparent;
            transition: all 0.2s ease-in-out;
        }

        .btn-steam-outline:hover {
            background-color: var(--steam-blue);
            color: var(--steam-bg-darker);
        }

        .btn-steam-green {
            background-color: var(--steam-green);
            color: #ffffff;
            font-weight: 600;
            border: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-steam-green:hover {
            background-color: #7ba21d;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .game-card {
            background-color: var(--steam-bg-card);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4), 0 0 10px rgba(102, 192, 244, 0.2);
            border-color: rgba(102, 192, 244, 0.3);
        }

        .game-card-img-wrapper {
            position: relative;
            padding-top: 56.25%; /* 16:9 ratio */
            overflow: hidden;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background-color: #000;
        }

        .game-card-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .game-card:hover .game-card-img {
            transform: scale(1.05);
        }

        .breadcrumb {
            background-color: var(--steam-bg-card) !important;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-stats {
            background: linear-gradient(135deg, var(--steam-bg-card) 0%, #1c2c3e 100%);
            border: 1px solid rgba(102, 192, 244, 0.1);
        }

        footer {
            background-color: var(--steam-bg-darker);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: var(--steam-bg-darker);
        }
        ::-webkit-scrollbar-thumb {
            background: #2a3f5a;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--steam-blue);
        }
        /* Custom Pagination Styling */
        .pagination {
            margin-bottom: 0;
            gap: 5px;
        }
        .pagination .page-item .page-link {
            background-color: var(--steam-bg-darker);
            border-color: rgba(255, 255, 255, 0.1);
            color: var(--steam-light);
            transition: all 0.2s ease;
        }
        .pagination .page-item .page-link:hover {
            background-color: var(--steam-blue);
            color: var(--steam-bg-darker);
            border-color: var(--steam-blue);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--steam-blue);
            border-color: var(--steam-blue);
            color: var(--steam-bg-darker);
            font-weight: bold;
        }
        .pagination .page-item.disabled .page-link {
            background-color: var(--steam-bg-card);
            border-color: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.2);
        }
        
        /* High Legibility text overrides for dark theme */
        .text-muted {
            color: #a2b3c4 !important;
        }
        .text-secondary {
            color: #b0c4d6 !important;
        }
        .game-card {
            color: var(--steam-light);
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= base_url('games') ?>">
                <i class="fab fa-steam me-2 text-info fs-3"></i>
                <span>STEAM DB</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-nav-item">
                        <a class="nav-link <?= url_is('/') ? 'active text-info' : '' ?>" href="<?= base_url('/') ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Nástěnka
                        </a>
                    </li>
                    <li class="nav-nav-item">
                        <a class="nav-link <?= url_is('stats') ? 'active text-info' : '' ?>" href="<?= base_url('stats') ?>">
                            <i class="fas fa-chart-bar me-1"></i>Statistiky
                        </a>
                    </li>
                    <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-nav-item">
                        <a class="nav-link text-success <?= url_is('games/create') ? 'active fw-bold' : '' ?>" href="<?= base_url('games/create') ?>">
                            <i class="fas fa-plus-circle me-1"></i>Přidat hru
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if (session()->get('isLoggedIn')): ?>
                        <span class="text-light me-3">
                            <i class="fas fa-user-circle text-info me-1"></i>
                            <strong><?= esc(session()->get('username')) ?></strong>
                        </span>
                        <a href="<?= base_url('logout') ?>" class="btn btn-steam-outline btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i>Odhlásit se
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('login') ?>" class="btn btn-steam-blue btn-sm">
                            <i class="fas fa-sign-in-alt me-1"></i>Přihlásit se
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        
        <!-- Alerts (Bootstrap alert windows) -->
        <?php if (session()->has('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #2e7d32; color: #fff;">
                <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Zavřít"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #c62828; color: #fff;">
                <i class="fas fa-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Zavřít"></button>
            </div>
        <?php endif; ?>

        <!-- Content injection point -->
        <?= $this->renderSection('content') ?>

    </div>

    <!-- Footer -->
    <footer class="footer py-3 bg-dark text-center">
        <div class="container text-muted">
            <small>&copy; <?= date('Y') ?> - Steam Databáze Projekt. Vytvořeno s pomocí CodeIgniter 4 & Bootstrap 5.</small>
        </div>
    </footer>

    <!-- Bootstrap 5 bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts Injection -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
