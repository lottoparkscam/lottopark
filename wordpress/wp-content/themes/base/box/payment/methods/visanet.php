<?php

use Helpers\UrlHelper;

$terms_slug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('terms'));
    
    $checkbox_text_start = _("I accept the")." ";
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

