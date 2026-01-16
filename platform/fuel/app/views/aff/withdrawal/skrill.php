<?php
    $skrill_first_name_error_class = '';
    if (isset($this->errors['input.sname'])) {
        $skrill_first_name_error_class = ' has-error';
    }
    
    $skrill_first_name_value = '';
    if (Input::post("input.sname") !== null) {
        $skrill_first_name_value = Input::post("input.sname");
    } elseif (isset($data['name'])) {
        $skrill_first_name_value = $data['name'];
    } elseif (isset($user['name'])) {
        $skrill_first_name_value = $user['name'];
    }
    
    $skrill_surname_error_class = '';
    if (isset($this->errors['input.ssurname'])) {
        $skrill_surname_error_class = ' has-error';
    }
    
    $skrill_surname_value = '';
    if (Input::post("input.ssurname") !== null) {
        $skrill_surname_value = Input::post("input.ssurname");
    } elseif (isset($data['surname'])) {
        $skrill_surname_value = $data['surname'];
    } elseif (isset($user['surname'])) {
        $skrill_surname_value = $user['surname'];
    }
    
    $skrill_email_error_class = '';
    if (isset($this->errors['input.skrill_email'])) {
        $skrill_email_error_class = ' has-error';
    }
    
    $skrill_email_value = '';
    if (Input::post("input.skrill_email") !== null) {
        $skrill_email_value = Input::post("input.skrill_email");
    } elseif (isset($data['skrill_email'])) {
        $skrill_email_value = $data['skrill_email'];
    }
?>
<div id="paymentDetailsSkrill" class="payment-details hidden">
    <h3><?= _("Skrill"); ?></h3>
    
    <div class="form-group <?= $skrill_first_name_error_class; ?>">
        <label for="inputName">
            <?= _("First name"); ?>:
        </label>
        <input type="text" 
               autofocus 
               value="<?= $skrill_first_name_value; ?>" 
               class="form-control" 
               id="inputName" 
               name="input[sname]" 
               placeholder="<?= _("Enter first name"); ?>">
    </div>
    
    <div class="form-group <?= $skrill_surname_error_class; ?>">
        <label for="inputSurName">
            <?= _("Last name"); ?>:
        </label>
        <input type="text" 
               value="<?= $skrill_surname_value; ?>" 
               class="form-control" 
               id="inputSurName" 
               name="input[ssurname]" 
               placeholder="<?= _("Enter last name"); ?>">
    </div>
    
    <div class="form-group <?= $skrill_email_error_class; ?>">
        <label for="inputSkrillEmail">
            <?= _("Skrill e-mail"); ?>:
        </label>
        <input type="email" 
               value="<?= $skrill_email_value; ?>" 
               class="form-control" 
               id="inputSkrillEmail" 
               name="input[skrill_email]"
               placeholder="<?= _("Enter Skrill e-mail"); ?>">
    </div>
</div>