<?php
if (!defined('WPINC')) {
    die;
}
?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>">
        <?php echo Security::htmlentities(_('Title')); ?>:
    </label>
    <input class="widefat" 
           id="<?php echo $this->get_field_id('title'); ?>" 
           name="<?php echo $this->get_field_name('title'); ?>" 
           type="text" 
           value="<?php echo $title; ?>" />
</p>

<p>
    <label for="<?php echo $this->get_field_id('width'); ?>">
        <?php echo Security::htmlentities(_('Width (%)')); ?>:
    </label>
    <input class="widefat" 
           id="<?php echo $this->get_field_id('width'); ?>" 
           name="<?php echo $this->get_field_name('width'); ?>" 
           type="text" 
           value="<?php echo $width; ?>" />
    <?php echo _("The margins are calculated automatically."); ?>
</p>

<?php
// 08.03.2019 15:26 Vordis TODO: presenter for this should be great
// Container option - user can choose container that should be used for widget (h2 or div)
?>
<p>
    <label for="<?= $this->get_field_id('title_container'); ?>">
        <?= Security::htmlentities(_('Title Container')); ?>:
    </label>
    <br>
    <select class="widefat" 
            id="<?= $this->get_field_id('title_container'); ?>"
            name="<?= $this->get_field_name('title_container'); ?>">
        <option value="<?= Lotto_Widget_List::TITLE_CONTAINER_H2 ?>"<?= (empty($title_container) || $title_container == Lotto_Widget_List::TITLE_CONTAINER_H2) ? ' selected="selected"' : '' ?>>
            <?= Security::htmlentities(_("H2")); ?>
        </option>
        <option value="<?= Lotto_Widget_List::TITLE_CONTAINER_DIV ?>"<?= (isset($title_container) && $title_container == Lotto_Widget_List::TITLE_CONTAINER_DIV) ? ' selected="selected"' : '' ?>>
            <?= Security::htmlentities(_("DIV")); ?>
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
        <option value="<?= Lotto_Widget_Small_Results::TARGET_RESULTS ?>"<?= (empty($target) || $target == Lotto_Widget_Small_Results::TARGET_RESULTS) ? ' selected="selected"' : '' ?>>
            <?= Security::htmlentities(_("Results")); ?>
        </option>
        <option value="<?= Lotto_Widget_Small_Results::TARGET_PLAY ?>"<?= (isset($target) && $target == Lotto_Widget_Small_Results::TARGET_PLAY) ? ' selected="selected"' : '' ?>>
            <?= Security::htmlentities(_("Play")); ?>
        </option>
    </select>
</p>

