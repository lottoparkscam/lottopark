<?php

use Fuel\Core\Validation;
use Fuel\Core\Config;
use Services\Logs\FileLoggerService;
use Fuel\Core\DB;
use Fuel\Core\Response;
use Fuel\Core\Session;
use Fuel\Core\Input;

/**
 * Class for preparing New form
 */
class Forms_Whitelabel_New extends Forms_Main
{
    use Traits_Gets_States;

    /**
     *
     * @var array
     */
    private $data = [];

    /**
     *
     * @var array
     */
    private $map_providers = [];

    /**
     *
     * @var array
     */
    private $errors = [];
    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var bool
     */
    private $log_errors = true;

    /**
     *
     * @var array
     */
    private $kcurrencies = [];

    /**
     *
     * @var View
     */
    private $inside = null;

    private $should_setup_fuel = true;
    private $should_setup_wordpress = true;

    private $logs = [];

    /**
     * This value is set to 500 EUR,
     * It is only a base to recalculation
     * If current currency is different that EUR i will be previously
     * recalculated on that currency
     * @var string
     */
    private $default_prepaid_value_in_EUR = "500.00";

    /**
     * @param bool $log_errors
     */
    public function __construct($log_errors = true)
    {
        $this->map_providers = $this->get_map_providers();

        $this->log_errors = $log_errors;

        $currencies = Helpers_Currency::getCurrencies();
        $kcurrencies = [];
        foreach ($currencies as $currency) {
            $kcurrencies[$currency['id']] = $currency['code'];
        }
        asort($kcurrencies);

        $this->kcurrencies = $kcurrencies;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    public function get_errors()
    {
        return $this->errors;
    }

    /**
     *
     * @param array $errors
     */
    public function set_errors($errors)
    {
        $this->errors = $errors;
    }

    /**
     *
     * @return bool
     */
    public function get_log_errors()
    {
        return $this->log_errors;
    }

    /**
     *
     * @param bool $log_errors
     */
    public function set_log_errors($log_errors)
    {
        $this->log_errors = $log_errors;
    }

    /**
     *
     * @return Presenter_Admin_Whitelabels_Add
     */
    public function get_inside(): Presenter_Admin_Whitelabels_Add
    {
        return $this->inside;
    }

    public function set($data): void
    {
        $this->data = $data;
    }

    public function disable_fuel(): void
    {
        $this->should_setup_fuel = false;
    }

    public function disable_wordpress(): void
    {
        $this->should_setup_wordpress = false;
    }


    private function setup_fuel()
    {
        if ($this->should_setup_fuel == false) {
            return;
        }

        $default_language_id = 1; // This value is equal ID of en_GB (english language in language table)
        $language_id = $this->data['language_id'] ?? $default_language_id;

        $site = $this->data['domain'];
        $name = $this->data['name'];
        $theme = $this->data['theme'];

        $whitelabel_set = [
            "language_id" => $language_id,
            "name" => $name,
            "email" => $this->data['email'],
            "realname" => $this->data['realname'],
            "domain" => $site,
            "company_details" => $this->data['company_details'],
            "margin" => $this->data['margin'],
            "type" => $this->data['type'],
            "user_activation_type" => Helpers_General::ACTIVATION_TYPE_OPTIONAL,
            "aff_activation_type" => Helpers_General::ACTIVATION_TYPE_OPTIONAL,
            "aff_auto_accept" => 1,
            "aff_payout_type" => 1,
            "aff_lead_auto_accept" => 1,
            "aff_enable_sign_ups" => 0,     // By default it will not be possible to see sign-up form for affs
            "max_payout" => 2500,
            "username" => $this->data['username'],
            "hash" => $this->data['hash'],
            "salt" => $this->data['salt'],
            "timezone" => "UTC",
            "prefix" => $this->data['prefix'],
            "manager_site_currency_id" => $this->data['manager_site_currency_id'],
            "max_order_count" => 20,
            'theme' => $theme,
            'us_state_active' => $this->data['us_state_active'],
            "enabled_us_states" => $this->data['enabled_us_states'],
            "is_report" => $this->data['is_report'] ?? 1,
            'support_email' => 'support@' . $site,
            'payment_email' => 'payments@' . $site,
        ];

        $new_whitelabel = Model_Whitelabel::forge();

        $new_whitelabel->set($whitelabel_set);

        if (!Helpers_Whitelabel::is_V1($new_whitelabel['type'])) {
            if ($this->data['prepaid'] != 0) {
                $whitelabel_prepaid_set = [
                    "prepaid" => $this->data['prepaid'],
                ];
                $new_whitelabel->set($whitelabel_prepaid_set);
            }

            // If user set alert limit different than zero
            if (($this->data['prepaid_alert_limit'] ?? 0) != 0) {
                $whitelabel_prepaid_alert_limit_set = [
                    "prepaid_alert_limit" => $this->data['prepaid_alert_limit'],
                ];
                $new_whitelabel->set($whitelabel_prepaid_alert_limit_set);
            }
        }

        $new_whitelabel->save();

        // Here add prepaid to whitelabel_prepaid table as primary income
        if (!Helpers_Whitelabel::is_V1($new_whitelabel['type']) &&
                            $this->data['prepaid'] != 0
        ) {
            $prepaid_set = [
                "whitelabel_id" => $new_whitelabel->id,
                "date" => DB::expr("NOW()"),
                "amount" => $this->data['prepaid']
            ];

            $new_whitelabel_prepaid = Model_Whitelabel_Prepaid::forge();
            $new_whitelabel_prepaid->set($prepaid_set);
            $new_whitelabel_prepaid->save();
        }

        $new_whitelabel_language = Model_Whitelabel_Language::forge();
        $new_whitelabel_language->set(array(
            "whitelabel_id" => $new_whitelabel->id,
            "language_id" => $language_id,
            "currency_id" => $this->data['site_currency_id'],
        ));
        $new_whitelabel_language->save();

        $set = [
            'whitelabel_id' => $new_whitelabel->id,
            'currency_id' => $this->data['site_currency_id'],
            'is_default_for_site' => 1,
            'default_deposit_first_box' => 20.00,
            'default_deposit_second_box' => 50.00,
            'default_deposit_third_box' => 100.00,
            'min_purchase_amount' => 0.00,
            'min_deposit_amount' => 0.00,
            'min_withdrawal' => 10.00,
            'max_order_amount' => 1000.00,
            'max_deposit_amount' => 1000.00
        ];

        $whitelabel_default_currency = Model_Whitelabel_Default_Currency::forge();

        $whitelabel_default_currency->set($set);
        $whitelabel_default_currency->save();


        $withdrawal_methods_uri_list = Helpers_Withdrawal_Method::get_methods_URI();
        foreach ($withdrawal_methods_uri_list as $withdrawal_id => $uri) {
            // TODO: extract affiliate withdrawals into another table
            if ((int)$withdrawal_id >= Helpers_Withdrawal_Method::WITHDRAWAL_DEBIT_CARD) {
                continue;
            }

            $withdrawal_set = [
                "whitelabel_id" => $new_whitelabel->id,
                "withdrawal_id" => $withdrawal_id
            ];
            $withdrawal_aff_model = Model_Whitelabel_Aff_Withdrawal::forge();
            $withdrawal_aff_model->set($withdrawal_set);
            $withdrawal_aff_model->save();
        }

        // populate whitelabel_settings
        $new_whitelabel_settings = array(
            "captcha_maxreq" => 1000,
            "captcha_timeout" => 600,
            "captcha_curreq" => 0,
            "captcha_curtime" => DB::expr("NOW()")
        );

        foreach ($new_whitelabel_settings as $key => $item) {
            $setting = Model_Whitelabel_Setting::forge();
            $setting->set(array(
                "whitelabel_id" => $new_whitelabel->id,
                "name" => $key,
                "value" => $item
            ));
            $setting->save();
        }

        // populate whitelabel_lottery
        $lotteries = Model_Lottery::get_all_lotteries();

        foreach ($lotteries['__by_id'] as $lottery) {
            $new_whitelabel_lottery = Model_Whitelabel_Lottery::forge();
            $lottery_provider_id = null;      // In the case that this value will be left as null
            // the wl_lottery will not be saved

            if (isset($lottery['id']) && isset($this->map_providers) &&
                !empty($this->map_providers[$lottery['id']])
            ) {
                $lottery_provider_id = $this->map_providers[$lottery['id']];

                $new_whitelabel_lottery->set(array(
                    "whitelabel_id" => $new_whitelabel->id,
                    "lottery_id" => $lottery['id'],
                    "lottery_provider_id" => $lottery_provider_id,
                    "is_enabled" => 1
                ));
                $new_whitelabel_lottery->save();
            }
        }

        Lotto_Helper::clear_cache(array('model_lottery', 'model_whitelabel', 'model_whitelabel_language'));
    }

    /**
     * This function cleans built-in wordpress content
     * We have to do it because this installation depends on texts ids
     * If we don't do it, we will get SQL integrity error
     */
    private function cleanSetUpContentTables(PDO $database, string $prefix): void
    {
        $tablesToTruncate = [
            $prefix . 'posts',
            $prefix . 'icl_translations',
            $prefix . 'terms',
            $prefix . 'term_taxonomy',
            $prefix . 'postmeta',
        ];
        $truncateQuery = '';
        foreach($tablesToTruncate as $table){
            $truncateQuery .= 'TRUNCATE ' . $table . ';';
        }

        $database->exec($truncateQuery);
    }

    private function setup_wordpress()
    {
        if ($this->should_setup_wordpress == false) {
            return;
        }

        $site = $this->data['domain'];
        $name = $this->data['name'];
        $theme = $this->data['theme'];

        // PREPARE STUFF FOR wp_cli
        // load wordpress information
        Config::load("translations", true);
        $wordpress_path = Config::get("translations.wp.path");
        $whitelotto_url = Config::get("translations.wp.url");

        // shared command-line appendix
        $wp_cli_addition = $global_wp_cli_addition = " --path=" . $wordpress_path . " --url=" . $whitelotto_url;

        // keep logs
        $this->logs = [];
        $wp_site_id = null;
        $theme_created = false;
        $prefix = null;
        // UNTIL HERE - wp_cli

        // COMPLICATED STUFF HERE
        /*
        * Now, let's start the complicated stuff, wordpress and WPML automatic set-up!
        * This is part #1 of the set-up, the second part covers adding more languages to whitelabel
        * The wp-cli has to be in the path for this to work
        */

        // First, let's create the WP site and get the new ID
        $command = "wp site create --porcelain --slug=" . $site . " --title=\"" . $name . "\"" . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command, $wp_site_id);

        // For this short period of time, our site is under http://{domain}.whitelotto.{TLD}
        $wp_cli_addition = " --path=" . $wordpress_path . " --url=http://" . $site . '.' . parse_url($whitelotto_url, PHP_URL_HOST);

        // get DB prefix for this site
        $prefix = "wp_" . $wp_site_id . "_";

        // update site URL

        $query = "UPDATE wp_blogs SET domain = '" . $site . "' WHERE blog_id = " . $wp_site_id;
        $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // Now we are changing the settings for different site!
        $wp_cli_addition = " --path=" . $wordpress_path . " --url=" . $site . "/";
        $wp_whitelotto_cli_addition = " --path=" . $wordpress_path . " --url=" . parse_url($whitelotto_url, PHP_URL_HOST) . "/";

        $command = 'wp option update siteurl "https://' . $site . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update home "https://' . $site . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // flush the cache, just to be sure the URL is reloaded
        $command = 'wp transient delete --all' . $global_wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp cache flush' . $global_wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // Remove default comments (two-step command)
        // could be done in one step, but only on linux
        $remove = null;
        $command = 'wp comment list --format=ids ' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command, $remove);

