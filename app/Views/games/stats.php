<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h1 class="fw-bold text-white mb-1"><i class="fas fa-chart-bar text-info me-2"></i>Statistiky Databáze</h1>
        <p class="text-muted mb-0">Přehled agregovaných statistik a nejčastějších žánrů v naší databázi.</p>
    </div>
</div>

<!-- Dynamic Breadcrumbs -->
<div class="mb-4">
    <?= $steamHelper->generateBreadcrumbs(['Statistiky' => null]) ?>
</div>

<!-- Aggregated KPI Cards Section -->
<div class="row g-4 mb-5">
    
    <!-- Total games (COUNT aggregation) -->
    <div class="col-md-4">
        <div class="card card-stats p-4 text-center">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <div class="bg-dark p-3 rounded-circle border border-info shadow-sm text-info" style="width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-gamepad fs-3"></i>
                </div>
            </div>
            <h5 class="text-muted mb-1 text-uppercase small">Celkem her v DB</h5>
            <h2 class="text-white fw-bold mb-0 display-5"><?= esc($totalGames) ?></h2>
            <p class="text-info small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Bez započítání smazaných her</p>
        </div>
    </div>

    <!-- Average price (AVG aggregation) -->
    <div class="col-md-4">
        <div class="card card-stats p-4 text-center">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <div class="bg-dark p-3 rounded-circle border border-warning shadow-sm text-warning" style="width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-euro-sign fs-3"></i>
                </div>
            </div>
            <h5 class="text-muted mb-1 text-uppercase small">Průměrná cena hry</h5>
            <h2 class="text-warning fw-bold mb-0 display-5"><?= number_format($avgPrice, 2, '.', ' ') ?> €</h2>
            <p class="text-muted small mt-2 mb-0"><i class="fas fa-info-circle me-1"></i>Vypočítáno z aktivních titulů</p>
        </div>
    </div>

    <!-- Maximum achievements game (MAX/ORDER achievements aggregation) -->
    <div class="col-md-4">
        <div class="card card-stats p-4 text-center">
            <div class="d-flex align-items-center justify-content-center mb-3">
                <div class="bg-dark p-3 rounded-circle border border-success shadow-sm text-success" style="width: 65px; height: 65px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-trophy fs-3"></i>
                </div>
            </div>
            <h5 class="text-muted mb-1 text-uppercase small">Nejvíce achievementů</h5>
            <h2 class="text-white fw-bold mb-1 fs-3 text-truncate" title="<?= esc($maxAchievementsGame['name'] ?? 'N/A') ?>">
                <?= esc($maxAchievementsGame['name'] ?? 'N/A') ?>
            </h2>
            <p class="text-success small mb-0 fw-bold">
                <i class="fas fa-star me-1 text-warning"></i><?= esc($maxAchievementsGame['achievements'] ?? 0) ?> achievementů
            </p>
        </div>
    </div>

</div>

<!-- Genres Game Count Distribution Section (GROUP BY & JOIN) -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card bg-dark text-light border border-secondary shadow-sm p-4" style="background-color: var(--steam-bg-card) !important;">
            <h3 class="border-bottom border-secondary pb-2 mb-4 text-info fw-bold">
                <i class="fas fa-tags me-2"></i>Top 10 Nejčastějších žánrů (M:N relace)
            </h3>
            
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr class="text-muted border-secondary">
                            <th scope="col" style="width: 10%;">#</th>
                            <th scope="col">Název žánru</th>
                            <th scope="col" style="width: 30%;">Počet zastoupených her</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($genresStats) && is_array($genresStats)): ?>
                            <?php $index = 1; foreach ($genresStats as $stat): ?>
                                <tr class="border-secondary">
                                    <td><strong class="text-info"><?= $index++ ?>.</strong></td>
                                    <td><span class="fw-bold text-white"><?= esc($stat['name']) ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-info text-dark fw-bold me-3" style="min-width: 45px;"><?= esc($stat['game_count']) ?></span>
                                            <div class="progress w-100 bg-secondary" style="height: 8px; border-radius: 4px;">
                                                <!-- Calculate percentage based on top game count -->
                                                <?php 
                                                $maxVal = (int)($genresStats[0]['game_count'] ?? 1);
                                                $percent = ($stat['game_count'] / $maxVal) * 100;
                                                ?>
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent ?>%; border-radius: 4px;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Žádná data nejsou k dispozici.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
