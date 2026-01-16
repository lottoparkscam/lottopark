<?php
if (!defined('WPINC')) {
    die;
}

$by_newest_selected = '';
if (empty($settings['order'])) {
    $by_newest_selected = ' selected="selected"';
}

$by_random_selected = '';
if (isset($settings['order']) && $settings['order'] == "1") {
    $by_random_selected = ' selected="selected"';
}

$normal_selected = '';
if (empty($type) || (int)$type === Lotto_Widget_Small_Winners::TYPE_NORMAL) {
    $normal_selected = ' selected="selected"';
}

$compact_selected = '';
if (isset($type) && (int)$type === Lotto_Widget_Small_Winners::TYPE_COMPACT) {
    $compact_selected = ' selected="selected"';
}

$information_selected = '';
if (empty($target) || $target == "1") {
    $information_selected = ' selected="selected"';
}

$play_selected = '';
if (isset($target) && $target == "2") {
    $play_selected = ' selected="selected"';
}

?>
<p>
    <label for="<?= $this->get_field_id('title'); ?>">
        <?= Security::htmlentities(_('Title')); ?>:
    </label>
    <input class="widefat" 
           id="<?= $this->get_field_id('title'); ?>" 
           name="<?= $this->get_field_name('title'); ?>" 
           type="text" 
           value="<?= $title; ?>" />
</p>

<p>
    <label for="<?= $this->get_field_id('width'); ?>">
        <?= Security::htmlentities(_('Width (%)')); ?>:
    </label>
    <input class="widefat" 
           id="<?= $this->get_field_id('width'); ?>" 
           name="<?= $this->get_field_name('width'); ?>" 
           type="text" 
           value="<?= $width; ?>" />
    <?= _("The margins are calculated automatically."); ?>
</p>

<p>
    <label for="<?= $this->get_field_id('amount'); ?>">
        <?= Security::htmlentities(_('Amount')); ?>:
    </label>
    <input class="widefat" 
           id="<?= $this->get_field_id('amount'); ?>" 
           name="<?= $this->get_field_name('amount'); ?>" 
           type="text" 
           value="<?= $amount; ?>" />
</p>

<p>
    <label for="<?= $this->get_field_id('settings').'_order'; ?>">
        <?= Security::htmlentities(_('Order')); ?>:
    </label>
    <select class="widefat" 
            id="<?= $this->get_field_id('settings').'_order'; ?>" 
            name="<?= $this->get_field_name('settings').'[order]'; ?>">
        <option value=""<?= $by_newest_selected; ?>>
            <?= Security::htmlentities(_("by newest")); ?>
        </option>
        <option value="1"<?= $by_random_selected; ?>>
            <?= Security::htmlentities(_("by random")); ?>
        </option>
    </select>
</p>

<p>
    <label for="<?= $this->get_field_id('type'); ?>">
        <?= Security::htmlentities(_('Type')); ?>:
    </label>
    <br>
    <select class="widefat" 
            id="<?= $this->get_field_id('type'); ?>" 
            name="<?= $this->get_field_name('type'); ?>">
        <option value="1"<?= $normal_selected; ?>>
            <?= Security::htmlentities(_("Normal")); ?>
        </option>
        <option value="2"<?= $compact_selected; ?>>
            <?= Security::htmlentities(_("Compact")); ?>
        </option>
    </select>
</p>

<p>
    <label for="<?= $this->get_field_id('target'); ?>">
        <?= Security::htmlentities(_('Target')); ?>:
    </label>
    <br>
    <select class="widefat" 
            id="<?= $this->get_field_id('target'); ?>" 
            name="<?= $this->get_field_name('target'); ?>">
        <option value="1"<?= $information_selected; ?>>
            <?= Security::htmlentities(_("Information")); ?>
        </option>
        <option value="2"<?= $play_selected; ?>>
            <?= Security::htmlentities(_("Play")); ?>
        </option>
    </select>
</p>