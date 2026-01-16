<?php

use Carbon\Carbon;
use Helpers\CurrencyHelper;
use Helpers\UrlHelper;

/**
 * Description of order
 */
class Forms_Wordpress_Myaccount_Order
{
    /**
     *
     * @var array
     */
    private $errors = [];

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var null|array
     */
    private $user = null;

    /**
     *
     * @var int
     */
    private $payment_type = 1;

    /**
     * It should never happened that it is equal to 0
     *
     * @var int
     */
    private $payment_method = 0;

    /**
     *
     * @var array
     */
    private $order = [];

    /**
     *
     * @var bool
     */
    private $is_user = false;

    /**
     *
     * @var array
     */
    private $lotteries = [];

    /**
     *
     * @var float
     */
    private $total_sum = 0;

    /**
     *
     * @var array
     */
    private $user_currency_tab = [];

    /**
     *
     * @var string
     */
    private $user_currency_code = "";

    /**
     *
     * @var float
     */
    private $purchase_min_amount = 0.00;

    /**
     *
     * @var float
     */
    private $deposit_min_amount = 0.00;

    /**
     *
     * @var bool
     */
    private $deposit = false;

    /**
     *
     * @param array $whitelabel
     * @param array $order
     * @param array $lotteries
     */
    public function __construct($whitelabel, $order, $lotteries)
    {
        $this->whitelabel = $whitelabel;

        if (!empty(Input::post("payment.type"))) {
            $this->payment_type = (int)Input::post("payment.type");
        }
        if (!empty(Input::post("payment.subtype"))) {
            $this->payment_method = (int)Input::post("payment.subtype");
        }
        $this->order = $order;

        $this->lotteries = $lotteries;

        Lotto_Settings::getInstance()->set("deposit", $this->deposit);

        $this->is_user = Lotto_Settings::getInstance()->get("is_user");
        if ($this->is_user) {
            $this->user = Lotto_Settings::getInstance()->get("user");
        }

        // The settings consists full user currency tab
        $this->user_currency_tab = CurrencyHelper::getCurrentCurrency()->to_array();

        $this->user_currency_code = $this->user_currency_tab['code'];
    }

    /**
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     *
     * @return float
     */
    public function get_min_amount()
    {
        $amount_to_return = 0.00;
        if ($this->deposit) {
            $amount_to_return = $this->deposit_min_amount;
        } else {
            $amount_to_return = $this->purchase_min_amount;
        }

        return $amount_to_return;
    }

    /**
     * Return total sum as string (formatted)
     *
     * @return string
     */
    public function get_total_sum()
    {
        $total_sum_formatted = Lotto_View::format_currency(
            $this->total_sum,
            $this->user_currency_code,
            true
        );

        return $total_sum_formatted;
    }

    /**
     *
     * @return float
     */
    public function get_total_sum_unformatted()
    {
        return $this->total_sum;
    }

    /**
     *
     * @param array $user
     */
    public function set_user($user)
    {
        $this->user = $user;
    }

    /**
     *
     * @param bool $deposit
     */
    public function set_deposit($deposit): void
    {
        $this->deposit = $deposit;
    }

    /**
     *
     */
    public function process_min_amounts(): void
    {
        $user_currency_raw = Model_Whitelabel_Default_Currency::get_for_user(
            $this->whitelabel,
            $this->user_currency_tab['id']
        );

        $this->purchase_min_amount = $user_currency_raw['min_purchase_amount'];
        $this->deposit_min_amount = $user_currency_raw['min_deposit_amount'];
    }

    /**
     *
     * @return bool
     */
    public function is_moved(): bool
    {
        $moved = false;
        $now = new DateTime("now", new DateTimeZone("UTC"));
        foreach ($this->order as $key => $item) {
            if (isset($this->lotteries['__by_id'][$item['lottery']])) {
                $lottery = $this->lotteries['__by_id'][$item['lottery']];
                $ndd = DateTime::createFromFormat(
                    "Y-m-d H:i:s",
                    $lottery['next_date_utc'],
                    new DateTimeZone("UTC")
                );
                if (Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel) && $now < $ndd) {
                    $moved = true;
                }
            }
        }

