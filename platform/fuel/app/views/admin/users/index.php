<?php
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php
            include(APPPATH . "views/admin/users/menu.php");
        ?>
	</div>
    <div class="col-md-10">
        <?php
            $titles = [
                "users" => _("Active users"),
                "deleted" => _("Deleted users"),
                "inactive" => _("Inactive users")
            ];
            $descs = [
                "users" => _("Here you can view and manage your activated users."),
                "inactive" => _("Here you can manage your inactive useres."),
                "deleted" => _("Here you can manage your deleted users.")
            ];
        ?>
        
		<h2><?= $titles[$rparam]; ?></h2>
        
		<p class="help-block">
            <?= $descs[$rparam]; ?>
        </p>
        
        <?php
            include(APPPATH . "views/admin/users/index_filters.php");
        ?>
        
        <div class="container-fluid container-admin">
            <?php
                include(APPPATH."views/whitelabel/shared/messages.php");
                
                $long_tip = _("1. Download and save the file.<br>".
                    "2. Open Excel and create new file.<br>".
                    "3. Choose <i>Data->From Text</i> from <i>Get External Data</i> section.<br>".
                    "4. Navigate and choose saved file.<br>".
                    "5. Choose <i>Delimited</i>, file origin <i>Unicode (UTF-8)</i>, check <i>My data has headers.</i><br>".
                    "6. On next step choose only <i>Comma</i> as the delimiter.<br>".
                    "7. You can adjust some format settings in the last step (for best results you should set all columns to <i>Text</i> type instead of <i>General</i>).<br>".
                    "8. Click <i>Finish</i> and choose data placement.");
            ?>

            <?php
                if (isset($users) && count($users) > 0):
            ?>
                    <div class="pull-right export-view">
                        <a href="<?= $link; ?>/export<?= Lotto_View::query_vars(); ?>" 
                           class="btn btn-primary btn-xs" 
                           data-toggle="popover" 
                           data-placement="bottom" 
                           data-trigger="hover" 
                           title="<?= _("How to open in Excel"); ?>" 
                           data-content="<?= Security::htmlentities($long_tip); ?>">
                            <span class="glyphicon glyphicon-download-alt"></span> <?= _("Export View to CSV"); ?>
                        </a>
                    </div>

                    <div class="clearfix"></div>

                    <?= $pages; ?>
                    <div class="table-responsive">

                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("ID"); ?>
                                    </th>
                                    <th>
                                        <?= _("Whitelabel"); ?>
                                    </th>
                                    <th class="tablesorter-header tablesorter-<?= $sort['name']['class']; ?>" 
                                        data-href="<?= $sort['name']['link']; ?>">
                                        <?= _("Name"); ?><br>
                                        <?= _("E-mail"); ?>
                                    </th>
                                    <th class="tablesorter-header tablesorter-<?= $sort['balance']['class']; ?>" 
                                        data-href="<?= $sort['balance']['link']; ?>">
                                        <?= _("Balance"); ?>
                                    </th>
                                    <th>
                                        <?= _("Bonus balance"); ?>
                                    </th>
                                    <th>
                                        <?= _("Phone"); ?>
                                    </th>
                                    <th>
                                        <?= _("Country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Language"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Time Zone"); ?>
                                    </th>
                                    <?php
                                        if ((string)$rparam !== "deleted"):
                                    ?>
                                            <th class="text-center tablesorter-header tablesorter-<?= $sort['id']['class']; ?>" 
                                                data-href="<?= $sort['id']['link']; ?>">
                                                <?= _("Registered<br>IP<br>Country"); ?>
                                            </th>
                                    <?php
                                        else:
                                    ?>
                                            <th class="text-center tablesorter-header tablesorter-<?= $sort['date_delete']['class']; ?>" 
                                                data-href="<?= $sort['date_delete']['link']; ?>">
                                                <?= _("Date Deleted"); ?>
                                            </th>
                                    <?php
                                        endif;
                                        
                                        if ((string)$rparam === "deleted"):
                                    ?>
                                            <th>
                                                <?= _("Active"); ?>
                                            </th>
                                    <?php
                                        endif;
                                    ?>
                                    <th class="text-center">
                                        <?= _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                    foreach ($users as $item):
                                        $idx = $item['id'];
                                        $endig_part_url = $idx . Lotto_View::query_vars();
                                    
                                        $urls = [
                                            'tickets' => '/tickets?filter[userid]=' . $idx,
                                            'transactions' => '/transactions?filter[userid]=' . $idx,
                                            'deposits' => '/deposits?filter[userid]=' . $idx,
                                            'withdrawals' => '/withdrawals?filter[userid]=' . $idx,
                                            'confirm' => '/users/confirm/' . $endig_part_url,
                                            'restore' => '/deleted/restore/' . $endig_part_url,
                                            'activate' => '/inactive/activate/' . $endig_part_url,
                                            'view' => $link . '/view/' . $endig_part_url,
                                            'delete' => $link . '/delete/' . $endig_part_url,
                                        ];
                                ?>
                                        <tr>
                                            <td>
                                                <?= $item['w_prefix'] . 'U' . $item['token']; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['w_name'])) {
                                                        echo Security::htmlentities($item['w_name']);
                                                    }
                                                ?>
                                                <br>
                                                <?php
                                                    if (!empty($item['w_domain'])) {
                                                        echo Security::htmlentities($item['w_domain']);
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php
                                                    if (!empty($item['name']) ||
                                                        !empty($item['surname'])
                                                    ):
                                                        echo Security::htmlentities($item['name'].' '.$item['surname']);
                                                    else:
                                                        echo _("anonymous");
                                                    endif;
                                                ?>
                                                <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['email']); ?>

                                                <?php
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="<?= $urls['tickets']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="<?= $urls['transactions']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View purchases"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="<?= $urls['deposits']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View deposits"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="<?= $urls['withdrawals']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View withdrawals"); ?>
                                                        </a>
                                                <?php
                                                        if (!$item['is_confirmed']):
                                                ?>
                                                            <br>
                                                            <button type="button" 
                                                                    data-href="<?= $urls['confirm']; ?>" 
                                                                    data-toggle="modal" 
                                                                    data-target="#confirmModal" 
                                                                    class="btn btn-xs btn-success btn-mt" 
                                                                    data-confirm="<?= _("Are you sure?"); ?>">
                                                                <span class="glyphicon glyphicon-ok"></span> <?= _("Confirm"); ?>
                                                            </button>
                                                <?php
                                                        endif;
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $balance = Lotto_View::format_currency(
                                                    $item['balance'],
                                                    $item['user_currency_code'],
                                                    true
                                                );
                                                    $balance_text = _("User currency") .
                                                        ": " . $balance;
                                                    
                                                    $balance_currency_tab = [
                                                        'id' => $item['user_currency_id'],
                                                        'code' => $item['user_currency_code'],
                                                        'rate' => $item['user_currency_rate'],
                                                    ];
                                                    $balance_in_manager_curr = Helpers_Currency::get_recalculated_to_given_currency(
                                                        $item['balance'],
                                                        $balance_currency_tab,
                                                        $item['manager_currency_code']
                                                    );
                                                    $balance_in_manager_curr_full = Lotto_View::format_currency(
                                                        $balance_in_manager_curr,
                                                        $item['manager_currency_code'],
                                                        true
                                                    );
                                                    
                                                    echo $balance_in_manager_curr_full;
                                                    
                                                    if ($item['user_currency_code'] !== $item['manager_currency_code']):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $balance_text; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;

                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                        $check_whitelabel_id = -1;
                                                        $check_whitelabel_type = -1;

                                                        if (!empty($whitelabel) &&
                                                            !empty($whitelabel['id']) &&
                                                            !empty($whitelable['type'])
                                                        ):
                                                            $check_whitelabel_id = $whitelabel['id'];
                                                            $check_whitelabel_type = $whitelabel['type'];
                                                        else:
                                                            $check_whitelabel_id = $item['w_id'];
                                                            $check_whitelabel_type = $item['w_type'];
                                                        endif;
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $bonus_balance = Lotto_View::format_currency(
                                                    $item['bonus_balance'],
                                                    $item['user_currency_code'],
                                                    true
                                                );
                                                    $bonus_balance_text = _("User currency") .
                                                        ": " . $bonus_balance;
                                                    
                                                    $balance_currency_tab = [
                                                        'id' => $item['user_currency_id'],
                                                        'code' => $item['user_currency_code'],
                                                        'rate' => $item['user_currency_rate'],
                                                    ];
                                                    $bonus_balance_in_manager_curr = Helpers_Currency::get_recalculated_to_given_currency(
                                                        $item['bonus_balance'],
                                                        $balance_currency_tab,
                                                        $item['manager_currency_code']
                                                    );
                                                    $bonus_balance_in_manager_curr_full = Lotto_View::format_currency(
                                                        $bonus_balance_in_manager_curr,
                                                        $item['manager_currency_code'],
                                                        true
                                                    );
                                                    
                                                    echo $bonus_balance_in_manager_curr_full;
                                                    
                                                    if ($item['user_currency_code'] !== $item['manager_currency_code']):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign" 
                                                                  data-toggle="tooltip" 
                                                                  data-placement="top" 
                                                                  title="" 
                                                                  data-original-title="<?= $bonus_balance_text; ?>">
                                                            </span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php
                                                    if (!empty($item['phone']) &&
                                                        !empty($item['phone_country'])
                                                    ):
                                                        echo Security::htmlentities(Lotto_View::format_phone($item['phone'], $item['phone_country']));
                                                    endif;

                                                    if (isset($countries[$item['phone_country']])):
                                                ?>
                                                        <br>
                                                        <?= Security::htmlentities($countries[$item['phone_country']]); ?>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    // NOTE: if country is unspecified return empty TODO: Export into presenter.
                                                    if (!empty($item['country'])):
                                                        echo Security::htmlentities($countries[$item['country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?= Lotto_View::format_language($languages[$item['language_id']]['code']); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php
                                                    if (!empty($item['timezone'])):
                                                        echo Security::htmlentities(Lotto_View::format_time_zone($item['timezone'], true));
                                                    endif;
                                                ?>
                                            </td>
                                            <?php
                                                if ((string)$rparam !== "deleted"):
                                                    $date_register = _("None");
                                                    if (!empty($item['date_register'])) {
                                                        $date_register = Lotto_View::format_date(
                                                            $item['date_register'],
                                                            IntlDateFormatter::MEDIUM,
                                                            IntlDateFormatter::SHORT
                                                        );
                                                    }
                                            ?>
                                                    <td class="text-center">
                                                        <?= $date_register; ?>
                                                        <br>
                                                        <?php
                                                            echo Security::htmlentities($item['register_ip']);
                                                            
                                                            if (isset($item['register_country'])):
                                                                echo '<br>' . $countries[$item['register_country']];
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php
                                                else:
                                                    $date_delete = _("None");
                                                    if (!empty($item['date_delete'])) {
                                                        $date_delete = Lotto_View::format_date(
                                                            $item['date_delete'],
                                                            IntlDateFormatter::MEDIUM,
                                                            IntlDateFormatter::SHORT
                                                        );
                                                    }
                                            ?>
                                                    <td class="text-center">
                                                        <?= $date_delete; ?>
                                                    </td>
                                            <?php
                                                endif;
                                                
                                                if ((string)$rparam === "deleted"):
                                            ?>
                                                    <td>
                                                        <?php
                                                            $check_user_activation_type = -1;
                                                            if (!empty($whitelabel) &&
                                                                !empty($whitelabel['user_activation_type'])
                                                            ):
                                                                $check_user_activation_type = $whitelabel['user_activation_type'];
                                                            else:
                                                                $check_user_activation_type = $item['w_user_activation_type'];
                                                            endif;

                                                            if ($item['is_active'] &&
                                                                    ($check_user_activation_type != Helpers_General::ACTIVATION_TYPE_REQUIRED) ||
                                                                ($check_user_activation_type == Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                                                                    $item['is_confirmed'])):
                                                                echo _("Yes");
                                                            else:
                                                                echo _("No");
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php
                                                endif;
                                            ?>
                                            <td class="text-center">
                                                <?php
                                                    if ($item['is_deleted']):
                                                ?>
                                                        <button type="button" 
                                                                data-href="<?= $urls['restore']; ?>" 
                                                                class="btn btn-xs btn-success" 
                                                                data-toggle="modal" 
                                                                data-target="#confirmModal" 
                                                                data-confirm="<?= _("Are you sure?"); ?>">
                                                            <span class="glyphicon glyphicon-check"></span> <?= _("Restore"); ?>
                                                        </button>
                                                <?php
                                                    else:
                                                        if ((string)$rparam === "inactive"):
                                                ?>
                                                            <a href="<?= $urls['activate']; ?>" 
                                                               class="btn btn-xs btn-success">
                                                                <span class="glyphicon glyphicon-ok"></span> <?= _("Activate"); ?>
                                                            </a>
                                                <?php
                                                        else:
                                                ?>
                                                            <a href="<?= $urls['view']; ?>" 
                                                               class="btn btn-xs btn-primary">
                                                                <span class="glyphicon glyphicon-list"></span> <?= _("Details"); ?>
                                                            </a>
                                                <?php
                                                        endif;
                                                ?>
                                                        <button type="button" 
                                                                data-href="<?= $urls['delete']; ?>" 
                                                                class="btn btn-xs btn-danger" 
                                                                data-toggle="modal" 
                                                                data-target="#confirmModal" 
                                                                data-confirm="<?= _("Are you sure?"); ?>">
                                                            <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                                        </button>
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
                    echo $pages;
                else:
            ?>
                    <p class="text-info"><?= _("No users."); ?></p>
            <?php
                endif;
            ?>
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