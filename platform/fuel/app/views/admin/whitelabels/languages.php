<?php include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/whitelabels/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<div class="pull-right">
			<a href="/whitelabels/languages/<?= $whitelabel['id']; ?>/new" 
               class="btn btn-success btn-sm">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
		</div>
		<h2>
            <?= _("Languages"); ?> <small><?= $whitelabel['name']; ?></small>
        </h2>
		<p class="help-block">
            <?= _("Here you can manage languages of your whitelabels."); ?>
        </p>
		
		<div class="container-fluid container-admin">
		<?php include(APPPATH."views/admin/shared/messages.php"); ?>
		<?php if ($languages !== null && count($languages)): ?>
		<div class="table-responsive">
			<table class="table table-striped table-hover table-bordered table-sort">
				<thead>
					<tr>
						<th><?= _("Language"); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
                        foreach ($languages as $item):
                    ?>
                            <tr>
                                <td><?= $item['code']; ?></td>
                            </tr>
					<?php 
                        endforeach;
                    ?>
				</tbody>
			</table>
		</div>
		<?php else: ?>
			<p class="text-info">
                <?= _("There are no languages installed for this whitelabel."); ?>
            </p>
		<?php endif; ?>
		</div>
	</div>
</div>