<?php

namespace Services\Lottery\Factory;

use Exception;
use Forms_Admin_Whitelabels_Prepaid_New;
use Fuel\Core\DB;
use Helpers_Currency;
use Helpers_General;
use Helpers_Ltech;
use Helpers_Time;
use InvalidArgumentException;
use Lotto_Helper;
use Lotto_Security;
use Model_Currency;
use Model_Lottery;
use Model_Lottery_Provider;
use Model_Lottery_Type;
use Model_Whitelabel;
use Model_Whitelabel_Lottery;
use Model_Whitelabel_Ltech;
use Model_Whitelabel_User;
use Model_Whitelabel_User_Ticket;
use Model_Whitelabel_User_Ticket_Line;
use Model_Whitelabel_User_Ticket_Slip;

/**
 * @deprecated this class must be refactored, when the whole legacy code will be.
 */
class TicketFactory
{
    /**
     * This is copy & paste from fuel\app\classes\forms\wordpress\myaccount\ticket\quickpick.php.
     * and from Lotto_Lotteries_Lottery:690
     * Don't judge me.
     *
     * @param int $whitelabel_id
     * @param int $user_id
     * @param string $slug
     * @param int $tickets_count
     * @param int $lines_count
     * @return Model_Whitelabel_User_Ticket[]
     * @throws Exception
     */
    public function create_bonus_ticket(
        int $whitelabel_id,
        int $user_id,
        string $slug,
        int $tickets_count = 1,
        int $lines_count = 1
    ): array
    {
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);

        if ($lines_count > 1) {
            # todo: https://trello.com/c/g7Qvseto
            throw new InvalidArgumentException(sprintf('Multiple lines case not supported.'));
        }

        /** @var Model_Lottery|null $lottery */
        $lottery = Model_Lottery::find_one_by('slug', $slug);

        if (empty($lottery)) {
            throw new InvalidArgumentException(sprintf('Unable to find lottery with slug %s.', $slug));
        }

        if (Lotto_Helper::is_lottery_closed($lottery->to_array(), null, $whitelabel->to_array())) {
            $next_draw_date = Lotto_Helper::get_lottery_next_draw(
                $lottery,
                true,
                null,
                2
            );
        } else {
            $next_draw_date = Lotto_Helper::get_lottery_next_draw($lottery);
        }

