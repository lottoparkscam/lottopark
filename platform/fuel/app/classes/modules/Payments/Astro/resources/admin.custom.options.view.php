<?php

use Modules\Payments\Astro\Form\AstroCustomOptionsValidation;

global $data, $errors;

$form = new AstroCustomOptionsValidation();
$data = $form->prepare_data_to_show($data, $errors);
?>

<div id="paymentDetailsAstro" class="payment-details hidden">
    <h3>
        <?= $data['title']; ?>
    </h3>

    <?php if (isset($data['sub_title'])) : ?>
    <p class="help-block">
        <?= $data['sub_title'] ?>
    </p>
    <?php endif; ?>

    <div class="form-group">
        <label class="control-label" for="inputAstroBaseUrl">
            <?= _("Astro base url") ?>:
        </label>
        <input id="inputAstroBaseUrl"
                type="text"
               value="<?= $data['astro_base_url'] ?>"
               class="form-control"
               name="input[astro_base_url]"
               placeholder="<?= $data['help']['astro_base_url'] ?>"/>
    </div>

    <div class="form-group">
        <label class="control-label" for="inputAstroApiKey">
            <?= _("Astro api key") ?>:
        </label>
        <input id="inputAstroApiKey"
                type="text"
               value="<?= $data['astro_api_key'] ?>"
               class="form-control"
               name="input[astro_api_key]"
               placeholder="<?= $data['help']['astro_api_key'] ?>"/>
    </div>

    <div class="form-group">
        <label class="control-label" for="inputAstroApiKey">
            <?= _("Astro secret key") ?>:
        </label>
        <input id="inputAstroApiKey"
                type="text"
               value="<?= $data['astro_secret_key'] ?>"
               class="form-control"
               name="input[astro_secret_key]"
               placeholder="<?= $data['help']['astro_secret_key'] ?>"/>
    </div>

    <div class="form-group">
        <label class="control-label" for="inputAstroApiKey">
            <?= _("Astro default country (ISO alpha-2)") ?>:
        </label>
        <input id="inputAstroApiKey"
                type="text"
               value="<?= $data['astro_default_country'] ?>"
               class="form-control"
               name="input[astro_default_country]"
               placeholder="<?= $data['help']['astro_default_country'] ?>"/>
    </div>
</div>
