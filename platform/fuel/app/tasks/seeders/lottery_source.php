<?php

namespace Fuel\Tasks\Seeders;

/**
* Lottery Source seeder.
*/
final class Lottery_Source extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'lottery_source' => ['id', 'lottery_id', 'name', 'website']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'lottery_source' => [
                [1, 1, 'powerball.com SITE', 'http://www.powerball.com'],
                [2, 1, 'valottery.com SITE/powerball.com SITE', 'https://valottery.com'],
                [3, 2, 'megamillions.com SITE', 'http://www.megamillions.com'],
                [4, 2, 'valottery.com XML/megamillions.com SITE', 'https://valottery.com'],
                [5, 3, 'eurojackpot.de JSON/SITE', 'http://eurojackpot.de'],
                [6, 3, 'loterija.si SITE', 'http://loterija.si'],
                [7, 4, 'superenalotto.it XML/JSON/SITE', 'http://www.superenalotto.it'],
                [8, 4, 'sisal.it XML/JSON/SITE', 'http://www.sisal.it'],
                [9, 5, 'national-lottery.co.uk SITE', 'https://www.national-lottery.co.uk'],
                [10, 5, 'lottery.co.uk SITE UNOFFICIAL', 'https://www.lottery.co.uk'],
                [11, 6, 'loteriasyapuestas.es SITE', 'http://www.loteriasyapuestas.es'],
                [12, 6, 'fdj.fr SITE', 'https://www.fdj.fr'],
                [13, 7, 'lotto.pl MOBILE API', 'http://www.lotto.pl'],
                [14, 7, 'lotto.pl SITE', 'http://www.lotto.pl'],
                [15, 4, 'superenalotto.com SITE UNOFFICIAL', 'http://www.superenalotto.com'],
                [16, 8, 'loteriasyapuestas.es SITE OFFICIAL', 'https://www.loteriasyapuestas.es'],
                [17, 9, 'loteriasyapuestas.es SITE OFFICIAL', 'https://www.loteriasyapuestas.es'],
                [18, 10, 'thelott.com SITE OFFICIAL', 'https://thelott.com'],
                [19, 11, 'thelott.com SITE OFFICIAL', 'https://thelott.com'],
                [20, 12, 'thelott.com SITE OFFICIAL', 'https://thelott.com'],
                [21, 13, 'thelott.com SITE OFFICIAL', 'https://thelott.com'],
                [22, 14, 'loteriasyapuestas.es SITE OFFICIAL', 'https://www.loteriasyapuestas.es'],
                [23, 15, 'fdj.fr SITE OFFICIAL', 'https://www.fdj.fr/'],
            ]
        ];
    }

}