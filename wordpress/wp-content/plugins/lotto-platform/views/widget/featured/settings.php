<?php

use Helpers\Wordpress\LanguageHelper;

if (!defined('WPINC')) {
    die;
}
?>
<p>
    <label for="<?= $this->get_field_id('useCustomColors'); ?>">
        <?= _('Use custom colors'); ?>:
    </label>
    <input type="checkbox" name="<?= $this->get_field_name('useCustomColors') ?>" id="<?= $this->get_field_id('useCustomColors') ?>" <?php echo $settings['useCustomColors'] === false ? '' : 'checked' ?>>
</p>
<p>
    <label for="<?= $this->get_field_id('backgroundColor'); ?>">
        <?= _('Background color'); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('backgroundColor') ?>" id="<?= $this->get_field_id('backgroundColor') ?>" value="<?= $settings['backgroundColor'] ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonBackgroundColor'); ?>">
        <?= _('Button background color'); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonBackgroundColor') ?>" id="<?= $this->get_field_id('buttonBackgroundColor') ?>" value="<?= $settings['buttonBackgroundColor'] ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonBackgroundColorOnHover'); ?>">
        <?= _('Button background color on hover'); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonBackgroundColorOnHover') ?>" id="<?= $this->get_field_id('buttonBackgroundColorOnHover') ?>" value="<?= $settings['buttonBackgroundColorOnHover'] ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonTextColor'); ?>">
        <?= _('Button text color'); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonTextColor') ?>" id="<?= $this->get_field_id('buttonTextColor') ?>" value="<?= $settings['buttonTextColor'] ?>">
</p>
<p>
    <label for="<?= $this->get_field_id('buttonTextColorOnHover'); ?>">
        <?= _('Button text on button hover'); ?>:
    </label>
    <input class="color-picker" name="<?= $this->get_field_name('buttonTextColorOnHover') ?>" id="<?= $this->get_field_id('buttonTextColorOnHover') ?>" value="<?= $settings['buttonTextColorOnHover'] ?>">
</p>
<p>
    <label for="<?php echo $this->get_field_id('type'); ?>">
        <?= _('Type'); ?>:
    </label>

    <br>
    <select class="widefat lotto-featured-type-select" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
        <option value="1" <?php if (empty($type) || $type == "1") : echo ' selected="selected"'; endif; ?>>
            <?=_("Small"); ?>
        </option>
        <option value="3" <?php if ($type == "3") : echo ' selected="selected"'; endif; ?>>
            <?=_("Large with background"); ?>
        </option>
    </select>
    <br />

