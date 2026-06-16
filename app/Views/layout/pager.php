<?php
/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="Stránkování her">
    <ul class="pagination justify-content-center mb-0 gap-1">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link bg-dark border-secondary text-info px-3 py-2" href="<?= $pager->getFirst() ?>" aria-label="První" style="border-radius: 4px;">
                    <span aria-hidden="true"><i class="fas fa-angle-double-left"></i></span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link bg-dark border-secondary text-info px-3 py-2" href="<?= $pager->getPreviousPage() ?>" aria-label="Předchozí" style="border-radius: 4px;">
                    <span aria-hidden="true"><i class="fas fa-angle-left"></i></span>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link px-3 py-2 <?= $link['active'] ? 'btn-steam-blue text-dark border-info fw-bold' : 'bg-dark border-secondary text-light' ?>" href="<?= $link['uri'] ?>" style="border-radius: 4px; transition: all 0.15s ease;">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link bg-dark border-secondary text-info px-3 py-2" href="<?= $pager->getNextPage() ?>" aria-label="Další" style="border-radius: 4px;">
                    <span aria-hidden="true"><i class="fas fa-angle-right"></i></span>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link bg-dark border-secondary text-info px-3 py-2" href="<?= $pager->getLast() ?>" aria-label="Poslední" style="border-radius: 4px;">
                    <span aria-hidden="true"><i class="fas fa-angle-double-right"></i></span>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
