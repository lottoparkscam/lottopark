<?php

use Repositories\LotteryLogRepository;

class Lotto_Lotteries_LottoPL extends Lotto_Lotteries_Lottery
{
    protected string $lottery_slug = 'lotto-pl';

    /**
     *
     * @throws Exception
     */
    public function get_results(): void
    {
        $lottery = $this->lottery;
        $sourceId = $lottery['source_id'];
        if ($sourceId == 13) {//lotto.pl MOBILE API
            $this->get_results_primary();
        } elseif ($sourceId == 14) {// lotto.pl SITE
            $this->get_results_secondary($lottery);
        }
    }

    /**
     *
     * @throws Exception
     */
    public function get_results_primary()
    {
        $lottery = $this->lottery;
        $jackpot = null;
        $date = null;
        $date_utc = null;
        $numbers = [];
        $bonus_numbers = [];
        $prizes = [];
//        $overwrite_jackpot = false;
        $data = Lotto_Helper::load_HTML_url('https://app.lotto.pl/wyniki/?type=dl');
        $data = explode("\n", $data);
        if (empty($data) || count($data) != 8) {
            throw new Exception("Bad draw length.");
        }
        $datea = trim($data[0]);
        $date = $this->calculateNextDueDrawDatetime($datea, $lottery['current_jackpot']);
        if ($date === null) {
            echo "Lottery not updated - draw date is not due." . PHP_EOL;
            return;
        }
        $date_utc = $date->clone()->setTimezone(new DateTimeZone("UTC"));

        foreach ($data as $key => $num) {
            if ($key != 0) {
                $value = intval($num);
                if ($value <= 0) {
                    throw new Exception("Bad draw value (<=0).");
                }
                $numbers[] = $value;
            }
            if ($key == 6) {
                break;
            }
        }
        $data = Lotto_Helper::load_HTML_url('https://app0.lotto.pl/losowania/kumulacja.txt');

        $data = explode("\n", $data);

        if (empty($data) || count($data) != 2) {
            throw new Exception("Bad jackpot count.");
        }

        $dateb = trim($data[0]);
        if ($dateb != $datea) {
            throw new Exception("Date mismatch.");
        }

        $data = explode("\t", $data[1]);
        $jackpot = intval($data[1]);

        // API is a little buggy (jackpot is delayed for some minutes)
//        $overwrite_jackpot = true;
        // winners data
        $data = Lotto_Helper::load_HTML_url('https://app.lotto.pl/wygrane/?type=dl');
        $data = explode("\n", $data);

        if (empty($data) || count($data) != 6) {
            throw new Exception("Bad prize data.");
        }

        $datec = trim($data[0]);
        if ($datec != $datea) {
            throw new Exception("Date mismatch #2.");
        }

        foreach ($data as $key => $num) {
            if ($key != 0) {
                $winners = explode("\t", $num);

                if (empty($winners) || count($winners) != 2) {
                    throw new Exception("Bad winners data count.");
                }
                $prizes[] = $winners;
            }
            if ($key == 4) {
                break;
            }
        }
        $this->set_lottery_with_data($lottery, $jackpot, $date, $date_utc, $numbers, $bonus_numbers, $prizes, $this->overwrite_jackpot);
    }

