<?php
    $range_start_t = '';
    if (!empty(Input::get("filter.range_start"))) {
        $range_start_t = Input::get("filter.range_start");
    }
    $range_start = Security::htmlentities($range_start_t);
    
    $range_end_t = '';
    if (!empty(Input::get("filter.range_end"))) {
        $range_end_t = Input::get("filter.range_end");
    }
    $range_end = Security::htmlentities($range_end_t);

    $email_t = '';
    if (null !== Input::get("filter.email")) {
        $email_t = Input::get("filter.email");
    }
    $email_text = Security::htmlentities($email_t);
?>
<form class="form-inline form-filter" method="get" action="/affs/reports">
    <div class="form-group text-nowrap">
        <label class="control-label" for="filterRange"><?= _("Range"); ?>:</label>
        <div class="input-group input-daterange datepicker" data-date-end-date="0d">
            <input id="filterRange" 
                   required 
                   name="filter[range_start]" 
                   type="text" 
                   class="form-control filterInput" 
                   value="<?= $range_start; ?>">
            <span class="input-group-addon"><?= _("to"); ?></span>
            <input type="text" 
                   required 
                   name="filter[range_end]" 
                   class="form-control filterInput" 
                   value="<?= $range_end; ?>">
        </div>
    </div>
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
        <?= _("Report"); ?>
    </button>
</form>
