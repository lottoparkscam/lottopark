<?php
if (!defined('WPINC')) {
    die;
}

$lotto_platform_payment_failure = "";

if (function_exists("lotto_platform_payment_failure")) {
    $lotto_platform_payment_failure = lotto_platform_payment_failure();
}

get_header();

$entropay_show = Lotto_Settings::getInstance()->get("entropay_show");
$transaction = Lotto_Settings::getInstance()->get("transaction");

$entropay_amount = explode(".", $transaction->amount);

$show_text = _(
    "The payment couldn't be processed. " .
    "Please try again or contact us."
);
?>
<div class="content-area">
    <div class="main-width content-width">
        <div class="content-box">
            <section class="page-content">
                <article class="page">
                    <h1 class="text-center"><?php the_title(); ?></h1>
                    <p style="text-align: center;">
                        <?= $show_text; ?>
                    </p>
                    <?php
                        if ($entropay_show):
                            $transaction_slug = '';
                            if ($transaction->type == Helpers_General::TYPE_TRANSACTION_DEPOSIT) {
                                $transaction_slug = 'deposit';
                            } else {
                                $transaction_slug = 'order';
                            }
                            
                            $entropay_text = lotto_platform_get_permalink_by_slug($transaction_slug);
                            $entropay_text .= 'entropay/' . $entropay_amount[0] . '-' . $entropay_amount[1];

                            $show_text = sprintf(
                                _(
                                    'Your credit card may be blocked because of ' .
                                    'the unsupported region. We suggest you to register an ' .
                                    '<a href="%s">Entropay</a> account.'
                                ),
                                $entropay_text
                            );
                    ?>
                            <p class="entropay-failure">
                                <?= $show_text; ?>
                            </p>
                    <?php
                        endif;
                        
                        the_content();
                    ?>
                </article>
                <?= $lotto_platform_payment_failure ?>
            </section>
        </div>
    </div>
</div>
<?php get_footer(); ?>
