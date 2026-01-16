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
    
    $id_t = '';
    if (null !== Input::get("filter.id")) {
        $id_t = Input::get("filter.id");
    }
    $id_text = Security::htmlentities($id_t);
    
    $email_t = '';
    if (null !== Input::get("filter.email")) {
        $email_t = Input::get("filter.email");
    }
    $email_text = Security::htmlentities($email_t);
    
    $name_t = '';
    if (null !== Input::get("filter.name")) {
        $name_t = Input::get("filter.name");
    }
    $name_text = Security::htmlentities($name_t);
    
    $surname_t = '';
    if (null !== Input::get("filter.surname")) {
        $surname_t = Input::get("filter.surname");
    }
    $surname_text = Security::htmlentities($surname_t);
?>
<form class="form-inline form-filter" method="get" action="/affs/leads">
    <label>
        <?php echo _("Filter"); ?>: 
    </label>
    
    <div class="form-group">
        <select name="filter[country]" 
                id="filterCountry" 
                class="form-control filterSelect">
            <option value="a">
                <?php echo _("Country"); ?>
            </option>
            <?php 
                foreach ($countries as $key => $country):
                    $is_selected = '';
                    if (Input::get("filter.country") == $key) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?php echo $key; ?>"<?= $is_selected; ?>>
                        <?php echo $countries[$key]; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>

    <div class="form-group">
        <input type="text" 
               value="<?= $id_text; ?>" 
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?php echo _("ID"); ?>">
    </div>

    <div class="form-group">
        <input type="text" 
               value="<?= $email_text; ?>" 
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?php echo _("E-mail"); ?>">
    </div>

    <div class="form-group">
        <input type="text" 
               value="<?= $name_text; ?>" 
               class="form-control filterInput" 
               id="filterName" 
               name="filter[name]" 
               placeholder="<?php echo _("First Name"); ?>">
    </div>

    <div class="form-group">
        <input type="text" 
               value="<?= $surname_text; ?>" 
               class="form-control filterInput" 
               id="filterSurname" 
               name="filter[surname]" 
               placeholder="<?php echo _("Last Name"); ?>">
    </div>

    <div class="new-filter-row">
        <div class="form-group text-nowrap">
            <label class="control-label" for="filterRange">
                <?php echo _("Range"); ?>:
            </label>
            <div class="input-group input-daterange input-daterange-small datepicker" 
                 data-date-end-date="0d">
                <input id="filterRange" 
                       name="filter[range_start]" 
                       autocomplete="off" 
                       type="text" 
                       class="form-control filterInput" 
                       value="<?= $range_start; ?>">
                <span class="input-group-addon">
                    <?php echo _("to"); ?>
                </span>
                <input type="text" 
                       name="filter[range_end]" 
                       autocomplete="off" 
                       class="form-control filterInput" 
                       value="<?= $range_end; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo _("Filter"); ?>
        </button>
        <button type="reset" class="btn btn-success" id="filter-form-whitelabel">
            <?= _("Reset"); ?>
        </button>
    </div>
</form>

