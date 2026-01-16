<?php
if (!defined('WPINC')) {
    die;
}

$checked_carousel = '';
if (empty($instance['type']) || $instance['type'] == Lotto_Widget_List::TYPE_CAROUSEL) {
    $checked_carousel = ' checked="checked"';
}

$checked_grid = '';
if (empty($instance['type']) || $instance['type'] == Lotto_Widget_List::TYPE_GRID) {
    $checked_grid = ' checked="checked"';
}

$countdown_always = '';
if (empty($countdown) || $countdown == Lotto_Widget_List::COUNTDOWN_ALWAYS) {
    $countdown_always = ' selected="selected"';
}

$countdown_24hours = '';
if (empty($countdown) || $countdown == Lotto_Widget_List::COUNTDOWN_24HOURS) {
    $countdown_24hours = ' selected="selected"';
}

$display_tall = '';
if (empty($display) || $display == Lotto_Widget_List::DISPLAY_TALL) {
    $display_tall = ' selected="selected"';
}

$display_short = '';
if (empty($display) || $display == Lotto_Widget_List::DISPLAY_SHORT) {
    $display_short = ' selected="selected"';
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
	<label>
        <?php echo Security::htmlentities(_('Type')); ?>:
    </label>
    <br>
	<label class="widefat" for="<?php echo $this->get_field_id('type'); ?>_1">
		<input id="<?php echo $this->get_field_id('type'); ?>_1" 
               name="<?php echo $this->get_field_name('type'); ?>" 
               type="radio" 
               value="1" <?php echo $checked_carousel; ?>> <?php
            echo Security::htmlentities(_("Carousel"));
        ?>
	</label><br>
	<label for="<?php echo $this->get_field_id('type'); ?>_2">
		<input id="<?php echo $this->get_field_id('type'); ?>_2" 
               name="<?php echo $this->get_field_name('type'); ?>" 
               type="radio" 
               value="2" <?php echo $checked_grid; ?>> <?php
            echo Security::htmlentities(_("Grid"));
        ?>
	</label>
</p>
<p>
    <label for="<?php echo $this->get_field_id('count'); ?>">
        <?php echo Security::htmlentities(_('Count')); ?>:
    </label>
    <input class="widefat" 
           id="<?php echo $this->get_field_id('count'); ?>" 
           name="<?php echo $this->get_field_name('count'); ?>" 
           type="text" 
           value="<?php echo isset($count) ? $count : ''; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('countdown'); ?>">
        <?php echo Security::htmlentities(_('Countdown')); ?>:
    </label>
    <br>
	<select class="widefat" id="<?php echo $this->get_field_id('countdown'); ?>" 
            name="<?php echo $this->get_field_name('countdown'); ?>">
        <option value="1"<?php echo $countdown_always; ?>>
            <?php echo Security::htmlentities(_("Always")); ?>
        </option>
        <option value="2"<?php echo $countdown_24hours; ?>>
            <?php echo Security::htmlentities(_("24 hours before")); ?>
        </option>
	</select>
</p>
<p>
    <label for="<?php echo $this->get_field_id('display'); ?>">
        <?php echo Security::htmlentities(_('Display')); ?>:
    </label>
    <br>
	<select class="widefat" id="<?php echo $this->get_field_id('display'); ?>" 
            name="<?php echo $this->get_field_name('display'); ?>">
        <option value="1"<?php echo $display_tall; ?>>
            <?php echo Security::htmlentities(_("Tall - 5 elements")); ?>
        </option>
        <option value="2"<?php echo $display_short; ?>>
            <?php echo Security::htmlentities(_("Short - 3 elements")); ?>
        </option>
	</select>
</p>
<?php
// 08.03.2019 15:26 Vordis TODO: presenter for this should be great
// Container option - user can choose container that should be used for widget (h2 or div)
?>
<p>
    <label for="<?= $this->get_field_id('title_container'); ?>"><?= Security::htmlentities(_('Title Container')); ?>:
    </label>
    <br>
    <select class="widefat" id="<?= $this->get_field_id('title_container'); ?>"
            name="<?= $this->get_field_name('title_container'); ?>">
        <option value="<?= Lotto_Widget_List::TITLE_CONTAINER_H2 ?>"<?= (empty($title_container) || $title_container == Lotto_Widget_List::TITLE_CONTAINER_H2) ? ' selected="selected"' : '' ?>><?= Security::htmlentities(_("H2")); ?>
        </option>
        <option value="<?= Lotto_Widget_List::TITLE_CONTAINER_DIV ?>"<?= (isset($title_container) && $title_container == Lotto_Widget_List::TITLE_CONTAINER_DIV) ? ' selected="selected"' : '' ?>><?= Security::htmlentities(_("DIV")); ?>
        </option>
    </select>
</p>