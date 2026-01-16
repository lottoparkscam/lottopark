<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $main_payment_method_title; ?>
        </h2>

        <p class="help-block">
            <?= _("You can add new or edit payment method here."); ?>
        </p>

        <div class="row">
            <div class="col-md-6">
                <a href="<?= $urls["back"]; ?>" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
                </a>

                <?php
                    if (isset($edit_lp)):
                ?>
                        <div class="pull-right">
                            <a href="<?= $urls["currency_list"]; ?>"
                               class="btn btn-xs btn-success">
                                <span class="glyphicon glyphicon-list"></span> <?= _("Currency list"); ?>
                            </a>
                        </div>
                <?php
                    endif;
                ?>
            </div>
        </div>

        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $urls["add_edit"]; ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group <?= $error_class["language"]; ?>">
                        <label class="control-label" for="inputLanguage">
                            <?= _("Language"); ?>:
                        </label>
                        <select required
                                name="input[language]"
                                id="inputLanguage"
                                class="form-control">
                            <?php
                                foreach ($langs as $lang):
                            ?>
                                    <option value="<?= $lang["id"]; ?>" <?= $lang["selected"]; ?>>
                                        <?= $lang["show_text"]; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="form-group <?= $error_class["method"]; ?>">
                        <label class="control-label" for="inputPaymentMethod">
                            <?= _("Integrated Method"); ?>:
                        </label>
                        <select required
                                name="input[method]"
                                id="inputPaymentMethod"
                                class="form-control">
                            <option value="0">
                                <?= _("None"); ?>
                            </option>
                            <?php
                                foreach ($methods as $method):
                            ?>
                                    <option value="<?= $method["id"]; ?>" <?= $method["selected"]; ?>>
                                        <?= $method["name"]; ?>
                                    </option>
                            <?php
                                endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="form-group <?= $error_class["name"]; ?>">
                        <label class="control-label" for="inputName">
                            <?= _("Name"); ?>:
                        </label>
                        <input type="text"
                               required
                               value="<?= $main_values["name"]; ?>"
                               class="form-control"
                               id="inputName"
                               name="input[name]"
                               placeholder="<?= _("Enter name"); ?>">
                    </div>

                    <div class="form-group <?= $error_class["cost_percent"]; ?>">
                        <label class="control-label" for="inputCostPercentage">
                            <?= _("Percentage cost"); ?>:
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   value="<?= $main_values["cost_percent"]; ?>"
                                   class="form-control"
                                   id="inputCostPercentage"
                                   name="input[cost_percent]"
                                   placeholder="<?= _("Enter cost percentage"); ?>">
                            <div class="input-group-addon">%</div>
                        </div>
                        <p class="help-block">
                            <?php
                                echo _(
                                "E.g. 4 if this payment method's company charge you " .
                                    "4%. Use dot for decimal digits. Not required."
                            );
                            ?>
                        </p>
                    </div>

                    <div class="form-group <?= $error_class["cost_fixed"]; ?>">
                        <label class="control-label" for="inputCostFixed">
                            <?= _("Fixed cost"); ?>:
                        </label>
                        <div class="row">
                            <div class="col-md-9">
                                <input type="text"
                                       required="required"
                                       value="<?= $main_values["cost_fixed"]; ?>"
                                       class="form-control"
                                       id="inputCostFixed"
                                       name="input[cost_fixed]"
                                       placeholder="<?= _("Enter cost percentage"); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="input[cost_currency]"
                                        id="inputCostCurrency"
                                        class="form-control">
                                    <?php
                                        foreach ($currencies as $currency):
                                    ?>
                                            <option value="<?= $currency["id"]; ?>" <?= $currency["selected"]; ?>>
                                                <?= $currency["code"]; ?>
                                            </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                        <p class="help-block">
                            <?php
                                echo _(
                                        "E.g. 4 EUR if this payment method's company charge you " .
                                        htmlentities('€') .
                                        "4. Use dot for decimal digits. Not required."
                                    );
                            ?>
                        </p>
                    </div>

                    <?php
                        if (!isset($edit_lp)):
                    ?>
                            <div class="form-group <?= $error_class["payment_currency"]; ?>">
                                <label>
                                    <?= _("Payment currency"); ?>:
                                </label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <select name="input[payment_currency]"
                                                id="inputPaymentCurrency"
                                                class="form-control">
                                            <?php
                                                foreach ($payment_currencies as $payment_currency):
                                            ?>
                                                    <option value="<?= $payment_currency["id"]; ?>" <?= $payment_currency["selected"]; ?> 
                                                            data-code="<?= $payment_currency["code"]; ?>">
                                                        <?= $payment_currency["code"]; ?>
                                                    </option>
                                            <?php
                                                endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group <?= $error_class["min_purchase_by_currency"]; ?>">
                                <label class="control-label" for="inputMinPurchaseByCurrency">
                                    <?= _("Minimum purchase by currency"); ?>:
                                </label>
                                <div class="input-group">
                                    <div class="input-group-addon" id="minPurchaseCurrencyCode">
                                        <?= $main_values["min_purchase_currency_code"]; ?>
                                    </div>
                                    <input type="text"
                                           required="required"
                                           value="<?= $main_values["min_purchase_by_currency"]; ?>"
                                           class="form-control"
                                           id="inputMinPurchaseByCurrency"
                                           name="input[min_purchase_by_currency]"
                                           placeholder="<?= _("Enter minimum payment amount"); ?>">
                                </div>
                            </div>
                    <?php
                        endif;
                    ?>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[only_deposit]"
                                   value="1"
                                   <?= $main_values["only_deposit"]; ?>>
                            <?= _("Deposit only"); ?>
                        </label>
                        <p class="help-block">
                            <?= _("If you check this, this payment method will be available only on deposit page"); ?>
                        </p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[show]"
                                   value="1"
                                   <?= $main_values["show"]; ?>>
                            <?= _("Show on payment page"); ?>
                        </label>
                        <p class="help-block">
                            <?= _("Do not check this for manual-only payments."); ?>
                        </p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[show_payment_logotype]"
                                   id="showPaymentLogotype"
                                   value="1"
                                    <?= $main_values["show_payment_logotype"]; ?>>
                                <?= _("Show payment logotype"); ?>
                        </label>
                    </div>

                    <div id="custom-logotype"
                         class="form-group <?= $error_class["custom_logotype"]; ?>">
                        <label class="control-label" for="inputCustomLogotype">
                            <?= _("Custom logotype"); ?>:
                        </label>

                        <input type="text"
                               value="<?= $main_values["custom_logotype"]; ?>"
                               class="form-control"
                               id="inputCustomLogotype"
                               name="input[custom_logotype]"
                               placeholder="<?= _("Enter custom logotype"); ?>">

                        <p class="help-block">
                            <?= _("To use custom logotype enter URL to that logotype. Not required."); ?>
                        </p>
                    </div>

                    <div class="checkbox">
                        <label>
                            <input type="checkbox"
                                   name="input[allow_user_to_select_currency]"
                                   value="1"
                                <?= $main_values["allow_user_to_select_currency"]; ?>>
                            <?= _("Allow user to select currency"); ?>
                        </label>
                        <p class="help-block">
                            <?= _("Check this option to allow user to select currency on payment page."); ?>
                        </p>
                    </div>
                    
                    <?php
                        include(APPPATH . "views/admin/payment/base.php");
                    ?>

                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>