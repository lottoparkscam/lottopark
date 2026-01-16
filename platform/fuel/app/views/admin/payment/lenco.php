<?php

use Core\App;

/** @var App $app */
?>
<div id="paymentDetailsLenco" class="payment-details hidden">
    <h3>
        <?= _('Lenco integration details'); ?>
    </h3>

    <div class="form-group<?= $inputHasErrorLenco('api_pub_key') ?>">
        <label class="control-label" for="inputLencoApiPubKey">
            <?= _('API Public Key') ?>:
        </label>
        <input type="text"
           value="<?= $inputLastValueLenco('api_pub_key') ?>"
           class="form-control"
           id="inputLencoApiPubKey"
           name="input[lenco_api_pub_key]"
           placeholder="<?= _('Enter API Public key') ?>">
    </div>

    <div class="form-group<?= $inputHasErrorLenco('api_key_secret') ?>">
        <label class="control-label" for="inputLencoApiKeySecret">
            <?= _('API Key Secret') ?>:
        </label>
        <input type="text"
           value="<?= $inputLastValueLenco('api_key_secret') ?>"
           class="form-control"
           id="inputLencoApiKeySecret"
           name="input[lenco_api_key_secret]"
           placeholder="<?= _('Enter API key secret') ?>">
    </div>

    <?php if ($app->isNotProduction()): ?>
    <div class="checkbox">
        <label>
            <input type="checkbox"
               name="input[lenco_is_test]"
               value="1" <?= $isCheckedLenco('is_test') ?>>
            <?= _('Test account') ?>
        </label>
        <p class="help-block"><?= _('Check it for test account.') ?>
    </div>
    <?php endif; ?>
</div>
