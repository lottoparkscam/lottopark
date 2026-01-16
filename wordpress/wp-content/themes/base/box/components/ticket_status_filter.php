<div class="myaccount-filter myaccount-filter-float-left" style="padding-bottom: 1.5rem;">
    <form method="get" action=".">
        <label for="myaccount-filter-select" class="table-sort-label hidden-normal">
            <?php echo Security::htmlentities(_("Show")); ?>:
        </label>
        <select id="myaccount-filter-select" class="myaccount-filter-select myaccount-filter-select-float-left" name="status">
            <option value="a"<?php if ($status == "a" || $status === null): echo ' selected="selected"'; endif; ?>>
                <?php echo Security::htmlentities(_("show all")); ?>
            </option>
            <option value="<?= Helpers_General::TICKET_STATUS_WIN ?>"<?php if ($status === Helpers_General::TICKET_STATUS_WIN): echo ' selected="selected"'; endif; ?>>
                <?php echo Security::htmlentities(_("show win")); ?>
            </option>
            <option value="<?= Helpers_General::TICKET_STATUS_NO_WINNINGS ?>"<?php if ($status === Helpers_General::TICKET_STATUS_NO_WINNINGS): echo ' selected="selected"'; endif; ?>>
                <?php echo Security::htmlentities(_("show no winnings")); ?>
            </option>
        </select>
    </form>
</div>
