<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $title; ?>
        </h2>
        
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        
        <a href="<?= $list_url; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $add_edit_url; ?>">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    <div class="form-group <?= $error_fields['payment_currency_id']; ?>">
                        <label>
                            <?= _("Payment currency"); ?>:
                        </label>
                        <div class="row">
                            <div class="col-md-3">
                                <select name="input[payment_currency_id]" 
                                        id="inputPaymentCurrency" 
                                        class="form-control">
                                    <?php
                                        foreach ($currencies as $currency):
                                    ?>
                                            <option value="<?= $currency['id']; ?>" <?= $currency['selected']; ?> 
                                                    data-code="<?= $currency['code']; ?>">
                                                <?= $currency['code']; ?>
                                            </option>
                                    <?php
                                        endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group <?= $error_fields['min_purchase_by_currency']; ?>">
                        <label class="control-label" for="inputMinPurchaseByCurrency">
                            <?= _("Minimum purchase by currency"); ?>:
                        </label>
                        <div class="input-group">
                            <div class="input-group-addon" id="minPurchaseCurrencyCode">
                                <?= $edit["min_purchase_currency_code"]; ?>
                            </div>
                            <input type="text" 
                                   required="required" 
                                   value="<?= $edit["min_purchase"]; ?>"
                                   class="form-control"
                                   id="inputMinPurchaseByCurrency" 
                                   name="input[min_purchase]"
                                   placeholder="<?= _("Enter minimum payment amount"); ?>">
                        </div>
                        <p class="help-block">
                            <?= _("Use dot for decimal digits."); ?>
                        </p>
                    </div>
                    
                    <?php
                        if ($show_default_tickbox):
                    ?>
                            <div class="form-group <?= $error_fields['is_default']; ?>">
                                <div class="input-group">
                                    <input type="checkbox" 
                                           name="input[is_default]" 
                                           value="1" 
                                            <?= $is_default_checked; ?>>
                                        <?= _("Make that currency default"); ?>
                                </div>
                            </div> 
                    <?php
                        endif;
                    ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</div>
