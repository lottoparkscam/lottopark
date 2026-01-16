<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Multi-draws settings"); ?>
        </h2>

        <div class="pull-right">
            <a href="/multidrawsettings/new" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New Option"); ?>
            </a>
        </div>

        <p class="help-block">
            <?= _("You can add multi-draw options here."); ?>
        </p>

        <div class="container-fluid container-admin">
            <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");

            if (isset($multi_draws_options) && count($multi_draws_options) > 0):
                //echo $pages;
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                        <tr>
                            <th>
                                <?= _("Number of draws"); ?>
                            </th>
                            <th class="text-center">
                                <?= _("Discount"); ?>
                            </th>
                            <th>
                                <?= _("Manage"); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($multi_draws_options as $option):
                            ?>
                            <tr>
                                <td>
                                    <?=$option['tickets'];?>
                                </td>

                                <td class="text-center">
                                    <?=$option['discount'] ? $option['discount'].'%' :  _("no discount");?>
                                </td>

                                <td class="text-center">
                                    <a href="/multidrawsettings/edit/<?= $option['id']; ?>"
                                       class="btn btn-xs btn-success">
                                        <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                    </a>

                                    <a href="/multidrawsettings/delete/<?= $option['id']; ?>"
                                       onclick="return confirm('Are you sure?')"
                                       class="btn btn-xs btn-danger">
                                        <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
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
                <p class="text-info"><?= _("There is no multi-draw options available to edit."); ?></p>
            <?php
            endif;
            ?>
        </div>
    </div>
</div>