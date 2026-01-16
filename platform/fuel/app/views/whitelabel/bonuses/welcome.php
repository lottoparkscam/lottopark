<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");

/**
 * @var Fuel\Core\View $this
 * @var array $purchaseLotteries
 * @var array $registerLotteries
 * @var string $lotteryPurchaseClassError
 * @var string $lotteryRegisterClassError
 * @var bool $registerWebsiteChecked
 * @var bool $registerApiChecked
 */
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/bonuses/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Welcome bonus"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can change welcome bonus here."); ?>
        </p>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form id="form-welcome-bonus" method="post" action="/bonuses/welcome">
                    <?php
                        include(APPPATH . "views/whitelabel/shared/messages.php");
                        if (!empty($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>

                    <div class="form-group <?= $lotteryPurchaseClassError; ?>">
                        <label class="control-label" for="inputLotteryPurchase">
                            <?= _("Free bonus ticket after first purchase"); ?>:
                        </label>
                        <select name="input[lottery_purchase]" id="inputLotteryPurchase" class="form-control">
                            <?php
                                foreach ($purchaseLotteries as $lottery):
                            ?>
                                    <option value="<?= $lottery['id']; ?>" <?= $lottery['selected'] ?? null; ?>>
                                        <?= $lottery['name']; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="form-group <?= $lotteryRegisterClassError; ?>">
                        <label class="control-label" for="inputLotteryRegister">
                            <?= _("Free bonus ticket after registration"); ?>:
                        </label>
                        <select name="input[lottery_register]" id="inputLotteryRegister" class="form-control">
                            <?php
                            foreach ($registerLotteries as $lottery):
                                ?>
                                <option value="<?= $lottery['id']; ?>" <?= $lottery['selected'] ?? null; ?>>
                                    <?= $lottery['name']; ?>
                                </option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="form-group" id="register-options">
                        <p class="help-block">
                            <span class="text-warning">
                               Warning: <?= _('Purchase of a free ticket is not protected against multiple registrations from one IP address.') ?>
                            </span>
                        </p>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="registerWebsite" name="input[register_website]" <?= $registerWebsiteChecked ? 'checked' : '' ?> value="1">
                                <?= _("Website Sign Up") ?>
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="registerApi" name="input[register_api]" <?= $registerApiChecked ? 'checked' : '' ?> value="1">
                                <?= _("API Sign Up") ?>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
