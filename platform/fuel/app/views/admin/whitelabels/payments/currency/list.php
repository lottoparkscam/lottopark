<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $title; ?>
        </h2>
        
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        
        <a href="<?= $urls['back']; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="pull-right">
            <a href="<?= $urls['new']; ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
        </div>
        
        <div class="container-fluid container-admin">
        <?php
            include(APPPATH . "views/admin/shared/messages.php");
            
            if (isset($payment_method_currencies) && count($payment_method_currencies) > 0):
                //echo $pages;
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("Currency"); ?>
                                </th>
                                <th>
                                    <?= _("Is default"); ?>
                                </th>
                                <th>
                                    <?= _("Minimum purchase"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Manage"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($payment_method_currencies as $currency):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $currency['code']; ?>
                                        </td>
                                        <td>
                                            <?= $currency['is_default_show_icon']; ?>
                                        </td>
                                        <td>
                                            <?= $currency['min_purchase']; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $currency['edit_url']; ?>" 
                                               class="btn btn-xs btn-success">
                                                <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                            </a>
                                            <?php
                                                if (!$currency['is_default']):
                                            ?>
                                                    <button type="button" 
                                                            data-href="<?= $currency['delete_url']; ?>" 
                                                            class="btn btn-xs btn-danger" 
                                                            data-toggle="modal" 
                                                            data-target="#confirmModal" 
                                                            data-confirm="<?= $currency['delete_text']; ?>">
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
                </div>
        <?php
                //echo $pages;
            else:
        ?>
                <p class="text-info"><?= _("No payment methods currencies."); ?></p>
        <?php
            endif;
        ?>
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
                <h4 class="modal-title"><?= _("Confirm"); ?></h4>
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