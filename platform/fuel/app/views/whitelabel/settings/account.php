<?php include(APPPATH . "views/whitelabel/shared/navbar.php"); ?>
<div class="container-fluid">
    <div class="col-md-2">
        <?php include(APPPATH . "views/whitelabel/settings/menu.php"); ?>
    </div>
    <div class="col-md-10">
        <h2>
            <?= _("Account settings"); ?> 
        </h2>
        <p class="help-block">
            <?= _("You can change your account settings here."); ?>
        </p>
        <div class="container-fluid container-admin row">
            <?php include(APPPATH . "views/whitelabel/shared/messages.php"); ?>
            <div class="col-md-6 user-details">
                <span class="details-label">
                    <?= Security::htmlentities(_("Username")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($whitelabel['username']); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Name")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($whitelabel['realname']); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("E-mail")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($whitelabel['email']); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Password")); ?>:
                </span>
                <span class="details-value">
                    **********
                </span>
                <a href="/account/password" class="btn btn-xs btn-default">
                    <span class="glyphicon glyphicon-edit"></span> <?= _("Change Password"); ?>
                </a>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Time Zone")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        $timezone_value = "";
                        if (!empty($whitelabel['timezone'] &&
                            isset($timezones) &&
                            isset($timezones[$whitelabel['timezone']]))
                        ) {
                            $timezone_value = $timezones[$whitelabel['timezone']];
                        } elseif (!empty($timezones['UTC'])) {
                            $timezone_value = $timezones['UTC'];
                        }
                        echo Security::htmlentities($timezone_value);
                    ?> 
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Language")); ?>:
                </span>
                <span class="details-value">
                    <?php
                        $language_value = "-";
                        if (!empty($whitelabel['language_id']) &&
                            !empty($languages) &&
                            !empty($languages[$whitelabel['language_id']]['code'])
                        ) {
                            $language_value = Lotto_View::format_language($languages[$whitelabel['language_id']]['code']);
                        }
                        echo Security::htmlentities($language_value);
                    ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Maximum order items")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($whitelabel['max_order_count']); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Last login")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($whitelabel['last_login']); ?>
                </span>
                <br>
                <span class="details-label">
                    <?= Security::htmlentities(_("Last active")); ?>:
                </span>
                <span class="details-value">
                    <?= Security::htmlentities($whitelabel['last_active']); ?>
                </span>

                <br>
                
                <?php
                    if ($edit_manager_currency):
                ?>
                        <span class="details-label">
                            <?= Security::htmlentities(_("Manager currency")); ?>:
                        </span>
                        <span class="details-value">
                            <?php
                                $manager_currency_value = "-";

                                if (!empty($whitelabel['manager_site_currency_id'])) {
                                    $currency_result = Model_Currency::find_by_id($whitelabel['manager_site_currency_id']);

                                    if (!empty($currency_result) &&
                                        count($currency_result) > 0
                                    ) {
                                        $manager_currency_value = $currency_result[0]['code'];
                                    }
                                }
                                echo Security::htmlentities($manager_currency_value);
                            ?>
                        </span>
                        <br>
                <?php
                    endif;
                ?>
                
                <a href="/account/edit" class="btn btn-success btn-mt">
                    <span class="glyphicon glyphicon-edit"></span> <?= _("Edit Details"); ?>
                </a>
                <br>
            </div>
        </div>
    </div>
</div>
