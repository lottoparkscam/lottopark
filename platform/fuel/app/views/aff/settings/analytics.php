<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Edit Google Analytics settings"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can edit Google Analytics settings."); ?>
        </p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
            	<?php include(APPPATH . "views/aff/shared/messages.php"); ?>
                <form method="post" action="/analytics">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/aff/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($this->errors['input.gaid'])): echo ' has-error'; endif; ?>">
                        <label for="inputGAID">
                            <?= _("Google Analytics Tracking ID"); ?>:
                        </label>
                        <input type="text" 
                               value="<?= Input::post("input.gaid") !== null ? Input::post("input.gaid") : (isset($user) && isset($user['analytics']) ? $user['analytics'] : ''); ?>" 
                               class="form-control" 
                               id="inputGAID" 
                               name="input[gaid]" 
                               placeholder="<?= _("Enter Google Analytics Tracking ID"); ?>">
                        <p class="help-block">
                            <?php
                                echo _(
                        'Sample format: <strong>UA-XXXXX-Y</strong>. ' .
                                    'You will see the data related to your leads only. ' .
                                    'You will have to <a href="https://support.google.com/analytics/answer/3123666" ' .
                                    'target="_blank">enable User-ID</a>* and <a href="https://support.google.com/analytics/answer/6032539?hl=en" target="_blank">Enhanced E-commerce</a>* to take the ' .
                                    'full advantage of the Google Analytics feature. * The above functionalities may not be fully available. Please contact support for details.'
                                );
                            ?>
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
</div>
