<?php

use Helpers\UrlHelper;

if (!empty($lottery)):
    $lotteries = lotto_platform_get_lotteries();
    $groupLotteries = Model_Lottery_Group::get_lotteries_for_group($lottery['group_id']);
?>
<?php if (!empty($groupLotteries)): ?>
    <?php if ($page_name == "results"): ?>
        <section class="page-content">
                    <article class="page">
    <?php endif; ?>
    <div class="content-group-lotteries">
    <?php
        foreach ($groupLotteries as $group):
            if (!isset($lotteries["__by_id"][$group["lottery_id"]])) {
                continue;
            }
            $glottery = $lotteries["__by_id"][$group["lottery_id"]];
            $link = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug($page_name) . $glottery['slug'] . '/');
            $class = "";
            if ($glottery['id'] == $lottery['id']):
                $class = " content-group-lottery-active";
            endif;
    ?>
        <div class="content-group-lottery<?= $class; ?>">
            <a href="<?= $link; ?>">
                <?= Security::htmlentities(_($glottery['name'])); ?>
            </a>
        </div>
    <?php endforeach; ?>
    </div>
    <?php if ($page_name == "results"): ?>
        </article>
    </section>
    <?php endif; ?>
<?php endif;
endif;
?>