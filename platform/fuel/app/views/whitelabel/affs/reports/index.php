<?php
include(APPPATH . "views/whitelabel/shared/navbar.php");

$manager_currency_tab = Helpers_Currency::get_mtab_currency(
    false,
    null,
    $this->whitelabel['manager_site_currency_id']
);
?>
<div class="container-fluid">
	<div class="col-md-2">
		<?php include(APPPATH . "views/whitelabel/affs/menu.php"); ?>
	</div>
	<div class="col-md-10">
		<h2>
            <?= _("Generate report"); ?>
        </h2>

        <p class="help-block">
            <?= _("Here you can generate a report for a given date range."); ?>
        </p>

        <?php
            include(APPPATH . "views/whitelabel/affs/reports/index_filters.php");
        ?>

		<div class="container-fluid container-admin">
            <?php
                include(APPPATH . "views/aff/shared/messages.php");

                if (isset($date_start)):
            ?>
                    <h3><?= _("Report"); ?></h3>
                    <span class="details-label">
                        <?= _("Start Date"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_date_without_timezone($date_start); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("End Date"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_date_without_timezone($date_end); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("Clicks"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number($clicks['count_all']); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("Unique clicks"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number($clicks['count_unique']); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("Leads"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number(isset($regcount) ? count($regcount) : ''); ?>
                    </span>
                    <br>
                    <span class="details-label">
                        <?= _("First-time purchases"); ?>: 
                    </span>
                    <span class="details-value">
                        <?= Lotto_View::format_number(isset($ftpcount) ? count($ftpcount) : ''); ?>
                    </span>
                    <br>
                  <span class="details-label">
                        <?= _('First-time deposits') ?>:
                    </span>
                  <span class="details-value">
                        <?= Lotto_View::format_number(isset($ftdCount) ? count($ftdCount) : ''); ?>
                    </span>
                  <br>
                    <span class="details-label">
                    <?= _("Total lottery commissions"); ?>:
                    </span>
                    <span class="details-value">
                        <?php
                            echo Lotto_View::format_currency(
                                $totalLotteryCommission,
                                $manager_currency_tab['code'],
                                true
                            );
                        ?>
                    </span>
                    <br>
                    <span class="details-label">
                    <?= _("Total casino commissions"); ?>:
                    </span>
                    <span class="details-value">
                        <?php
                            echo Lotto_View::format_currency(
                                $totalCasinoCommission,
                                $manager_currency_tab['code'],
                                true
                            );
                        ?>
                    </span>
                    <br>
                    <span class="details-label">
                    <?= _("Total commissions"); ?>:
                    </span>
                    <span class="details-value">
                        <?php
                            echo Lotto_View::format_currency(
                                $totalCommission,
                                $manager_currency_tab['code'],
                                true
                            );
                        ?>
                    </span>
                    <br>
            <?php
                endif;

                if (isset($regcount) && count($regcount) > 0):
            ?>
                    <h3>
                        <?= _("Leads"); ?>
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("#"); ?>
                                    </th>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th>
                                        <?= _("Active"); ?>
                                    </th>
                                    <th>
                                        <?= _("Expired"); ?>
                                    </th>
                                    <th>
                                        <?= _("User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Register country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Last country"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Registered"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("First purchase"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("First deposit"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($regcount as $key => $item):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $key+1; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['aff_name']) || !empty($item['aff_surname'])):
                                                        echo Security::htmlentities($item['aff_name'] . ' ' . $item['aff_surname']);
                                                    else:
                                                        echo _("anonymous");
                                                    endif;
                                                    echo " &bull; ";
                                                    echo Security::htmlentities($item['aff_login']);
                                                ?>
                                                <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['aff_is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['aff_is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['aff_email']); ?>
                                                <br>
                                                <a href="/affs?filter[id]=<?= strtoupper($item['aff_token']); ?>"
                                                   class="btn btn-xs btn-primary"><span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_active']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_active']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_expired']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_expired']); ?>
                                                </span>
                                            </td>
                                            <td>
                                            <?php
                                                if (!empty($item['name']) ||
                                                    !empty($item['surname'])
                                                ):
                                                    echo Security::htmlentities($item['name'].' '.$item['surname']);
                                                else:
                                                    echo _("anonymous");
                                                endif;
                                                echo " &bull; ";
                                                echo $whitelabel['prefix'].'U'.$item['token'];
                                            ?>
                                            <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['email']); ?>
                                                <?php
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="/users?filter[id]=<?= $item['token']; ?>"
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="/tickets?filter[userid]=<?= $item['token']; ?>"
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['country'])):
                                                        echo Security::htmlentities($countries[$item['country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['register_country'])):
                                                        echo Security::htmlentities($countries[$item['register_country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['last_country'])):
                                                        echo Security::htmlentities($countries[$item['last_country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($item['first_purchase']) ? Lotto_View::format_date($item['first_purchase'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($item['first_deposit']) ? Lotto_View::format_date($item['first_deposit'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                                            </td>
                                        </tr>
                                <?php
                                    endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
            <?php
                endif;

                if (isset($ftpcount) && count($ftpcount) > 0):
            ?>
                    <h3><?= _("First-time purchases"); ?></h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("#"); ?>
                                    </th>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th>
                                        <?= _("User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Register country"); ?>
                                    </th>
                                    <th>
                                        <?= _("Last country"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Registered"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("First purchase"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($ftpcount as $key => $item):
                                ?>
                                        <tr>
                                            <td>
                                                <?= $key+1; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['aff_name']) || !empty($item['aff_surname'])):
                                                        echo Security::htmlentities($item['aff_name'] . ' ' . $item['aff_surname']);
                                                    else:
                                                        echo _("anonymous");
                                                    endif;
                                                    echo " &bull; ";
                                                    echo Security::htmlentities($item['aff_login']);
                                                ?>
                                                <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['aff_is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['aff_is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['aff_email']); ?>
                                                <br>
                                                <a href="/affs?filter[id]=<?= strtoupper($item['aff_token']); ?>"
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                </a>
                                            </td>
                                            <td>
                                            <?php
                                                if (!empty($item['name']) ||
                                                    !empty($item['surname'])
                                                ):
                                                    echo Security::htmlentities($item['name'].' '.$item['surname']);
                                                else:
                                                    echo _("anonymous");
                                                endif;
                                                echo " &bull; ";
                                                echo $whitelabel['prefix'].'U'.$item['token'];
                                            ?>
                                            <br>
                                                <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['email']); ?>
                                                <?php
                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="/users?filter[id]=<?= $item['token']; ?>" class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="/tickets?filter[userid]=<?= $item['token']; ?>" class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['country'])):
                                                        echo Security::htmlentities($countries[$item['country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['register_country'])):
                                                        echo Security::htmlentities($countries[$item['register_country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    if (!empty($item['last_country'])):
                                                        echo Security::htmlentities($countries[$item['last_country']] ?? '');
                                                    endif;
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($item['first_purchase']) ? Lotto_View::format_date($item['first_purchase'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                                            </td>
                                        </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
            <?php
                endif;
            if (isset($ftdCount) && count($ftdCount) > 0):
                ?>
              <h3><?= _('First-time deposits') ?></h3>
              <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-sort">
                  <thead>
                  <tr>
                    <th>
                        <?= _("#"); ?>
                    </th>
                    <th>
                        <?= _("Affiliate"); ?>
                    </th>
                    <th>
                        <?= _("User"); ?>
                    </th>
                    <th>
                        <?= _("Country"); ?>
                    </th>
                    <th>
                        <?= _("Register country"); ?>
                    </th>
                    <th>
                        <?= _("Last country"); ?>
                    </th>
                    <th class="text-center">
                        <?= _("Registered"); ?>
                    </th>
                    <th class="text-center">
                        <?= _("First deposit"); ?>
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php
                  foreach ($ftdCount as $key => $item):
                      ?>
                    <tr>
                      <td>
                          <?= $key+1; ?>
                      </td>
                      <td>
                          <?php
                          if (!empty($item['aff_name']) || !empty($item['aff_surname'])):
                              echo Security::htmlentities($item['aff_name'] . ' ' . $item['aff_surname']);
                          else:
                              echo _("anonymous");
                          endif;
                          echo " &bull; ";
                          echo Security::htmlentities($item['aff_login']);
                          ?>
                        <br>
                        <span class="<?= Lotto_View::show_boolean_class($item['aff_is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['aff_is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['aff_email']); ?>
                        <br>
                        <a href="/affs?filter[id]=<?= strtoupper($item['aff_token']); ?>"
                           class="btn btn-xs btn-primary">
                          <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                        </a>
                      </td>
                      <td>
                          <?php
                          if (!empty($item['name']) ||
                              !empty($item['surname'])
                          ):
                              echo Security::htmlentities($item['name'].' '.$item['surname']);
                          else:
                              echo _("anonymous");
                          endif;
                          echo " &bull; ";
                          echo $whitelabel['prefix'].'U'.$item['token'];
                          ?>
                        <br>
                        <span class="<?= Lotto_View::show_boolean_class($item['is_confirmed']); ?>">
                                                    <?= Lotto_View::show_boolean($item['is_confirmed']); ?>
                                                </span> <?= Security::htmlentities($item['email']); ?>
                          <?php
                          if (!in_array($rparam, ["deleted", "inactive"])):
                              ?>
                            <br>
                            <a href="/users?filter[id]=<?= $item['token']; ?>" class="btn btn-xs btn-primary">
                              <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                            </a>
                            <br>
                            <a href="/tickets?filter[userid]=<?= $item['token']; ?>" class="btn btn-xs btn-primary">
                              <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                            </a>
                          <?php
                          endif;
                          ?>
                      </td>
                      <td>
                          <?php
                          if (!empty($item['country'])):
                              echo Security::htmlentities($countries[$item['country']] ?? '');
                          endif;
                          ?>
                      </td>
                      <td>
                          <?php
                          if (!empty($item['register_country'])):
                              echo Security::htmlentities($countries[$item['register_country']] ?? '');
                          endif;
                          ?>
                      </td>
                      <td>
                          <?php
                          if (!empty($item['last_country'])):
                              echo Security::htmlentities($countries[$item['last_country']] ?? '');
                          endif;
                          ?>
                      </td>
                      <td class="text-center">
                          <?= Lotto_View::format_date($item['date_register'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT); ?>
                      </td>
                      <td class="text-center">
                          <?= !empty($item['first_deposit']) ? Lotto_View::format_date($item['first_deposit'], IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT) : ''; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php
            endif;

                if (isset($commissions) && count($commissions) > 0):
            ?>
                    <h3><?= _("Commissions"); ?></h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sort">
                            <thead>
                                <tr>
                                    <th>
                                        <?= _("#"); ?>
                                    </th>
                                    <th>
                                        <?= _("Affiliate"); ?>
                                    </th>
                                    <th>
                                        <?= _("User"); ?>
                                    </th>
                                    <th>
                                        <?= _("Transaction ID"); ?>
                                    </th>
                                    <th>
                                        <?= _("Type"); ?>
                                    </th>
                                    <th class="text-center">
                                        <?= _("Date"); ?>
                                    </th>
                                    <th>
                                        <?= _("Amount"); ?>
                                    </th>
                                    <th class="text-nowrap">
                                        <?= _("Ticket cost"); ?>
                                        <br>
                                        <?= _("Payment cost"); ?>
                                    </th>
                                    <th>
                                        <?= _("Income"); ?>
                                    </th>
                                    <th>
                                        <?= _("Commission"); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    foreach ($commissions as $key => $commission):
                                ?>
                                        <tr>
                                            <td><?= $key+1; ?></td>
                                            <td>
                                                <?= $commission['aff_full_name']; ?>
                                                <br>
                                                <?= $commission['aff_is_confirmed_span']; ?>
                                                <?= $commission['aff_email']; ?>
                                                <br>
                                                <a href="<?= $commission['view_aff_url']; ?>"
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View affiliate"); ?>
                                                </a>
                                            </td>
                                            <td>
                                            <?= $commission['user_full_name']; ?>
                                            <br>
                                                <?= $commission['user_is_confirmed_span']; ?>
                                                <?php
                                                    echo $commission['user_email'];

                                                    if (!in_array($rparam, ["deleted", "inactive"])):
                                                ?>
                                                        <br>
                                                        <a href="<?= $commission['view_user_url']; ?>"
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View user"); ?>
                                                        </a>
                                                        <br>
                                                        <a href="<?= $commission['view_tickets_url']; ?>"
                                                           class="btn btn-xs btn-primary">
                                                            <span class="glyphicon glyphicon-th-list"></span> <?= _("View tickets"); ?>
                                                        </a>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?= $commission['transaction_id']; ?>
                                                <br>
                                                <a href="<?= $commission['view_transaction_url']; ?>"
                                                   class="btn btn-xs btn-primary">
                                                    <span class="glyphicon glyphicon-th-list"></span> <?= _("View transaction"); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= $commission['aff_type']; ?>
                                                <br>
                                                <?= $commission['tier']; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $commission['date_confirmed']; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $commission['amount_manager'];

                                                    if (!empty($commission['amounts_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign"
                                                                  data-toggle="tooltip"
                                                                  data-placement="top"
                                                                  title=""
                                                                  data-original-title="<?php
                                                                    echo $commission['amounts_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $commission['cost_manager'];

                                                    if (!empty($commission['costs_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign"
                                                                  data-toggle="tooltip"
                                                                  data-placement="top"
                                                                  title=""
                                                                  data-original-title="<?php
                                                                    echo $commission['costs_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                                <br>
                                                <?php
                                                    echo $commission['payment_cost_manager'];

                                                    if (!empty($commission['payment_costs_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign"
                                                                  data-toggle="tooltip"
                                                                  data-placement="top"
                                                                  title=""
                                                                  data-original-title="<?php
                                                                    echo $commission['payment_costs_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $commission['real_income_manager'];

                                                    if (!empty($commission['real_incomes_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign"
                                                                  data-toggle="tooltip"
                                                                  data-placement="top"
                                                                  title=""
                                                                  data-original-title="<?php
                                                                    echo $commission['real_incomes_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                    echo $commission['commission_manager'];

                                                    if (!empty($commission['commissions_other'])):
                                                ?>
                                                        <small>
                                                            <span class="glyphicon glyphicon-info-sign"
                                                                  data-toggle="tooltip"
                                                                  data-placement="top"
                                                                  title=""
                                                                  data-original-title="<?php
                                                                    echo $commission['commissions_other'];
                                                            ?>"></span>
                                                        </small>
                                                <?php
                                                    endif;
                                                ?>
                                            </td>
                                        </tr>
                                <?php
                                    endforeach;
                                ?>
                            </tbody>
                        </table>
                    </div>
            <?php
                endif;
        if (count($casinoCommissions ?? []) > 0):
            ?>
                <h3>
                    <?= _("Casino Commissions"); ?>
                </h3>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-sort">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>
                                    <?= _("Affiliate"); ?>
                                </th>
                                <th>
                                    <?= _("Lead"); ?>
                                </th>
                                <th>
                                    <?= _("Lead Email") ?>
                                </th>
                                <th class="text-center">
                                    <?= _("Date"); ?>
                                </th>
                                <th>
                                    <?= _('GGR') ?>
                                </th>
                                <th>
                                    <?= _("Commission (NGR)"); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($casinoCommissions as $key => $commission): ?>
                                <tr>
                                    <td><?= $key+1 ?>.</td>
                                    <td><?= $commission['aff_full_name']; ?></td>
                                    <td><?= $commission['lead_full_name']; ?></td>
                                    <td><?= $commission['lead_email']; ?></td>
                                    <td class="text-center"><?= $commission['created_at']; ?></td>
                                    <td>
                                        <?= $commission['show_ggr'] ? $commission['ggr'] :'N\A'; ?>
                                        <?php if (!empty($commission['ggrInUserCurrency'])): ?>
                                            <small>
                                                <span class="glyphicon glyphicon-info-sign"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    title=""
                                                    data-original-title="<?= $commission['ggrInUserCurrency'];?>">
                                                </span>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                    <?php
                                        if (!empty($commission['show_commission'])):
                                            echo $commission['commission'];
                                            if (!empty($commission['commissions_other'])):
                                    ?>
                                            <small>
                                                <span class="glyphicon glyphicon-info-sign"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    title=""
                                                    data-original-title="<?= $commission['commissions_other'];?>">
                                                </span>
                                            </small>
                                    <?php endif; endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
		</div>
	</div>
</div>