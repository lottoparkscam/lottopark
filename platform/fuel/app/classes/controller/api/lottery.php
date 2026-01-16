<?php

use Fuel\Core\CacheNotFoundException;
use Fuel\Core\Input;
use Fuel\Core\Request;
use Fuel\Core\Response;
use Helpers\SanitizerHelper;
use Helpers\UrlHelper;
use Services\Api\Controller;
use Services\Api\Reply;
use Services\CacheService;
use Services\Logs\FileLoggerService;

class Controller_Api_Lottery extends Controller
{
    private FileLoggerService $fileLoggerService;
    private CacheService $cacheService;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->fileLoggerService->setSource('api');
        $this->cacheService = Container::get(CacheService::class);
    }

    private function prepareLotteryPayload(array $lottery): array
    {
        $draw = Lotto_Helper::get_lottery_real_next_draw($lottery);
        $real_next_draw = $draw->format("Y-m-d H:i:s");
        $draw->setTimezone(new DateTimeZone("UTC"));
        $real_next_draw_utc = $draw->format("Y-m-d H:i:s");

        return [
            "id" => $lottery['id'],
            "name" => $lottery['slug'],
            "full_name" => $lottery['name'],
            "country" => $lottery['country'],
            "country_iso" => $lottery['country_iso'],
            "timezone" => $lottery['timezone'],
            "draw_days" => Helpers_Time::drawDateToDrawDays(json_decode($lottery['draw_dates'], true)),
            "draw_dates" => $lottery['draw_dates'],
            "draw_date_local" => $lottery['next_date_local'],
            "draw_date_utc" => $lottery['next_date_utc'],
            "jackpot" => $lottery['current_jackpot'] * 1000000,
            "currency" => $lottery['currency'],
            "last_date_local" => $lottery['last_date_local'],
            "last_numbers" => $lottery['last_numbers'],
            "last_bnumbers" => $lottery['last_bnumbers'],
            "last_total_prize" => $lottery['last_total_prize'],
            "last_total_winners" => $lottery['last_total_winners'],
            "real_draw_date_local" => $real_next_draw,
            "real_draw_date_utc" => $real_next_draw_utc,
        ];
    }

    /**
     * @OA\Get(
     *     path="/lotteries",
     *     tags={"Lotteries"},
     *     @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="ASC or DESC"
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field to order by",
     *          example="id"
     *     ),
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Max amount of returned lotteries"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Get current lotteries"
     *     )
     * )
     */
    public function get_lotteries(): Response
    {
        $order = SanitizerHelper::sanitizeString(Input::get('order') ?? '');
        $order_by = SanitizerHelper::sanitizeString(Input::get('order_by') ?? '');
        $limit = (int)SanitizerHelper::sanitizeString(Input::get('limit') ?? '');

        if ($order === null) {
            $order = "ASC";
        }

        if (!in_array(strtoupper($order), ["ASC", "DESC"])) {
            $message = ["The specified order value is not correct"];

            $response = $this->returnResponse(
                $message,
                Reply::BAD_REQUEST
            );

            return $response;
        }

        if (!in_array($order_by, ["id", "jackpot"])) {
            $message = ["The specified order_by value is not correct"];

            $response = $this->returnResponse(
                $message,
                Reply::BAD_REQUEST
            );

            return $response;
        }

        if ($order_by == "jackpot") {
            $order_by = "current_jackpot";
        }

        $lotteries = Model_Whitelabel::get_lotteries_by_order_for_whitelabel(
            $this->whitelabel->id,
            (string) $order_by,
            (string) $order
        );

        if ($limit !== null) {
            $limit = intval($limit);
            if ($limit > 0) {
                if ($lotteries !== null && $limit > count($lotteries)) {
                    $limit = count($lotteries);
                }
                $lotteries = array_slice($lotteries, 0, $limit);
            }
        }

        $data = [];

        foreach ($lotteries as $lottery) {
            $data[] = $this->prepareLotteryPayload($lottery);
        }

        return $this->returnResponse($data);
    }

    /**
     * @OA\Get(
     *     path="/lottery",
     *     tags={"Lotteries"},
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Lottery name"
     *     ),
     *     @OA\Response(response="200", description="Get specific lottery")
     * )
     */
    public function get_lottery(): Response
    {
        $name = Input::get("name");
        $this->setXmlBasenode('lottery');
        $lotteries = Model_Lottery::get_all_lotteries();

        if (!isset($lotteries['__by_slug'][$name])) {
            $message = ["The specified lottery could not be found"];

            $response = $this->returnResponse(
                $message,
                Reply::BAD_REQUEST
            );

            return $response;
        }

        $lottery = $lotteries['__by_slug'][$name];

        $data = $this->prepareLotteryPayload($lottery);

        return $this->returnResponse($data);
    }

    /**
     * Prepare twig template to use in mautic
     *
     * @OA\Get(
     *     path="/lotteries_mautic",
     *     tags={"Lotteries"},
     *     @OA\Response(
     *          response="200",
     *          description="Get 3 lotteries with highest jackpot to use in mautic"
     *     )
     * )
     */
    public function get_lotteries_mautic(): Response
    {
        $cacheKey = 'lotteries_mautic_data';
        try {
            $lotteries = $this->cacheService->getCacheForWhitelabelByDomain($cacheKey);
            $success = true;
        } catch (CacheNotFoundException $e) {
            $limit = 3;

            $lotteries = Model_Whitelabel::get_lotteries_by_highest_jackpot_for_whitelabel($this->whitelabel->id);

            if ($lotteries !== null && $limit > $lotteriesCount = count($lotteries)) {
                $limit = $lotteriesCount;
            }

            $lotteries = array_slice($lotteries, 0, $limit);

            $this->cacheService->setCacheForWhitelabelByDomain($cacheKey, $lotteries, Helpers_Time::MINUTE_IN_SECONDS);
            $successCache = true;
        } catch (Throwable $e) {
            $this->fileLoggerService->error('Cannot send lotteries data to mautic:' . $e->getMessage());
            return $this->returnResponse(['Unable to fetch data.'], Reply::SERVICE_UNAVAILABLE);
        } finally {
            if (!(isset($success) || isset($successCache))) {
                $this->fileLoggerService->error('Cannot send lotteries data to mautic:' . var_export(error_get_last(), true));
            }
        }

        $data = [];

        foreach ($lotteries as $key => $lottery) {
            $data[$key] = $this->prepareLotteryPayload($lottery);
            $data[$key]['img'] = UrlHelper::getHomeUrlWithoutLanguage() . '/wp-content/plugins/lotto-platform/public/images/lotteries/lottery_' . intval($lottery['id']) . '.png';
            $data[$key]['link'] = UrlHelper::getHomeUrlWithoutLanguage() . '/play/' . $lottery['slug'];
            $data[$key]['jackpot_formatted'] = Lotto_View::format_currency($lottery['current_jackpot'] * 1000000, $lottery['currency'], 0, 'en_US');
        }

        $this->setFormat('json');

        return $this->returnResponse([
            'wl_lottery_1_name' => $data[0]['full_name'],
            'wl_lottery_1_date' => $data[0]['real_draw_date_utc'],
            'wl_lottery_1_img' => $data[0]['img'],
            'wl_lottery_1_link' => $data[0]['link'],
            'wl_lottery_1_jackpot' => $data[0]['jackpot_formatted'],

            'wl_lottery_2_name' => $data[1]['full_name'],
            'wl_lottery_2_date' => $data[1]['real_draw_date_utc'],
            'wl_lottery_2_img' => $data[1]['img'],
            'wl_lottery_2_link' => $data[1]['link'],
            'wl_lottery_2_jackpot' => $data[1]['jackpot_formatted'],

            'wl_lottery_3_name' => $data[2]['full_name'],
            'wl_lottery_3_date' => $data[2]['real_draw_date_utc'],
            'wl_lottery_3_img' => $data[2]['img'],
            'wl_lottery_3_link' => $data[2]['link'],
            'wl_lottery_3_jackpot' => $data[2]['jackpot_formatted'],
        ]);
    }
}
