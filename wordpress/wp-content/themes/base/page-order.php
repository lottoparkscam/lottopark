<?php

use Helpers\CurrencyHelper;
use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
get_header();

$errors = Lotto_Settings::getInstance()->get('errors');
$order = Session::get('order');
$lotteries = lotto_platform_get_lotteries();

$order_obj = new Forms_Wordpress_Myaccount_Order($whitelabel, $order, $lotteries);
$order_obj->process_min_amounts();

$user = $user = Lotto_Settings::getInstance()->get("user");

if (!empty($order)) {
    $items = [];
    foreach ($order as $item) {
        $lottery = $lotteries['__by_id'][$item['lottery']];
        $items[] = [
            'item_id' => $lottery['slug'],
            'item_name' => $lottery['name'],
            'price' => lotto_platform_get_pricing($lottery),
            'quantity' => count($item['lines']),
        ];
    }

    $beginCheckoutData = [
        'event' => 'begin_checkout',
        'user_id' =>  $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
        'currency' => lotto_platform_user_currency(),
        'value' => 0,
        'items' => $items,
    ];
}

$newTicketAdded = Session::get('ticket_added');
if ($newTicketAdded) {
    Session::delete('ticket_added');

    $addToCartData = [
        'event' => 'add_to_cart',
        'user_id' => $user ? $whitelabel['prefix'] . 'U' . $user['token'] : '',
        'currency' => lotto_platform_user_currency(),
        'value' => 0,
        'items' => $items
    ];
}

$promo_active = $order_obj->check_promo_active();
$user_currency = CurrencyHelper::getCurrentCurrency()->to_array();
$profiled_lotteries = Lotto_Helper::get_profiled_lotteries_for_user();

$promocode_obj = Forms_Whitelabel_Bonuses_Promocodes_Code::get_or_create(
    $whitelabel,
    Forms_Whitelabel_Bonuses_Promocodes_Code::TYPE_PURCHASE
);
$result = $promocode_obj->process_content();
$promocode_obj->process_form();
$code = $promocode_obj->get_promo_code();
$promo_errors = $promocode_obj->get_errors();
$any_multidraw = $promocode_obj->getHasMultiDraw();
$code_used_text = $promocode_obj->get_message();
$possibleOrderCount = Lotto_Helper::get_possible_order_count();
$disabledAddTickets = $possibleOrderCount <= 0;
$userCurrency = CurrencyHelper::getCurrentCurrency()->to_array();
$userCurrencyData = Model_Whitelabel_Default_Currency::get_for_user($whitelabel, $userCurrency['id']);
$maxOrderAmount = $userCurrencyData['max_order_amount'];

echo lotto_platform_messages(true, true);

