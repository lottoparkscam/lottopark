<?php

use Models\Whitelabel;

include(APPPATH . "views/whitelabel/shared/navbar.php");

/** @var Whitelabel $whitelabelModel */
$whitelabelModel = Container::get('whitelabel');

?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/users/menu.php"); ?>
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
        <h2>
            <?= $titles[$rparam]; ?>
        </h2>
        <p class="help-block">
            <?= $descs[$rparam]; ?>
        </p>

        <?php
            include(APPPATH . "views/whitelabel/users/index_filters.php");
        ?>
        
        <div class="container-fluid container-admin">
            <?php
                include(APPPATH."views/whitelabel/shared/messages.php");

                $long_tip = _(
                    "1. Download and save the file.<br>".
                    "2. Open Excel and create new file.<br>".
                    "3. Choose <i>Data->From Text</i> from <i>Get External Data</i> section.<br>".
                    "4. Navigate and choose saved file.<br>".
                    "5. Choose <i>Delimited</i>, file origin <i>Unicode (UTF-8)</i>, check <i>My data has headers.</i><br>".
                    "6. On next step choose only <i>Comma</i> as the delimiter.<br>".
                    "7. You can adjust some format settings in the last step (for best results you should set all columns to <i>Text</i> type instead of <i>General</i>).<br>".
                    "8. Click <i>Finish</i> and choose data placement."
                );

                if (isset($users) && count($users) > 0):
                    $whitelabel['manager_site_currency_id'];
                    
                    $export_url = $link . "/export" . Lotto_View::query_vars();
                    $long_tip_text = Security::htmlentities($long_tip);
                    
            ?>
                    <div class="pull-right export-view">
                        <a href="<?= $export_url; ?>" 
                           class="btn btn-primary btn-xs" 
                           data-toggle="popover" 
                           data-placement="bottom" 
                           data-trigger="hover" 
                           title="<?= _("How to open in Excel"); ?>" 
                           data-content="<?= $long_tip_text; ?>">
                            <span class="glyphicon glyphicon-download-alt"></span> <?= _("Export View to CSV"); ?>
                        </a>
                    </div>

                    <div class="clearfix"></div>

                    <?php echo $pages; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("ID"); ?>
                                    </th>
                                    <th class="tablesorter-header tablesorter-<?= $sort['name']['class']; ?>" 
                                        data-href="<?= $sort['name']['link']; ?>">
                                        <?= _("Name"); ?><br>
                                        <?= _("E-mail"); ?><br>
                                        <?php
                                            if ($whitelabelModel->loginForUserIsUsedDuringRegistration()):
                                        ?>
                                                <?= _("Login"); ?>
                                        <?php
                                            endif;
                                        ?>
                                    </th>
                                    <th>
                                        <?= _("Affiliate"); ?>
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
                                ?>
                                        <tr>
                                            <td>
                                                <?= $item['w_prefix'] . 'U' . $item['token']; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php
                                                    if (!empty($item['name']) ||
                                                        !empty($item['surname'])
                                                    ):
                                                        echo Security::htmlentities($item['name'] . ' ' . $item['surname']);

                                                    else:
                                                        echo _("anonymous");
                                                    endif;
                                                ?>
                                                <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> 
                                                <?php
                                                    echo Security::htmlentities($item['email']);
                                                    if ($whitelabelModel->loginForUserIsUsedDuringRegistration()) {
                                                        $login = "-";
                                                        if (isset($item['login'])) {
                                                            $login = Security::htmlentities($item['login']);
                                                        }
                                                        echo "<br>";
                                                        echo $login;
                                                    }
                                                
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="/tickets?filter[userid]=<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="/transactions?filter[userid]=<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View purchases"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="/deposits?filter[userid]=<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View deposits"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="/withdrawals?filter[userid]=<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View withdrawals"); ?>
                                                        </a>
                                                <?php
                                                        if (!$item['is_confirmed']):
                                                ?>
                                                            <br>
                                                            <button type="button" 
                                                                    data-href="/users/confirm/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
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
                                                    if (!empty($item['whitelabel_aff_id'])):
                                                        if (!empty($item['aff_name']) || !empty($item['aff_surname'])):
                                                            echo Security::htmlentities($item['aff_name'] . ' ' . $item['aff_surname']);
                                                        else:
                                                            echo _("anonymous");
                                                        endif;
                                                        
                                                        echo ' &bull; ';
                                                        
                                                        echo Security::htmlentities($item['aff_login']);
                                                ?>
                                                        <br>
                                                        <span class="<?= Lotto_View::show_boolean_class($item['aff_is_confirmed']); ?>">
                                                            <?= Lotto_View::show_boolean($item['aff_is_confirmed']); ?>
                                                        </span> <?= Security::htmlentities($item['aff_email']); ?>
                                                        <br>
                                                            <?= _("Accepted"); ?>: 
                                                            <span class="<?= Lotto_View::show_boolean_class($item['aff_is_accepted']); ?>">
                                                                <?= Lotto_View::show_boolean($item['aff_is_accepted']); ?>
                                                            </span>
                                                        <br>
                                                            <?= _("Deleted"); ?>: 
                                                            <span class="<?= Lotto_View::show_boolean_class(!$item['aff_is_deleted']); ?>">
                                                                <?= Lotto_View::show_boolean($item['aff_is_deleted']); ?>
                                                            </span>
                                                        <br>
                                                        <a href="/affs<?php if ($item['aff_is_deleted']): echo '/deleted'; endif; ?>?filter[id]=<?= strtoupper($item['aff_token']); ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                        </a>
                                                <?php
                                                    else:
                                                        echo _("None");
                                                    endif;
                                                ?>
                                                <br>
                                                <?php
                                                    if ((string)$rparam !== "deleted"):
                                                ?>
                                                        <a href="<?= $link; ?>/aff/<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-success">
                                                            <span class="glyphicon glyphicon-edit"></span> <?= _("Change affiliate"); ?>
                                                        </a>
                                                <?php
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
                                                <?php endif; ?>
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
                                                    if (!empty($item['phone']) && !empty($item['phone_country'])):
                                                        echo Security::htmlentities(Lotto_View::format_phone($item['phone'], $item['phone_country']));
                                                    endif;
                                                
                                                    if (isset($countries[$item['phone_country']])):
                                                ?>
                                                        <br>
                                                <?php
                                                        echo Security::htmlentities($countries[$item['phone_country']]);
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['country'])):
                                                        echo Security::htmlentities($countries[$item['country']]);

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

                                                            if (!empty($item['register_country'])):
                                                                echo isset($countries[$item['register_country']]) ? '<br>'.$countries[$item['register_country']] ?? '' : '';
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
                                                            if ($item['is_active'] &&
                                                                ($whitelabel['user_activation_type'] != Helpers_General::ACTIVATION_TYPE_REQUIRED) ||
                                                                ($whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                                                                    $item['is_confirmed'])
                                                            ) {
                                                                echo _("Yes");
                                                            } else {
                                                                echo _("No");
                                                            }
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
                                                                data-href="/deleted/restore/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
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
                                                            <a href="/inactive/activate/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
                                                               class="btn btn-xs btn-success">
                                                                <span class="glyphicon glyphicon-ok"></span> <?= _("Activate"); ?>
                                                            </a>
                                                <?php
                                                        else:
                                                ?>
                                                            <a href="<?= $link; ?>/view/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
                                                               class="btn btn-xs btn-primary">
                                                                <span class="glyphicon glyphicon-list"></span> <?= _("Details"); ?>
                                                            </a>
                                                <?php
                                                        endif;
                                                        
                                                ?>
                                                        <button type="button" 
                                                                data-href="<?= $link; ?>/delete/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
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
