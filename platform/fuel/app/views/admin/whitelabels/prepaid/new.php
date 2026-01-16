<?php 
    include(APPPATH . "views/admin/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php 
            include(APPPATH . "views/admin/whitelabels/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Prepaid"); ?>
        </h2>
        
		<p class="help-block">
            <?= _("You can add new prepaid transaction here."); ?>
        </p>
        
        <a href="<?= $urls['back']; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <form method="post" 
                      autocomplete="off" 
                      action="<?= $urls['form']; ?>">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/admin/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?= $amount_error_class; ?>">
                        <label class="control-label" for="inputAmount">
                            <?= _("Amount"); ?>:
                        </label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <?= $manager_currency_code; ?>
                            </div>
                            <input type="text" required="required" autofocus 
                                   value="<?= $amount_value; ?>" 
                                   class="form-control" id="inputAmount" 
                                   name="input[amount]" 
                                   placeholder="<?= _("Enter amount"); ?>">
                        </div>
                        <p class="help-block">
                            <?= $amount_help_text; ?>
                        </p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
