<?php

namespace Fuel\Tasks;

use DateTime;
use DateInterval;

class Report_Sales
{
    private $emails;
    private $domain;

    private $start_date;
    private $end_date;

    private function loadConfig()
    {
        \Config::load("lotteries", true);

        $this->emails = \Config::get("lotteries.sale_report_emails");
        $this->domain = \Config::get("lotteries.domain");
    }

    private function checkConfig()
    {
        if (empty($this->emails) || empty($this->domain)) {
            echo "No e-mails defined for this task. Please check SALE_REPORT_EMAILS .env variable";
            return false;
        }
        return true;
    }

    private function setDateRange()
    {
        $yesterday = new DateTime("yesterday");
        $week_ago = (clone $yesterday)->sub(new DateInterval("P1W"));
        $yesterday->setTime(23, 59, 59);

        $this->start_date = $week_ago;
        $this->end_date = $yesterday;
    }

    private function getDatesBetweenRange()
    {
        $all_days = [];
        $current = clone $this->start_date;
        while ($current <= $this->end_date) {
            $all_days[] = $current->format("Y-m-d");
            $current->add(new DateInterval("P1D"));
        }
        return $all_days;
    }

    private function prepareAndRunStatement()
    {
        $sql = \DB::select(
            'w.id',
            'w.name',
            'wt.type',
            [\DB::expr('DATE_FORMAT(wt.date_confirmed, "%Y-%m-%d")'), 'date_formatted'],
            [\DB::expr('COUNT(*)'), 'count'],
            [\DB::expr('SUM(wt.amount_usd)'), 'sum']
        )
        ->from(['whitelabel_transaction', 'wt'])
        ->join(['whitelabel', 'w'], 'left')->on('w.id', '=', 'wt.whitelabel_id')
        ->where('wt.date_confirmed', 'BETWEEN', [$this->start_date->format('Y-m-d H:i:s'), $this->end_date->format('Y-m-d H:i:s')])
        ->and_where('wt.status', '=', 1)
        ->group_by('whitelabel_id', 'date_formatted', 'type')
        ->order_by('w.id')
        ->order_by('type')
        ->order_by('date_formatted');

        return $sql->execute()->as_array();
    }

    public function prepareDataForEmail($result, &$all_days)
    {
        $prepared_results = [];
        foreach ($result as $item) {
            $prepared_results[$item['name']][$item['type']][$item['date_formatted']] = [$item['sum'], $item['count']];
        }

        $final_table = [];
        foreach ($result as $item) {
            $final_table[$item['name']] = [];
        }

        foreach ($final_table as $key => $item) {
            foreach ([0, 1] as $type) {
                foreach ($all_days as $date) {
                    $final_table[$key][$type][$date] = [0, 0];
                    if (isset($prepared_results[$key][$type][$date])) {
                        $final_table[$key][$type][$date] = $prepared_results[$key][$type][$date];
                    }
                }
            }
        }

        return $final_table;
    }

    private function generateHtmlForEmail($final_table)
    {
        $html = "<h1>Daily report from the last week</h1>";
        foreach ($final_table as $whitelabel_name => $whitelabel_data) {
            foreach ($whitelabel_data as $type => $dates) {
                $html .= "<h2>".$whitelabel_name." - ".$this->formatType($type)."</h2>\n";
                $html .= '<table border="1" cellspacing="0" cellpadding="4" style="border: 1px solid black; border-collapse: collapse;">'."\n";
                $html .= "<tr>\n";
                foreach ($dates as $date => $daily_data) {
                    $html .= "<th>".$date."</th>\n";
                }

                $html .= "</tr><tr>\n";
                foreach ($dates as $date => $daily_data) {
                    $html .= "<td>$".$daily_data[0]." (".$daily_data[1].")</td>\n";
                }
                $html .= "</tr>\n";
                $html .= "</table>\n";
            }
        }
        return $html;
    }

    private function sendEmail($html)
    {
        \Package::load('email');
        $email = \Email::forge();
        $email->from('noreply@'.$this->domain);
        $email->subject('White Lotto daily report from the last week');
        $email->to($this->emails);
        $email->html_body($html);
        $email->send();
    }

    protected function formatType($type)
    {
        switch ($type) {
            case \Helpers_General::TYPE_TRANSACTION_PURCHASE:
                return 'Purchases';
            case \Helpers_General::TYPE_TRANSACTION_DEPOSIT:
                return 'Deposits';
        }
        return "Unknown type";
    }

    public function run()
    {
        $this->loadConfig();
        if (!$this->checkConfig()) {
            return;
        }

        $this->setDateRange();

        $all_days = $this->getDatesBetweenRange();

        $result = $this->prepareAndRunStatement();

        $final_table = $this->prepareDataForEmail($result, $all_days);

        $html = $this->generateHtmlForEmail($final_table);

        $this->sendEmail($html);
    }
}
