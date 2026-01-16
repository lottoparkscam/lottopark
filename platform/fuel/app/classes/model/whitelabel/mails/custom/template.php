<?php

use Fuel\Core\DB;
use Services\Logs\FileLoggerService;

class Model_Whitelabel_Mails_Custom_Template extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_mail_template';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     * Update email template
     *
     * @param int $custom_template_id
     * @param int $template_id
     * @param int $whitelabel_id
     * @param string $title
     * @param string $content
     * @param string $text_content
     * @param string $mail_lang
     * @param array $additional_translations
     * @return void
     */
    public static function update_email_template(
        int $custom_template_id = null,
        int $template_id = null,
        int $whitelabel_id = null,
        string $title = "",
        string $content = "",
        string $text_content = "",
        string $mail_lang = "",
        array $additional_translations = []
    ): void {
        $fileLoggerService = Container::get(FileLoggerService::class);

        if (empty($template_id) || empty($whitelabel_id) || empty($title)) {
            $check_template_id = "Not exists";
            if (!empty($template_id)) {
                $check_template_id = $template_id;
            }
            $check_whitelabel_id = "Not exists";
            if (!empty($whitelabel_id)) {
                $check_whitelabel_id = $whitelabel_id;
            }
            $check_title = "Not exists";
            if (!empty($title)) {
                $check_title = $title;
            }
            
            $error_message = "Template could not be saved becuase of " .
                "empty or null data amongst template_id (" . $check_template_id .
                "), whitelabel_id (" . $check_whitelabel_id .
                "), title (" . $check_title .
                ").";
            $fileLoggerService->error(
                $error_message
            );
            return;
        }
        
        // Insert custom email template or update if already exist
        if (!empty($custom_template_id)) {
            $template = self::find_by_pk($custom_template_id);
        } else {
            $template = self::forge();
        }

        $data = [
            'whitelabel_id' => $whitelabel_id,
            'mail_template_id' => $template_id,
            'title' => $title,
            'content' => $content,
            'text_content' => $text_content,
            'lang' => $mail_lang ? $mail_lang : '',
            'additional_translates' => serialize($additional_translations)
        ];

        $template->set($data);
        $template->save();
    }

    /**
     * Restore default mail template
     *
     * @param int $whitelabel_id
     * @param string $slug
     * @param string $lang
     * @return object
     */
    public static function restore_default(
        int $whitelabel_id,
        string $slug,
        string $lang
    ) {
        $query = "DELETE `whitelabel_mail_template`      
            FROM `mail_template`
            INNER JOIN `whitelabel_mail_template`
                ON `whitelabel_mail_template`.`mail_template_id` = `mail_template`.`id` 
                AND `whitelabel_mail_template`.`whitelabel_id`=" . $whitelabel_id;

        if (!empty($lang)) {
            $query .= " AND `whitelabel_mail_template`.`lang`='" . $lang . "'";
        }

        if (!empty($slug)) {
            $query .= " WHERE `mail_template`.`slug` = '" . addslashes($slug) . "'";
        }

        return DB::query($query)->execute();
    }

    /**
     * Update additional translates for specific custom email template translsation
     *
     * @param int $custom_template_id
     * @param array $additional_translates
     * @return void
     */
    public static function update_additional_translates(
        int $custom_template_id,
        array $additional_translates
    ): void {
        $data = [
            'additional_translates' => serialize($additional_translates)
        ];

        $template = self::find_by_pk($custom_template_id);
        $template->set($data);
        $template->save();
    }
}
