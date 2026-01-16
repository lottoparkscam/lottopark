<?php

use Models\Whitelabel;

include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php
            include(APPPATH . "views/whitelabel/users/menu.php");
        ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("User details"); ?> <small><?= $user_data['email']; ?></small>
        </h2>
		<p class="help-block">
            <?= _("You can view and edit user details here."); ?>
        </p>
		<a href="<?= $users_urls['main']; ?>" 
           class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
			<div class="container-fluid container-admin row">
                <?php
                    include(APPPATH . "views/whitelabel/shared/messages.php");
                ?>
                <div class="col-md-6 user-details">
                    <span class="details-label">
                        <?= Security::htmlentities(_("ID")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['id']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("First Name")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['name']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Last Name")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['surname']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("E-mail")); ?>:
                    </span>
                    <span class="details-value">
                        <span class="<?= $user_data['is_confrimed_class']; ?>">
                            <?= $user_data['is_confrimed_value']; ?>
                        </span> <?= $user_data['email']; ?>
                    </span>
                    <a href="<?= $users_urls['email_edit']; ?>" 
                       class="btn btn-xs btn-default">
                        <span class="glyphicon glyphicon-edit"></span> <?= _("Edit E-mail"); ?>
                    </a>
                    <br>
                    <?php
                        /** @var Whitelabel $whitelabelModel */
                        $whitelabelModel = Container::get('whitelabel');
                        if ($whitelabelModel->loginForUserIsUsedDuringRegistration()):
                    ?>
                        <span class="details-label">
                        <?= Security::htmlentities(_("Login")); ?>:
                        </span>
                        <span class="details-value">
                            <?= $user_data['login']; ?>
                        </span>
                        <br>
                    <?php endif; ?>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Country")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['country']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("City")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['city']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Region")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['state']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Address #1")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['address_1']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Address #2")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['address_2']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Postal/ZIP Code")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['zip']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Birthdate")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['birthdate']; ?>
                    </span><br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Phone")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['phone']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Time Zone")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['timezone']; ?> 
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Gender")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['gender']; ?> 
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("National ID")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['national_id']; ?> 
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Password")); ?>:
                    </span>
                    <span class="details-value">
                        **********
                    </span>
                    <a href="<?= $users_urls['password']; ?>" 
                       class="btn btn-xs btn-default">
                        <span class="glyphicon glyphicon-edit"></span> <?= _("Change Password"); ?>
                    </a>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Language")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['language']; ?>
                    </span><br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Balance")); ?>:
                    </span>
                    <span class="details-value">
                        <?php
                            echo $user_data['balance_in_manager'];
                            
                            if ($user_data['show_user_balance']):
                                $balance_user = _("User currency") .
                                    ": " . $user_data['balance'];
                        ?>
                                <small>
                                    <span class="glyphicon glyphicon-info-sign" 
                                          data-toggle="tooltip" 
                                          data-placement="top" 
                                          title="" 
                                          data-original-title="<?php
                                            echo $balance_user;
                                    ?>"></span>
                                </small>
                        <?php
                            endif;
                        ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Bonus balance")); ?>:
                    </span>
                    <span class="details-value">
                        <?php
                            echo $user_data['bonus_balance_in_manager'];
                            
                            if ($user_data['show_user_balance']):
                                $bonus_balance_user = _("User currency") .
                                    ": " . $user_data['bonus_balance'];
                        ?>
                                <small>
                                    <span class="glyphicon glyphicon-info-sign" 
                                          data-toggle="tooltip" 
                                          data-placement="top" 
                                          title="" 
                                          data-original-title="<?php
                                            echo $bonus_balance_user;
                                    ?>"></span>
                                </small>
                        <?php
                            endif;
                        ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Date register")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['date_register']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Register IP")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['register_ip']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Last IP")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['last_ip']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Last Active")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['last_active']; ?>
                    </span>
                        <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("Last Update")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['last_update']; ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= Security::htmlentities(_("First Purchase")); ?>:
                    </span>
                    <span class="details-value">
                        <?= $user_data['first_purchase']; ?>
                    </span>
                    <br>
                    <a href="<?= $users_urls['edit']; ?>" 
                       class="btn btn-success btn-mt">
                        <span class="glyphicon glyphicon-edit"></span> <?= _("Edit Details"); ?>
                    </a>
                    <br>
                </div>
            </div>
		</div>
	</div>
</div>