    /**
     *
     * @param array $lottery
     * @throws Exception
     */
    public function get_results_secondary($lottery)
    {
        /** @var LotteryLogRepository $lotteryLogRepository */
        $lotteryLogRepository = Container::get(LotteryLogRepository::class);

        $jackpot = null;
        $date = null;
        $date_utc = null;
        $numbers = [];
        $bonus_numbers = [];
        $prizes = [];
//        $overwrite_jackpot = false;
        $doc = new DOMDocument;
        $doc->loadHTML(Lotto_Helper::load_HTML_url("https://www.lotto.pl/"));
        $xpath = new DOMXPath($doc);

        $query = "//div[contains(concat(@class, ' '), 'glowna_wyniki_lotto ')]/div[contains(@class, 'wynik_lotto')]";
        $draw = $xpath->query($query);
        if ($draw->length != 6) {
            throw new Exception("Bad draw length.");
        }
        foreach ($draw as $key => $item) {
            $value = intval($item->nodeValue);
            if ($value <= 0) {
                throw new Exception("Bad draw value (<=0).");
            }
            $numbers[] = $value;
        }
        $query = "//div[contains(concat(@class, ' '), 'start-wyniki_lotto ')][1]/div[contains(concat(@class, ' '), 'wyniki_data ')]/strong[1]";
        $date = $xpath->query($query);
        if ($date->length != 1) {
            throw new Exception("Bad date.");
        }
        $date = trim($date->item(0)->nodeValue);
        $query = "//div[contains(@class, 'rotator_content')]";
        $jackpot = $xpath->query($query);
        $date = $this->calculateNextDueDrawDatetime($date, $jackpot);
        if ($date === null) {
            echo "Lottery not updated - draw date is not due." . PHP_EOL;
            return;
        }
        $date_utc = $date->clone()->setTimezone(new DateTimeZone("UTC"));

        if (!$jackpot->length) {
            $jackpot = false;
            $lotteryLogRepository->addWarningLog(
                $lottery['id'],
                'Current jackpot not found. Received jackpot length is != 1.'
            );
        } else {
            foreach ($jackpot as $key => $item) {
                $data = trim($item->ownerDocument->saveHTML($item));
                if (preg_match('/\/images\/kumulacje/u', $data, $m)) {
                    if (preg_match('/[0-9.]{2,}\.jpg/u', $data, $m)) {
                        $jackpot = intval($m[0]) / 1000000;
                    } elseif (preg_match('/rozbita\.jpg/u', $data, $m)) {
                        $jackpot = 2;
                    }
                }
            }
            if ($jackpot == null) {
                $jackpot = false;
                $lotteryLogRepository->addWarningLog(
                    $lottery['id'],
                    'Current jackpot not found. Received data is null.'
                );
            }
        }

        // winners data
        $doc->loadHTML(Lotto_Helper::load_HTML_url("https://www.lotto.pl/lotto/wyniki-i-wygrane"));
        $xpath = new DOMXPath($doc);

        $query = "//table[contains(@class, 'ostatnie-wyniki-table')][1]/tbody[1]/tr[2]";

        $prizedata = $xpath->query($query);
        if ($prizedata->length == 0) {
            throw new Exception("Bad prize data length.");
        }

        $prizedata = $prizedata->item(0)->getElementsByTagName('td');
        if ($prizedata->length != 5) {
            throw new Exception("Bad prize data td length.");
        }
        $dateb = trim($prizedata->item(2)->nodeValue);
        if (preg_match('/^[0-9-]+/u', $dateb, $m)) {
            $dateb = $m[0];
            $dateb = DateTime::createFromFormat(
                'd-m-y H:i:s',
                $dateb . ' 00:00:00',
                new DateTimeZone("UTC")
            );
            if ($dateb->format('Y-m-d') != $date->format("Y-m-d")) {
                throw new Exception("Date mismatch.");
            }
        } else {
            throw new Exception("Bad date preg.");
        }

        $prizedata = $prizedata->item(4)->getElementsByTagName('div');
        if ($prizedata->length != 1) {
            throw new Exception("Bad prizedata length.");
        }

        $prizedata = $prizedata->item(0);
        $prizedata = $prizedata->getAttribute('title');

        $prizedoc = new DOMDocument();
        $prizedoc->loadHTML($prizedata);

        $prizedata = $prizedoc->getElementsByTagName('table');
        if ($prizedata->length != 1) {
            throw new Exception("Bad prizedata table length.");
        }
        $prizedata = $prizedata->item(0)->getElementsByTagName('tr');

        for ($i = 1; $i < 5; $i++) {
            $tds = $prizedata->item($i)->getElementsByTagName('td');
            if ($tds->length != 3) {
                throw new Exception("Bad prizedata tds length.");
            }
            $winners_cnt = trim($tds->item(1)->nodeValue);
            $winners_prz = trim($tds->item(2)->nodeValue);
            $winners_prz = str_replace(' ', '', $winners_prz);

            $prizes[] = [$winners_cnt, $winners_prz];
        }
        $this->set_lottery_with_data($lottery, $jackpot, $date, $date_utc, $numbers, $bonus_numbers, $prizes, $this->overwrite_jackpot);
    }
}
