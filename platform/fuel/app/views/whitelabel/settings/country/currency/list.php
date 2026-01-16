<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
    <div class="col-md-10">
        <h2>
            <?= _("Currencies for countries"); ?>
        </h2>
		<p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
		<div class="pull-right">
			<a href="<?= $urls["new"]; ?>" class="btn btn-success">
                <span class="glyphicon glyphicon-plus"></span> <?= _("Add New"); ?>
            </a>
		</div>
		<div class="btn-group" role="group">
            <a href="<?= $urls["currency"]; ?>" class="btn btn-default">
                <?= _("Available currencies"); ?>
            </a>
            <a href="<?= $urls["country_currency"]; ?>" class="btn btn-default active">
                <?= _("Defaults for countries"); ?>
            </a>
		</div>
        
        <div class="container-fluid container-admin">
            <?php 
                include(APPPATH . "views/whitelabel/shared/messages.php");

                if (!empty($countries_with_defaults) &&
                    count($countries_with_defaults) > 0
                ):
            ?>
                    <label class="control-label">
                        <?= _("List of countries with default currencies"); ?>
                    </label>
            
                    <table class="table table-bordered table-hover table-striped table-sort">
                        <thead>
                            <tr>
                                <th>
                                    <?= _("Country"); ?>
                                </th>
                                <th>
                                    <?= _("Default currency"); ?>
                                </th>
                                <th>
                                    <?= _("Action"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($countries_with_defaults as $country_with_defaults):
                            ?>
                                    <tr>
                                        <td>
                                            <?= $country_with_defaults["country_name"]; ?>
                                        </td>
                                        <td>
                                            <?= $country_with_defaults["currency_code"]; ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-success" 
                                               href="<?= $country_with_defaults["edit_url"]; ?>">
                                                <span class="glyphicon glyphicon-edit"></span> <?= _("Edit"); ?>
                                            </a>
                                            <button type="button" 
                                                    data-href="<?= $country_with_defaults["delete_url"]; ?>" 
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
            <?php
                else:
            ?>
                    <p class="text-info">
                        <?= _("No default currency definitions for countries."); ?>
                    </p>
            <?php 
                endif;
            ?>
        </div>
    </div>
    
</div>

<!-- Modals -->
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