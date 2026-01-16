<?php

use Helpers\UrlHelper;

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    
    if (!empty($whitelabel) &&
        Helpers_Whitelabel::is_V1((int)$whitelabel['type'])
    ):
        $descriptor_text = '';
        if (!empty($whitelabel_payment_method['data'])) {
            $data = unserialize($whitelabel_payment_method['data']);
            if (!empty($data['truevocc_descriptor'])) {
                $descriptor_text = $data['truevocc_descriptor'];
            }
        }
        $final_text = "";
        if (!empty($descriptor_text)) {
            $final_text .= _('This charge will appear on your statement as').' ';
            $final_text .= '"' . $descriptor_text . '".';
        }
?>
        <p class="payment-info">
            <?= $final_text; ?>
        </p>
<?php
    endif;
    
    $terms_slug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('terms'));
    
    $checkbox_text_start = _("I accept the"). "&nbsp;";
    $checkbox_text_end = _("Terms and Conditions");
?>
    <div class="checkbox">
        <label class="accept-checkbox-label">
            <input type="checkbox" 
                   name="accept_term_and_conditions" 
                   class="accept_term_and_conditions" 
                   value="1"> 
            <?= $checkbox_text_start; ?>
            <a href="<?= UrlHelper::esc_url($terms_slug) ?>"
               target="_blank"><?= $checkbox_text_end; ?></a>
        </label>
    </div>
