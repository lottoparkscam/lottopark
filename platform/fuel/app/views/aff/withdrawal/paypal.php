<?php
    $paypal_first_name_error_class = '';
    if (isset($this->errors['input.ppname'])) {
        $paypal_first_name_error_class = ' has-error';
    }
    
    $paypal_first_name_value = '';
    if (Input::post("input.ppname") !== null) {
        $paypal_first_name_value = Input::post("input.ppname");
    } elseif (isset($data['name'])) {
        $paypal_first_name_value = $data['name'];
    } elseif (isset($user['name'])) {
        $paypal_first_name_value = $user['name'];
    }
    
    $paypal_surname_error_class = '';
    if (isset($this->errors['input.ppsurname'])) {
        $paypal_surname_error_class = ' has-error';
    }
    
    $paypal_surname_value = '';
    if (Input::post("input.ppsurname") !== null) {
        $paypal_surname_value = Input::post("input.ppsurname");
    } elseif (isset($data['surname'])) {
        $paypal_surname_value = $data['surname'];
    } elseif (isset($user['surname'])) {
        $paypal_surname_value = $user['surname'];
    }
    
    $paypal_email_error_class = '';
    if (isset($this->errors['input.ppemail'])) {
        $paypal_email_error_class = ' has-error';
    }
    
    $paypal_email_value = '';
    if (Input::post("input.ppemail") !== null) {
        $paypal_email_value = Input::post("input.ppemail");
    } elseif (isset($data['ppemail'])) {
        $paypal_email_value = $data['ppemail'];
    }
?>
<div id="paymentDetailsPaypal" class="payment-details hidden">
    <h3><?= _("Pay Pal"); ?></h3>
    
    <div class="form-group <?= $paypal_first_name_error_class; ?>">
        <label for="inputPaypalName">
            <?= _("Name"); ?>:
        </label>
        <input type="text"
               value="<?= $paypal_first_name_value; ?>"
               class="form-control"
               id="inputPaypalName"
               name="input[ppname]"
               placeholder="<?= _("Enter name"); ?>">
    </div>
    
    <div class="form-group <?= $paypal_surname_error_class; ?>">
        <label for="inputPaypalSurName">
            <?= _("Surname"); ?>:
        </label>
        <input type="text"
               value="<?= $paypal_surname_value; ?>"
               class="form-control"
               id="inputPaypalSurName"
               name="input[ppsurname]"
               placeholder="<?= _("Enter surname"); ?>">
    </div>
    
    <div class="form-group <?= $paypal_email_error_class; ?>">
        <label for="inputPaypalEmail">
            <?= _("PayPal e-mail"); ?>:
        </label>
        <input type="email"
               value="<?= $paypal_email_value; ?>"
               class="form-control"
               id="inputPaypalEmail"
               name="input[ppemail]"
               placeholder="<?= _("Enter e-mail"); ?>">
    </div>
</div>