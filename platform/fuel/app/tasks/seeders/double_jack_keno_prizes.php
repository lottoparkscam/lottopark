<?php

namespace Fuel\Tasks\Seeders;

use Model_Lottery_Type_Data;


class Double_Jack_Keno_Prizes extends Seeder
{
    const FIRST_MULTIPLIER_ID = 11;

    protected function columnsStaging(): array
    {
        return [
            'lottery_prize_data' => ['lottery_draw_id', 'lottery_type_data_id', 'winners', 'prizes', 'lottery_type_multiplier_id']
        ];
    }

    private function prize_data_rows(): array
    {
        $prizes = [];
        $type_datas = Model_Lottery_Type_Data::get_lottery_type_data(\Model_Lottery::find_by_pk(Double_Jack_Keno_Integration::LOTTERY_ID)->to_array());
        if (is_null($type_datas)) {
            throw new \Exception('Could not find DoubleJack Keno Model_Lottery_Type_Data');
        }

        foreach (Double_Jack_Keno_Integration::lottery_multipliers_rows() as $i_multiplier => $multiplier) {
            foreach ($type_datas as $type_data){
                $prizes[] = [
                    null,
                    $type_data['id'],
                    null,
                    $multiplier[0] * $type_data['prize'],
                    self::FIRST_MULTIPLIER_ID + $i_multiplier
                ];
            }
        }

        return $prizes;
    }

    protected function rowsStaging(): array
    {
        return [
            'lottery_prize_data' => $this->prize_data_rows()
        ];
    }
}