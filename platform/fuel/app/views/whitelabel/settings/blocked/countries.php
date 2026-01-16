<?php include(APPPATH."views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
        <div class="pull-right">
			<a href="/blocked_countries/new" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> <?php echo _("Add New"); ?></a>
		</div>
		<h2><?= _("Blocked countries"); ?></h2>
        <p class="help-block"><?= _("You can change blocked countries here."); ?></p>
		<div class="container-fluid container-admin">
            <?php
            include(APPPATH."views/whitelabel/shared/messages.php");
            // show table if it's not empty.
            if (!empty($blocked_countries)):
                ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th class="text-center"><?= _("ISO code") ?></th>
                                <th class="text-center"><?= _("Name") ?></th>
                                <?php if ($is_admin): ?>
                                    <th class="text-center">Deletable</th>
                                <?php endif ?>
                                <th class="text-center"><?= _("Manage") ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blocked_countries as $row): ?>
                                <tr class="<?= !$row['is_deletable'] && !$is_admin ? 'opacity-mid' : ''?>">
                                    <?php foreach ($row as $key => $item) : ?>
                                    <?php if ($key === "is_deletable") continue; ?>
                                    <td class="text-center valign-middle"><?= $item ?></td>
                                    <?php endforeach; ?>
                                    <?php if ($is_admin): ?>
                                        <td class="text-center valign-middle">
                                            <div class="checkbox">
                                                <label>
                                                    <input
                                                            type="checkbox"
                                                            name="deletable"
                                                            class ="blocked_country_toggle_deletable"
                                                            <?= $row['is_deletable'] ? 'checked' : ''?>
                                                            data-href="/blocked_countries/deletable/<?= $row['code'] ?>"
                                                    />
                                                    Is deletable
                                                </label>
                                            </div>
                                        </td>
                                    <?php endif ?>
                                    <td class="text-center valign-middle">
                                        <?php //TODO: It may be a good idea to make modals into closure provided by presenter, it will enhance readability and consistency e.g. $make_modal('Are you sure?') ?>
                                        <?php if ($row['is_deletable'] || $is_admin) : ?>
                                            <button type="button" data-href="/blocked_countries/delete/<?= $row['code'] ?>"
                                                    class="btn btn-xs btn-danger" data-toggle="modal" data-target="#confirmModal" data-confirm="<?= _("Are you sure?"); ?>">
                                                <span class="glyphicon glyphicon-remove"></span> <?= _("Delete"); ?>
                                            </button>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-info">
                    <?= _("No blocked countries.") ?>
                </p>
            <?php endif; ?>
        </div>
	</div>
</div>

<?php //TODO: It would be a great idea to make it as shared include, for views which need it; or as closure provided by presenter ?>
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