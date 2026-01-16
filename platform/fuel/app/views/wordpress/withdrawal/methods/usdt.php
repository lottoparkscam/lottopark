<?php
$usdt_wallet_type_error_class = '';
if (isset($this->errors['withdrawal.add.usdt_wallet_type'])) {
    $name_error_class = ' has-error';
}

$usdt_wallet_type = '';
if (Input::post("withdrawal.add.usdt_wallet_type") !== null) {
    $usdt_wallet_type = stripslashes(Input::post("withdrawal.add.usdt_wallet_type"));
}

$usdt_wallet_address_error_class = '';
if (isset($this->errors['withdrawal.add.usdt_wallet_address'])) {
    $usdt_wallet_address_error_class = ' has-error';
}

$usdt_wallet_address = '';
if (Input::post("withdrawal.add.usdt_wallet_address") !== null) {
    $usdt_wallet_address = stripslashes(Input::post("withdrawal.add.usdt_wallet_address"));
}

$email_error_class = '';
if (isset($this->errors['withdrawal.add.email'])) {
    $email_error_class = ' has-error';
}

$email = '';
if (Input::post("withdrawal.add.email") !== null) {
    $email = stripslashes(Input::post("withdrawal.add.email"));
}

?>
<div class="form-group <?= $usdt_wallet_type_error_class; ?>">
    <label for="inputUsdtWalletType">
        <?= Security::htmlentities(_("USDT Wallet Type")); ?>:
    </label>
    <select
        value="<?= $usdt_wallet_type; ?>"
        class="form-control"
        id="inputUsdtWalletType"
        name="withdrawal[add][usdt_wallet_type]"
        required
        autofocus
    >
        <option disabled selected><?= htmlspecialchars(_("Your USDT wallet type")); ?></option>
        <option>ERC20</option>
        <option>TRC20</option>
        <option>OMNI</option>
    </select>
</div>

<div class="form-group <?= $usdt_wallet_address_error_class; ?>">
    <label for="inputUsdtWalletAddress">
        <?= Security::htmlentities(_("USDT Wallet Address")); ?>:
    </label>
    <input type="text" required
           value="<?= $usdt_wallet_address; ?>"
           class="form-control"
           id="inputUsdtWalletAddress"
           name="withdrawal[add][usdt_wallet_address]"
           placeholder="<?= htmlspecialchars(_("Your USDT wallet address")); ?>">
</div>

<div class="form-group <?= $email_error_class; ?>">
    <label for="inputEmail">
        <?= Security::htmlentities(_("Email address")); ?>:
    </label>
    <input type="text" required autofocus 
           value="<?= $email; ?>"
           class="form-control" 
           id="inputEmail"
           name="withdrawal[add][email]"
           placeholder="<?= htmlspecialchars(_("Your email")); ?>">
</div>
