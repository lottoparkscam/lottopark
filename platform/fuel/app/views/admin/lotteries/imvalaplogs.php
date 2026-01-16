<?php use Helpers\SanitizerHelper;

include(APPPATH."views/admin/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH."views/admin/lotteries/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= _("Lotteries"); ?> <small><?= _("Imvalap Logs"); ?></small></h2>
		<p class="help-block"><?= _("Here you can find all logs related to Imvalap provider. Logs are kept for 1 month."); ?></p>
		<form class="form-inline" method="get" action="/lotteries/imvalaplogs">
  			<div class="form-group text-nowrap">
  				<label class="control-label" for="filterRange"><?= _("Date Range"); ?>:</label>
				<div class="input-group input-daterange datepicker" data-date-start-date="-7d" data-date-end-date="0d">
				    <input id="filterRange" name="filter[range_start]" type="text" class="form-control" 
                           value="<?= SanitizerHelper::sanitizeString(!empty(Input::get("filter.range_start")) ?
                               Input::get("filter.range_start") : ''); ?>">
				    <span class="input-group-addon"><?= _("to"); ?></span>
				    <input type="text" name="filter[range_end]" class="form-control" value="<?= !empty(Input::get("filter.range_start")) ? Security::htmlentities(Input::get("filter.range_end")) : ''; ?>">
				</div>
			</div>
			<div class="form-group">
				<label for="filterWhitelabel"><?= _("Whitelabel"); ?>:</label>
				<select name="filter[whitelabel]" id="filterWhitelabel" class="form-control">
					<option value="0"><?= _("All"); ?></option>
					<?php foreach ($whitelabels as $whitelabel): ?>
						<option value="<?= $whitelabel['id']; ?>"<?php if (Input::get("filter.whitelabel") == $whitelabel['id']): echo ' selected="selected"'; endif; ?>>
                            <?= Security::htmlentities($whitelabel['name']); ?>
                        </option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="form-group">
				<label for="filterType"><?= _("Type"); ?>:</label>
				<select name="filter[type]" id="filterType" class="form-control">
					<option value="-1"><?= _("All"); ?></option>
					<?php for ($i = 0; $i < 4; $i++): ?>
						<option value="<?= $i; ?>"<?php if (Input::get("filter.type") === "$i"): echo ' selected="selected"'; endif; ?>>
                            <?= Security::htmlentities(Lotto_View::type_to_name($i)); ?>
                        </option>
					<?php endfor; ?>
				</select>
			</div>
			<button type="submit" class="btn btn-primary"><?= _("Filter"); ?></button>
		</form>
		<div class="container-fluid container-admin">
		<?php if ($logs !== null && count($logs)): ?>
			<?= $pages->render(); ?>
			<div class="table-responsive">
			<table class="table table-hover">
			<thead>
				<tr>
					<th><?= _("Type"); ?></th>
					<th><?= _("Date"); ?></th>
					<th><?= _("Whitelabel"); ?></th>
					<th class="text-nowrap"><?= _("Ticket ID"); ?></th>
					<th><?= _("Job ID"); ?></th>
					<th><?= _("Message"); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($logs as $item): ?>
				<tr class="<?= Lotto_View::type_to_class($item['type']); ?> text-<?= Lotto_View::type_to_class($item['type']); ?>">
					<td>
						<strong><?= Lotto_View::type_to_name($item['type']); ?></strong>
					</td>
					<td class="text-nowrap">
						<?= Lotto_View::format_date($item['date']); ?>
					</td>
					<td class="text-nowrap">
						<?= Security::htmlentities($item['name']); ?>
					</td>
					<td>
						<?php if (!empty($item['whitelabel_user_ticket_id'])): ?>
							<?= intval($item['whitelabel_user_ticket_id']); ?><br>
							<?= $whitelabel['prefix'].'T'.$item['token']; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if (!empty($item['jobid'])): ?>
							<?= Security::htmlentities($item['jobid']); ?>
						<?php endif; ?>
					</td>
					<td>
						<?= Security::htmlentities($item['message']); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
			</div>
			<?= $pages->render(); ?>
		<?php else: ?>
			<p class="text-info"><?= _("There are no logs for this criteria."); ?></p>
		<?php endif; ?>
		</div>
	</div>
</div>