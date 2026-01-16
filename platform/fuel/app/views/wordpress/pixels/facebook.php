!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?= $whitelabel_pixel_id; ?>'<?php echo !empty($additional_config) ? ", ".json_encode($additional_config) : ''; ?>);
<?php if(!is_null($affiliate_pixel_id)): ?>
  fbq('init', '<?= $affiliate_pixel_id; ?>'<?php echo !empty($affiliate_additional_config) ? ", ".json_encode($affiliate_additional_config) : ''; ?>);
<?php endif; ?>
  fbq('track', 'PageView');
<?php if(!empty($events)): foreach($events AS $key => $event): ?>
<?php if($event['name'] == "InitiateCheckout"): ?>
function facebook_checkout_events(e) {
    window.setTimeout(function() {
        facebook_done = true;
        jQuery('.payment-form').submit();
    }, 1000);
<?php endif; ?>
  fbq('trackSingle<?php if(!empty($event['custom'])): echo 'Custom'; endif; ?>', '<?= $whitelabel_pixel_id; ?>', '<?= $event['name']; ?>', <?= str_replace('"--deposit--"', "jQuery('#paymentAmount').val()", json_encode($event['data'])); ?>);
<?php if(!is_null($affiliate_pixel_id)): ?>
  fbq('trackSingle<?php if(!empty($affiliate_events[$key]['custom'])): echo 'Custom'; endif; ?>', '<?= $affiliate_pixel_id; ?>', '<?= $affiliate_events[$key]['name']; ?>', <?= str_replace('"--deposit--"', "jQuery('#paymentAmount').val()", json_encode($affiliate_events[$key]['data'])); ?>);
<?php endif; ?>
<?php if($event['name'] == "InitiateCheckout"): ?>
}
<?php endif; ?>
<?php endforeach; endif; ?>
