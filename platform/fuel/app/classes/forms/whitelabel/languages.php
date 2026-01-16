<?php

use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

/**
 * Class for preparing Forms_Whitelabel_Languages form
 */
final class Forms_Whitelabel_Languages extends Forms_Main
{

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var string
     */
    private $subaction = "";

    /**
     *
     * @var array
     */
    private $errors = [];

    /**
     *
     * @var bool
     */
    private $log_errors = true;

    private $should_setup_fuel = true;
    private $should_setup_wordpress = true;

    private $logs = [];
    private FileLoggerService $fileLoggerService;

    /**
     * @param array $whitelabel
     * @param string $subaction
     * @param bool $log_errors Default true
     */
    public function __construct($whitelabel, $subaction = "", $log_errors = true)
    {
        $this->whitelabel = $whitelabel;
        $this->subaction = $subaction;
        $this->log_errors = $log_errors;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }


    public function disable_fuel(): void
    {
        $this->should_setup_fuel = false;
    }

    public function disable_wordpress(): void
    {
        $this->should_setup_wordpress = false;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     *
     * @return string
     */
    public function get_subaction()
    {
        return $this->subaction;
    }

    /**
     *
     * @param array $whitelabel
     */
    public function set_whitelabel($whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @param string $subaction
     */
    public function set_subaction($subaction)
    {
        $this->subaction = $subaction;
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("input.language", _("Language"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        return $validation;
    }

    private function setup_fuel($language)
    {
        if ($this->should_setup_fuel == false) {
            return;
        }

        $whitelabel = $this->whitelabel;

        $wlanguage = Model_Whitelabel_Language::find([
            "where" => [
                "whitelabel_id" => $whitelabel['id'],
                "language_id" => $language["id"]
            ]
        ]);

        if (!empty($wlanguage)) {
            throw new Exception("This language is already present in platform database!");
        }

        $default_currency_tab = Helpers_Currency::get_mtab_currency(false);
        $default_currency_id = $default_currency_tab['id'];

        // add new language
        $new_whitelabel_language = Model_Whitelabel_Language::forge();
        $new_whitelabel_language->set(array(
            "whitelabel_id" => $whitelabel['id'],
            "language_id" => $language['id'],
            "currency_id" => $default_currency_id
        ));
        $new_whitelabel_language->save();
    }

    private function updateLanguageCode(PDO $localDatabase, string $prefix, array $lang): void
    {
        $newCode = $lang[1];
        $defaultLanguageCode = $lang[5];
        $iclLanguagesTranslationsTable = $prefix . 'icl_languages_translations';
        $flagTable = $prefix . 'icl_flags';

        $this->logs[] = 'Detected language code change.';

        $mysqlQuery = "UPDATE $iclLanguagesTranslationsTable SET language_code = ? WHERE language_code = ?";
        $this->logs[] = 'Executing command: ' . $mysqlQuery;
        $pdoQuery = $localDatabase->prepare($mysqlQuery);
        $updatedLanguageCodeCount = $pdoQuery->execute([$newCode, $defaultLanguageCode]);

        $mysqlQuery = "UPDATE $iclLanguagesTranslationsTable SET display_language_code = ? WHERE display_language_code = ?";
        $this->logs[] = 'Executing command: ' . $mysqlQuery;
        $pdoQuery = $localDatabase->prepare($mysqlQuery);
        $updatedDisplayLanguageCount = $pdoQuery->execute([$newCode, $defaultLanguageCode]);

        $this->logs[] = 'Updating flag for new language.';
        $mysqlQuery = "UPDATE $flagTable SET lang_code = ? WHERE lang_code = ?";
        $this->logs[] = 'Executing command: ' . $mysqlQuery;
        $pdoQuery = $localDatabase->prepare($mysqlQuery);
        $updatedFlagCount = $pdoQuery->execute([$newCode, $defaultLanguageCode]);

        $updateDisplayLanguageResult = $updatedDisplayLanguageCount == 0 ? 'fail' : 'success';
        $updateLanguageCodeResult = $updatedLanguageCodeCount == 0 ? 'fail' : 'success';
        $updateFlagResult = $updatedFlagCount == 0 ? 'fail' : 'success';
        $languageHasNotBeenUpdatedProperly = $updatedDisplayLanguageCount == 0 || $updatedLanguageCodeCount == 0 || $updatedFlagCount == 0;
        if ($languageHasNotBeenUpdatedProperly) {
            throw new Exception(
                "Language $lang has not been updated properly for wordpress site with prefix $prefix.
                Display language: $updateDisplayLanguageResult,
                Language code: $updateLanguageCodeResult,
                Flag: $updateFlagResult
                ");
        }
    }

    private function setup_wordpress($language)
    {
        if ($this->should_setup_wordpress == false) {
            return;
        }

        $whitelabel = $this->whitelabel;

        // load wordpress information
        Config::load("translations", true);
        $wordpress_path = Config::get("translations.wp.path");

        $whitelabel_url = 'https://' . $whitelabel['domain'] . '/';
        $site = $whitelabel['domain'];
        $name = $whitelabel['name'];

        $new_language = $language;

        // shared command-line appendix
        $wp_cli_addition = " --no-color --path=" . $wordpress_path . " --url=" . $whitelabel_url;

        $prefix = null;
        $command = 'wp db prefix' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;

        Lotto_Helper::execute_CLI($command, $prefix, false);

        // map locales
        /*
         * Item #1: true, if language exists in Wordpress (wp_X_icl_languages)
         * Item #2: what code the language should have in database
         * Item #3: what default_locale the language should have in database
         * Item #4: what tag the language should have in database
         *
         * If first item is true, then:
         * Item #5: what default_locale the language has now
         *
         * If first item is false, then:
         * Item #5: what name should the new language have
         * 
         * Required only during language code change (we have to pass default wp language code here to be able to change it for new one)
         * example for pt_BR/zh_CN:
         * It changes codes in db from pt-br to pt and zh-hans to zh
         * Item #6: default language code in wp db
         */

        $wp_languages = [
            'en_GB' => [true, 'en', 'en_GB', 'en', 'en_US'],
            'ar_SA' => [true, 'ar', 'ar_SA', 'ar', 'ar'],
            'el_GR' => [true, 'el', 'el_GR', 'el', 'el'],
            'et_EE' => [true, 'et', 'et_ET', 'et', 'et'],
            'fa_IR' => [true, 'fa', 'fa_IR', 'fa', 'fa_IR'],
            'hi_IN' => [false, 'hi', 'hi_IN', 'hi', 'Hindi'],
            'hr_HR' => array(true, 'hr', 'hr_HR', 'hr', 'hr'),
            'sq_AL' => [false, 'sq', 'sq_AL', 'sq', 'Albanian'],
            'ka_GE' => [false, 'ge', 'ka_GE', 'ka', 'Georgian'],
            'pt_PT' => [true, 'pt-pt', 'pt_PT', 'pt-pt', 'pt_PT'],
            'pt_BR' => [true, 'pt', 'pt_BR', 'pt', 'pt_BR', 'pt-br'],
            'bn_BD' => [false, 'bn', 'bn_BD', 'bn', 'Bengali'],
            'zh_CN' => [true, 'zh', 'zh_CN', 'zh', 'zh_CN', 'zh-hans'],
            'az_AZ' => [false, 'az', 'az_AZ', 'az', 'Azerbaycan'],
            'fil_PH' => [false, 'fil', 'tl_PH', 'tl', 'Tagalog'], // @todo change it fil != tl && filipino != tagalog
            'nl_NL' => [true, 'nl', 'nl_NL', 'nl', 'nl'],
            'fi_FI' => [true, 'fi', 'fi_FI', 'fi', 'fi', 'fi'],
            'th_TH' => [true, 'th', 'th_TH', 'th', 'th', 'th'],
            'ja_JP' => [true, 'ja', 'ja_JP', 'ja', 'ja', 'ja'],
            'uk_UA' => [true, 'uk', 'uk_UA', 'uk', 'uk', 'uk'],
            'he_IL' => [true, 'he', 'he_IL', 'he', 'he', 'he'],
        ];

        $code = explode('_', $new_language['code']);
        $code = $code[0];

        // we need to adjust some values
        if (isset($wp_languages[$new_language['code']])) {
            $lang = $wp_languages[$new_language['code']];
            $code = $lang[1];
            if ($lang[0]) { // language exists
                $query = "UPDATE " . $prefix . "icl_languages SET code = '" . $lang[1] . "', default_locale = '" . $lang[2] . "', tag = '" . $lang[3] . "', active = 1 WHERE default_locale = '" . $lang[4] . "'";
                $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
                $this->logs[] = "Executing command: " . $command;
                $this->logs[] = Lotto_Helper::execute_CLI($command);
            } else {
                $query = "INSERT INTO " . $prefix . "icl_languages SET code = '" . $lang[1] . "', default_locale = '" . $lang[2] . "', tag = '" . $lang[3] . "', active = 1, major = 1, english_name = '" . $lang[4] . "', encode_url = 0";
                $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
                $this->logs[] = "Executing command: " . $command;
                $this->logs[] = Lotto_Helper::execute_CLI($command);
            }
        } else { // language exists and we do not need any adjustments
            $query = "UPDATE " . $prefix . "icl_languages SET active = 1 WHERE default_locale = '" . $new_language['code'] . "'";
            $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
            $this->logs[] = "Executing command: " . $command;
            $this->logs[] = Lotto_Helper::execute_CLI($command);
        }

        $query = "INSERT INTO " . $prefix . "icl_locale_map SET code = '" . $code . "', locale = '" . $new_language['code'] . "'";
        $command = 'wp db query "' . $query . '"' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $dsn = 'mysql:dbname=' . Config::get("translations.wp.dbname") . ';host=' . Config::get("translations.wp.dbhost") . ';charset=utf8mb4';
        $user = Config::get("translations.wp.dbuser");
        $password = Config::get("translations.wp.dbpassword");

        try {
            $wp_platform = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error($e->getMessage());
            }
        }

        // update language code
        $shouldUpdateLanguageCode = !empty($lang[5]); // use !empty() in order to avoid empty value
        if ($shouldUpdateLanguageCode) {
            $this->updateLanguageCode($wp_platform, $prefix, $lang);
        } else {
            $this->logs[] = 'This language does not need to change language code.';
        }

        $dsn = 'mysql:dbname=' . Config::get("translations.wp_translations.dbname") . ';host=' . Config::get("translations.wp_translations.dbhost") . ';charset=utf8mb4';
        $user = Config::get("translations.wp_translations.dbuser");
        $password = Config::get("translations.wp_translations.dbpassword");

        $wp_translations = null;

        try {
            $wp_translations = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            if ($this->get_log_errors()) {
                $this->fileLoggerService->error($e->getMessage());
            }
            exit("There is a problem connecting to the translations database!");
        }

        $query = "SELECT wp_2_posts.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id WHERE language_code = 'en' AND element_type = 'post_page' AND post_name = 'form'";
        $this->logs[] = "Executing query: " . $query;
        $pages = $wp_translations->query($query);
        $pages = $pages->fetchAll();
        $contact_en = null;
        if ($pages !== null && count($pages)) {
            $contact_en = $pages[0];
        }

        $query = "SELECT wp_2_terms.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id WHERE language_code = 'en' AND element_type = 'tax_category' AND slug = 'uncategorized'";
        $this->logs[] = "Executing query: " . $query;
        $terms = $wp_translations->query($query);
        $terms = $terms->fetchAll();
        $term_uncategorized = null;
        if ($terms !== null && count($terms)) {
            $term_uncategorized = $terms[0];
        }

        $query = "SELECT wp_2_posts.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id WHERE language_code = '" . $code . "' AND element_type = 'post_page' AND post_type = 'page' AND post_status = 'publish'";
        $this->logs[] = "Executing query: " . $query;
        $pages = $wp_translations->query($query);

        // replace LottoPark with the name of the whitelabel!
        $whitelabel_url_noslash = substr($whitelabel_url, 0, -1);
        $name_lowercased = mb_strtolower($whitelabel['name']);
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
        $replace = [
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            $whitelabel_url_noslash,
            parse_url($whitelabel_url_noslash, PHP_URL_HOST),
            //parse_url($whitelabel_url_noslash, PHP_URL_HOST),
            parse_url($whitelabel_url_noslash, PHP_URL_HOST),
            $whitelabel['name'],
            $name_lowercased,
            "lottopark.work"
        ];

        // TODO: check if pages exists

        $contact_form = null;
        $parents = [];

        foreach ($pages as $page) {
            $query = "INSERT INTO " . $prefix . "posts SET post_author = ?, post_date = NOW(), post_date_gmt = NOW(), post_content = ?, post_status = 'publish', comment_status = 'closed', ping_status = 'closed', post_name = ?, post_title = ?, post_modified = NOW(), post_modified_gmt = NOW(), post_parent = ?, guid = ?, menu_order = 0, post_type = 'page', comment_count = 0, post_excerpt = '', to_ping = '', pinged = '', post_content_filtered = ''";
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

            $sth->execute([1, $page['post_content'], $page['post_name'], $page['post_title'], $parent, $page['guid']]);
            $id = $wp_platform->lastInsertId();
            $parents[$page['ID']] = $id;

            if ($page['trid'] == $contact_en['trid']) {
                $contact_form = $id;
            }

            $query = "INSERT INTO " . $prefix . "icl_translations SET element_type = 'post_page', element_id = ?, trid = ?, language_code = '" . $code . "'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $page['trid']]);
        }

        // now, main categories
        $query = "SELECT wp_2_terms.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id WHERE language_code = '" . $code . "' AND element_type = 'tax_category'";
        $this->logs[] = "Executing query: " . $query;
        $taxonomies = $wp_translations->query($query);

        $main_taxonomy = null;

        foreach ($taxonomies as $taxonomy) {
            $query = "INSERT INTO " . $prefix . "terms SET name = ?, slug = ?, term_group = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $taxonomy['name'] = str_replace($search, $replace, $taxonomy['name']);
            $taxonomy['slug'] = str_replace($search, $replace, $taxonomy['slug']);

            $sth->execute([$taxonomy['name'], $taxonomy['slug']]);
            $id = $wp_platform->lastInsertId();

            if ($term_uncategorized['trid'] == $taxonomy['trid']) {
                $main_taxonomy = $id;
            }

            $query = "INSERT INTO " . $prefix . "term_taxonomy SET term_id = ?, taxonomy = ?, description = '', parent = 0, count = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $sth->execute([$id, 'category']);

            $query = "INSERT INTO " . $prefix . "icl_translations SET element_type = 'tax_category', element_id = ?, trid = ?, language_code = '" . $code . "'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $taxonomy['trid']]);
        }
        // TODO: check if all lottery slugs are present

        $settings = null;
        $command = 'wp option get icl_sitepress_settings --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;

        Lotto_Helper::execute_CLI($command, $settings, false);
        $settings = json_decode($settings, true);

        $settings['active_languages'][] = $code;
        $settings['languages_order'][] = $code;

        $settings['default_categories'][$code] = $main_taxonomy;

        $settings = json_encode($settings);

        $command = 'wp option update icl_sitepress_settings "' . addslashes($settings) . '" --format=json' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);


        // now, FAQ categories
        $query = "SELECT wp_2_terms.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id WHERE language_code = '" . $code . "' AND element_type = 'tax_faq-category'";
        $this->logs[] = "Executing query: " . $query;
        $faqtaxonomies = $wp_translations->query($query);

        $taxonomy_map = [];

        foreach ($faqtaxonomies as $faqtaxonomy) {
            $query = "INSERT INTO " . $prefix . "terms SET name = ?, slug = ?, term_group = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $faqtaxonomy['name'] = str_replace($search, $replace, $faqtaxonomy['name']);
            $faqtaxonomy['slug'] = str_replace($search, $replace, $faqtaxonomy['slug']);


            $sth->execute([$faqtaxonomy['name'], $faqtaxonomy['slug']]);
            $id = $wp_platform->lastInsertId();

            $taxonomy_map[$faqtaxonomy['term_id']] = $id;

            $query = "INSERT INTO " . $prefix . "term_taxonomy SET term_id = ?, taxonomy = ?, description = '', parent = 0, count = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $sth->execute([$id, 'faq-category']);

            $query = "INSERT INTO " . $prefix . "icl_translations SET element_type = 'tax_faq-category', element_id = ?, trid = ?, language_code = '" . $code . "'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $faqtaxonomy['trid']]);
        }

        // get relationships
        // and connect them
        $query = "SELECT * FROM wp_2_term_relationships";
        $this->logs[] = "Executing query: " . $query;
        $relations = $wp_translations->query($query);

        $relation_map = [];
        foreach ($relations as $relation) {
            $relation_map[$relation['object_id']] = $relation['term_taxonomy_id'];
        }

        // FAQs
        $query = "SELECT wp_2_posts.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id WHERE language_code = '" . $code . "' AND element_type = 'post_faq'";
        $this->logs[] = "Executing query: " . $query;
        $faqs = $wp_translations->query($query);

        foreach ($faqs as $faq) {
            $query = "INSERT INTO " . $prefix . "posts SET post_author = ?, post_date = NOW(), post_date_gmt = NOW(), post_content = ?, post_status = 'publish', comment_status = 'closed', ping_status = 'closed', post_name = ?, post_title = ?, post_modified = NOW(), post_modified_gmt = NOW(), post_parent = ?, guid = ?, menu_order = 0, post_type = 'faq', comment_count = 0, post_excerpt = '', to_ping = '', pinged = '', post_content_filtered = ''";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $faq['post_name'] = str_replace($search, $replace, $faq['post_name']);
            $faq['post_content'] = str_replace($search, $replace, $faq['post_content']);
            $faq['post_title'] = str_replace($search, $replace, $faq['post_title']);
            $faq['guid'] = str_replace($search, $replace, $faq['guid']);

            $sth->execute([1, $faq['post_content'], $faq['post_name'], $faq['post_title'], $parent, $faq['guid']]);
            $id = $wp_platform->lastInsertId();
            $parents[$faq['ID']] = $id; // update this array

            $taxonomy_id = $taxonomy_map[$relation_map[$faq['ID']]];

            // connect the faq with faq category
            $query = "INSERT INTO " . $prefix . "term_relationships SET object_id = ?, term_taxonomy_id = ?, term_order = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $taxonomy_id]);

            $query = "INSERT INTO " . $prefix . "icl_translations SET element_type = 'post_faq', element_id = ?, trid = ?, language_code = '" . $code . "', source_language_code = ?";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $faq['trid'], LanguageHelper::DEFAULT_LANGUAGE]);
        }

        // the last thing: menus! not so easy though
        // first, create menus (taxonomies)
        $query = "SELECT wp_2_terms.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_terms ON wp_2_terms.term_id = element_id WHERE language_code = '" . $code . "' AND element_type = 'tax_nav_menu'";
        $this->logs[] = "Executing query: " . $query;
        $menus = $wp_translations->query($query);
        $menus_map = [];

        foreach ($menus as $menu) {
            $query = "INSERT INTO " . $prefix . "terms SET name = ?, slug = ?, term_group = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $sth->execute([$menu['name'], $menu['slug']]);
            $id = $wp_platform->lastInsertId();

            $menus_map[$menu['term_id']] = $id;

            $query = "INSERT INTO " . $prefix . "term_taxonomy SET term_id = ?, taxonomy = ?, description = '', parent = 0, count = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $sth->execute([$id, 'nav_menu']);

            $query = "INSERT INTO " . $prefix . "icl_translations SET element_type = 'tax_nav_menu', element_id = ?, trid = ?, language_code = '" . $code . "'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $menu['trid']]);
        }

        // then create menu items (posts)
        $query = "SELECT wp_2_posts.*, wp_2_icl_translations.* FROM `wp_2_icl_translations` INNER JOIN wp_2_posts ON wp_2_posts.ID = element_id WHERE language_code = '" . $code . "' AND element_type = 'post_nav_menu_item'";
        $this->logs[] = "Executing query: " . $query;
        $items = $wp_translations->query($query);

        $numberOfItemsInMenu = [];
        foreach ($items as $item) {
            if (!isset($relation_map[$item['ID']]) || !isset($menus_map[$relation_map[$item['ID']]])) {
                // probably removed
                continue;
            }
            $query = "INSERT INTO " . $prefix . "posts SET post_author = ?, post_date = NOW(), post_date_gmt = NOW(), post_content = ?, post_status = 'publish', comment_status = 'closed', ping_status = 'closed', post_name = ?, post_title = ?, post_modified = NOW(), post_modified_gmt = NOW(), post_parent = ?, guid = ?, menu_order = ?, post_type = 'nav_menu_item', comment_count = 0, post_excerpt = '', to_ping = '', pinged = '', post_content_filtered = ''";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);

            $item['post_name'] = str_replace($search, $replace, $item['post_name']);
            $item['post_content'] = str_replace($search, $replace, $item['post_content']);
            $item['post_title'] = str_replace($search, $replace, $item['post_title']);
            $item['guid'] = str_replace($search, $replace, $item['guid']);

            $sth->execute([1, $item['post_content'], $item['post_name'], $item['post_title'], $parent, $item['guid'], $item['menu_order']]);
            $id = $wp_platform->lastInsertId();

            $taxonomy_id = $menus_map[$relation_map[$item['ID']]];

            $query = "INSERT INTO " . $prefix . "term_relationships SET object_id = ?, term_taxonomy_id = ?, term_order = 0";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $taxonomy_id]);

