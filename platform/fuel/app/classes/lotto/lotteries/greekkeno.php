<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_GreekKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 1; // 100 000 * 10 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'Europe/Athens';

    protected string $lottery_slug = Lottery::GREEK_KENO_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }

        try {
            $results = $this->get_primary_json_results();
            $numbers = $this->process_results($results);
            $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
            return;
        } catch (\Throwable $e) {
            $useSecondarySource = true;
            echo $e->__toString();
        }

        try {
            if ($useSecondarySource) {
                $results = $this->get_secondary_json_results();
                $numbers = $this->process_results($results);
                $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
                return;
            }
        } catch (\Throwable $e) {
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'All Sources',
                exception: $e,
                nextDrawDateFormatted: $this->providerNextDrawDate->format('YmdHi'),
                delayInHours: 2,
            );
            echo $e->__toString();
        }
    }

    public function get_primary_json_results(): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.opap.gr/draws/v3.0/1100/last/100");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        
        if ($data == true) {
            $jsonDraws = json_decode($data, true);
            if (empty($jsonDraws)) {
                throw new Exception('Empty reply from server');
            }
        } else {
            throw new Exception('No data received');
        }
        curl_close($ch);

        return $jsonDraws;
    }

    public function get_secondary_json_results(): array
    {
        $nextDrawDay = $this->providerNextDrawDate->format('Y-m-d');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.opap.gr/draws/v3.0/1100/draw-date/{$nextDrawDay}/{$nextDrawDay}?limit=200");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        
        if ($data == true) {
            $jsonDraws = json_decode($data, true);
            if (empty($jsonDraws)) {
                throw new Exception('Empty reply from server');
            }
        } else {
            throw new Exception('No data received');
        }
        curl_close($ch);

        if (!array_key_exists('content', $jsonDraws) || empty($jsonDraws['content'])) {
            throw new Exception('No data in API');
        }

        return $jsonDraws['content'];
    }

    public function process_results(array $results, bool $isEnabledDrawDateFix = false): array
    {
        $drawIndex = -1;
        for ($index = 0; $index < count($results); $index++) {
            if (isset($results[$index]) && is_array($results[$index]) && isset($results[$index]['status']) && isset($results[$index]['drawTime'])) {
                if ($results[$index]['status'] === 'results' && Carbon::createFromTimestamp($results[$index]['drawTime'] / 1000, self::PROVIDER_TIMEZONE)->format('Y-m-d H:i') === $this->providerNextDrawDate->format('Y-m-d H:i')) {
                    $drawIndex = $index;
                    break;
                }
            } else {
                throw new Exception('Invalid data in API');
            }
        }

        if ($drawIndex === -1) {
            if ($isEnabledDrawDateFix) {
                $this->drawDateFix($this->providerNextDrawDate);
            }
            throw new Exception($this->lottery_slug . ' - primary source unable to find draw');
        }
        
        $numbers = $results[$drawIndex]['winningNumbers']['list'];
        sort($numbers);
        
        return $numbers ?? [];
    }
}
