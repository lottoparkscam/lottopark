<?php

use Helpers\CurrencyHelper;

if (!defined('WPINC')) {
    die;
}

get_header();

if ($message = Session::get_flash('message_after_register')):
?>
<div class="platform-alert platform-alert-front platform-alert-success">
    <div class="main-width">
        <i class="fa fa-exclamation-circle"></i> <?= $message ?>
    </div>
</div>
<?php
endif;

$errors = Lotto_Settings::getInstance()->get('errors');

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

$payment_type = null;
if (!empty(Input::post("payment.type"))) {
    $payment_type = (int) Input::post("payment.type");
}

$payment_method_id = null;
$whitelabel_payment_method_id = 0;    // This should not be happend
if (!empty(Input::post("payment.subtype"))) {
    $subtype_temp = (int) Input::post("payment.subtype");
    
    $user_currency = CurrencyHelper::getCurrentCurrency()->to_array();
    
    $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
    $methods_with_currencies = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
        $whitelabel,
        $whitelabel_payment_methods_without_currency,
        $user_currency
    );
    $whitelabel_payment_methods = Lotto_Helper::get_whitelabel_payment_methods_for_language(
        $whitelabel,
        $methods_with_currencies
    );


    
    $i = 0;
    foreach ($whitelabel_payment_methods as $whitelabel_payment_method) {
        $i++;
        if ($whitelabel_payment_method['show'] && $i === $subtype_temp) {
            $payment_method_id = (int)$whitelabel_payment_method["payment_method_id"];
            $whitelabel_payment_method_id = (int)$whitelabel_payment_method["id"];
            break;
        }
    }
}

$promocode_obj = Forms_Whitelabel_Bonuses_Promocodes_Code::get_or_create(
    $whitelabel,
    Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_DEPOSIT
);
$promocode_obj->process_content();
$promocode_obj->process_form();
$code = $promocode_obj->get_promo_code();
$promo_errors = $promocode_obj->get_errors();
$code_used_text = $promocode_obj->get_message();

$deposit_obj = new Forms_Wordpress_Myaccount_Deposit(
    $whitelabel,
    $payment_type,
    $payment_method_id,
    $whitelabel_payment_method_id
);
$deposit_obj->set_errors($errors);
$deposit_obj->process_content();

$user_currency_tab = $deposit_obj->get_user_currency_tab();
$user_currency_code = $user_currency_tab['code'];
$proposal_deposits = $deposit_obj->get_proposal_deposits();

$first_field_class = $deposit_obj->get_field_class(0);
$second_field_class = $deposit_obj->get_field_class(1);
$third_field_class = $deposit_obj->get_field_class(2);
$fourth_field_class = $deposit_obj->get_custom_field_class();

$gateway_currency_code = $deposit_obj->get_gateway_currency_code();

$fourth_field_value = $deposit_obj->get_custom_field_value();
$fourth_field_value_in_gateway = $deposit_obj->get_custom_field_value_gateway($user_currency_code);
$deposit_error_amount = $deposit_obj->get_deposit_error_amount();
$deposit_amount_wrong_format = $deposit_obj->get_deposit_amount_wrong_format();

$promo_active = $deposit_obj->check_promo_active();

