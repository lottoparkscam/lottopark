<?php

$exchangeErrorClass = '';
if (isset($this->errors['withdrawal.add.exchange'])) {
    $exchangeErrorClass = ' has-error';
}

$exchange = '';
if (Input::post("withdrawal.add.exchange") !== null) {
    $exchange = stripslashes(Input::post("withdrawal.add.exchange"));
}

$nameErrorClass = '';
if (isset($this->errors['withdrawal.add.name'])) {
    $nameErrorClass = ' has-error';
}

$name = '';
if (Input::post("withdrawal.add.name") !== null) {
    $name = stripslashes(Input::post("withdrawal.add.name"));
}

$emailErrorClass = '';
if (isset($this->errors['withdrawal.add.email'])) {
    $emailErrorClass = ' has-error';
}

$email = '';
if (Input::post("withdrawal.add.email") !== null) {
    $email = stripslashes(Input::post("withdrawal.add.email"));
}

?>
<div class="form-group <?= $exchangeErrorClass; ?>">
    <label for="inputExchange">
        <?= Security::htmlentities(_("Exchange")); ?>:
    </label>
    <select
        value="<?= $exchange; ?>"
        class="form-control"
        id="inputExchange"
        name="withdrawal[add][exchange]"
        required
        autofocus
    >
        <?php foreach (Forms_Wordpress_Withdrawal_CryptoExchanges::OPTIONS as $option) : ?>
        <option><?= $option ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group <?= $nameErrorClass; ?>">
    <label for="inputName">
        <?= Security::htmlentities(_("Your name")); ?>:
    </label>
    <input type="text" required
           value="<?= $name; ?>"
           class="form-control"
           id="inputName"
           name="withdrawal[add][name]"
           placeholder="<?= htmlspecialchars(_("Your name")); ?>">
</div>

<div class="form-group <?= $emailErrorClass; ?>">
    <label for="inputEmail">
        <?= Security::htmlentities(_("Your email on the exchange")); ?>:
    </label>
    <input type="text" required autofocus
           value="<?= $email; ?>"
           class="form-control"
           id="inputEmail"
           name="withdrawal[add][email]"
           placeholder="<?= htmlspecialchars(_("Your email on the exchange")); ?>">
</div>
