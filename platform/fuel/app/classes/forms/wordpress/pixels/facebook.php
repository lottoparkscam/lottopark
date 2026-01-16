<?php

/*
 * (c) Tomasz Klapsia
 *
 */

use Helpers\UserHelper;

class Forms_Wordpress_Pixels_Facebook
{

    /**
     * Stores the name of the session item
     * @var string
     */
    protected static $session_flash_name = "fb_delay_event";

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
        static::$affiliate = $affiliate;
    }

    public static function getAffFbPixel(): string
    {
        return empty(self::$affiliate['fb_pixel']) ? '' : self::$affiliate['fb_pixel'];
    }

    public static function trigger_event($name, $data, $delay = false, $custom = false): void
    {
        $event = ["name" => $name, "data" => $data, "custom" => $custom];

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
        $whitelabel = Container::get('whitelabel');
        $events = self::$events;
        if (!empty($events)) {
            foreach ($events as $key => $event) {
                if (isset($events[$key]['data']['price'])) {
                    if ($whitelabel['aff_hide_amount'] == 1) {
                        $events[$key]['data']['price'] = 0;
                    }
                }
                if (isset($events[$key]['data']['transaction_id'])) {
                    if (!is_null(self::$affiliate) && self::$affiliate['hide_transaction_id'] == 1) {
                        unset($events[$key]['data']['transaction_id']);
                    }
                }
                if (isset($events[$key]['data']['items'])) {
                    foreach ($events[$key]['data']['items'] as $ikey => $item) {
                        if ($whitelabel['aff_hide_amount'] == 1) {
                            $events[$key]['data']['items'][$ikey]['price'] = 0;
                        }
                    }
                }
            }
        }
        return $events;
    }

    public static function convert_data(?array $events)
    {
        $new_events = [];
        if (!empty($events)) {
            foreach ($events as $event) {
                $data = $event['data'];
                $fb_items = [];
                if (!empty($data['items'])) {
                    foreach ($data['items'] as $item) {
                        $fb_item = [
                            "id" => $item['id'],
                            "quantity" => $item['quantity'],
                            "content_category" => $item['list_name'],
                            "content_name" => $item['name']
                        ];
                        if (isset($item['price'])) {
                            $fb_item['value'] = $item['price'];
                            $fb_item['currency'] = $item['currency'];
                        }
                        $fb_items[] = $fb_item;
                    }
                }
                $fb_data = [];
                if (!empty($fb_items)) {
                    $fb_data["content_type"] = "product_group";
                    $fb_data["contents"] = $fb_items;
                    $fb_data["num_items"] = count($fb_items);
                }
                if (isset($data["price"])) {
                    $fb_data["value"] = $data["price"];
                    $fb_data["currency"] = $data["currency"];
                }
                if (isset($data["transaction_id"])) {
                    $fb_data['transaction_id'] = $data["transaction_id"];
                }
                $new_event = ["name" => $event['name'], "data" => $fb_data];
                if (isset($event['custom'])) {
                    $new_event['custom'] = $event['custom'];
                }
                $new_events[] = $new_event;
            }
        }
        return $new_events;
    }

    /**
     * Used to filter JS events pixel configuration for affiliates
     *
     * @return array
     */
    protected static function get_affiliate_additional_config(array $additional_config): array
    {
        if (!is_null(self::$affiliate) && self::$affiliate['hide_lead_id'] == 1) {
            unset($additional_config["uid"]);
        }
        return $additional_config;
    }

    /**
     * @return string
     */
    public static function generate_code(): string
    {
        $whitelabel = Container::get('whitelabel');
        if (empty($whitelabel['fb_pixel'])) {
            return "";
        }

        $fb_session = Session::get_flash(self::$session_flash_name);
        if (!empty($fb_session)) {
            self::$events[] = $fb_session;
        }

        $affiliate_events = self::convert_data(self::get_affiliate_events());
        self::$events = self::convert_data(self::$events);

        $additional_config = [];
        $affiliate_additional_config = [];

        $user = UserHelper::getUser();
        $isUser = !empty($user);
        if ($isUser) {
            $additional_config["uid"] = Lotto_Helper::get_user_token($user);
        }

        /* Advanced user matching */
        if ($whitelabel['fb_pixel_match'] && $isUser) {
            $user_data = [];

            $user_data["em"] = strtolower($user['email']);

            if (!empty($user['name'])) {
                $user_data["fn"] = mb_strtolower(str_replace(" ", "", $user['name']));
            }
            if (!empty($user['surname'])) {
                $user_data["ln"] = mb_strtolower(str_replace(" ", "", $user['surname']));
            }
            if (!empty($user['phone'])) {
                $user_data["ph"] = str_replace("+", "", $user['phone']);
            }
            if (!empty($user['birthdate'])) {
                $user_data["db"] = str_replace("-", "", $user['birthdate']);
            }
            if (!empty($user['city'])) {
                $user_data["ct"] = mb_strtolower(str_replace(["-", " "], "", $user['city']));
            }
            if (!empty($user['country'])) {
                $user_data["cn"] = mb_strtolower($user['country']);
            }
            if ($user['gender'] != 0) {
                $user_data["ge"] = $user['gender'] == "1" ? "m" : "f";
            }
            if (!empty($user['state'])) {
                $state = explode("-", $user['state']);
                if (isset($state[1])) {
                    $user_data["st"] = mb_strtolower($state[1]);
                }
            }
            if (!empty($user['zip'])) {
                $user_data['zp'] = str_replace(["-", " "], "", $user['zip']);
            }
            $affiliate_additional_config = $additional_config;
            $additional_config = array_merge($additional_config, $user_data);
            if (!is_null(self::$affiliate) && self::$affiliate['hide_lead_id'] == 0 && self::$affiliate['is_show_name']) {
                $affiliate_additional_config = $additional_config;
            }
        }

        return View::forge("wordpress/pixels/facebook", [
            "whitelabel_pixel_id" => $whitelabel['fb_pixel'],
            "affiliate_pixel_id" => empty(self::$affiliate['fb_pixel']) ? null : self::$affiliate['fb_pixel'],
            "additional_config" => $additional_config,
            "events" => self::$events,
            "affiliate_additional_config" => self::get_affiliate_additional_config($affiliate_additional_config),
            "affiliate_events" => $affiliate_events
        ]);
    }
}
