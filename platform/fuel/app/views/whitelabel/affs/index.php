<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<?php 
            if ($rparam == null || $rparam == "list"):
        ?>
                <div class="pull-right">
                    <a href="<?= $link; ?>/new" class="btn btn-success">
                        <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
                    </a>
                </div>
		<?php 
            endif;
        ?>
		<h2>
            <?= _("Affiliates"); ?>
        </h2>
		<p class="help-block">
            <?= _("You can manage your affiliates here."); ?>
        </p>
        
		<?php
            include(APPPATH . "views/whitelabel/affs/index_filters.php");
        ?>
        
		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/whitelabel/shared/messages.php");
                 
                if (isset($affs) && count($affs) > 0):
                    $longtip = _("1. Download and save the file.<br>".
                        "2. Open Excel and create new file.<br>".
                        "3. Choose <i>Data->From Text</i> from <i>Get External Data</i> section.<br>".
                        "4. Navigate and choose saved file.<br>".
                        "5. Choose <i>Delimited</i>, file origin <i>Unicode (UTF-8)</i>, check <i>My data has headers.</i><br>".
                        "6. On next step choose only <i>Comma</i> as the delimiter.<br>".
                        "7. You can adjust some format settings in the last step (for best results you should set all columns to <i>Text</i> type instead of <i>General</i>).<br>".
                        "8. Click <i>Finish</i> and choose data placement.");
            ?>
                    <div class="pull-right export-view">
                        <a href="<?= $link; ?>/export" 
                           class="btn btn-primary btn-xs" 
                           data-toggle="popover" 
                           data-placement="bottom" 
                           data-trigger="hover" 
                           title="<?= _("How to open in Excel"); ?>" 
                           data-content="<?= Security::htmlentities($longtip); ?>">
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
                                    <th class="tablesorter-header tablesorter-<?= $sort['name']['class']; ?>" data-href="<?= $sort['name']['link']; ?>">
                                        <?= _("Username"); ?> &bull; <?= _("Login"); ?>
                                        <br>
                                        <?= _("E-mail"); ?>
                                    </th>
                                    <th>
                                        <?= _("Is Aff User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Parent"); ?>
                                    </th>
                                    <th>
                                        Lottery Group
                                    </th>
                                    <th>
                                        Casino Group
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
                                        if ($rparam != "deleted"):
                                    ?>
                                            <th class="text-center tablesorter-header tablesorter-<?= $sort['id']['class']; ?>" 
                                                data-href="<?= $sort['id']['link']; ?>">
                                                <?= _("Created"); ?>
                                            </th>
                                            <th class="text-center tablesorter-header tablesorter-<?= $sort['last_active']['class']; ?>" 
                                                data-href="<?= $sort['last_active']['link']; ?>">
                                                <?= _("Last Active<br>IP<br>Country"); ?>
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
                                        
                                        if ($rparam == "deleted"):
                                    ?>
                                            <th>
                                                <?= _("Active"); ?>
                                            </th>
                                            <th>
                                                <?= _("Accepted"); ?>
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
                                    foreach ($affs as $item):
                                ?>
                                        <tr>
                                            <td>
                                                <?= strtoupper($item['token']); ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php 
                                                    if (!empty($item['name']) || !empty($item['surname'])):
                                                        echo Security::htmlentities($item['name'].' '.$item['surname']);
                                                    else:
                                                        echo _("anonymous");
                                                    endif;
                                                    
                                                    echo " &bull; ";
                                                    echo Security::htmlentities($item['login']); ?>
                                                <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['email']); ?>
                                                <?php 
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                        if (!$item['is_confirmed']):
                                                ?>
                                                            <br>
                                                            <button type="button" 
                                                                    data-href="<?= $link; ?>/confirm/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
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
                                            <td><?php echo (isset($item['is_aff_user'])) ? $item['is_aff_user'] : 'BRAK';?></td>
                                            <td>
                                                <?php 
                                                    if (!empty($item['whitelabel_aff_parent_id'])):
                                                        if (!empty($rallaffs[$item['whitelabel_aff_parent_id']]['name']) || !empty($rallaffs[$item['whitelabel_aff_parent_id']]['surname'])):
                                                            echo Security::htmlentities($rallaffs[$item['whitelabel_aff_parent_id']]['name'].' '.$rallaffs[$item['whitelabel_aff_parent_id']]['surname']);
                                                        else:
                                                            echo _("anonymous");
                                                        endif;
                                                        
                                                        echo " &bull; ";
                                                        echo Security::htmlentities($rallaffs[$item['whitelabel_aff_parent_id']]['login']);
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    if (!empty($item['whitelabel_aff_group_id'])):
                                                        echo $lotteryGroups[$item['whitelabel_aff_group_id']]['name'];
                                                    else:
                                                        echo 'Default Lottery Group';
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                if (!empty($item['whitelabel_aff_casino_group_id'])):
                                                    echo $casinoGroups[$item['whitelabel_aff_casino_group_id']]['name'];
                                                else:
                                                    echo 'Default Casino Group';
                                                endif;
                                                ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?php 
                                                    if (!empty($item['phone']) && !empty($item['phone_country'])):
                                                        echo Security::htmlentities(Lotto_View::format_phone($item['phone'], $item['phone_country']));
                                                    endif;
                                                
                                                    if (isset($countries[$item['phone_country']])):
                                                        echo '<br>';
                                                        echo Security::htmlentities($countries[$item['phone_country']]);
                                                    endif;
                                                ?>
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
                                                    if (!empty($languages[$item['language_id']]['code'])):
                                                        echo Lotto_View::format_language($languages[$item['language_id']]['code']);
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    if (!empty($item['timezone'])):
                                                        echo Security::htmlentities(Lotto_View::format_time_zone($item['timezone'], true));
                                                    endif;
                                                ?>
                                            </td>
                                            <?php 
                                                if ($rparam != "deleted"):
                                            ?>
                                                    <td class="text-center">
                                                        <?= Lotto_View::format_date($item['date_created'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php 
                                                            if (!empty($item['last_active'])):
                                                                echo Lotto_View::format_date($item['last_active'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
                                                            endif;

                                                            echo '<br>';

                                                            if (!empty($item['last_ip'])):
                                                                echo Security::htmlentities($item['last_ip']);
                                                            endif;

                                                            if (!empty($item['last_country'])):
                                                                echo '<br>' . $countries[$item['last_country']];
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php 
                                                else:
                                            ?>
                                                    <td class="text-center">
                                                        <?php
                                                            if (!empty($item['date_delete'])):
                                                                echo Lotto_View::format_date($item['date_delete'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php 
                                                endif;
                                                
                                                if ($rparam == "deleted"):
                                            ?>
                                                    <td>
                                                        <?php 
                                                            if ($item['is_active'] &&
                                                                (($whitelabel['user_activation_type'] != Helpers_General::ACTIVATION_TYPE_REQUIRED) ||
                                                                    ($whitelabel['user_activation_type'] == Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                                                                    $item['is_confirmed']))
                                                            ):
                                                                echo _("Yes");
                                                            else:
                                                                echo _("No");
                                                            endif;
                                                        ?>
                                                    </td>
                                            <?php 
                                                endif;
                                                
                                                if ($rparam == "deleted"):
                                            ?>
                                                    <td>
                                                        <?php 
                                                            if ($item['is_accepted']):
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
                                                                data-href="/affs/deleted/restore/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
                                                                class="btn btn-xs btn-success" 
                                                                data-toggle="modal" 
                                                                data-target="#confirmModal" 
                                                                data-confirm="<?= _("Are you sure?"); ?>">
                                                            <span class="glyphicon glyphicon-check"></span> <?= _("Restore"); ?>
                                                        </button>
                                                <?php 
                                                    else:
                                                        if ($rparam == "inactive"):
                                                    ?>
                                                            <a href="/affs/inactive/activate/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
                                                               class="btn btn-xs btn-success">
                                                                <span class="glyphicon glyphicon-ok"></span> <?= _("Activate"); ?>
                                                            </a>
                                                    <?php 
                                                        elseif ($rparam == "notaccepted"):
                                                    ?>
                                                            <a href="/affs/notaccepted/accept/<?= $item['token']; ?><?= Lotto_View::query_vars(); ?>" 
                                                               class="btn btn-xs btn-success">
                                                                <span class="glyphicon glyphicon-ok"></span> <?= _("Accept"); ?>
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
                    <p class="text-info">
                        <?= _("No affiliates."); ?>
                    </p>
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
