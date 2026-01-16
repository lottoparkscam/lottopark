<?php
if (!defined('WPINC')) {
    die;
}

if ($show_lotteries):
?>
    <p>
        <label for="<?php echo $this->get_field_id('lottery'); ?>">
            <?php echo Security::htmlentities(_('Lottery')); ?>:
        </label>
        <br>	
        <select class="widefat" 
                id="<?php echo $this->get_field_id('lottery'); ?>" 
                name="<?php echo $this->get_field_name('lottery'); ?>">
            <?php
                foreach ($lotteries['__by_id'] as $item):
                    $lottery_selected = '';
                    if ((empty($lottery) && $item['id'] == 1) ||
                        $lottery == $item['id']
                    ) {
                        $lottery_selected = ' selected="selected"';
                    }
            ?>
                <option value="<?php echo $item['id']; ?>"<?php echo $lottery_selected; ?>>
                    <?php echo Security::htmlentities(_($item['name'])); ?>
                </option>
            <?php
                endforeach;
            ?>
        </select>
    </p>
<?php
endif;
?>

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