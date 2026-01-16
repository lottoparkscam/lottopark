<?php

use Fuel\Core\DB;

/**
 * Insert prizes into database.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-12
 * Time: 10:33:04
 */
final class Task_Lotterycentralserver_Database_Insert_Prizes extends Task_Lotterycentralserver_Database_Task
{
    /**
     * Model to which prizes belongs to.
     *
     * @var Model_Lottery_Draw[]
     */
    private $lottery_draws;

    /**
     * @var array
     */
    private $multipliers = [];

    /**
     * Create new insert prizes task.
     *
     * @param Model_Lottery_Draw[] $lottery_draws     Model to which prizes belongs to.
     * @param Task_Result          $draw_fetch_result Result of the fetch draw task.
     * @param array                $multipliers
     */
    public function __construct(array $lottery_draws, Task_Result $draw_fetch_result, array $multipliers)
    {
        parent::__construct($draw_fetch_result);
        $this->set_lottery_draws($lottery_draws);
        $this->set_multipliers($multipliers);
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     *
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        Helpers_Cli::info("Inserting prizes into database");
        // prepare an insert statement
        $this->query = DB::insert('lottery_prize_data'); // TODO: {Vordis 2019-06-12 10:51:35} could be defined in model
        // Set the columns
        $this->query->columns(
            [
                'lottery_draw_id',
                'lottery_type_data_id',
                'winners',
                'prizes',
                'lottery_type_multiplier_id',
            ]
        );

        $last_draws = $this->previous_task_result->get_data()['last_draws'];
        if (empty($last_draws)) {
            Helpers_Cli::warning("Last draws array is empty");

            return;
        }

        foreach ($this->lottery_draws as $draw_key => $draw) {
            Helpers_Cli::writeln("Inserting prizes for draw " . ($draw_key + 1) . "/" . count($this->lottery_draws));
            if (!isset($last_draws[$draw_key]['lottery_prizes'])){
                throw new Exception("The draw being inserted has no prize data.");
            }
            $prizes = $last_draws[$draw_key]['lottery_prizes'];
            $prizes_count = count($prizes);
            // fetch rule tiers (lottery_type) from whitelotto
            $lottery_type_datum =
                Model_Lottery_Type_Data::tail_for_lottery_type(
                    $draw->lottery_type_id,
                    $prizes_count
                );
            if ($lottery_type_datum === null) {
                throw new \Exception('Unable to find lottery_type_datum!');
            }

            // we need to reverse datum, they are fetched from lowest to highest (revers of insert), and we need in LCS order (highest to lowest)
            $lottery_type_datum = array_reverse($lottery_type_datum);

            // Set the values
            if ($lottery_type_datum[0]['slug'] === null) {
                // Old method, compatibility matter, soon to be deprecated
                // In order to use the new method, lottery_rule_tier.slug should be filled in
                // TODO: Get rid of the method called below as soon as all lottery_rule_tiers have their slugs
                $this->addQueryValues($lottery_type_datum, $prizes, $draw);
            } else {
                // Keno only works with slugs
                $this->addQueryValuesBySlugs($lottery_type_datum, $prizes, $draw);
            }

            $this->query->execute();
            Lotto_Helper::clear_cache(['model_lottery_prize_data']);
        }
    }

    /**
     * @property array $lottery_type_datum
     * @property array $prizes
     * 
     * @property Model_Lottery_Draw $draw
     * 
     * @return void
     */
    private function addQueryValues(array $lottery_type_datum, array &$prizes, Model_Lottery_Draw $draw)
    {
        $prizes_count = count($prizes);
        for ($i = 0; $i < $prizes_count; $i++) {
            $multiplier_id = null;
            if (isset($prizes[$i]['multiplier']['multiplier']) && empty($this->multipliers) === false) {
                $multiplier_id = (int)$this->multipliers[$prizes[$i]['multiplier']['multiplier']]['id'];
            }

            $this->query->values(
                [
                    $draw->id,
                    $lottery_type_datum[$i]->id,
                    $prizes[$i]['lines_won_count'],
                    $prizes[$i]['per_user'],
                    $multiplier_id
                ]
            );
        }
    }

    private function addQueryValuesBySlugs(array $lottery_type_datum, array &$prizes, Model_Lottery_Draw $draw)
    {
        foreach ($prizes as $prize) {
            $slug_to_find = $prize['lottery_rule_tier']['slug'];
            $lottery_type_data = array_filter($lottery_type_datum, function ($lottery_type_data) use ($slug_to_find) {
                return $lottery_type_data['slug'] === $slug_to_find;
            }); // This should throw an error if improper slug has been stored
            if (empty($lottery_type_data)) {
                throw new Exception("Slug " . $slug_to_find . " not found.");
            }
            $lottery_type_data = array_values($lottery_type_data)[0];

            $multiplier_id = null;
            if (isset($prize['multiplier']['multiplier']) && empty($this->multipliers) === false) {
                $multiplier_id = (int)$this->multipliers[$prize['multiplier']['multiplier']]['id'];
            }
            $this->query->values(
                [
                    $draw->id,
                    $lottery_type_data['id'],
                    $prize['lines_won_count'],
                    $prize['per_user'],
                    $multiplier_id
                ]
            );
        }
    }

    /**
     * @param array $multipliers
     */
    public function set_multipliers(array $multipliers): void
    {
        $this->multipliers = $multipliers;
    }

    /**
     * @param Model_Lottery_Draw[] $lottery_draws
     */
    public function set_lottery_draws(array $lottery_draws): void
    {
        $this->lottery_draws = $lottery_draws;
    }
}
