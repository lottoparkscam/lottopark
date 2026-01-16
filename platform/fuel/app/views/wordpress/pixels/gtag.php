window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());

gtag('config', '<?= $whitelabel_gtag_id ?>'<?php if(!empty($additional_config)): ?>, <?= json_encode($additional_config); ?><?php endif; ?>);
<?php if(!is_null($affiliate_gtag_id)): ?>
gtag('config', '<?= $affiliate_gtag_id ?>'<?php if(!empty($affiliate_additional_config)): ?>, <?= json_encode($affiliate_additional_config); ?><?php endif; ?>);
  is_affiliate_gtag = true;
<?php endif; ?>

<?php if (!empty($events)): ?>
    <?php foreach ($events as $key => $event): ?>

        <?php if ($event['name'] == "begin_checkout"):
            $event['data']['event_callback'] = "--function--";
            ?>
            function gtag_checkout_events(e) {
        <?php endif; ?>

        <?php if ($event['name'] == 'login' || $event['name'] == 'sign_up'): ?>
            dataLayer.push(<?= json_encode($event['data']); ?>);
        <?php else: ?>
            gtag('event', '<?= $event['name'] ?>',
            <?= str_replace(
                ['"--function--"', '"--deposit--"'],
                [
                    "function() { gtag_done = true; jQuery('.payment-form').submit(); }",
                    "jQuery('#paymentAmount').val()"
                ],
                json_encode(array_merge($event['data'], ["send_to" => $whitelabel_gtag_id]))
            ); ?>
            );
        <?php endif; ?>
        <?php if (!is_null($affiliate_gtag_id)): ?>
            gtag('event', '<?= $affiliate_events[$key]['name'] ?>',
            <?= str_replace(
                ['"--function--"', '"--deposit--"'],
                [
                    "function() { gtag_aff_done = true; jQuery('.payment-form').submit(); }",
                    "jQuery('#paymentAmount').val()"
                ],
                json_encode(array_merge($affiliate_events[$key]['data'], ["send_to" => $affiliate_gtag_id]))
            ); ?>
            );
        <?php endif; ?>

        <?php if ($event['name'] == "begin_checkout"): ?>
            }
        <?php endif; ?>

    <?php endforeach; ?>
<?php endif; ?>