        $command = 'wp comment delete --force ' . $remove . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // Remove default pages
        $remove = null;
        $command = 'wp post list --post_type="page" --format=ids ' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command, $remove);

        $command = 'wp post delete --force ' . $remove . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // Remove default posts
        $remove = null;
        $command = 'wp post list --post_type="post" --format=ids ' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command, $remove);

        $command = 'wp post delete --force ' . $remove . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        /*                 * * Adjust all the options *** */

        // 6 news per page
        $command = 'wp option update posts_per_page 6' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // set up the most strict comments settings
        $command = 'wp option update comment_registration 1' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update close_comments_for_old_posts 1' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update comment_moderation 1' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // disable ping
        $command = 'wp option update default_ping_status "closed"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update default_comment_status "closed"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // disable avatars
        $command = 'wp option update show_avatars 0' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // set up permalinks
        $command = 'wp option update permalink_structure "/%postname%/"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // set up images
        $command = 'wp option update thumbnail_size_w 300' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update thumbnail_size_h 300' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update thumbnail_crop 0' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update medium_size_w 495' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update medium_size_h 495' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // remove automatic ping sites
        $command = 'wp option update ping_sites ""' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // disable yearmonth media folder structure
        $command = 'wp option update uploads_use_yearmonth_folders 0' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // update some languages (there is a lack of consequence in the WPML sometimes)
        // the rest will be updated while adding them
        $query = "UPDATE " . $prefix . "icl_languages SET default_locale = 'en_GB', active = 1 WHERE default_locale = 'en_US'";
        $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $query = "INSERT INTO " . $prefix . "icl_locale_map SET code = 'en', locale = 'en_GB'";
        $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // set up WPML String Translations
        // taken from WPML_ST_Upgrade_MO_Scanning
        // may need an update after WPML update
        // current version: 2.7.3
        // TODO: get these variables directly from the plugin file (?)
        $sql = "
                CREATE TABLE PREFIXicl_mo_files_domains ( 
                    id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                    file_path varchar(250) NOT NULL,
                    file_path_md5 varchar(32) NOT NULL,
                    domain varchar(45) NOT NULL,
                    status varchar(20) NOT NULL DEFAULT %s,
                    num_of_strings int(11) NOT NULL DEFAULT '0',
                    last_modified int(11) NOT NULL,
                    component_type enum('plugin','theme','other') NOT NULL DEFAULT 'other',
                    component_id varchar(100) DEFAULT NULL,
                    UNIQUE KEY file_path_md5_UNIQUE (file_path_md5)
                )";
        $sql = str_replace("PREFIX", $prefix, $sql);
        $sql = str_replace("%s", "'not_imported'", $sql);

        $sql = preg_replace('/\r?\n/', '', $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);

        $command = 'wp db query "' . $sql . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // taken from WPML_ST_Upgrade_Db_Cache_Command
        $icl_string_pages_sql_prototype = '
                CREATE TABLE IF NOT EXISTS %sicl_string_pages (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    string_id bigint(20) NOT NULL,
                    url_id bigint(20) NOT NULL,
                    PRIMARY KEY (id),
                    KEY string_to_url_id (url_id)
                    )
            ';
        $icl_string_pages_sql_prototype = sprintf($icl_string_pages_sql_prototype, $prefix);

        $icl_string_pages_sql_prototype = preg_replace('/\r?\n/', '', $icl_string_pages_sql_prototype);
        $icl_string_pages_sql_prototype = preg_replace('/\s+/', ' ', $icl_string_pages_sql_prototype);

        $command = 'wp db query "' . $icl_string_pages_sql_prototype . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $icl_string_urls_sql_prototype = '
                CREATE TABLE IF NOT EXISTS %sicl_string_urls (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    language varchar(7) %s DEFAULT NULL,
                    url varchar(255) DEFAULT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY string_string_lang_url (language,url(191))
                )
            ';
        $icl_string_urls_sql_prototype = sprintf($icl_string_urls_sql_prototype, $prefix, 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $icl_string_urls_sql_prototype = preg_replace('/\r?\n/', '', $icl_string_urls_sql_prototype);
        $icl_string_urls_sql_prototype = preg_replace('/\s+/', ' ', $icl_string_urls_sql_prototype);

        $command = 'wp db query "' . $icl_string_urls_sql_prototype . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // change language negotiation type
        // change auto adjust ids
        // change theme localization type
        $settings = null;
        $command = 'wp option get icl_sitepress_settings --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;

        Lotto_Helper::execute_CLI($command, $settings, true);

        $settings = json_decode($settings, true);
        $settings['existing_content_language_verified'] = true;
        $settings['language_negotiation_type'] = 1;
        $settings['auto_adjust_ids'] = 0;
        $settings['theme_localization_type'] = 2;
        $settings['languages_order'] = ['en'];
        $settings['default_language'] = 'en';
        $settings['setup_wizard_step'] = 4;
        $settings['custom_posts_sync_option'] = [
            'custom_css' => '0',
            'customize_changeset' => '0',
            'wpcf7_contact_form' => '0',
            'faq' => '1',
            'slider' => '1',
            'winners' => '1',
            'post' => 1,
            'page' => 1,
            'attachment' => 1,
            'wp_block' => 1
        ];
        $settings['site_does_not_use_icl'] = true;
        $settings['language_per_domain_sso_enabled'] = false;
        $settings['taxonomies_sync_option'] = [
            'faq-category' => '1',
            'category' => 1,
            'post_tag' => 1,
            'translation_priority' => 1
        ];
        // string translation set up
        $settings['st']['WPML_ST_Upgrade_Migrate_Originals_has_run'] = true;
        $settings['st']['WPML_ST_Upgrade_Display_Strings_Scan_Notices_has_run'] = true;
        $settings['st']['WPML_ST_Upgrade_DB_String_Name_Index_2_has_run'] = true;
        $settings['st']['WPML_ST_Upgrade_DB_Longtext_String_Value_has_run'] = true;
        $settings['st']['strings_per_page'] = 10;
        $settings['st']['icl_st_auto_reg'] = 'disable';
        $settings['st']['strings_language'] = 'en';
        $settings['st']['translated-users'] = [];

        // others
        $settings['taxonomy_names_checked'] = true;
        $settings['ajx_health_checked'] = true;
        $settings['language_selector_initialized'] = 1;
        $settings['default_categories'] = [
            'en' => '1'
        ];
        $settings['dont_show_help_admin_notice'] = true;

        $settings['setup_complete'] = 1;

        // new batch of options:
        // added on 13th of January 2021

        $settings['translation-management'] = [
            'custom_fields_translation' =>
                [
                    'title' => 2,
                    'description' => 2,
                    'keywords' => 2,
                    '_edit_last' => 0,
                    '_edit_lock' => 0,
                    '_wp_page_template' => 0,
                    '_wp_attachment_metadata' => 0,
                    'original_post_id' => 0,
                    '_wpml_original_post_id' => 0,
                    '_wp_old_slug' => 0,
                    '_icl_translator_note' => 0,
                    '_alp_processed' => 0,
                    '_pingme' => 0,
                    '_encloseme' => 0,
                    '_icl_lang_duplicate_of' => 0,
                    '_thumbnail_id' => 0,
                    '_wp_attached_file' => 0,
                    '_wp_attachment_image_alt' => 2,
                ],
            'custom_fields_readonly_config' => [
                0 => 'title',
                1 => 'description',
                2 => 'keywords',
                3 => '_edit_last',
                4 => '_edit_lock',
                5 => '_wp_page_template',
                6 => '_wp_attachment_metadata',
                7 => 'original_post_id',
                8 => '_wpml_original_post_id',
                9 => '_wp_old_slug',
                10 => '_icl_translator_note',
                11 => '_alp_processed',
                12 => '_pingme',
                13 => '_encloseme',
                14 => '_icl_lang_duplicate_of',
                15 => '_thumbnail_id',
                16 => '_wp_attached_file',
                17 => '_wp_attachment_image_alt',
            ],
            'custom_fields_translation_custom' => [],
            'custom_fields_translation_custom_readonly' => [],
            'doc_translation_method' => 0,
            '__custom_types_readonly_config_prev' => [],
            'custom_types_readonly_config' => [],
            '__custom_fields_readonly_config_prev' => [
                0 => 'title',
                1 => 'description',
                2 => 'keywords',
                3 => '_edit_last',
                4 => '_edit_lock',
                5 => '_wp_page_template',
                6 => '_wp_attachment_metadata',
                7 => 'original_post_id',
                8 => '_wpml_original_post_id',
                9 => '_wp_old_slug',
                10 => '_icl_translator_note',
                11 => '_alp_processed',
                12 => '_pingme',
                13 => '_encloseme',
                14 => '_icl_lang_duplicate_of',
                15 => '_thumbnail_id',
                16 => '_wp_attached_file',
                17 => '_wp_attachment_image_alt',
            ],
            '__custom_term_fields_readonly_config_prev' => [],
            'custom_term_fields_readonly_config' => [],
            'taxonomies_readonly_config' => [
                'translation_priority' => 1,
            ],
            'custom-types_readonly_config' => [
              'attachment' => 1,
              'wp_block' => 1,
            ],
        ];
        $settings['custom-types_sync_option'] = [
            'attachment' => 1,
            'wp_block' => 1,
        ];

        $settings = json_encode($settings);

        $command = 'wp option update icl_sitepress_settings "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // SET UP ALL BASIC WIDGETS
        // first set up widgets
        $settings = json_encode(unserialize('a:2:{i:2;a:3:{s:8:"settings";N;s:4:"type";i:1;s:5:"title";b:0;}s:12:"_multiwidget";i:1;}'));

        $command = 'wp option update widget_lotto_platform_widget_featured "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $settings = json_encode(unserialize('a:3:{i:2;a:4:{s:5:"title";b:0;s:4:"type";i:1;s:9:"countdown";i:2;s:7:"display";i:2;}i:3;a:4:{s:5:"title";b:0;s:4:"type";i:2;s:9:"countdown";i:2;s:7:"display";i:2;}s:12:"_multiwidget";i:1;}'));

        $command = 'wp option update widget_lotto_platform_widget_list "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $settings = json_encode(unserialize('a:2:{i:2;a:3:{s:5:"title";b:0;s:6:"number";i:6;s:7:"columns";i:2;}s:12:"_multiwidget";i:1;}'));

        $command = 'wp option update widget_lotto_platform_widget_news "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $settings = json_encode(unserialize('a:2:{i:2;a:2:{s:5:"title";b:0;s:5:"width";i:50;}s:12:"_multiwidget";i:1;}'));

        $command = 'wp option update widget_lotto_platform_widget_small_results "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $settings = json_encode(unserialize('a:2:{i:2;a:5:{s:5:"title";b:0;s:8:"settings";a:1:{s:5:"order";i:1;}s:5:"width";i:50;s:6:"amount";i:5;s:4:"type";i:2;}s:12:"_multiwidget";i:1;}'));

        $command = 'wp option update widget_lotto_platform_widget_small_winners "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $settings = json_encode(unserialize('a:4:{i:2;a:4:{s:7:"content";s:0:"";s:7:"lottery";i:1;s:5:"width";i:100;s:5:"title";b:0;}i:3;a:4:{s:7:"content";s:0:"";s:7:"lottery";i:1;s:5:"width";i:100;s:5:"title";b:0;}i:4;a:4:{s:7:"content";s:0:"";s:7:"lottery";i:1;s:5:"width";i:100;s:5:"title";b:0;}s:12:"_multiwidget";i:1;}'));

        $command = 'wp option update widget_lotto_platform_widget_small_lottery_draw "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);


        // flush the cache, just to be sure the widgets are reloaded
        $command = 'wp transient delete --all' . $global_wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp cache flush' . $global_wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // now put them in the correct areas

        $settings = null;
        $command = 'wp option get sidebars_widgets --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;

        Lotto_Helper::execute_CLI($command, $settings, true);

        $settings = json_decode($settings, true);

        $settings['frontpage-sidebar-id'] = [
            'lotto_platform_widget_featured-2',
            'lotto_platform_widget_list-2',
            'lotto_platform_widget_small_winners-2',
            'lotto_platform_widget_small_results-2',
            'lotto_platform_widget_news-2'
        ];

        $settings['play-sidebar-id'] = [
            'lotto_platform_widget_list-3'
        ];

        $settings['lottery-results-content-sidebar-id'] = [
            'lotto_platform_widget_small_lottery_draw-2'
        ];

        $settings['lottery-info-content-sidebar-id'] = [
            'lotto_platform_widget_small_lottery_draw-3'
        ];

        $settings['lottery-news-content-sidebar-id'] = [
            'lotto_platform_widget_small_lottery_draw-4'
        ];

        $settings = serialize($settings);

        // wp option update does not work here, we need to perform low-level query
        $dsn = 'mysql:dbname=' . Config::get("translations.wp.dbname") . ';host=' . Config::get("translations.wp.dbhost") . ';charset=utf8mb4';
        $user = Config::get("translations.wp.dbuser");
        $password = Config::get("translations.wp.dbpassword");

        /** @var PDO $wp_platform local database */
        $wp_platform = new PDO($dsn, $user, $password);

        $this->logs[] = 'Truncating tables after clean wordpress install.';
        $this->cleanSetUpContentTables($wp_platform, $prefix);
        $this->logs[] = 'Tables have been truncated.';

        $query = "UPDATE " . $prefix . "options SET option_value = \"" . addslashes($settings) . "\" WHERE option_name = 'sidebars_widgets'";
        $this->logs[] = "Executing query: " . $query;
        $status_query = $wp_platform->query($query);

        // create icl_string_packages table
        $sql = "
                CREATE TABLE PREFIXicl_string_packages (
                    ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    kind_slug varchar(160) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                    kind varchar(160) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                    name varchar(160) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                    title varchar(160) COLLATE utf8mb4_unicode_520_ci NOT NULL,
                    edit_link text COLLATE utf8mb4_unicode_520_ci NOT NULL,
                    view_link text COLLATE utf8mb4_unicode_520_ci NOT NULL,
                    post_id int(11) DEFAULT NULL,
                    word_count varchar(2000) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
                    PRIMARY KEY (ID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
            ";

        $sql = str_replace("PREFIX", $prefix, $sql);

        $this->logs[] = "Executing query: " . $command;
        $status_query = $wp_platform->query($sql);

        if (!$status_query) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        // now create the connection to the translations database
        // and import pages
        $dsn = 'mysql:dbname=' . Config::get("translations.wp_translations.dbname") . ';host=' . Config::get("translations.wp_translations.dbhost") . ';charset=utf8mb4';
        $user = Config::get("translations.wp_translations.dbuser");
        $password = Config::get("translations.wp_translations.dbpassword");

        /** @var ?PDO $wp_translations remote texts. database */
        $wp_translations = null;

        try {
            $wp_translations = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    $e->getMessage()
                );
            }
            return;
        }

        $query = "SELECT wp_2_posts.*, wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id
            WHERE language_code = 'en'
                AND element_type = 'post_page'
                AND post_type = 'page'
                AND post_status = 'publish'
                ORDER BY `post_parent` ASC";
        $this->logs[] = "Executing query: " . $query;
        $pages = $wp_translations->query($query);

        if (!$pages) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        $parents = [];
        $home = null;
        $news = null;
        $contact_form = null;

        // replace LottoPark with the name of the whitelabel!
        // The last item is a hax to fix broken URL for review apps
        $name_lowercased = mb_strtolower($name);
        $search = [
            "http://texts.lottopark.work",
            "https://texts.lottopark.work",
            "http://lottopark.com",
            "http://lottopark.work",
            "http://lottopark.loc",
            "https://lottopark.com",
            "https://lottopark.work",
            "https://lottopark.loc",
            "texts.lottopark.work",
            //"lottopark.work",
            "lottopark.com",
            "LottoPark",
            "lottopark",
            $name_lowercased . '.work'
        ];

        $site_url = "https://".$site;
        $replace = [
            $site_url,
            $site_url,
            $site_url,
            $site_url,
            $site_url,
            $site_url,
            $site_url,
            $site_url,
            parse_url($site_url, PHP_URL_HOST),
            //parse_url($site_url, PHP_URL_HOST),
            parse_url($site_url, PHP_URL_HOST),
            $name,
            $name_lowercased,
            "lottopark.work"
        ];

        foreach ($pages as $page) {
            $query = "INSERT INTO " . $prefix . "posts
                SET
                    post_author = ?,
                    post_date = NOW(),
                    post_date_gmt = NOW(),
                    post_content = ?,
                    post_status = 'publish',
                    comment_status = 'closed',
                    ping_status = 'closed',
                    post_name = ?,
                    post_title = ?,
                    post_modified = NOW(),
                    post_modified_gmt = NOW(),
                    post_parent = ?,
                    guid = ?,
                    menu_order = 0,
                    post_type = 'page',
                    comment_count = 0,
                    post_excerpt = '',
                    to_ping = '',
                    pinged = '',
                    post_content_filtered = ''";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $parent = 0;
            if (isset($parents[$page['post_parent']])) {
                $parent = $parents[$page['post_parent']];
            }

            $page['post_name'] = str_replace($search, $replace, $page['post_name']);
            $page['post_content'] = str_replace($search, $replace, $page['post_content']);
            $page['post_title'] = str_replace($search, $replace, $page['post_title']);
            $page['guid'] = str_replace($search, $replace, $page['guid']);

            $status_query = $sth->execute([1, $page['post_content'], $page['post_name'], $page['post_title'], $parent, $page['guid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $id = $wp_platform->lastInsertId();
            $parents[$page['ID']] = $id;

            if ($page['post_name'] == "home") {
                $home = $id;
            }

            if ($page['post_name'] == "news") {
                $news = $id;
            }

            if ($page['post_name'] == "form") {
                $contact_form = $id;
            }

            $query = "INSERT INTO " . $prefix . "icl_translations
                SET
                    element_type = 'post_page',
                    element_id = ?,
                    trid = ?,
                    language_code = 'en'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $page['trid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }
        }

        if ($home === null || $news === null) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "No home or news page."
                );
            }
            throw new Exception("No home or news page");
        }
        if ($contact_form === null) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "No contact form page."
                );
            }
            throw new Exception("No contact form page");
        }

        // TODO: Check if all required articles are present
        // set up home & news pages
        $command = 'wp option update show_on_front "page"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update page_on_front ' . $home . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp option update page_for_posts ' . $news . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);


        // now, main categories
        $query = "SELECT
                wp_2_terms.*,
                wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id
            WHERE language_code = 'en'
                AND element_type = 'tax_category'";
        $this->logs[] = "Executing query: " . $query;
        $taxonomies = $wp_translations->query($query);

        if (!$taxonomies) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        foreach ($taxonomies as $taxonomy) {
            // do not import Uncategorized (at least for english)
            if ($taxonomy['trid'] == 2) {
                continue;
            }
            $query = "INSERT INTO " . $prefix . "terms SET name = ?, slug = ?, term_group = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $taxonomy['name'] = str_replace($search, $replace, $taxonomy['name']);
            $taxonomy['slug'] = str_replace($search, $replace, $taxonomy['slug']);

            $status_query = $sth->execute([$taxonomy['name'], $taxonomy['slug']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $id = $wp_platform->lastInsertId();

            $query = "INSERT INTO " . $prefix . "term_taxonomy
                SET
                    term_id = ?,
                    taxonomy = ?,
                    description = '',
                    parent = 0,
                    count = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $status_query = $sth->execute([$id, 'category']);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $query = "INSERT INTO " . $prefix . "icl_translations
                SET
                    element_type = 'tax_category',
                    element_id = ?,
                    trid = ?,
                    language_code = 'en'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $taxonomy['trid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }
        }
        // TODO: check if all lottery slugs are present
        // now, FAQ categories
        $query = "SELECT
                wp_2_terms.*,
                wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id
            WHERE language_code = 'en'
                AND element_type = 'tax_faq-category'";
        $this->logs[] = "Executing query: " . $query;
        $faqtaxonomies = $wp_translations->query($query);

        if (!$faqtaxonomies) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        $taxonomy_map = [];

        foreach ($faqtaxonomies as $faqtaxonomy) {
            $query = "INSERT INTO " . $prefix . "terms SET name = ?, slug = ?, term_group = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $faqtaxonomy['name'] = str_replace($search, $replace, $faqtaxonomy['name']);
            $faqtaxonomy['slug'] = str_replace($search, $replace, $faqtaxonomy['slug']);

            $status_query = $sth->execute([$faqtaxonomy['name'], $faqtaxonomy['slug']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $id = $wp_platform->lastInsertId();

            $taxonomy_map[$faqtaxonomy['term_id']] = $id;

            $query = "INSERT INTO " . $prefix . "term_taxonomy
                SET
                    term_id = ?,
                    taxonomy = ?,
                    description = '',
                    parent = 0,
                    count = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $status_query = $sth->execute([$id, 'faq-category']);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            try {
                $query = "INSERT INTO {$prefix}icl_translations SET
                    element_type = 'tax_faq-category',
                    element_id = ?,
                    trid = ?,
                    language_code = 'en'";
                $this->logs[] = "Executing query: $query";

                $sth = $wp_platform->prepare($query);
                $sth->execute([$id, $faqtaxonomy['trid']]);
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->fileLoggerService->error(
                    "There is a problem with executing query: $query | Message: $message"
                );
            }
        }

        // get relationships
        // and connect them
        $query = "SELECT * FROM wp_2_term_relationships";
        $this->logs[] = "Executing query: " . $query;
        $relations = $wp_translations->query($query);

        if (!$relations) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        $relation_map = [];
        foreach ($relations as $relation) {
            $relation_map[$relation['object_id']] = $relation['term_taxonomy_id'];
        }

        // FAQs
        $query = "SELECT
                wp_2_posts.*,
                wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id
            WHERE language_code = 'en'
                AND element_type = 'post_faq'";
        $this->logs[] = "Executing query: " . $query;
        $faqs = $wp_translations->query($query);

        if (!$faqs) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        foreach ($faqs as $faq) {
            $query = "INSERT INTO " . $prefix . "posts
                SET
                    post_author = ?,
                    post_date = NOW(),
                    post_date_gmt = NOW(),
                    post_content = ?,
                    post_status = 'publish',
                    comment_status = 'closed',
                    ping_status = 'closed',
                    post_name = ?,
                    post_title = ?,
                    post_modified = NOW(),
                    post_modified_gmt = NOW(),
                    post_parent = ?,
                    guid = ?,
                    menu_order = 0,
                    post_type = 'faq',
                    comment_count = 0,
                    post_excerpt = '',
                    to_ping = '',
                    pinged = '',
                    post_content_filtered = ''";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $faq['post_name'] = str_replace($search, $replace, $faq['post_name']);
            $faq['post_content'] = str_replace($search, $replace, $faq['post_content']);
            $faq['post_title'] = str_replace($search, $replace, $faq['post_title']);
            $faq['guid'] = str_replace($search, $replace, $faq['guid']);

            // by default, posts of type 'faq' do not have a 'post_parent' set and are treated as independent entries
            // therefore, we set the 'post_parent' to 0
            $faqParentId = 0;
            $status_query = $sth->execute([1, $faq['post_content'], $faq['post_name'], $faq['post_title'], $faqParentId, $faq['guid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $id = $wp_platform->lastInsertId();
            $parents[$faq['ID']] = $id; // update this array

            $taxonomy_id = $taxonomy_map[$relation_map[$faq['ID']]];

            // connect the faq with faq category
            $query = "INSERT INTO " . $prefix . "term_relationships
                SET
                    object_id = ?,
                    term_taxonomy_id = ?,
                    term_order = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $taxonomy_id]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $query = "INSERT INTO " . $prefix . "icl_translations
                SET
                    element_type = 'post_faq',
                    element_id = ?,
                    trid = ?,
                    language_code = 'en'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $faq['trid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }
        }

        // Contact Form
        $query = "SELECT
                wp_2_posts.*,
                wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id
            WHERE language_code = 'en'
                AND post_type = 'wpcf7_contact_form'";
        $this->logs[] = "Executing query: " . $query;
        $contact = $wp_translations->query($query);

        if (!$contact) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        if (empty($contact)) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "Cannot find contact form."
                );
            }
            throw new Exception("Cannot find contact form");
        }

        $contact = $contact->fetch();

        $query = "INSERT INTO " . $prefix . "posts
            SET
                post_author = ?,
                post_date = NOW(),
                post_date_gmt = NOW(),
                post_content = ?,
                post_status = 'publish',
                comment_status = 'closed',
                ping_status = 'closed',
                post_name = ?,
                post_title = ?,
                post_modified = NOW(),
                post_modified_gmt = NOW(),
                post_parent = 0,
                guid = ?,
                menu_order = 0,
                post_type = 'wpcf7_contact_form',
                comment_count = 0,
                post_excerpt = '',
                to_ping = '',
                pinged = '',
                post_content_filtered = ''";
        $this->logs[] = "Executing query: " . $query;
        $sth = $wp_platform->prepare($query);

        $contact['post_content'] = str_replace($search, $replace, $contact['post_content']);
        $contact['guid'] = str_replace($search, $replace, $contact['guid']);

        $status_query = $sth->execute([1, $contact['post_content'], $contact['post_name'], $contact['post_title'], $contact['guid']]);

        if (!$status_query) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        $id = $wp_platform->lastInsertId();

        // contact form meta
        $query = "SELECT *
            FROM wp_2_postmeta
            WHERE post_id = " . $contact['ID'] . "
                AND meta_key IN ('_form', '_mail', '_messages', '_additional_settings', '_locale')";
        $this->logs[] = "Executing query: " . $query;
        $cmeta = $wp_translations->query($query);

        if (!$cmeta) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        foreach ($cmeta as $meta) {
            $query = "INSERT INTO " . $prefix . "postmeta
                SET
                    post_id = ?,
                    meta_key = ?,
                    meta_value = ?";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            if ($meta['meta_key'] !== '_mail') {
                $meta['meta_value'] = str_replace($search, $replace, $meta['meta_value']);
            } else {
                $tmp = unserialize($meta['meta_value']);
                foreach ($tmp as $key => $item) {
                    $tmp[$key] = str_replace($search, $replace, $item);
                }
                $meta['meta_value'] = serialize($tmp);
            }

            $status_query = $sth->execute([$id, $meta['meta_key'], $meta['meta_value']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }
        }

        $query = "UPDATE " . $prefix . "posts SET post_content = ? WHERE id = ?";
        $this->logs[] = "Executing query: " . $query;
        $sth = $wp_platform->prepare($query);

        $status_query = $sth->execute(['[contact-form-7 id="' . $id . '" title="' . $contact['post_title'] . '"]', $contact_form]);

        if (!$status_query) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        // the last thing: menus! not so easy though
        // first, create menus (taxonomies)
        $query = "SELECT
                wp_2_terms.*,
                wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id
            WHERE language_code = 'en'
                AND element_type = 'tax_nav_menu'";
        $this->logs[] = "Executing query: " . $query;
        $menus = $wp_translations->query($query);

        if (!$menus) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        $menus_map = [];

        $menu_primary = null;
        $menu_footer = null;

        $numberOfItemsInMenu = [];
        foreach ($menus as $menu) {
            $query = "INSERT INTO " . $prefix . "terms
                SET
                    name = ?,
                    slug = ?,
                    term_group = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $status_query = $sth->execute([$menu['name'], $menu['slug']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $id = $wp_platform->lastInsertId();

            if ($menu['slug'] == 'primary-menu') {
                $menu_primary = $id;
            }
            if ($menu['slug'] == 'footer-menu') {
                $menu_footer = $id;
            }

            $menus_map[$menu['term_id']] = $id;

            $query = "INSERT INTO " . $prefix . "term_taxonomy
                SET
                    term_id = ?,
                    taxonomy = ?,
                    description = '',
                    parent = 0,
                    count = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $status_query = $sth->execute([$id, 'nav_menu']);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $query = "INSERT INTO " . $prefix . "icl_translations
                SET
                    element_type = 'tax_nav_menu',
                    element_id = ?,
                    trid = ?,
                    language_code = 'en'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $menu['trid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }
        }

        // then create menu items (posts)
        $query = "SELECT
                wp_2_posts.*,
                wp_2_icl_translations.*
            FROM `wp_2_icl_translations`
            INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id
            WHERE language_code = 'en'
                AND element_type = 'post_nav_menu_item'
                AND post_status = 'publish'";
        $this->logs[] = "Executing query: " . $query;
        $items = $wp_translations->query($query);

        if (!$items) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        foreach ($items as $item) {
            $query = "INSERT INTO " . $prefix . "posts
                SET
                    post_author = ?,
                    post_date = NOW(),
                    post_date_gmt = NOW(),
                    post_content = ?,
                    post_status = 'publish',
                    comment_status = 'closed',
                    ping_status = 'closed',
                    post_name = ?,
                    post_title = ?,
                    post_modified = NOW(),
                    post_modified_gmt = NOW(),
                    post_parent = ?,
                    guid = ?,
                    menu_order = ?,
                    post_type = 'nav_menu_item',
                    comment_count = 0,
                    post_excerpt = '',
                    to_ping = '',
                    pinged = '',
                    post_content_filtered = ''";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $item['post_name'] = str_replace($search, $replace, $item['post_name']);
            $item['post_content'] = str_replace($search, $replace, $item['post_content']);
            $item['post_title'] = str_replace($search, $replace, $item['post_title']);
            $item['guid'] = str_replace($search, $replace, $item['guid']);

            $status_query = $sth->execute([1, $item['post_content'], $item['post_name'], $item['post_title'], $parent, $item['guid'], $item['menu_order']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $id = $wp_platform->lastInsertId();

            $taxonomy_id = null;

            if (empty($relation_map[$item['ID']])) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "DEBUG: Empty relation_map for item: ". json_encode($item)
                    );
                }
            }

            $taxonomy_key = $relation_map[$item['ID']];

            if (empty($menus_map[$taxonomy_key])) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "DEBUG: Empty menus_map for key: ". $taxonomy_key
                    );
                }
            }

            $taxonomy_id = $menus_map[$taxonomy_key];

            $query = "INSERT INTO " . $prefix . "term_relationships
                SET
                    object_id = ?,
                    term_taxonomy_id = ?,
                    term_order = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $taxonomy_id]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $query = "INSERT INTO " . $prefix . "icl_translations
                SET
                    element_type = 'post_nav_menu_item',
                    element_id = ?,
                    trid = ?,
                    language_code = 'en'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $status_query = $sth->execute([$id, $item['trid']]);

            if (!$status_query) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            // now menu item meta
            $query = "SELECT *
                FROM wp_2_postmeta
                WHERE post_id = " . $item['ID'];
            $this->logs[] = "Executing query: " . $query;
            $cmeta = $wp_translations->query($query);

            if (!$cmeta) {
                if ($this->get_log_errors()) {
                    $this->fileLoggerService->error(
                        "There is a problem with executing query: " . $query
                    );
                }
            }

            $cmeta = $cmeta->fetchAll();

            $kmeta = [];
            foreach ($cmeta as $meta) {
                $kmeta[$meta['meta_key']] = $meta['meta_value'];
            }
            foreach ($cmeta as $meta) {
                $query = "INSERT INTO " . $prefix . "postmeta
                    SET
                        post_id = ?,
                        meta_key = ?,
                        meta_value = ?";
                $this->logs[] = "Executing query: " . $query;
                $sth = $wp_platform->prepare($query);

                if ($meta['meta_key'] == '_menu_item_object_id' && $kmeta['_menu_item_type'] == 'post_type') {
                    $meta['meta_value'] = $parents[$meta['meta_value']];
                }
                if ($meta['meta_key'] == '_menu_item_object_id' && $kmeta['_menu_item_type'] == 'taxonomy' && $kmeta['_menu_item_object'] == 'faq-category') {
                    $meta['meta_value'] = $taxonomy_map[$meta['meta_value']];
                }
                $meta['meta_value'] = str_replace($search, $replace, $meta['meta_value']);

                $status_query = $sth->execute([$id, $meta['meta_key'], $meta['meta_value']]);

                if (!$status_query) {
                    if ($this->get_log_errors()) {
                        $this->fileLoggerService->error(
                            "There is a problem with executing query: " . $query
                        );
                    }
                }
            }

            // numberOfItemsInMenu[term_id from term_taxonomy table] = number of items on the menu
            if (isset($numberOfItemsInMenu[$taxonomy_id])) {
                $numberOfItemsInMenu[$taxonomy_id] += 1;
            } else {
                $numberOfItemsInMenu[$taxonomy_id] = 1;
            }
        }

        // fill count field for menu because we dont fill it anywhere before
        foreach ($numberOfItemsInMenu as $termId => $item) {
            $query = "UPDATE " . $prefix . "term_taxonomy SET count = ? WHERE term_id = ?";
            $this->logs[] = "Executing query: $query";
            $statement = $wp_platform->prepare($query);
            $result = $statement->execute([$numberOfItemsInMenu[$termId], $termId]);

            if (!$result && $this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: $query"
                );
            }
        }

        // set up menus in their places
        $settings = 'a:2:{s:18:"nav_menu_locations";a:2:{s:7:"primary";i:{primary};s:6:"footer";i:{footer};}s:18:"custom_css_post_id";i:-1;}';
        $settings = str_replace(["{primary}", "{footer}"], [$menu_primary, $menu_footer], $settings);
        $query = "insert into " . $prefix . "options
            SET
                option_value = '" . $settings . "', 
                option_name = 'theme_mods_" . $theme . "', 
                autoload = 'yes'";

        $this->logs[] = "Executing query: " . $query;
        $status_query = $wp_platform->query($query);

        if (!$status_query) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error(
                    "There is a problem with executing query: " . $query
                );
            }
        }

        // we're done!
        // resave permalinks
        $command = 'wp rewrite flush' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // Set theme mode base_deposit_button_place outside user area
        $command = "wp theme mod set base_deposit_button_place outside";
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // Activate the whitelabel theme, have to be done at the end
        $command = "wp theme enable " . $theme . " --activate" . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);
    }

    public function create_whitelabel()
    {
        $this->setup_fuel();
        $this->setup_wordpress();

        return $this->logs;
    }

    /**
     *
     * @return array
     */
    public function get_map_providers(): array
    {
        if (empty($this->map_providers)) {
            // only lottorisq now
            $providers = Model_Lottery_Provider::find([
                'where' => function ($query) {
                    $query->where("provider", Helpers_General::PROVIDER_LOTTORISQ)->or_where("provider", Helpers_General::PROVIDER_LOTTERY_CENTRAL_SERVER);
                }
            ]);

            $this->map_providers = [];
            foreach ($providers as $provider) {
                $this->map_providers[intval($provider['lottery_id'])] = intval($provider['id']);
            }
        }

        return $this->map_providers;
    }

    /**
     *
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $types = [
            (string) Helpers_General::WHITELABEL_TYPE_V1,
            (string) Helpers_General::WHITELABEL_TYPE_V2,
        ];

        $validation->add("input.name", _("Name"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("min_length", 3)
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ['alpha', 'numeric', 'dashes', 'spaces', 'dots']);

        $validation->add("input.themename", _("Theme name"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("min_length", 3)
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ['alpha', 'numeric']);

        $validation->add("input.domain", _("Domain"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_url");

        $validation->add("input.company", _("Company"))
            ->add_rule("trim");

        $validation->add("input.email", _("E-mail"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_email");

        $validation->add("input.realname", _("Real name"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validation->add("input.margin", _("Margin"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 100);

        $validation->add("input.type", _("Whitelabel Type"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("match_collection", $types, true);

        if (!Helpers_Whitelabel::is_V1(Input::post("input.type"))) {
            $validation->add("input.prepaid", _("Prepaid"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);

            $validation->add("input.prepaid_alert_limit", _("Prepaid alert limit"))
                ->add_rule("trim")
                ->add_rule("required")
                ->add_rule("is_numeric")
                ->add_rule("numeric_min", 0)
                ->add_rule("numeric_max", 9999999999999);
        }

        $validation->add("input.username", _("Username"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 30)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("input.password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 6);

        $validation->add("input.prefix", _("Prefix"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('exact_length', 2)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add("input.managercurrency", _("Manager site currency"))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric");

        $validation->add("input.sitecurrency", _("Default site currency"))
            ->add_rule("required")
            ->add_rule("trim")
            ->add_rule("is_numeric");

        $validation->add("input.us_state_active", _("Send US state information to L-Tech"))
            ->add_rule("trim");

        $validation->add("input.is_report", _("Should be cosidered in reports"))
            ->add_rule("trim");

        $validation->add("input.enabled_us_states", _("Enabled US States"));

        return $validation;
    }

    /**
     *
     * @return void
     * @throws Exception
     */
    public function process_form(): void
    {
        $this->inside = Presenter::forge("admin/whitelabels/add");
        $this->inside->set("currencies", $this->kcurrencies);
        $this->inside->set("us_states", $this->get_us_states());

        $manager_currency_id = 0;
        $manager_currency_tab = [];
        if (!is_null(Input::post("input.managercurrency"))) {
            $manager_currency_id = Input::post("input.managercurrency");
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $manager_currency_id
            );
        } else {
            $manager_currency_code = reset($this->kcurrencies);
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                $manager_currency_code
            );
        }

        $prepaid_currency_code = $manager_currency_tab['code'];
        $this->inside->set("prepaid_currency_code", $prepaid_currency_code);

        $prepaid_alert_limit_value = $this->default_prepaid_value_in_EUR;
        if (Input::post("input.prepaid_alert_limit") === null) {
            $default_system_currency_tab = Helpers_Currency::get_mtab_currency();
            if ((string)$default_system_currency_tab['code'] !== (string)$manager_currency_tab['code']) {
                $prepaid_alert_limit_value = Helpers_Currency::get_recalculated_to_given_currency(
                    $this->default_prepaid_value_in_EUR,
                    $default_system_currency_tab,
                    $manager_currency_tab['code']
                );
            }
        } else {
            $prepaid_alert_limit_value = Input::post("input.prepaid_alert_limit");
        }
        $this->inside->set("prepaid_alert_limit_value", $prepaid_alert_limit_value);

        $whitelabel_types = [
            Helpers_General::WHITELABEL_TYPE_V1 => _("Our payments (V1)"),
            Helpers_General::WHITELABEL_TYPE_V2 => _("Whitelabel payments (V2)"),
        ];
        $this->inside->set("whitelabel_types", $whitelabel_types);

        if (Input::post("input") === null) {
            return;
        }

        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $res = Model_Whitelabel::exist_domain_username_prefix(
                $validated_form->validated("input.domain"),
                $validated_form->validated("input.username"),
                $validated_form->validated("input.prefix")
            );

            if (is_null($res)) {
                $errors = ["input.domain" => _("There is something wrong with database!")];
                $this->inside->set("errors", $errors);
                return;
            }

            if ((int)$res[0]['count'] !== 0) {
                $errors = ["input.domain" => "Whitelabel with this domain, username or prefix already exists!"];
                $this->inside->set("error_whitelabel_exists", 1);
                $this->inside->set("errors", $errors);
                return;
            }

            if (!empty($validated_form->validated("input.managercurrency")) &&
                !in_array($validated_form->validated("input.managercurrency"), array_keys($this->kcurrencies))
            ) {
                $errors = ["input.managercurrency" => _("Incorrect currency.")];
                $this->inside->set("errors", $errors);
                return;
            }

            if (!empty($validated_form->validated("input.sitecurrency")) &&
                !in_array($validated_form->validated("input.sitecurrency"), array_keys($this->kcurrencies))
            ) {
                $errors = ["input.sitecurrency" => _("Incorrect currency.")];
                $this->inside->set("errors", $errors);
                return;
            }

            $manager_currency_id = $validated_form->validated("input.managercurrency");

            $us_state_active = ($validated_form->validated("input.us_state_active") == 1) ? 1 : 0;

            $this->logs = [];

            try {
                DB::start_transaction();

                $salt = Lotto_Security::generate_salt();
                $hash = Lotto_Security::generate_hash($validated_form->validated("input.password"), $salt);

                $is_report = ($validated_form->validated("input.is_report") == 1) ? 1 : 0;

                $site = parse_url($validated_form->validated("input.domain"), PHP_URL_HOST);
                $name = $validated_form->validated("input.name");
                $theme = $validated_form->validated("input.themename");

                $data = [
                    "name" => $name,
                    "email" => $validated_form->validated("input.email"),
                    "realname" => $validated_form->validated("input.realname"),
                    "domain" => $site,
                    "company_details" => $validated_form->validated("input.company"),
                    "margin" => $validated_form->validated('input.margin'),
                    "type" => $validated_form->validated("input.type"),
                    "username" => $validated_form->validated("input.username"),
                    "hash" => $hash,
                    "salt" => $salt,
                    "prefix" => $validated_form->validated("input.prefix"),
                    "manager_site_currency_id" => $manager_currency_id,
                    'theme' => $theme,
                    'us_state_active' => $us_state_active,
                    "enabled_us_states" => serialize($validated_form->validated("input.enabled_us_states")),
                    "is_report" => $is_report,
                    "prepaid" => $validated_form->validated("input.prepaid"),
                    "prepaid_alert" => $validated_form->validated("input.prepaid_alert_limit"),
                    "site_currency_id" => $validated_form->validated("input.sitecurrency"),
                ];

                $this->set($data);

                $this->logs = $this->create_whitelabel();

                Session::set_flash('message', array('success', array_merge(array(_("Whitelabel has been added!")), $this->logs)));

                DB::commit_transaction();

                Lotto_Helper::clear_cache('model_whitelabel.bydomain');
                Lotto_Helper::clear_cache('model_whitelabel_language.whitelabellanguages');

                Response::redirect('whitelabels');
            } catch (Exception $e) {
                DB::rollback_transaction();

                // wp needs to be cleared out manually

                $errors = [($e->getMessage()) . "<br>" . implode('<br>', $this->logs)];
                $this->set_errors($errors);
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
        }
    }
}
