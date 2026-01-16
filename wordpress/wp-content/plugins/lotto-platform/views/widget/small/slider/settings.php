<?php
if (!defined('WPINC')) {
    die;
}
use Fuel\Core\Security;
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
    <br>
    <?php echo _("The margins are calculated automatically."); ?>
</p>

<?php if (!empty($gamesCountToDisplay)): ?>
    <p>
    <label for="<?= $this->get_field_id('games_count'); ?>">
        <?= Security::htmlentities(_('Games count to display')); ?>:
    </label>
    <input class="widefat" 
           id="<?= $this->get_field_id('games_count'); ?>" 
           name="<?= $this->get_field_name('games_count'); ?>" 
           type="number" 
           value="<?= $gamesCountToDisplay; ?>" />
    <br>
    <?=  _("The number of games should be divisible by 8 [the count of games per slide]."); ?>
    <?= _('NOTE: This widget will not be displayed if the site for internal slot init does not exist'); ?>
</p>

<?php endif; ?>