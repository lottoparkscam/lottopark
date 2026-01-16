<?php

use Repositories\WhitelabelLotteryProviderApiRepository;
use Repositories\WhitelabelUserTicketRepository;
use Services\LotteryProvider\TheLotterLotteryMap;
use Traits\Scans\ScansTrait;

/**
 * Description of Forms_Wordpress_Myaccount_Ticket_Details
 */
class Forms_Wordpress_Myaccount_Ticket_Details extends Forms_Main
{
    use ScansTrait;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var array
     */
    private $user = [];

    /**
     *
     * @param array $whitelabel
     * @param array $user
     */
    public function __construct($whitelabel, $user)
    {
        $this->whitelabel = $whitelabel;
        $this->user = $user;
    }

    /**
     *
     * @return array
     */
    public function get_errors(): array
    {
        return $this->errors;
    }

    /**
     *
     * @param View $view
     *
     * @return int
     */
    public function process_form(&$view): int
    {
        /** @var Model_Whitelabel_User_Ticket $tickets */
        $tickets = Model_Whitelabel_User_Ticket::find([
            "where" => [
                "whitelabel_id" => $this->whitelabel['id'],
                "token" => intval(get_query_var('id')),
            ]
        ]);

        if ($tickets === null || count($tickets) === 0) {
            $this->errors = ["details" => _("Incorrect ticket.")];
            return self::RESULT_WITH_ERRORS;
        }

        $ticket = $tickets[0];

        if (Helpers_Lottery::supports_ticket_multipliers_by_lottery_id($ticket['lottery_id'])) {
            $keno_data = Model_Whitelabel_User_Ticket_Keno_Data::by_ticket_id($ticket['id']);
            $view->set("ticket_multiplier", $keno_data['multiplier']);
        }

        if (!((int)$ticket->whitelabel_id === (int)$this->whitelabel['id'] &&
            (int)$ticket->whitelabel_user_id === (int)$this->user['id'])
        ) {
            $this->errors = ["details" => _("Incorrect ticket.")];
            return self::RESULT_WITH_ERRORS;
        }

        $view->set('ticket_type', 'lottery');
        $view->set("ticket", $ticket);

        if (!empty($ticket['multi_draw_id'])) {
            $multi_draws = Model_Whitelabel_User_Ticket::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "multi_draw_id" => $ticket['multi_draw_id']
                ]
            ]);

            $view->set("multi_draws", $multi_draws);
        }

        $lines = Model_Whitelabel_User_Ticket_Line::get_with_slip_by_ticket_id($ticket->id);

        if (is_null($lines) || count($lines) === 0) {
            $this->errors = ["details" => _("Incorrect ticket.")];
            return self::RESULT_WITH_ERRORS;
        }

        $slips = Model_Whitelabel_User_Ticket_Slip::find([
            "where" => [
                "whitelabel_user_ticket_id" => $ticket->id
            ],
            "order_by" => ["id" => "asc"]
        ]);

        Config::load("platform", true);
        $images_dir = Config::get("platform.images.dir");
        $images = [];
        if ($slips !== null) {
            foreach ($slips as $slip) {
                if (!empty($slip->ticket_scan_url)) {
                    $images[] = $slip->id;
                }
            }
        }

        $images = $this->getGgWorldScanImages($images, $ticket['id']);

        $view->set("images", $images);

        if ((int)$ticket->status !== Helpers_General::TICKET_STATUS_PENDING &&
            (int)$ticket->status !== Helpers_General::TICKET_STATUS_QUICK_PICK
        ) {
            $draw = Model_Lottery_Draw::find([
                'where' => [
                    'lottery_id' => $ticket->lottery_id,
                    'date_local' => $ticket->draw_date
                ]
            ]);
            if ($draw !== null) {
                $view->set("draw", $draw[0]);
            }
        }

        $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($this->whitelabel);
        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $view->set('lines', $lines);
        $view->set('slips', $slips);
        $view->set('lotteries', $lotteries);
        $view->set('currencies', $currencies);

        $excludeLotterySlug = ['gg-world', 'gg-world-x', 'gg-world-million']; // these lotteries receive scans from LCS, so we don't want to hide them
        $whitelabelLotteryProviderApiRepository = Container::get(WhitelabelLotteryProviderApiRepository::class);
        $whitelabelUserTicketRepository = Container::get(WhitelabelUserTicketRepository::class);
        $supportedLotterySlugs = TheLotterLotteryMap::getAllLotterySlugsByTheLotterProvider();
        $supportedWhitelabelIds = $whitelabelLotteryProviderApiRepository->getWhitelabelIdsWithApiEnabled();
        $lotterySlug = $whitelabelUserTicketRepository->getLotterySlugByTicketId($ticket->id, $ticket->whitelabel_id);

        $isScansDisplayedForUsers = $this->whitelabel['is_scans_displayed_for_users'] &&
            (
                !empty($images) ||
                (
                    in_array($lotterySlug, $supportedLotterySlugs) &&
                    in_array($ticket->whitelabel_id, $supportedWhitelabelIds) &&
                    !in_array($lotterySlug, $excludeLotterySlug)
                )
            );
        $view->set('isScansDisplayedForUsers', $isScansDisplayedForUsers);

        if (!empty($ticket->whitelabel_transaction_id)) {
            $transaction = Model_Whitelabel_Transaction::find([
                "where" => [
                    "whitelabel_id" => $this->whitelabel['id'],
                    "id" => $ticket->whitelabel_transaction_id
                ]
            ]);
            if ($transaction !== null && count($transaction) > 0) {
                $view->set('transaction', $transaction[0]);
            }
        }

        return self::RESULT_OK;
    }
}
