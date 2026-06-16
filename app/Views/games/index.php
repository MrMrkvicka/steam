<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Header / Actions Section -->
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h1 class="fw-bold text-white mb-1"><i class="fas fa-gamepad text-info me-2"></i>Katalog her</h1>
        <p class="text-muted mb-0">Prohlížejte a spravujte nejpopulárnější hry z platformy Steam.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <?php if (session()->get('isLoggedIn')): ?>
            <a href="<?= base_url('games/add') ?>" class="btn btn-steam-green py-2 px-4 shadow">
                <i class="fas fa-plus-circle me-2"></i>Přidat novou hru
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Dynamic Breadcrumbs using helper -->
<div class="mb-4">
    <?= $steamHelper->generateBreadcrumbs(['Hry' => null]) ?>
</div>

<!-- Filter Buttons Section -->
<div class="mb-4 d-flex flex-wrap gap-3 align-items-center justify-content-between p-3 rounded" style="background-color: var(--steam-bg-card); border: 1px solid rgba(255, 255, 255, 0.05);">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="text-muted small me-2"><i class="fas fa-filter text-info"></i> Filtrovat hry:</span>
        <a href="<?= base_url('games' . (!empty($search) ? '?search=' . urlencode($search) : '')) ?>" class="btn <?= empty($activeFilter) ? 'btn-steam-blue' : 'btn-steam-outline' ?> btn-sm px-3">
            Všechny hry
        </a>
        <a href="<?= base_url('games?filter=library' . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="btn <?= $activeFilter === 'library' ? 'btn-success text-white' : 'btn-steam-outline' ?> btn-sm px-3">
            <i class="fas fa-bookmark me-1"></i>Moje knihovna
        </a>
        <a href="<?= base_url('games?filter=created' . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" class="btn <?= $activeFilter === 'created' ? 'btn-warning text-dark fw-bold' : 'btn-steam-outline' ?> btn-sm px-3">
            <i class="fas fa-plus-circle me-1"></i>Moje přidané (vytvořené)
        </a>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-3">
        <!-- Search form -->
        <form action="<?= base_url('games') ?>" method="get" class="d-flex align-items-center">
            <?php if (!empty($activeFilter)): ?>
                <input type="hidden" name="filter" value="<?= esc($activeFilter) ?>">
            <?php endif; ?>
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control bg-dark border-secondary text-light" placeholder="Hledat podle názvu..." value="<?= esc($search ?? '') ?>" style="max-width: 220px;">
                <button class="btn btn-steam-blue" type="submit" title="Hledat">
                    <i class="fas fa-search"></i>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?= base_url('games' . (!empty($activeFilter) ? '?filter=' . $activeFilter : '')) ?>" class="btn btn-outline-danger d-flex align-items-center justify-content-center" title="Vymazat vyhledávání">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if (!empty($activeFilter) || !empty($search)): ?>
            <div>
                <a href="<?= base_url('games') ?>" class="text-danger text-decoration-none small" title="Zrušit všechny filtry a vyhledávání">
                    Zrušit vše <i class="fas fa-times-circle ms-1"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Games Grid Section -->
<div class="row g-4">
    <?php if (!empty($games) && is_array($games)): ?>
        <?php foreach ($games as $game): ?>
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

                        <div class="mb-3">
                            <!-- Display genres -->
                            <?php 
                            $genreList = explode(';', $game['genres']);
                            foreach (array_slice($genreList, 0, 2) as $g): 
                                if (!empty($g)):
                            ?>
                                    <span class="badge bg-dark border border-secondary text-light me-1"><?= esc(trim($g)) ?></span>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>

                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <!-- Helper Price Formatting -->
                            <?= $steamHelper->formatPrice((float)$game['price']) ?>

                            <a href="<?= base_url('games/show/' . $game['id'] . '/' . url_title($game['name'])) ?>" class="btn btn-steam-outline btn-sm">
                                Detail <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>

                    <?php if (session()->get('isLoggedIn')): ?>
                        <!-- Admin controls floating overlays -->
                        <div class="card-footer bg-dark border-top border-secondary d-flex justify-content-between py-2 px-3">
                            <a href="<?= base_url('games/edit/' . $game['id']) ?>" class="btn btn-outline-warning btn-sm border-0 py-1 px-2">
                                <i class="fas fa-edit me-1"></i>Upravit
                            </a>
                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm border-0 py-1 px-2 btn-delete-trigger" 
                                    data-id="<?= $game['id'] ?>" 
                                    data-name="<?= esc($game['name']) ?>">
                                <i class="fas fa-trash-alt me-1"></i>Smazat
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-search text-muted fs-1 mb-3"></i>
            <h4 class="text-white">Žádné hry nebyly nalezeny</h4>
            <p class="text-muted">Zkuste databázi znovu nainstalovat a nasadit seedy.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination Links (Stránkování) -->
<?php if ($pager): ?>
    <div class="d-flex justify-content-center mt-5">
        <div class="bg-dark p-2 rounded shadow-sm border border-secondary">
            <?= $pager->links() ?>
        </div>
    </div>
<?php endif; ?>

<!-- Confirmation Modal Window for Soft-delete (Required) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border border-danger">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-danger fw-bold" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Potvrdit smazání hry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Zavřít"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-1">Opravdu si přejete smazat hru <strong id="deleteGameName" class="text-white"></strong>?</p>
                <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1 text-info"></i>Tato operace využívá soft-delete. Hra bude skryta v rozhraní, ale zůstane uložená s časovým razítkem smazání.</p>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary py-2 px-3" data-bs-dismiss="modal">Zrušit</button>
                <form id="deleteGameForm" action="" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger py-2 px-4">
                        <i class="fas fa-trash-alt me-2"></i>Ano, smazat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        const deleteGameName = document.getElementById('deleteGameName');
        const deleteGameForm = document.getElementById('deleteGameForm');
        
        // Find all delete buttons
        document.querySelectorAll('.btn-delete-trigger').forEach(button => {
            button.addEventListener('click', function() {
                const gameId = this.getAttribute('data-id');
                const gameName = this.getAttribute('data-name');
                
                // Update text and form action dynamically
                deleteGameName.textContent = gameName;
                deleteGameForm.action = `<?= base_url('games/delete/') ?>/${gameId}`;
                
                // Show modal
                deleteModal.show();
            });
        });
    });
</script>
<?= $this->endSection() ?>
