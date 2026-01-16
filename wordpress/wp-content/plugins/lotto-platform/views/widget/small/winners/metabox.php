<?php
if (!defined('WPINC')) {
    die;
}
?>

<div>
	<label for="small-winners-metabox-lottery" class="platform-label">
        <?php echo Security::htmlentities(_("Lottery")); ?>: 
    </label>
	<select id="small-winners-metabox-lottery" required autofocus name="winners[lottery]">
		<?php foreach ($lotteries['__by_id'] as $lottery): ?>
			<option<?php echo isset($data[0]) && $data[0]['lottery'] == $lottery['id'] ? ' selected="selected"' : ''; ?> 
                value="<?php echo $lottery['id']; ?>" 
                data-currency="<?php echo Lotto_View::format_currency_code($lottery['currency'], ICL_LANGUAGE_CODE); ?>">
                <?php echo Security::htmlentities(_($lottery['name'])); ?>
            </option>
		<?php endforeach; ?>
	</select>
</div>
<div>
	<label for="small-winners-metabox-name" class="platform-label">
        <?php echo Security::htmlentities(_("Name")); ?>: 
    </label>
	<input type="text" 
           value="<?php echo isset($data[0]) ? htmlspecialchars($data[0]['name']) : ''; ?>" 
           name="winners[name]" 
           required 
           id="small-winners-metabox-name">
</div>
<div>
	<label for="small-winners-metabox-country" class="platform-label">
        <?php echo Security::htmlentities(_("Country")); ?>: 
    </label>
	<select id="small-winners-metabox-country" required name="winners[country]">
		<?php foreach ($countries as $key => $country): ?>
			<option<?php echo isset($data[0]) && $data[0]['country'] == $key ? ' selected="selected"' : ''; ?> 
                value="<?php echo $key; ?>">
                <?php echo Security::htmlentities($country); ?>
            </option>
		<?php endforeach; ?>
	</select>
</div>
<div>
	<label for="small-winners-metabox-amount" 
           id="small-winners-metabox-amount-label" 
           class="platform-label">
        <?php echo wp_kses(_("Amount<span></span>"), array("span" => array())); ?>: 
    </label>
	<input type="text" 
           value="<?php echo isset($data[0]) ? htmlspecialchars($data[0]['amount']) : ''; ?>" 
           name="winners[amount]" 
           required 
           id="small-winners-metabox-amount">
</div>