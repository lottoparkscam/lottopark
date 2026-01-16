<?php
    $neteller_first_name_error_class = '';
    if (isset($this->errors['input.nname'])) {
        $neteller_first_name_error_class = ' has-error';
    }
    
    $neteller_first_name_value = '';
    if (Input::post("input.nname") !== null) {
        $neteller_first_name_value = Input::post("input.nname");
    } elseif (isset($data['name'])) {
        $neteller_first_name_value = $data['name'];
    } elseif (isset($user['name'])) {
        $neteller_first_name_value = $user['name'];
    }
    
    $neteller_surname_error_class = '';
    if (isset($this->errors['input.nsurname'])) {
        $neteller_surname_error_class = ' has-error';
    }
    
    $neteller_surname_value = '';
    if (Input::post("input.nsurname") !== null) {
        $neteller_surname_value = Input::post("input.nsurname");
    } elseif (isset($data['surname'])) {
        $neteller_surname_value = $data['surname'];
    } elseif (isset($user['surname'])) {
        $neteller_surname_value = $user['surname'];
    }
    
    $neteller_email_error_class = '';
    if (isset($this->errors['input.neteller_email'])) {
        $neteller_email_error_class = ' has-error';
    }
    
    $neteller_email_value = '';
    if (Input::post("input.neteller_email") !== null) {
        $neteller_email_value = Input::post("input.neteller_email");
    } elseif (isset($data['neteller_email'])) {
        $neteller_email_value = $data['neteller_email'];
    }
?>
<div id="paymentDetailsNeteller" class="payment-details hidden">
    <h3><?= _("Neteller"); ?></h3>
    
    <div class="form-group <?= $neteller_first_name_error_class; ?>">
        <label for="inputName">
            <?= _("First name"); ?>:
        </label>
        <input type="text" 
               autofocus 
               value="<?= $neteller_first_name_value; ?>" 
               class="form-control" 
               id="inputName" 
               name="input[nname]" 
               placeholder="<?= _("Enter first name"); ?>">
    </div>
    
    <div class="form-group <?= $neteller_surname_error_class; ?>">
        <label for="inputSurName">
            <?= _("Last name"); ?>:
        </label>
        <input type="text" 
               value="<?= $neteller_surname_value; ?>" 
               class="form-control" 
               id="inputSurName" 
               name="input[nsurname]" 
               placeholder="<?= _("Enter last name"); ?>">
    </div>
    
    <div class="form-group <?= $neteller_email_error_class; ?>">
        <label for="inputNetellerEmail">
            <?= _("Neteller e-mail"); ?>:
        </label>
        <input type="email" 
               value="<?= $neteller_email_value; ?>" 
               class="form-control" 
               id="inputNetellerEmail" 
               name="input[neteller_email]"
               placeholder="<?= _("Enter Neteller e-mail"); ?>">
    </div>
</div>
