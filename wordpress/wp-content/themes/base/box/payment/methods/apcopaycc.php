<div class="payment-cc-content" style="display:block !important;">
    <?php
        $errors = Lotto_Settings::getInstance()->get("errors");

        $ac_name_class_error = "";
        if (isset($errors['apcopaycc.name'])) {
            $ac_name_class_error = ' has-error';
        }

        $ac_address1_class_error = "";
        if (isset($errors['apcopaycc.address_1'])) {
            $ac_address1_class_error = ' has-error';
        }

        $ac_address2_class_error = "";
        if (isset($errors['apcopaycc.address_2'])) {
            $ac_address2_class_error = ' has-error';
        }

        $ac_post_code_class_error = "";
        if (isset($errors['apcopaycc.post-code'])) {
            $ac_post_code_class_error = ' has-error';
        }

        $ac_city_class_error = "";
        if (isset($errors['apcopaycc.city'])) {
            $ac_city_class_error = ' has-error';
        }

        $ac_country_class_error = "";
        if (isset($errors['apcopaycc.country'])) {
            $ac_country_class_error = ' has-error';
        }

        if (Input::post("apcopaycc.name") !== null) {
            $ac_name_value = htmlspecialchars(Input::post("apcopaycc.name"));
        } else {
            $ac_name_value = $user['name'].' '.$user['surname'];
        }

        if (Input::post("apcopaycc.address_1") !== null) {
            $ac_address1_value = htmlspecialchars(Input::post("apcopaycc.address_1"));
        } else {
            $ac_address1_value = $user['address_1'];
        }

        if (Input::post("apcopaycc.address_2") !== null) {
            $ac_address2_value = htmlspecialchars(Input::post("apcopaycc.address_2"));
        } else {
            $ac_address2_value = $user['address_2'];
        }

        if (Input::post("apcopaycc.post-code") !== null) {
            $ac_post_code_value = htmlspecialchars(Input::post("apcopaycc.post-code"));
        } else {
            $ac_post_code_value = $user['zip'];
        }

        if (Input::post("apcopaycc.city") !== null) {
            $ac_city_value = htmlspecialchars(Input::post("apcopaycc.city"));
        } else {
            $ac_city_value = $user['city'];
        }
    ?>
    <div class="form-group <?= $ac_name_class_error; ?>">
        <label for="paymentCCHolder"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Name on card")); ?>:
        </label>
        <input type="text"
               id="paymentCCHolder"
               name="apcopaycc[name]"
               value="<?= $ac_name_value; ?>">
    </div>
    <div class="form-group <?= $ac_address1_class_error; ?>">
        <label for="paymentApcoAddress1"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Address")); ?>:
        </label>
        <input type="text"
               id="paymentApcoAddress1"
               name="apcopaycc[address_1]"
               value="<?= $ac_address1_value; ?>">
    </div>
    <div class="form-group <?= $ac_address2_class_error; ?>">
        <label for="paymentApcoAddress2"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Address (optional additional information)")); ?>:
        </label>
        <input type="text"
               id="paymentApcoAddress2"
               name="apcopaycc[address_2]"
               value="<?= $ac_address2_value; ?>">
    </div>
    <div class="form-group <?= $ac_post_code_class_error; ?>">
        <label for="paymentApcoPostCode"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("Postal/ZIP Code")); ?>:
        </label>
        <input type="text" 
               id="paymentApcoPostCode" 
               name="apcopaycc[post-code]" 
               maxlength="10" 
               value="<?= $ac_post_code_value; ?>" 
               class="payment-cc-input">
    </div>
    <div class="form-group <?= $ac_city_class_error; ?>">
        <label for="paymentApcoCity"
               class="payment-cc-content-field">
            <?= Security::htmlentities(_("City")); ?>:
        </label>
        <input type="text"
               id="paymentApcoCity"
               name="apcopaycc[city]"
               value="<?= $ac_city_value; ?>">
    </div>
    <div class="form-group <?= $ac_country_class_error; ?>">
        <label for="paymentApcoCountry" class="payment-cc-content-field">
            <?php echo Security::htmlentities(_("Country")); ?>:
        </label>
        <select name="apcopaycc[country]"
                id="paymentApcoCountry" 
                class="payment-selector">
            <?php
                $countries = Lotto_Helper::get_localized_country_list();
                foreach ($countries as $key => $country):
                    $is_selected = '';
                    if ((Input::post("apcopaycc.country") !== null &&
                            stripslashes(Input::post("apcopaycc.country")) == htmlspecialchars($country)) ||
                        (Input::post("apcopaycc.country") === null &&
                            $key == $user['country'])
                    ) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= htmlspecialchars($country); ?>" <?= $is_selected; ?>>
                        <?= Security::htmlentities($country); ?>
                    </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
    <div class="clearfix"></div>
</div>
