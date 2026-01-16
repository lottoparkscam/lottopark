<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Edit Facebook Pixel settings"); ?>
        </h2>
		<p class="help-block">
            <?= _("Here you can edit Facebook Pixel settings."); ?>
        </p>
        <div class="container-fluid container-admin row">
            <div class="col-md-6">
            	<?php include(APPPATH . "views/aff/shared/messages.php"); ?>
                <form method="post" action="/fbpixel">
                    <?php
                        if (isset($this->errors)) {
                            include(APPPATH . "views/aff/shared/errors.php");
                        }
                    ?>
                    <div class="form-group<?php if (isset($this->errors['input.fbpixel'])): echo ' has-error'; endif; ?>">
                        <label for="inputFBID"><?php echo _("Facebook Pixel ID"); ?>:</label>
                        <input type="text" value="<?php echo Input::post("input.fbpixel") !== null ? Input::post("input.fbpixel") : (isset($user['fb_pixel']) ? $user['fb_pixel'] : ''); ?>" class="form-control" id="inputFBID" name="input[fbpixel]" placeholder="<?php echo _("Enter Facebook Pixel ID"); ?>">
                    </div>
                    <?php if(!$user['hide_lead_id'] && $user['is_show_name']): ?>
                    <div class="checkbox">
                        <label><input type="checkbox" name="input[fbmatch]" value="1"<?php if ((null !== Input::post("input.fbmatch") && Input::post("input.fbmatch") == 1) || (Input::post("input.fbmatch") === null && $user['fb_pixel_match'] == 1)): echo ' checked="checked"'; endif; ?>><?php echo _("Activate advanced matching."); ?></label>
                        <p class="help-block"><?php echo _("Send extended user metadata for better matching."); ?></p>
                    </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?= _("Submit"); ?>
                    </button>
				</form>
			</div>
        </div>
    </div>
</div>
</div>
