<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Multidraw_Option extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_multi_draw_option';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     * Get multi-draws options for whitelabel
     *
     * @param int $whitelabel_id
     * @return array|null
     */
    public static function get_whitelabel_options(int $whitelabel_id):? array
    {
        return self::find([
            'select' => '*',
            'where' => [
                'whitelabel_id' => $whitelabel_id,
            ],
            'order_by' => [
                'tickets' => 'asc',
            ],
        ]);
    }

    /**
     * Get all lotteries for whitelabel
     *
     * @param int $whitelabel_id
     * @return array|null
     */
    public static function get_whitelabel_lotteries(int $whitelabel_id):? array
    {
        return self::find_by('whitelabel_id', $whitelabel_id);
    }

    /**
     * Get specific whitelabel multi-draw option
     *
     * @param int $whitelabel_id
     * @param int $id
     * @return Model_Whitelabel_Multidraw_Option|null
     */
    public static function get_whitelabel_option(
        int $whitelabel_id,
        int $id
    ):? Model_Whitelabel_Multidraw_Option {
        return self::find_one_by([
            'whitelabel_id' => $whitelabel_id,
            'id' => $id
        ]);
    }

    /**
     * Add multi-draw option
     *
     * @param int $whitelabel_id
     * @param int $tickets
     * @param string $discount
     * @return array|int
     */
    public static function add_whitelabel_option(
        int $whitelabel_id,
        int $tickets,
        string $discount = null
    ) {
        $option_set = [
            'whitelabel_id' => $whitelabel_id,
            'tickets' => $tickets,
            'discount' => $discount ? $discount : null
        ];
        
        $option = self::forge()->set($option_set);

        return $option->save();
    }

    /**
     * Edit multi-draw option
     *
     * @param int $whitelabel_id
     * @param int $id
     * @param int $tickets
     * @param string $discount
     * @return array|int
     */
    public static function edit_whitelabel_option(
        int $whitelabel_id,
        int $id = null,
        int $tickets = null,
        string $discount = null
    ) {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($id) || empty($tickets)) {
            $error_message = "No Id or tickets given!";

            $fileLoggerService->error(
                $error_message
            );
        }
        
        $option = self::find_by_pk($id);
        $option->tickets = $tickets;
        $option->discount = !empty($discount) ? $discount : null;

        return $option->save();
    }
}
