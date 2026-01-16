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

$paypalemail_error_class = '';
if (isset($this->errors['withdrawal.add.paypal_email'])) {
    $paypalemail_error_class = ' has-error';
}
$user_e = '';
if (Input::post("withdrawal.add.paypal_email") !== null) {
    $user_e = stripslashes(Input::post("withdrawal.add.paypal_email"));
}
$user_paypalemail = htmlspecialchars($user_e);

?>
<div class="form-group <?= $name_error_class; ?>">
    <label for="inputName">
        <?= Security::htmlentities(_("Name")); ?>:
    </label>
    <input type="text" 
           required 
           autofocus 
           value="<?= $user_name; ?>" 
           class="form-control" 
           id="inputName" 
           name="withdrawal[add][name]" 
           placeholder="<?= htmlspecialchars(_("Your name")); ?>">
</div>

<div class="form-group <?= $surname_error_class; ?>">
    <label for="inputSurName">
        <?= Security::htmlentities(_("Surname")); ?>:
    </label>
    <input type="text" 
           required 
           value="<?= $user_surname; ?>" 
           class="form-control" 
           id="inputSurName" 
           name="withdrawal[add][surname]" 
           placeholder="<?= htmlspecialchars(_("Your surname")); ?>">
</div>

<div class="form-group <?= $paypalemail_error_class; ?>">
    <label for="inputPaypalEmail">
        <?= Security::htmlentities(_("Paypal E-mail")); ?>:
    </label>
    <input type="email" 
           required 
           value="<?= $user_paypalemail; ?>"
           class="form-control"
           id="inputPaypalEmail"
           name="withdrawal[add][paypal_email]"
           placeholder="<?= htmlspecialchars(_("Your PayPal e-mail")); ?>">
</div>
