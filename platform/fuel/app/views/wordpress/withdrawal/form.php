<?php

$type_error_class = '';
if (isset($this->errors['withdrawal.type'])) {
    $type_error_class = ' has-error';
}

$amount_error_class = '';
if (isset($this->errors['withdrawal.amount'])) {
    $amount_error_class = ' has-error';
}

$user_currency_code = lotto_platform_user_currency();
$f_code = Lotto_View::format_currency_code($user_currency_code);
$code_text = sprintf(_("Amount (%s)"), $f_code);
$code_text_prepared = Security::htmlentities($code_text);

$amount_temp = '';
if (Input::post("withdrawal.amount") !== null) {
    $amount_temp = stripslashes(Input::post("withdrawal.amount"));
}
$amount_prepared = htmlspecialchars($amount_temp);

$min_withdrawal_formatted = Lotto_View::format_currency(
    $minwithdrawal,
    $user_currency_code,
    true
);
$min_withdrawal_text_prepare = sprintf(
    _("<strong>Min. amount:</strong> %s"),
    $min_withdrawal_formatted
);
$min_withdrawal_text = wp_kses($min_withdrawal_text_prepare, ["strong" => []]);

$withdrawal_max_class = '';
if (!(Input::post("withdrawal.type") === null ||
    Input::post("withdrawal.type") != Helpers_Withdrawal_Method::WITHDRAWAL_DEBIT_CARD)
) {
    $withdrawal_max_class = ' class="hidden-normal"';
}

$max_withdrawal_formatted = Lotto_View::format_currency(
    $maxwithdrawal,
    $user_currency_code,
    true
);
$max_withdrawal_text_prepare = sprintf(
    _("<strong>Max. amount:</strong> %s"),
    $max_withdrawal_formatted
);
$max_withdrawal_text = wp_kses($max_withdrawal_text_prepare, ["strong" => []]);

$withdrawal_page = get_post(
    apply_filters(
        'wpml_object_id',
        lotto_platform_get_post_id_by_slug('withdrawal'),
        'page',
        true
    )
);

$withdrawal_content = apply_filters('the_content', $withdrawal_page->post_content);

?>
<div class="form-group <?= $type_error_class; ?>">
    <label for="inputWithdrawalType">
        <?= Security::htmlentities(_("Type")); ?>:
    </label>
    <select name="withdrawal[type]" id="inputWithdrawalType">
        <option value="0">
            <?= Security::htmlentities(_("Choose withdrawal type")); ?>
        </option>
        <?php
            foreach ($methods as $method):
                $selected = '';
                if (Input::post("withdrawal.type") !== null &&
                    Input::post("withdrawal.type") == $method->withdrawal_id
                ) {
                    $selected = ' selected="selected"';
                }
        ?>
                <option value="<?= $method->withdrawal_id; ?>"<?= $selected; ?>>
                    <?= _($method->withdrawal->name);?>
                </option>
        <?php
            endforeach;
        ?>
    </select>
</div>

<div class="form-group <?= $amount_error_class; ?>">
    <label for="inputWithdrawalAmount">
        <?= $code_text_prepared; ?>:
    </label>
    <input type="text" 
           autofocus 
           required 
           value="<?= $amount_prepared; ?>" 
           class="form-control" 
           id="inputWithdrawalAmount" 
           name="withdrawal[amount]" 
           placeholder="<?= htmlspecialchars(_("Enter withdrawal amount")); ?>">
    <p class="help-block">
        <span id="minWithdrawalValue"><?= $min_withdrawal_text; ?></span>
        <br>
        <span id="withdrawalMax"<?= $withdrawal_max_class; ?>>
            <?= $max_withdrawal_text; ?>
        </span>
    </p>
</div>

<div>
    <?= $withdrawal_content ?>
</div>
