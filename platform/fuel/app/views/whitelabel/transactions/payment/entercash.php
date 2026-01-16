<h3>
    <?= _("Entercash"); ?>
</h3>
<?php 
    if (isset($adata['pre_deposit_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Pre-deposit ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['pre_deposit_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['order_status'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status (Return URL)")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_status']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['order_status_details'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Status details (Return URL)")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['order_status_details']); ?></span><br>
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
    
    if (isset($adata['static_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Static ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['static_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['clearinghouse'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Clearing house")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['clearinghouse']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['method'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Method")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['method']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['uuid'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("UUID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['uuid']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['enduser_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Enduser ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['enduser_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['notification_id'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Notification ID")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['notification_id']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['timestamp'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Timestamp")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['timestamp']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['attributes']['fee'])):
?>
        <span class="details-label"><?= Security::htmlentities(_("Fee")); ?>:</span>
        <span class="details-value"><?= Security::htmlentities($adata['attributes']['fee']); ?></span><br>
<?php 
    endif;
    
    if (isset($adata['attributes'])):
?>
        <h4>
            <?= _("Attributes"); ?>
        </h4>
    <?php 
        if (isset($adata['attributes']['creditreasoncode'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Credit reason code")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['creditreasoncode']); ?></span><br>
    <?php 
        endif;

        if (isset($adata['attributes']['creditreasonmessage'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Credit reason message")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['creditreasonmessage']); ?></span><br>
    <?php 
        endif;

        if (isset($adata['attributes']['closedreason'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Closed reason")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['closedreason']); ?></span><br>
    <?php 
        endif;

        if (isset($adata['attributes']['failreasoncode'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Fail reason code")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['failreasoncode']); ?></span><br>
    <?php 
        endif;

        if (isset($adata['attributes']['failreasonmessage'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Fail reason message")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['failreasonmessage']); ?></span><br>
    <?php 
        endif;

        if (isset($adata['attributes']['static_id'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Static ID")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['static_id']); ?></span><br>
    <?php 
        endif;

        if (isset($adata['attributes']['same_bank'])):
    ?>
            <span class="details-label"><?= Security::htmlentities(_("Same bank")); ?>:</span>
            <span class="details-value"><?= Security::htmlentities($adata['attributes']['same_bank']); ?></span><br>
    <?php 
        endif;
        
        if (isset($adata['attributes']['user_details'])):
    ?>
            <h5>
                <?= _("User details"); ?>
            </h5>
        <?php 
            if (isset($adata['attributes']['user_details']['name'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Name")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['name']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['bank_id'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Bank ID")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['bank_id']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['account_number'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Account number")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['account_number']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['national_id'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("National ID")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['national_id']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['login_id'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Login ID")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['login_id']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['bank_name'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Bank name")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['bank_name']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['login_method'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Login method")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['login_method']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['message_on_statement'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("Message on statement")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['message_on_statement']); ?></span><br>
        <?php 
            endif;

            if (isset($adata['attributes']['user_details']['siirto_id'])):
        ?>
                <span class="details-label"><?= Security::htmlentities(_("siirto id")); ?>:</span>
                <span class="details-value"><?= Security::htmlentities($adata['attributes']['user_details']['siirto_id']); ?></span><br>
<?php 
            endif;
        endif;
    endif;
