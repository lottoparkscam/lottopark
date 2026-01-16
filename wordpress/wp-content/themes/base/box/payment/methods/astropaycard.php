<div class="payment-cc-content" >
    <?php
        $astropaycard = [];
    
        $astropaycard_number_class_error = "";
        if (isset($errors['astropaycard.number'])) {
            $astropaycard_number_class_error = ' has-error';
        }
        $astropaycard['number_class_error'] = $astropaycard_number_class_error;
        
        $astropaycard_number_value = "";
        if (Input::post("astropaycard.number") !== null) {
            $astropaycard_number_value = htmlspecialchars(Input::post("astropaycard.number"));
        }
        $astropaycard['number_value'] = $astropaycard_number_value;
        
        $astropaycard_expmonth_class_error = "";
        if (isset($errors['astropaycard.expmonth']) ||
            isset($errors['astropaycard.expyear'])
        ) {
            $astropaycard_expmonth_class_error = ' has-error';
        }
        $astropaycard['expmonth_class_error'] = $astropaycard_expmonth_class_error;
        
        $astropaycard_cvv_class_error = "";
        if (isset($errors['astropaycard.cvv'])) {
            $astropaycard_cvv_class_error = ' has-error';
        }
        $astropaycard['cvv_class_error'] = $astropaycard_cvv_class_error;
        
        $astropaycard_cvv_value = "";
        if (Input::post("astropaycard.cvv") !== null) {
            $astropaycard_cvv_value = htmlspecialchars(Input::post("astropaycard.cvv"));
        }
        $astropaycard['cvv_value'] = $astropaycard_cvv_value;
        
        $tooltip_text = _(
            "The 3 or 4-digit CVV code is located " .
            "on the back side of your payment card."
        ) .
            '<br><div class="text-center"><img src="' .
            get_template_directory_uri() .
            '/images/cvv.png" alt="cvv"></div>';
        
        $astropaycard['cvv_tooltip_text'] = htmlspecialchars($tooltip_text);
    ?>
    
    <div class="form-group <?= $astropaycard['number_class_error']; ?>">
        <label for="astropaycardNumber"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Card number (spaces allowed)")); ?>:
        </label>
        
        <input type="text"
               id="astropaycardNumber"
               name="astropaycard[number]"
               value="<?= $astropaycard['number_value']; ?>">
    </div>
    
    <div class="form-group <?= $astropaycard['expmonth_class_error']; ?>">
        <label for="astropaycardExpirationDate"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Expiration date")); ?>:
        </label>
        
        <select name="astropaycard[expmonth]"
                id="astropaycardExpirationDate"
                class="payment-cc-select payment-selector">
            <?php
                for ($i = 1; $i <= 12; $i++):
                    $exp1_is_selected = "";
                    if (Input::post("astropaycard.expmonth") == sprintf("%02d", $i)) {
                        $exp1_is_selected = ' selected="selected"';
                    }
            ?>
                <option value="<?= sprintf("%02d", $i); ?>" <?= $exp1_is_selected; ?>>
                    <?= sprintf("%02d", $i); ?>
                </option>
            <?php
                endfor;
            ?>
        </select>
        <div class="pull-left exp-break"> / </div>
        <select name="astropaycard[expyear]"
                id="astropaycardExpirationDate2"
                class="payment-cc-select payment-selector">
            <?php
                $dt = new DateTime("now", new DateTimeZone("UTC"));
                
                for ($i = 0; $i < 50; $i++):
                    $exp2_is_selected = "";
                    if (Input::post("astropaycard.expyear") == $dt->format('y')) {
                        $exp2_is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $dt->format('y'); ?>" <?= $exp2_is_selected; ?>>
                        <?= $dt->format('y'); ?>
                    </option>
            <?php
                    $dt->add(new DateInterval("P1Y"));
                endfor;
            ?>
        </select>
    </div>
    
    <div class="form-group <?= $astropaycard['cvv_class_error']; ?>">
        <label for="astropaycardCVV"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("CVV code")); ?>:
        </label>
        
        <input type="text"
               id="astropaycardCVV"
               name="astropaycard[cvv]"
               maxlength="4"
               value="<?= $astropaycard['cvv_value']; ?>"
               class="payment-cc-input">
        <div class="pull-left cvv-info">
            <a href="#"
               class="cvv-info-tooltip tooltip tooltip-bottom"
               data-tooltip="<?= $astropaycard['cvv_tooltip_text']; ?>">
                <span class="fa fa-info-circle" 
                      aria-hidden="true"></span> <?= Security::htmlentities(_("What is this?")); ?>
            </a>
        </div>
    </div>
    
    <div class="clearfix"></div>
</div>