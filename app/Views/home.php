<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Hero Welcome Section -->
<div class="p-5 mb-4 rounded-3 shadow-lg" style="background: linear-gradient(135deg, rgba(27, 40, 56, 0.95) 0%, rgba(22, 32, 45, 0.95) 100%), url('https://store.cloudflare.steamstatic.com/public/images/v6/home/background_main_english.jpg') center/cover; border: 1px solid rgba(102, 192, 244, 0.15);">
    <div class="container-fluid py-3">
        <h1 class="display-5 fw-bold text-white mb-2"><i class="fab fa-steam text-info me-3"></i>Vítejte v databázi Steam</h1>
        <p class="col-md-9 fs-5 text-light opacity-75 mb-4">
            Prohlížejte katalog tisíců her, analyzujte statistiky nebo si sestavte svou vlastní osobní knihovnu oblíbených titulů.
        </p>
        <div class="d-flex flex-wrap gap-3">
            <a href="<?= base_url('games') ?>" class="btn btn-steam-blue btn-lg px-4 py-2">
                <i class="fas fa-gamepad me-2"></i>Otevřít katalog her
            </a>
            <a href="<?= base_url('stats') ?>" class="btn btn-steam-outline btn-lg px-4 py-2">
                <i class="fas fa-chart-bar me-2"></i>Statistiky databáze
            </a>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-4">
        <div class="card card-stats text-light p-3 rounded shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block">Celkem her v databázi</span>
                    <h2 class="fw-bold mb-0 text-info mt-1"><?= esc($totalGames) ?></h2>
                </div>
                <div class="bg-dark p-3 rounded-circle border border-secondary">
                    <i class="fas fa-database text-info fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card card-stats text-light p-3 rounded shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block">Průměrná cena hry</span>
                    <h2 class="fw-bold mb-0 text-warning mt-1"><?= number_format($avgPrice, 2, '.', ' ') ?> €</h2>
                </div>
                <div class="bg-dark p-3 rounded-circle border border-secondary">
                    <i class="fas fa-euro-sign text-warning fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-4">
        <div class="card card-stats text-light p-3 rounded shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small d-block">Moje uložené hry</span>
                    <h2 class="fw-bold mb-0 text-success mt-1"><?= count($myGames) ?></h2>
                </div>
                <div class="bg-dark p-3 rounded-circle border border-secondary">
                    <i class="fas fa-bookmark text-success fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Library Section -->
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-secondary pb-2">
        <h2 class="fw-bold text-white mb-0"><i class="fas fa-bookmark text-success me-2"></i>Moje knihovna</h2>
        <?php if (!empty($myGames)): ?>
            <span class="badge bg-success px-3 py-2 rounded-pill"><?= count($myGames) ?> <?= count($myGames) === 1 ? 'hra' : (count($myGames) < 5 ? 'hry' : 'her') ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($myGames)): ?>
        <div class="row g-4">
            <?php foreach ($myGames as $game): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card game-card">
                        <div class="game-card-img-wrapper">
                            <img src="<?= esc($steamHelper->getGameImage($game)) ?>" alt="<?= esc($game['name']) ?>" class="game-card-img" onerror="this.onerror=null;this.src='https://via.placeholder.com/460x215.png?text=No+Image';">
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <h5 class="card-title text-truncate fw-bold mb-1" title="<?= esc($game['name']) ?>">
                                <?= esc($game['name']) ?>
                            </h5>
                            <p class="text-muted small mb-2 text-truncate">
                                <i class="fas fa-tools me-1 text-secondary"></i><?= esc($game['developer']) ?>
                            </p>
                            
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <?= $steamHelper->formatPrice((float)$game['price']) ?>
                                <div class="btn-group">
                                    <a href="<?= base_url('games/show/' . $game['id'] . '/' . url_title($game['name'])) ?>" class="btn btn-steam-outline btn-sm">
                                        Detail
                                    </a>
                                    <a href="<?= base_url('library/toggle/' . $game['id']) ?>" class="btn btn-outline-danger btn-sm" title="Odebrat z knihovny">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 rounded bg-dark border border-secondary" style="background-color: var(--steam-bg-card) !important; border-style: dashed !important;">
            <i class="fas fa-folder-open text-muted fs-1 mb-3"></i>
            <h4 class="text-white">Vaše knihovna je prázdná</h4>
            <p class="text-muted mb-4">Přejděte do katalogu her a přidejte si své oblíbené tituly kliknutím na hvězdičku v detailu hry.</p>
            <a href="<?= base_url('games') ?>" class="btn btn-steam-blue px-4 py-2">
                <i class="fas fa-search me-2"></i>Prohlížet a přidávat hry
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Recommended Games Showcase Section -->
<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-secondary pb-2">
        <h2 class="fw-bold text-white mb-0"><i class="fas fa-percentage text-warning me-2"></i>Doporučené levné hry</h2>
        <a href="<?= base_url('games') ?>" class="text-info text-decoration-none small">Zobrazit vše <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
        <?php foreach ($featuredGames as $game): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card game-card">
                    <div class="game-card-img-wrapper">
                        <img src="<?= esc($steamHelper->getGameImage($game)) ?>" alt="<?= esc($game['name']) ?>" class="game-card-img" onerror="this.onerror=null;this.src='https://via.placeholder.com/460x215.png?text=No+Image';">
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <h5 class="card-title text-truncate fw-bold mb-1" title="<?= esc($game['name']) ?>">
                            <?= esc($game['name']) ?>
                        </h5>
                        <p class="text-muted small mb-2 text-truncate">
                            <i class="fas fa-tools me-1 text-secondary"></i><?= esc($game['developer']) ?>
                        </p>
                        
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <?= $steamHelper->formatPrice((float)$game['price']) ?>
                            <a href="<?= base_url('games/show/' . $game['id'] . '/' . url_title($game['name'])) ?>" class="btn btn-steam-outline btn-sm">
                                Detail <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?= $this->endSection() ?>
