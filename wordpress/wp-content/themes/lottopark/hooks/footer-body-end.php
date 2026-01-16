<?php
if (Helpers_App::is_production_environment()): ?>
    <script id="sbinit" src="https://support.lottopark.com/js/main.js"></script>
    <script type="text/javascript">
        window.smartlook || (function(d) {
            var o = smartlook = function() { o.api.push(arguments) },
                h = d.getElementsByTagName('head')[0],
                c = d.createElement('script');
            o.api = [];
            c.async = true;
            c.type = 'text/javascript';
            c.charset = 'utf-8';
            c.src = 'https://web-sdk.smartlook.com/recorder.js';
            h.appendChild(c);
        })(document);
        smartlook('init', 'ef71360e1ab80b9befc31a6e218ef34704b18830', { region: 'eu' });
    </script>
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s){
            if(f.fbq) return;
            n = f.fbq = function(){
                n.callMethod ? n.callMethod.apply(n,arguments) : n.queue.push(arguments)
            };
            if(!f._fbq) f._fbq = n;
            n.push = n; n.loaded = !0; n.version='2.0';
            n.queue = [];
            t = b.createElement(e); t.async = !0;
            t.src = v; s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)
        }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

        fbq('init', '317046154451580');

        const isEvoDynamicFB = window.parentAffiliateToken === '1b309a8da3';
        if (isEvoDynamicFB) {
            fbq('init', '1488642465069695');

            <?php if (Lotto_Platform::is_page('purchase') || Lotto_Platform::is_page('success')): ?>
            if (window.transactionType && window.orderId && window.orderAmount) {
                switch (window.transactionType) {
                    case 'purchase':
                        fbq('track', 'Purchase', {
                            content_name: window.orderTitle ?? 'Unknown purchase',
                            value: window.orderAmount,
                            currency: 'USD'
                        });
                        break;
                    case 'deposit':
                        fbq('track', 'Deposit', {
                            value: window.orderAmount,
                            currency: 'USD',
                            payment_method_id: window.paymentMethodId
                        });
                        break;
                }
            }
            <?php endif; ?>
            <?php
              $user = lotto_platform_user();
              if (Lotto_Platform::is_page('welcome') && isset($user['token'])):
            ?>
                fbq('track', 'Registration', {
                    user_id: '<?= 'LPU' . $user['token'] ?>';
                });
            <?php endif; ?>
        } else if ('<?= ($_COOKIE['ref'] ?? '') ?>' === '1b309a8da3') {
          fbq('init', '1488642465069695');
        }
        fbq('track', 'PageView');
    </script>
    <noscript>
      <img height="1" width="1" style="display:none"
           src="https://www.facebook.com/tr?id=835085538711554&ev=PageView&noscript=1" />
    </noscript>
  <!-- End Meta Pixel Code -->
<?php endif; ?>
<!-- GTM send events -->
<script>
    <?php if (Lotto_Platform::is_page('purchase') || Lotto_Platform::is_page('success')): ?>
      if (window.transactionType && window.dataLayer && Array.isArray(window.dataLayer)) {
        switch (window.transactionType) {
          case 'purchase':
            if (window.purchaseData) {
              window.dataLayer.push(window.purchaseData);
            }
            break;
          case 'deposit':
            if (window.depositData) {
              window.dataLayer.push(window.depositData);
            }
            break;
        }
      }
    <?php endif; ?>
</script>