<div class="lotto-featured-type-content" id="<?php echo $this->get_field_id('bg-large-content'); ?>" 
    style="<?php if ($type != "3") { echo 'display: none;'; } ?>">
    <p>
        <label for="<?php echo $this->get_field_id('bg-large-disable-mobile'); ?>">
            <?= _('Disable for mobile'); ?>:
        </label>

        <input type="checkbox" class="widefat" name="<?php echo $this->get_field_name('bg-large-disable-mobile'); ?>" id="<?php echo $this->get_field_id('bg-large-disable-mobile'); ?>" <?php echo ($settings['bg_large_disable_mobile'] == 1) ? 'checked' : ''; ?> />
    </p>

    <h3>
        <span>Texts</span>
        <small>
            <a href="#" class="lotto-toggle" data-togglename="<?php echo htmlspecialchars(_("[hide]")); ?>"><?= _("[show]"); ?></a>
        </small>
    </h3>

    <div class="widget-featured-toggle-language">
        <h4>DEFAULT TEXTS:</h4>
        <p>
            <label for="<?= $this->get_field_id(Lotto_Widget_Featured::BG_LARGE_TEXT_ID); ?>"><?= _('Title'); ?>:</label>
            <input type="text" class="widefat" name="<?= $this->get_field_name(Lotto_Widget_Featured::BG_LARGE_TEXT_ID); ?>" id="<?= $this->get_field_id(Lotto_Widget_Featured::BG_LARGE_TEXT_ID); ?>" value="<?= htmlspecialchars($settings[Lotto_Widget_Featured::BG_LARGE_TEXT_ID]); ?>" />
        </p>
        <p>
            <label for="<?= $this->get_field_id(Lotto_Widget_Featured::BG_LARGE_SUBTEXT_ID); ?>"><?= _('Subtitle'); ?>:</label>
            <input type="text" class="widefat" name="<?= $this->get_field_name(Lotto_Widget_Featured::BG_LARGE_SUBTEXT_ID); ?>" id="<?= $this->get_field_id(Lotto_Widget_Featured::BG_LARGE_SUBTEXT_ID); ?>" value="<?= htmlspecialchars($settings[Lotto_Widget_Featured::BG_LARGE_SUBTEXT_ID]); ?>" />
        </p>

        <h4>TEXTS BY LANGUAGE</h4>
        <table class="widefat">
            <thead>
                <tr>
                    <th scope="col"><?= _('Language'); ?></th>
                    <th scope="col"><?= _('Title'); ?></th>
                    <th scope="col"><?= _('Subtitle'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($languages as $language) : ?>
                    <?php
                        $letterOnlyLocale = str_replace('_', '', $language['code']);
                        $translatedLanguageName = LanguageHelper::getLanguageNameByLocale($language['code']);
                    ?>
                    <tr>
                        <th scope="row"><?= $translatedLanguageName; ?></th>
                        <td><input class="widefat" id="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_title'; ?>" name="<?php echo $this->get_field_name('settings') . '[' . $letterOnlyLocale . '][title]'; ?>" type="text" value="<?php echo isset($settings[$letterOnlyLocale]['title']) ? $settings[$letterOnlyLocale]['title'] : ''; ?>"></td>
                        <td><input class="widefat" id="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_subtitle'; ?>" name="<?php echo $this->get_field_name('settings') . '[' . $letterOnlyLocale . '][subtitle]'; ?>" type="text" value="<?php echo isset($settings[$letterOnlyLocale]['subtitle']) ? $settings[$letterOnlyLocale]['subtitle'] : ''; ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</p>

<?php foreach ($languages as $language) : ?>
    <?php
        $letterOnlyLocale = str_replace('_', '', $language['code']);
        $translatedLanguageName = LanguageHelper::getLanguageNameByLocale($language['code']);
    ?>
    <h3>
        <?= _('Language: '); ?><?php echo $translatedLanguageName; ?>
        <small>
            <a href="#" class="lotto-toggle" data-togglename="<?php echo htmlspecialchars(_("[hide]")); ?>">
                <?= _("[show]"); ?>
            </a>
        </small>
    </h3>
    <div class="widget-featured-toggle-language">
        <p>
            <label for="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_order'; ?>">
                <?= _('Order'); ?>:
            </label>
            <select class="widefat lotto-widget-featured-type" id="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_order'; ?>" name="<?php echo $this->get_field_name('settings') . '[' . $letterOnlyLocale . '][order]'; ?>">
                <option value="" <?php if (empty($settings[$letterOnlyLocale]['order'])) : echo ' selected="selected"'; endif; ?>>
                    <?= _("<< DO NOT USE >> - by highest jackpot"); ?>
                </option>
                <option value="1" <?php if (isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "1") : echo ' selected="selected"'; endif; ?>>
                    <?= _("<< DO NOT USE >> - by nearest draw"); ?>
                </option>
                <option value="2" <?php if (isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "2") : echo ' selected="selected"'; endif; ?>>
                    <?= _("<< DO NOT USE >> - by custom order"); ?>
                </option>
                <option value="3" <?php if (isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "3") : echo ' selected="selected"'; endif; ?>>
                    <?= _("NEW - lotteries, keno and raffle with highest jackpot"); ?>
                </option>
                <option value="4" <?php if (isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "4") : echo ' selected="selected"'; endif; ?>>
                    <?= _("NEW - lotteries by custom order"); ?>
                </option>
                <option value="5" <?php if (isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "5") : echo ' selected="selected"'; endif; ?>>
                    <?= _("NEW - lotteries by nearest draw"); ?>
                </option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_count'; ?>">
                <?= _('Slide count'); ?>:
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_count'; ?>" name="<?php echo $this->get_field_name('settings') . '[' . $letterOnlyLocale . '][count]'; ?>" type="text" value="<?php echo isset($settings[$letterOnlyLocale]['count']) ? $settings[$letterOnlyLocale]['count'] : $this->default_count; ?>" />
        </p>

        <table class="widefat">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col"><?= _('Lottery name'); ?></th>
                    <th scope="col"><?= _('Custom name'); ?></th>
                    <th scope="col" class="lotto-widget-featured-toggle-order<?php if (!(isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "4")) : echo ' hidden'; endif; ?>"><?= _('Custom order'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lotteries['__by_id'] as $lid => $lottery) : ?>
                    <tr>
                        <th scope="row"><?= $lid ?></th>
                        <td><?= _($lottery['name']); ?></td>
                        <td>
                            <input class="widefat" id="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_lotteries_' . $lid . '_name'; ?>" name="<?php echo $this->get_field_name('settings') . '[' . $letterOnlyLocale . '][lotteries][' . $lid . '][name]'; ?>" type="text" value="<?php echo isset($settings[$letterOnlyLocale]['lotteries'][$lid]['name']) ? $settings[$letterOnlyLocale]['lotteries'][$lid]['name'] : ''; ?>">
                        </td>
                        <td class="lotto-widget-featured-toggle-order<?php if (!(isset($settings[$letterOnlyLocale]['order']) && $settings[$letterOnlyLocale]['order'] == "4")) : echo ' hidden'; endif; ?>">
                            <input class="widefat" id="<?php echo $this->get_field_id('settings') . '_' . $letterOnlyLocale . '_lotteries_' . $lid . '_order'; ?>" name="<?php echo $this->get_field_name('settings') . '[' . $letterOnlyLocale . '][lotteries][' . $lid . '][order]'; ?>" type="text" value="<?php echo isset($settings[$letterOnlyLocale]['lotteries'][$lid]['order']) ? $settings[$letterOnlyLocale]['lotteries'][$lid]['order'] : ''; ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>
