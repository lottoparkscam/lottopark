<form class="form-inline form-filter" method="get" action="/withdrawals">
    <label>
        <?= _("Filter"); ?>: 
    </label>
    <div class="form-group">
        <select name="filter[method]" id="filterMethod" class="form-control">
            <option value="a">
                <?= _("Method"); ?>
            </option>
            <?php 
                $i = 0;
                foreach ($methods as $method):
                    $i++;
                    $is_selected = "";
                    if (Input::get("filter.method") == $i) {
                        $is_selected = ' selected="selected"';
                    }
                    $value_to_show_t = $method['name'];
                    $value_to_show = Security::htmlentities($value_to_show_t);
            ?>
                    <option value="<?= $i; ?>"<?= $is_selected; ?>>
                        <?= $value_to_show; ?>
                    </option>
            <?php 
                endforeach;
            ?>
        </select>
    </div>
    <div class="form-group">
        <select name="filter[status]" id="filterStatus" class="form-control">
            <option value="a">
                <?= _("Status"); ?>
            </option>
            <?php
                foreach ($withdrawals_statuses as $status_key => $withdrawal_status):
                    $is_selected = "";
                    if (Input::get("filter.status") == (string) $status_key) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $status_key; ?>" <?= $is_selected; ?>>
                        <?= $withdrawal_status; ?>
                    </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['withdrawal_request_token']; ?>" 
               class="form-control" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID"); ?>">
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['user_id']; ?>" 
               class="form-control" 
               id="filterUserId" 
               name="filter[userid]" 
               placeholder="<?= _("User ID"); ?>">
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['email']; ?>" 
               class="form-control" 
               id="filterEmail" 
               name="filter[email]" 
               placeholder="<?= _("User E-mail"); ?>">
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
                       class="form-control" 
                       value="<?= $filters_data['range_start']; ?>">
                <span class="input-group-addon">
                    <?php echo _("to"); ?>
                </span>
                <input type="text" 
                       name="filter[range_end]" 
                       autocomplete="off" 
                       class="form-control" 
                       value="<?= $filters_data['range_end']; ?>">
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