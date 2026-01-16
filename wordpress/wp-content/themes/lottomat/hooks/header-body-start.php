<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MKXWBCK" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php

use Helpers\Wordpress\LanguageHelper;

if (LanguageHelper::getCurrentLanguageShortcode() === 'ro') {
    echo '<script> (function(){ var pixel = document.createElement("script"); pixel.src = "https://platformio-resources.s3.amazonaws.com/js/session-pixel.js"; pixel.type = "text/javascript"; pixel.dataset.id = "178379"; pixel.dataset.pixelScope = "advertiser"; document.body.appendChild(pixel); })(); </script>';
}
?>