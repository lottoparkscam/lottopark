<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Mail settings"); ?>
        </h2>

        <div class="container-fluid container-admin">
            <p class="help-block">
                <?= sprintf(_('You are about to edit "%s" email template! Choose the language that you would like to edit.'), '<b>'.$mail['title'].'</b>'); ?>
            </p>
            
            <?php
            include(APPPATH . "views/whitelabel/shared/messages.php");

            if (isset($languages) && count($languages) > 0):
                //echo $pages;
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <tbody>
                        <?php
                        $cols = 4;
                        $counter = 1;
                        foreach ($languages as $lang):
                            if (($counter % $cols) == 1) {    // Check if it's new row
                                echo '<tr>';
                            }
                            ?>
                                <td><a href="/mailsettings/edit/<?= $mail['slug']; ?>/<?=$lang['code'];?>"><?=$lang['text']; ?></a></a></td>
                        <?php
                            if (($counter % $cols) == 0) { // If it's last column in each row then counter remainder will be zero
                                echo '</tr>';
                            }
                            $counter++;
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