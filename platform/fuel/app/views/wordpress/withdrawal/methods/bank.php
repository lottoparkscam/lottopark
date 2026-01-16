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

$address_error_class = '';
if (isset($this->errors['withdrawal.add.address'])) {
    $address_error_class = ' has-error';
}
$user_a = '';
if (Input::post("withdrawal.add.address") !== null) {
    $user_a = stripslashes(Input::post("withdrawal.add.address"));
}
$user_address = htmlspecialchars($user_a);

$accountno_error_class = '';
if (isset($this->errors['withdrawal.add.account_no'])) {
    $accountno_error_class = ' has-error';
}
$user_an = '';
if (Input::post("withdrawal.add.account_no") !== null) {
    $user_an = stripslashes(Input::post("withdrawal.add.account_no"));
}
$user_accountno = htmlspecialchars($user_an);

$accountswift_error_class = '';
if (isset($this->errors['withdrawal.add.account_swift'])) {
    $accountswift_error_class = ' has-error';
}
$user_as = '';
if (Input::post("withdrawal.add.account_swift") !== null) {
    $user_as = stripslashes(Input::post("withdrawal.add.account_swift"));
}
$user_accountswift = htmlspecialchars($user_as);

$bankname_error_class = '';
if (isset($this->errors['withdrawal.add.bank_name'])) {
    $bankname_error_class = ' has-error';
}
$user_b = '';
if (Input::post("withdrawal.add.bank_name") !== null) {
    $user_b = stripslashes(Input::post("withdrawal.add.bank_name"));
}
$user_bankname = htmlspecialchars($user_b);

$bankaddress_error_class = '';
if (isset($this->errors['withdrawal.add.bank_address'])) {
    $bankaddress_error_class = ' has-error';
}
$user_ba = '';
if (Input::post("withdrawal.add.bank_address") !== null) {
    $user_ba = stripslashes(Input::post("withdrawal.add.bank_address"));
}
$user_bankaddress = htmlspecialchars($user_ba);

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

<div class="form-group <?= $address_error_class; ?>">
    <label for="inputAddress">
        <?= Security::htmlentities(_("Address")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_address; ?>" 
           class="form-control" 
           id="inputAddress" 
           name="withdrawal[add][address]" 
           placeholder="<?= htmlspecialchars(_("Your address")); ?>">
</div>

<div class="form-group <?= $accountno_error_class; ?>">
    <label for="inputAccountNo">
        <?= Security::htmlentities(_("IBAN / Account number")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_accountno; ?>" 
           class="form-control" 
           id="inputAccountNo" 
           name="withdrawal[add][account_no]"
           placeholder="<?= htmlspecialchars(_("Your IBAN / Account number")); ?>">
</div>

<div class="form-group <?= $accountswift_error_class; ?>">
    <label for="inputAccountSwift">
        <?= Security::htmlentities(_("Routing number / SWIFT / BIC")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_accountswift; ?>" 
           class="form-control" 
           id="inputAccountSwift" 
           name="withdrawal[add][account_swift]"
           placeholder="<?= htmlspecialchars(_("Routing number / SWIFT / BIC")); ?>">
</div>

<div class="form-group <?= $bankname_error_class; ?>">
    <label for="inputBankName">
        <?= Security::htmlentities(_("Bank name")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_bankname; ?>" 
           class="form-control" 
           id="inputBankName" 
           name="withdrawal[add][bank_name]"
           placeholder="<?= htmlspecialchars(_("Your bank name")); ?>">
</div>

<div class="form-group <?= $bankaddress_error_class; ?>">
    <label for="inputBankAddress">
        <?= Security::htmlentities(_("Bank Address")); ?>:
    </label>
    <input type="text" required 
           value="<?= $user_bankaddress; ?>" 
           class="form-control" 
           id="inputBankAddress" 
           name="withdrawal[add][bank_address]"
           placeholder="<?= htmlspecialchars(_("Your bank address")); ?>">
</div>
