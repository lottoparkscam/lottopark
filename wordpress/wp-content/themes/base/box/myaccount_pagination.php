<?php

$pagination = $pages->render(true);

if (!empty($pagination) && count($pagination) > 0):
    $prev_link = null;
    $next_link = null;
    foreach ($pagination as $page):
        if ($page['type'] == 'next'):
            $next_link = $page['uri'];
        elseif ($page['type'] == 'previous'):
            $prev_link = $page['uri'];
        endif;

        if ($next_link != null && $prev_link != null):
            break;
        endif;
    endforeach;
?>
    <nav class="pagination">
        <?php
            foreach ($pagination as $page):
                if ($page['type'] != 'previous-inactive' &&
                    $page['type'] != 'next-inactive'
                ):
                    if ($page['type'] == 'active'):
        ?>
                        <span class="page-numbers current">
                            <?= $page['title']; ?>
                        </span>
        <?php
                    elseif ($page['type'] == 'next'):
        ?>
                        <a href="<?= $page['uri']; ?>" class="next page-numbers">
                            <span class="mobile-only"><span class="fa fa-long-arrow-right" aria-hidden="true"></span></span>
                            <span class="mobile-hide"><?= Security::htmlentities(_("next")); ?></span>
                        </a>
        <?php
                    elseif ($page['type'] == 'previous'):
        ?>
                        <a href="<?= $page['uri']; ?>" class="prev page-numbers">
                            <span class="mobile-only"><span class="fa fa-long-arrow-left" aria-hidden="true"></span></span>
                            <span class="mobile-hide"><?= Security::htmlentities(_("previous")); ?></span>
                        </a>
        <?php
                    else:
        ?>
                        <a href="<?= $page['uri']; ?>" class="page-numbers">
                            <?= $page['title']; ?>
                        </a>
        <?php
                    endif;
                endif;
            endforeach;
        ?>
    </nav>
<?php

endif;
