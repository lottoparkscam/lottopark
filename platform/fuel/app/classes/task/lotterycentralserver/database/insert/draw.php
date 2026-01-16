<?php

use Carbon\Carbon;
use Fuel\Core\DB;
use Helpers\ArrayHelper;

/**
 * Insert new draw based on data from LCS (fetch task result).'
 * NOTE: it will also insert it's prizes.
 */
final class Task_Lotterycentralserver_Database_Insert_Draw extends Task_Lotterycentralserver_Database_Lottery
{
    protected function get_date_download(): string
    {
        return Helpers_Time::now();
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
        Helpers_Cli::info("Inserting draws into database");
        $data = $this->get_previous_task_result()->get_data();
        $draws = $data['last_draws'];
        Helpers_Cli::writeln("Draws count: " . count($draws));
        $this->query = DB::insert('lottery_draw');
        $columns = [
            'lottery_id',
            'lottery_type_id',
            'draw_no',
            'date_download',
            'date_local',
            'numbers',
            'bnumbers',
            'total_prize',
            'total_winners',
            'final_jackpot',
            'jackpot'
        ];
        $this->query->columns($columns);
        $lottery_types = $this->get_lottery_types($draws);
        $draws_models = [];
        $lottery_provider = Model_Lottery_Provider::last_for_lottery($this->lottery->id);
        $kenoCacheKeysToDelete = [];

        foreach ($draws as $draw_key => $draw) {
            // get binding rule for lottery
            Helpers_Cli::writeln("Inserting a draw " . ($draw_key + 1) . "/" . count($draws) . " (draw_no=" . $draw['draw_no'] . ") into database");
            $draw_date = Carbon::parse($draw['date'], $lottery_provider->timezone)->setTimezone($this->lottery->timezone);
            array_filter($lottery_types, function ($lottery_type) use ($draw_date) {
                $date_start = $lottery_type['date_start'] ? Carbon::createFromTimeString($lottery_type['date_start']) : null;
                $date_end = $lottery_type['date_end'] ? Carbon::createFromTimeString($lottery_type['date_end']) : null;

                return ($draw_date >= $date_start || is_null($date_start))
                    && ($draw_date <= $date_end || is_null($date_end));
            });
            if (empty($lottery_types)) {
                throw new Exception("Lottery type not found");
            }
            if (count($lottery_types) > 1) {
                throw new Exception("More than one lottery type match the criteria");
            }
            $lottery_type = ArrayHelper::first($lottery_types);

            // top prize value, if not won will be 0 (LCS)
            $top_prize_value = 0;
            if (!empty($draw['lottery_prizes'])) {
                $lottery_prizes = array_values($draw['lottery_prizes']);
                $top_prize = ArrayHelper::first($lottery_prizes);
                $top_prize_value = $top_prize['total'];
            }

            // add set of values to the batch
            $values = [
                $this->get_lottery()->id,
                $lottery_type['id'],
                $draw['draw_no'],
                $this->get_date_download(),
                $draw_date->format(Helpers_Time::DATETIME_FORMAT),
                implode(",", $draw['numbers'][0]),
                empty($draw['numbers'][1]) ? null : implode(",", $draw['numbers'][1]),
                $draw['prize_total'],
                $draw['lines_won_count'],
                $top_prize_value,
                Helper_Lottery::calculate_jackpot_value($draw['jackpot'])
            ];
            $this->query->values($values);
            $draws_models[] = Model_Lottery_Draw::forge(array_combine($columns, $values));

			$kenoCacheKeysToDelete += [Model_Lottery_Draw::$cache_list[0] . '.' . $this->lottery['id'] . '.' . $draw_date->format(Helpers_Time::DATE_FORMAT)];
        }

        $first_id = $this->query->execute()[0];

        if ($this->lottery->is_keno()) {
        	foreach ($kenoCacheKeysToDelete as $key) {
				Lotto_Helper::clear_cache_item($key);
			}
		} else {
			Lotto_Helper::clear_cache(['model_lottery_draw']);
		}

        foreach ($draws_models as $draw) {
            $draw->set(['id' => $first_id++]);
        }
        // set saved models into result data
        Helpers_Cli::success("The draws have been stored, count: " . count($draws_models));
        $this->get_result()->put_data_item('draws', $draws_models);
    }

    public function get_lottery_types($draws): array
    {
        return Model_Lottery_Type::get_lottery_types($draws, $this->get_lottery()->id);
    }
}
