<form class="form-inline form-filter" method="get" action="/tickets">
    <label>
        <?= _("Filter"); ?>: 
    </label>
    <div class="form-group">
        <select name="filter[lottery]" 
                id="filterMethod" 
                class="form-control filterSelect">
            <option value="a">
                <?= _("Lottery"); ?>
            </option>
            <?php
                foreach ($lotteries['__by_id'] as $lottery):
                    $is_selected = "";
                    if (Input::get("filter.lottery") == $lottery['id']) {
                        $is_selected = ' selected="selected"';
                    }
                    $lottery_name = Security::htmlentities(_($lottery['name']));
            ?>
                    <option value="<?= $lottery['id']; ?>" <?= $is_selected; ?>>
                        <?= $lottery_name; ?>
                    </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
    <div class="form-group">
        <select name="filter[status]" 
                id="filterStatus" 
                class="form-control filterSelect">
            <option value="a">
                <?= _("Status"); ?>
            </option>
            <?php
                foreach ($ticket_statuses as $status_key => $ticket_status):
                    $is_selected = "";
                    if (Input::get("filter.status") == (string) $status_key) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $status_key; ?>" <?= $is_selected; ?>>
                        <?= $ticket_status; ?>
                    </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
    
    <div class="form-group">
        <select name="filter[payout]" 
                id="filterPayout" 
                class="form-control filterSelect">
            <option value="a">
                <?= _("Paid out"); ?>
            </option>
            <?php
                foreach ($ticket_payouts as $payout_key => $ticket_payout):
                    $is_selected = "";
                    if (Input::get("filter.payout") == (string) $payout_key) {
                        $is_selected = ' selected="selected"';
                    }
            ?>
                    <option value="<?= $payout_key; ?>" <?= $is_selected; ?>>
                        <?= $ticket_payout; ?>
                    </option>
            <?php
                endforeach;
            ?>
        </select>
    </div>
    <div class="form-group">
        <input type="text" 
               id="filterDrawDate" 
               value="<?= $filters_data['date']; ?>" 
               class="form-control datepicker filterInput" 
               data-date-end-date="0d" 
               data-date-clear-btn="true" 
               data-date-week-start="<?= $filters_data['first_day_of_week']; ?>" 
               name="filter[date]" 
               placeholder="<?= _("Draw Date"); ?>">
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['ticket_id']; ?>" 
               class="form-control filterInput" 
               id="filterId" 
               name="filter[id]" 
               placeholder="<?= _("ID"); ?>">
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['transaction_id']; ?>" 
               class="form-control filterInput" 
               id="filterTransactionId" 
               name="filter[transactionid]" 
               placeholder="<?= _("Transaction ID"); ?>">
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['user_id']; ?>" 
               class="form-control filterInput" 
               id="filterUserId" 
               name="filter[userid]" 
               placeholder="<?= _("User ID"); ?>">
    </div>
    <div class="form-group">
        <input type="text"
               value="<?= $filters_data['multidraw_id']; ?>"
               class="form-control filterInput"
               id="filterMultiDrawId"
               name="filter[multidrawid]"
               placeholder="<?= _("Multi-draw ID"); ?>">
    </div>
    <div class="form-group">
        <input type="text" 
               value="<?= $filters_data['email']; ?>" 
               class="form-control filterInput" 
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
                       class="form-control filterInput" 
                       value="<?= $filters_data['range_start']; ?>">
                <span class="input-group-addon">
                    <?php echo _("to"); ?>
                </span>
                <input type="text" 
                       name="filter[range_end]" 
                       autocomplete="off" 
                       class="form-control filterInput" 
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