?>
<div class="content-area">
    <div class="main-width content-width content-payment">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1 id="your-deposit"><?php the_title(); ?></h1>
                    <?php the_content(); ?> 
                </article>
            </section>
            <div class="deposit-amounts">
                <div class="deposit-amount <?= $first_field_class; ?>" 
                     data-value="<?= $proposal_deposits['first_in_currency']; ?>" 
                     data-gatewaycurrencyvalue="<?= $proposal_deposits['first_in_gateway_currency']; ?>">
                    <div class="deposit-amount-value">
                        <?php
                            $f_value = htmlspecialchars(Lotto_View::format_currency(
    $proposal_deposits['first_in_currency'],
    $user_currency_code,
    true
));
                            echo $f_value;
                        ?>
                        <span class="deposit-amount-gateway-currency">
                            <?php
                                $fg_value = htmlspecialchars(Lotto_View::format_currency(
                            $proposal_deposits['first_in_gateway_currency'],
                            $gateway_currency_code,
                            true
                        ));
                                echo $fg_value;
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="deposit-amount <?= $second_field_class; ?>"
                     data-value="<?= $proposal_deposits['second_in_currency']; ?>" 
                     data-gatewaycurrencyvalue="<?= $proposal_deposits['second_in_gateway_currency']; ?>">
                    <div class="deposit-amount-value">
                        <?php
                            $s_value = htmlspecialchars(Lotto_View::format_currency(
                                $proposal_deposits['second_in_currency'],
                                $user_currency_code,
                                true
                            ));
                            echo $s_value;
                        ?>
                        <span class="deposit-amount-gateway-currency">
                            <?php
                                $sg_value = htmlspecialchars(Lotto_View::format_currency(
                            $proposal_deposits['second_in_gateway_currency'],
                            $gateway_currency_code,
                            true
                        ));
                                echo $sg_value;
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="deposit-amount <?= $third_field_class; ?>"
                     data-value="<?= $proposal_deposits['third_in_currency']; ?>" 
                     data-gatewaycurrencyvalue="<?= $proposal_deposits['third_in_gateway_currency']; ?>">
                    <div class="deposit-amount-value">
                        <?php
                            $t_value = htmlspecialchars(Lotto_View::format_currency(
                                $proposal_deposits['third_in_currency'],
                                $user_currency_code,
                                true
                            ));
                            echo $t_value;
                        ?>
                        <span class="deposit-amount-gateway-currency">
                            <?php
                                $tg_value = htmlspecialchars(Lotto_View::format_currency(
                            $proposal_deposits['third_in_gateway_currency'],
                            $gateway_currency_code,
                            true
                        ));
                                echo $tg_value;
                            ?>
                        </span>
                    </div>
                </div>
                
                <div class="deposit-amount <?= $fourth_field_class; ?> platform-form">
                    <label for="inputAmount">
                        <?= Security::htmlentities(_("Different amount")); ?>:
                    </label>
                    <input type="text" id="inputAmount" value="<?= $fourth_field_value;?>">
                    <span class="deposit-amount-currency">
                        <?php
                            echo htmlspecialchars(Lotto_View::format_currency_code($user_currency_code));
                        ?>
                    </span>
                    <span class="deposit-amount-gateway-currency" id="deposit-amount-gateway" 
                          data-gatewayvalue="<?= htmlspecialchars($fourth_field_value_in_gateway); ?>">
                        <?php
                            $fog_value = htmlspecialchars(Lotto_View::format_currency(
                            $fourth_field_value_in_gateway,
                            $gateway_currency_code,
                            true
                        ));
                            echo $fog_value;
                        ?>
                    </span>
                    <div class="deposit-amount-error-cloud" id="deposit-amount-error-cloud"
                         data-textmindeposit="<?= Security::htmlentities($deposit_error_amount); ?>" 
                         data-textwrongformat="<?= Security::htmlentities($deposit_amount_wrong_format); ?>">
                        <?= Security::htmlentities($deposit_error_amount); ?>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>            
                <div class="deposit-promo-code">
                    <form id="form-promo-code" method="post" action=".">
                    <?php
                    $amount = "";
                    if (!empty(Input::post("deposit.amount"))) {
                        $amount = Input::post("deposit.amount");
                    }
                        if (!empty($code)):
                    ?>
                        <div class="promo-info">
                            <div class="promo-code-alert">
                                <span class="fa fa-check-circle"></span>
                                <span><?= $code_used_text; ?></span>     
                            </div>
                            <button type="submit" name="input[delete]" id="deletePromoCode" class="btn" value="1"><?= Security::htmlentities(_("Remove")) ?></button>              
                        </div>
                    <?php
                        elseif ($promo_active && (empty($code))):
                    ?>
                            <div class="platform-form form-promo-code">
                                <label for="inputPromoCode"><?= Security::htmlentities(_("Got a promo code?")); ?></label>
                                <input type="text"
                                    class="form-control" 
                                    name="input[promo_code]" 
                                    placeholder="<?= htmlspecialchars(_("Enter promo code")); ?>"/>
                                <button type="submit" id="applyPromoCode" class="btn"><?= Security::htmlentities(_("Apply")); ?></button>                    
                            </div>                             
                        <?php
                        endif;
                    ?>
                            <input type="hidden" id="depositAmount" name="deposit[amount]" value="<?= $amount ?>"/>
                        </form>   
                </div>
        </div>
    </div>
<?php
/*
<?php if (is_active_sidebar('deposit-sidebar-id')) : ?>
    <?php Lotto_Helper::widget_before_area('deposit-sidebar-id'); ?>
    <?php dynamic_sidebar('deposit-sidebar-id'); ?>
    <?php Lotto_Helper::widget_after_area('deposit-sidebar-id'); ?>
<?php endif; ?> */
?>
<?php
    if (!empty($promo_errors) && count($promo_errors) > 0):
?>
        <div class="main-width error-area">
            <div class="platform-alert platform-alert-error">
            <?php
                foreach ($promo_errors as $error):
                    echo '<p><span class="fa fa-exclamation-circle"></span> '.Security::htmlentities($error).'</p>';
                endforeach;
            ?>
            </div>
        </div>
<?php
    endif;
    if (!empty($errors) && count($errors) > 0):
?>
        <div class="main-width error-area">
            <div class="platform-alert platform-alert-error">
            <?php
                foreach ($errors as $error):
                    echo '<p><span class="fa fa-exclamation-circle"></span> '.Security::htmlentities($error).'</p>';
                endforeach;
            ?>
            </div>
        </div>
<?php
    endif;

    get_template_part('content', 'order-payment');
?>

</div>

<?php
get_footer();
