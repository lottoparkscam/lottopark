<?php
if (!defined('WPINC')) {
    die;
}
/** @var Lotto_Widget_Raffle_Settings $setting */ $setting;
$is_selected = function (string $size) use ($setting): bool {
    return $setting->size === $size;
};

$is_selected_type = function (string $type) use ($setting): bool {
    return $setting->type === $type;
};
$types = $setting::get_types();
?>

<p>
    <label for="<?= $this->get_field_id('size'); ?>">
        <?php echo Security::htmlentities(_('Size')); ?>:
    </label>
    <select name="<?= $this->get_field_name('size') ?>" id="<?= $this->get_field_id('size') ?>">
        <?php foreach ($setting::get_sizes() as $size): ?>
            <option <?= $is_selected($size) ? 'selected' : null ?> value="<?= $size?>"><?= _($size) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="<?= $this->get_field_id('type'); ?>">
        <?php echo Security::htmlentities(_('Type')); ?>:
    </label>
    <select name="<?= $this->get_field_name('type') ?>" id="<?= $this->get_field_id('type') ?>">
        <?php foreach ($types as $raffle): ?>
            <option <?= $is_selected_type($raffle->slug) ? 'selected' : null ?> value="<?= $raffle->slug?>"><?= _($raffle->name) ?></option>
        <?php endforeach; ?>
    </select>
</p>
<p>
    <label for="<?= $this->get_field_id('useCustomColors'); ?>">
        <?= Security::htmlentities(_('Use custom colors')); ?>:
    </label>
    <input type="checkbox" name="<?= $this->get_field_name('useCustomColors') ?>" id="<?= $this->get_field_id('useCustomColors') ?>" <?php echo $setting->useCustomColors === false ? '' : 'checked' ?>>
</p>
<p>
    <label for="<?= $this->get_field_id('buttonTextColor'); ?>">
        <?= Security::htmlentities(_('Button text color')); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonTextColor') ?>" id="<?= $this->get_field_id('buttonTextColor') ?>" value="<?= $setting->buttonTextColor ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonTextColorOnHover'); ?>">
        <?= Security::htmlentities(_('Button text on button hover')); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonTextColorOnHover') ?>" id="<?= $this->get_field_id('buttonTextColorOnHover') ?>" value="<?= $setting->buttonTextColorOnHover ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonBackgroundColor'); ?>">
        <?= Security::htmlentities(_('Button background color')); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonBackgroundColor') ?>" id="<?= $this->get_field_id('buttonBackgroundColor') ?>" value="<?= $setting->buttonBackgroundColor ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonBackgroundColorOnHover'); ?>">
        <?= Security::htmlentities(_('Button background color on hover')); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonBackgroundColorOnHover') ?>" id="<?= $this->get_field_id('buttonBackgroundColorOnHover') ?>" value="<?= $setting->buttonBackgroundColorOnHover ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('backgroundColor'); ?>">
        <?= Security::htmlentities(_('Background color')); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('backgroundColor') ?>" id="<?= $this->get_field_id('backgroundColor') ?>" value="<?= $setting->backgroundColor ?>">
</p>

