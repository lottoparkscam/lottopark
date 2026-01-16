<?php

if (Helpers_App::is_production_environment()) : ?>

    <?php if (IS_CASINO) : ?>
        <script type="text/javascript" src="https://partners.lovcasino.com/integration/general_integration"></script>
    <?php
    endif;
endif;
?>