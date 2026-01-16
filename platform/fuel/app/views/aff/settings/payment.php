<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/aff/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Edit payment settings"); ?>
        </h2>
        <p class="help-block">
            <?= _("Edit your payment details here."); ?>
        </p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <?php include(APPPATH . "views/aff/shared/messages.php"); ?>
                
                <form method="post" action="/payment">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/aff/shared/errors.php");
                        }
                        
                        $method_error = '';
                        if (isset($errors['input.method'])) {
                            $method_error = ' has-error';
                        }
                        
                        $withdrawal_methods = Forms_Aff_Withdrawal_Method::get_methods_list_by_whitelabel($whitelabel);
                        $withdrawal_methods_keys = array_keys($withdrawal_methods);
                    ?>

                    <div class="form-group <?= $method_error; ?>">
                        <label class="control-label" for="inputAffPaymentMethod">
                            <?= _("Method"); ?>:
                        </label>
                        <select autofocus required name="input[method]" id="inputAffPaymentMethod" class="form-control">
                            <option value="0"><?= _("None"); ?></option>
                            <?php
                                if (!empty($withdrawal_methods)):
                                    foreach ($methods as $method):
                                        $is_selected = '';
                                        $withdrawal_methods_id = 0;
                                        if (in_array($method['withdrawal_id'], $withdrawal_methods_keys)) {
                                            $withdrawal_methods_id = (int)$method['withdrawal_id'];
                                        }

                                        if ((Input::post("input.method") !== null &&
                                                Input::post("input.method") == $withdrawal_methods_id) ||
                                            (Input::post("input.method") === null &&
                                                !empty($user) &&
                                                (int)$user['whitelabel_aff_withdrawal_id'] === (int)$method['id'])
                                        ) {
                                            $is_selected = ' selected="selected"';
                                        }
                                        if (!isset($withdrawal_methods[$withdrawal_methods_id])) {
                                            continue;
                                        }
                                        $withdrawal_method_name = $withdrawal_methods[$withdrawal_methods_id];
                                        $withdrawal_method_value = Security::htmlentities($withdrawal_method_name);
                                ?>
                                        <option value="<?= $withdrawal_methods_id; ?>"<?= $is_selected; ?>>
                                            <?= $withdrawal_method_value; ?>
                                        </option>
                                <?php
                                    endforeach;
                                endif;
                            ?>
                        </select>
                    </div>
                    
                    <?php
                        $withdrawal_methods_uri = Helpers_Withdrawal_Method::get_methods_URI();
                        foreach ($methods as $method) {
                            $uri = $withdrawal_methods_uri[$method['withdrawal_id']];
                            $full_uri = APPPATH . "views/aff/withdrawal/" . $uri . ".php";
                            include($full_uri);
                        }
                    ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
