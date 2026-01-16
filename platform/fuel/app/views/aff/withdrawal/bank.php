<?php
    $bank_first_name_error_class = '';
    if (isset($this->errors['input.bname'])) {
        $bank_first_name_error_class = ' has-error';
    }
    
    $bank_first_name_value = '';
    if (Input::post("input.bname") !== null) {
        $bank_first_name_value = Input::post("input.bname");
    } elseif (isset($data['name'])) {
        $bank_first_name_value = $data['name'];
    } elseif (isset($user['name'])) {
        $bank_first_name_value = $user['name'];
    }
    
    $bank_surname_error_class = '';
    if (isset($this->errors['input.bsurname'])) {
        $bank_surname_error_class = ' has-error';
    }
    
    $bank_surname_value = '';
    if (Input::post("input.bsurname") !== null) {
        $bank_surname_value = Input::post("input.bsurname");
    } elseif (isset($data['surname'])) {
        $bank_surname_value = $data['surname'];
    } elseif (isset($user['surname'])) {
        $bank_surname_value = $user['surname'];
    }
    
    $bank_accountno_error_class = '';
    if (isset($this->errors['input.account_no'])) {
        $bank_accountno_error_class = ' has-error';
    }
    
    $bank_accountno_value = '';
    if (Input::post("input.account_no") !== null) {
        $bank_accountno_value = Input::post("input.account_no");
    } elseif (isset($data['account_no'])) {
        $bank_accountno_value = $data['account_no'];
    }
    
    $bank_accountswift_error_class = '';
    if (isset($this->errors['input.account_swift'])) {
        $bank_accountswift_error_class = ' has-error';
    }
    
    $bank_accountswift_value = '';
    if (Input::post("input.account_swift") !== null) {
        $bank_accountswift_value = Input::post("input.account_swift");
    } elseif (isset($data['account_swift'])) {
        $bank_accountswift_value = $data['account_swift'];
    }
    
    $bank_name_error_class = '';
    if (isset($this->errors['input.bank_name'])) {
        $bank_name_error_class = ' has-error';
    }
    
    $bank_name_value = '';
    if (Input::post("input.bank_name") !== null) {
        $bank_name_value = Input::post("input.bank_name");
    } elseif (isset($data['bank_name'])) {
        $bank_name_value = $data['bank_name'];
    }
?>
<div id="paymentDetailsBankAccount" class="payment-details hidden">
    <h3><?= _("Bank account"); ?></h3>
    
    <div class="form-group <?= $bank_first_name_error_class;  ?>">
        <label for="inputName">
            <?= _("First name"); ?>:
        </label>
        <input type="text" 
               value="<?= $bank_first_name_value; ?>" 
               class="form-control" 
               id="inputName" 
               name="input[bname]" 
               placeholder="<?= _("Enter first name"); ?>">
    </div>
    
    <div class="form-group <?= $bank_surname_error_class; ?>">
        <label for="inputSurName">
            <?= _("Last name"); ?>:
        </label>
        <input type="text" 
               value="<?= $bank_surname_value; ?>" 
               class="form-control" 
               id="inputSurName" 
               name="input[bsurname]" 
               placeholder="<?= _("Enter last name"); ?>">
    </div>
    
    <div class="form-group <?= $bank_accountno_error_class; ?>">
        <label for="inputAccountNo">
            <?= _("Account IBAN number"); ?>:
        </label>
        <input type="text" 
               value="<?= $bank_accountno_value; ?>" 
               class="form-control" 
               id="inputAccountNo" 
               name="input[account_no]"
               placeholder="<?= _("Enter your account IBAN number"); ?>">
    </div>
    
    <div class="form-group <?= $bank_accountswift_error_class; ?>">
        <label for="inputAccountSwift">
            <?= _("SWIFT"); ?>:
        </label>
        <input type="text" 
               value="<?= $bank_accountswift_value; ?>" 
               class="form-control" 
               id="inputAccountSwift" 
               name="input[account_swift]"
               placeholder="<?= _("Enter your bank SWIFT code"); ?>">
    </div>
    
    <div class="form-group <?= $bank_name_error_class; ?>">
        <label for="inputBankName">
            <?= _("Bank name"); ?>:
        </label>
        <input type="text" 
               value="<?= $bank_name_value; ?>" 
               class="form-control" 
               id="inputBankName" 
               name="input[bank_name]"
               placeholder="<?= _("Enter your bank name"); ?>">
    </div>
</div>
