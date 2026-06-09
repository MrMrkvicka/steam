<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">
        
        <!-- dynamic breadcrumbs -->
        <div class="mb-4">
            <?= (new \App\Libraries\SteamHelper())->generateBreadcrumbs(['Přihlášení' => null]) ?>
        </div>

        <div class="card bg-dark text-light border border-secondary shadow-lg">
            <div class="card-header border-bottom border-secondary text-center py-4">
                <i class="fab fa-steam text-info fs-1 mb-2"></i>
                <h3 class="fw-bold mb-0">Přihlášení do Steam DB</h3>
                <p class="text-muted small mb-0">Zadejte své přihlašovací údaje administrátora</p>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('login') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label text-light">Uživatelské jméno</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-light">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   name="username" 
                                   id="username" 
                                   class="form-control bg-dark border-secondary text-light" 
                                   placeholder="Zadejte uživatelské jméno" 
                                   value="<?= old('username') ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-light">Heslo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary border-secondary text-light">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="form-control bg-dark border-secondary text-light" 
                                   placeholder="Zadejte heslo" 
                                   required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-steam-blue py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Přihlásit se
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer border-top border-secondary text-center text-muted py-3 bg-dark">
                <small>Výchozí přihlašovací údaje: <code class="text-info">admin</code> / <code class="text-info">admin123</code></small>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
