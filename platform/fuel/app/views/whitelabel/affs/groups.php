<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");

?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="/affs/lottery-groups/new" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
        </div>
        <h2>
            <?= _("Affiliate lottery groups"); ?>
        </h2>
        <p class="help-block">
            <?= _("You can manage affiliate lottery groups here."); ?>
        </p>
        <div class="container-fluid container-admin">
            <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");

            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                    <thead>
                        <tr>
                            <th>
                                <?= _("ID"); ?>
                            </th>
                            <th>
                                <?= _("Name"); ?>
                            </th>
                            <th>
                                <?= _("Commissions"); ?>
                            </th>
                            <th class="text-center">
                                <?= _("Manage"); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0</td>
                            <td><?= _("Default Group"); ?></td>
                            <td>
                                <?php
                                if (!empty($default_group_data['commission_value_manager'])) :
                                ?>
                                    <div>
                                        <?= $default_group_data['commission_value_manager']; ?>
                                    </div>
                                <?php
                                endif;

                                if (!empty($default_group_data['commission_value_2_manager'])) :
                                ?>
                                    <div>
                                        <?= $default_group_data['commission_value_2_manager']; ?>
                                    </div>
                                <?php
                                endif;

                                if (!empty($default_group_data['ftp_commission_value_manager'])) :
                                ?>
                                    <div>
                                        <?= $default_group_data['ftp_commission_value_manager']; ?>
                                    </div>
                                <?php
                                endif;

                                if (!empty($default_group_data['ftp_commission_value_2_manager'])) :
                                ?>
                                    <div>
                                        <?= $default_group_data['ftp_commission_value_2_manager']; ?>
                                    </div>
                                <?php
                                endif;
                                ?>
                            </td>
                            <td class="text-center">
                                <a href="/affs/lottery-groups/edit/default" class="btn btn-xs btn-success">
                                    <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                </a>
                            </td>
                        </tr>
                        <?php
                        foreach ($other_groups_data as $other_group_data) :
                        ?>
                            <tr>
                                <td>
                                    <?= $other_group_data['index']; ?>
                                </td>
                                <td>
                                    <?= $other_group_data['name']; ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($other_group_data['commission_value_manager'])) :
                                    ?>
                                        <div>
                                            <?= $other_group_data['commission_value_manager']; ?>
                                        </div>
                                    <?php
                                    endif;

                                    if (!empty($other_group_data['commission_value_2_manager'])) :
                                    ?>
                                        <div>
                                            <?= $other_group_data['commission_value_2_manager']; ?>
                                        </div>
                                    <?php
                                    endif;

                                    if (!empty($other_group_data['ftp_commission_value_manager'])) :
                                    ?>
                                        <div>
                                            <?= $other_group_data['ftp_commission_value_manager']; ?>
                                        </div>
                                    <?php
                                    endif;

                                    if (!empty($other_group_data['ftp_commission_value_2_manager'])) :
                                    ?>
                                        <div>
                                            <?= $other_group_data['ftp_commission_value_2_manager']; ?>
                                        </div>
                                    <?php
                                    endif;
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= $other_group_data['edit_url']; ?>" class="btn btn-xs btn-success">
                                        <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                    </a>
                                    <button type="button" data-href="<?= $other_group_data['delete_url']; ?>" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#confirmModal" data-confirm="<?= _("Are you sure?"); ?>">
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
        </div>
    </div>
</div>
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