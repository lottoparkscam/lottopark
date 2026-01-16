<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?php echo _("Edit GTM settings"); ?></h2>
		<p class="help-block"><?php echo _("Here you can edit GTM settings."); ?></p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <?php include(APPPATH . "views/whitelabel/shared/messages.php"); ?>
				<form method="post" action="/analytics">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($this->errors['input.gtmid'])): echo ' has-error'; endif; ?>">
                        <label for="input_gtmid"><?php echo _("GTM Tracking ID"); ?>:</label>
                        <input 
                            type="text" 
                            value="<?php echo Input::post('input.gtmid') !== null 
                                ? Input::post('input.gtmid') 
                                : (isset($whitelabel['analytics']) ? $whitelabel['analytics'] : ''); ?>" 
                            class="form-control" 
                            id="input_gtmid" 
                            name="input[gtmid]" 
                            placeholder="<?php echo _('Enter GTM Tracking ID'); ?>"
                        >
                        <p class="help-block"><?php echo _('Sample format: <strong>GTM-</strong>. You will have to <a href="https://support.google.com/analytics/answer/3123666" target="_blank">enable User-ID</a> and <a href="https://support.google.com/analytics/answer/6032539?hl=en" target="_blank">Enhanced E-commerce</a> to take the full advantage of the GTM feature.'); ?></p>
                    </div>
                    <div class="form-group<?php if (isset($this->errors['input.gtmid_casino'])): echo ' has-error'; endif; ?>">
                        <label for="input_gtmid_casino"><?php echo _("Casino GTM Tracking ID"); ?>:</label>
                        <input
                            type="text"
                            value="<?php echo Input::post('input.gtmid_casino') !== null 
                                ? Input::post('input.gtmid_casino') 
                                : (isset($whitelabel['analytics_casino']) ? $whitelabel['analytics_casino'] : ''); ?>"
                            class="form-control"
                            id="input_gtmid_casino"
                            name="input[gtmid_casino]"
                            placeholder="<?php echo _('Enter Casino GTM Tracking ID'); ?>"
                        >
                        <p class="help-block"><?php echo _('Sample format: <strong>GTM-</strong>. You will have to <a href="https://support.google.com/analytics/answer/3123666" target="_blank">enable User-ID</a> and <a href="https://support.google.com/analytics/answer/6032539?hl=en" target="_blank">Enhanced E-commerce</a> to take the full advantage of the GTM feature.'); ?></p>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo _("Submit"); ?></button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
