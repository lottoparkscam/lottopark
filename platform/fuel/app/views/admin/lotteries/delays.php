<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<div class="pull-right">
			<a href="/lotteries/delays/new" class="btn btn-sm btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
		</div>
		<h2>
            <?= _("Postponed Draws"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can add or edit delayed lottery draw dates to automatically process the changes within the system."); ?>
        </p>
		<div class="container-fluid container-admin">
            <?php include(APPPATH."views/admin/shared/messages.php"); ?>
            <?php if ($delays !== null && count($delays) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?= _("Lottery"); ?></th>
                            <th><?= _("Original date"); ?></th>
                            <th><?= _("Postponed date"); ?></th>
                            <th><?= _("Manage"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach ($delays as $item):
                                $lottery = $lotteries['__by_id'][$item['lottery_id']];
                        ?>
                                <tr>
                                    <td>
                                        <?= Security::htmlentities($lottery['name']); ?>
                                    </td>
                                    <td>
                                        <?= Lotto_View::format_date($item['date_local'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE); ?>
                                    </td>
                                    <td>
                                        <?= Lotto_View::format_date($item['date_delay'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE); ?>
                                    </td>
                                    <td>
                                        <a href="/lotteries/delays/edit/<?= $item['id']; ?>" class="btn btn-xs btn-success">
                                            <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                        </a>
                                        <?php
                                            $delaydate = DateTime::createFromFormat(Helpers_Time::DATETIME_FORMAT, $item->date_local, new DateTimeZone($lottery['timezone']));
                                            $nextdate = DateTime::createFromFormat(Helpers_Time::DATETIME_FORMAT, $lottery['next_date_local'], new DateTimeZone($lottery['timezone']));

                                            if ($delaydate > $nextdate):
                                        ?>
                                                <button type="button" data-href="/lotteries/delays/delete/<?= $item['id']; ?><?= Lotto_View::query_vars(); ?>" 
                                                        class="btn btn-xs btn-danger" data-toggle="modal" data-target="#confirmModal" 
                                                        data-confirm="<?= _("Are you sure?"); ?>">
                                                    <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                                </button>
                                        <?php 
                                            endif;
                                        ?>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= $pages->render(); ?>
            <?php else: ?>
                <p class="text-info"><?= _("There are no delays specified."); ?></p>
            <?php endif; ?>
		</div>
	</div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= _("Confirm"); ?></h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <a href="#" id="confirmOK" class="btn btn-success"><?= _("OK"); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _("Cancel"); ?></button>
            </div>
        </div>
    </div>
</div>