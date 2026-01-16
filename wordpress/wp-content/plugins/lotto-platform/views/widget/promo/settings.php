<?php
if (!defined('WPINC')) {
    die;
}
?>
<p>Select visibility:</p>
<p>
    <input id="<?= $this->get_field_id('visibility') ?>-show" name="<?= $this->get_field_name('visibility') ?>" value="show" type="radio" class="widefat" <?= (empty($instance['visibility']) || $instance['visibility'] === 'show') ? 'checked' : '' ?>>
    <label for="<?= $this->get_field_id('visibility') ?>-show">Show widget (default)</label>
    <br>
    <input id="<?= $this->get_field_id('visibility') ?>-hide" name="<?= $this->get_field_name('visibility') ?>" value="hide" type="radio" class="widefat" <?= (!empty($instance['visibility']) && $instance['visibility'] === 'hide') ? 'checked' : '' ?>>
    <label for="<?= $this->get_field_id('visibility') ?>-hide">Hide widget</label>
</p>
<p>
    <label for="<?php echo $this->get_field_id('video'); ?>">YouTube Video ID: <br> Example: https://www.youtube.com/watch?v=<strong style="color:red">vH98XFxys8M</strong></label>
    <input class="widefat" id="<?php echo $this->get_field_id('video'); ?>" name="<?php echo $this->get_field_name('video'); ?>" type="text" value="<?php echo $video; ?>">
</p>
<p>
    <label for="<?php echo $this->get_field_id('image'); ?>"><?= _('YouTube thumbnail') ?>:</label>
    <input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" value="<?php echo empty($image) ? '' : $image; ?>" type="text" class="media-input widefat title">
    <a href="#" class="media-button">Select image</a>
</p>
