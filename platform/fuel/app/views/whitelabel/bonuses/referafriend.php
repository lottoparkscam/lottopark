<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/bonuses/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Refer a friend bonus"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can change refer a friend bonus here."); ?>
        </p>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" action="/bonuses/referafriend">
                    <?php
                        include(APPPATH . "views/whitelabel/shared/messages.php");
                        if (!empty($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    <div class="form-group">
                        <label for="minTotalPurchase"><?= _('Minimum total purchase after free ticket will be assigned') ?></label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <?= Lotto_View::format_currency_code($currency_code); ?>
                            </div>
                            <input type="text" class="form-control" id="minTotalPurchase" name="min_total_purchase" value="<?= $min_total_purchase ?>" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="inputLottery">
                            <?= _("Choose lottery"); ?>:
                        </label>
                        <select name="lottery" id="inputLottery" class="form-control">
                            <option value="<?= $lottery_id ?>">
                                None
                            </option>
                            <?php
                                foreach ($lotteries as $lottery):
                            ?>
                                    <option value="<?= $lottery['id']; ?>" <?= $lottery_id == $lottery['id'] ? 'selected="selected"' : '' ?>>
                                        <?= $lottery['name']; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
