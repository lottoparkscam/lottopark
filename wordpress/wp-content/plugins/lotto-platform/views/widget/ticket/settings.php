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