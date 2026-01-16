<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/aff/settings/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Edit profile"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
		<p class="help-block">
            <?= _("You can view and edit your details here."); ?>
        </p>
        <div class="container-fluid container-admin row">
			<?php include(APPPATH . "views/aff/shared/messages.php"); ?>
            
			<div class="col-md-6 user-details">
				<span class="details-label">
                    <?= Security::htmlentities(_("ID")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['token']) ? strtoupper($user['token']) : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_('Login')); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities($user['login']); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_('Company')); ?>:</span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['company']) ? $user['company'] : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("First Name")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['name']) ? $user['name'] : _("Anonymous")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Last Name")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['surname']) ? $user['surname'] : _("Anonymous")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("E-mail")); ?>:
                </span>
				<span class="details-value">
                    <span class="<?= Lotto_View::show_boolean_class($user['is_confirmed']); ?>">
                        <?= Lotto_View::show_boolean($user['is_confirmed']); ?>
                    </span> <?= Security::htmlentities($user['email']); ?>
                </span>
                <br>
				<?php /*
                <a href="/affs/list/email/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default"><span class="glyphicon glyphicon-edit"></span> <?= _("Edit E-mail"); ?></a><br>
                */ ?>
				<span class="details-label">
                    <?= Security::htmlentities(_("Country")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['country']) && !empty($countries[$user['country']]) ? $countries[$user['country']] : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("City")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['city']) ? $user['city'] : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Region")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['state']) ? Lotto_View::get_region_name($user['state']) : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Address #1")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['address_1']) ? $user['address_1'] : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Address #2")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['address_2']) ? $user['address_2'] : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Postal/ZIP Code")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['zip']) ? $user['zip'] : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Birthdate")); ?>:
                </span>
				<span class="details-value">
                    <?= !empty($user['birthdate']) ? Lotto_View::format_date($user['birthdate'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE) : _("-"); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Phone")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['phone']) && !empty($user['phone_country']) ? Lotto_View::format_phone($user['phone'], $user['phone_country']).(isset($countries[$user['phone_country']]) ? ' ('.$countries[$user['phone_country']].')' : '') : _("-")); ?>
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Time Zone")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['timezone'] && isset($timezones[$user['timezone']])) ? $timezones[$user['timezone']] : _("-")); ?> 
                </span>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Password")); ?>:
                </span>
				<span class="details-value">
                    **********
                </span>
				<a href="/settings/password" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-edit"></span> <?= _("Change Password"); ?>
                </a>
                <br>
				<span class="details-label">
                    <?= Security::htmlentities(_("Language")); ?>:
                </span>
				<span class="details-value">
                    <?= Security::htmlentities(!empty($user['language_id']) ? Lotto_View::format_language($languages[$user['language_id']]['code']) : _("-")); ?>
                </span>
                <br>
				<a href="/settings/edit" class="btn btn-success btn-mt">
                    <span class="glyphicon glyphicon-edit"></span> <?= _("Edit Details"); ?>
                </a>
                <br>
            </div>
        </div>
    </div>
</div>
