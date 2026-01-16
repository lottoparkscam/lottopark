<?php

use Modules\Payments\Trustpayments\Form\TrustpaymentsCustomOptionsValidation;

global $data, $errors;

$form = new TrustpaymentsCustomOptionsValidation();
$data = $form->prepare_data_to_show($data, $errors);
?>

<div id="paymentDetailsTrustpayments" class="payment-details hidden">
    <h3>
        <?= $data['title']; ?>
    </h3>

    <?php if (isset($data['sub_title'])) : ?>
        <p class="help-block">
            <?= $data['sub_title'] ?>
        </p>
    <?php endif; ?>

    <div class="form-group">
        <label class="control-label" for="inputTrustpaymentsSitereference">
            <?= _("Trustpayments sitereference") ?>:
        </label>
        <input id="inputTrustpaymentsSitereference"
               type="text"
               value="<?= $data['trustpayments_sitereference'] ?>"
               class="form-control"
               name="input[trustpayments_sitereference]"
               placeholder="<?= $data['help']['trustpayments_sitereference'] ?>"/>
    </div>
</div>
