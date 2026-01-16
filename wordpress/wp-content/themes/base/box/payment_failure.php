<?php
if (!defined('WPINC')) {
    die;
}

$corder_inner_obj = new Forms_Wordpress_Myaccount_Corder();
$additional_failure_text = $corder_inner_obj->get_additional_failure_text($transaction);

if (!empty($additional_failure_text)):
?>
    <article class="page">
        <p class="text-center additional-payment-text">
            <?= $additional_failure_text; ?>
        </p>
    </article>
<?php
endif;

include('payment/additional_text.php');

$back_url = '';
if ((int)$transaction['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
    $back_url = lotto_platform_get_permalink_by_slug("order");
} else {
    $back_url = lotto_platform_get_permalink_by_slug("deposit");
}

?>
<div class="text-center deposit-failure">
    <p>
        <a href="<?= $back_url; ?>" 
           class="btn btn-lg btn-primary">
               <?= Security::htmlentities(_("Try again")); ?>
        </a>
    </p>
</div>
<?php if (isset($purchaseData)):?>
  <script>
    window.purchaseData = <?php echo json_encode($purchaseData); ?>;
  </script>
<?php endif;?>

<?php if (isset($depositData)):?>
  <script>
    window.depositData = <?php echo json_encode($depositData); ?>;
  </script>
<?php endif;?>
