<?php

$warning_text = _(
    "Please be aware that wire transfers may take up " .
    "to 2 working days to be processed."
);

if (!$deposit) {
    $warning_text .= " " . _("This can result in delay of processing the ticket.");
}

$entercash_text_warning = Security::htmlentities($warning_text);
?>
<div class="platform-alert platform-alert-warning platform-alert-entercash-warning">
    <p>
        <span class="fa fa-exclamation-circle"></span>
        <?= $entercash_text_warning; ?>
    </p>
</div>
