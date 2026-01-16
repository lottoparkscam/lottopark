<?php

use Helpers\UrlHelper;
use Fuel\Core\Input;
use Fuel\Core\Security;

$action = (string)get_query_var('action');
$isDetailsTab = $action === "details";
$isLotteryTransactions = $action === "" && !IS_CASINO;
$isCasinoTransactions = IS_CASINO;
$tab = empty(Input::get('deposit')) ? 'transaction' : 'deposit';
$isDepositTab = $tab === 'deposit';
$isTransactionTab = !$isDepositTab;
$depositTypeId = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
$hasTransactions = (!empty($transactions) && count($transactions) > 0) || (!empty($deposits) && $isDepositTab);
$transactionsCount = !empty($transactions) && is_array($transactions) ? count($transactions) : 0;
$lotteryDepositsCount = $isLotteryTransactions ? array_search(Helpers_General::TYPE_TRANSACTION_DEPOSIT, array_column($transactions, 'type')) : [];
$lotteryTransactionsCount = $isLotteryTransactions ? array_search(!Helpers_General::TYPE_TRANSACTION_DEPOSIT, array_column($transactions, 'type')) : [];
$hasOnlyDeposits = $isLotteryTransactions && $lotteryDepositsCount === $transactionsCount;
if ($isDetailsTab) :
    if (isset($transaction)) :
