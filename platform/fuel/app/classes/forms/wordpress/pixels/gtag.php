<?php

use Helpers\UserHelper;
use Fuel\Core\Input;
use Fuel\Core\View;
use Fuel\Core\Session;
use Models\Whitelabel;
use Services\Logs\FileLoggerService;

class Forms_Wordpress_Pixels_Gtag
{

    /**
     * Stores the name of the session item
     * @var string
     */
    protected static $session_flash_name = "gtag_delay_event";

    /**
     * Stores affiliate
     * @var Object|array affiliate
     */
    protected static $affiliate = null;

    /**
     * Stores event to fire
     * @var ?array
     */
    protected static $events = null;

    /**
     * @param $affiliate
     * @return void
     */
    public static function set_affiliate($affiliate = null): void
    {
        self::$affiliate = $affiliate;
    }

    public static function trigger_event($name, $data, $delay = false): void
    {
        $event = ["name" => $name, "data" => $data];

        if (!empty(IS_CASINO)) {
            return;
        }

        if ($delay) {
            Session::set_flash(self::$session_flash_name, $event);
        } else {
            self::$events[] = $event;
        }
    }

    /**
     * Used to filter JS events pixel data for affiliates
     *
     * @return array
     */
    protected static function get_affiliate_events(): ?array
    {
        /** @var FileLoggerService $fileLoggerService */
        $fileLoggerService = Container::get(FileLoggerService::class);

        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        if (empty($whitelabel)) {
            $fileLoggerService->error('Cannot find whitelabel');
            return null;
        }

        $events = self::$events;
        if (!empty($events)) {
            foreach ($events as $ekey => $event) {
                if (isset($event['data']['price'])) {
                    if ($whitelabel->affHideAmount) {
                        $events[$ekey]['data']['price'] = "1.00";
                    }
                }
                if (isset($event['data']['transaction_id'])) {
                    if (!is_null(self::$affiliate) && self::$affiliate['hide_transaction_id'] == 1) {
                        if ($event['name'] == "purchase") {
                            $events[$ekey]['data']['transaction_id'] = round(microtime(true) * 1000);
                        } else {
                            unset($events[$ekey]['data']['transaction_id']);
                        }
                    }
                }
                if (isset($event['data']['items'])) {
                    foreach ($event['data']['items'] as $ikey => $item) {
                        if ($whitelabel->affHideAmount) {
                            $events[$ekey]['data']['items'][$ikey]['price'] = "1.00";
                        }
                    }
                }
            }
        }
        return $events;
    }

    /**
     * Used to filter JS events pixel configuration for affiliates
     *
     * @return array
     */
    protected static function get_affiliate_additional_config(array $additional_config): array
    {
        if (!is_null(self::$affiliate) && self::$affiliate['hide_lead_id'] == 1) {
            unset($additional_config["user_id"]);
        }
        return $additional_config;
    }

    /**
     * @return string
     */
    public static function generate_code(): string
    {
        $whitelabel = Container::get('whitelabel');
        if (empty($whitelabel['analytics'])) {
            return "";
        }

        $gtag_session = Session::get_flash(self::$session_flash_name);
        if (!empty($gtag_session)) {
            self::$events[] = $gtag_session;
        }

        $additional_config = [];


        $user = UserHelper::getUser();
        $isUser = !empty($user);
        if ($isUser) {
            $additional_config["user_id"] = Lotto_Helper::get_user_token($user);
        }

        if (
            empty(Input::get("utm_campaign")) && empty(Input::get("utm_medium"))
            && empty(Input::get("utm_content")) && empty(Input::get("utm_source"))
        ) {
            if (!is_null(self::$affiliate)) {
                if (!empty(self::$affiliate['analytics'])) {
                    if (!empty(self::$affiliate['campaign'])) {
                        $additional_config['campaign']['name'] = self::$affiliate['campaign'];
                    }
                    if (!empty(self::$affiliate['medium'])) {
                        $additional_config['campaign']['medium'] = self::$affiliate['medium'];
                    }
                    if (!empty(self::$affiliate['content'])) {
                        $additional_config['campaign']['content'] = self::$affiliate['content'];
                    }
                }
                $additional_config['campaign']['source'] = strtoupper(self::$affiliate['token']);
            }

            if (empty($additional_config['campaign']['name']) && !empty(Session::get("campaign"))) {
                $additional_config['campaign']['name'] = Session::get("campaign");
            }
            if (empty($additional_config['campaign']['medium']) && !empty(Session::get("medium"))) {
                $additional_config['campaign']['medium'] = Session::get("medium");
            }
            if (empty($additional_config['campaign']['content']) && !empty(Session::get("content"))) {
                $additional_config['campaign']['content'] = Session::get("content");
            }

            if (empty($additional_config['campaign']['name']) && !empty(Input::get("campaign"))) {
                $additional_config['campaign']['name'] = Input::get("campaign");
            }
            if (empty($additional_config['campaign']['medium']) && !empty(Input::get("medium"))) {
                $additional_config['campaign']['medium'] = Input::get("medium");
            }
            if (empty($additional_config['campaign']['content']) && !empty(Input::get("content"))) {
                $additional_config['campaign']['content'] = Input::get("content");
            }

            if (
                !empty($additional_config) && !empty($additional_config['campaign'])
                && empty($additional_config['campaign']['source'])
            ) {
                $additional_config['campaign']['source'] = "";
            }
        }

        return View::forge("wordpress/pixels/gtag", [
            "whitelabel_gtag_id" => $whitelabel['analytics'],
            "affiliate_gtag_id" => empty(self::$affiliate['analytics']) ? null : self::$affiliate['analytics'],
            "additional_config" => $additional_config,
            "events" => self::$events,
            "affiliate_events" => self::get_affiliate_events(),
            "affiliate_additional_config" => self::get_affiliate_additional_config($additional_config)
        ]);
    }
}
