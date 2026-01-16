<?php 
include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-12">
		<h2>
            <?= _("Prepaid transactions"); ?> <small><?= $whitelabel['name']; ?></small>
        </h2>
		<p class="help-block">
            <?= _("Here you can view your prepaid transactions."); ?>
        </p>

		<div class="container-fluid container-admin">
		<?php 
            include(APPPATH . "views/whitelabel/shared/messages.php");
        ?>
            <div class="alert alert-<?= $sum_prepaid_class; ?>">
                <?= _("Summary: "); ?>
                <strong>
                    <?= $sum_value; ?>
                </strong>
            </div>
        <?php
            if ($prepaids !== null && count($prepaids) > 0):
        ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("Date"); ?>
                                </th>
                                <th>
                                    <?= _("Amount"); ?>
                                </th>
                                <th>
                                    <?= _("Transaction ID") ?>
                                </th>
                                <th>
                                    <?= _("Manage") ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach ($prepaids as $prepaid):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $prepaid['date']; ?>
                                        </td>
                                        <td>
                                            <?= $prepaid['amount']; ?>
                                        </td>
                                        <td>
                                            <?= $prepaid['transaction_id']; ?>
                                        </td>
                                        <td>
                                            <?php
                                                if ($prepaid['show_manage_view']):
                                            ?>
                                                    <a href="<?= $prepaid["transaction_view_url"]; ?>" 
                                                       class="btn btn-xs btn-primary">
                                                        <span class="glyphicon glyphicon-list"></span> <?= _("Transaction details"); ?>
                                                    </a>
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
                <p class="text-info"><?php echo _("There are no prepaid transactions."); ?></p>
		<?php 
            endif;
        ?>
		</div>
	</div>
</div>