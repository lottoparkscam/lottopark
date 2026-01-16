<?php

namespace Fuel\Tasks\Seeders;

class Update_Closing_Time extends Reseeder
{
    const CLOSING_TIME = [
        "22:00:00", //powerball
        "22:00:00", //mega-millions
        "19:00:00", //eurojackpot
        "19:30:00", //superenalotto
        "19:30:00", //lotto-uk
        "18:30:00", //euromillions
        "21:40:00", //lotto-pl
        "21:00:00", //la-primitiva
        "21:00:00", //bonoloto
        "19:30:00", //oz-lotto
        "19:30:00", //powerball-au
        "19:30:00", //saturday-lotto-au
        "19:30:00", //monday-wednesday-lotto-au
        "21:00:00", //el-gordo-primitiva
        "20:15:00", //lotto-fr
        "19:30:00", //gg-world
        "19:30:00", //gg-world-x
        "19:30:00", //gg-world-million
        "19:00:00", //mega-sena
        "19:00:00", //quina
        "18:45:00", //otoslotto
        "16:00:00", //hatoslotto
        "19:30:00", //set-for-life-uk
        "19:30:00", //thunderball
        "22:00:00", //lotto-america
        "18:00:00", //lotto-at
        "19:00:00", //lotto-6aus49
        "18:00:00", //skandinav-lotto
        "20:00:00", //multi-multi
        "19:30:00", //gg-world-keno
    ];

    const PROVIDER_IDS_STAGING = [
        2, //powerball
        5, //mega-millions
        8, //eurojackpot
        10, //superenalotto
        13, //lotto-uk
        16, //euromillions
        19, //lotto-pl
        20, //la-primitiva
        21, //bonoloto
        22, //oz-lotto
        23, //powerball-au
        24, //saturday-lotto-au
        25, //monday-wednesday-lotto-au
        26, //el-gordo-primitiva
        27, //lotto-fr
        29, //gg-world
        31, //gg-world-x
        32, //gg-world-million
        34, //mega-sena
        35, //quina
        36, //otoslotto
        37, //hatoslotto
        38, //set-for-life-uk
        39, //thunderball
        40, //lotto-america
        41, //lotto-at
        42, //lotto-6aus49
        43, //skandinav-lotto
        44, //multi-multi
        45, //gg-world-keno
    ];

    const PROVIDER_IDS_PRODUCTION = [
        2, //powerball
        5, //mega-millions
        8, //eurojackpot
        10, //superenalotto
        13, //lotto-uk
        16, //euromillions
        19, //lotto-pl
        20, //la-primitiva
        21, //bonoloto
        22, //oz-lotto
        23, //powerball-au
        24, //saturday-lotto-au
        25, //monday-wednesday-lotto-au
        26, //el-gordo-primitiva
        27, //lotto-fr
        29, //gg-world
        31, //gg-world-x
        32, //gg-world-million
        34, //mega-sena
        35, //quina
        36, //otoslotto
        37, //hatoslotto
        38, //set-for-life-uk
        39, //thunderball
        40, //lotto-america
        41, //lotto-at
        42, //lotto-6aus49
        45, //gg-world-keno
    ];

    const CLOSING_TIMES_STAGING = [
        34 => "{\"3\": \"19:00:00\", \"6\": \"12:00:00\"}", //mega-sena
        35 => "{\"1\": \"19:00:00\", \"2\": \"19:00:00\", \"3\": \"19:00:00\", \"4\": \"19:00:00\", \"5\": \"19:00:00\", \"6\": \"12:00:00\"}", //quina
        41 => "{\"3\": \"18:30:00\", \"7\": \"18:00:00\"}", //lotto-at
        42 => "{\"3\": \"18:00:00\", \"6\": \"19:00:00\"}", //lotto-6aus49
    ];

    const CLOSING_TIMES_PRODUCTION = [
        34 => "{\"3\": \"19:00:00\", \"6\": \"12:00:00\"}", //mega-sena
        35 => "{\"1\": \"19:00:00\", \"2\": \"19:00:00\", \"3\": \"19:00:00\", \"4\": \"19:00:00\", \"5\": \"19:00:00\", \"6\": \"12:00:00\"}", //quina
        41 => "{\"3\": \"18:30:00\", \"7\": \"18:00:00\"}", //lotto-at
        42 => "{\"3\": \"18:00:00\", \"6\": \"19:00:00\"}", //lotto-6aus49
    ];

    protected function rowsStaging(): array
    {
        return $this->provider_rows(
            array_combine(self::PROVIDER_IDS_STAGING, self::CLOSING_TIME),
            self::CLOSING_TIMES_STAGING
        );
    }

    protected function rowsProduction(): array
    {
        return $this->provider_rows(
            array_combine(self::PROVIDER_IDS_PRODUCTION, self::CLOSING_TIME),
            self::CLOSING_TIMES_PRODUCTION
        );
    }

    protected function provider_rows(array $closing_time, array $closing_times): array
    {
        $rows = [
            'lottery_provider' => []
        ];
        foreach ($closing_time as $provider_id => $hour) {
            $row = [
                'where' => [["id", "=", $provider_id]],
                'set' => ['closing_time' => $hour]
            ];
            if (isset($closing_times[$provider_id])) {
                $row['set']['closing_times'] = $closing_times[$provider_id];
            }
            $rows['lottery_provider'][] = $row;
        }

        return $rows;
    }
}