?>
        <div class="myaccount-transactions">
            <span class="myaccount-transactions-label">
                <?= $to_show['status_label']; ?>:
            </span>
            <span class="myaccount-transactions-value">
                <span class="transactions-status transactions-status-<?php
                                                                        echo htmlspecialchars($transaction['status']);
                                                                        ?>">
                    <?= $to_show['status_value']; ?>
                </span>
            </span>
            <br>
            <span class="myaccount-transactions-label">
                <?= $to_show['date_label']; ?>:
            </span>
            <span class="myaccount-transactions-value">
                <?= $to_show['date_value']; ?>
            </span>
            <br>
            <span class="myaccount-transactions-label">
                <?= $to_show['confirm_date_label']; ?>:
            </span>
            <span class="myaccount-transactions-value">
                <?= $to_show['confirm_date_value']; ?>
            </span>
            <br>
            <?php
            if (!empty($transaction['payment_method_type'])) :
            ?>
                <span class="myaccount-transactions-label">
                    <?= $to_show['payment_label']; ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <?= $to_show['payment_method_type_value']; ?>
                </span>
                <br>
            <?php
            endif;
            ?>
            <span class="myaccount-transactions-label">
                <?= $to_show['amount_label']; ?>:
            </span>
            <span class="myaccount-transactions-value">
                <?php if ((int)$transaction['payment_method_type'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) : ?>
                    <span class="transactions-amount">
                        <?= $to_show['bonus_amount']; ?>
                    </span>
                    <span class="info-circle fa fa-info-circle tooltip tooltip-bottom" data-tooltip="<?= _("Paid with bonus balance."); ?>">
                    </span>
                <?php else : ?>
                    <span class="transactions-amount">
                        <?= $to_show['amount']; ?>
                    </span>
                <?php endif; ?>
            </span>
            <br>
            <?php
            if (
                !empty($transaction['payment_currency_id']) &&
                isset($transaction['amount_payment']) &&
                (int)$transaction['currency_id'] !== (int)$transaction['payment_currency_id']
            ) :
            ?>
                <span class="myaccount-transactions-label">
                    <?= $to_show['amount_payment_label']; ?>:
                </span>
                <span class="myaccount-transactions-value">
                    <span class="transactions-amount">
                        <?= $to_show['amount_payment']; ?>
                    </span>
                </span>
                <br>
            <?php
            endif;
            ?>
        </div>
    <?php
    else :
        $incorr_text = _("Incorrect transaction.");
        $incorrect_text = Security::htmlentities($incorr_text);
    ?>
        <div class="platform-alert platform-alert-error tickets-alert-error">
            <?= $incorrect_text; ?>
        </div>
    <?php
    endif;

    if (
        isset($transaction) &&
        (int)$transaction['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE &&
        (isset($tickets) && count($tickets) > 0)
    ) :
        $class_table = ' table-hover';
    ?>
        <div class="header-transaction">
            <?= _("Transaction details"); ?>
        </div>

        <table class="table table-payment<?= $class_table; ?>">
            <?php
            // I don't think that there is situation that code could enter in that if
            // statement - maybe it is future feature?
            if ((int)$transaction['type'] === Helpers_General::TYPE_TRANSACTION_DEPOSIT) :
            ?>
                <tbody>
                    <tr>
                        <td>
                            <div class="order-summary-content">
                                <span class="order-summary-content-header">
                                    <?= $deposit_to_show['deposit_order_header']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="text-right col-amount">
                            <?= $deposit_to_show['deposit_amount']; ?>
                        </td>
                    </tr>
                </tbody>
            <?php
            elseif ((int)$transaction['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE) :
            ?>
                <tbody>
                    <?php
                    foreach ($tickets_to_show as $ticket_to_show) :
                    ?>
                        <tr>
                            <td>
                                <div class="order-summary-image">
                                    <img src="<?= $ticket_to_show['lottery_image']; ?>" alt="<?= $ticket_to_show['lottery_image_alt']; ?>">
                                </div>
                            </td>
                            <td>
                                <div class="order-summary-content">
                                    <span class="order-summary-content-header">
                                        <?= $ticket_to_show['ticket_text']; ?>
                                    </span>
                                    <span class="order-summary-content-desc">
                                        <?= $ticket_to_show['ticket_lines_text']; ?>
                                    </span>
                                    <br>
                                </div>
                            </td>
                            <td class="text-right col-amount">
                                <?= $ticket_to_show['unit_price']; ?>
                                <?php if ((int)$transaction['payment_method_type'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) : ?>
                                    <span class="info-circle fa fa-info-circle tooltip tooltip-bottom" data-tooltip="<?= _("Paid with bonus balance."); ?>">
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?= $ticket_to_show['ticket_details_url']; ?>" class="tooltip tooltip-bottom" data-tooltip="<?= $ticket_to_show['ticket_tooltip_text']; ?>">
                                    <span class="fa fa-search"></span>
                                </a>
                            </td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right payment-summary-text">
                            <?= $total_sum_to_show['text']; ?>:
                            <span>
                                <?= $total_sum_to_show['value']; ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            <?php
            endif;
            ?>
        </table>
    <?php
    endif;

    $back_trans_url = UrlHelper::esc_url($transaction_link . Lotto_View::query_vars());
    $back_trans_text = _("Return to the transaction list");
    ?>
    <a href="<?= $back_trans_url; ?>" class="btn btn-primary btn-md">
        <?= $back_trans_text; ?>
    </a>
<?php
else :
    $balance = IS_CASINO ? $user['casino_balance'] : $user['balance'];
    $balance_with_curr = Lotto_View::format_currency(
        $balance,
        lotto_platform_user_currency(),
        true
    );
    $myaccount_balance_value = Security::htmlentities($balance_with_curr);

    $withdrawal_url = $accountlink . "withdrawal/";
    $balanceField = IS_CASINO ? 'casino_balance' : 'balance';
    $withdrawal_class = "";
    if ($user[$balanceField] <= 0) {
        $withdrawal_class = ' disabled';
    }

?>
    <div class="myaccount-balance pull-left">
        <?= _("Account balance"); ?>:
        <span>
            <?= $myaccount_balance_value; ?>
        </span>
    </div>
    <a href="<?= $withdrawal_url; ?>" class="btn btn-primary btn-withdrawal pull-left<?= $withdrawal_class; ?>">
        <?= _("Withdrawal"); ?>
    </a>

    <div class="clearfix"></div>

    <form class="myaccount-transactions-menu">
        <input class="myaccount-transactions-menu-item <?= $isDepositTab ? 'active' : '' ?>" type="submit" name="deposit" value="<?= _("Deposits") ?>">
        <input class="myaccount-transactions-menu-item <?= $isTransactionTab ? 'active' : '' ?>" type="submit" name="transaction" value="<?= _("Transactions") ?>">
    </form>

    <?php
    echo lotto_platform_messages();
    if ($hasTransactions) :
    ?>
        <div class="mobile-only-tickets pull-right">
            <label for="myaccount-tickets-mobile-sort" class="table-sort-label">
                <?= Security::htmlentities(_("Sort by")); ?>:
            </label>
            <select id="myaccount-tickets-mobile-sort" class="myaccount-tickets-mobile-sort">
                <?php
                foreach ($sortOptions as $key => $sortOption) :
                ?>
                    <option value="<?= $sortOption['value']; ?>" <?= $sortOption['select']; ?>>
                        <?= $sortOption['text']; ?>
                    </option>
                <?php
                endforeach;
                ?>
            </select>
        </div>

        <div class="clearfix"></div>
        <?php if ($isLotteryTransactions) :
            if (($isTransactionTab && !$hasOnlyDeposits) || $isDepositTab) : ?>
                <table class="table table-transactions clickable table-sort">
                    <thead>
                        <tr>
                            <th class="text-left">
                                <?= _("Transaction ID"); ?>
                            </th>
                            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['amount']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['amount']['link']); ?>">
                                <?= _("Amount"); ?>
                            </th>
                            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['date']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['date']['link']); ?>">
                                <?php
                                echo _("Date");
                                echo "&nbsp;";
                                echo _("(confirmed)");
                                ?>
                            </th>
                            <th>
                                <?= _("Method") ?>
                            </th>
                            <th>
                                <?= _("Status") ?>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($transactions as $transactionItem) :
                            $displayDeposits = $isDepositTab &&  (int) $transactionItem['type'] === $depositTypeId;
                            $displayTransactions = $isTransactionTab && (int) $transactionItem['type'] !==  $depositTypeId;
                            if ($displayDeposits || $displayTransactions) :
                        ?>
                                <tr<?= $transactionItem['tr_class'] ?? '' ?>>
                                    <td class="transactions-id">
                                        <?= Security::htmlentities($transactionItem['full_id_text']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="mobile-only-label">
                                            <?= _("Amount") ?>:
                                        </span>
                                        <?php if ((int)$transactionItem['payment_method_type_num'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) : ?>
                                            <span class="transactions-amount">
                                                <?= $transactionItem['bonus_amount']; ?>
                                            </span>
                                            <span class="info-circle fa fa-info-circle tooltip tooltip-bottom" data-tooltip="<?= _("Paid with bonus balance.") ?>">
                                            </span>
                                        <?php else : ?>
                                            <span class="transactions-amount">
                                                <?= $transactionItem['amount']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center transactions-date">
                                        <span class="mobile-only-label">
                                            <?php
                                            echo _("Date");
                                            echo "&nbsp;";
                                            echo _("(confirmed)");
                                            ?>:
                                        </span>
                                        <?php
                                        echo $transactionItem['date'];

                                        if (!empty($transactionItem['date_confirmed'])) :
                                        ?>
                                            <br>
                                            <span>
                                                (<?= $transactionItem['date_confirmed']; ?>)
                                            </span>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td class="transactions-method text-center">
                                        <span class="mobile-only-label">
                                            <?= _("Method") ?>:
                                        </span>
                                        <?= Security::htmlentities($transactionItem['payment_method_type']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="mobile-only-label">
                                            <?= _("Status") ?>:
                                        </span> <span class="transactions-status transactions-status-<?php
                                                                                                        echo Security::htmlentities($transactionItem['status']);
                                                                                                        ?>">
                                            <?= Security::htmlentities($transactionItem['status_text']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= $transactionItem['details_url']; ?>" class="tooltip tooltip-bottom" data-tooltip="<?= _("Details") ?>">
                                            <span class="fa fa-search"></span>
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            <?php
            else :
            ?>
                <p>
                    <?= _("No transactions."); ?>
                </p>
            <?php
            endif;
        elseif ($isCasinoTransactions) :
            if ($isDepositTab) {
                $transactions = !empty($deposits) ? $deposits : [];
            ?>
                <table class="table table-transactions clickable table-sort">
                    <thead>
                        <tr>
                            <th class="text-left">
                                <?= _("Transaction ID"); ?>
                            </th>
                            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['amount']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['amount']['link']); ?>">
                                <?= _("Amount"); ?>
                            </th>
                            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['date']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['date']['link']); ?>">
                                <?php
                                echo _("Date");
                                echo "&nbsp;";
                                echo _("(confirmed)");
                                ?>
                            </th>
                            <th>
                                <?= _("Method") ?>
                            </th>
                            <th>
                                <?= _("Status") ?>
                            </th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($transactions as $transactionItem) :
                            $displayDeposits = $isDepositTab && (int) $transactionItem['type'] === $depositTypeId;
                            $displayTransactions = $isTransactionTab && (int) $transactionItem['type'] !==  $depositTypeId;
                            if ($displayDeposits || $displayTransactions) :
                        ?>
                                <tr<?= $transactionItem['tr_class'] ?? '' ?>>
                                    <td class="transactions-id">
                                        <?= Security::htmlentities($transactionItem['full_id_text']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="mobile-only-label">
                                            <?= _("Amount") ?>:
                                        </span>
                                        <?php if ((int)$transactionItem['payment_method_type_num'] === Helpers_General::PAYMENT_TYPE_BONUS_BALANCE) : ?>
                                            <span class="transactions-amount">
                                                <?= $transactionItem['bonus_amount']; ?>
                                            </span>
                                            <span class="info-circle fa fa-info-circle tooltip tooltip-bottom" data-tooltip="<?= _("Paid with bonus balance.") ?>">
                                            </span>
                                        <?php else : ?>
                                            <span class="transactions-amount">
                                                <?= $transactionItem['amount']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center transactions-date">
                                        <span class="mobile-only-label">
                                            <?php
                                            echo _("Date");
                                            echo "&nbsp;";
                                            echo _("(confirmed)");
                                            ?>:
                                        </span>
                                        <?php
                                        echo $transactionItem['date'];

                                        if (!empty($transactionItem['date_confirmed'])) :
                                        ?>
                                            <br>
                                            <span>
                                                (<?= $transactionItem['date_confirmed']; ?>)
                                            </span>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td class="transactions-method text-center">
                                        <span class="mobile-only-label">
                                            <?= _("Method") ?>:
                                        </span>
                                        <?= Security::htmlentities($transactionItem['payment_method_type']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="mobile-only-label">
                                            <?= _("Status") ?>:
                                        </span> <span class="transactions-status transactions-status-<?php
                                                                                                        echo Security::htmlentities($transactionItem['status']);
                                                                                                        ?>">
                                            <?= Security::htmlentities($transactionItem['status_text']) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= $transactionItem['details_url']; ?>" class="tooltip tooltip-bottom" data-tooltip="<?= _("Details") ?>">
                                            <span class="fa fa-search"></span>
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            <?php

            } else {
            ?>
                <table class="table table-transactions table-sort">
                    <thead>
                        <tr>
                            <th>
                                <?= _("Game Id") ?>
                            </th>
                            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['amount']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['amount']['link']); ?>">
                                <?= _("Amount"); ?>
                            </th>
                            <th class="tablesorter-header tablesorter-<?= htmlspecialchars($sort['date']['class']); ?>" data-href="<?= UrlHelper::esc_url($sort['date']['link']); ?>">
                                <?php
                                echo _("Date");
                                ?>
                            </th>
                            <th>
                                <?= _("Game Name (Provider)") ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($transactions as $transactionItem) :
                        ?>
                            <tr<?= $transactionItem['tr_class'] ?? '' ?>>
                                <td class="text-center transactions-date">
                                    <span class="mobile-only-label">
                                        <?php
                                        echo _("Game Id");
                                        ?>:
                                    </span>
                                    <?php
                                    echo $transactionItem['session_id'];
                                    ?>
                                </td>
                                <td class="text-center">
                                    <span class="mobile-only-label">
                                        <?= _("Amount") ?>:
                                    </span>
                                    <span class="transactions-amount <?= $transactionItem['isLost'] ? 'transactions-status-2' : ''?>">
                                        <?= $transactionItem['amount']; ?>
                                    </span>
                                </td>
                                <td class="text-center transactions-date">
                                    <span class="mobile-only-label">
                                        <?php
                                        echo _("Date");
                                        ?>:
                                    </span>
                                    <?php
                                    echo $transactionItem['date'];
                                    ?>
                                </td>
                                <td class="text-center transactions-date">
                                    <span class="mobile-only-label">
                                        <?php
                                        echo _("Game Name (Provider)");
                                        ?>:
                                    </span>
                                    <?php
                                    echo $transactionItem['game_name'];
                                    ?>
                                </td>
                            </tr>
                    <?php
                        endforeach;
                    }
                    ?>
                    </tbody>
                </table>
            <?php
        endif;
        include('myaccount_pagination.php');
    else :
            ?>
            <p>
                <?= _("No transactions."); ?>
            </p>
    <?php
    endif;
endif;
