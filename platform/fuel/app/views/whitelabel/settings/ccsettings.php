<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?php echo _("Credit Card methods"); ?></h2>
		<p class="help-block"><?php echo _("Here you can manage Credit Card payment methods settings."); ?></p>
		<div class="pull-right">
			<a href="/ccsettings/new" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> <?php echo _("Add New"); ?></a>
		</div>
		<div class="btn-group" role="group">
		  <a href="/paymentmethods" class="btn btn-default"><?php echo _("Payment methods"); ?></a>
		</div>
		<div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/whitelabel/shared/messages.php");

                if (isset($methods) && count($methods) > 0):
            ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?php echo _("ID"); ?>
                                    </th>
                                    <th>
                                        <?php echo _("Gateway"); ?>
                                    </th>
                                    <th>
                                        <?php echo _("Payment currency"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?php echo _("Manage"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $i = 0;
                                    foreach ($methods as $item):
                                        $i++;
                                ?>
                                        <tr>
                                            <td>
                                                <?php echo $i; ?>
                                            </td>
                                            <td>
                                                <?php echo Lotto_View::get_gateway_name($item['method']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    // TODO: add multi currencies served by CC?
                                                    if (!empty($currencies) && !empty($currencies[$item['payment_currency_id']])) {
                                                        echo $currencies[$item['payment_currency_id']];
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="/ccsettings/edit/<?php echo $i; ?>" class="btn btn-xs btn-success">
                                                    <span class="glyphicon glyphicon-edit"></span> <?php echo _("Edit"); ?>
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
                    //echo $pages;
                else:
            ?>
                    <p class="text-info"><?php echo _("No Credit Card payment methods."); ?></p>
            <?php 
                endif;
            ?>
		</div>
	</div>
</div>