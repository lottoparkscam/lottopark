<?php

$email_t = '';
if (null !== Input::get("filter.email")) {
    $email_t = Input::get("filter.email");
}
$email_text = Security::htmlentities($email_t);

?>
<form class="form-inline form-filter" method="get" action="/affs/payouts">
    <label>
        <?= _("Filter"); ?>: 
    </label>
    <div class="form-group">
        <input type="text" 
               value="<?= $email_text; ?>" 
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("E-mail"); ?>">
    </div>
    <button type="submit" class="btn btn-primary">
        <?= _("Filter"); ?>
    </button>
</form>

