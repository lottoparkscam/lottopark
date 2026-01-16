<?php 
    include(APPPATH . "views/whitelabel/shared/navbar.php");
?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Affiliate details"); ?> <small><?= Security::htmlentities($user['email']); ?></small>
        </h2>
        <p class="help-block">
            <?= _("You can view and edit affiliate details here."); ?>
        </p>
        <a href="/affs<?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
            <span class="glyphicon glyphicon-chevron-left"></span> <?= _("Back"); ?>
        </a>
        <div class="container-fluid container-admin row">
            <?php include(APPPATH . "views/whitelabel/shared/messages.php"); ?>

            <div class="col-md-6 user-details">
                <span class="details-label">
                    <?= Security::htmlentities(_("ID")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities(!empty($user['token']) ? strtoupper($user['token']) : _("-")); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Parent")); ?>:
                </span>
				<span class="details-value">
                    <?php 
                        if (!empty($user['whitelabel_aff_parent_id'])):
                            if (!empty($rallaffs[$user['whitelabel_aff_parent_id']]['name']) ||
                                !empty($rallaffs[$user['whitelabel_aff_parent_id']]['surname'])
                            ):
                                echo Security::htmlentities($rallaffs[$user['whitelabel_aff_parent_id']]['name'].' '.$rallaffs[$user['whitelabel_aff_parent_id']]['surname']);
                            else:
                                echo _("anonymous");
                            endif;
                            
                            echo " &bull; ";
                            echo Security::htmlentities($rallaffs[$user['whitelabel_aff_parent_id']]['login']);
                        else:
                            echo Security::htmlentities(_("-"));
                        endif;
                    ?>
				</span><br>
				<span class="details-label">
                    Lottery Group
                </span>
                <span class="details-value">
                    <?= !empty($user['whitelabel_aff_group_id']) ? $lotteryGroups[$user['whitelabel_aff_group_id']]['name'] : 'Default Lottery Group' ?>
                </span>
                <br>
                <span class="details-label">
                    Casino Group
                </span>
                <span class="details-value">
                    <?= !empty($user['whitelabel_aff_casino_group_id']) ? $casinoGroups[$user['whitelabel_aff_casino_group_id']]['name'] : 'Default Casino Group' ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Company")); ?>:
                </span>
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
                    <?= Security::htmlentities(_("Login")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($user['login']); ?>
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
                <a href="/affs/list/email/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-edit"></span> <?= _("Edit E-mail"); ?>
                </a>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Country")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities(!empty($user['country']) ? $countries[$user['country']] ?? _("-") : _("-")); ?>
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
                    <?php 
                        if (!empty($user['birthdate'])):
                            echo Lotto_View::format_date($user['birthdate'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);
                        else:
                            echo "-";
                        endif;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Phone")); ?>:
                </span>
				<span class="details-value">
                    <?php 
                        $phone_text = "-";
                        if (!empty($user['phone']) && !empty($user['phone_country'])):
                            $phone_text = Lotto_View::format_phone($user['phone'], $user['phone_country']);
                            if (isset($countries[$user['phone_country']])):
                                $phone_text .= ' (' . $countries[$user['phone_country']] . ')';
                            endif;
                        endif;
                        echo Security::htmlentities($phone_text);
                    ?>
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
                <a href="/affs/list/password/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" class="btn btn-xs btn-default">
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
                <span class="details-label">
                    <?= Security::htmlentities(_("Date created")); ?>:
                </span>
                <span class="details-value">
                    <?= Lotto_View::format_date($user['date_created'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Last IP")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        $last_ip = "-";
                        if (!empty($user['last_ip'])):
                            $last_ip = $user['last_ip'];
                        endif;
                        echo  Security::htmlentities($last_ip);
                        
                        $last_country = '';
                        if (!empty($user['last_country'])):
                            $last_country = " (" . $countries[$user['last_country']] ?? '' . ")";
                        endif;
                        echo $last_country;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Last Active")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        $last_active = '-';
                        if (!empty($user['last_active'])):
                            $last_active = Lotto_View::format_date($user['last_active'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
                        endif;
                        echo $last_active;
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= $lead_lifetime_label ?>:
                </span>
                <span class="details-value">
                    <?= $lead_lifetime_value ?>
                </span>
                <br>
                <span class="details-label">
                    <?= $is_show_name_label ?>:
                </span>
                <span class="details-value">
                    <?= $is_show_name_value ?>
                </span>
                <br>
                <span class="details-label">
                    <?= $hide_lead_id_label ?>:
                </span>
                <span class="details-value">
                    <?= $hide_lead_id_value ?>
                </span>
                <br>
                <span class="details-label">
                    <?= $hide_transaction_id_label ?>:
                </span>
                <span class="details-value">
                    <?= $hide_transaction_id_value ?>
                </span>
                <br>
                <a href="/affs/list/edit/<?= $user['token']; ?><?= Lotto_View::query_vars(); ?>" class="btn btn-success btn-mt">
                    <span class="glyphicon glyphicon-edit"></span> <?= _("Edit Details"); ?>
                </a>
                <hr>
                <h2><?= _("Withdrawal details"); ?></h2>
                <br>
                <?php if (isset($withdrawal_data['name'])): ?>
                <span class="details-label">
                    <?= _("First name") ?>
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['name'] ?>
                </span>
                <br>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['surname'])): ?>
                <span class="details-label">
                    <?= _("Last name") ?>
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['surname'] ?>
                </span>
                <br>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['account_no'])): ?>
                <span class="details-label">
                    <?= _("Account IBAN number") ?>
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['account_no'] ?>
                </span>
                <br>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['account_swift'])): ?>
                <span class="details-label">
                    <?= _("SWIFT") ?>
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['account_swift'] ?>
                </span>
                <br>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['bank_name'])): ?>
                <span class="details-label">
                    <?= _("Bank name") ?>
                </span>
                <span class="details-value">
                    <?= $withdrawal_data['bank_name'] ?>
                </span>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['bitcoin'])): ?>
                    <span class="details-label">
                    <?= _("Bitcoin wallet address") ?>
                </span>
                    <span class="details-value">
                    <?= $withdrawal_data['bitcoin'] ?>
                </span>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['skrill_email'])): ?>
                    <span class="details-label">
                    <?= _("Skrill e-mail") ?>
                </span>
                    <span class="details-value">
                    <?= $withdrawal_data['skrill_email'] ?>
                </span>
                <?php endif; ?>
                <?php if (isset($withdrawal_data['neteller_email'])): ?>
                    <span class="details-label">
                    <?= _("Neteller e-mail") ?>
                </span>
                    <span class="details-value">
                    <?= $withdrawal_data['neteller_email'] ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

