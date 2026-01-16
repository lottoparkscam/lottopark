<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2><?php echo _("Edit Facebook Pixel settings"); ?></h2>
		<p class="help-block"><?php echo _("Here you can edit Facebook Pixel settings."); ?></p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
                <?php include(APPPATH . "views/whitelabel/shared/messages.php"); ?>
				<form method="post" action="/fbpixel">
                    <?php 
                        if (isset($this->errors)) {
                            include(APPPATH . "views/whitelabel/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($this->errors['input.fbpixel'])): echo ' has-error'; endif; ?>">
                        <label for="inputFBID"><?php echo _("Facebook Pixel ID"); ?>:</label>
                        <input type="text" value="<?php echo Input::post("input.fbpixel") !== null ? Input::post("input.fbpixel") : (isset($whitelabel['fb_pixel']) ? $whitelabel['fb_pixel'] : ''); ?>" class="form-control" id="inputFBID" name="input[fbpixel]" placeholder="<?php echo _("Enter Facebook Pixel ID"); ?>">
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="input[fbmatch]" value="1"<?php if ((null !== Input::post("input.fbmatch") && Input::post("input.fbmatch") == 1) || (Input::post("input.fbmatch") === null && $whitelabel['fb_pixel_match'] == 1)): echo ' checked="checked"'; endif; ?>><?php echo _("Activate advanced matching."); ?></label>
                        <p class="help-block"><?php echo _("Send extended user metadata for better matching."); ?></p>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo _("Submit"); ?></button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
