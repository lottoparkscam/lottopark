<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Lottery settings"); ?>
        </h2>
        
        <p class="help-block">
            <?= _("You can change lottery settings here."); ?>
        </p>
        
        <div class="container-fluid container-admin">
            <?php
                include(APPPATH . "views/whitelabel/shared/messages.php");

                if (isset($lotteries) && count($lotteries) > 0):
                //echo $pages;
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("Name"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Enabled"); ?>
                                    </th>
                                    <?php /*
                                        <th><?= _("Provider"); ?></th> */
                                    ?>
                                    <th class="text-left">
                                        <?= _("Line Price"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Final Income"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Royalties"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Minimum Lines"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Quick-Pick Lines"); ?>
                                    </th>
                                    <th>
                                        <?= _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($lotteries as $lottery_data):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $lottery_data['name']; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="<?= $lottery_data['wis_enabled_class']; ?>">
                                                    <?= $lottery_data['wis_enabled_value']; ?>
                                                </span>
                                                <?php
                                                    if ($lottery_data['is_temporarily_disabled'] == 1):
                                                ?>
                                                        <br><span class="text-danger"><?= _("Temporarily disabled"); ?></span>
                                                <?php
                                                    endif;
                                                ?>
                                                <?php
                                                    if ($lottery_data['playable'] != 1):
                                                ?>
                                                        <br><span class="text-danger"><?= _("Not playable"); ?></span>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <?php
                                                /*
                                                <td><?= _($providers[$item['provider']]); ?></td> */
                                            ?>
                                            <td class="text-left">
                                                <?= $lottery_data['model_text']; ?>
                                                <br>
                                                <?= $lottery_data['expected_income_text']; ?>
                                                <?php
                                                    echo $lottery_data['expected_income'];

                                                    if (!empty($lottery_data['expected_income_lottery'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
                                                                    echo $lottery_data['expected_income_lottery'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                                <br>
                                                <?= $lottery_data['insured_tiers']; ?>
                                                <?= $lottery_data['current_cost_text']; ?>
                                                <?php
                                                    echo $lottery_data['current_cost'];

                                                    if (!empty($lottery_data['current_cost_lottery'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
                                                                    echo $lottery_data['current_cost_lottery'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                                <br>
                                                <?= $lottery_data['current_price_text']; ?>
                                                <?php
                                                    echo $lottery_data['current_price'];

                                                    if (!empty($lottery_data['current_price_lottery'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
                                                                    echo $lottery_data['current_price_lottery'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    echo $lottery_data['final_income'];

                                                    if (!empty($lottery_data['final_income_lottery'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
                                                                    echo $lottery_data['final_income_lottery'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                                <?= $lottery_data['asterix']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    echo $lottery_data['margin'];

                                                    if (!empty($lottery_data['margin_lottery'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?php
                                                                    echo $lottery_data['margin_lottery'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $lottery_data['min_lines']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $lottery_data['quick_pick_lines']; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= $lottery_data['edit_url']; ?>" 
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
                    if ($show_asterisk):
            ?>
                        <p class="help-block">
                            <?= _("* subject to change depending on current jackpot and volume"); ?>
                        </p>
            <?php
                    endif;
                
                //echo $pages;
                else:
            ?>
                    <p class="text-info"><?= _("There are no lotteries."); ?></p>
            <?php
                endif;
            ?>
        </div>
    </div>
</div>