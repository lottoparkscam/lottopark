<?php

if (Helpers_App::is_production_environment()) : ?>
    <?php if (IS_CASINO) : ?>
    <script type="text/javascript" src="https://partners.lottopark.com/integration/general_integration"></script>
<?php
    endif;
endif;
?>
<meta name="facebook-domain-verification" content="ckwfx50e1o4pd2x2obg1hv5dc7s14t" />

<?php if (Helpers_App::is_production_environment()):?>

    <!-- Aweber -->
    <script async src="https://assets.aweber-static.com/aweberjs/aweber.js"></script>
    <script>
        var AWeber = window.AWeber || [];
        AWeber.push(function() {
            AWeber.WebPush.init(
                'BGQW9QnIUU-PPSnM6v_tI6VIsxFMI689NHFlucfi4O6wuR31OtP6W-EroQ7kqtbVanc-q29S-j-opwTognrxpwg',
                '624afaa3-cd60-407b-be99-2b75769b9481',
                'a856874a-fb6f-45cb-90d8-b5c43306d085'
            );
        });
    </script>
    <!-- End Aweber -->

<?php endif;?>
