<?php

use Repositories\{
    LottorisqLogRepository,
    Orm\WhitelabelUserRepository,
    WhitelabelPluginRepository,
    WhitelabelUserTicketRepository,
    WhitelabelPluginUserRepository,
    WhitelabelTransactionRepository
};
use Models\WhitelabelPlugin;
use Fuel\{Core\Arr,
    Core\Cache,
    Core\Config,
    Core\Controller,
    Core\DB,
    Core\Event,
    Core\Input,
    Core\Request,
    Core\Response};
use Carbon\Carbon;
use Core\App;
use Services\{Logs\FileLoggerService,
    LotteryProvider\TheLotterScanTicketService,
    Plugin\MauticPluginImportService,
    LtechService};

/**
 * The Task Controller.
 *
 * A basic controller example.  Has examples of how to set the
 * response body and status.
 *
 * @package  app
 * @extends  Controller
 */
class Controller_Task extends Controller
{
    use Services_Api_Mautic;

    private FileLoggerService $fileLoggerService;
    private const LOTTOPARK_ID = 1;

    public function before()
    {
        if (!Lotto_Helper::allow_access("empire")) {
            $error = Request::forge('index/404')->execute();
            echo $error;
            exit();
        }

        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    public function action_checkpages($whitelabel_domain)
    {
        try {
            $check_language_task = new \Task\Admin\CheckLanguage($whitelabel_domain);
            return $check_language_task->display();
        } catch (Throwable $e) {
            echo $e->getMessage();
        }
    }

    public function action_optimizeimages()
    {
        set_time_limit(0);

        $path = dirname(APPPATH, 3) . "/wordpress/wp-content/uploads/sites/";

        /* first step is to get all files that are not miniatures
         * it is a bit tricky because our whitelabels uploaded filenames like xxxx-750x800.jpg
         * so those files look like miniature but they are not!
         * that is why we have to make it two-pass
         */
        $miniatures = [];
        $main_images = [];

        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $dir2 = new DirectoryIterator($path . $fileinfo->getFilename());
                $dir2_name = $fileinfo->getFilename();
                foreach ($dir2 as $imagefile) {
                    if (!$imagefile->isDir() && !$imagefile->isDot()) {
                        $filename = $imagefile->getFilename();
                        $ext = $imagefile->getExtension();
                        $mtime = $imagefile->getMTime();
                        if ($mtime >= time() - 60 * 5 - 10) { // the cronjob runs every 5 minutes, so process only new files (since 5 minutes, 10 seconds error possibility)
                            if (preg_match('/(.*)-([0-9]{2,4})x([0-9]{2,4})\.([a-z]{3,4})$/u', $filename, $m)) {
                                if ($m[1] != "cropped-fav" && $m[1] != "fav") {
                                    // is miniature
                                    $miniatures[] = [$filename, $imagefile->getPath(), $imagefile->getExtension()];
                                }
                            } else {
                                // is not miniature
                                $main_images[] = [$filename, $imagefile->getPath(), $imagefile->getExtension()];
                            }
                        }
                    }
                }
            }
        }


        $not_a_miniature = [];
        $new_miniatures = [];
        foreach ($miniatures as $miniature) {
            $name = $miniature[0];
            if (preg_match('/(.*)-([0-9]{2,4})x([0-9]{2,4})-([0-9]{2,4})x([0-9]{2,4})\.([a-z]{3,4})$/u', $name, $m)) {
                // we found double size present in the filename, so the full version of this "miniature" is not a "miniature"
                $found = false;
                foreach ($not_a_miniature as $item) {
                    if ($item[0] == $m[1] . '-' . $m[2] . 'x' . $m[3] . '.' . $m[6]) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $not_a_miniature[] = [$m[1] . '-' . $m[2] . 'x' . $m[3] . '.' . $m[6], $miniature[1], $m[6]];
                }
            }
        }

        foreach ($miniatures as $miniature) {
            $found = false;
            foreach ($not_a_miniature as $item) {
                if ($miniature[0] == $item[0]) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $new_miniatures[] = $miniature;
            }
        }

        $main_images = array_merge($main_images, $not_a_miniature);

