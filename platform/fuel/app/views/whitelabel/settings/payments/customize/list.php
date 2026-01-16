<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= $title; ?>
        </h2>
        
        <p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        
        <?php
            if (!$show_new_button):
        ?>
                <div class="alert alert-info" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <?= _("Customization for all available languages already added."); ?>
                </div>
        <?php
            endif;
        ?>
        
        <a href="<?= $start_url; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <?php
            if ($show_new_button):
        ?>
                <div class="pull-right">
                    <a href="<?= $new_url; ?>" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
                    </a>
                </div>
        <?php
            endif;
        ?>
        
        <div class="container-fluid container-admin">
        <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");
            
            if (isset($payment_method_customize) && count($payment_method_customize) > 0):
                //echo $pages;
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("Language"); ?>
                                </th>
                                <th>
                                    <?= _("Title"); ?>
                                </th>
                                <th>
                                    <?= _("Title for mobile"); ?>
                                </th>
                                <th>
                                    <?= _("Title in description area"); ?>
                                </th>
                                <th>
                                    <?= _("Description"); ?>
                                </th>
                                <th>
                                    <?= _("Additional text on failure page"); ?>
                                </th>
                                <th>
                                    <?= _("Additional text on success page"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Manage"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($payment_method_customize as $customize):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $customize['language']; ?>
                                        </td>
                                        <td>
                                            <?= $customize['title']; ?>
                                        </td>
                                        <td>
                                            <?= $customize['title_for_mobile']; ?>
                                        </td>
                                        <td>
                                            <?= $customize['title_in_description']; ?>
                                        </td>
                                        <td>
                                            <?= $customize['description']; ?>
                                        </td>
                                        <td>
                                            <?= $customize['additional_failure_text']; ?>
                                        </td>
                                        <td>
                                            <?= $customize['additional_success_text']; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $customize['edit_url']; ?>" 
                                               class="btn btn-xs btn-success">
                                                <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                            </a>
                                            <button type="button" 
                                                    data-href="<?= $customize['delete_url']; ?>" 
                                                    class="btn btn-xs btn-danger" 
                                                    data-toggle="modal" 
                                                    data-target="#confirmModal" 
                                                    data-confirm="<?= $customize['delete_text']; ?>">
                                                <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                            </button>
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
                <p class="text-info"><?= _("No payment methods customization defined."); ?></p>
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