<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php
            include(APPPATH . "views/whitelabel/bonuses/menu.php");
        ?>
    </div>
    <div class="col-md-10">
        <div class="pull-right">
            <a href="/bonuses/promocodes/new" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
        </div>
        <h2>
            <?= _("Promo Codes"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can manage promo codes bonus here."); ?>
        </p>

        <div class="container-fluid container-admin">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");
            ?>

<?php
                if (isset($campaigns) && count($campaigns) > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                    <thead>
                        <tr>
                            <th>
                                <?= _("Prefix"); ?>
                            </th>
                            <th>
                                <?= _("Type"); ?>
                            </th>
                            <th>
                                <?= _("Bonus type"); ?>
                            </th>
                            <th>
                                <?= _("Affiliate"); ?>
                            </th>
                            <th>
                                <?= _("Lottery"); ?>
                            </th>
                            <th>
                                <?= _("Discount amount"); ?>
                            </th>
                            <th>
                                <?= _("Bonus money amount"); ?>
                            </th>
                            <th>
                                <?= _("Start date"); ?>
                            </th>
                            <th>
                                <?= _("End date"); ?>
                            </th>
                            <th>
                                <?= _("Active"); ?>
                            </th>
                            <th>
                                <?= _("Codes used"); ?>
                            </th>
                            <th>
                                <?= _("Times used"); ?>
                            </th>
                            <th>
                                <?= _("Manage"); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($campaigns as $item):
                        ?>
                            <tr>
                                <td>
                                    <?= $item['prefix']; ?>
                                </td>
                                <td>
                                    <?= $item['type']; ?>
                                </td>
                                <td>
                                    <?= $item['bonus_type']; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php
                                        if (!empty($item['whitelabel_aff_id'])):
                                            if (!empty($item['aff_name']) ||
                                                !empty($item['aff_surname'])
                                            ):
                                                echo Security::htmlentities($item['aff_name'] . ' ' . $item['aff_surname']);
                                            else:
                                                echo _("anonymous");
                                            endif;
                                            echo ' &bull; ';
                                            echo Security::htmlentities($item['aff_login']);
                                        else:
                                            echo('-');
                                        endif;
                                    ?>                                                
                                </td>
                                <td>
                                    <?php
                                        if (!empty($item['lottery_name'])):
                                            echo Security::htmlentities($item['lottery_name']);
                                        else:
                                            echo('-');
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if (!empty($item['discount_amount'])):
                                            echo Security::htmlentities($item['discount_amount']);
                                        else:
                                            echo('-');
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if (!empty($item['bonus_balance_amount'])):
                                            echo Security::htmlentities($item['bonus_balance_amount']);
                                        else:
                                            echo('-');
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if (!empty($item['date_start'])):
                                            echo Security::htmlentities($item['date_start']);
                                        else:
                                            echo('-');
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if (!empty($item['date_end'])):
                                            echo Security::htmlentities($item['date_end']);
                                        else:
                                            echo('-');
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if ($item['is_active'] == 1):
                                            echo _('Yes');
                                        else:
                                            echo _('No');
                                        endif;
                                    ?>
                                </td>
                                <td>
                                    <?= $item['used']; ?>
                                </td>
                                <td>
                                    <?= $item['used_times'];?>
                                </td>
                                <td>                                    
                                    <a href=<?= $item['action_url'] . "/s/users" ?> 
                                        class="btn btn-xs btn-primary">
                                        <span class="glyphicon glyphicon-user"></span> 
                                        <?= _("View users"); ?>
                                    </a> 
                                    <a href=<?= $item['action_url'] . "/s/edit" ?> 
                                        class="btn btn-xs btn-primary">
                                        <span class="glyphicon glyphicon-edit"></span> 
                                        <?= _("Edit"); ?>
                                    </a> 
                                    <?php
                                        if ($item['codes_count'] > 1):
                                    ?>
                                    <a href=<?= $item['action_url'] . "/s/codes" ?> 
                                        class="btn btn-xs btn-primary">
                                        <span class="glyphicon glyphicon-list"></span> 
                                        <?= _("View codes"); ?>
                                    </a>
                                    <?php
                                        endif;
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
                else:
            ?>
                    <p class="text-info"><?= _("No campaigns."); ?></p>
            <?php
                endif;
            ?>
        </div>
    </div>
</div>
