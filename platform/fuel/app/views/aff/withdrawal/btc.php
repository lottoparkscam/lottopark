<?php
    $btc_first_name_error_class = '';
    if (isset($this->errors['input.btname'])) {
        $btc_first_name_error_class = ' has-error';
    }
    
    $btc_first_name_value = '';
    if (Input::post("input.btname") !== null) {
        $btc_first_name_value = Input::post("input.btname");
    } elseif (isset($data['name'])) {
        $btc_first_name_value = $data['name'];
    } elseif (isset($user['name'])) {
        $btc_first_name_value = $user['name'];
    }
    
    $btc_surname_error_class = '';
    if (isset($this->errors['input.btsurname'])) {
        $btc_surname_error_class = ' has-error';
    }
    
    $btc_surname_value = '';
    if (Input::post("input.btsurname") !== null) {
        $btc_surname_value = Input::post("input.btsurname");
    } elseif (isset($data['surname'])) {
        $btc_surname_value = $data['surname'];
    } elseif (isset($user['surname'])) {
        $btc_surname_value = $user['surname'];
    }
    
    $btc_bitcoin_error_class = '';
    if (isset($this->errors['input.bitcoin'])) {
        $btc_bitcoin_error_class = ' has-error';
    }
    
    $btc_bitcoin_value = '';
    if (Input::post("input.bitcoin") !== null) {
        $btc_bitcoin_value = Input::post("input.bitcoin");
    } elseif (isset($data['bitcoin'])) {
        $btc_bitcoin_value = $data['bitcoin'];
    }
?>
<div id="paymentDetailsBTC" class="payment-details hidden">
    <h3><?= _("BTC"); ?></h3>
    
    <div class="form-group <?= $btc_first_name_error_class; ?>">
        <label for="inputName">
            <?= _("First name (optional)"); ?>:
        </label>
        <input type="text" 
               value="<?= $btc_first_name_value; ?>" 
               class="form-control" 
               id="inputName" 
               name="input[btname]" 
               placeholder="<?= _("Enter first name (optional)"); ?>">
    </div>
    
    <div class="form-group <?= $btc_surname_error_class; ?>">
        <label for="inputSurName">
            <?= _("Last name (optional)"); ?>:
        </label>
        <input type="text" 
               value="<?= $btc_surname_value; ?>" 
               class="form-control" 
               id="inputSurName" 
               name="input[btsurname]" 
               placeholder="<?= _("Enter last name (optional)"); ?>">
    </div>
    
    <div class="form-group <?= $btc_bitcoin_error_class; ?>">
        <label for="inputBitcoin">
            <?= _("Bitcoin wallet address"); ?>:
        </label>
        <input type="text" 
               value="<?= $btc_bitcoin_value; ?>" 
               class="form-control" 
               id="inputBitcoin" 
               name="input[bitcoin]" 
               placeholder="<?= _("Enter bitcoin wallet address"); ?>">
    </div>
</div>