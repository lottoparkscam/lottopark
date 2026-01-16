<form class="form-inline form-filter" method="get" action="/multidraw_tickets">
    <label>
        <?= _("Filter"); ?>:
    </label>
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