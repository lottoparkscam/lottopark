<?php

use Modules\Payments\Jeton\Form\JetonCustomOptionsValidation;

global $data, $errors;

$form = new JetonCustomOptionsValidation();
$data = $form->prepare_data_to_show($data, $errors);
?>

<div>
    <h3>
        <?= $data['title']; ?>
    </h3>

    <?php if (isset($data['sub_title'])): ?>
    <p class="help-block">
        <?= $data['sub_title'] ?>
    </p>
    <?php endif; ?>

    <div class="form-group">
        <label class="control-label" for="inputJetonBaseUrl">
            <?= _("Jeton base url") ?>:
        </label>
        <input id="inputJetonBaseUrl"
                type="text"
               value="<?= $data['base_url'] ?>"
               class="form-control"
               name="input[base_url]"
               placeholder="<?= $data['help']['base_url'] ?>"/>
    </div>

    <div class="form-group">
        <label class="control-label" for="inputJetonReturnUrl">
            <?= _("Jeton return url") ?>:
        </label>
        <input id="inputJetonBaseUrl"
                type="text"
               value="<?= $data['return_url'] ?>"
               class="form-control"
               name="input[return_url]"
               placeholder="<?= $data['help']['return_url'] ?>"/>
    </div>

    <div class="form-group">
        <label class="control-label" for="inputJetonApiKey">
            <?= _("Jeton api key") ?>:
        </label>
        <input id="inputJetonApiKey"
                type="text"
               value="<?= $data['api_key'] ?>"
               class="form-control"
               name="input[api_key]"
               placeholder="<?= $data['help']['api_key'] ?>"/>
    </div>
</div>
