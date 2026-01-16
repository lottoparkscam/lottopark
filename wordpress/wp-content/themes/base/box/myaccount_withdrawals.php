<?php

use Helpers\FlashMessageHelper;

if (FlashMessageHelper::anyFlashMessageExists()):
    echo lotto_platform_messages();
else:
    if (!isset($hideform)):
?>
        <form class="platform-form platform-form-withdrawal" 
              autocomplete="off" 
              method="post" 
              action=".">
            <?php
                echo \Form::csrf();
            ?>
            <input type="hidden" 
                   name="withdrawal[step]" 
                   value="<?= $step; ?>">
            <?php
                if (!empty($this->errors) && count($this->errors) > 0):
            ?>
                    <div class="platform-alert platform-alert-error">
                        <?php
                            foreach ($this->errors as $error):
                                echo '<p><span class="fa fa-exclamation-circle"></span> '.Security::htmlentities($error).'</p>';
                            endforeach;
                        ?>
                    </div>
            <?php
                endif;

                if (isset($step) && (int)$step === 2):
                    $type_t = Input::post("withdrawal.type");
                    $type_withdrawal = htmlspecialchars($type_t);

                    $amount_t = stripslashes(Input::post("withdrawal.amount"));
                    $amount_withdrawal = htmlspecialchars($amount_t);
            ?>
                    <input type="hidden" 
                           name="withdrawal[type]" 
                           value="<?= $type_withdrawal; ?>">
                    <input type="hidden" 
                           name="withdrawal[amount]" 
                           value="<?= $amount_withdrawal; ?>">
            <?php
                    $withdrawal_methods_uri_list = Helpers_Withdrawal_Method::get_methods_URI();
                    foreach ($withdrawal_methods_uri_list as $id_list => $uri):
                        if ((int)$type_t === (int)$id_list):
                            $full_uri = APPPATH . "views/wordpress/withdrawal/methods/" . $uri . ".php";
                            include($full_uri);
                            break;
                        endif;
                    endforeach;
                else:
                    include(APPPATH . "views/wordpress/withdrawal/form.php");
                endif;
            ?>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary platform-form-btn-margin btn-mobile-large">
                        <?= Security::htmlentities(_("Withdrawal")); ?>
                    </button>
                </div>
        </form>
<?php
    endif;
endif;

include('myaccount_withdrawals_list.php');
