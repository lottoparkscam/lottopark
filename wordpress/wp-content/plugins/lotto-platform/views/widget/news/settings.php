<?php
if (!defined('WPINC')) {
    die;
}

$columns_2_selected = '';
if (empty($columns) || $columns == Lotto_Widget_News::TWO_COLUMNS) {
    $columns_2_selected = ' selected="selected"';
}

$columns_3_selected = '';
if (empty($columns) || $columns == Lotto_Widget_News::THREE_COLUMNS) {
    $columns_3_selected = ' selected="selected"';
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
	<label for="<?php echo $this->get_field_id('columns'); ?>">
        <?php echo Security::htmlentities(_('Columns')); ?>:
    </label>
    <br>
	<select class="widefat" id="<?php echo $this->get_field_id('columns'); ?>" 
            name="<?php echo $this->get_field_name('columns'); ?>">
        <option value="1"<?php echo $columns_2_selected; ?>>
            <?php echo Security::htmlentities(_("2")); ?>
        </option>
        <option value="2"<?php echo $columns_3_selected; ?>>
            <?php echo Security::htmlentities(_("3")); ?>
        </option>
	</select>
</p>

<p>
    <label for="<?php echo $this->get_field_id('number'); ?>">
        <?php echo Security::htmlentities(_('Number of posts to show:')); ?>
    </label>
    <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" 
           name="<?php echo $this->get_field_name('number'); ?>" 
           type="number" step="1" min="1" 
           value="<?php echo $number; ?>" 
           size="3" />
</p>