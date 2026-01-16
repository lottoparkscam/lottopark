<?php

        use Models\WhitelabelRaffle;

if (!defined('WPINC')) {
    die;
}

$selected_lottery = null;
$selected_lottery_slug = null;

if (isset($lottery))
{
    $selected_lottery = $lottery;
    $selected_lottery_slug = explode('_', $selected_lottery)[1];
}

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
$lotteries = Model_Whitelabel::get_lotteries_by_custom_order_for_whitelabel($whitelabel['id'], ['lottery.name']);
$whitelabel_raffle_lotteries = WhitelabelRaffle::find("all", [
    'where' => [
        'whitelabel_id' => $whitelabel['id']
    ]
]);

/** @var WhitelabelRaffle $whitelabel_raffle */
foreach ($whitelabel_raffle_lotteries as $whitelabel_raffle)
{
    $lotteries[] = $whitelabel_raffle->raffle;
}
?>
<p>
	<label for="<?= $this->get_field_id('lottery'); ?>">
        <?= Security::htmlentities(_('Lottery')); ?>:
    </label>
    <br>
	<select
            class="widefat"
            id="<?= $this->get_field_id('lottery'); ?>"
            name="<?= $this->get_field_name('lottery'); ?>"
    >
        <?php
            foreach ($lotteries as $lottery):
            $lottery_type = $lottery instanceof Raffle ? 'raffle' : 'lottery';
                $lottery_is_selected = $lottery['slug'] === $selected_lottery_slug ? 'selected' : '';
        ?>
                <option value="<?= $lottery_type . '_' . $lottery['slug']; ?>" <?= $lottery_is_selected ?>>
                    <?= Security::htmlentities(_($lottery['name'])); ?>
                </option>
        <?php endforeach; ?>
	</select>
</p>