get_template_part('content', 'login-register-box-mobile');
?>
    <div class="content-area">
        <div class="main-width content-width">
            <div class="content-box">
                <section class="page-content">
                    <article class="page">
                        <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play')); ?>" class="btn btn-secondary btn-more-mobi pull-right"><?= Security::htmlentities(_("Add more tickets")); ?></a>
                        <div class="order-lottery-quick-pick-container pull-right">
                            <div class="order-lottery-quick-pick-label pull-left"><?= Security::htmlentities(_("Add Quick Pick ticket:")); ?></div>
                            <?php
                            foreach ($profiled_lotteries as $plottery):
                                $lotteryUrl = lotto_platform_get_permalink_by_slug('order') . 'quickpick/' . $plottery['slug'] . '/';
                                $amount = $plottery['multiplier'] > $plottery['min_lines'] ? $plottery['multiplier'] : $plottery['min_lines'];
                                $lotteryUrl .= $amount > 0 ? $amount : 1;
                                ?>
                                <div class="order-lottery-quick-pick pull-left">
                                    <a href="<?= UrlHelper::esc_url($lotteryUrl) ?>" rel="nofollow"><?= Security::htmlentities(_($plottery['name'])); ?></a>
                                </div>
                            <?php
                            endforeach;
                            ?>
                            <div class="clearfix"></div>
                        </div>
                        <h1 class="header-order" id="your-order"><?php the_title(); ?></h1>
                        <div class="clearfix"></div>
                        <?php the_content(); ?>
                    </article>
                </section>
                <?php /**** moved ****/ ?>
                <?php
                if (!empty($promo_errors) && count($promo_errors) > 0):
                    ?>
                    <div class="main-width">
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
                echo lotto_platform_messages();

                if (!empty($errors['order'])):
                    ?>
                    <div class="platform-alert platform-alert-error">
                        <p>
                            <span class="fa fa-exclamation-circle"></span> <?php
                            echo $errors['order'];
                            ?>
                        </p>
                    </div>
                <?php
                endif;
                if (isset($order) && count($order) > 0):
                    $moved = $order_obj->is_moved();
                    if ($moved):
                        ?>
                        <div class="platform-alert platform-alert-warning">
                            <p>
                                <span class="fa fa-exclamation-circle"></span> <?php
                                    echo Security::htmlentities(_("Some of the tickets have been moved to the next draw!"));
                                ?>
                            </p>
                        </div>
                    <?php
                    endif;
                    ?>
                    <ul class="new-checkout-list">
                        <?php
                        $num = 0;

                        foreach ($order as $key => $item):
                            $num++;
                            if (isset($lotteries['__by_id'][$item['lottery']])):
                                $lottery = $lotteries['__by_id'][$item['lottery']];
                                list(
                                    $lottery_image,
                                    $play_text_full,
                                    $mobile_hide_text_full,
                                    $order_text_full,
                                    $draw_date_text_closed,
                                    $draw_date_text_open,
                                    $amount_value,
                                    $multi_draw_badge,
                                    $ticket_multiplier,
                                    $numbers_per_line
                                    ) = $order_obj->prepare_order_data($item, $lottery);
                                ?>
                                <li class="new-checkout-list-item">

                                    <div class="new-checkout-list-item-img-box">
                                        <?php if (!is_null($multi_draw_badge)): ?>
                                            <div class="new-checkout-list-item-multidraw-badge"><?= $multi_draw_badge; ?></div>
                                        <?php endif; ?>
                                        <img class="new-checkout-list-item-img" src="<?= UrlHelper::esc_url($lottery_image); ?>" alt="<?= Security::htmlentities(_($lottery['name'])); ?>">
                                    </div>

                                    <div class="new-checkout-list-item-col">
                                        <div class="new-checkout-list-item-content">

                                            <div class="new-checkout-list-item-header">
                                                <div class="new-checkout-list-item-title"><?= $play_text_full; ?></div>
                                                <div class="new-checkout-list-item-info"><?= $order_text_full; ?></div>
                                                
                                                <div class="new-checkout-list-item-details">
                                                    <?php if ($numbers_per_line !== null): ?>
                                                        <div><?php echo sprintf(_("Playing %s numbers per line.") . ' ', $numbers_per_line); ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($ticket_multiplier > 1): ?>
                                                        <div><?= Security::htmlentities(_("Multiplier") . ": x" . $ticket_multiplier) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                            </div>

                                            <div class="new-checkout-list-item-price"><?= Security::htmlentities($amount_value); ?></div>

                                            <div class="new-checkout-list-item-btns">
                                                <a href="#" class="btn btn-checkout-details tooltip tooltip-bottom" data-tooltip="<?= Security::htmlentities(_("Details")); ?>">
                                                    <span class="fa fa-search"></span>
                                                </a>
                                                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('order') . 'remove/' . $key . '/'); ?>" class="btn tooltip tooltip-bottom" data-tooltip="<?= Security::htmlentities(_("Remove")); ?>">
                                                    <span class="fa fa-times"></span>
                                                </a>
                                            </div>
                                            
                                        </div>

                                        <div class="new-checkout-list-lines hidden-normal">
                                            <?php foreach ($item['lines'] as $line):?>
                                                <div class="ticket-line">
                                                    <?php foreach ($line['numbers'] as $number):?>
                                                        <div class="ticket-line-number"><?= intval($number); ?></div>
                                                    <?php endforeach;?>

                                                    <?php foreach ($line['bnumbers'] as $bnumber):?>
                                                        <div class="ticket-line-bnumber"><?= intval($bnumber); ?></div>
                                                    <?php endforeach;?>
                                                </div>
                                            <?php endforeach;?>
                                        </div>

                                    </div>
                                </li>
                            <?php
                            endif;
                        endforeach;
                        ?>
                        </tbody>
                    </ul>
                    <div class="clearfix"></div>
                    <div class="order-promo-code">
                        <?php
                        if (!empty($code)):
                            ?>
                            <form method="post" action=".">
                                <div class="promo-info">
                                    <?php if ($any_multidraw):
                                        ?>
                                        <div class="promo-code-info">
                                            <span
                                                class="promo-info-circle fa fa-exclamation-circle tooltip tooltip-bottom"
                                                data-tooltip="<?= Security::htmlentities(_("Only one discount applies when buying a multidraw ticket.<br>Additional discount codes do not apply.")); ?>">
                                            </span>
                                            <span><?= $code_used_text; ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="promo-code-alert">
                                            <span class="fa fa-check-circle"></span>
                                            <span><?= $code_used_text; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" name="input[delete]" id="deletePromoCode" class="btn"
                                            value="1"><?= Security::htmlentities(_("Remove")) ?></button>
                                </div>
                            </form>
                        <?php
                        elseif ($promo_active && (empty($code))):
                            ?>
                            <form method="post" action=".">
                                <div class="platform-form form-promo-code">
                                    <input type="text"
                                           class="form-control"
                                           name="input[promo_code]"
                                           placeholder="<?= htmlspecialchars(_("Enter promo code")); ?>"/>
                                    <button type="submit" id="applyPromoCode"
                                            class="btn"><?= Security::htmlentities(_("Apply")); ?></button>
                                </div>
                            </form>
                        <?php
                        endif;
                        ?>
                    </div>
                <?php
                else:
                    ?>
                    <div class="platform-alert platform-alert-info order-alert">
                        <?php
                        echo '<p><span class="fa fa-exclamation-circle"></span> ' .
                            sprintf(
                                wp_kses(
                                        _(
                                        'You don\'t have any tickets. <a href="%s">' .
                                            'Play now</a> to add tickets to your order.'
                                    ),
                                    array('a' => array("href" => array()))
                                ),
                                    UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play'))
                            ) . '</p>';
                        ?>
                    </div>
                <?php
                endif;
                ?>
                <?php
                    list(
                        $class,
                        $msg,
                        $disabled,
                        $url
                        ) = $order_obj->prepare_html_vars();

                    $min_amount_formatted = $order_obj->get_min_amount();
                    $total_sum = $order_obj->get_total_sum_unformatted();
                    $total_sum_discounted = 0;
                    if (isset($code) && isset($code['discount_user']) && $code['discount_user'] != 0) {
                        $discount = $code['discount_user'];
                        $total_sum_discounted = $total_sum - $discount;
                    }
                    $orderAmountLeft = $maxOrderAmount - ($total_sum_discounted > 0 ? $total_sum_discounted : $total_sum);
                    $disabledAddTickets = $disabledAddTickets && $orderAmountLeft > 0;
                    if ($orderAmountLeft < 0.1 * $maxOrderAmount && $orderAmountLeft > 0):
                ?>
                <div class="platform-alert platform-alert-info order-alert">
                    <p>
                        <span class="fa fa-exclamation-circle"></span>
                        You can add tickets for a maximum amount of <?= Lotto_View::format_currency(
                            $orderAmountLeft,
                            $user_currency["code"],
                            true
                        );?>.
                    </p>
                </div>
                <?php endif; ?>
                <div class="order-buttons">
                    <div class="pull-left">
                      <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('play')); ?>"
                         class="btn btn-secondary btn-more-pc <?php echo $disabledAddTickets ? 'disabled' : '';?>"
                         <?php if ($disabledAddTickets): ?>onclick="return false;"<?php endif; ?>>
                          <?= _("Add more tickets"); ?>
                      </a>
                    </div>

                    <?php
                    if (isset($order) && count($order) > 0):
                        ?>
                        <div class="pull-right">
                        <span class="order-sum">
                            <?= Security::htmlentities(_("Total")); ?>: <span>
                                <?php
                                $total_sum_formatted = $order_obj->get_total_sum();
                                if (isset($code) && ($total_sum_discounted > 0)):
                                    ?>
                                    <span class="sum-discounted"><?= Security::htmlentities($total_sum_formatted); ?></span>
                                    <span><?= Security::htmlentities(Lotto_View::format_currency(
                                            $total_sum_discounted,
                                            $user_currency["code"],
                                            true
                                        )); ?></span>
                                <?php
                                else:
                                        echo(Security::htmlentities($total_sum_formatted));
                                endif;
                                ?>
                            </span>
                        </span>
                            <?php
                            if (!lotto_platform_is_user()):
                                ?>
                                <a href="<?= UrlHelper::esc_url($url) ?>"
                                   class="btn btn-primary btn-order btn-lg btn-mobile-large <?= $class; ?><?= $disabled; ?>"
                                   data-minorder="<?= Security::htmlentities($min_amount_formatted); ?>">
                                    <?= Security::htmlentities(_("Pay now")); ?>
                                </a>
                            <?php
                            endif;
                            ?>
                        </div>
                    <?php
                    endif;
                    ?>
                    <div class="clearfix"></div>
                </div>
                <?php /* end of moved */ ?>
            </div>
        </div>
        <?php
        Lotto_Settings::getInstance()->set("payment_errors", $errors);
        if (isset($msg)) {
            Lotto_Settings::getInstance()->set("payment_msg", $msg);
        }
        /*
        <?php if (is_active_sidebar('order-sidebar-id')) : ?>
            <?php Lotto_Helper::widget_before_area('order-sidebar-id'); ?>
            <?php dynamic_sidebar('order-sidebar-id'); ?>
            <?php Lotto_Helper::widget_after_area('order-sidebar-id'); ?>
        <?php endif; ?> */

        get_template_part('content', 'order-payment');
        ?>
    </div>
<?php get_footer(); ?>
<?php
  if (!empty($beginCheckoutData) || !empty($addToCartData)) {
      $value = $total_sum_discounted > 0 ? $total_sum_discounted : $total_sum;

      if (!empty($beginCheckoutData)) {
          $beginCheckoutData['value'] = round($value, 2);
      }

      if (!empty($addToCartData)) {
          $addToCartData['value'] = round($value, 2);
      }
  }
?>
<?php if (!empty($beginCheckoutData)): ?>
  <script>
    window.beginCheckoutData = <?php echo json_encode($beginCheckoutData); ?>;
  </script>
<?php endif; ?>
<?php if (!empty($addToCartData)): ?>
  <script>
    if (window.dataLayer && Array.isArray(window.dataLayer)) {
      // Sending of the "add_to_cart" event
      // See commented method: @see \Events_User_Cart_Add::run
      window.dataLayer.push(<?php echo json_encode($addToCartData); ?>);
    }
  </script>
<?php endif; ?>