            $query = "INSERT INTO " . $prefix . "icl_translations SET element_type = 'post_nav_menu_item', element_id = ?, trid = ?, language_code = '" . $code . "'";
            $this->logs[] = "Executing query: " . $query;
            $sth = $wp_platform->prepare($query);
            $sth->execute([$id, $item['trid']]);

            // now menu item meta
            $query = "SELECT * FROM wp_2_postmeta WHERE post_id = " . $item['ID'];
            $this->logs[] = "Executing query: " . $query;
            $cmeta = $wp_translations->query($query);

            $cmeta = $cmeta->fetchAll();

            $kmeta = [];
            foreach ($cmeta as $meta) {
                $kmeta[$meta['meta_key']] = $meta['meta_value'];
            }
            foreach ($cmeta as $meta) {
                $query = "INSERT INTO " . $prefix . "postmeta SET post_id = ?, meta_key = ?, meta_value = ?";
                $this->logs[] = "Executing query: " . $query;
                $sth = $wp_platform->prepare($query);

                if ($meta['meta_key'] == '_menu_item_object_id' && $kmeta['_menu_item_type'] == 'post_type') {
                    $meta['meta_value'] = $parents[$meta['meta_value']];
                }
                if ($meta['meta_key'] == '_menu_item_object_id' && $kmeta['_menu_item_type'] == 'taxonomy' && $kmeta['_menu_item_object'] == 'faq-category') {
                    $meta['meta_value'] = $taxonomy_map[$meta['meta_value']];
                }
                $meta['meta_value'] = str_replace($search, $replace, $meta['meta_value']);

                $sth->execute([$id, $meta['meta_key'], $meta['meta_value']]);
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

        // remove WPML cache
        $command = 'wp option delete _icl_cache' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // flush the cache, just to be sure the widgets are reloaded
        $command = 'wp transient delete --all' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        $command = 'wp cache flush' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        // remove fuel cache
        Cache::delete_all();

        // resave permalinks
        $command = 'wp rewrite flush' . $wp_cli_addition;
        $this->logs[] = "Executing command: " . $command;
        $this->logs[] = Lotto_Helper::execute_CLI($command);

        return $this->logs;
    }


