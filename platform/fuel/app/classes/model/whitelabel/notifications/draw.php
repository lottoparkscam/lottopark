<?php
use Fuel\Core\DB;
use Services\MailLimitService;

/**
 *
 */
class Model_Whitelabel_Notifications_Draw extends \Fuel\Core\Model_Crud
{
    protected static string $_table_name = 'user_draw_notification';
    public static array $cache_list = [];

    /** Get available users list for draw email notifications*/
    public static function get_users_draw_notification(): array
    {
        $select_columns = [
            'user_draw_notification.id',
            'whitelabel_user.email',
            'whitelabel_user.whitelabel_id',
            'whitelabel_user.is_confirmed',
            ['whitelabel_user.timezone', 'user_timezone'],
            'lottery.name',
            'lottery.timezone',
            'lottery_draw.date_local',
            'lottery_draw.numbers',
            'lottery_draw.bnumbers',
            'language.code'
        ];

        /** @var MailLimitService $mailLimitService */
        $mailLimitService = Container::get(MailLimitService::class);
        $limit = $mailLimitService->getDrawMailsLimitPerMinute();

        $result = DB::select_array($select_columns)
            ->from(self::$_table_name)
            ->join('whitelabel_user')->on('whitelabel_user.id', '=', 'user_draw_notification.user_id')
            ->join('lottery_draw')->on('lottery_draw.id', '=', 'user_draw_notification.lottery_draw_id')
            ->join('lottery')->on('lottery.id', '=', 'user_draw_notification.lottery_id')
            ->join('language')->on('language.id', '=', 'whitelabel_user.language_id')
            ->where('is_email_sent', '=', 0)
            ->limit($limit)
            ->execute()
            ->as_array();

        return $result;
    }

    /**
     * Get notification draw record
     * @param int $user_id
     * @param int $lottery_id
     * @param string $lottery_draw_date
     * @return array
     */
    public static function get_draw_notification_record(int $user_id, int $lottery_id, string $lottery_draw_date) : array
    {
        $result = DB::select('*')
            ->from(self::$_table_name)
            ->where('user_id', '=', $user_id)
            ->where('lottery_id', '=', $lottery_id)
            ->where('lottery_draw_date', '=', $lottery_draw_date)
            ->execute()
            ->current();

        return $result ?? [];
    }

    /**
     * Insert new record to user_draw_notification
     * @param int $user_id
     * @param int $lottery_id
     * @param string $lottery_draw_date
     * @return array
     * @throws Exception
     */
    public static function insert_draw_notification_record(int $user_id, int $lottery_id, string $lottery_draw_date) : array
    {
        $email_notification_draw = self::forge()->set([
            'user_id' => $user_id,
            'lottery_id' => $lottery_id,
            'lottery_draw_date' => $lottery_draw_date,
            'is_email_sent' => 0
        ]);

        return $email_notification_draw->save();
    }

    /**
     * Update records in user_draw_notification
     * Updated records means that emails are ready to sent
     * @param int $lottery_id
     * @param string $lottery_draw_date
     * @param int $lottery_draw_id
     * @return bool
     */
    public static function update_draw_notification_emails(int $lottery_id, string $lottery_draw_date, int $lottery_draw_id) : bool
    {
        DB::update(self::$_table_name)
            ->set([
                'lottery_draw_id'  => $lottery_draw_id,
                'lottery_draw_date' => null
            ])
            ->where('lottery_id', '=', $lottery_id)
            ->where('lottery_draw_date', '=', $lottery_draw_date)
            ->execute();

        return true;
    }
}
