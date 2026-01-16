<h3>
    <?= _("Flutterwave"); ?>
</h3>
<?php if (isset($adata['request']['txref'])):?>
    <span class="details-label"><?= Security::htmlentities(_("txRef")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['request']['txref']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['flwRef'])):?>
    <span class="details-label"><?= Security::htmlentities(_("flwRef")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['flwRef']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['orderRef'])):?>
    <span class="details-label"><?= Security::htmlentities(_("orderRef")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['orderRef']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['amount'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['amount']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['charged_amount'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Charged amount")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['charged_amount']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['status'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Status")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['status']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['IP'])):?>
    <span class="details-label"><?= Security::htmlentities(_("IP")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['IP']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['currency'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Currency")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['currency']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['payment_entity'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Payment entity")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['payment_entity']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['event.type'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Event type")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['event.type']); ?></span><br>
<?php endif; ?>

<?php if (isset($adata['result']['customer']['id'])):?>
    <span class="details-label"><?= Security::htmlentities(_("Customer email")); ?>:</span>
    <span class="details-value"><?= Security::htmlentities($adata['result']['customer']['id']); ?></span><br>
<?php endif; ?>