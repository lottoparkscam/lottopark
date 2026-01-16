<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH . "views/whitelabel/affs/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Leads"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can view your affiliates latest leads."); ?>
        </p>
        
		<?php
            include(APPPATH . "views/whitelabel/affs/reports/leads_filters.php");
        ?>
        
		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/aff/shared/messages.php");

                if (isset($regcount) && count($regcount) > 0):
                    echo $pages;
            ?>
                    <div class="pull-right export-view">
                        <a href="/affs/leads/export<?= Lotto_View::query_vars(); ?>" 
                           class="btn btn-primary btn-xs">
                            <span class="glyphicon glyphicon-download-alt"></span> <?= _("Export View to CSV"); ?>
                        </a>
                    </div>

                    <div class="clearfix"></div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th>
                                        <?= _("Active"); ?>
                                    </th>
                                    <th>
                                        <?= _("Expired"); ?>
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
                                        <?= _("First purchase"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    foreach ($regcount as $key => $item):
                                ?>
                                        <tr>
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
                                                <br>
                                                <a href="/affs?filter[id]=<?= strtoupper($item['aff_token']); ?>" 
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_active']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_active']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_expired']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_expired']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    if (!empty($item['name']) || !empty($item['surname'])):
                                                        echo Security::htmlentities($item['name'] . ' ' . $item['surname']);
                                                    else:
                                                        echo _("anonymous");
                                                    endif;

                                                    echo " &bull; ";
                                                    echo $whitelabel['prefix'].'U'.$item['token'];
                                                ?>
                                                <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['email']); ?>
                                                <?php 
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="/users?filter[id]=<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="/tickets?filter[userid]=<?= $item['token']; ?>" 
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php 
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
                                                <?php 
                                                    if (!empty($item['register_country'])):
                                                        echo Security::htmlentities($countries[$item['register_country']]);
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    if (!empty($item['last_country'])):
                                                        echo Security::htmlentities($countries[$item['last_country']]);
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($item['first_purchase']) ? Lotto_View::format_date($item['first_purchase'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                    if ($item['is_accepted'] == 0):
                                                ?>
                                                        <button type="button" 
                                                                data-href="/affs/leads/accept/<?= $item['id']; ?><?= Lotto_View::query_vars(); ?>" 
                                                                class="btn btn-xs btn-success" 
                                                                data-toggle="modal" 
                                                                data-target="#confirmModal" 
                                                                data-confirm="<?= _("Are you sure?"); ?>">
                                                            <span class="glyphicon glyphicon-ok"></span> <?= _("Accept"); ?>
                                                        </button>
                                                <?php 
                                                    endif;
                                                ?>
                                                <button type="button" 
                                                        data-href="/affs/leads/delete/<?= $item['id']; ?><?= Lotto_View::query_vars(); ?>" 
                                                        class="btn btn-xs btn-danger" 
                                                        data-toggle="modal" 
                                                        data-target="#confirmModal" 
                                                        data-confirm="<?= _("Are you sure?"); ?>">
                                                    <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                                </button>
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
                        <?= _("No leads."); ?>
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