        $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
            $lottery->to_array(),
            $lottery->next_date_local
        );

        # copy & paste from Lotto_Lotteries_Lottery:690

        $result = [];

        $whitelabel_user = Model_Whitelabel_User::find_by_pk($user_id);
        $wlotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
        $wlottery = $wlotteries['__by_id'][$lottery['id']];

        $model = $wlottery['model'];

        $is_insured = false;
        $tier = 0;

        if ($model == Helpers_General::LOTTERY_MODEL_MIXED &&
            Lotto_Helper::should_insure($wlottery, $wlottery['tier'], $wlottery['volume'])
        ) {
            $is_insured = true;
            $tier = $wlottery['tier'];
        }

        $calc_cost = Lotto_Helper::get_price(
            $wlottery,
            $wlottery['model'],
            $wlottery['tier'],
            $wlottery['volume']
        );

        $system_currency_tab = Helpers_Currency::get_mtab_currency(false, "USD");

        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $whitelabel['manager_site_currency_id']
        );

        $cost_local = $calc_cost[0] + $calc_cost[1];
        $cost_usd = Helpers_Currency::convert_to_USD($cost_local, $wlottery['currency']);
        $whitelabel_user_currency_code = Model_Currency::find_by_pk($whitelabel_user['currency_id'])['code'];
        $cost = Helpers_Currency::get_recalculated_to_given_currency(
            $cost_usd,
            $system_currency_tab,
            $whitelabel_user_currency_code
        );
        $cost_manager = Helpers_Currency::get_recalculated_to_given_currency(
            $cost_usd,
            $system_currency_tab,
            $manager_currency_tab['code']
        );

        $income_local = 0 - $cost_local; // -ticketcost
        $income_usd = 0 - (float)$cost_usd; // -ticketcost
        $income = 0 - (float)$cost; //-ticketcost
        $income_manager = Helpers_Currency::get_recalculated_to_given_currency(
            $income_usd,
            $system_currency_tab,
            $manager_currency_tab['code']
        );

        $income_value = $wlottery['income'];
        $income_type = $wlottery['income_type'];

        $margin_value = $whitelabel['margin'];
        $margin_percent = $margin_value / 100;

        $margin_local = $margin_percent * $cost_local;
        $margin_usd = $margin_percent * (float)$cost_usd;
        $margin = $margin_percent * (float)$cost;
        $margin_manager = $margin_percent * (float)$cost_manager;
        /*** end of price calculations ***/

        $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
            $whitelabel_id,
            $lottery['id']
        )[0];

        $lottery_provider = Model_Lottery_Provider::find_by_pk($whitelabel_lottery['lottery_provider_id']);

        for ($ticket_c = 0; $ticket_c !== $tickets_count; $ticket_c++) {
            /** @var Model_Whitelabel_User_Ticket $ticket */
            $ticket = Model_Whitelabel_User_Ticket::forge([
                'token' => Lotto_Security::generate_ticket_token($whitelabel_id),
                'whitelabel_id' => $whitelabel_id,
                'whitelabel_user_id' => $user_id,
                'lottery_id' => $lottery['id'],
                'lottery_type_id' => $lottery_type['id'],
                'lottery_provider_id' => $lottery_provider['id'],
                'currency_id' => $whitelabel_user['currency_id'],
                'valid_to_draw' => $next_draw_date->format(Helpers_Time::DATETIME_FORMAT),
                'draw_date' => $next_draw_date->format(Helpers_Time::DATETIME_FORMAT),
                'amount' => 0,
                'amount_usd' => 0,
                'amount_payment' => 0,
                'amount_local' => 0,
                'amount_manager' => 0,
                'date' => DB::expr("NOW()"),
                'status' => Helpers_General::TICKET_STATUS_PENDING,
                'paid' => Helpers_General::TICKET_PAID,
                'payout' => Helpers_General::TICKET_PAYOUT_PENDING,
                'is_insured' => $is_insured,
                'tier' => $tier,
                'cost_local' => $cost_local,
                'cost_usd' => $cost_usd,
                'cost' => $cost,
                'cost_manager' => $cost_manager,
                'income_local' => $income_local,
                'income_usd' => $income_usd,
                'income' => $income,
                'income_value' => $income_value,
                'income_manager' => $income_manager,
                'income_type' => $income_type,
                'margin_value' => $margin_value,
                'margin_local' => $margin_local,
                'margin_usd' => $margin_usd,
                'bonus_cost_local' => $cost_local,
                'bonus_cost_usd' => $cost_usd,
                'bonus_cost' => $cost,
                'bonus_cost_manager' => $cost_manager,
                'margin' => $margin,
                'margin_manager' => $margin_manager,
                'ip' => '127.0.0.1',
                'line_count' => 0,
            ]);
            $ticket->save();

            // decrease whitelabel prepaid
            if ((int)$wlottery['should_decrease_prepaid'] === 1) {
                $prepaid = new Forms_Admin_Whitelabels_Prepaid_New($whitelabel->to_array());
                $prepaid->subtract_prepaid($ticket['cost_manager'], null, false);
            }

            $brandom = [];

            if (empty($lottery_type)) {
                throw new InvalidArgumentException('Unable to find lottery type.');
            }

            $random = Lotto_Helper::get_random_values($lottery_type['ncount'], $lottery_type['nrange']);

            if ($lottery_type['bextra'] == 0 && $lottery_type['bcount'] > 0) {
                $brandom = Lotto_Helper::get_random_values(
                    $lottery_type['bcount'],
                    $lottery_type['brange']
                );
            }

            $line = Model_Whitelabel_User_Ticket_Line::forge();
            $line->set(array(
                'whitelabel_user_ticket_id' => $ticket['id'],
                'numbers' => implode(',', $random),
                'bnumbers' => implode(',', $brandom),
                'amount' => 0,
                'amount_usd' => 0,
                'amount_local' => 0,
                'amount_payment' => 0,
                'amount_manager' => 0,
                'status' => Helpers_General::TICKET_STATUS_PENDING,
                'payout' => Helpers_General::TICKET_PAYOUT_PENDING
            ));
            $line->save();

            $ticket->set(array(
                'status' => Helpers_General::TICKET_STATUS_PENDING,
                'lottery_type_id' => $lottery_type['id'],
                'line_count' => $lines_count
            ));
            $ticket->save();

            /** @var object $slip */
            $slip = Model_Whitelabel_User_Ticket_Slip::forge();
            /** @var object $ticket */
            $slip->set([
                'whitelabel_user_ticket_id' => $ticket->id,
                'whitelabel_lottery_id' => $whitelabel_lottery['id']
            ]);

            $ltech = Model_Whitelabel_Ltech::find([
                "where" => [
                    "whitelabel_id" => $whitelabel_id,
                    "is_enabled" => 1
                ]]);

            $ltech_helper = new Helpers_Ltech($ltech !== null ? $ltech[0]['id'] : null);
            $ltech_details = $ltech_helper->get_ltech_details();
            $ltech_id_exists = (int)$lottery_provider['provider'] === Helpers_General::PROVIDER_LOTTORISQ
                && isset($ltech_details['ltech_id'])
                && !empty($ltech_details['ltech_id']);

            if ($ltech_id_exists) {
                $slip->set([
                    'whitelabel_ltech_id' => $ltech_details['ltech_id']
                ]);
            }

            $slip->save();
            $line
                ->set([
                    'whitelabel_user_ticket_slip_id' => $slip->id
                ])
                ->save();

            $result[] = $ticket;
        }

        return $result;
    }
}
