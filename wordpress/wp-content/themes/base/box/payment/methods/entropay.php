<?php

// TODO: {Vordis 2019-05-20 13:46:10} comparisons below should be done via switch
// TODO: {Vordis 2019-05-22 15:18:03} Also we could use Security since fuel is present here
?>
<p class="payment-info entropay-info"<?php
    if (!$deposit || !empty($entropay_bp)):
        $total_sum_arr = explode('.', !$deposit ? $total_sum : $entropay_bp);
        echo ' data-pounds="'.$total_sum_arr[0].'" data-cents="'.$total_sum_arr[1].'"';
    endif;
?>><?php
        echo wp_kses(sprintf(_('You will be redirected to the standard credit card payment form. If you don\'t have an Entropay account, you can always register it <a href="%s">here</a>.'), lotto_platform_get_permalink_by_slug($deposit ? 'deposit' : 'order').'entropay/'), array('a' => array('href' => array())));
?></p>
