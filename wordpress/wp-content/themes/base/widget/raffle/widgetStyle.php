<?php
if ($setting->useCustomColors !== false): ?>
<style>
    #<?= $widgetId ?> .promo-widget__button a {
        color: <?= Security::htmlentities($setting->buttonTextColor) ?>!important;
        background-color: <?= Security::htmlentities($setting->buttonBackgroundColor) ?>!important;
    }
    #<?= $widgetId ?> .promo-widget__button a:hover {
        color: <?= Security::htmlentities($setting->buttonTextColorOnHover) ?>!important;
        background-color: <?= Security::htmlentities($setting->buttonBackgroundColorOnHover) ?>!important;
    }
    div.promo-widget#<?= $widgetId ?> {
        background-color: <?= Security::htmlentities($setting->backgroundColor) ?>!important;
    }
    div.promo-widget#<?= $widgetId ?> {
        background-color: <?= Security::htmlentities($setting->backgroundColor) ?>!important;
    }
</style>
<?php endif; ?>
