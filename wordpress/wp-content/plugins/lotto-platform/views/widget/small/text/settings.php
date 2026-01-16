<?php
use Helpers\UrlHelper;
    if (empty($instance)):
?>
        <p>
            <?php echo _("Please save before use."); ?>
        </p>
<?php
    else:
?>
        <p>
            <?php
                $text_to_format = _(
    'Please fill the fields in English and translate them using WPML ' .
                    'String Translation <a href="%s">here</a>.'
);
                $full_url = Lotto_Helper::get_URL() .
                    '/wp-admin/admin.php?page=wpml-string-translation%2Fmenu%2Fstring-translation.php&context=Widgets';
                echo sprintf($text_to_format, UrlHelper::esc_url($full_url));
            ?>
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
            if (!$show_lotteries):
        ?>
                <p>
                    <label for="<?php echo $this->get_field_id('title'); ?>">
                        <?php echo _('Title'); ?>:
                    </label>
                    <input class="widefat" 
                           id="<?php echo $this->get_field_id('title'); ?>" 
                           name="<?php echo $this->get_field_name('title'); ?>" 
                           type="text" 
                           value="<?php echo htmlspecialchars($title); ?>"/>
                </p>
                <p>
                    <label for="<?php echo $this->get_field_id('content'); ?>">
                        <?php echo _('Content'); ?>:
                    </label>
                    <textarea class="widefat" 
                              rows="16" 
                              cols="20" 
                              id="<?php echo $this->get_field_id('content'); ?>" 
                              name="<?php echo $this->get_field_name('content'); ?>"><?php
                        echo esc_textarea($content);
                    ?></textarea>
                </p>
        <?php
            else:
                foreach ($lotteries['__by_id'] as $lid => $lottery):
        ?>
                    <h4>
                        <?php echo Security::htmlentities(_($lottery['name'])); ?> 
                        <small>
                            <a href="#" 
                               class="<?php echo $this->id; ?>-toggle" 
                               data-togglename="<?php echo htmlspecialchars(_("[hide]")); ?>">
                                <?php echo Security::htmlentities(_("[show]")); ?>
                            </a>
                        </small>
                    </h4>
                    <div class="widget-small-text-toggle-content">
                        <p>
                            <label for="<?php echo $this->get_field_id('settings') . 'lotteries_' . $lid . '_title'; ?>">
                                <?php echo _('Title'); ?>:
                            </label>
                            <input class="widefat" 
                                   id="<?php echo $this->get_field_id('settings') . 'lotteries_' . $lid . '_title'; ?>" 
                                   name="<?php echo $this->get_field_name('settings') . '[lotteries][' . $lid . '][title]'; ?>" 
                                   type="text" 
                                   value="<?php echo isset($settings['lotteries'][$lid]['title']) ? $settings['lotteries'][$lid]['title'] : ''; ?>"/>
                        </p>
                        <p>
                            <label for="<?php echo $this->get_field_id('settings') . 'lotteries_' . $lid . '_content'; ?>">
                                <?php echo _('Content'); ?>:
                            </label>
                            <textarea class="widefat" rows="16" cols="20" 
                                      id="<?php echo $this->get_field_id('settings') . 'lotteries_' . $lid . '_content'; ?>" 
                                      name="<?php echo $this->get_field_name('settings') . '[lotteries][' . $lid . '][content]'; ?>"><?php
                                echo esc_textarea(isset($settings['lotteries'][$lid]['content']) ? $settings['lotteries'][$lid]['content'] : '');
                            ?></textarea>
                        </p>
                    </div>
        <?php
                endforeach;
            endif;
        ?>

        <script type="text/javascript">
            jQuery('.<?php echo $this->id; ?>-toggle').click(function(e) {
                var text = jQuery(this).text();
                e.preventDefault();
                jQuery(this).parent().parent().next().toggle();
                jQuery(this).text(jQuery(this).data('togglename'));
                jQuery(this).data("togglename", text);
            });
        </script>
<?php
    endif;
    
// 08.03.2019 15:26 Vordis TODO: presenter for this should be great
// Container option - user can choose container that should be used for widget (h2 or h1)
?>
<p>
    <label for="<?= $this->get_field_id('title_container'); ?>">
        <?= Security::htmlentities(_('Title Container')); ?>:
    </label>
    <br>
    <select class="widefat" 
            id="<?= $this->get_field_id('title_container'); ?>"
            name="<?= $this->get_field_name('title_container'); ?>">
        <option value="<?= Lotto_Widget_Small_Text::TITLE_CONTAINER_H2 ?>"<?= (empty($title_container) || $title_container == Lotto_Widget_List::TITLE_CONTAINER_H2) ? ' selected="selected"' : '' ?>>
            <?= Security::htmlentities(_("H2")); ?>
        </option>
        <option value="<?= Lotto_Widget_Small_Text::TITLE_CONTAINER_H1 ?>"<?= (isset($title_container) && $title_container == Lotto_Widget_Small_Text::TITLE_CONTAINER_H1) ? ' selected="selected"' : '' ?>>
            <?= Security::htmlentities(_("H1")); ?>
        </option>
    </select>
</p>
