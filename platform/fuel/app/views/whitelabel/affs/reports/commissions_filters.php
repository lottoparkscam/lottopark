<?php
    $commission_filters = [];
    
    $commission_id_t = '';
    if (null !== Input::get("filter.id")) {
        $commission_id_t = Input::get("filter.id");
    }
    $commission_filters['id'] = Security::htmlentities($commission_id_t);
    
    $commission_email_t = '';
    if (null !== Input::get("filter.email")) {
        $commission_email_t = Input::get("filter.email");
    }
    $commission_filters['email'] = Security::htmlentities($commission_email_t);
    
    $commission_name_t = '';
    if (null !== Input::get("filter.name")) {
        $commission_name_t = Input::get("filter.name");
    }
    $commission_filters['name'] = Security::htmlentities($commission_name_t);
    
    $commission_surname_t = '';
    if (null !== Input::get("filter.surname")) {
        $commission_surname_t = Input::get("filter.surname");
    }
    $commission_filters['surname'] = Security::htmlentities($commission_surname_t);
    
    $commission_range_start_t = '';
    if (!empty(Input::get("filter.range_start"))) {
        $commission_range_start_t = Input::get("filter.range_start");
    }
    $commission_filters['range_start'] = Security::htmlentities($commission_range_start_t);

    $commission_range_end_t = '';
    if (!empty(Input::get("filter.range_end"))) {
        $commission_range_end_t = Input::get("filter.range_end");
    }
    $commission_filters['range_end'] = Security::htmlentities($commission_range_end_t);
?>

<form class="form-inline form-filter" method="get" action="/affs/commissions">
    <label>
        <?= _("Filter"); ?>:
    </label>
    
    <div class="form-group">
        <select name="filter[country]" 
                id="filterCountry" 
                class="form-control filterSelect">
            <option value="a">
                <?= _("Country"); ?>
            </option>
            <?php 
                foreach ($countries as $key => $country):
                    $is_selected = '';
                    if (Input::get("filter.country") == $key) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $key; ?>"<?= $is_selected; ?>>
                        <?= $countries[$key]; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $commission_filters['id']; ?>" 
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $commission_filters['email']; ?>" 
               class="form-control filterInput" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("E-mail"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $commission_filters['name']; ?>" 
               class="form-control filterInput" 
               id="filterName" 
               name="filter[name]" 
               placeholder="<?= _("First Name"); ?>">
    </div>
    
    <div class="form-group">
        <input type="text" 
               value="<?= $commission_filters['surname']; ?>" 
               class="form-control filterInput" 
               id="filterSurname" 
               name="filter[surname]" 
               placeholder="<?= _("Last Name"); ?>">
    </div>
    
    <div class="new-filter-row">
        <div class="form-group text-nowrap">
            <label class="control-label" for="filterRange">
                <?= _("Range"); ?>:
            </label>
            <div class="input-group input-daterange input-daterange-small datepicker" 
                 data-date-end-date="0d">
                <input id="filterRange" 
                       name="filter[range_start]" 
                       autocomplete="off" 
                       type="text" 
                       class="form-control filterInput" 
                       value="<?= $commission_filters['range_start']; ?>">
                <span class="input-group-addon">
                    <?= _("to"); ?>
                </span>
                <input type="text" 
                       name="filter[range_end]" 
                       autocomplete="off" 
                       class="form-control filterInput" 
                       value="<?= $commission_filters['range_end']; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <?= _("Filter"); ?>
        </button>
        <button type="reset" class="btn btn-success" id="filter-form-whitelabel">
            <?= _("Reset"); ?>
        </button>
    </div>
</form>

