<?php

use Modules\Payments\Tamspay\Form\TamspayCustomOptionsValidation;

global $data, $errors;

$form = new TamspayCustomOptionsValidation();
$data = $form->prepare_data_to_show($data, $errors);
?>

<div id="paymentDetailsTamspay" class="payment-details hidden">
    <h3>
        <?= $data['title']; ?>
    </h3>

    <?php if (isset($data['sub_title'])) : ?>
        <p class="help-block">
            <?= $data['sub_title'] ?>
        </p>
    <?php endif; ?>

    <div class="form-group">
        <label class="control-label" for="inputTamspayApiKey">
            <?= _("Tamspay sid key") ?>:
        </label>
        <input id="inputTamspayApiKey"
               type="text"
               value="<?= $data['tamspay_sid'] ?>"
               class="form-control"
               name="input[tamspay_sid]"
               placeholder="<?= $data['help']['tamspay_sid'] ?>"/>
    </div>
</div>
