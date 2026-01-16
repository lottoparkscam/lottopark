<?php 
if (!defined('WPINC')) {
    die;
}

if (!empty($title) && !empty($content)):
?>
    <div class="small-widget small-widget-text">
        <?= $title_start_tag . $title . $title_end_tag ?>
        <div class="small-widget-content">
            <p><?= $content; ?></p>
        </div>
    </div>
<?php 
endif;
