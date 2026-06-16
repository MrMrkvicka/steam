<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<!-- Dynamic Breadcrumbs using helper -->
<div class="mb-4">
    <?= $steamHelper->generateBreadcrumbs([
        'Hry' => 'games',
        $game['name'] => null
    ]) ?>
</div>

<!-- Main Game Details Banner -->
<div class="card bg-dark text-light border-0 shadow-lg overflow-hidden mb-4" style="background-color: var(--steam-bg-card) !important;">
    <div class="row g-0">
        <!-- Media / Header Column -->
        <div class="col-md-6 bg-black d-flex align-items-center justify-content-center" style="min-height: 300px;">
            <img src="<?= esc($steamHelper->getGameImage($game)) ?>" alt="<?= esc($game['name']) ?>" class="img-fluid w-100" style="max-height: 450px; object-fit: cover;" onerror="this.onerror=null;this.src='https://via.placeholder.com/460x215.png?text=Bez+obrazku';">
        </div>
        
        <!-- Quick Info Column -->
        <div class="col-md-6 d-flex flex-column justify-content-between p-4">
            <div>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h1 class="fw-bold text-white mb-0 fs-2"><?= esc($game['name']) ?></h1>
                    <div class="d-flex gap-2 align-items-center">
                        <?php 
                        $inLibrary = in_array($game['id'], session()->get('library') ?? []);
                        if ($inLibrary): 
                        ?>
                            <a href="<?= base_url('library/toggle/' . $game['id']) ?>" class="btn btn-success btn-sm py-1 px-3">
                                <i class="fas fa-bookmark me-1"></i>V knihovně
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('library/toggle/' . $game['id']) ?>" class="btn btn-steam-outline btn-sm py-1 px-3">
                                <i class="far fa-bookmark me-1"></i>Do knihovny
                            </a>
                        <?php endif; ?>

                        <?php if (session()->get('isLoggedIn')): ?>
                            <div class="btn-group">
                                <a href="<?= base_url('games/edit/' . $game['id']) ?>" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>Upravit
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-delete-trigger" 
                                        data-id="<?= $game['id'] ?>" data-name="<?= esc($game['name']) ?>">
                                    <i class="fas fa-trash-alt me-1"></i>Smazat
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="text-info small mb-3">AppID: <?= esc($game['id']) ?></p>
                
                <!-- Genres Badges (M:N loaded) -->
                <div class="mb-4">
                    <?php if (!empty($genres) && is_array($genres)): ?>
                        <?php foreach ($genres as $gName): ?>
                            <span class="badge bg-dark border border-secondary text-light p-2 me-1 mb-1">
                                <i class="fas fa-tag me-1 text-info"></i><?= esc($gName) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted small">Žádné žánry.</span>
                    <?php endif; ?>
                </div>

                <!-- Game Specific Attributes (JOINed/Direct) -->
                <div class="row g-3">
                    <div class="col-6">
                        <span class="text-muted d-block small">Vývojář</span>
                        <strong class="text-white"><?= esc($game['developer']) ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block small">Vydavatel</span>
                        <strong class="text-white"><?= esc($game['publisher'] ?? 'Není k dispozici') ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block small">Datum vydání</span>
                        <strong class="text-white"><i class="far fa-calendar-alt me-1"></i><?= date('j. n. Y', strtotime($game['release_date'])) ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block small">Cena</span>
                        <strong><?= $steamHelper->formatPrice((float)$game['price']) ?></strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block small">Platformy</span>
                        <div><?= $steamHelper->formatPlatforms($game['platforms']) ?></div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block small">Dosažené úspěchy (Achievements)</span>
                        <strong class="text-white"><i class="fas fa-trophy text-warning me-1"></i><?= esc($game['achievements']) ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top border-secondary">
                <span class="text-muted small d-block mb-1">Věková hranice</span>
                <?php if ($game['required_age'] > 0): ?>
                    <span class="badge bg-danger fs-6"><?= esc($game['required_age']) ?>+ let</span>
                <?php else: ?>
                    <span class="badge bg-success fs-6">Pro všechny věkové kategorie</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Game Descriptions & Requirements Rows (JOIN checks) -->
<div class="row g-4 mb-4">
    <!-- Description Column (WYSIWYG compatible) -->
    <div class="col-lg-8">
        <div class="card bg-dark text-light border border-secondary shadow-sm p-4 h-100" style="background-color: var(--steam-bg-card) !important;">
            <h3 class="border-bottom border-secondary pb-2 mb-3 text-info fw-bold">O hře</h3>
            <div class="detailed-desc text-light lh-lg">
                <!-- If raw HTML contains WYSIWYG outputs, render them safe but raw -->
                <?= $game['detailed_description'] ?>
            </div>
        </div>
    </div>
    
    <!-- Requirements Column -->
    <div class="col-lg-4">
        <div class="card bg-dark text-light border border-secondary shadow-sm p-4 h-100" style="background-color: var(--steam-bg-card) !important;">
            <h3 class="border-bottom border-secondary pb-2 mb-3 text-info fw-bold"><i class="fas fa-desktop me-2"></i>Požadavky na PC</h3>
            <div class="pc-requirements text-muted small lh-lg">
                <?= nl2br($game['pc_requirements'] ?? 'Minimální požadavky nebyly definovány.') ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal window delete trigger duplicate for code reuse compatibility -->
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
        
        document.querySelectorAll('.btn-delete-trigger').forEach(button => {
            button.addEventListener('click', function() {
                const gameId = this.getAttribute('data-id');
                const gameName = this.getAttribute('data-name');
                
                deleteGameName.textContent = gameName;
                deleteGameForm.action = `<?= base_url('games/delete/') ?>/${gameId}`;
                deleteModal.show();
            });
        });
    });
</script>
<?= $this->endSection() ?>
