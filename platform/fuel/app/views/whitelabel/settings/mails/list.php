<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Mails settings"); ?>
        </h2>

        <p class="help-block">
            <?= _("You can change email templates here."); ?>
        </p>

        <div class="container-fluid container-admin">
            <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");
            
            if (isset($mails) && count($mails) > 0):
                //echo $pages;
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                        <tr>
                            <th>
                                <?= _("Slug"); ?>
                            </th>
                            <th class="text-center">
                                <?= _("Title"); ?>
                            </th>
                            <th>
                                <?= _("Manage"); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($mails as $mail):
                            ?>
                            <tr>
                                <td>
                                    <?=$mail['slug'];?>
                                </td>

                                <td class="text-center">
                                    <?=$mail['original_title'];?>
                                </td>
                                
                                <td class="text-center">
                                    <a href="/mailsettings/edit/<?= $mail['slug']; ?>"
                                       class="btn btn-xs btn-success">
                                        <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
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
                <p class="text-info"><?= _("There is no email template available to edit."); ?></p>
            <?php
            endif;
            ?>
        </div>
    </div>
</div>