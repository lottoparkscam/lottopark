<?php

use Fuel\Core\Input;
use Helpers\SanitizerHelper;

if (isset($single_main_name)):
?>
    <hr>
    <h2>
        <?= $single_main_name; ?>
    </h2>

<?php
    endif;
    if (isset($single_main_info['reg_count'])):
?>
        <h3>
            <?= _("Report"); ?>
        </h3>
        <span class="details-label">
            <?= _("Start Date"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['date_start']; ?>
        </span>
        <br>
        <span class="details-label">
            <?= _("End Date"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['date_end']; ?>
        </span>
        <br>
        <span class="details-label">
            <?= _("New Users (Active)"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['reg_count']; ?> (<?= $single_main_info['active_count']; ?>)
        </span>
        <br>
        <span class="details-label">
            <?= _("New Users Confirmed"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['register_confirmed_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['ftd_count'])):
?>
        <span class="details-label">
            <?= _('New <span data-toggle="tooltip" title="First Time Deposit">FTD</span> Users'); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['ftd_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['std_count'])):
?>
        <span class="details-label">
            <?= _('<span data-toggle="tooltip" title="Second Time Deposit">STD</span> Users'); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['std_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['ftp_count'])):
?>
        <span class="details-label">
            <?= _('New <span data-toggle="tooltip" title="First Time Purchase">FTP</span> Users'); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['ftp_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['stp_count'])):
?>
        <span class="details-label">
            <?= _('<span data-toggle="tooltip" title="Second Time Purchase">STP</span> Users'); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['stp_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['tickets_count'])):
?>
        <span class="details-label">
            <?= _("Sold Tickets"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['tickets_count']; ?>
        </span>
        <br>
<?php
    endif;
    
    if (isset($single_main_info['lines_count'])):
?>
        <span class="details-label">
            <?= _("Sold Lines"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['lines_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['bonus_tickets_count'])):
?>
        <span class="details-label">
            <?= _("Bonus Tickets"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['bonus_tickets_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['tickets_win_count'])):
?>
        <span class="details-label">
            <?= _("Won tickets number"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['tickets_win_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['tickets_win_sum_prize_value'])):
?>
        <span class="details-label">
            <?= _("Prizes"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['tickets_win_sum_prize_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['deposits_count'])):
?>
        <span class="details-label">
            <?= _("Total number of deposits"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['deposits_count']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['deposit_amount_value'])):
?>
        <span class="details-label">
            <?= _("Deposits Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['deposit_amount_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['sales_amount_value'])):
?>
        <span class="details-label">
            <?= _("Sales Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['sales_amount_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['sales_income_value'])):
?>
        <span class="details-label">
            <?= _("Income Sum"); ?>: 
        </span>
        <span class="details-value">
            <strong>
                <?= $single_main_info['sales_income_value']; ?>
            </strong>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['sales_cost_value'])):
?>
        <span class="details-label">
            <?= _("Ticket Costs Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['sales_cost_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['sum_bonus_cost_value'])):
?>
        <span class="details-label">
            <?= _("Bonus Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['sum_bonus_cost_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['sales_payment_cost_value'])):
?>
        <span class="details-label">
            <?= _("Payment Costs Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['sales_payment_cost_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['sales_margin_value'])):
?>
        <span class="details-label">
            <?= _("Royalties Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['sales_margin_value']; ?>
        </span>
        <br>
<?php
    endif;

    if (isset($single_main_info['commissions_sum_value'])):
?>
        <span class="details-label">
            <?= _("Commissions Sum"); ?>: 
        </span>
        <span class="details-value">
            <?= $single_main_info['commissions_sum_value']; ?>
        </span>
        <br>
<?php
    endif;

    $whitelabelId = (int)SanitizerHelper::sanitizeString(Input::get('filter.whitelabels_extended') ?? '');
    if (is_numeric($whitelabelId)):
        $bonusSum = $single_main_info['sum_bonus_cost_value'] ?? '0,00';
        $incomeSum = $single_main_info['sales_income_value'] ?? '0,00';
        $royaltiesSum = $single_main_info['sales_margin_value'] ?? '0,00';
        $paymentSum = $single_main_info['sales_payment_cost_value'] ?? '0,00';
        $dateEnd = SanitizerHelper::sanitizeString(Input::get('filter.range_end'));
        $url = preg_replace('/\s+/u', '', "/invoice?bonusSum=$bonusSum&incomeSum=$incomeSum&royaltiesSum=$royaltiesSum&paymentSum=$paymentSum&whitelabelId=$whitelabelId&dateEnd=$dateEnd");
    ?>
        <a id="generate-invoice" href="<?= $url ?>" target="_blank" type="button" class="btn btn-primary">
            Generate Invoice
        </a>
    <?php endif;

    if (isset($single_finance_data)):
    ?>
        <h3>
            <?= _("Full finance report"); ?>
        </h3>
        <table class="table table-striped table-bordered table-sort">
            <thead>
                <tr>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_name']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_name']['link']; ?>">
                        <?= _("Lottery"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_sold_tickets_count']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_sold_tickets_count']['link']; ?>">
                        <?= _("Sold Tickets"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_sold_lines_count']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_sold_lines_count']['link']; ?>">
                        <?= _("Sold Lines"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_bonus_tickets_count']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_bonus_tickets_count']['link']; ?>">
                        <?= _("Bonus Tickets"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_win_tickets_count']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_win_tickets_count']['link']; ?>">
                        <?= _("Win Lines count"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_win_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_win_usd_sum']['link']; ?>">
                        <?= _("Win Tickets prize"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_amount_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_amount_usd_sum']['link']; ?>">
                        <?= _("Sales"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_cost_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_cost_usd_sum']['link']; ?>">
                        <?= _("Ticket Costs"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_income_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_income_usd_sum']['link']; ?>">
                        <?= _("Income"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_bonus_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_bonus_usd_sum']['link']; ?>">
                        <?= _("Bonus"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_margin_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_margin_usd_sum']['link']; ?>">
                        <?= _("Royalties"); ?>
                    </th>
                    <th class="tablesorter-header tablesorter-<?= $single_sort['lottery_uncovered_prize_usd_sum']['class']; ?>" 
                        data-href="<?= $single_sort['lottery_uncovered_prize_usd_sum']['link']; ?>">
                        <?= _("Uninsured winnings"); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($single_finance_data as $finance_row):
                ?>
                        <tr>
                            <td>
                                <?= $finance_row['name']; ?>
                            </td>
                            <td>
                                <?= $finance_row['sold_tickets_count']; ?>
                            </td>
                            <td>
                                <?= $finance_row['sold_lines_count']; ?>
                            </td>
                            <td>
                                <?= $finance_row['bonus_tickets_count']; ?>
                            </td>
                            <td>
                                <?= $finance_row['win_tickets_count']; ?>
                            </td>
                            <td>
                                <?= $finance_row['win_tickets_prize']; ?>
                            </td>
                            <td>
                                <?= $finance_row['sales']; ?>
                            </td>
                            <td>
                                <?= $finance_row['ticket_costs']; ?>
                            </td>
                            <td>
                                <?= $finance_row['income']; ?>
                            </td>
                            <td>
                                <?= $finance_row['bonus']; ?>
                            </td>
                            <td>
                                <?= $finance_row['maring']; ?>
                            </td>
                            <td>
                                <?= $finance_row['uncovered']; ?>
                            </td>
                        </tr>
                <?php
                    endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>
                        <?= _("Total Sum"); ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['sold_tickets_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['sold_lines_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['bonus_tickets_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['win_tickets_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['win_tickets_prize_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['amount_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['cost_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['income_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['bonus_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['margin_total']; ?>
                    </th>
                    <th>
                        <?= $single_finance_sums['uncovered_total']; ?>
                    </th>
                </tr>
            </tfoot>
        </table>
<?php
    endif;

/*
<?php if (isset($deposit_details) && count($deposit_details) > 0): ?>
<h3><?= _("Deposits sum by currency"); ?></h3>
<?php foreach ($deposit_details AS $item): ?>
    <span class="details-label"><?= $currencies[$item['currency_id']]['code']; ?>: </span>
    <span class="details-value"><?= Lotto_View::format_currency($item['sum_manager'], $currencies[$item['currency_id']]['code'], true); ?></span><br>
<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($sale_details) && count($sale_details) > 0): ?>
<h3><?= _("Sales sum by currency"); ?></h3>
<?php foreach ($sale_details AS $item): ?>
    <span class="details-label"><?= $currencies[$item['currency_id']]['code']; ?>: </span>
    <span class="details-value"><?= Lotto_View::format_currency($item['sum_manager'], $currencies[$item['currency_id']]['code'], true); ?></span><br>
<?php endforeach; ?>
<?php endif; ?>
</div> */

    if (isset($single_payment_methods_purchase_report)):
?>
        <h3>
            <?= _("Payment methods - Purchase"); ?>
        </h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <?= _("Payment method"); ?>
                    </th>
                    <th>
                        <?= _("Payment provider"); ?>
                    </th>
                    <th>
                        <?= _("Amount"); ?>
                    </th>
                    <th>
                        <?= _("Income"); ?>
                    </th>
                    <th>
                        <?= _("Ticket Cost"); ?>
                    </th>
                    <th>
                        <?= _("Transactions"); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($single_payment_methods_purchase_report as $payment_method_purchase_row):
                ?>
                        <tr>
                            <td>
                                <?= $payment_method_purchase_row['payment_method']; ?>
                            </td>
                            <td>
                                <?= $payment_method_purchase_row['provider_name']; ?>
                            </td>
                            <td>
                                <?= $payment_method_purchase_row['amount_value']; ?>
                            </td>
                            <td>
                                <?= $payment_method_purchase_row['income_value']; ?>
                            </td>
                            <td>
                                <?= $payment_method_purchase_row['cost_value']; ?>
                            </td>
                            <td>
                                <?= $payment_method_purchase_row['total']; ?>
                            </td>
                        </tr>
                <?php
                    endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>
                        <?= _("Total Sum"); ?>
                    </th>
                    <th></th>
                    <th>
                        <?= $single_payment_methods_purchase_sums['amount_total']; ?>
                    </th>
                    <th>
                        <?= $single_payment_methods_purchase_sums['income_total']; ?>
                    </th>
                    <th>
                        <?= $single_payment_methods_purchase_sums['ticket_total']; ?>
                    </th>
                    <th>
                        <?= $single_payment_methods_purchase_sums['transaction_total']; ?>
                    </th>
                </tr>
            </tfoot>
        </table>
<?php
    endif;

    if (isset($single_payment_methods_deposit_report)):
?>
        <h3>
            <?= _("Payment methods - Deposit"); ?>
        </h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>
                        <?= _("Payment method"); ?>
                    </th>
                    <th>
                        <?= _("Payment provider"); ?>
                    </th>
                    <th>
                        <?= _("Amount"); ?>
                    </th>
                    <th>
                        <?= _("Income"); ?>
                    </th>
                    <th>
                        <?= _("Ticket Cost"); ?>
                    </th>
                    <th>
                        <?= _("Transactions"); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ($single_payment_methods_deposit_report as $payment_method_deposit_row):
                ?>
                        <tr>
                            <td>
                                <?= $payment_method_deposit_row['payment_method']; ?>
                            </td>
                            <td>
                                <?= $payment_method_deposit_row['provider_name']; ?>
                            </td>
                            <td>
                                <?= $payment_method_deposit_row['amount_value']; ?>
                            </td>
                            <td>
                                <?= $payment_method_deposit_row['income_value']; ?>
                            </td>
                            <td>
                                <?= $payment_method_deposit_row['cost_value']; ?>
                            </td>
                            <td>
                                <?= $payment_method_deposit_row['total']; ?>
                            </td>
                        </tr>
                <?php
                    endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>
                        <?= _("Total Sum"); ?>
                    </th>
                    <th></th>
                    <th>
                        <?= $single_payment_methods_deposit_sums['amount_total']; ?>
                    </th>
                    <th>
                        <?= $single_payment_methods_deposit_sums['income_total']; ?>
                    </th>
                    <th>
                        <?= $single_payment_methods_deposit_sums['ticket_total']; ?>
                    </th>
                    <th>
                        <?= $single_payment_methods_deposit_sums['transaction_total']; ?>
                    </th>
                </tr>
            </tfoot>
        </table>
<?php endif; ?>
