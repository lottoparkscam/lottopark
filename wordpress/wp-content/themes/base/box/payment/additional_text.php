<?php
if (!empty($additional_text)):
?>
    <article class="page">
        <p class="text-center">
            <?php
                if (!empty($additional_text['order_number'])):
                    echo _("Purchase number") . ": ";
            ?>
                    <b><?= $additional_text['order_number']; ?></b>
                    <br>
            <?php
                endif;
            
                if (!empty($additional_text['masked_card_number'])):
                    echo _("Card Number") . ": ";
            ?>
                    <b><?= $additional_text['masked_card_number']; ?></b>
                    <br>
            <?php
                endif;
                
                if (!empty($additional_text['brand_card_name'])):
                    echo _("Brand") . ": ";
            ?>
                    <b><?= $additional_text['brand_card_name']; ?></b>
                    <br>
            <?php
                endif;
                
                if (!empty($additional_text['date_time_of_order'])):
                    echo _("Date of the order") . ": ";
            ?>
                    <b><?= $additional_text['date_time_of_order']; ?></b>
                    <br>
            <?php
                endif;
                
                if (!empty($additional_text['refusal_reason'])):
                    echo _("Refusal reason") . ": ";
            ?>
                    <b><?= $additional_text['refusal_reason']; ?></b>
                    <br>
            <?php
                endif;
            ?>
        </p>
    </article>
<?php
endif;