        return $moved;
    }

    /**
     * @param array  $lottery
     * @param int    $next
     * @param string $with_text
     *
     * @return string
     * @throws Exception
     */
    private function draw_date_text(array $lottery, int $next, string $with_text = 'with'): string
    {
        $draw_date_value = $this->format_draw_date(
            Lotto_Helper::get_lottery_real_next_draw($lottery, $next),
            $lottery['timezone']
        );

        if ($with_text == "first") {
            $draw_date_text = sprintf(
                _("First draw event on %s"),
                $draw_date_value
            );
        } elseif ($with_text == "with") {
            $draw_date_text = sprintf(
                _("Draw event on %s"),
                $draw_date_value
            );
        } else {
            $draw_date_text = $draw_date_value;
        }

        return $draw_date_text;
    }

    private function format_draw_date(Carbon $draw_date, string $timezone): string
    {
        return Helpers_View_Date::format_date_for_user_timezone(
            $draw_date->format(Helpers_Time::DATETIME_FORMAT),
            $timezone,
        );
    }

    /**
     *
     * @param array $item
     * @param array $lottery
     *
     * @return array
     */
    public function prepare_order_data(array $item, array $lottery): array
    {
        $pricing = lotto_platform_get_pricing($lottery);
        $ticket_multiplier = 1;
        if (isset($item['ticket_multiplier'])){
            $ticket_multiplier = $item['ticket_multiplier'];
        }
        $ticket_lines_count = count($item['lines']);
        $ticket_price = round($pricing * $ticket_multiplier, 2) * $ticket_lines_count;
        $multi_draw_badge = null;
        $numbers_per_line = null;
        $amount_to_win = Lotto_View::get_jackpot_for_order($lottery);
        $lottery_image = Lotto_View::get_lottery_image($lottery['id']);
        $play_info_href = lotto_platform_get_permalink_by_slug('play/' . $lottery['slug']);
        $play_text = '';
        $allowed_html_play = ["a" => ["href" => []]];
        $mobile_hide_text = '';
        $allowed_html_mobile = [
            "span" => [
                "class" => [],
                "aria-hidden" => [],
                "data-tooltip" => []
            ]
        ];
        $mobile_hide_text_full = wp_kses($mobile_hide_text, $allowed_html_mobile);
        $order_value = Lotto_View::format_currency(
            $pricing,
            $this->user_currency_code,
            true
        );

        if (isset($item['ticket_multiplier'])) {
            $ticket_multiplier = $item['ticket_multiplier'];
        }
        $order_text = sprintf(_("Single ticket, Lines: <span>%s</span>"), $ticket_lines_count);

		$now = Carbon::now($lottery['timezone']);
		$drawDate = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);
		$lotteryIsClosed = Lotto_Helper::is_lottery_closed($lottery, null, $this->whitelabel);
		$nowIsBeforeNextDraw = $now->lessThan($drawDate);

		if ($lotteryIsClosed && $nowIsBeforeNextDraw) {
			$draw_date_value = $this->format_draw_date(Lotto_Helper::get_lottery_next_draw($lottery, true, null, 2), $lottery['timezone']);
		} else {
			$draw_date_value = $this->format_draw_date(Lotto_Helper::get_lottery_next_draw($lottery), $lottery['timezone']);
		}

        $draw_date_text_closed = sprintf(_("Draw event on %s"), $draw_date_value);
        if (isset($item['multidraw'])) {
            $multi_draw_helper = new Helpers_Multidraw($this->whitelabel);
            $multi_draw = $multi_draw_helper->check_multidraw($item['multidraw']);
            if (!empty($multi_draw['tickets'])) {
                $multi_draw_badge = $multi_draw['tickets'];
                $ticket_price = $multi_draw_helper->calculate($multi_draw, $ticket_price);
                $order_text = _('Multi-Draw') . ': x'. $multi_draw['tickets'] . ', Lines: ' . $ticket_lines_count;
                $draw_date_text_closed = sprintf(_("First draw event on %s"), $draw_date_value);
            }
        }
        $play_text = sprintf(
                _('<a href="%s">%s</a>'),
                $play_info_href,
                _($lottery['name'])
            );
        $play_text_full = wp_kses($play_text, $allowed_html_play);
        $this->total_sum += $ticket_price;

        if (Helpers_Lottery::is_keno($lottery)) {
            $numbers_per_line = count($item['lines'][0]['numbers']);
        }

        $allowed_html_order = [
            "span" => [],
            "strong" => [],
            "br" => []
        ];
        $order_text_full = wp_kses($order_text, $allowed_html_order);
        $draw_date_text_open = $this->draw_date_text($lottery, 1);
        $amount_value = Lotto_View::format_currency(
            $ticket_price,
            $this->user_currency_code,
            true
        );

        return [
            $lottery_image,
            $play_text_full,
            $mobile_hide_text_full,
            $order_text_full,
            $draw_date_text_closed,
            $draw_date_text_open,
            $amount_value,
            $multi_draw_badge,
            $ticket_multiplier,
            $numbers_per_line,
        ];
    }

    /**
     *
     * @return bool
     */
    public function check_promo_active()
    {
        return (isset($this->user) && Model_Whitelabel_Campaign::is_active_purchase($this->whitelabel['id']));
    }

    /**
     * This function calculates and prepare different variables for show in front-end
     *
     * @return array
     */
    public function prepare_html_vars()
    {
        $class = '';
        $msg = null;
        $disabled = '';
        $url = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('order')) . '#payment';

        if (!$this->is_user) {
            $url = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));
            $class = ' popup-order';
        } elseif ($this->total_sum < $this->purchase_min_amount) {
            $class = ' order-min-prompt';
            if ($this->purchase_min_amount > 0) {
                $min_purchase_amount_formatted = Lotto_View::format_currency(
                    $this->purchase_min_amount,
                    $this->user_currency_code,
                    true
                );

                if ($this->user['balance'] < $this->total_sum) {
                    $disabled = ' disabled';
                    $msg = sprintf(
                        _(
                            "For orders less than %1s you can pay with account " .
                            "balance only, however your balance is insufficient."
                        ),
                        $min_purchase_amount_formatted
                    );
                } else {
                    $msg = sprintf(
                        _("For orders less than %1s you can pay with account balance only."),
                        $min_purchase_amount_formatted
                    );
                }
            }
        }

        return [
            $class,
            $msg,
            $disabled,
            $url
        ];
    }
}
