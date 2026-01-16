<?php
    $start_t = '';
    if (null !== Input::get("filter.range_start")) {
        $start_t = Input::get("filter.range_start");
    }
    $start_value = Security::htmlentities($start_t);
    
    $end_t = '';
    if (null !== Input::get("filter.range_end")) {
        $end_t = Input::get("filter.range_end");
    }
    $end_value = Security::htmlentities($end_t);
?>
<form class="form-inline" method="get" action="/paymentlogs">
    <div class="form-group text-nowrap">
        <label class="control-label" for="filterRange">
            <?= _("Date Range"); ?>:
        </label>
        <div class="input-group input-daterange datepicker" 
             data-date-start-date="-7d" 
             data-date-end-date="0d">
            <input id="filterRange" 
                   name="filter[range_start]" 
                   type="text" 
                   class="form-control filterInput" 
                   value="<?= $start_value; ?>">
            <span class="input-group-addon">
                <?= _("to"); ?>
            </span>
            <input type="text" 
                   name="filter[range_end]" 
                   class="form-control filterInput" 
                   value="<?= $end_value; ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="filterWhitelabel">
            <?= _("Whitelabel"); ?>:
        </label>
        <select name="filter[whitelabel]" 
                id="filterWhitelabel" 
                class="form-control filterSelect">
            <option value="0">
                <?= _("All"); ?>
            </option>
            <?php 
                foreach ($whitelabels as $whitelabel):
                    $is_selected = '';
                    if (Input::get("filter.whitelabel") == $whitelabel['id']) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $whitelabel['id']; ?>"<?= $is_selected; ?>>
                        <?= Security::htmlentities($whitelabel['name']); ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    <div class="form-group">
        <label for="filterType">
            <?= _("Type"); ?>:
        </label>
        <select name="filter[type]" 
                id="filterType" 
                class="form-control filterSelect">
            <option value="-1">
                <?= _("All"); ?>
            </option>
            <?php 
                for ($i = 0; $i < 4; $i++):
                    $is_selected = '';
                    if (Input::get("filter.type") === "$i") {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $i; ?>"<?= $is_selected; ?>>
                        <?= Security::htmlentities(Lotto_View::type_to_name($i)); ?>
                    </option>
            <?php 
                endfor;
            ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">
        <?= _("Filter"); ?>
    </button>
    <button type="reset" class="btn btn-success" id="filter-form-admin">
        <?= _("Reset"); ?>
    </button>
</form>