<h3>
    <?= _("VisaNet"); ?>
</h3>
<?php
    if (isset($adata['errorCode'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Error Code")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['errorCode']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['errorMessage'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Error Message")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['errorMessage']); ?></span><br>
<?php
    endif;
    
    if (isset($adata['header'])):
        $header = $adata['header'];
?>
        <h4>
            <?= _("Header"); ?>
        </h4>
<?php
        if (isset($header->ecoreTransactionUUID)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ecoreTransactionUUID")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($header->ecoreTransactionUUID); ?></span><br>
<?php
        endif;

        if (isset($header->ecoreTransactionDate)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ecoreTransactionDate")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($header->ecoreTransactionDate); ?></span><br>
<?php
        endif;

        if (isset($header->millis)):
?>
            <span class="details-label"><?= Security::htmlentities(_("millis")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($header->millis); ?></span><br>
<?php
        endif;
    endif;
    
    if (isset($adata['data'])):
        $visanet_data = $adata['data'];
?>
        <h4>
            <?= _("Data"); ?>
        </h4>
<?php
        if (isset($visanet_data->CURRENCY)):
?>
            <span class="details-label"><?= Security::htmlentities(_("CURRENCY")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data->CURRENCY); ?></span><br>
<?php
        endif;

        if (isset($visanet_data->AMOUNT)):
?>
            <span class="details-label"><?= Security::htmlentities(_("AMOUNT")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data->AMOUNT); ?></span><br>
<?php
        endif;

        if (isset($visanet_data->MERCHANT)):
?>
            <span class="details-label"><?= Security::htmlentities(_("MERCHANT")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data->MERCHANT); ?></span><br>
<?php
        endif;
    endif;
    
    if (isset($adata['fulfillment'])):
        $fulfillment = $adata['fulfillment'];
?>
        <h4>
            <?= _("Fulfillment"); ?>
        </h4>
<?php
        if (isset($fulfillment->channel)):
?>
            <span class="details-label"><?= Security::htmlentities(_("channel")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->channel); ?></span><br>
<?php
        endif;

        if (isset($fulfillment->merchantId)):
?>
            <span class="details-label"><?= Security::htmlentities(_("merchantId")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->merchantId); ?></span><br>
<?php
        endif;

        if (isset($fulfillment->terminalId)):
?>
            <span class="details-label"><?= Security::htmlentities(_("terminalId")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->terminalId); ?></span><br>
<?php
        endif;

        if (isset($fulfillment->captureType)):
?>
            <span class="details-label"><?= Security::htmlentities(_("captureType")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->captureType); ?></span><br>
<?php
        endif;
        
        if (isset($fulfillment->countable)):
?>
            <span class="details-label"><?= Security::htmlentities(_("countable")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->countable); ?></span><br>
<?php
        endif;

        if (isset($fulfillment->fastPayment)):
?>
            <span class="details-label"><?= Security::htmlentities(_("fastPayment")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->fastPayment); ?></span><br>
<?php
        endif;

        if (isset($fulfillment->signature)):
?>
            <span class="details-label"><?= Security::htmlentities(_("signature")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($fulfillment->signature); ?></span><br>
<?php
        endif;
    endif;
    
    if (isset($adata['order'])):
        $visanet_order = $adata['order'];
?>
        <h4>
            <?= _("Order"); ?>
        </h4>
<?php
        if (isset($visanet_order->tokenId)):
?>
            <span class="details-label"><?= Security::htmlentities(_("tokenId")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->tokenId); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->purchaseNumber)):
?>
            <span class="details-label"><?= Security::htmlentities(_("purchaseNumber")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->purchaseNumber); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->amount)):
?>
            <span class="details-label"><?= Security::htmlentities(_("amount")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->amount); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_order->currency)):
?>
            <span class="details-label"><?= Security::htmlentities(_("currency")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->currency); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->authorizedAmount)):
?>
            <span class="details-label"><?= Security::htmlentities(_("authorizedAmount")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->authorizedAmount); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->authorizationCode)):
?>
            <span class="details-label"><?= Security::htmlentities(_("authorizationCode")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->authorizationCode); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->actionCode)):
?>
            <span class="details-label"><?= Security::htmlentities(_("actionCode")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->actionCode); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->traceNumber)):
?>
            <span class="details-label"><?= Security::htmlentities(_("traceNumber")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->traceNumber); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->transactionDate)):
?>
            <span class="details-label"><?= Security::htmlentities(_("transactionDate")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->transactionDate); ?></span><br>
<?php
        endif;

        if (isset($visanet_order->transactionId)):
?>
            <span class="details-label"><?= Security::htmlentities(_("transactionId")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_order->transactionId); ?></span><br>
<?php
        endif;
    endif;

    if (isset($adata['dataMap'])):
        $visanet_data_map = $adata['dataMap'];
?>
        <h4>
            <?= _("Data Map"); ?>
        </h4>
<?php
        if (isset($visanet_data_map->CURRENCY)):
?>
            <span class="details-label"><?= Security::htmlentities(_("CURRENCY")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->CURRENCY); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->TRANSACTION_DATE)):
?>
            <span class="details-label"><?= Security::htmlentities(_("TRANSACTION_DATE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->TRANSACTION_DATE); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->TERMINAL)):
?>
            <span class="details-label"><?= Security::htmlentities(_("TERMINAL")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->TERMINAL); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->ACTION_CODE)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ACTION_CODE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->ACTION_CODE); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->TRACE_NUMBER)):
?>
            <span class="details-label"><?= Security::htmlentities(_("TRACE_NUMBER")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->TRACE_NUMBER); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->ECI_DESCRIPTION)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ECI_DESCRIPTION")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->ECI_DESCRIPTION); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->ECI)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ECI")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->ECI); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->SIGNATURE)):
?>
            <span class="details-label"><?= Security::htmlentities(_("SIGNATURE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->SIGNATURE); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->BRAND)):
?>
            <span class="details-label"><?= Security::htmlentities(_("BRAND")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->BRAND); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->CARD)):
?>
            <span class="details-label"><?= Security::htmlentities(_("CARD")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->CARD); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->MERCHANT)):
?>
            <span class="details-label"><?= Security::htmlentities(_("MERCHANT")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->MERCHANT); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->STATUS)):
?>
            <span class="details-label"><?= Security::htmlentities(_("STATUS")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->STATUS); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->ADQUIRENTE)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ADQUIRENTE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->ADQUIRENTE); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->ACTION_DESCRIPTION)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ACTION_DESCRIPTION")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->ACTION_DESCRIPTION); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->ID_UNICO)):
?>
            <span class="details-label"><?= Security::htmlentities(_("ID_UNICO")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->ID_UNICO); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->AMOUNT)):
?>
            <span class="details-label"><?= Security::htmlentities(_("AMOUNT")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->AMOUNT); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->PROCESS_CODE)):
?>
            <span class="details-label"><?= Security::htmlentities(_("PROCESS_CODE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->PROCESS_CODE); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->TRANSACTION_ID)):
?>
            <span class="details-label"><?= Security::htmlentities(_("TRANSACTION_ID")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->TRANSACTION_ID); ?></span><br>
<?php
        endif;
        
        if (isset($visanet_data_map->AUTHORIZATION_CODE)):
?>
            <span class="details-label"><?= Security::htmlentities(_("AUTHORIZATION_CODE")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($visanet_data_map->AUTHORIZATION_CODE); ?></span><br>
<?php
        endif;
    endif;
