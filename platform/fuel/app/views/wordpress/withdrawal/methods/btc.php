<?php

$name_error_class = '';
if (isset($this->errors['withdrawal.add.name'])) {
    $name_error_class = ' has-error';
}
$user_n = '';
if (Input::post("withdrawal.add.name") !== null) {
    $user_n = stripslashes(Input::post("withdrawal.add.name"));
} else {
    $user_n = $user['name'];
}
$user_name = htmlspecialchars($user_n);

$surname_error_class = '';
if (isset($this->errors['withdrawal.add.surname'])) {
    $surname_error_class = ' has-error';
}
$user_sn = '';
if (Input::post("withdrawal.add.surname") !== null) {
    $user_sn = stripslashes(Input::post("withdrawal.add.surname"));
} else {
    $user_sn = $user['surname'];
}
$user_surname = htmlspecialchars($user_sn);

$bitcoin_error_class = '';
if (isset($this->errors['withdrawal.add.bitcoin'])) {
    $bitcoin_error_class = ' has-error';
}
$user_bi = '';
if (Input::post("withdrawal.add.bitcoin") !== null) {
    $user_bi = stripslashes(Input::post("withdrawal.add.bitcoin"));
}
$user_bitcoin = htmlspecialchars($user_bi);

?>
<div class="form-group <?= $name_error_class; ?>">
    <label for="inputName">
        <?= Security::htmlentities(_("First Name (optional)")); ?>:
    </label>
    <input type="text" autofocus 
           value="<?= $user_name; ?>" 
           class="form-control" 
           id="inputName" 
           name="withdrawal[add][name]" 
           placeholder="<?= htmlspecialchars(_("Your first name")); ?>">
</div>

<div class="form-group <?= $surname_error_class; ?>">
    <label for="inputSurName"><?= Security::htmlentities(_("Last Name (optional)")); ?>:</label>
    <input type="text" 
           value="<?= $user_surname; ?>" 
           class="form-control" 
           id="inputSurName" 
           name="withdrawal[add][surname]" 
           placeholder="<?= htmlspecialchars(_("Your last name")); ?>">
</div>

<div class="form-group <?= $bitcoin_error_class; ?>">
    <label for="inputBitcoin">
        <?= Security::htmlentities(_("Your bitcoin wallet address")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_bitcoin; ?>" 
           class="form-control" 
           id="inputBitcoin" 
           name="withdrawal[add][bitcoin]" 
           placeholder="<?= htmlspecialchars(_("Bitcoin Wallet Address")); ?>">
</div>

