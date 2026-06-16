<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        
        <!-- Dynamic Breadcrumbs -->
        <div class="mb-4">
            <?= $steamHelper->generateBreadcrumbs([
                'Hry' => 'games',
                $game['name'] => 'games/show/' . $game['id'] . '/' . $steamHelper->slugify($game['name']),
                'Upravit' => null
            ]) ?>
        </div>

        <div class="card bg-dark text-light border border-secondary shadow-lg mb-5" style="background-color: var(--steam-bg-card) !important;">
            <div class="card-header border-bottom border-secondary py-3">
                <h2 class="fw-bold mb-0 text-warning"><i class="fas fa-edit me-2"></i>Upravit hru: <?= esc($game['name']) ?></h2>
                <p class="text-muted small mb-0">Upravte detaily hry níže.</p>
            </div>
            
            <div class="card-body p-4">
                <form action="<?= base_url('games/update/' . $game['id']) ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="current_background" value="<?= esc($game['background']) ?>">

                    <div class="row g-4 mb-4">
                        <!-- Left Block: Basic Details -->
                        <div class="col-md-6">
                            <h4 class="text-white mb-3 border-bottom border-secondary pb-1">Základní informace</h4>
                            
                            <div class="mb-3">
                                <label for="id" class="form-label text-muted">Steam AppID (Nelze změnit)</label>
                                <input type="number" id="id" class="form-control bg-dark border-secondary text-muted" value="<?= esc($game['id']) ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Název hry <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control bg-dark border-secondary text-light" placeholder="Např. Team Fortress 2" value="<?= old('name', $game['name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="release_date" class="form-label">Datum vydání <span class="text-danger">*</span></label>
                                <input type="date" name="release_date" id="release_date" class="form-control bg-dark border-secondary text-light" value="<?= old('release_date', $game['release_date']) ?>" required>
                            </div>

                            <!-- Dropdown 1: Developer (Loaded from DB, first option disabled & hidden, required) -->
                            <div class="mb-3">
                                <label for="developer" class="form-label">Vývojář (Developer) <span class="text-danger">*</span></label>
                                <select name="developer" id="developer" class="form-select bg-dark border-secondary text-light" required>
                                    <option value="" disabled hidden>-- Vyberte vývojáře --</option>
                                    <?php foreach ($developers as $dev): ?>
                                        <option value="<?= esc($dev) ?>" <?= old('developer', $game['developer']) === $dev ? 'selected' : '' ?>><?= esc($dev) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Dropdown 2: Publisher (Loaded from DB, first option disabled & hidden, required) -->
                            <div class="mb-3">
                                <label for="publisher" class="form-label">Vydavatel (Publisher) <span class="text-danger">*</span></label>
                                <select name="publisher" id="publisher" class="form-select bg-dark border-secondary text-light" required>
                                    <option value="" disabled hidden>-- Vyberte vydavatele --</option>
                                    <?php foreach ($publishers as $pub): ?>
                                        <option value="<?= esc($pub) ?>" <?= old('publisher', $game['publisher']) === $pub ? 'selected' : '' ?>><?= esc($pub) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Right Block: Numerical/Platform details -->
                        <div class="col-md-6">
                            <h4 class="text-white mb-3 border-bottom border-secondary pb-1">Specifikace</h4>

                            <div class="mb-3">
                                <label for="price" class="form-label">Cena (€) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="price" id="price" class="form-control bg-dark border-secondary text-light" placeholder="Např. 19.99" value="<?= old('price', $game['price']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="platforms" class="form-label">Platformy <span class="text-danger">*</span></label>
                                <input type="text" name="platforms" id="platforms" class="form-control bg-dark border-secondary text-light" placeholder="Např. windows;mac;linux" value="<?= old('platforms', $game['platforms']) ?>" required>
                                <div class="form-text text-muted">Oddělujte středníkem (;).</div>
                            </div>

                            <div class="mb-3">
                                <label for="achievements" class="form-label">Počet achievementů</label>
                                <input type="number" name="achievements" id="achievements" class="form-control bg-dark border-secondary text-light" placeholder="0" value="<?= old('achievements', $game['achievements']) ?>">
                            </div>

                            <div class="mb-3">
                                <label for="required_age" class="form-label">Věková hranice (Age rating)</label>
                                <input type="number" name="required_age" id="required_age" class="form-control bg-dark border-secondary text-light" placeholder="0" value="<?= old('required_age', $game['required_age']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Lokalizace</label>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="english" id="english_yes" value="1" <?= (int)old('english', $game['english']) === 1 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="english_yes">Angličtina</label>
                                </div>
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="english" id="english_no" value="0" <?= (int)old('english', $game['english']) === 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="english_no">Ostatní</label>
                                </div>
                            </div>

                            <!-- File Upload & Current Image Preview -->
                            <div class="mb-3">
                                <label for="background_image" class="form-label">Změnit obrázek hry</label>
                                <input type="file" name="background_image" id="background_image" class="form-control bg-dark border-secondary text-light mb-2" accept="image/*">
                                <?php if (!empty($game['background'])): ?>
                                    <div class="d-flex align-items-center bg-black p-2 rounded border border-secondary" style="max-width: 250px;">
                                        <img src="<?= esc($game['background']) ?>" alt="Aktuální" class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                        <span class="text-muted small ms-2">Aktuální obrázek</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- M:N Relationship Selection -->
                    <div class="mb-4">
                        <h4 class="text-white mb-3 border-bottom border-secondary pb-1">Žánry (M:N vazba)</h4>
                        <div class="row g-2 border border-secondary rounded p-3 bg-dark">
                            <?php foreach ($genres as $g): ?>
                                <div class="col-sm-6 col-md-4 col-lg-3">
                                    <div class="form-check">
                                        <!-- Check if selected in old output or database relations -->
                                        <?php 
                                        $isChecked = false;
                                        if (is_array(old('genres'))) {
                                            $isChecked = in_array($g['id'], old('genres'));
                                        } else {
                                            $isChecked = in_array($g['id'], $selectedGenres);
                                        }
                                        ?>
                                        <input class="form-check-input" type="checkbox" name="genres[]" value="<?= $g['id'] ?>" id="genre_<?= $g['id'] ?>" <?= $isChecked ? 'checked' : '' ?>>
                                        <label class="form-check-label text-muted-hover" for="genre_<?= $g['id'] ?>">
                                            <?= esc($g['name']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Description Fields with WYSIWYG TinyMCE Editor -->
                    <div class="mb-4">
                        <h4 class="text-white mb-3 border-bottom border-secondary pb-1">Popis a požadavky</h4>
                        
                        <div class="mb-3">
                            <label for="short_description" class="form-label">Krátký popis</label>
                            <input type="text" name="short_description" id="short_description" class="form-control bg-dark border-secondary text-light" placeholder="Stručné shrnutí hry..." value="<?= old('short_description', $game['short_description']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="detailed_description" class="form-label">Podrobný popis (WYSIWYG editor)</label>
                            <textarea name="detailed_description" id="detailed_description" class="form-control bg-dark border-secondary text-light wysiwyg" rows="8"><?= old('detailed_description', $game['detailed_description']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="about_the_game" class="form-label">O hře (WYSIWYG editor)</label>
                            <textarea name="about_the_game" id="about_the_game" class="form-control bg-dark border-secondary text-light wysiwyg" rows="6"><?= old('about_the_game', $game['about_the_game']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="pc_requirements" class="form-label">Minimální PC Požadavky</label>
                            <textarea name="pc_requirements" id="pc_requirements" class="form-control bg-dark border-secondary text-light" rows="4"><?= old('pc_requirements', $steamHelper->getCleanRequirements($game['pc_requirements'])) ?></textarea>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="d-flex justify-content-end gap-3 mt-4 border-top border-secondary pt-3">
                        <a href="<?= base_url('games/show/' . $game['id'] . '/' . $steamHelper->slugify($game['name'])) ?>" class="btn btn-steam-outline py-2 px-4">Zrušit</a>
                        <button type="submit" class="btn btn-steam-green py-2 px-5">
                            <i class="fas fa-save me-2"></i>Uložit změny
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- TinyMCE CDN WYSIWYG Editor implementation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: 'textarea.wysiwyg',
        skin: 'oxide-dark',
        content_css: 'dark',
        height: 350,
        menubar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | code',
        content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; background-color: #16202d; color: #c7d5e0; }'
    });
</script>
<?= $this->endSection() ?>
