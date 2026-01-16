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

$netelleremail_error_class = '';
if (isset($this->errors['withdrawal.add.neteller_email'])) {
    $netelleremail_error_class = ' has-error';
}
$user_ne = '';
if (Input::post("withdrawal.add.neteller_email") !== null) {
    $user_ne = stripslashes(Input::post("withdrawal.add.neteller_email"));
}
$user_netelleremail = htmlspecialchars($user_ne);

?>
<div class="form-group <?= $name_error_class; ?>">
    <label for="inputName">
        <?= Security::htmlentities(_("First Name")); ?>:
    </label>
    <input type="text" required autofocus 
           value="<?= $user_name; ?>" 
           class="form-control" 
           id="inputName" 
           name="withdrawal[add][name]" 
           placeholder="<?= htmlspecialchars(_("Your first name")); ?>">
</div>

<div class="form-group <?= $surname_error_class; ?>">
    <label for="inputSurName">
        <?= Security::htmlentities(_("Last Name")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_surname; ?>" 
           class="form-control" 
           id="inputSurName" 
           name="withdrawal[add][surname]" 
           placeholder="<?= htmlspecialchars(_("Your last name")); ?>">
</div>

<div class="form-group <?= $netelleremail_error_class; ?>">
    <label for="inputNetellerEmail">
        <?= Security::htmlentities(_("Neteller E-mail")); ?>:
    </label>
    <input type="email" required 
           value="<?= $user_netelleremail; ?>" 
           class="form-control" 
           id="inputNetellerEmail" 
           name="withdrawal[add][neteller_email]"
           placeholder="<?= htmlspecialchars(_("Your Neteller e-mail")); ?>">
</div>
