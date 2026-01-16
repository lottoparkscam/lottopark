<?php foreach ($languages as $language) : ?>
    <?php $lshort = substr($language['code'], 0, 2); ?>
    <?php $lname = Lotto_View::get_language_name($lshort); ?>
    <h3>
        <?= _('Language: ') ?><?= $lname ?>
        <small>
            <a href="#" class="lotto-toggle" data-togglename="<?= _("[hide]") ?>">
                <?= _("[show]") ?>
            </a>
        </small>
    </h3>
    <div class="widget-featured-toggle-language">
        <p>
            <label for="<?= $this->get_field_id('settings'), '_', $lshort, '_image' ?>"><?= _('Image') ?></label>
            <input id="<?= $this->get_field_id('settings'), '_', $lshort, '_image' ?>" name="<?= $this->get_field_name('settings'), '[', $lshort, '][image]' ?>" value="<?= empty($settings[$lshort]['image']) ? '' : $settings[$lshort]['image'] ?>" type="text" class="media-input widefat title">
            <a href="#" class="media-button">Select image</a>
        </p>
        <p>
            <label for="<?= $this->get_field_id('settings'), '_', $lshort, '_url' ?>"><?= _('URL (optional)') ?></label>
            <input id="<?= $this->get_field_id('settings'), '_', $lshort, '_url' ?>" name="<?= $this->get_field_name('settings'), '[', $lshort, '][url]' ?>" value="<?= empty($settings[$lshort]['url']) ? '' : $settings[$lshort]['url'] ?>" type="text" class="widefat title">
        </p>
        <p>
            <label for="<?= $this->get_field_id('settings'), '_', $lshort, '_altText' ?>"><?= _('Alt text (optional)') ?></label>
            <input id="<?= $this->get_field_id('settings'), '_', $lshort, '_altText' ?>" name="<?= $this->get_field_name('settings'), '[', $lshort, '][altText]' ?>" value="<?= empty($settings[$lshort]['altText']) ? '' : $settings[$lshort]['altText'] ?>" type="text" class="widefat title">
        </p>
    </div>
<?php endforeach; ?>
<p>
    <label for="<?= $this->get_field_id('width') ?>"><?= _('Image width (px)') ?></label>
    <input id="<?= $this->get_field_id('width') ?>" name="<?= $this->get_field_name('width') ?>" value="<?= empty($instance['width']) ? '' : $instance['width'] ?>" type="number" class="widefat title">
</p>
<p>
    <label for="<?= $this->get_field_id('height') ?>"><?= _('Image height (px)') ?></label>
    <input id="<?= $this->get_field_id('height') ?>" name="<?= $this->get_field_name('height') ?>" value="<?= empty($instance['height']) ? '' : $instance['height'] ?>" type="text" class="widefat title">
</p>
<p>
    <input id="<?= $this->get_field_id('target') ?>" name="<?= $this->get_field_name('target') ?>" value="blank" type="checkbox" class="widefat title" <?= !empty($instance['target']) ? 'checked' : '' ?>>
    <label for="<?= $this->get_field_id('target') ?>">Open link in new tab</label>
</p>
<p>Select visibility:</p>
<p>
    
    <input id="<?= $this->get_field_id('visibility') ?>-all" name="<?= $this->get_field_name('visibility') ?>" value="all" type="radio" class="widefat" <?= (!empty($instance['visibility']) && $instance['visibility'] === 'all') ? 'checked' : '' ?>>
    <label for="<?= $this->get_field_id('visibility') ?>-all">Visible for both guests and logged in users (default)</label>
    <br>
    <input id="<?= $this->get_field_id('visibility') ?>-guests" name="<?= $this->get_field_name('visibility') ?>" value="guests" type="radio" class="widefat" <?= (!empty($instance['visibility']) && $instance['visibility'] === 'guests') ? 'checked' : '' ?>>
    <label for="<?= $this->get_field_id('visibility') ?>-guests">Visible for guests</label>
    <br>
    <input id="<?= $this->get_field_id('visibility') ?>-users" name="<?= $this->get_field_name('visibility') ?>" value="users" type="radio" class="widefat" <?= (!empty($instance['visibility']) && $instance['visibility'] === 'users') ? 'checked' : '' ?>>
    <label for="<?= $this->get_field_id('visibility') ?>-users">Visible for logged in users</label>
</p>

<div class="media-widget-fields">
</div>