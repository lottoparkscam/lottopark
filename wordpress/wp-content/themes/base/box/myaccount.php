<?php
if (!defined('WPINC')) {
    die;
}
?>
<div class="myaccount-content">
    <?php
        if ((string)$section === "transactions" && isset($action) &&
            (string)$action === "details" && isset($transaction)
        ):
            $header_text = $whitelabel['prefix'];
            if ((int)$transaction['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $header_text .= 'P';
            } else {
                $header_text .= 'D';
            }
            $header_text .= $transaction['token'];
            $s_header_text = sprintf(_("Transaction %s"), $header_text);
    ?>
            <h1 class="account">
                <?= Security::htmlentities($s_header_text); ?>
            </h1>
    <?php
        elseif ((string)$section === "tickets" && isset($action) &&
            (string)$action === "details" && isset($ticket) &&
            (int)$ticket['status'] !== Helpers_General::TICKET_STATUS_QUICK_PICK
        ):
            $header_text = $whitelabel['prefix'] . 'T' . $ticket['token'];
            $s_header_text = sprintf(_("Ticket %s"), $header_text);
    ?>
            <h1 class="account">
                <?= Security::htmlentities($s_header_text); ?>
            </h1>
    <?php
        endif;
    ?>

    <div class="myaccount-data myaccount-details">
    <?php
        switch ($section):
            case 'withdrawal':
                include('myaccount_withdrawals.php');
                break;
            case 'tickets':
                if (isset($action) && $action === 'details') {
                    include('myaccount_tickets.php');
                } else {
                    include('raffle-myaccount_tickets.php');
                }
                break;
            case 'transactions':
                include('myaccount_transactions.php');
                break;
            case 'payments':
                include('myaccount_payments.php');
                break;
            case 'promote':
                include('myaccount_promote.php');
                break;
            default:
                include('myaccount_profile.php');
            break;
        endswitch;
    ?>
    </div>
</div>
<div class="clearfix"></div>
