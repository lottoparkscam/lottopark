<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<div class="pull-right">
			<button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#confirmaddModal">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </button>
		</div>
		<h2>
            <?= _("Draws"); ?> <small><?= Security::htmlentities($lottery['name']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("This is a list of lottery draws."); ?>
        </p>
		<a href="/lotteries" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
		<div class="container-fluid container-admin">
            <?php include(APPPATH."views/admin/shared/messages.php"); ?>
            <?php if ($draws !== null && count($draws)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?= _("Draw Date"); ?></th>
                                <th><?= _("Download Date"); ?></th>
                                <th class="text-center"><?= _("Jackpot"); ?></th>
                                <th><?= _("Draw Numbers"); ?></th>
                                <th><?= _("Draw Bonus Numbers"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                foreach ($draws as $item):
                            ?>
                                    <tr>
                                        <td>
                                            <?= Lotto_View::format_date($item['date_local'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE); ?>
                                        </td>
                                        <td>
                                            <?= Lotto_View::format_date($item['date_download'], IntlDateFormatter::SHORT, IntlDateFormatter::FULL); ?>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <?= Lotto_View::format_currency($item['jackpot']*1000000, $currencies[$lottery['currency_id']]['code']); ?>
                                        </td>
                                        <td>
                                            <?= Lotto_View::format_numbers($item['numbers']); ?>
                                        </td>
                                        <td>
                                            <?= Lotto_View::display_additional_numbers($item['bnumbers'], $item['additional_data']); ?>
                                        </td>
                                    </tr>
                            <?php 
                                endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?= $pages->render(); ?>
            <?php else: ?>
                <p class="text-info">
                    <?= _("There are no draws for this lottery yet."); ?>
                </p>
            <?php endif; ?>
		</div>
	</div>
</div>

<div class="modal fade" id="confirmaddModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _("Confirm new draw"); ?></h4>
            </div>
            <div class="modal-body">
                <?php 
                    $modal_text = _("Are you sure you want to add new draw?&#10;" .
                        "You should only do this when automatic update is not available!&#10;" .
                        "All users' tickets will be processed according to added draw data!");
                    echo $modal_text;
                ?>
            </div>
            <div class="modal-footer">
                <a href="/lotteries/view/<?= $lottery['id']; ?>/s/<?= $page; ?>/add" class="btn btn-success"><?= _("Add New"); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>