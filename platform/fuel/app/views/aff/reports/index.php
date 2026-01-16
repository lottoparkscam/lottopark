<?php
$manager_currency_tab = Helpers_Currency::get_mtab_currency(
    false,
    null,
    $this->whitelabel['manager_site_currency_id']
);
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/aff/reports/menu.php");?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Generate report"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can generate a report for a given date range."); ?>
        </p>
		
        <?php
            include(APPPATH . "views/aff/reports/index_filters.php");
?>
        
		<div class="container-fluid container-admin">
            <?php
        include(APPPATH . "views/aff/shared/messages.php");

if (isset($date_start)):
    ?>
                    <h3>
                        <?= _("Report"); ?>
                    </h3>
                    <span class="details-label">
                        <?= _("Start Date"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_date_without_timezone($date_start); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("End Date"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_date_without_timezone($date_end); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("Clicks"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number($clicks['count_all']); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("Unique clicks"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number($clicks['count_unique']); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("Leads"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number(isset($regcount) ? count($regcount) : 0); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("First-time purchases"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number(isset($ftpcount) ? count($ftpcount) : 0); ?>
                    </span>
                    <br>

                    <span class="details-label"><?= _("First-time deposits"); ?>:</span>
                    <span class="details-value"><?= Lotto_View::format_number(!empty($ftdCount) ? count($ftdCount) : 0); ?></span>
                    <br>

                    <span class="details-label">
                    <?= _("Total lottery commissions"); ?>:
                    </span>
                    <span class="details-value">
                        <?php
                    echo Lotto_View::format_currency(
        $totalLotteryCommission,
        $manager_currency_tab['code'],
        true
    );
    ?>
                    </span>
                    <br>
                    <span class="details-label">
                    <?= _("Total casino commissions"); ?>:
                    </span>
                    <span class="details-value">
                        <?php
        echo Lotto_View::format_currency(
        $totalCasinoCommission,
        $manager_currency_tab['code'],
        true
    );
    ?>
                    </span>
                    <br>
                    <span class="details-label">
                    <?= _("Total commissions"); ?>:
                    </span>
                    <span class="details-value">
                        <?php
        echo Lotto_View::format_currency(
        $totalCommission,
        $manager_currency_tab['code'],
        true
    );
    ?>
                    </span>
                    <br>
            <?php
endif;

if (isset($regcount) && count($regcount) > 0):
    ?>
                    <h3>
                        <?= _("Leads"); ?>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("#"); ?>
                                    </th>
                                    <th>
                                        <?= _("Active"); ?>
                                    </th>
                                    <th>
                                        <?= _("Confirmed"); ?>
                                    </th>
                                    <th>
                                        <?= _("Expired"); ?>
                                    </th>
                                    <?php
                                if ($is_lead_id_visible):
                                    ?>
                                            <th>
                                                <?= _("ID"); ?>
                                            </th>
                                    <?php
                                endif;
                                        
    if ($user['is_show_name']):
        ?>
                                            <th>
                                                <?= _("Lead name") ?>
                                            </th>
                                    <?php
    endif;
                                        
    if ($user['is_show_name']):
        ?>
                                            <th>
                                                <?= _("Lead e-mail") ?>
                                            </th>
                                    <?php
    endif;
    ?>
                                    <th>
                                        <?= _("Register country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Last country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Medium"); ?>
                                    </th>
                                    <th>
                                        <?= _("Campaign"); ?>
                                    </th>
                                    <th>
                                        <?= _("Content"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Registered"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("First purchase"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
    foreach ($regcount as $key => $item):
        ?>
                                        <tr>
                                            <td>
                                                <?= $key+1; ?>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_active']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_active']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_expired']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_expired']); ?>
                                                </span>
                                            </td>
                                            <?php
                        if ($is_lead_id_visible):
                            ?>
                                                    <td>
                                                        <?= Security::htmlentities($whitelabel['prefix'].'U'.$item['token']); ?>
                                                    </td>
                                            <?php
                        endif;
                                                
        if ($user['is_show_name']):
            ?>
                                                    <td>
                                                        <?= $item['lead_name'] ?>
                                                    </td>
                                            <?php
        endif;

        if ($user['is_show_name']):
            ?>
                                                    <td>
                                                        <?= $item['lead_email'] ?>
                                                    </td>
                                            <?php
        endif;
        ?>
                                            <td>
                                                <?php
                if (!empty($item['register_country'])):
                    echo Security::htmlentities($countries[$item['register_country']] ?? '');
                endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['last_country'])):
                echo Security::htmlentities($countries[$item['last_country']] ?? '');
            endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['medium'])):
                echo Security::htmlentities($item['medium']);
            endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['campaign'])):
                echo Security::htmlentities($item['campaign']);
            endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['content'])):
                echo Security::htmlentities($item['content']);
            endif;
        ?>
                                            </td>
                                            <td class="text-center">
                                                <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($item['first_purchase']) ? Lotto_View::format_date($item['first_purchase'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                                            </td>
                                        </tr>
                                <?php
    endforeach;
    ?>
                            </tbody>
                        </table>
                    </div>
            <?php
endif;

if (isset($ftpcount) && count($ftpcount) > 0):
    ?>
                    <h3>
                        <?= _("First-time purchases"); ?>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("#"); ?>
                                    </th>
                                    <?php
                                if ($is_lead_id_visible):
                                    ?>
                                            <th>
                                                <?= _("ID"); ?>
                                            </th>
                                    <?php
                                endif;
                                        
    if ($user['is_show_name']):
        ?>
                                            <th>
                                                <?= _("Lead name") ?>
                                            </th>
                                    <?php
    endif;
                                        
    if ($user['is_show_name']):
        ?>
                                            <th>
                                                <?= _("Lead e-mail") ?>
                                            </th>
                                    <?php
    endif;
    ?>
                                    <th>
                                        <?= _("Register country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Last country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Medium"); ?>
                                    </th>
                                    <th>
                                        <?= _("Campaign"); ?>
                                    </th>
                                    <th>
                                        <?= _("Content"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Registered"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("First purchase"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
    foreach ($ftpcount as $key => $item):
        ?>
                                        <tr>
                                            <td>
                                                <?= $key+1; ?>
                                            </td>
                                            <?php
                        if ($is_lead_id_visible):
                            ?>
                                                    <td>
                                                        <?= Security::htmlentities($whitelabel['prefix'].'U'.$item['token']); ?>
                                                    </td>
                                            <?php
                        endif;
                                                
        if ($user['is_show_name']):
            ?>
                                                    <td>
                                                        <?= $item['lead_name']; ?>
                                                    </td>
                                            <?php
        endif;
                                                
        if ($user['is_show_name']):
            ?>
                                                    <td>
                                                        <?= $item['lead_email']; ?>
                                                    </td>
                                            <?php
        endif;
        ?>
                                            <td>
                                                <?php
                if (!empty($item['register_country'])):
                    echo Security::htmlentities($countries[$item['register_country']] ?? '');
                endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['last_country'])):
                echo Security::htmlentities($countries[$item['last_country']] ?? '');
            endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['medium'])):
                echo Security::htmlentities($item['medium']);
            endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['campaign'])):
                echo Security::htmlentities($item['campaign']);
            endif;
        ?>
                                            </td>
                                            <td>
                                                <?php
            if (!empty($item['content'])):
                echo Security::htmlentities($item['content']);
            endif;
        ?>
                                            </td>
                                            <td class="text-center">
                                                <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($item['first_purchase']) ? Lotto_View::format_date($item['first_purchase'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                                            </td>
                                        </tr>
                                <?php
    endforeach;
    ?>
                            </tbody>
                        </table>
                    </div>
            <?php
endif;

if (isset($ftdCount) && count($ftdCount) > 0):
?>
    <h3><?= _('First-time deposits') ?></h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sort">
            <thead>
                <tr>
                    <th>
                        <?= _("#"); ?>
                    </th>
                    <th>
                        <?= _("Affiliate"); ?>
                    </th>
                    <th>
                        <?= _("User"); ?>
                    </th>
                    <th>
                        <?= _("Country"); ?>
                    </th>
                    <th>
                        <?= _("Register country"); ?>
                    </th>
                    <th>
                        <?= _("Last country"); ?>
                    </th>
                    <th class="text-center">
                        <?= _("Registered"); ?>
                    </th>
                    <th class="text-center">
                        <?= _("First deposit"); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ftdCount as $key => $item):?>
                    <tr>
                        <td>
                            <?= $key+1; ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['aff_name']) || !empty($item['aff_surname'])):
                                echo Security::htmlentities($item['aff_name'] . ' ' . $item['aff_surname']);
                            else:
                                echo _("anonymous");
                            endif;
                            echo " &bull; ";
                            echo Security::htmlentities($item['aff_login']);
                            ?>
                            <br>
                            <span class="<?= Lotto_View::show_boolean_class($item['aff_is_confirmed']); ?>">
                                <?= Lotto_View::show_boolean($item['aff_is_confirmed']); ?>
                            </span> <?= Security::htmlentities($item['aff_email']); ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['name']) || !empty($item['surname'])):
                                echo Security::htmlentities($item['name'] . ' ' . $item['surname']);
                            else:
                                echo _("anonymous");
                            endif;
                            echo " &bull; ";
                            echo $whitelabel['prefix'] . 'U' . $item['token'];
                            ?>
                            <br>
                            <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                            </span> <?= Security::htmlentities($item['email']); ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['country'])):
                                echo Security::htmlentities($countries[$item['country']] ?? '');
                            endif;
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['register_country'])):
                                echo Security::htmlentities($countries[$item['register_country']] ?? '');
                            endif;
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($item['last_country'])):
                                echo Security::htmlentities($countries[$item['last_country']] ?? '');
                            endif;
                            ?>
                        </td>
                        <td class="text-center">
                            <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                        </td>
                        <td class="text-center">
                            <?= !empty($item['first_deposit']) ? Lotto_View::format_date($item['first_deposit'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
endif;

if (isset($commissions) && count($commissions) > 0):
    ?>
                    <h3>
                        <?= _("Commissions"); ?>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("#"); ?>
                                    </th>
                                    <?php
                                if ($is_lead_id_visible):
                                    ?>
                                            <th>
                                                <?= _("ID"); ?>
                                            </th>
                                    <?php
                                endif;
    
    if ($user['is_show_name']):
        ?>
                                            <th>
                                                <?= _("Lead name") ?>
                                            </th>
                                    <?php
    endif;
                                    
    if ($user['is_show_name']):
        ?>
                                            <th>
                                                <?= _("Lead e-mail") ?>
                                            </th>
                                    <?php
    endif;
                                        
    if ($is_transaction_id_visible):
        ?>
                                            <th>
                                                <?= _("Transaction ID"); ?>
                                            </th>
                                    <?php
    endif;
    ?>
                                    <th>
                                        <?= _("Medium"); ?>
                                    </th>
                                    <th>
                                        <?= _("Campaign"); ?>
                                    </th>
                                    <th>
                                        <?= _("Content"); ?>
                                    </th>
                                    <th>
                                        <?= _("Type"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <?php
        if ($is_amount_visible):
            ?>
                                            <th>
                                                <?= _("Amount"); ?>
                                            </th>
                                    <?php
        endif;
                                        
    if ($are_ticket_and_payment_cost_visible):
        ?>
                                            <th class="text-nowrap">
                                                <?= _("Ticket cost"); ?>
                                                <br>
                                                <?= _("Payment cost"); ?>
                                            </th>
                                    <?php
    endif;
                                        
    if ($is_income_visible):
        ?>
                                            <th>
                                                <?= _("Income"); ?>
                                            </th>
                                    <?php
    endif;
    ?>
                                    <th>
                                        <?= _("Commission"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
    foreach ($commissions as $key => $commission):
        ?>
                                        <tr>
                                            <td>
                                                <?= $key+1; ?>
                                            </td>
                                            <?php
                        if ($is_lead_id_visible):
                            ?>
                                                    <td>
                                                        <?= $commission['user_full_name']; ?>
                                                    </td>
                                            <?php
                        endif;
                                                
        if ($user['is_show_name']):
            ?>
                                                    <td>
                                                        <?= $commission['lead_name']; ?>
                                                    </td>
                                            <?php
        endif;
                                                
        if ($user['is_show_name']):
            ?>
                                                    <td>
                                                        <?= $commission['lead_email']; ?>
                                                    </td>
                                            <?php
        endif;
                                                
        if ($is_transaction_id_visible):
            ?>
                                                    <td>
                                                        <?= $commission['transaction_id']; ?>
                                                    </td>
                                            <?php
        endif;
        ?>
                                            <td>
                                                <?= $commission['medium']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['campaign']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['content']; ?>
                                            </td>
                                            <td>
                                                <?= $commission['aff_type']; ?>
                                                <br>
                                                <?= $commission['tier']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $commission['date_confirmed']; ?>
                                            </td>
                                            <?php
            if ($is_amount_visible):
                ?>
                                                    <td>
                                                        <?php
                                echo $commission['amount_manager'];

                if (!empty($commission['amounts_other'])):
                    ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php
                                        echo $commission['amounts_other'];
                    ?>"></span>
                                                                </small>
                                                        <?php
                endif;
                ?>
                                                    </td>
                                            <?php
            endif;
                                                
        if ($are_ticket_and_payment_cost_visible):
            ?>
                                                    <td>
                                                        <?php
                            echo $commission['cost_manager'];

            if (!empty($commission['costs_other'])):
                ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php
                                    echo $commission['costs_other'];
                ?>"></span>
                                                                </small>
                                                        <?php
            endif;
            ?>
                                                        <br>
                                                        <?php
                echo $commission['payment_cost_manager'];

            if (!empty($commission['payment_costs_other'])):
                ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php
                                    echo $commission['payment_costs_other'];
                ?>"></span>
                                                                </small>
                                                        <?php
            endif;
            ?>
                                                    </td>
                                            <?php
        endif;
                                                
        if ($is_income_visible):
            ?>
                                                    <td>
                                                        <?php
                            echo $commission['real_income_manager'];

            if (!empty($commission['real_incomes_other'])):
                ?>
                                                                <small>
                                                                    <span class="glyphicon glyphicon-info-sign" 
                                                                          data-toggle="tooltip" 
                                                                          data-placement="top" 
                                                                          title="" 
                                                                          data-original-title="<?php
                                    echo $commission['real_incomes_other'];
                ?>"></span>
                                                                </small>
                                                        <?php
            endif;
            ?>
                                                    </td>
                                            <?php
        endif;
        ?>
                                            <td>
                                                <?php
                echo $commission['commission_manager'];

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
endif;
        if (count($casinoCommissions ?? []) > 0):
            ?>
                <h3>
                    <?= _("Casino Commissions"); ?>
                </h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>#</th>
                                <?php if ($is_lead_id_visible): ?>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                <?php endif ?>
                                <?php if ($user['is_show_name']): ?>
                                    <th>
                                        <?= _("User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Lead Email") ?>
                                    </th>
                                <?php endif ?>
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
                            <?php foreach ($casinoCommissions as $key => $commission): ?>
                                <tr>
                                    <td><?= $key+1 ?>.</td>
                                    <?php if ($is_lead_id_visible): ?>
                                        <td>
                                            <?= $commission['aff_full_name']; ?>
                                        </td>
                                    <?php endif;
                                    if ($user['is_show_name']):
                                    ?>
                                        <td>
                                            <?= $commission['user_full_name']; ?>
                                        </td>
                                        <td>
                                            <?= $commission['lead_email']; ?>
                                        </td>
                                    <?php endif; ?>
                                        <td class="text-center">
                                            <?= $commission['created_at']; ?>
                                        </td>
                                        <td>
                                            <?= $commission['show_ggr'] ? $commission['ggr'] :'N\A'; ?>
                                    <?php if (!empty($commission['ggrInUserCurrency'])): ?>
                                            <small>
                                                <span class="glyphicon glyphicon-info-sign" 
                                                    data-toggle="tooltip" 
                                                    data-placement="top" 
                                                    title="" 
                                                    data-original-title="<?= $commission['ggrInUserCurrency'];?>">
                                                </span>
                                            </small>
                                    <?php endif; ?>
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
                                                    data-original-title="<?= $commission['commissions_other'];?>">
                                                </span>
                                            </small>
                                    <?php endif; endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
		</div>
	</div>
</div>