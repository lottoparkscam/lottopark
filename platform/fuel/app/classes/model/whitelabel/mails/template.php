<?php

use Fuel\Core\DB;
use Services\Logs\FileLoggerService;

class Model_Whitelabel_Mails_Template extends \Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'mail_template';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @param array $whitelabel
     * @return array
     */
    public static function get_settings(array $whitelabel): array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $settings = [];
        $query = "SELECT * 
            FROM whitelabel_setting 
            WHERE whitelabel_id = :whitelabel";

        $db = DB::query($query);
        $db->param(":whitelabel", $whitelabel['id']);

        try {
            $settings = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        $settings_arr = [];
        foreach ($settings as $setting) {
            $settings_arr[$setting['name']] = $setting['value'];
        }

        return $settings_arr;
    }

    /**
     * Get specific mail template for whitelabel
     *
     * @param int $whitelabel_id
     * @param null|string $slug
     * @param null|string $lang
     * @return mixed
     *
     */
    public static function get_whitelabel_templates(
        int $whitelabel_id,
        string $slug = null,
        string $lang = null
    ) {
        $query = "SELECT  
            DISTINCT `mail_template`.`slug` AS `slug`,
            `mail_template`.`id` AS `template_id`,
            `mail_template`.`additional_translates` AS `additional_translates`,
            `whitelabel_mail_template`.`additional_translates` AS `custom_additional_translates`,
            `whitelabel_mail_template`.`id` AS `custom_template_id`,
            `mail_template`.`title` AS `original_title`,
            IF(`whitelabel_mail_template`.`content` IS NULL, `mail_template`.`content`, `whitelabel_mail_template`.`content`) AS `content`,
            IF(`whitelabel_mail_template`.`text_content` IS NULL, `mail_template`.`text_content`, `whitelabel_mail_template`.`text_content`) AS `text_content`,
            IF(`whitelabel_mail_template`.`title` IS NULL, `mail_template`.`title`, `whitelabel_mail_template`.`title`) AS `title`             
        FROM `mail_template`
        LEFT JOIN `whitelabel_mail_template`
            ON `whitelabel_mail_template`.`mail_template_id` = `mail_template`.`id` 
            AND `whitelabel_mail_template`.`whitelabel_id` = :whitelabel";

        if (!empty($lang)) {
            $query .= " AND `whitelabel_mail_template`.`lang` = :lang";
        }

        if (!empty($slug)) {
            $query .= " WHERE `mail_template`.`slug` = :slug";
        }

        $db = DB::query($query);

        // Whitelabel param
        $db->param(":whitelabel", $whitelabel_id);

        // Lang param
        if (!empty($lang)) {
            $db->param(":lang", $lang);
        }

        // Slug param
        if (!empty($slug)) {
            $db->param(":slug", $slug);
        }

        $db = $db->execute();

        if (!empty($slug)) {
            return $db->current();
        }

        return $db->as_array();
    }

    /**
     * Get mail templates list for whitelabel
     *
     * @return null|array
     */
    public static function get_whitelabel_templates_manager_list():? array
    {
        $query = "SELECT 
            `mail_template`.`slug` AS `slug`,
            `mail_template`.`id` AS `template_id`,
            `mail_template`.`title` AS `original_title` 
        FROM `mail_template`";

        $db = DB::query($query);

        $db = $db->execute();

        return $db->as_array();
    }
}
