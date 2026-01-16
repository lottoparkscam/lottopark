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

$fairox_account_id_error_class = '';
if (isset($this->errors['withdrawal.add.fairox_account_id'])) {
    $fairox_account_id_error_class = ' has-error';
}
$fairox_account_id = '';
if (Input::post("withdrawal.add.fairox_account_id") !== null) {
    $fairox_account_id = stripslashes(Input::post("withdrawal.add.fairox_account_id"));
}

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

<div class="form-group <?= $fairox_account_id_error_class; ?>">
    <label for="fairoxAccountId">
        <?= Security::htmlentities(_("Fairox Account ID")); ?>:
    </label>
    <input type="text" required autofocus 
           value="<?= $fairox_account_id; ?>"
           class="form-control" 
           id="fairoxAccountId"
           name="withdrawal[add][fairox_account_id]"
           placeholder="<?= htmlspecialchars(_("Fairox Account ID (user name or email)")); ?>">
</div>
