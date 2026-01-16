<?php 
    include(APPPATH."views/admin/shared/navbar.php");
    
    $begin_payments_url = '/whitelabels/payments/' . $whitelabel['id'];
    $begin_ccpayments_url = '/whitelabels/ccpayments/' . $whitelabel['id'];

    $payment_new_url = $begin_ccpayments_url . '/new';

?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/whitelabels/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<div class="pull-right">
			<a href="<?= $payment_new_url; ?>" class="btn btn-success btn-sm">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
		</div>
		
        <h2>
            <?= _("Credit Card methods"); ?> <small><?= $whitelabel['name']; ?></small>
        </h2>
        
		<p class="help-block">
            <?= _("Here you can manage Credit Card payment methods of your whitelabels."); ?>
        </p>
		
        <div class="btn-group" role="group">
            <a href="<?= $begin_payments_url; ?>" class="btn btn-default">
                <?= _("Payment methods"); ?>
            </a>
		</div>
        
		<div class="container-fluid container-admin">
		<?php 
            include(APPPATH."views/admin/shared/messages.php");
            
            if ($methods !== null && count($methods)):
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("ID"); ?>
                                </th>
                                <th>
                                    <?= _("Gateway"); ?>
                                </th>
                                <th>
                                    <?= _("Payment currency"); ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Manage"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $i = 0;
                                foreach ($methods as $item):
                                    $i++;
                                    $edit_payment_url = $begin_ccpayments_url .
                                        '/edit/' . $i;
                            ?>
                                    <tr>
                                        <td>
                                            <?= $i; ?>
                                        </td>
                                        <td>
                                            <?= Lotto_View::get_gateway_name($item['method']); ?>
                                        </td>
                                        <td>
                                            <?php 
                                                // TODO: add multi currencies served by CC?
                                                if (!empty($currencies) &&
                                                    !empty($currencies[$item['payment_currency_id']])
                                                ) {
                                                    echo $currencies[$item['payment_currency_id']];
                                                }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $edit_payment_url; ?>" 
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
            //echo $pages;
            else: ?>
                <p class="text-info">
                    <?= _("There are no credit card payment methods specified for this whitelabel."); ?>
                </p>
		<?php 
            endif;
        ?>
		</div>
	</div>
</div>