<h3>
    <?= _("Truevo CC"); ?>
</h3>
<?php
    if (isset($adata['id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['id']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['paymentType'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payment Type")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paymentType']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['paymentBrand'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Payment Brand")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['paymentBrand']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['amount'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Amount")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['amount']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['currency'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Currency")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['currency']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['descriptor'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Descriptor")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['descriptor']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['merchantTransactionId'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Merchant Transaction Id")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['merchantTransactionId']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['result'])):
?>
        <h4>
            <?= _("Result"); ?>
        </h4>
    <?php
        if (isset($adata['result']['code'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Code")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['result']['code']); ?></span><br>
    <?php
        endif;
        
        if (isset($adata['result']['description'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Description")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['result']['description']); ?></span><br>
    <?php
        endif;
    endif;
    
    if (isset($adata['card'])):
?>
        <h4>
            <?= _("Card"); ?>
        </h4>
    <?php
        if (isset($adata['card']['bin'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Bin")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['bin']); ?></span><br>
    <?php
        endif;
        
        if (isset($adata['card']['last4Digits'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Last 4 Digits")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['last4Digits']); ?></span><br>
    <?php
        endif;
        
        if (isset($adata['card']['holder'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Holder")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['holder']); ?></span><br>
    <?php
        endif;
        
        if (isset($adata['card']['expiryMonth'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Expiry Month")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['expiryMonth']); ?></span><br>
    <?php
        endif;
        
        if (isset($adata['card']['expiryYear'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Expiry Year")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['card']['expiryYear']); ?></span><br>
    <?php
        endif;
    endif;

    if (isset($adata['threeDSecure'])):
?>
        <h4>
            <?= _("ThreeDSecure"); ?>
        </h4>
    <?php
        if (isset($adata['threeDSecure']['eci'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("ECI")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['threeDSecure']['eci']); ?></span><br>
    <?php
        endif;
    endif;
    
    if (isset($adata['customParameters'])):
?>
        <h4>
            <?= _("Custom Parameters"); ?>
        </h4>
    <?php
        if (isset($adata['customParameters']['SHOPPER_EndToEndIdentity'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("SHOPPER_EndToEndIdentity")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['customParameters']['SHOPPER_EndToEndIdentity']); ?></span><br>
    <?php
        endif;
        
        if (isset($adata['customParameters']['CTPE_DESCRIPTOR_TEMPLATE'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("CTPE_DESCRIPTOR_TEMPLATE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['customParameters']['CTPE_DESCRIPTOR_TEMPLATE']); ?></span><br>
    <?php
        endif;
    endif;
    
    if (isset($adata['risk'])):
?>
        <h4>
            <?= _("Risk"); ?>
        </h4>
    <?php
        if (isset($adata['risk']['score'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Score")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['risk']['score']); ?></span><br>
    <?php
        endif;
    endif;
    
    if (isset($adata['buildNumber'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Build Number")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['buildNumber']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['timestamp'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Timestamp")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['timestamp']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['ndc'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("NDC")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['ndc']); ?></span><br>
<?php
    endif;
