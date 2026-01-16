<?php
if (isset($withdrawals) && count($withdrawals) > 0):
    $user_timezone = get_user_timezone();
?>
    <hr class="separator">
    <div class="header-withdrawal">
        <?= _("Withdrawal List"); ?>
    </div>
    <table class="table table-withdrawals">
        <thead>
            <tr>
                <th class="text-left">
                    <?= Security::htmlentities(_("Withdrawal ID")); ?>
                </th>
                <th>
                    <?= Security::htmlentities(_("Amount")); ?>
                </th>
                <th>
                    <?= Security::htmlentities(_("Date")); ?>
                </th>
                <th class="text-left">
                    <?= Security::htmlentities(_("Type")); ?>
                </th>
                <th class="text-left">
                    <?= Security::htmlentities(_("Status")); ?>
                </th>
                <th>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($withdrawals as $withdrawal):
                    $withdrawal_token = $whitelabel['prefix'] .
                        'W' .
                        $withdrawal['token'];
                    $withdrawal_token_id = Security::htmlentities($withdrawal_token);
                
                    $withdrawal_amount = Lotto_View::format_currency(
                        $withdrawal['amount'],
                        $currencies[$withdrawal['currency_id']]['code'],
                        true
                    );
                    $withdrawal_amount_text = Security::htmlentities($withdrawal_amount);
                    
                    $withdrawal_date = Lotto_View::format_date(
                        $withdrawal['date'],
                        IntlDateFormatter::SHORT,
                        IntlDateFormatter::SHORT,
                        $user_timezone
                    );
                    $withdrawal_date_text = Security::htmlentities($withdrawal_date);
                    
                    $withdrawal_status_class = "transactions-status-";
                    $withdrawal_status_class .= htmlspecialchars($withdrawal['status']);
                    
                    $withdrawal_status_text = "";
                    switch ($withdrawal['status']) {
                        case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING:
                            $withdrawal_status_text = Security::htmlentities(_("pending"));
                            break;
                        case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED:
                            $withdrawal_status_text = Security::htmlentities(_("approved"));
                            break;
                        case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED:
                            $withdrawal_status_text = Security::htmlentities(_("declined"));
                            break;
                        case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED:
                            $withdrawal_status_text = Security::htmlentities(_("canceled"));
                            break;
                    }
            ?>
                    <tr>
                        <td class="transactions-id">
                            <?= $withdrawal_token_id; ?>
                        </td>
                        <td class="text-center">
                            <span class="mobile-only-label mobile-unbold">
                                <?= Security::htmlentities(_("Amount")); ?>:
                            </span>
                            <span class="transactions-amount">
                                <?= $withdrawal_amount_text; ?>
                            </span>
                        </td>
                        <td class="text-center transactions-date">
                            <span class="mobile-only-label">
                                <?= Security::htmlentities(_("Date"));?>:
                            </span>
                            <?= $withdrawal_date_text; ?>
                        </td>
                        <td class="transactions-method">
                            <span class="mobile-only-label">
                                <?= Security::htmlentities(_("Type")); ?>:
                            </span> 
                            <?= Security::htmlentities(_($withdrawal['name'])); ?>
                        </td>
                        <td class="transactions-status">
                            <span class="mobile-only-label">
                                <?= Security::htmlentities(_("Status")); ?>:
                            </span>
                            <span class="<?= $withdrawal_status_class; ?>">
                                <?= $withdrawal_status_text; ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="/account/withdrawal/details/<?= $withdrawal['token'] ?>" class="tooltip tooltip-bottom" data-tooltip="Details" style="position: relative;">
                                <span class="fa fa-search"></span>
                            </a>
                        </td>
                    </tr>
            <?php
                endforeach;
            ?>
        </tbody>
    </table>
<?php
    include('myaccount_pagination.php');
endif;
