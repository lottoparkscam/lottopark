<?php 
include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/admin/whitelabels/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?= $title_text; ?></h2>
		<p class="help-block">
            <?= $main_help_block_text; ?>
        </p>
        
		<a href="<?= $urls["country_currency"]; ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" autocomplete="off" action="<?= $urls["form"]; ?>">
                    <?php 
                        if (!empty($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="control-label" >
                                <?= _("Country"); ?>:
                            </label>
                            <select name="input[country]" 
                                    id="inputCountry" 
                                    class="form-control">
                                <?php
                                    foreach ($available_countries as $country):
                                ?>
                                        <option value="<?= $country["code"]; ?>" 
                                                <?= $country["is_selected"]; ?>>
                                            <?= $country["name"]; ?>
                                        </option>
                                <?php 
                                    endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="control-label" >
                                <?= _("Available currencies"); ?>:
                            </label>
                            <select name="input[defaultcurrency]" 
                                    id="inputSiteCurrency" 
                                    class="form-control">
                                <?php 
                                    foreach ($available_currencies as $available_currency):
                                ?>
                                        <option value="<?= $available_currency['id']; ?>" 
                                                <?= $available_currency['is_selected']; ?> >
                                            <?= $available_currency['currency_code']; ?>
                                        </option>
                                <?php
                                    endforeach;
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</div>