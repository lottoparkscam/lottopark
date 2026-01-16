<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("First-Time Purchases"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can view the latest first-time purchases of your affiliate leads."); ?>
        </p>
        
		<?php
            include(APPPATH . "views/whitelabel/affs/reports/ftps_filters.php");
        ?>
        
		<div class="container-fluid container-admin">
		<?php 
            include(APPPATH . "views/aff/shared/messages.php");
            
            if (isset($ftpcount) && count($ftpcount) > 0):
                echo $pages;
        ?>
                <div class="pull-right export-view">
                    <a href="/affs/ftps/export<?= Lotto_View::query_vars(); ?>" 
                       class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-download-alt"></span> <?= _("Export View to CSV"); ?>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach ($ftpcount as $key => $item):
                            ?>
                                    <tr>
                                        <td>
                                            <?php 
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
                                            <a href="/affs?filter[id]=<?= strtoupper($item['aff_token']); ?>" class="btn btn-xs btn-primary">
                                                <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php 
                                                if (!empty($item['name']) || !empty($item['surname'])):
                                                    echo Security::htmlentities($item['name'].' '.$item['surname']);
                                                else:
                                                    echo _("anonymous");
                                                endif;

                                                echo ' &bull; ';
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
                                                    <a href="/users?filter[id]=<?= $item['token']; ?>" class="btn btn-xs btn-primary">
                                                        <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                    </a>
                                                    <br>
                                                    <a href="/tickets?filter[userid]=<?= $item['token']; ?>" class="btn btn-xs btn-primary">
                                                        <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                    </a>
                                            <?php 
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
                echo $pages;
            else:
        ?>
                <p class="text-info">
                    <?= _("No first-time purchases."); ?>
                </p>
		<?php 
            endif;
        ?>
		</div>
	</div>
</div>