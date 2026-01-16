<?php
if (!defined('WPINC')) {
    die;
}

$title_text = '';
if (empty($title)) {
    $title_text = _("Latest winners");
} else {
    $title_text = $title;
}

?>
<div class="small-widget small-widget-winners">
    <div class="small-widget-title"><?= $title_text; ?></div>
    <?php
        if ($winners->have_posts()):
    ?>
            <div class="small-widget-content small-widget-winners-content">
                <?php
                    while ($winners->have_posts()):
                        $winners->the_post();
                    
                        if (empty($post->ID)) {
                            continue;
                        }
                        
                        $data = get_post_meta($post->ID, 'winners-data');

                        if (!isset($data[0]) ||
                            !isset($lotteries['__by_id']) ||
                            !isset($data[0]['lottery']) ||
                            !isset($lotteries['__by_id'][$data[0]['lottery']])
                        ) {
                            continue;
                        }
                        
                        $lottery = $lotteries['__by_id'][$data[0]['lottery']];

                        $lotto_path = LOTTO_PLUGIN_DIR;
                        $lotto_url = LOTTO_PLUGIN_URL;

                        if ($type == 1):
                            ?><div class="small-widget-winners-item">
                                <h2><?= Security::htmlentities(_($lottery['country']).' - '._($lottery['name'])); ?></h2>
                                <div class="small-widget-winners-item-name"><?= Security::htmlentities(sprintf(_("%s from %s won"), $data[0]['name'], $countries[$data[0]['country']])); ?></div>
                                <div class="small-widget-winners-amount"><?= Security::htmlentities(Lotto_View::format_currency($data[0]['amount'], $currencies[$lottery['currency_id']]['code'])); ?></div>
                            </div><?php
                        else:
                ?>
                            <div class="small-widget-winners-item-compact">
                                <div class="small-widget-winners-item-compact-name"><?= Security::htmlentities($data[0]['name']); ?></div>
                                <div class="small-widget-winners-item-compact-country"><i class="sprite-lang sprite-lang-<?= strtolower($data[0]['country']); ?>"></i><?= Security::htmlentities($countries[$data[0]['country']]); ?></div>
                                <div class="small-widget-winners-item-compact-amount"><span><?= Security::htmlentities(Lotto_View::format_currency($data[0]['amount'], $currencies[$lottery['currency_id']]['code'])); ?></span> - <h2><a href="<?= lotto_platform_get_permalink_by_slug('lotteries/' . $lottery['slug']); ?>"><?= _($lottery['name']); ?></a></h2></div>
                                <div class="clearfix"></div>
                            </div>
                <?php
                        endif;

                    endwhile;
                ?>
            </div>
    <?php
        else:
    ?>
            <div class="small-widget-no-info"><?= _("No winners.") ?></div>
    <?php
        endif;
    ?>
</div>