    public function add_whitelabel_language($language)
    {
        $this->setup_fuel($language);
        $this->setup_wordpress($language);
        return $this->logs;
    }

    /**
     *
     * @return void
     */
    public function process_form(&$inside): void
    {
        $whitelabel = $this->get_whitelabel();
        $subaction = $this->get_subaction();
        $inside->set("whitelabel", $whitelabel);

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
        $whitelabel_languages_indexed_by_code = [];

        if ($subaction == "new") {          // I THINK THAT, AT THIS MOMENT ONLY THIS IS CORRECT
            foreach ($whitelabel_languages as $whitelabel_language) {
                $whitelabel_languages_indexed_by_code[$whitelabel_language['code']] = $whitelabel_language;
            }

            $all_languages = Model_Language::get_all_languages();

            $languages_indexed_by_id = [];
            foreach ($all_languages as $language) {
                if (!isset($whitelabel_languages_indexed_by_code[$language['code']])) {
                    $languages_indexed_by_id[$language['id']] = $language;
                }
            }
            $inside->set("languages", $languages_indexed_by_id);

            if (Input::post("input") === null) {
                return;
            }

            $validated_form = $this->validate_form();

            if ($validated_form->run()) {
                if (!isset($languages_indexed_by_id[$validated_form->validated('input.language')])) {
                    Session::set_flash('message', ['danger', _("Wrong language!")]);
                    Response::redirect('whitelabels/' . $whitelabel['id'] . '/languages');
                }

                $language = $languages_indexed_by_id[$validated_form->validated('input.language')];

                $logs = [];
                try {
                    $logs = $this->add_whitelabel_language($language);

                    Lotto_Helper::clear_cache('model_whitelabel_language.whitelabellanguages');
                    Session::set_flash('message', ['success', array_merge([_("Whitelabel language has been added!")], $logs)]);
                    Response::redirect('whitelabels/' . $whitelabel['id'] . '/languages');
                } catch (Exception $e) {
                    $errors = [($e->getMessage()) . "<br>" . implode('<br>', $logs)];
                    $this->set_errors($errors);
                }
            } else {
                $errors = Lotto_Helper::generate_errors($validated_form->error());
                $this->set_errors($errors);
            }
        } else { // THIS IS NOT CORRECT - I BASED ON THE CODE WHICH WAS LEFT WITHIN languages
            $inside->set("languages", $whitelabel_languages);
        }
    }
}
