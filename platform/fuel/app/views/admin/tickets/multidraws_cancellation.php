<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
        include(APPPATH . "views/admin/tickets/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Multidraws Tickets Cancellation"); ?>
        </h2>
        <p class="help-block">
            <?= _("You can cancel multiple multidraws here"); ?>
        </p>

        <a href="/" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <?php
            include(APPPATH."views/whitelabel/shared/messages.php");
            ?>
            <div class="col-md-3">
                <form method="get" action="/multidraw_tickets/details">
                    <div class="form-group">
                        <label class="control-label" for="inputLottery">
                            <?= _("Lottery"); ?>:
                        </label>
                        <select autofocus required name="lottery" id="inputLottery" class="form-control">
                            <?php
                            foreach ($lotteries['__by_id'] as $key => $lottery):
                                ?>
                                <option value="<?= $lottery['id']; ?>">
                                    <?= Security::htmlentities($lottery['name']); ?>
                                </option>
                            <?php
                            endforeach;
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="filterRangeStart">
                            <?= _("Range from"); ?>:
                        </label>
                        <div class="input-group input-daterange input-daterange-small datepicker"
                             data-date-end-date="0d" style="width:100% !important;">
                            <input type="text"
                                   id="filterRangeEnd"
                                   name="range_from"
                                   autocomplete="off"
                                   class="form-control"
                                   value="">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>

                    <button type="reset" class="btn btn-success" id="filter-form-admin">
                        <?= _("Reset"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _("Are you sure?"); ?></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmOK" class="btn btn-success"><?= _("OK"); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>
