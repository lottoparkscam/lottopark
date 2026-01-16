<?php

use Services\LcsService;

abstract class LottoLotteriesLotteryLcs extends Lotto_Lotteries_Lottery
{
    public function getResultsFromLcs(string $lotterySlug): array
    {
        $lcsService = Container::get(LcsService::class);
        $draws = $lcsService->getLotteryDrawsFromLcs($lotterySlug, $this->lottery['currency']);

        $data = [];

        foreach($draws as $draw) {
            $data[] = [
                'timestamp' => $draw->getDate()->getTimestamp(),
                'numbers' => $draw->getNumbers(),
            ];
        }

        return $data;
    }
}
