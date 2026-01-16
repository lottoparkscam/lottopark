<?php 
    include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Currencies available on the site"); ?>
        </h2>
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        <div class="pull-right">
            <a href="<?= $urls["new"]; ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
        </div>
        <div class="btn-group" role="group">
            <a href="<?= $urls["currency"]; ?>" class="btn btn-default active">
                <?= _("Available currencies"); ?>
            </a>
            <a href="<?= $urls["country_currency"]; ?>" class="btn btn-default">
                <?= _("Defaults for countries"); ?>
            </a>
        </div>
        
        <div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/admin/shared/messages.php");

                if (!empty($available_currencies) && count($available_currencies) > 0):
            ?>
                    <label class="control-label">
                        <?= _("List of available currencies"); ?>
                    </label>

                    <table class="table table-bordered table-hover table-striped table-sort">

                        <thead>
                            <tr>
                                <th>
                                    <?= _("Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Default for site?"); ?>
                                </th>
                                <th>
                                    <?= _("Deposits in boxes"); ?>
                                </th>
                                <th>
                                    <?= _("Minimum Payment"); ?><br>
                                    <?= _("by Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Minimum Deposit"); ?><br>
                                    <?= _("by Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Minimum Withdrawal"); ?><br>
                                    <?= _("by Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Maximum Order Amount"); ?><br>
                                    <?= _("by Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Maximum Deposit Amount"); ?><br>
                                    <?= _("by Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Action"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($available_currencies as $available_currency):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $available_currency['currency_code']; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency['is_default_for_site_sign']; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency["first_box_value"]; ?>
                                            <br>
                                            <?= $available_currency["second_box_value"]; ?>
                                            <br>
                                            <?= $available_currency["third_box_value"]; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency['min_purchase_amount']; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency['min_deposit_amount']; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency['min_withdrawal']; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency['max_order_amount']; ?>
                                        </td>
                                        <td>
                                            <?= $available_currency['max_deposit_amount']; ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-success" 
                                               href="<?= $available_currency["edit_url"]; ?>">
                                                <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                            </a>
                                            <?php 
                                                if ($show_delete_button &&
                                                    !$available_currency['is_default_for_site']
                                                ):
                                            ?>
                                                    <button type="button" 
                                                            data-href="<?= $available_currency["delete_url"]; ?>" 
                                                            class="btn btn-xs btn-danger" 
                                                            data-toggle="modal" 
                                                            data-target="#confirmModal" 
                                                            data-confirm="<?= $available_currency["delete_text"]; ?>">
                                                        <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                                    </button>
                                            <?php 
                                                endif;
                                            ?>
                                        </td>
                                    </tr>
                            <?php
                                endforeach;
                            ?>
                        </tbody>
                    </table>
            <?php
                else:
            ?>
                    <p class="text-info">
                        <?= _("No currency definitions."); ?>
                    </p>
            <?php 
                endif;
            ?>
        </div>
    </div>
    
</div>

<!-- Modals -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <?= _("Confirm"); ?>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmOK" class="btn btn-success">
                    <?= _("OK"); ?>
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= _("Cancel"); ?>
                </button>
            </div>
        </div>
    </div>
</div>