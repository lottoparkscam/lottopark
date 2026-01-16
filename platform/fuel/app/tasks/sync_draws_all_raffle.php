<?php


namespace Fuel\Tasks;


use Models\Raffle;

class Sync_Draws_All_Raffle
{
    public function run()
    {
        $raffles = Raffle::find('all', [
            'where' => [
                'is_enabled' => true
            ]
        ]);

        if (!empty($raffles)) {
            /** @var Raffle $raffle */
            foreach ($raffles as $raffle) {
                $slug = $raffle->slug;
                shell_exec("php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r sync_draws $slug > /dev/null 2>&1 &");
            }
        }
    }
}