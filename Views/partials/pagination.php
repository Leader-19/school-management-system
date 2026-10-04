<?php
/**
 * Reusable server-side pagination bar.
 *
 * Expects these variables from the including view:
 *
 * @var array  $pagination        ['page','per_page','total','total_pages','offset']
 * @var string $paginationBaseUrl Base URL without query string, e.g. BASE_URL . '/student'
 * @var array  $paginationQuery   Optional extra GET params to preserve, e.g. ['q' => $keyword]
 * @var string $paginationNoun    Optional row label, defaults to 'entries'
 */

$pagination = $pagination ?? [];
if (empty($pagination) || (int) $pagination['total'] === 0) {
    return;
}

$page        = max(1, (int) ($pagination['page'] ?? 1));
$perPage     = max(1, (int) ($pagination['per_page'] ?? 15));
$total       = (int) ($pagination['total'] ?? 0);
$totalPages  = max(1, (int) ($pagination['total_pages'] ?? 1));
$offset      = (int) ($pagination['offset'] ?? 0);
$noun        = $paginationNoun ?? 'entries';
$extraParams = $paginationQuery ?? [];

/**
 * Build a page link, preserving any extra query params (e.g. the search term).
 */
$smsPageUrl = function ($targetPage) use ($paginationBaseUrl, $extraParams) {
    $params = array_merge($extraParams, ['page' => $targetPage]);
    return $paginationBaseUrl . (str_contains($paginationBaseUrl, '?') ? '&' : '?')
        . http_build_query($params);
};

// Which page numbers to render: 1, a window around the current page, and the last.
$smsWindow = [];
for ($i = $page - 2; $i <= $page + 2; $i++) {
    if ($i >= 1 && $i <= $totalPages) {
        $smsWindow[] = $i;
    }
}
$smsPages = array_values(array_unique(array_merge([1], $smsWindow, [$totalPages])));

$from = $total === 0 ? 0 : $offset + 1;
$to   = min($offset + $perPage, $total);
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 bg-white">
    <span class="text-muted small">
        Showing <strong><?= $from ?></strong>&ndash;<strong><?= $to ?></strong>
        of <strong><?= $total ?></strong> <?= htmlspecialchars($noun) ?>
    </span>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($smsPageUrl(max(1, $page - 1))) ?>"
                       aria-label="Previous page" <?= $page <= 1 ? 'tabindex="-1"' : '' ?>>
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                </li>

                <?php foreach ($smsPages as $i => $smsPage): ?>
                    <?php
                    // Insert an ellipsis li wherever the numbers skip.
                    $smsPrevious = $smsPages[$i - 1] ?? null;
                    if ($smsPrevious !== null && $smsPage - $smsPrevious > 1): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php endif; ?>
                    <li class="page-item <?= $smsPage === $page ? 'active' : '' ?>"
                        <?= $smsPage === $page ? 'aria-current="page"' : '' ?>>
                        <a class="page-link" href="<?= htmlspecialchars($smsPageUrl($smsPage)) ?>"><?= $smsPage ?></a>
                    </li>
                <?php endforeach; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= htmlspecialchars($smsPageUrl(min($totalPages, $page + 1))) ?>"
                       aria-label="Next page" <?= $page >= $totalPages ? 'tabindex="-1"' : '' ?>>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
