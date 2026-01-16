<?php
if (!defined('WPINC')) {
    die;
}
?>
<p>
    <label for="<?= $this->get_field_id('title') ?>">
        <?= _('Title') ?>:
    </label>
    <input class="widefat"
           id="<?= $this->get_field_id('title') ?>"
           name="<?= $this->get_field_name('title') ?>"
           type="text"
           value="<?= $title ?>" />
</p>

<p>
    <label for="<?= $this->get_field_id('width') ?>">
        <?= _('Width (%)') ?>:
    </label>
    <input class="widefat"
           id="<?= $this->get_field_id('width') ?>"
           name="<?= $this->get_field_name('width') ?>"
           type="number"
           value="<?= $width ?>" />
    <br>
    <?= _('The margins are calculated automatically.') ?>
</p>

<?php if (!empty($gamesCountToDisplay)): ?>
    <p>
        <label for="<?= $this->get_field_id('games_count') ?>">
            <?= _('Games count to display') ?>:
        </label>
        <input class="widefat"
               id="<?= $this->get_field_id('games_count'); ?>"
               name="<?= $this->get_field_name('games_count') ?>"
               type="number"
               value="<?= $gamesCountToDisplay; ?>" />
        <br>
        <?=  _('The number of games should be divisible by 8 [the count of games per slide].'); ?>
        <?= _('NOTE: This widget will not be displayed if the site for internal slot init does not exist'); ?>
    </p>
<?php endif; ?>

<p>
  <label for="<?= $this->get_field_id('api_url') ?>">
      <?= _('API URL') ?>:
  </label>
  <input class="widefat"
         id="<?= $this->get_field_id('api_url') ?>"
         name="<?= $this->get_field_name('api_url') ?>"
         type="text"
         value="<?= $apiUrl ?>" />
  <br>
    <?=  _("Link to the new casino's API from which games will be fetched for the slider."); ?><br>
    <?= _('NOTE: for example "https://new.lottopark.com/casino/api/internal/limited-games"'); ?>
</p>

<p>
  <label for="<?= $this->get_field_id('casino_url') ?>">
      <?= _('CASINO URL') ?>:
  </label>
  <input class="widefat"
         id="<?= $this->get_field_id('casino_url') ?>"
         name="<?= $this->get_field_name('casino_url') ?>"
         type="text"
         value="<?= $casinoUrl ?>" />
  <br>
    <?=  _("Link to the casino website."); ?><br>
    <?= _('NOTE: for example "https://casino.lottopark.com'); ?>
</p>
