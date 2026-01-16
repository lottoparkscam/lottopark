<?php

use Helpers\NumberHelper;

/**
 * Task Dev Generate Tickets.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-16
 * Time: 15:36:25
 */
final class Task_Dev_Generate_Tickets extends Task_Dev_Task
{
    /**
     * @var int
     */
    const RANDOM_NUMBER_OF_LINES = 0;

    protected $in_transaction = true;

    /**
     * Mail of the user for which tickets will be generated.
     *
     * @var string
     */
    private $user_email;

    /**
     * How many tickets should be generated.
     *
     * @var int
     */
    private $tickets_count;

    /**
     * Slug for the lottery.
     *
     * @var string
     */
    private $lottery_slug;

    /**
     * @var int
     */
    private $lines_per_ticket;

    /**
     * lines_per_slip.
     *
     * @var int
     */
    private $lines_per_slip;

    /**
     * Ticket multiplier
     *
     * @var int|null
     */
    private $ticket_multiplier;

    /**
     * Numbers per line
     *
     * @var int|null
     */
    private $numbers_per_line;

    /** @var string */
    private $draw_date;

    /**
     * Create new generate tickets task.
     *
     * @param string      $user_email       Mail of the user for which tickets will be generated.
     * @param string      $lottery_slug     Slug for the lottery.
     * @param int         $tickets_count    How many tickets should be generated.
     * @param int         $lines_per_slip
     * @param int         $lines_per_ticket on default random 1,25
     * @param string|null $draw_date
     * @param int|null    $ticket_multiplier
     * @param int|null    $numbers_per_line
     */
    public function __construct(string $user_email, string $lottery_slug, int $tickets_count, int $lines_per_slip = 7, int $lines_per_ticket = self::RANDOM_NUMBER_OF_LINES, string $draw_date = null, ?int $ticket_multiplier = null, ?int $numbers_per_line = null)
    {
        parent::__construct();
        $this->user_email = $user_email;
        $this->tickets_count = $tickets_count;
        $this->lottery_slug = $lottery_slug;
        $this->lines_per_ticket = $lines_per_ticket;
        $this->lines_per_slip = $lines_per_slip;
        $this->ticket_multiplier = $ticket_multiplier;
        $this->numbers_per_line = $numbers_per_line;
        $this->draw_date = $draw_date;
    }

    public function run(): void
    {
        // fetch models
        $user = Model_Whitelabel_User::find_one_by('email', $this->user_email);
        $lotteryWithProvider = Model_Lottery::for_slug_with_provider($this->lottery_slug);
        $lottery_type = Model_Lottery_Type::last_for_lottery($lotteryWithProvider['id']);
        $lottery_id = $lottery_type['lottery_id'];
        $whitelabel_id = $user->whitelabel_id;
        $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
            $whitelabel_id,
            $lottery_id
        )[0];
        $type_of_lottery = $lotteryWithProvider['type'];
        $multipliers = Model_Lottery_Type_Multiplier::for_ticket_saving()[$lotteryWithProvider['id']] ?? [];

        // having those models now create dummy tickets for next draw
        for ($ticketIndex = 0; $ticketIndex < $this->tickets_count; $ticketIndex++) {
            $lines_per_ticket = $this->lines_per_ticket ?: rand(1, 25);
            $with_factories = [];
            $ticket_multiplier = 1;

            // run additional factories according to the type of lottery
            switch ($type_of_lottery) {
                case 'keno':
                    $ticket_multiplier = $this->ticket_multiplier;
                    $multipliers_filtered = array_filter($multipliers, function ($multiplier) use (&$ticket_multiplier) {
                        return (int)$ticket_multiplier === (int)$multiplier['multiplier'];
                    });
                    if (true === empty($multipliers_filtered)) {
                        throw new Exception('Unsupported ticket multiplier');
                    }
                    $multiplier = array_values($multipliers_filtered)[0]; // just to be sure that we always get the first element of the filtered array

                    $with_factories = array_merge($with_factories, [
                        Test_Factory_Whitelabel_User_Ticket_Keno_Data::class => [
                            'values' => [
                                'lottery_type_multiplier_id' => $multiplier['id'],
                                'numbers_per_line' => $this->numbers_per_line,
                            ]
                        ]
                    ]);
                    break;
                default:
                    break;
            }
            /** @var object $lottery_type */
            $factory = Test_Factory_Whitelabel_Transaction::create([
                'whitelabel_id' => $user->whitelabel_id,
                'whitelabel_user_id' => $user->id,
                'currency_id' => $user->currency_id,
            ])
                ->with(Test_Factory_Whitelabel_User_Ticket::class, [
                    'whitelabel_id' => $user->whitelabel_id,
                    'lottery_id' => $lotteryWithProvider['id'],
                    'lottery_type_id' => $lottery_type->id,
                    'lottery_provider_id' => $lotteryWithProvider['lottery_provider_id'],
                    'valid_to_draw' => $drawDate = $this->draw_date ?: Helpers_Lottery::calculate_next_draw_datetime($lotteryWithProvider),
                    'draw_date' => $drawDate,
                    'line_price' => $lotteryWithProvider['price'],
                    'line_count' => $lines_per_ticket,
                    'ticket_multiplier' => $ticket_multiplier,
                ]);
            $has_additional_factories = !empty($with_factories);
            if ($has_additional_factories) {
                $factory->with_multiple_without_reusable($with_factories);
            }
            $slips_count = (int) NumberHelper::roundUpWhenNumberAfterPrecisionIsBiggerThenZero($lines_per_ticket / $this->lines_per_slip, 0);
            for ($i = 0; $i < $slips_count; $i++) {
                $is_on_last_slip = $slips_count - 1 === $i;
                $lines_per_slip = $this->lines_per_slip;
                if ($is_on_last_slip) {
                    $lines_per_slip = $lines_per_ticket - (($slips_count - 1) * $this->lines_per_slip); // last slip is construed from lines_per_ticket subtracted over sum of slip lines up to previous slip.
                }

                $with_factories = [
                    Test_Factory_Whitelabel_User_Ticket_Slip::class => [
                            'values' => [
                                'whitelabel_lottery_id' => $whitelabel_lottery['id']
                            ]
                        ],
                    Test_Factory_Whitelabel_User_Ticket_Line::class => [
                        'values' => [
                            'lottery_type' => $lottery_type,
                        ],
                        'count' => $lines_per_slip,
                    ],
                ];
                if ($this->numbers_per_line !== null){
                    $with_factories[Test_Factory_Whitelabel_User_Ticket_Line::class]['numbers_per_line'] = $this->numbers_per_line;
                }
                $factory->with_multiple($with_factories, true, $i);
            }
            // TODO: {Vordis 2019-12-03 14:09:17} autonomous clear of result
            $factory->get_result();
        }
    }
}
