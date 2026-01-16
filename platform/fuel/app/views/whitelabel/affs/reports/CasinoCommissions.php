<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Casino Commissions"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("Here you can view the latest commissions of your casino affiliates."); ?>
        </p>
        
        <?php 
            include(APPPATH . "views/whitelabel/affs/reports/CasinoCommissionsFilters.php");
?>
        
        <div class="container-fluid container-admin">
            <?php
        include(APPPATH . "views/aff/shared/messages.php");

if (count($casinoCommissions ?? []) > 0):
    echo $pages;
    ?>
                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th>
                                        <?= _("User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Type") ?>
                                    </th>
                                   
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <th>
                                        <?= _('GGR') ?>
                                    </th>

                                    <th>
                                        <?= _("Commission (NGR)"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($casinoCommissions as $commission): ?>
                                <tr>
                                    <td>
                                        <?= $commission['aff_full_name']; ?>
                                        <br>
                                        <?= $commission['aff_is_confirmed']; ?> 
                                        <?= $commission['aff_email']; ?>
                                        <br>
                                        <a href="<?= $commission['aff_url']; ?>" 
                                            class="btn btn-xs btn-primary">
                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= $commission['lead_full_name']; ?>
                                        <br>
                                        <?= $commission['lead_is_confirmed']; ?> 
                                        <?= $commission['lead_email']; ?>
                                        <?php 
                                            if (!in_array($rparam, ["deleted", "inactive"])):
                                        ?>
                                                <br>
                                                <a href="<?= $commission['user_url']; ?>" 
                                                    class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                </a>
                                                <br>
                                                <a href="<?= $commission['tickets_url']; ?>" 
                                                    class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                </a>
                                        <?php 
                                            endif;
                                        ?>
                                    </td>
                                    <td>
                                        <?= _('Sale') ?><br>
                                        <?= _('Tier: ') ?><?= $commission['tier']; ?>
                                    </td>

                                          
                                            <td class="text-center">
                                                <?= $commission['created_at']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['show_ggr'] ? $commission['ggr'] :'N\A'; ?>
                                            <?php
                                            
                                    if (!empty($commission['ggrInUserCurrency'])):
                                        ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
                                                            echo $commission['ggrInUserCurrency'];
                                        ?>"></span>
                                                        </small>
                                                <?php
                                    endif;
                                    ?>
                                            </td>
                                            <td>
                                                <?php
                                            if (!empty($commission['show_commission'])):
                                                echo $commission['commission'];

                                                if (!empty($commission['commissions_other'])):
                                                    ?>
                                                                    <small>
                                                                        <span class="glyphicon glyphicon-info-sign" 
                                                                              data-toggle="tooltip" 
                                                                              data-placement="top" 
                                                                              title="" 
                                                                              data-original-title="<?php
                                                                        echo $commission['commissions_other'];
                                                    ?>"></span>
                                                                    </small>
                                                            <?php
                                                endif;
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
                    echo $pages;
else:
    ?>
                    <p class="text-info">
                        <?= _("No casino commissions."); ?>
                    </p>
            <?php
endif;
?>
        </div>
    </div>
</div>
