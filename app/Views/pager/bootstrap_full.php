<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Compact pagination with per-page selector (10 / 20 / 50).
 *
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(1);

$current = $pager->getCurrentPageNumber();
$last    = $pager->getPageCount();
$total   = $pager->getTotal();
$start   = $pager->getPerPageStart();
$end     = $pager->getPerPageEnd();
$perPage = $pager->getPerPage() ?? 10;
$options = [10, 20, 50];
if (! in_array((int) $perPage, $options, true)) {
    $options[] = (int) $perPage;
    sort($options);
}

$request = service('request');
$get     = $request->getGet() ?? [];
// Reset to page 1 when changing page size
unset($get['page'], $get['per_page']);
?>

<?php if ($last > 1 || ($total !== null && $total > 0)): ?>
<div class="pager-bar">
    <div class="pager-start">
        <?php if ($total !== null && $start !== null && $end !== null): ?>
            <p class="pager-meta mb-0">
                <?= number_format($start) ?>–<?= number_format($end) ?>
                dari <?= number_format($total) ?>
            </p>
        <?php elseif ($last > 1): ?>
            <p class="pager-meta mb-0">Halaman <?= (int) $current ?> / <?= (int) $last ?></p>
        <?php endif; ?>

        <form method="get" class="pager-size" aria-label="Jumlah baris per halaman">
            <?php foreach ($get as $key => $value): ?>
                <?php if (is_array($value)) {
                    continue;
                } ?>
                <input type="hidden" name="<?= esc($key) ?>" value="<?= esc((string) $value) ?>">
            <?php endforeach; ?>
            <label class="pager-size-label" for="pager-per-page">Per halaman</label>
            <select name="per_page" id="pager-per-page" class="form-select form-select-sm pager-size-select" onchange="this.form.submit()">
                <?php foreach ($options as $n): ?>
                    <option value="<?= (int) $n ?>" <?= (int) $perPage === (int) $n ? 'selected' : '' ?>><?= (int) $n ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($last > 1): ?>
    <nav class="pager-nav" aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pager->hasPreviousPage() ? '' : 'disabled' ?>">
                <?php if ($pager->hasPreviousPage()): ?>
                    <a class="page-link" href="<?= $pager->getPreviousPage() ?>" aria-label="Halaman sebelumnya" title="Sebelumnya">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <span class="page-link" aria-disabled="true"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
                <?php endif; ?>
            </li>

            <?php if ($pager->hasPrevious() && $pager->getFirstPageNumber() > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pager->getFirst() ?>" aria-label="Halaman 1">1</a>
                </li>
                <?php if ($pager->getFirstPageNumber() > 2): ?>
                    <li class="page-item disabled pager-ellipsis" aria-hidden="true">
                        <span class="page-link">…</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php foreach ($pager->links() as $link): ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <?php if ($link['active']): ?>
                        <span class="page-link" aria-current="page"><?= esc($link['title']) ?></span>
                    <?php else: ?>
                        <a class="page-link" href="<?= $link['uri'] ?>"><?= esc($link['title']) ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>

            <?php if ($pager->hasNext() && $pager->getLastPageNumber() < $last): ?>
                <?php if ($pager->getLastPageNumber() < $last - 1): ?>
                    <li class="page-item disabled pager-ellipsis" aria-hidden="true">
                        <span class="page-link">…</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pager->getLast() ?>" aria-label="Halaman <?= (int) $last ?>"><?= (int) $last ?></a>
                </li>
            <?php endif; ?>

            <li class="page-item <?= $pager->hasNextPage() ? '' : 'disabled' ?>">
                <?php if ($pager->hasNextPage()): ?>
                    <a class="page-link" href="<?= $pager->getNextPage() ?>" aria-label="Halaman berikutnya" title="Berikutnya">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <span class="page-link" aria-disabled="true"><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>
<?php endif; ?>
