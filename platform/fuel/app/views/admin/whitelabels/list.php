<?php 
    include(APPPATH."views/admin/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php 
            include(APPPATH."views/admin/whitelabels/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="/whitelabels/new" class="btn btn-success btn-sm">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add new"); ?>
            </a>
        </div>
        
        <h2>
            <?= _("Whitelabels"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("This is an overview of your whitelabels."); ?>
        </p>
        
        <div class="container-fluid container-admin">
            <?php
                include(APPPATH."views/admin/shared/messages.php");

                if ($whitelabels !== null && count($whitelabels) > 0):
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= _("Name"); ?></th>
                                    <th><?= _("Domain"); ?></th>
                                    <th><?= _("Type"); ?></th>
                                    <th><?= _("Prepaid"); ?></th>
                                    <th><?= _("Prepaid alert limit"); ?></th>
                                    <th><?= _("Royaltees"); ?></th>
                                    <th><?= _("Last login"); ?></th>
                                    <th><?= _("Last active"); ?></th>
                                    <th><?= _("Manage"); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($whitelabels as $whitelabel):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $whitelabel['name']; ?>
                                            </td>
                                            <td>
                                                <?= $whitelabel['domain']; ?>
                                            </td>
                                            <td>
                                                <?= $whitelabel['type']; ?>
                                            </td>
                                            <td class="<?= $whitelabel['prepaid_class_alert'] ?>">
                                                <?= $whitelabel['prepaid_text']; ?>
                                            </td>
                                            <td class="<?= $whitelabel['prepaid_class_alert'] ?>">
                                                <?= $whitelabel['prepaid_alert_limit_text']; ?>
                                            </td>
                                            <td>
                                                <?= $whitelabel['margin']; ?>
                                            </td>
                                            <td>
                                                <?= $whitelabel['last_login']; ?>
                                            </td>
                                            <td>
                                                <?= $whitelabel['last_active']; ?>
                                            </td>
                                            <td>
                                                <a href="<?= $whitelabel['edit_url']; ?>" 
                                                   class="btn btn-xs btn-success">
                                                    <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                                </a>
                                                <a href="<?= $whitelabel['languages_url']; ?>" 
                                                   class="btn btn-xs btn-success">
                                                    <span class="glyphicon glyphicon-font"></span> <?= _("Languages"); ?>
                                                </a>
                                                <a href="<?= $whitelabel['payments_url']; ?>" 
                                                   class="btn btn-xs btn-success">
                                                    <span class="glyphicon glyphicon-usd"></span> <?= _("Payment methods"); ?>
                                                </a>
                                                <?php 
                                                    if ($whitelabel['show_prepaid_button']):
                                                ?>
                                                        <a href="<?= $whitelabel['prepaid_url']; ?>" 
                                                           class="btn btn-xs btn-success">
                                                            <span class="glyphicon glyphicon-piggy-bank"></span> <?= _("Prepaid"); ?>
                                                        </a>
                                                <?php 
                                                    endif;
                                                ?>
                                                <a href="<?= $whitelabel['settings_url']; ?>" 
                                                   class="btn btn-xs btn-success">
                                                    <span class="glyphicon glyphicon-cog"></span> <?= _("Settings"); ?>
                                                </a>
                                                <a href="<?= $whitelabel['currencies_url']; ?>" 
                                                   class="btn btn-xs btn-success">
                                                    <span class="glyphicon glyphicon-usd"></span> <?= _("Currency settings"); ?>
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
            <?php 
                else:
            ?>
                    <p class="text-info"><?= _("There are no whitelabels."); ?></p>
            <?php 
                endif;
            ?>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmswitchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <?= _("Confirm lottery switch off"); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?= $text_to_show_on_confirmation; ?>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmswitchA" class="btn btn-warning">
                    <?= _("Disable"); ?>
                </a>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?= _("Cancel"); ?>
                </button>
            </div>
        </div>
    </div>
</div>