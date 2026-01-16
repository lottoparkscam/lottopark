<nav class="pagination">
    <?php
    /** Helpers_View_Pagination $pagination */
    $pagination;

    const MAX_PAGES_DISPLAY = 4;
    $all_pages = (int)floor($pagination->get_all_results_count() / $pagination->get_per_page());
    $start = $pagination->get_current_page() - MAX_PAGES_DISPLAY;
    if ($start < 1) {
        $start = 1;
    }
    $end = $pagination->get_current_page() + MAX_PAGES_DISPLAY;
    if ($end > $all_pages) {
        $end = $all_pages + 1;
    }
    $range = range($start, !$end ? 1 : $end);
    ?>

    <?php foreach ($range as $page): ?>
        <?php if ((int)$page === $pagination->get_current_page()): ?>
            <span class="page-numbers current"><?= $page ?></span>
        <?php else: ?>
            <a href="<?= $pagination->change_page($page) ?>" class="page-numbers"><?= $page ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