        foreach ($main_images as $image) {
            $file = $image[1] . '/' . $image[0];
            $ext = $image[2];
            if ($ext == "png") {
                //echo 'optipng -strip all -o5 '.$file;
                //echo '<br>';
                exec('optipng -strip all -o5 ' . $file);
            } elseif ($ext == "jpg" || $ext == "jpeg") {
                //echo 'jpegoptim -m90 --strip-all --all-progressive '.$file;
                //echo '<br>';
                exec('jpegoptim -m90 --strip-all --all-progressive ' . $file);
            }
        }
        foreach ($new_miniatures as $miniature) {
            $file = $miniature[1] . '/' . $miniature[0];
            $ext = $miniature[2];
            if ($ext == "png") {
                //echo 'optipng -strip all -o5 '.$file;
                //echo '<br>';
                exec('optipng -strip all -o5 ' . $file);
            } elseif ($ext == "jpg" || $ext == "jpeg") {
                //echo 'jpegoptim --strip-all --all-normal '.$file;
                //echo '<br>';
                exec('jpegoptim --strip-all --all-progressive ' . $file);
            }
        }
    }

    public function action_calcaffpayouts()
    {
        Config::load("platform", true);
        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }
        set_time_limit(0);

        $dt_start = new DateTime("now", new DateTimeZone("UTC"));
        $date = clone $dt_start;

        $month = $dt_start->format('n');
        $year = $dt_start->format('Y');
        $month--;
        if ($month == 0) {
            $month = 12;
            $year--;
        }

        $dt_start->setTime(0, 0, 0);
        $dt_start->setDate($year, $month, '1');

        $dt_end = clone $dt_start;
        $dt_end->setTime(23, 59, 59);
        $dt_end->setDate($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));

        $payout = Model_Whitelabel_Aff_Commission::fetch_commissions_for_payout(
            $dt_start->format("Y-m-d H:i:s"),
            $dt_end->format("Y-m-d H:i:s")
        );

        foreach ($payout as $aff_payout) {
            $set = [
                "whitelabel_id" => $aff_payout['whitelabel_id'],
                "whitelabel_aff_id" => $aff_payout['whitelabel_aff_id'],
                "currency_id" => $aff_payout['wa_currency_id'],
                "date" => $date->format('Y-m-d'),
                "amount" => $aff_payout['sum_commission'],
                "amount_usd" => $aff_payout['sum_commission_usd'],
                "amount_manager" => $aff_payout['sum_commission_manager'],
                "commissions" => $aff_payout['count'],
                "is_paidout" => 0
            ];

            $db = Model_Whitelabel_Aff_Payout::forge();
            $db->set($set);
            $db->save();
        }
    }

    /**
     * This fix was used to download missing lottorisq confirmations. There was some time, that confirmation script was
     * disabled due to minor bug which was fixed in the meantime I was abroad. The confirmation script was returning
     * error 500
     */
    public function action_lottorisqfix2()
    {
        exit("Script disabled.");
        set_time_limit(0);
        $res = DB::query("SELECT lt.whitelabel_user_ticket_slip_id, lt.lottorisqid, lt.confirm_data, wut.* FROM lottorisq_ticket lt LEFT JOIN whitelabel_user_ticket_slip wuts ON wuts.id = lt.whitelabel_user_ticket_slip_id LEFT JOIN whitelabel_user_ticket wut ON wut.id = wuts.whitelabel_user_ticket_id WHERE confirm_data IS NULL;")->execute()->as_array();

        Config::load("lottorisq", true);

        foreach ($res as $item) {
            $endpoint = Config::get("lottorisq.lottorisq.endpoint");
            $key = Config::get("lottorisq.lottorisq.key");
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $endpoint . 'tickets/' . $item['lottorisqid']);

            curl_setopt($ch, CURLOPT_USERPWD, $key);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type:application/json",
                "Cache-Control:no-cache"
            ]);

            $ssl_verifypeer = 2;
            $ssl_verifyhost = 2;
            if (Helpers_General::is_development_env()) {
                $ssl_verifypeer = 0;
                $ssl_verifyhost = 0;
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($response === false) {
                echo "\nERR\n";
            }
            $data = json_decode($response);
            if (isset($data->error) || !isset($data->lines)) {
                var_dump($data->error);
                continue;
            }
            var_dump($item);
            var_dump($data);

//            $lottorisq = Model_Lottorisq_Ticket::find_by_lottorisqid($item['lottorisqid']);
//            $lottorisq = $lottorisq[0];
//            $lottorisq->set(array(
//                'confirm_data' => serialize($data)
//            ));
//            $lottorisq->save();
//            if (isset($data->meta->id)) {
//                $slip_id = $data->meta->id;
//                $lottorisq = Model_Lottorisq_Ticket::find_by_whitelabel_user_ticket_slip_id($slip_id);
//                $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($slip_id);
//
//
//
//                $ticket = Model_Whitelabel_User_Ticket::find_by_pk($slip->whitelabel_user_ticket_id);
//                if ($ticket->draw_date !== $data->lines[0]->draws[0]->date) {
//                    echo "\nbad date\n";
//                }
//            } else {
//                echo "\ndoes not have meta\n";
//            }
        }
    }

    /**
     * This fix was used to check how many slips were sent to the lottorisq to gently sum our unexpected costs
     * this will also clean the database from previous bugs, which have left unused slips in database for one ticket
     */
    public function action_lottorisqfix1()
    {
        exit("Script disabled.");
        set_time_limit(0);

        Config::load("lottorisq", true);
        $res = DB::query("SELECT wuts.id AS wutsid, wut.id AS wutid, wut.lottery_provider_id, lt.* FROM whitelabel_user_ticket_slip wuts LEFT JOIN whitelabel_user_ticket_line wutl ON wutl.whitelabel_user_ticket_slip_id = wuts.id LEFT JOIN whitelabel_user_ticket wut ON wut.id = wuts.whitelabel_user_ticket_id LEFT JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wuts.id WHERE wutl.id IS NULL ORDER BY wuts.id;")->execute()->as_array();

        echo '<table border="1" style="font-family: Verdana; font-size: 12px;">';
        echo '<tr>';
        echo '<th>LP</th>';
        echo '<th>SLIP ID</th>';
        echo '<th>TICKET ID</th>';
        echo '<th>LOTTORISQ ID</th>';
        echo '<th>DRAW DATE</th>';
        echo '<th>MATCHES</th>';
        echo '<th>PRIZE</th>';
        echo '<th>NUMBERS</th>';
        echo '<th>INSTANT WIN NUMBERS</th>';
        echo '<th>INFO</th>';
        //echo '<th>DEBUG</th>';

        echo '</tr>';
        $i = 0;

        $total_lines = 0;
        $total_prize = "0";
        foreach ($res as $item) {
            $endpoint = Config::get("lottorisq.lottorisq.endpoint");
            $key = Config::get("lottorisq.lottorisq.key");
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $endpoint . 'tickets/' . $item['lottorisqid']);

            curl_setopt($ch, CURLOPT_USERPWD, $key);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type:application/json",
                "Cache-Control:no-cache"
            ]);
            $ssl_verifypeer = 2;
            $ssl_verifyhost = 2;
            if (Helpers_General::is_development_env()) {
                $ssl_verifypeer = 0;
                $ssl_verifyhost = 0;
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($response === false) {
                echo "\nERR\n";
            }
            DB::query("DELETE FROM whitelabel_user_ticket_slip WHERE id = " . $item['wutsid'])->execute();
            $data = json_decode($response);
            if (isset($data->error) || !isset($data->lines)) {
                // remove the ticket slip, clean the database
                echo '<tr>';
                echo '<td>-</td>';
                echo '<td>' . $item['wutsid'] . '</td>';
                echo '<td>' . $item['wutid'] . '</td>';
                echo '<td>' . $item['lottorisqid'] . '</td>';
                echo '<td colspan="6">' . $data->error->message . ' Overbought on demo. Removed from database, not counted.</td>';
                echo '</tr>';
                continue;
            }
            $i++;
            $total_lines++;
            echo '<tr>';
            echo '<td>' . $i . '</td>';
            echo '<td rowspan="' . count($data->lines) . '">' . $item['wutsid'] . '</td>';
            echo '<td rowspan="' . count($data->lines) . '">' . $item['wutid'] . '</td>';
            echo '<td rowspan="' . count($data->lines) . '">' . $item['lottorisqid'] . '</td>';
            echo '<td>' . $data->lines[0]->draws[0]->date . '</td>';
            echo '<td>' . (!empty($data->lines[0]->draws[0]->matches) ? implode(', ', $data->lines[0]->draws[0]->matches) : '') . '</td>';
            echo '<td>' . $data->lines[0]->draws[0]->prize . ' EUR</td>';
            echo '<td>' . implode(', ', $data->lines[0]->numbers->main) . '</td>';
            echo '<td rowspan="' . count($data->lines) . '">' . implode(', ', $data->numbers->instant) . '</td>';
            echo '<td rowspan="' . count($data->lines) . '">Removed from database.</td>';

            //echo '<td rowspan="'.count($data->lines).'">'; var_dump($data); echo '</td>';

            echo '</tr>';
            $total_prize = bcadd($total_prize, $data->lines[0]->draws[0]->prize, 2);
            if (count($data->lines) == 2) {
                $i++;
                $total_lines++;
                echo '<tr>';
                echo '<td>' . $i . '</td>';
                echo '<td>' . $data->lines[1]->draws[0]->date . '</td>';
                echo '<td>' . (!empty($data->lines[1]->draws[0]->matches) ? implode(', ', $data->lines[1]->draws[0]->matches) : '') . '</td>';
                echo '<td>' . $data->lines[1]->draws[0]->prize . ' EUR</td>';
                echo '<td>' . implode(', ', $data->lines[1]->numbers->main) . '</td>';
                echo '</tr>';
                $total_prize = bcadd($total_prize, $data->lines[1]->draws[0]->prize, 2);
            }
        }
        echo '</table>';
        echo '<h1>TOTAL OVERBOUGHT</h1>';
        echo '<p>' . $total_lines . ' lines &times; 1.15 EUR = ' . bcmul($total_lines, "1.15", 2) . ' EUR</p>';
        echo '<h1>TOTAL PRIZES</h1>';
        echo '<p>' . $total_prize . ' EUR</p>';
        echo '<h1>TOTAL LOSS</h1>';
        echo '<p>' . bcsub(bcmul($total_lines, "1.15", 2), $total_prize) . '</p>';
    }

    /**
     * Fired up automatically by lottorisq, used to process lottorisq ticket (our slip) state
     *
     * @throws Exception
     */
    public function action_lottorisq_confirm()
    {
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        $slip_id = $this->request->param("id");
        $key = $this->request->param("key");
        $slip = null;
        try {
            Config::load("lottorisq", true);

            $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($slip_id);

            if ($slip === null) {
                throw new Exception("[lottorisqconfirm] Couldn't find slip with id: " . $slip_id);
            }

            $ticket = Model_Whitelabel_User_Ticket::find_by_pk($slip->whitelabel_user_ticket_id);
            $whitelabel = Model_Whitelabel::find_by_pk($ticket->whitelabel_id);
            $ltech_helper = new Helpers_Ltech($slip->whitelabel_ltech_id);
            $ltech_details = $ltech_helper->get_ltech_details();

            if ($key != $ltech_details['secret']) {
                echo 'NOT ALLOWED';
                http_response_code(400);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'));

            if ($data === false || !isset($data->meta->id)) {
                throw new Exception("[lottorisqconfirm] Bad JSON data.");
            }
            $prefix = Config::get("lottorisq.lottorisq.prefix");
            $id = str_replace($prefix, '', $data->meta->id);
            if ($id !== $slip_id) {
                throw new Exception("[lottorisqconfirm] Slips IDs do not match.");
            }
            $lottorisq = Model_Lottorisq_Ticket::find_by_whitelabel_user_ticket_slip_id($slip->id);
            if ($lottorisq === null || count($lottorisq) == 0) {
                throw new Exception("[lottorisqconfirm] Can't find Lottorisq Ticket entry.");
            }

            $lottorisq = $lottorisq[0];
            $lottorisq->set([
                'confirm_data' => serialize($data)
            ]);
            $lottorisq->save();

            if ($data->type == 'bonoloto') {
                $slip->additional_data = serialize(['refund' => $data->numbers->refund]);
                $slip->save();
            }

            $ticket = Model_Whitelabel_User_Ticket::find_by_pk($slip->whitelabel_user_ticket_id);
            $lottery = Model_Lottery::find_by_pk($ticket->lottery_id);
            $old_date = Carbon::parse($ticket->draw_date, $lottery->timezone);
            $draw_date = Carbon::parse($data->lines[0]->draws[0]->date, $lottery->timezone);
            $draw_date->setTimeFromTimeString($old_date->toTimeString());
            $old_date_formatted = $old_date->format(Helpers_Time::DATETIME_FORMAT);
            $draw_date_formatted = $draw_date->format(Helpers_Time::DATETIME_FORMAT);
            if ($draw_date->notEqualTo($old_date) && $ticket->date_processed == null) {
                $ticket->set([
                    "draw_date" => $draw_date_formatted,
                    'valid_to_draw' => $draw_date_formatted
                ]);
                $ticket->save();

                $lottorisqLogRepository->addWarningLog(
                    $ticket->whitelabel_id,
                    $ticket->id,
                    $slip->id,
                    "Our platform and lottorisq lottery draw date mismatch! Changed ticket draw date from " . $old_date_formatted . " to " . $draw_date_formatted . ". This shouldn't be triggered normally!",
                    null,
                    $slip->whitelabel_ltech_id
                );
            }

            // Add ticket scan if exist
            if (!empty($data->scan)) {
                $slip_update = $slip;
                $slip_update->set([
                    'ticket_scan_url' => $data->scan
                ]);
                $slip_update->save();
            }

            // Multidraw check
            if (!empty($ticket->multi_draw_id)) {
                $multidraw_helper = new Helpers_Multidraw([]);
                $multidraw_helper->check_multidraw_ltech_confirmation(
                    $ticket->multi_draw_id,
                    $draw_date_formatted
                );
            }

            $lottorisqLogRepository->addSuccessLog(
                $ticket->whitelabel_id,
                $ticket->id,
                $slip->id,
                "Received confirmation of ticket from Lottorisq.",
                $data,
                $slip->whitelabel_ltech_id
            );
        } catch (Exception $e) {
            http_response_code(400);
            $err_slip = null;
            $err_ticket = null;
            $err_whitelabel = null;
            if ($slip !== null) {
                $err_slip = $slip->id;
                $ticket = Model_Whitelabel_User_Ticket::find_by_pk($slip->whitelabel_user_ticket_id);
                $err_ticket = $ticket->id;
                $err_whitelabel = $ticket->whitelabel_id;
            }
            $lottorisqLogRepository->addErrorLog(
                $err_whitelabel,
                $err_ticket,
                $err_slip,
                $e->getMessage(),
                null,
                $slip->whitelabel_ltech_id ?? null
            );
        }
    }

    public function action_provider_scan_confirm()
    {
        if (Input::method() !== 'POST') {
            die('Http method not allowed.');
        }

        $theLotterScanConfirmService = Container::get(TheLotterScanTicketService::class);
        $responseData = Input::json();
        try {
            $theLotterScanConfirmService->confirmScan($responseData);
            return Response::forge('OK', 200);
        } catch (Exception $exception) {
            $this->fileLoggerService->error(
                "[TheLotter Scan confirm] Error message: {$exception->getMessage()}, Response: " . json_encode($responseData, true)
            );
        }
    }

    public function action_checkneteller()
    {
        // this is the new way neteller transactions are confirmed
        // these are not instant, but checked every X minutes
        // it was needed, because in Neteller you can have only one Webhook address
        Config::load("platform", true);

        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }

        set_time_limit(0);

        $neteller = new Forms_Wordpress_Payment_Neteller();
        $neteller->confirm_all();
    }

    /**
     * Let's check the statuses of imvalad jobs
     * should be run every ~30 minutes
     *
     * @throws Exception
     */
    public function action_imvalapjobcheck()
    {
        exit('Disabled');
        Config::load("platform", true);
        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }
        set_time_limit(0);
        libxml_use_internal_errors(true);


        // this is how to release a job, we don't need this now anyway

        /* $job = Model_Imvalap_Job::find_by_pk(6);
          var_dump(Lotto_Helper::release_imvalap_job($job));
          exit(); */

        // find all jobs with statuses:
        // 107 - opened, 108 - closed, 101 - reserved, 102 - completed
        $jobs = Model_Imvalap_Job::find([
            "where" => [
                ['status', 'in', ["101", "102", "107", "108"]]
            ]
        ]);
        // no need to connect to imvalap if we don't have pending jobs
        if ($jobs !== null && count($jobs)) {
            Config::load("imvalap.ini", true);
            Config::load("platform", true);
            $url = Config::get("imvalap.imvalap.url");

            foreach ($jobs as $job) {
                $token = Config::get("imvalap.tokens.game" . $job->game_id);
                $data = [
                    "token" => $token,
                    "jobid" => $job->jobid
                ];
                $errortype = null;
                try {
                    $curl = curl_init($url . 'job_info');
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Accept: text/xml', 'Cache-control: no-cache']);
                    $response = curl_exec($curl);
                    curl_close($curl);
                    if ($response !== false) {
                        $xmlres = new SimpleXMLElement(trim($response));
                        if (!isset($xmlres->error)) {
                            $state = (string)$xmlres->state[0];

                            $bets = str_replace(['(', ')'], '', (string)$xmlres->bets[0]);
                            $bets = explode(" ", $bets);

                            $uploaded = 0;
                            // 103 - uploaded, 106 - processed_cancel (by imvalap), 109 - released (by us), 110 - not used for now - only for El Gordo (confirmed)
//                            if (in_array($state, array("107", "108", "101", "103", "106", "109", "110"))) {
//                                // do nothing, just update info
//                            }
                            // completed, now we can call upload_job to receive the scans
                            // the job->status == 102 shouldn't normally appear, but let's leave it in case of some bug inside to allow the script to run again
                            if ($state == "102" || $job->status == "102") {
                                try {
                                    $curl_upload = curl_init($url . 'upload_job');
                                    curl_setopt($curl_upload, CURLOPT_POST, true);
                                    curl_setopt($curl_upload, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($curl_upload, CURLOPT_POSTFIELDS, $data);
                                    curl_setopt($curl_upload, CURLOPT_HTTPHEADER, ['Accept: text/xml', 'Cache-control: no-cache']);
                                    $response = curl_exec($curl_upload);
                                    curl_close($curl_upload);
                                    if ($response !== false) {
                                        $xmlres = new SimpleXMLElement(trim($response));
                                        if (!isset($xmlres->error)) {
                                            $zip = new ZipArchive();
                                            $imgdir = Config::get("platform.images.dir");
                                            if ($zip->open($imgdir . '/imvalap_tmp/' . $job->jobid . '.zip') === true) {
                                                if (file_exists($imgdir . '/imvalap_tmp/' . $job->jobid . '/') || mkdir($imgdir . '/imvalap_tmp/' . $job->jobid, 0755)) {
                                                    $zip->extractTo($imgdir . '/imvalap_tmp/' . $job->jobid);
                                                    $zip->close();
                                                } else {
                                                    throw new Exception("Couldn't create new directory with upload job [" . $job->id . "] from Imvalap.");
                                                }
                                            } else {
                                                throw new Exception("Couldn't unzip upload job [" . $job->id . "] from Imvalap.");
                                            }
                                            $subscriptions = $xmlres->subscription;
                                            foreach ($subscriptions as $subscription) {
                                                $slip_id = $subscription->attributes()->idslip;
                                                $image_name = $subscription->attributes()->image;
                                                $ext = explode('.', $image_name);
                                                $ext = array_pop($ext);
                                                rename($imgdir . '/imvalap_tmp/' . $job->jobid . '/' . $image_name, $imgdir . '/' . $slip_id . '.' . $ext);
                                            }
                                            // let's clear after job
                                            unlink($imgdir . '/imvalap_tmp/' . $job->jobid . '.zip');
                                            rmdir($imgdir . '/imvalap_tmp/' . $job->jobid);
                                            Model_Imvalap_Log::add_log(Helpers_General::TYPE_SUCCESS, null, null, $job->id, "Job has been uploaded and unzipped!");
                                        } else {
                                            $add = "";
                                            if (!empty($xmlres->error)) {
                                                $add = ' [' . $xmlres->error . ']';
                                            }
                                            $errortype = 0;
                                            throw new Exception("Couldn't get upload job [" . $job->id . "] from Imvalap" . $add . ".");
                                        }
                                    } else {
                                        // curl error
                                        $error = curl_error($curl_upload);
                                        $errortype = 0;
                                        throw new Exception("CURL Error [" . $error . "].");
                                    }
                                } catch (Exception $e) {
                                    $state = "102";
                                    Model_Imvalap_Log::add_log(Helpers_General::TYPE_ERROR, null, null, $job->id, "Failed to get uploaded job from Imvalap. Additional message: " . $e->getMessage());
                                }
                            }

                            $job->set([
                                "status" => $state,
                                "bets" => $bets[0],
                                "bets_reserved" => $bets[1]
                            ]);
                            $job->save();
                        } else {
                            $add = "";
                            if (!empty($xmlres->error)) {
                                $add = ' [' . $xmlres->error . ']';
                            }
                            $errortype = 0;
                            throw new Exception("Couldn't get job status [" . $job->id . "] from Imvalap" . $add . ".");
                        }
                    } else {
                        // curl error
                        $error = curl_error($curl);
                        $errortype = 0;
                        throw new Exception("CURL Error [" . $error . "].");
                    }
                } catch (Exception $e) {
                    Model_Imvalap_Log::add_log(Helpers_General::TYPE_ERROR, null, null, $job->id, "Failed to get job status from Imvalap. Additional message: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * [2016/11/30]
     * We moved the lottorisq/imvalap ticket purchase into seperate task
     * should process all the pending tickets every 30 seconds
     * also shouldn't be allowed to run multiple times at once
     *
     * @return null
     */
    public function action_purchasetickets()
    {
        Config::load("platform", true);

        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }

        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        set_time_limit(0);

        $settings = Model_Setting::get_settings("task");

        // run only once at the time
        if (intval($settings['task_lockpurchase']) == "1") {
            Config::load("lotteries", true);
            $recipients = Config::get("lotteries.emergency_emails");
            \Package::load('email');
            $email = Email::forge();
            $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
            $email->to($recipients);
            $title = "Lotto Emergency: The purchase ticket job has been locked.";
            $email->subject($title);

            $body_text = "It seems that the purchase ticket job runs over 30 seconds, " .
                "so the script couldn't start next purchase round. " .
                "If the e-mail is received every 30 seconds - it means serious " .
                "problem as tickets are not purchased! The solution: manually " .
                "clear up the flag by running out following query: " .
                "\"UPDATE setting SET value = 0 WHERE name = `task_lockpurchase`\" " .
                "and remove the `/home/site/www/platform/fuel/app/cache/model_setting` " .
                "folder [task.php/purchasetickets].";

            $email->body($body_text);

            try {
                $email->send();
            } catch (Exception $e) {
                $error_message = "There is a problem with delivering the mail. " .
                    "Description of error: " . $e->getMessage();
                $this->fileLoggerService->error(
                    $error_message
                );
            }

            return;
        }

        $updatedRows = DB::update('setting')
            ->set([
                'value' => 1
            ])
            ->where('name', 'task_lockpurchase')
            ->and_where('value', 0)
            ->execute();
        $lockFailed = $updatedRows < 1;
        if ($lockFailed) {
            $this->fileLoggerService->error(
                'Ltech purchase is locked. Other task is running. If it will happen again, check why it is locked.'
            );
            return;
        }

        $currencyCondition = $negativeCurrencyCondition = "";
        $app = Container::get(App::class);

        /** To optimize this task we are processing only these tickets
         * where ltech balance in ticket's currency is enough
         * It works only on Production because on staging we can buy tickets even if balance is negative
         */
        $thereIsNoBalanceOnAnyAccount = false;
        if ($app->isProduction()) {
            $ltechService = Container::get(LtechService::class);

            try {
                $allowedCurrencies = $ltechService->getCurrenciesWithEnoughBalance();
                if (empty($allowedCurrencies)) {
                    // There were no problems to fetch balances
                    // It means that we can set all tickets to is_insufficient_ltech_balance = true
                    $thereIsNoBalanceOnAnyAccount = true;
                }
            } catch (Throwable) {
                // There were problem to fetch balances from Ltech
                // We set allowedCurrencies to empty array to process all currencies
                $allowedCurrencies = [];
            }

            if (!empty($allowedCurrencies)) {
                foreach ($allowedCurrencies as $key => $currency) {
                    $allowedCurrencies[$key] = "'$currency'";
                }
                $allowedCurrenciesInString = implode(', ', $allowedCurrencies);
                // We get tickets only in specific currency where balance on ltech is enough
                $currencyCondition = "AND c.code IN ($allowedCurrenciesInString)";
                $negativeCurrencyCondition = "AND c.code NOT IN ($allowedCurrenciesInString)";
            }
        }

        /**
         * This query get SLIPS
         * In first order we get slips for megajackpot.ph, megajackpot, redfox, lottohoy, lottomat, doublejack.online, lottopark
         * Next we order slips by the nearest draw date
         * We limit to get max 500 slips per task (task is run every minute)
         * wut.model = 3 means LCS tickets and we don't want to process it here
         */
        $query_string = "SELECT 
            DISTINCT wutl.whitelabel_user_ticket_slip_id, 
            wut.*,
            lot.next_date_utc,
            lot.timezone as lottery_timezone,
            lp.timezone as provider_timezone,
            lp.offset as provider_offset,
            lp.closing_times as provider_closing_times,
            lp.closing_time as provider_closing_time
        FROM whitelabel_user_ticket_line wutl
        INNER JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
        INNER JOIN lottery_provider lp ON lp.id = wut.lottery_provider_id
        INNER JOIN lottery lot ON lot.id = wut.lottery_id
        INNER JOIN currency c ON lot.currency_id = c.id
        INNER JOIN whitelabel_lottery wl ON wut.whitelabel_id = wl.whitelabel_id AND wut.lottery_id = wl.lottery_id
        LEFT JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wutl.whitelabel_user_ticket_slip_id
        WHERE wutl.whitelabel_user_ticket_slip_id IS NOT NULL
            AND wut.paid = 1 
            AND wut.status = 0 
            AND wut.date_processed IS NULL
            AND provider = 1 
            AND wut.model != 3
            AND lt.id IS NULL 
            AND lot.next_date_local >= wut.draw_date
            AND wut.whitelabel_id != 20
            $currencyCondition
        ORDER BY CASE wut.whitelabel_id
        WHEN 40 THEN 1
        WHEN 35 THEN 2
        WHEN 8 THEN 3
        WHEN 3 THEN 4
        WHEN 2 THEN 5
        WHEN 26 THEN 6
        WHEN 1 THEN 7
        ELSE 8
        END ASC,
        lot.next_date_utc
        LIMIT 300";

        $idsToUpdateQuery = "SELECT 
            DISTINCT wut.id AS id
        FROM whitelabel_user_ticket_line wutl
        INNER JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
        INNER JOIN lottery_provider lp ON lp.id = wut.lottery_provider_id
        INNER JOIN lottery lot ON lot.id = wut.lottery_id
        INNER JOIN currency c ON lot.currency_id = c.id
        INNER JOIN whitelabel_lottery wl ON wut.whitelabel_id = wl.whitelabel_id AND wut.lottery_id = wl.lottery_id
        LEFT JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wutl.whitelabel_user_ticket_slip_id
        WHERE wutl.whitelabel_user_ticket_slip_id IS NOT NULL
            AND wut.paid = 1 
            AND wut.status = 0 
            AND wut.date_processed IS NULL
            AND provider = 1 
            AND wut.model != 3
            AND lt.id IS NULL 
            AND lot.next_date_local >= wut.draw_date
            AND wut.whitelabel_id != 20
            AND wut.is_ltech_insufficient_balance = 0
            $negativeCurrencyCondition";

        try {
            $shouldUpdateInsufficient = $negativeCurrencyCondition !== "" || $thereIsNoBalanceOnAnyAccount;
            if ($shouldUpdateInsufficient) {
                $idsToUpdate = DB::query($idsToUpdateQuery)->execute()->as_array();
                $idsToUpdate = array_column($idsToUpdate, 'id');
                $idsToUpdateInString = implode(', ', $idsToUpdate);
                if (!empty($idsToUpdateInString)) {
                    $updateQuery = "UPDATE whitelabel_user_ticket SET is_ltech_insufficient_balance = 1 WHERE id IN ($idsToUpdateInString)";
                    Db::query($updateQuery)->execute();
                }
            }
        } catch (Throwable $exception) {
            $this->fileLoggerService->error(
                "Cannot update tickets (set is_ltech_insufficient_balance): " . $exception->getMessage()
            );
        }

        try {
            // check if there's any lottorisq slip failed and retry if there's still time to do that - if not, inform us about
            // it will catch any connection errors too - if the slip is already created within lottorisq system it will not create another one
            // wut.model=3 => this is none. I do not know if we will have multiple providers, we had invalap and lottorisq before
            // now we have only lottorisq, so I added model None (3) to lottorisq
            $tickets = DB::query($query_string);
            $tickets = $tickets->execute();

            // We don't want to even process the query because there is no balance on any account on Ltech
            $isEnoughLtechBalance = !$thereIsNoBalanceOnAnyAccount;
            $isAnyTicketToProcess = $tickets !== null && count($tickets) > 0;
            if ($isEnoughLtechBalance && $isAnyTicketToProcess) {
                // is_ltech_insufficient_balance flag is set only on logging purpose
                // If ltech hasn't enough money to process purchase, we set this flag
                // And when we get the ticket after closing time, and it still not be purchased
                // We won't log this situation cause set insufficient flag
                $ticketsIdsToSetIsLtechInsufficientBalance = [];
                $currenciesIdsToSetIsLtechInsufficientBalance = [];
                foreach ($tickets as $ticket) { // ticket means lottorisq slip here
                    $closing_time_date = Lotto_Helper::calculate_closing_time($ticket['lottery_id'], $ticket['lottery_timezone'], Model_Lottery_Provider::forge([
                        'timezone' => $ticket['provider_timezone'],
                        'offset' => $ticket['provider_offset'],
                        'closing_times' => $ticket['provider_closing_times'],
                        'closing_time' => $ticket['provider_closing_time'],
                    ]), $ticket['draw_date']);

                    $now = Carbon::now();
                    $now->addMinutes(10);

                    $whitelabel = Model_Whitelabel::find_by_pk($ticket['whitelabel_id']);
                    $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
                    // in this case we only need id
                    $lottery = $lotteries['__by_id'][$ticket['lottery_id']];

                    $model_ticket = Model_Whitelabel_User_Ticket::find_by_pk($ticket['id']);
                    $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($ticket['whitelabel_user_ticket_slip_id']);

                    // do not purchase tickets when you there are 10 minutes left to l-tech closing time and beyond
                    if ($now < $closing_time_date) {
                        $slip_lines = Model_Whitelabel_User_Ticket_Line::find([
                            "where" => [
                                "whitelabel_user_ticket_slip_id" => $ticket['whitelabel_user_ticket_slip_id']
                            ],
                            "order_by" => ["id" => "asc"]
                        ]);

                        try {
                            DB::start_transaction();

                            Lotto_Helper::process_lottorisq_slip(
                                $lottery,
                                $whitelabel,
                                $model_ticket,
                                $slip,
                                $slip_lines,
                                $ticketsIdsToSetIsLtechInsufficientBalance,
                                $currenciesIdsToSetIsLtechInsufficientBalance
                            );

                            DB::commit_transaction();
                        } catch (\Throwable $e) {
                            DB::rollback_transaction();

                            $lottorisqLogRepository->addErrorLog(
                                $whitelabel['id'],
                                $model_ticket->id,
                                $slip->id,
                                $e->getMessage(),
                                null,
                                $slip->whitelabel_ltech_id
                            );
                        }
                    } elseif (empty($ticket['is_ltech_insufficient_balance'])) {
                        // If ticket wasn't bought because balance on ltech was too low
                        // We take the risk of this situation, and this log is unnecessary

                        $error_message = "Failed to send ticket-slip (TICKET ID: " .
                            $model_ticket->id .
                            ", SLIP ID: " .
                            $slip->id .
                            ") to the Lottorisq in allowed time.";

                        $lottorisqLogRepository->addLogIfDoesNotExistByWhitelabelUserTicketSlipId(
                            Helpers_General::TYPE_ERROR,
                            $model_ticket->whitelabel_id,
                            $model_ticket->id,
                            $slip->id,
                            $error_message,
                            null,
                            $slip->whitelabel_ltech_id
                        );
                    }
                }

                /** @var WhitelabelUserTicketRepository $whitelabelUserTicketRepository */
                $whitelabelUserTicketRepository = Container::get(WhitelabelUserTicketRepository::class);
                $whitelabelUserTicketRepository->changeIsLtechInsufficientBalance(
                    $ticketsIdsToSetIsLtechInsufficientBalance,
                    true
                );

                $currenciesIdsToSetIsLtechInsufficientBalance = array_unique($currenciesIdsToSetIsLtechInsufficientBalance);
                $whitelabelUserTicketRepository->setTicketsAsInsufficientByCurrencyId($currenciesIdsToSetIsLtechInsufficientBalance);

                // We want to refresh cache when balance is insufficient
                // In other case task could be tried to process tickets although balance is not enough
                if (!empty($ticketsIdsToSetIsLtechInsufficientBalance)) {
                    try {
                        Cache::delete(LtechService::CACHE_KEY);
                    } catch (Throwable $exception) {
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->fileLoggerService->error(
                $e->getMessage()
            );

            \Package::load('email');

            Config::load("lotteries", true);
            $recipients = Config::get("lotteries.emergency_emails");
            $email = Email::forge();
            $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
            $email->to($recipients);
            $title = "Lotto Emergency: Lottorisq Slip Failed";
            $email->subject($title);

            $body_text = "Unknown error! More info [task.php/purchasetickets]: " .
                $e->getMessage();
            $email->body($body_text);

            try {
                $email->send();
            } catch (Exception $e) {
                $error_message = "There is a problem with delivering the mail. " .
                    "Description of error: " . $e->getMessage();
                $this->fileLoggerService->error(
                    $error_message
                );
            }
        }

        Model_Setting::update_setting("task_lockpurchase", "0");
    }

    /**
     * Check for lottorisq account balance
     *
     * @throws Exception
     */
    public function action_lottorisqbalance()
    {
        Config::load("platform", true);
        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }
        set_time_limit(0);

        $form = new Forms_Admin_Whitelabels_Ltech();
        $form->check_lottorisqbalance();

        exit();
    }

    /**
     * Check for quick pick prizes
     * should be run every ~5 minutes
     *
     * @throws Exception
     */
    public function action_providerscheck()
    {
        Config::load("platform", true);
        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }
        set_time_limit(0);
        Config::load("lottorisq", true);
        /** @var LottorisqLogRepository $lottorisqLogRepository */
        $lottorisqLogRepository = Container::get(LottorisqLogRepository::class);

        // SPECIAL CASE FOR UK-LOTTERY LOTTORISQ LUCKY-DIP QUICK PICK (THEY PROCESS IT BY THEMSELFES SO ITS BETTER FOR US)
        // this is really complex stuff, do not touch this unless you have knowledge what you are doing
        $res = DB::query("SELECT DISTINCT wutl.id, wutl.whitelabel_user_ticket_slip_id, lt.lottorisqid, wl.lottery_provider_id, wl.income, wl.income_type, ltd.lottery_type_id, wut.whitelabel_id, wut.whitelabel_user_id, wut.currency_id, w.margin FROM whitelabel_user_ticket_line wutl
			JOIN whitelabel_user_ticket wut ON wut.id = wutl.whitelabel_user_ticket_id
			JOIN whitelabel_lottery wl ON wl.lottery_id = wut.lottery_id
			JOIN whitelabel w ON w.id = wl.whitelabel_id
			JOIN lottery_provider lp ON lp.id = wl.lottery_provider_id
			JOIN lottery_type_data ltd ON ltd.id = wutl.lottery_type_data_id
			JOIN lottorisq_ticket lt ON lt.whitelabel_user_ticket_slip_id = wutl.whitelabel_user_ticket_slip_id
			WHERE wl.whitelabel_id = wut.whitelabel_id AND wut.lottery_id = 5 AND lp.provider = 1 AND ltd.type = 2 AND wut.status = 1 AND wutl.status = 1 AND wutl.payout = 0");
        $res = $res->execute();
        if ($res !== null && count($res)) {
            foreach ($res as $slipdata) {
                $line = Model_Whitelabel_User_Ticket_Line::find_by_pk($slipdata['id']);
                $line_ticket = Model_Whitelabel_User_Ticket::find_by_pk($line->whitelabel_user_ticket_id);

                try {
                    DB::start_transaction();
                    $lottorisq_id = $slipdata['lottorisqid'];

                    $slip = Model_Whitelabel_User_Ticket_Slip::find_by_pk($slipdata['whitelabel_user_ticket_slip_id']);

                    $whitelabel = Model_Whitelabel::find_by_pk($line_ticket->whitelabel_id);
                    $wlotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
                    $ltech_helper = new Helpers_Ltech($slip->whitelabel_ltech_id);
                    $ltech_details = $ltech_helper->get_ltech_details();

                    $ltech_request = $ltech_helper->sendLtechRequest('tickets/' . $lottorisq_id, null, $ltech_details['ltech_id']);

                    $response = $ltech_request['response'];
                    $httpcode = $ltech_request['httpcode'];

                    if ($response === false) {
                        throw new Exception("CURL error while getting winning ticket from Lottorisq");
                    }

                    $data = json_decode($response);
                    if (isset($data->error) || !isset($data->lines)) {
                        throw new Exception("Trouble getting winning ticket from Lottorisq " . var_export(
                            [
                                    'response' => $response,
                                    'error_code' => $data->error->code ?? '',
                                    'error_message' => $data->error->message ?? '',
                                ],
                            true
                        ));
                    }
                    foreach ($data->lines as $data_line) {
                        if (isset($data_line->draws) && isset($data_line->draws[0]->ticket) && implode(',', $data_line->numbers->main) == $line->numbers) { // we found the exact ticket-slip
                            // we've got the lucky dip ticket id, we want to get the numbers
                            $luckydip_id = $data_line->draws[0]->ticket;

                            $ltech_request = $ltech_helper->sendLtechRequest('tickets/' . $luckydip_id, null, $ltech_details['ltech_id']);

                            $response = $ltech_request['response'];
                            $httpcode = $ltech_request['httpcode'];

                            if ($response === false) {
                                throw new Exception("CURL error while getting lucky dip ticket from Lottorisq");
                            }
                            $luckydata = json_decode($response);
                            if (isset($luckydata->error) || !isset($luckydata->lines)) {
                                $code = isset($luckydata->error->code) ? $luckydata->error->code : "";
                                $message = isset($luckydata->error->message) ? $luckydata->error->message : "";
                                throw new Exception("Trouble getting lucky dip ticket from Lottorisq [" . $code . ": " . $message . "]");
                            }

                            if (isset($luckydata->lines) && isset($luckydata->lines[0]->draws)) {
                                // if we are here, that means we purchased the ticket, not insured, or not purchased
                                $luckydrawdate = $luckydata->lines[0]->draws[0]->date;
                                $luckynumbers = $luckydata->lines[0]->numbers->main;

                                // little cheat, less queries:>
                                $lucky_lottery_id = 5; // ID = 5 is for UK Lottery

                                $lucky_ticket = Model_Whitelabel_User_Ticket::forge();
                                $lottery = ['id' => $lucky_lottery_id];
                                //$lottery = Model_Lottery::find_by_pk($lucky_lottery_id); // uk

                                // get type for the lucky draw date
                                $lottery_type = Model_Lottery_Type::get_lottery_type_for_date(
                                    $lottery,
                                    $luckydrawdate
                                );

                                $lucky_ticket->set([
                                    'token' => Lotto_Security::generate_ticket_token($slipdata['whitelabel_id']),
                                    'whitelabel_transaction_id' => null,
                                    'whitelabel_id' => $slipdata['whitelabel_id'],
                                    'whitelabel_user_id' => $slipdata['whitelabel_user_id'],
                                    'lottery_id' => $lucky_lottery_id,
                                    'lottery_type_id' => $lottery_type['id'], /* $slipdata['lottery_type_id'] */
                                    'currency_id' => $slipdata['currency_id'], // TODO: adjust currency
                                    'lottery_provider_id' => $slipdata['lottery_provider_id'],
                                    'valid_to_draw' => "$luckydrawdate 19:30",
                                    'draw_date' => "$luckydrawdate 19:30",
                                    'amount' => 0,
                                    'amount_usd' => 0,
                                    'amount_payment' => 0,
                                    'amount_manager' => 0,
                                    'date' => DB::expr("NOW()"),
                                    'status' => Helpers_General::TICKET_STATUS_PENDING,
                                    'paid' => Helpers_General::TICKET_PAID,
                                    'payout' => Helpers_General::TICKET_PAYOUT_PENDING,
                                    'model' => Helpers_General::LOTTERY_MODEL_PURCHASE,
                                    'amount_local' => 0,
                                    'is_insured' => 0,
                                    'tier' => 0,
                                    'cost_local' => 0,
                                    'cost_usd' => 0,
                                    'cost' => 0,
                                    'cost_manager' => 0,
                                    'income_local' => 0,
                                    'income_usd' => 0,
                                    'income' => 0,
                                    'income_value' => $slipdata['income'],
                                    'income_manager' => 0,
                                    'income_type' => $slipdata['income_type'],
                                    'margin_value' => $slipdata['margin'],
                                    'margin_local' => 0,
                                    'margin_usd' => 0,
                                    'margin' => 0,
                                    'margin_manager' => 0,
                                    'ip' => $line_ticket->ip,
                                    'line_count' => 1,
                                ]);
                                $lucky_ticket->save();

                                $whitelabel_lottery = Model_Whitelabel_Lottery::find_for_whitelabel_and_lottery(
                                    $lucky_ticket['whitelabel_id'],
                                    $lucky_ticket['lottery_id']
                                )[0];

                                $lucky_slip = Model_Whitelabel_User_Ticket_Slip::forge();
                                $lucky_slip->set([
                                    'whitelabel_user_ticket_id' => $lucky_ticket->id,
                                    'whitelabel_ltech_id' => $slip->whitelabel_ltech_id,
                                    'whitelabel_lottery_id' => $whitelabel_lottery['id']
                                ]);
                                $lucky_slip->save();

                                $lucky_line = Model_Whitelabel_User_Ticket_Line::forge();
                                $lucky_line->set([
                                    "whitelabel_user_ticket_id" => $lucky_ticket->id,
                                    "whitelabel_user_ticket_slip_id" => $lucky_slip->id,
                                    "numbers" => implode(',', $luckynumbers),
                                    "bnumbers" => "",
                                    "amount" => 0,
                                    "amount_usd" => 0,
                                    "amount_payment" => 0,
                                    "amount_manager" => 0,
                                    "amount_local" => 0,
                                    "status" => Helpers_General::TICKET_STATUS_PENDING,
                                    "payout" => Helpers_General::TICKET_PAYOUT_PENDING
                                ]);
                                $lucky_line->save();

                                $lottorisq_ticket = Model_Lottorisq_Ticket::forge();
                                $lottorisq_ticket->set([
                                    'whitelabel_user_ticket_slip_id' => $lucky_slip->id,
                                    'lottorisqid' => $luckydip_id
                                ]);
                                $lottorisq_ticket->save();

                                $line->set([
                                    "payout" => Helpers_General::TICKET_PAYOUT_PAIDOUT
                                ]);
                                $line->save();

                                $checklines = DB::query("SELECT COUNT(*) AS count FROM whitelabel_user_ticket_line WHERE
								whitelabel_user_ticket_id = :ticket AND payout = 0");

                                $checklines->param(":ticket", $line->whitelabel_user_ticket_id);
                                $checklines = $checklines->execute()->as_array();
                                if ($checklines[0]['count'] == 0) {
                                    $line_ticket->set([
                                        'payout' => Helpers_General::TICKET_PAYOUT_PAIDOUT
                                    ]);
                                    $line_ticket->save();
                                }
                            }
                        }
                    }
                    DB::commit_transaction();
                } catch (Exception $e) {
                    DB::rollback_transaction();
                    $lottorisqLogRepository->addErrorLog($line_ticket->whitelabel_id, $line_ticket->id, $slipdata['whitelabel_user_ticket_slip_id'], $e->getMessage(), $slip->whitelabel_ltech_id);
                }
            }
        }
    }

    public function action_updatecurrencies()
    {
        set_time_limit(0);

        Config::load("openexchangerates", true);

        $app_id = Config::get("openexchangerates.app.id");

        if (empty($app_id)) {
            exit("No app id defined!");
        }

        $url_text = "https://openexchangerates.org/api/latest.json?app_id=" . $app_id;

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url_text);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $ssl_verifypeer = 2;
            $ssl_verifyhost = 2;
            if (Helpers_General::is_development_env()) {
                $ssl_verifypeer = 0;
                $ssl_verifyhost = 0;
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost);

            $response = curl_exec($ch);

            curl_close($ch);

            $response = json_decode($response);

            foreach ($response->rates as $key => $value) {
                $dbcur = Model_Currency::find_by_code($key);
                if ($dbcur !== null && count($dbcur) > 0) {
                    $dbcur = $dbcur[0];
                    $dbcur->rate = round($value, Helpers_Currency::RATE_SCALE);
                    $dbcur->save();
                }
            }
        } catch (Exception $e) {
            \Package::load('email');
            Config::load("lotteries", true);
            $recipients = Config::get("lotteries.emergency_emails");
            $email = Email::forge();
            $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
            $email->to($recipients);
            $title = "Lotto Emergency: OpenExchange Rates Update Failed";
            $email->subject($title);
            $email->body("Rates download failed! More info [task.php/updatecurrencies]: " . $e->getMessage());
            try {
                $email->send();
            } catch (Exception $e) {
                $error_message = "There is a problem with delivering the mail. " .
                    "Description of error: " . $e->getMessage();
                $this->fileLoggerService->error(
                    $error_message
                );
            }
        }
        Cache::delete('model_currency.allcurrencies');
    }

    /**
     * Main lottery update script, it's the hearth of the system
     * should be run every ~20 minutes
     */
    public function action_lotteryupdate()
    {
        Config::load("platform", true);

        if (
            !in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))
            && !(\Fuel::$env == Fuel::DEVELOPMENT)
        ) {
            exit('Access denied.');
        }
        set_time_limit(0);
        libxml_use_internal_errors(true);

        $lotteries = Model_Lottery::get_all_lotteries();
        $lotteries = $lotteries['__by_slug'];
        // make sure cache is cleared.
        Cache::delete('model_lottery.lotteriesorderbyid');
        // update currency USD value

        $failure = false;

        $feed_classes = [
            Lotto_Lotteries_Powerball::class,
            Lotto_Lotteries_MegaMillions::class,
            Lotto_Lotteries_Eurojackpot::class,
            Lotto_Lotteries_SuperEnalotto::class,
            Lotto_Lotteries_UKLottery::class,
            Lotto_Lotteries_Euromilions::class,
            Lotto_Lotteries_LottoPL::class,
            Lotto_Lotteries_LaPrimitiva::class,
            Lotto_Lotteries_Bonoloto::class,
            Lotto_Lotteries_ElGordo::class,
            Lotto_Lotteries_LottoFR::class,
            Lotto_Lotteries_MegaSena::class,
            Lotto_Lotteries_LottoFL::class,
            Lotto_Lotteries_Quina::class,
            Lotto_Lotteries_SetForLifeUK::class,
            Lotto_Lotteries_Thunderball::class,
            Lotto_Lotteries_LottoAmerica::class,
            Lotto_Lotteries_Lotto6Aus49::class,
            Lotto_Lotteries_LottoAT::class,
            Lotto_Lotteries_OzLotto::class,
            Lotto_Lotteries_PowerballAU::class,
            Lotto_Lotteries_SaturdayLottoAU::class,
            Lotto_Lotteries_MondayWednesdayLottoAU::class,
            Lotto_Lotteries_OtosLotto::class,
            Lotto_Lotteries_HatosLotto::class,
            Lotto_Lotteries_SkandinavLotto::class,
            Lotto_Lotteries_LottoMultiMulti::class,
        ];

        /** @var Lotto_Lotteries_Feed $feed_class */
        foreach ($feed_classes as $feed_class) {
            $lottery = new $feed_class();
            $slug = $lottery->get_lottery_slug();
            try {
                if (isset($lotteries[$slug]) === false) {
                    throw new Exception("Lottery with slug {$slug} does not exist.");
                }
                if ($lotteries[$slug]['is_enabled'] === false || $lotteries[$slug]['is_temporarily_disabled'] === true) {
                    continue;
                }
                $lottery->set_lottery($lotteries[$slug]);
                $lottery->get_results();
            } catch (Exception $e) {
                $failure = $this->lottery_error($e, $lotteries[$slug]);
            }
        }

        echo '1';
        if ($failure) {
            $settings = Model_Setting::get_settings('admin');
            $now = new DateTime("now", new DateTimeZone("UTC"));
            $admin_firsterror = clone $now;
            if (!empty($settings['admin_firsterror'])) {
                $admin_firsterror = DateTime::createFromFormat('Y-m-d H:i:s', $settings['admin_firsterror'], new DateTimeZone("UTC"));
            } else {
                Model_Setting::update_setting('admin_firsterror', $now->format("Y-m-d H:i:s"));
            }
            $trigger = clone $now;
            $trigger->sub(new DateInterval('PT5H'));
            if ($trigger > $admin_firsterror) {
                Model_Setting::update_setting('admin_firsterror', '');
                \Package::load('email');
                Config::load("lotteries", true);
                $recipients = Config::get("lotteries.emergency_emails");
                $email = Email::forge();
                $email->from('noreply@' . Lotto_Helper::getWhitelabelDomainFromUrl(), 'Lotto Emergency');
                $email->to($recipients);
                $title = "Lotto Emergency: Lottery Update Critical Error!";
                $email->subject($title);
                $email->body("Some of the lotteries haven't been updated for longer than 5 hours! Please check the logs and decide what to do (change lottery source, disable the lottery, contact admin)!");
                try {
                    $email->send();
                } catch (Exception $e) {
                    $error_message = "There is a problem with delivering the mail. " .
                        "Description of error: " . $e->getMessage();
                    $this->fileLoggerService->error(
                        $error_message
                    );
                }
            }
        } else {
            Model_Setting::update_setting('admin_firsterror', '');
        }
    }

    public function action_check_superenalotto_delay()
    {
        Config::load("platform", true);

        if (!in_array(Lotto_Security::get_IP(), Config::get("platform.ip.whitelist"))) {
            exit('Access denied.');
        }

        set_time_limit(0);
        libxml_use_internal_errors(true);

        /** @var Lotto_Lotteries_SuperEnalotto $superEnalottoParser */
        $superEnalottoParser = Lotto_Lotteries_SuperEnalotto::make();
        $superEnalottoParser->check_delay();
    }

    public function action_updateestimated()
    {
        set_time_limit(0);

        $lotteries = Model_Lottery::get_all_enabled_lotteries();
        $lotteries = $lotteries['__by_slug'];

        $lotteriesToUpdate = [
            'eurojackpot',
            'lotto-pl',
            'oz-lotto',
            'powerball-au',
            'saturday-lotto-au',
            'monday-wednesday-lotto-au',
            'el-gordo-primitiva'
        ];

        foreach ($lotteriesToUpdate as $lotteryToUpdate) {
            if (isset($lotteries[$lotteryToUpdate]) && $lotteries[$lotteryToUpdate]['estimated_updated'] == 0) {
                Lotto_Helper::update_estimated($lotteries[$lotteryToUpdate]);
            }
        }
    }

    /**
     * This is intended to run by cron, once each month (like aff payouts)
     * calculates the monthly volume
     */
    public function action_monthlyvolume()
    {
        set_time_limit(0);

        $dt_start = new DateTime("now", new DateTimeZone("UTC"));
        $date = clone $dt_start;

        $month = $dt_start->format('n');
        $year = $dt_start->format('Y');
        $month--;
        if ($month == 0) {
            $year--;
            $month = 12;
        }

        $dt_start->setTime(0, 0, 0);
        $dt_start->setDate($year, $month, '1');

        $dt_end = clone $dt_start;
        $dt_end->setTime(23, 59, 59);
        $dt_end->setDate($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));

        $whitelabels = Model_Whitelabel::find_all();

        $res = DB::query("SELECT whitelabel_id, lottery_id, COUNT(*) AS count FROM whitelabel_user_ticket GROUP BY whitelabel_id, lottery_id");

        $res = $res->execute()->as_array();

        $res_arr = [];
        foreach ($res as $item) {
            $res_arr[$item['whitelabel_id']][$item['lottery_id']] = $item['count'];
        }
        foreach ($whitelabels as $whitelabel) {
            $lotteries = Model_Lottery::get_really_all_lotteries_for_whitelabel($whitelabel);
            foreach ($lotteries['__by_id'] as $lottery) {
                $count = 0;
                if (isset($res_arr[$whitelabel['id']][$lottery['id']])) {
                    $count = $res_arr[$whitelabel['id']][$lottery['id']];
                }
                if ($count < 1000) {
                    $count = 1000;
                }
                $dlottery = Model_Whitelabel_Lottery::find_by_pk($lottery['wid']);
                $dlottery->set(["volume" => $count]);
                $dlottery->save();
            }
        }
    }

    /**
     *
     * @param Exception $e
     * @param array     $lottery
     *
     * @return bool
     */
    public function lottery_error($e, $lottery)
    {
        $add = '';
        if (!empty($e->getMessage())) {
            $add = ' [' . $e->getMessage() . ']';
        }
        $this->fileLoggerService->error(
            "Data download error $add ."
        );

        echo '0';

        return true;
    }

    /**
     * This function is only for update different
     * tables within DB by currencies - some of them
     * should be run only once on live, and some of them
     * could be run more that once (but it should not be)
     */
    public function action_update_manager_currencies()
    {
        set_time_limit(0);

        $show_notice = true;

        if (empty(Input::get()) || empty(Input::get("action"))) {
            echo "There is no action given on Input";

            return;
        }

        if (!is_null(Input::get("notice"))) {
            $show_notice = ((int)Input::get("notice") === 1 ? true : false);
        }

        switch (Input::get("action")) {
            case 'all_in_one':
                $result = Controller_Temp::default_currency_update($show_notice);
                echo "Result of default_currency_update: " . $result . "<br>";

                $result = Controller_Temp::update_transactions_amount_payment_currencies($show_notice, true, true);
                echo "Result of update_transactions_amount_payment_currencies: " . $result . "<br>";

                $result = Controller_Temp::update_transactions_values_manager($show_notice);
                echo "Result of update_transactions_values_manager: " . $result . "<br>";

                $result = Controller_Temp::update_withdrawals_request_amount_manager($show_notice);
                echo "Result of update_withdrawals_request_amount_manager: " . $result . "<br>";

                $result = Controller_Temp::update_tickets_prizes_manager($show_notice);
                echo "Result of update_tickets_prizes_manager: " . $result . "<br>";

                $result = Controller_Temp::update_tickets_amount_payment($show_notice);
                echo "Result of update_tickets_amount_payment: " . $result . "<br>";

                $result = Controller_Temp::update_ticket_lines_prizes_manager($show_notice);
                echo "Result of update_ticket_lines_prizes_manager: " . $result . "<br>";

                $result = Controller_Temp::update_ticket_lines_amount_payment($show_notice);
                echo "Result of update_ticket_lines_amount_payment: " . $result . "<br>";

                $result = Controller_Temp::whitelabel_aff_payout($show_notice);
                echo "Result of whitelabel_aff_payout: " . $result . "<br>";

                $result = Controller_Temp::update_aff_commission_manager($show_notice);
                echo "Result of update_aff_commission_manager: " . $result . "<br>";

                $result = Controller_Temp::update_def_groups_values($show_notice);
                echo "Result of update_def_groups_values: " . $result . "<br>";

                $result = Controller_Temp::update_groups_values($show_notice);
                echo "Result of update_groups_values: " . $result . "<br>";

                $result = Controller_Temp::update_payment_method_currency($show_notice);
                echo "Result of update_payment_method_currency: " . $result . "<br>";

                $result = Controller_Temp::update_whitelabel_payment_method_currencies($show_notice);
                echo "Result of update_whitelabel_payment_method_currencies: " . $result . "<br>";

                break;
            case 'default_currency_update':
                $result = Controller_Temp::default_currency_update($show_notice);
                echo "Result of default_currency_update: " . $result . "<br>";
                break;
            case 'update_transactions_amount_payment_currencies':
                $result = Controller_Temp::update_transactions_amount_payment_currencies($show_notice, true, true);
                echo "Result of update_transactions_amount_payment_currencies: " . $result . "<br>";
                break;
            case 'update_transactions_values_manager':
                $result = Controller_Temp::update_transactions_values_manager($show_notice);
                echo "Result of update_transactions_values_manager: " . $result . "<br>";
                break;
            case 'update_withdrawals_request_amount_manager':
                $result = Controller_Temp::update_withdrawals_request_amount_manager($show_notice);
                echo "Result of update_withdrawals_request_amount_manager: " . $result . "<br>";
                break;
            case 'update_tickets_prizes_manager':
                $result = Controller_Temp::update_tickets_prizes_manager($show_notice);
                echo "Result of update_tickets_prizes_manager: " . $result . "<br>";
                break;
            case 'update_tickets_amount_payment':
                $result = Controller_Temp::update_tickets_amount_payment($show_notice);
                echo "Result of update_tickets_amount_payment: " . $result . "<br>";
                break;
            case 'update_ticket_lines_prizes_manager':
                $result = Controller_Temp::update_ticket_lines_prizes_manager($show_notice);
                echo "Result of update_ticket_lines_prizes_manager: " . $result . "<br>";
                break;
            case 'update_ticket_lines_amount_payment':
                $result = Controller_Temp::update_ticket_lines_amount_payment($show_notice);
                echo "Result of update_ticket_lines_amount_payment: " . $result . "<br>";
                break;
            case 'whitelabel_aff_payout':
                $result = Controller_Temp::whitelabel_aff_payout($show_notice);
                echo "Result of whitelabel_aff_payout: " . $result . "<br>";
                break;
            case 'update_aff_commission_manager':
                $result = Controller_Temp::update_aff_commission_manager($show_notice);
                echo "Result of update_aff_commission_manager: " . $result . "<br>";
                break;
            case 'update_def_groups_values':
                $result = Controller_Temp::update_def_groups_values($show_notice);
                echo "Result of update_def_groups_values: " . $result . "<br>";
                break;
            case 'update_groups_values':
                $result = Controller_Temp::update_groups_values($show_notice);
                echo "Result of update_groups_values: " . $result . "<br>";
                break;
            case 'update_payment_method_currency':
                $result = Controller_Temp::update_payment_method_currency($show_notice);
                echo "Result of update_payment_method_currency: " . $result . "<br>";
                break;
            case 'update_whitelabel_payment_method_currencies':
                $result = Controller_Temp::update_whitelabel_payment_method_currencies($show_notice);
                echo "Result of update_whitelabel_payment_method_currencies: " . $result . "<br>";
                break;
            default:
                echo "Wrong action given on Input";
                break;
        }

        return;
    }

    /**
     * Send email notification about new draw
     */
    public function action_drawnotification()
    {
        $notification_draw_helper = new Helpers_Notifications_Draw();
        $notification_draw_helper->send_emails();

        echo "OK";

        return;
    }

    /**
     * This function should be run once per 24 h to inform admins and manager
     * about low prepaid amount (compared to prepaid_alert_limit value in DB
     * All limits are compared in whitelabels currency
     */
    public function action_check_prepaids_are_low_for_whitelabels()
    {
        $whitelabels_prepaid_email = new Forms_Admin_Whitelabels_Prepaid_Email();
        $result = $whitelabels_prepaid_email->send_email_to_admins();

        switch ($result) {
            case Forms_Admin_Whitelabels_Prepaid_Email::RESULT_OK:
                $result_send_email_to_managers = $whitelabels_prepaid_email->send_email_to_managers();
                switch ($result_send_email_to_managers) {
                    case Forms_Admin_Whitelabels_Prepaid_Email::RESULT_OK:
                        ;
                        break;
                    case Forms_Admin_Whitelabels_Prepaid_Email::RESULT_NOTHING_TO_SEND:
                        break;
                    case Forms_Admin_Whitelabels_Prepaid_Email::RESULT_EMAIL_NOT_SENT:
                        break;
                }
                break;
            case Forms_Admin_Whitelabels_Prepaid_Email::RESULT_NOTHING_TO_SEND:
                break;
            case Forms_Admin_Whitelabels_Prepaid_Email::RESULT_EMAIL_NOT_SENT:
                break;
        }
    }

    /**
     * The purpose of this function is only for one time run
     */
    public function action_list_empty_users_for_truevo()
    {
        $constraints = [
            'select' => [
                'date'
            ],
            'where' => [
                ['message', 'like', '%Empty%'],                     // THE MAIN interested rows to find
                //['message', 'like', '%Begin of the process%'],
                //'whitelabel_transaction_id' => null,
                'payment_method_id' => Helpers_Payment_Method::TRUEVOCC,
                ['date', '>=', '2019-09-26 18:39:20'],              // First time Empty user happened
                ['date', '<=', '2019-10-25 16:35:01'],              // Last time Empty user happened
            ],
            'order_by' => [
                'date' => 'asc'
            ],
            'limit' => 165
        ];

        $empty_users_from_logs = Model_Payment_Log::find($constraints);

        if (empty($empty_users_from_logs)) {
            echo 'No empty users logs found:(';
            die();
        }

        // For those rows try to get whitelabel_transaction_id be get
        // previous to that row entrance with 'Show forms...' text
        // for Truevo and before that date.
        foreach ($empty_users_from_logs as $key => $empty_user_data) {
            $date = $empty_user_data->date;
            $inner_constraints = [
                'select' => [
                    'id',
                    'date',
                    'whitelabel_transaction_id'
                ],
                'where' => [
                    'payment_method_id' => Helpers_Payment_Method::TRUEVOCC,
                    ['whitelabel_transaction_id', 'IS NOT', null],              // So, we try to find those which are not null
                    'type' => Model_Payment_Log::TYPE_INFO,
                    ['message', 'like', '%Show form%'],
                    ['date', '<', $date],
                ],
                'order_by' => [
                    'id' => 'desc'
                ],
                'limit' => 1
            ];

            $payment_logs_with_transaction_id = Model_Payment_Log::find($inner_constraints);

            if (empty($payment_logs_with_transaction_id)) {
                echo '<br>NO Proper log found:(<br>';
            } else {
                $payment_log = $payment_logs_with_transaction_id[0];
                echo '------------------------------------------------------------';
                echo '<br>Found! ';
                echo 'Date: ' . $payment_log->date . ' ';
                echo 'Whitelabel_transaction_id: ' . $payment_log->whitelabel_transaction_id . '<br>';

                $transaction_constraints = [
                    'where' => [
                        'id' => $payment_log->whitelabel_transaction_id
                    ]
                ];

                $transactions = Model_Whitelabel_Transaction::find($transaction_constraints);

                if (empty($transactions)) {
                    echo '<br>No transactions found:(<br>';
                } else {
                    $single_transaction = $transactions[0];
                    echo '<br>Transaction ID: ' . $single_transaction->id . ' | ';
                    echo 'Whitelabel ID: ' . $single_transaction->whitelabel_id . ' | ';
                    echo 'Whitelabel User ID: ' . $single_transaction->whitelabel_user_id . ' | ';
                    echo 'Token: ' . $single_transaction->token . ' | ';
                    echo 'whitelabel_payment_method_id: ' . $single_transaction->whitelabel_payment_method_id . ' | ';
                    echo 'Type: ' . $single_transaction->type . ' | ';
                    echo 'Status: ' . $single_transaction->status . '<br>';
                    if (!empty($single_transaction->additional_data)) {
                        echo($single_transaction->additional_data);
                        echo '<br>////////////////////////////////////////////////////////<br>';
                    }
                }
            }
        }
    }

    public function action_import_to_mautic()
    {
        /** @var MauticPluginImportService $importService */
        $importService = Container::get(MauticPluginImportService::class);

        if ($importService->start() === false) {
            return;
        }

        $limitPerWhitelabel = MauticPluginImportService::WHITELABEL_USERS_LIMIT;
        $whitelabelUserRepository = Container::get(WhitelabelUserRepository::class);
        $whitelabelPluginRepository = Container::get(WhitelabelPluginRepository::class);

        Event::register('user_register', 'Events_User_Register::handle');

        $languages = Model_Language::get_all_languages();
        $whitelabelEnabledMauticPlugins = $whitelabelPluginRepository->getAllEnabledPlugins(WhitelabelPlugin::MAUTIC_API_NAME);

        $whitelabelList = $importService->getImportField('whitelabel');
        $whitelabelQueue = $importService->prepareWhitelabelQueueFromMauticPlugins($whitelabelEnabledMauticPlugins, $whitelabelList);

        foreach ($whitelabelQueue as $whitelabelId => $whitelabel) {
            $mauticPlugin = null;
            foreach ($whitelabelEnabledMauticPlugins as $plugin) {
                if ($plugin->whitelabel->id === $whitelabelId) {
                    $mauticPlugin = $plugin;
                    break;
                }
            }

            if (!$mauticPlugin) {
                continue;
            }

            $whitelabelImportSettings = [];
            $whitelabelImportUsers = true;
            $whitelabelLastUserId = null;
            $whitelabelStatusMessage = '';

            if (empty($mauticPlugin->whitelabel)) {
                continue;
            }

            $sinceUserId = (int) $importService->getImportField('sinceUserId', $whitelabelId);

            $importService->setImportStatus(MauticPluginImportService::STATUS_PENDING, $whitelabelId);

            echo "Whitelabel ID: {$whitelabelId} ";

            $wlImportStatusMsg = null;

            $activeUsersCriterias = WhitelabelPluginUserRepository::getActiveUsersCriterias($mauticPlugin->id);
            $users = $whitelabelUserRepository->getUsersAfterId($whitelabelId, $sinceUserId, $limitPerWhitelabel, $activeUsersCriterias);

            if (empty($users)) {
                $sinceUserId = null;
                $wlImportStatusMsg = 'Mautic import completed...starting from 0';
                $users = $whitelabelUserRepository->getUsersAfterId($whitelabelId, $sinceUserId, $limitPerWhitelabel, $activeUsersCriterias);
            }

            $importService->setImport(['sinceUserId' => $sinceUserId], $whitelabelId);

            if (empty($users)) {
                $whitelabelImportUsers = false;
                $whitelabelStatusMessage = 'No users found';
                $importService->setImportStatus(MauticPluginImportService::STATUS_SKIPPED, $whitelabelId);

                $importService->setImport([
                    'lastUserId' => null,
                    'message' => $whitelabelStatusMessage,
                ], $whitelabelId);
            } else {
                $whitelabelLastUserId = $whitelabelUserRepository->getLastUserId($whitelabelId, $activeUsersCriterias);
                $importService->setImport(['lastUserId' => $whitelabelLastUserId], $whitelabelId);
            }

            $importService->saveImport();

            if (!$whitelabelImportUsers) {
                echo $whitelabelStatusMessage . '<br>';
                continue;
            }

            if ($wlImportStatusMsg) {
                echo "[$wlImportStatusMsg]";
            }

            echo "[Limit: $limitPerWhitelabel] <br>";

            $usersIds = Arr::pluck($users, 'id');
            $lastPurchasedLotteryNameByUserId = $whitelabelUserRepository->getLastPurchasedLotteryNameByUsersIds($usersIds);

            foreach ($users as $user) {
                $currency = Model_Currency::find_by_pk($user->currency_id);

                $language_code = array_values(array_filter($languages, function ($v) use ($user) {
                    if ($user->language_id == $v['id']) {
                        return $v['code'];
                    }
                }));

                Event::trigger('user_register', [
                    'whitelabel_id' => $whitelabelId,
                    'user_id' => $user->id,
                    'plugin_data' => [
                        'created_at' => time(),
                        'email' => $user->email,
                        'currency' => $currency->code ?? '',
                        'is_active' => $user->is_active,
                        'is_confirmed' => $user->is_confirmed,
                        'is_deleted' => $user->is_deleted,
                        'date_register' => strtotime($user->date_register),
                        'balance' => $user->balance,
                        'register_ip' => $user->register_ip,
                        'last_ip' => $user->last_ip,
                        'last_active' => strtotime($user->last_active),
                        'last_update' => strtotime($user->last_update),
                        'last_country' => $user->last_country,
                        'register_country' => $user->register_country,
                        'firstname' => $user->name,
                        'lastname' => $user->surname,
                        'date_of_birth' => $user->birthdate,
                        'phone' => $user->phone,
                        'phone_country_code' => $user->phone_country,
                        'country_code' => $user->country,
                        'city' => $user->city,
                        'region' => $user->state,
                        'address_1' => $user->address_1,
                        'address_2' => $user->address_2,
                        'zipcode' => $user->zip,
                        'gender' => $user->gender == 1 ? 'male' : 'female',
                        'language' => $language_code[0]['code'],
                        'first_deposit_amount_manager' => $user->first_deposit_amount_manager,
                        'last_purchase_amount_manager' => $user->last_purchase_amount_manager,
                        'total_deposit_manager' => $user->total_deposit_manager,
                        'total_withdrawal_manager' => $user->total_withdrawal_manager,
                        'total_purchases_manager' => $user->total_purchases_manager,
                        'total_net_income_manager' => $user->total_net_income_manager,
                        'last_purchase_date' => strtotime($user->last_purchase_date),
                        'last_purchase_lottery' => $lastPurchasedLotteryNameByUserId[$user->id],
                        'last_deposit_date' => strtotime($user->last_deposit_date),
                        'last_deposit_amount_manager' => $user->last_deposit_amount_manager,
                        'net_winnings_manager' => $user->net_winnings_manager,
                        'sale_status' => $user->sale_status,
                        'pnl_manager' => $user->pnl_manager,
                    ],
                    'register_data' => [
                        'event' => 'register',
                        'user_id' => $whitelabel['prefix'] . 'U' . $user->token,
                    ]
                ]);

                $sinceUserId = $user->id;

                $whitelabelImportSettings['sinceUserId'] = $sinceUserId;
                $whitelabelImportSettings['lastUserId'] = $whitelabelLastUserId;

                if ($importService->isTimeoutExceeded()) {
                    break;
                }

                $importService->setTotalTime();
                $importService->setImport($whitelabelImportSettings, $whitelabelId);
                $importService->saveImport();

                echo "$user->email imported. <br>";
            }

            if ($sinceUserId >= $whitelabelLastUserId) {
                $importService->setImportStatus(MauticPluginImportService::STATUS_COMPLETED, $whitelabelId);
            } else {
                $importService->setImportStatus(MauticPluginImportService::STATUS_QUEUED, $whitelabelId);
            }

            $whitelabelListImport = $importService->getImportField('whitelabel');
            $currentWhitelabelItem = $whitelabelListImport[$whitelabelId];
            unset($whitelabelListImport[$whitelabelId]);
            $whitelabelListImport[$whitelabelId] = $currentWhitelabelItem;
            $importService->setWhitelabelQueue($whitelabelListImport);

            $importService->saveImport();

            if ($importService->isTimeoutExceeded()) {
                break;
            }
        }

        $importService->finish();

        echo 'DONE!!!<br>';
    }

    public function action_import_casino_data_to_mautic()
    {
        $whitelabelTransactionRepository = Container::get(WhitelabelTransactionRepository::class);

        $casinoDepositsDataForUsers = $whitelabelTransactionRepository->getCasinoDepositUsdSumforUsersForMautic(self::LOTTOPARK_ID);

        foreach ($casinoDepositsDataForUsers as $user) {
            self::process_mautic($user['whitelabel_user_id'], self::LOTTOPARK_ID, [
                'last_30_d_casino_deposit' => $user['last_30_days_casino_deposit'],
                'total_casino_deposit' => $user['total_casino_deposit'],
            ]);
        }
    }
}
