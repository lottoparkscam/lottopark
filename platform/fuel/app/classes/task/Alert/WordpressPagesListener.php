<?php

namespace Task\Alert;

use Carbon\Carbon;
use Container;
use Exception;
use Helpers\Wordpress\LanguageHelper;
use Helpers_Time;
use Models\Whitelabel;
use Repositories\LotteryRepository;
use Repositories\Orm\RaffleRepository;

class WordpressPagesListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_WORDPRESS_PAGES;
    private string $currentWhitelabelsDomain;
    private array $lifeTimeCache = [];
    private array $pages;
    private array $translations;
    private array $templates;
    protected string $slackChannelName = 'health-check-wordpress';

    public function __construct()
    {
        parent::__construct();
        $this->currentWhitelabelsDomain = get_site_url();
    }

    /** @var array|array[] Key means parent */
    private array $expectedPages = [
        0 => [
            'none' => [
                'account',
                'auth',
                'contact',
                'footer',
                'faq',
                'home',
                'lotteries',
                'results',
                'news',
                'play',
                'privacy',
                'terms',
                'activation',
                'activated',
                'deposit',
                'order',
            ],
        ],
        1 => [
            'none' => [
                'play-raffle',
                'information-raffle',
                'results-raffle',
            ],
            'auth' => [
                'login',
                'lostpassword',
                'signup',
            ],
            'contact' => [
                'form',
            ],
            'deposit' => [
                'success',
                'failure',
            ],
            'order' => [
                'success',
                'failure',
            ]
        ],
    ];

    private array $raffleSlugs = ['play-raffle', 'information-raffle', 'results-raffle'];
    private array $lotterySlugs = ['play', 'lotteries', 'results'];
    private array $raffleWithTemplatesBySlug = [
        'play-raffle' => 'template-raffle-play.php',
        'information-raffle' => 'template-raffle-information.php',
    ];

    public function shouldSendAlert(): bool
    {
        $dayOfYearParity = Carbon::now()->dayOfYear % 2;
        $lotteryRepository = Container::get(LotteryRepository::class);
        $enabledLotteries = $lotteryRepository->findEnabledForCurrentWhitelabel();

        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $raffleRepository = Container::get(RaffleRepository::class);
        $enabledRaffles = $raffleRepository->getAllRafflesForWhitelabelShort($whitelabel->id);

        $this->fetchPages();

        $activeLanguages = $this->getActiveLanguages();
        foreach ($activeLanguages as $language) {
            $languageCode = $language['code'];
            $expectedPages = $this->expectedPages[$dayOfYearParity];
            foreach ($expectedPages as $parentSlug => $parentSlugs) {
                foreach ($parentSlugs as $pageSlug) {
                    $isRaffleSlug = $parentSlug === 'none' && in_array($pageSlug, $this->raffleSlugs);
                    // We don't want to check raffle pages when whitelabel has no raffle
                    $shouldOmitRaffleCheck = $isRaffleSlug && empty($enabledRaffles);
                    if ($shouldOmitRaffleCheck) {
                        continue;
                    }

                    /**
                     * We check here:
                     * - if page exists in default language
                     * - if page has translation for $language
                     * - if page in default language has correct parent
                     * - if page in $Language has correct parent
                     */
                    $isPageNotCorrect = !$this->isPageCorrect($pageSlug, $parentSlug, $languageCode);
                    if ($isPageNotCorrect) {
                        // We send only first error, run task manually to get more when you fix this one
                        return true;
                    }

                    // Lotteries Pages Check
                    $shouldCheckLotteries = $parentSlug === 'none' && in_array($pageSlug, $this->lotterySlugs);
                    if ($shouldCheckLotteries) {
                       foreach ($enabledLotteries as $lottery) {
                           $isLotteryPageNotCorrect = !$this->isPageCorrect(
                               $lottery['slug'],
                               $pageSlug,
                               $languageCode
                           );
                           if ($isLotteryPageNotCorrect) {
                               // We send only first error, run task manually to get more when you fix this one
                               return true;
                           }
                       }
                    }

                    $shouldCheckRaffles = $isRaffleSlug;
                    if ($shouldCheckRaffles) {
                        $expectedTemplate = null;
                        $shouldCheckTemplate = key_exists($pageSlug, $this->raffleWithTemplatesBySlug);
                        if ($shouldCheckTemplate) {
                            $expectedTemplate = $this->raffleWithTemplatesBySlug[$pageSlug];
                        }

                        foreach ($enabledRaffles as $raffle) {
                            $isRafflePageNotCorrect = !$this->isPageCorrect(
                                $raffle['slug'],
                                $pageSlug,
                                $languageCode,
                                $expectedTemplate,
                            );
                            if ($isRafflePageNotCorrect) {
                                // We send only first error, run task manually to get more when you fix this one
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    private function getActiveLanguages(): array
    {
        return apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    }

    private function fetchPages(): void
    {
        global $wpdb;

        $pagesDatabaseName = $wpdb->posts;
        $this->pages = $wpdb->get_results("SELECT ID, post_name, post_parent FROM $pagesDatabaseName WHERE post_type = 'page'");

        $pagesTranslationsDatabaseName = str_replace('_posts', '_icl_translations', $pagesDatabaseName);
        $this->translations = $wpdb->get_results("SELECT trid, language_code, element_id FROM $pagesTranslationsDatabaseName WHERE element_type = 'post_page'");

        $postmetaDatabaseName = str_replace('_posts', '_postmeta', $pagesDatabaseName);
        $this->templates = $wpdb->get_results("SELECT post_id, meta_value FROM $postmetaDatabaseName WHERE meta_key = '_wp_page_template'");
    }

    private function findPagesBySlug(string $slug): array
    {
        return array_values(array_filter(
            $this->pages,
            fn($page) => $page->post_name === $slug
        ));
    }

    private function findPageByID(string $ID): ?object
    {
        $index = array_search($ID, array_column($this->pages, 'ID'), true);
        return $index !== false ? $this->pages[$index] : null;
    }

    private function findTranslationByPageID(string $ID): ?object
    {
        $index = array_search($ID, array_column($this->translations, 'element_id'), true);
        return $index !== false && isset($this->translations[$index]) ? $this->translations[$index] : null;
    }

    private function findTranslationInDefaultLanguageByTrid(string $trid): ?object
    {
        $filtered = array_filter(
            $this->translations,
            fn($t) => $t->trid === $trid && $t->language_code === LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE
        );

        return reset($filtered) ?: null;
    }

    private function findTranslationByTridAndLanguageShortcode(string $trid, string $languageShortcode): ?object
    {
        $filtered = array_filter(
            $this->translations,
            fn($t) => $t->trid === $trid && $t->language_code === $languageShortcode
        );

        return reset($filtered) ?: null;
    }

    private function findTemplateByPageID(string $ID): ?object
    {
        $index = array_search($ID, array_column($this->templates, 'post_id'), true);
        return $index !== false && isset($this->templates[$index]) ? $this->templates[$index] : null;
    }

    private function isPageCorrect(
        string $slug,
        string $expectedParentSlug,
        string $languageShortcode,
        string $expectedTemplate = null,
    ): bool {
        $forParentText = $expectedParentSlug === 'none' ? '' : ' for parent ' . $expectedParentSlug;
        $shouldSearchForSpecificParent = $expectedParentSlug !== 'none';
        $defaultLanguageShortcode = LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE;

        // Find row in en language to detect trid value
        // The trid value is key that connect wp page with group of translations in WPML
        // We check here if slug exists in default language also
        $firstFoundPageCacheKey = "first_found_page_{$slug}_{$expectedParentSlug}";
        $firstFoundPageExists = key_exists($firstFoundPageCacheKey, $this->lifeTimeCache);
        if ($firstFoundPageExists) {
            $firstFoundPage =  $this->lifeTimeCache[$firstFoundPageCacheKey];
        }

        if (empty($firstFoundPage)) {
            $pages = $this->findPagesBySlug($slug);
            if (empty($pages)) {
                $this->setMessage("Any page with slug: '$slug' does not exist for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }

            $firstFoundPage = $pages[0];
            // e.g. faireum-raffle can have post with information-raffle or results-raffle
            if ($shouldSearchForSpecificParent) {
                $isNotAnyPageWithCorrectParent = true;
                foreach ($pages as $page) {
                    $parentPageID = $page->post_parent;
                    $parentPage = $this->findPageByID($parentPageID);
                    if (empty($parentPage)) {
                        continue;
                    }

                    $parentPageSlug = $parentPage->post_name;
                    $isParentCorrect = $parentPageSlug === $expectedParentSlug;
                    if ($isParentCorrect) {
                        $isNotAnyPageWithCorrectParent = false;
                        $firstFoundPage = $page;
                        break;
                    }
                }

                if ($isNotAnyPageWithCorrectParent) {
                    $this->setMessage("There is no page with parent: '$expectedParentSlug' and slug: '$slug' for whitelabel: '$this->currentWhitelabelsDomain'");
                    return false;
                }
            }

            $this->lifeTimeCache[$firstFoundPageCacheKey] = $firstFoundPage;
        }

        // In wp_posts we have also pages for translations, so we need to find page in default 'en' language
        $firstFoundTranslationCacheKey = "first_found_translation_{$slug}_${expectedParentSlug}";
        $firstFoundTranslationInCacheExists = key_exists($firstFoundTranslationCacheKey, $this->lifeTimeCache);
        if ($firstFoundTranslationInCacheExists) {
            $firstFoundTranslation = $this->lifeTimeCache[$firstFoundTranslationCacheKey];
        }

        if (empty($firstFoundTranslation)) {
            $firstFoundTranslation = $this->findTranslationByPageID($firstFoundPage->ID);
            if (empty($firstFoundTranslation)) {
                $this->setMessage("Page with slug: '$slug'$forParentText does not have any translation for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }
            $this->lifeTimeCache[$firstFoundTranslationCacheKey] = $firstFoundTranslation;
        }

        $trid = $firstFoundTranslation->trid;

        $defaultLanguageTranslationsCacheKey = "default_language_translation_{$slug}_${expectedParentSlug}";
        $defaultLanguageTranslationsInCacheExists = key_exists($defaultLanguageTranslationsCacheKey, $this->lifeTimeCache);
        if ($defaultLanguageTranslationsInCacheExists) {
            $defaultLanguageTranslation = $this->lifeTimeCache[$defaultLanguageTranslationsCacheKey];
        }

        if (empty($defaultLanguageTranslation)) {
            $defaultLanguageTranslation = $this->findTranslationInDefaultLanguageByTrid($trid);
            if (empty($defaultLanguageTranslation)) {
                $this->setMessage("Page with slug: '$slug'$forParentText does not have translation for default '$defaultLanguageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }
            $this->lifeTimeCache[$defaultLanguageTranslationsCacheKey] = $defaultLanguageTranslation;
        }

        // Page in default language
        $pageIDInDefaultLanguage = $defaultLanguageTranslation->element_id;

        $pageInDefaultLanguageCacheKey = "page_in_default_language_{$slug}_${expectedParentSlug}";
        $pageInDefaultLanguageInCacheExists = key_exists($pageInDefaultLanguageCacheKey, $this->lifeTimeCache);
        if ($pageInDefaultLanguageInCacheExists) {
            $pageInDefaultLanguage = $this->lifeTimeCache[$pageInDefaultLanguageCacheKey];
        }

        if (empty($pageInDefaultLanguage)) {
            $pageInDefaultLanguage = $this->findPageByID($pageIDInDefaultLanguage);
            $this->lifeTimeCache[$pageInDefaultLanguageCacheKey] = $pageInDefaultLanguage;
        }

        $parentIDInDefaultLanguage = $pageInDefaultLanguage->post_parent;

        // Check if page exists in specific language
        $pagesTranslation = $this->findTranslationByTridAndLanguageShortcode($trid, $languageShortcode);
        $translationNotExists = empty($pagesTranslation);
        if ($translationNotExists && $this->shouldAddMissingTranslation()) {
            // We need to find parent in this language
            $this->addMissingTranslation(
                $pageIDInDefaultLanguage,
                $languageShortcode,
                $trid,
            );

            // Renew pagesTranslations
            $pagesTranslation = $this->findTranslationByTridAndLanguageShortcode($trid, $languageShortcode);
        } else if ($translationNotExists) {
            $this->setMessage("Page with slug: '$slug'$forParentText does not have translation for '$languageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
            return false;
        }

        $translatedPageID = $pagesTranslation->element_id;
        $translatedPage = $this->findPageByID($translatedPageID);
        if (empty($translatedPage)) {
            $this->setMessage("Cannot find page with ID: {$pagesTranslation->element_id}");
            return false;
        }

        $shouldCheckTemplate = !empty($expectedTemplate);
        if ($shouldCheckTemplate) {
            // Check template of default language
            $templatesInDefaultLanguage = $this->findTemplateByPageID($pageIDInDefaultLanguage);
            if (empty($templatesInDefaultLanguage)) {
                $this->setMessage("Page with slug: '$slug' has not set template. It should be: '$expectedTemplate' for default '$defaultLanguageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }

            $templateInDefaultLanguage = $templatesInDefaultLanguage->meta_value;
            $isWrongTemplateInDefaultLanguage = $templateInDefaultLanguage !== $expectedTemplate;
            if ($isWrongTemplateInDefaultLanguage) {
                $this->setMessage("Page with slug: '$slug' has set wrong template: '$templateInDefaultLanguage'. It should be: '$expectedTemplate' for default '$defaultLanguageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }

            // Check template of translation
            $translatedTemplate = $this->findTemplateByPageID($translatedPageID);
            if (empty($translatedTemplate)) {
                $this->setMessage("Page with slug: '$slug' has not set template. It should be: '$expectedTemplate' for '$languageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }

            $translatedTemplate = $translatedTemplate->meta_value;
            $isWrongTranslatedTemplate = $translatedTemplate !== $expectedTemplate;
            if ($isWrongTranslatedTemplate) {
                $this->setMessage("Page with slug: '$slug' has set wrong template: '$templateInDefaultLanguage'. It should be: '$expectedTemplate' for default '$languageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
                return false;
            }
        }

        $shouldOmitParentCheck = !$shouldSearchForSpecificParent;
        if ($shouldOmitParentCheck) {
            return true;
        }

        // Check if parent of default 'en' language is correct
        $parentPageInDefaultLanguage = $this->findPageByID($parentIDInDefaultLanguage);
        if (empty($parentPageInDefaultLanguage)) {
            $this->setMessage("Page with slug: '$pageInDefaultLanguage->post_name' has set parent ID that not exists: '$parentIDInDefaultLanguage' for default '$defaultLanguageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
            return false;
        }

        $isParentNotCorrect = $parentPageInDefaultLanguage->post_name !== $expectedParentSlug;
        if ($isParentNotCorrect) {
            $this->setMessage("Page with slug: '$slug' has wrong parent: '$parentPageInDefaultLanguage->post_name' instead of '$expectedParentSlug' for default '$defaultLanguageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
            return false;
        }

        // Check if parent of translated page is correct
        $translatedPageParentID = $translatedPage->post_parent;
        $parentIsNotSet = $translatedPageParentID === '0';
        if ($parentIsNotSet) {
            $this->setMessage("Parent is not set for page with slug: '$slug' for '$languageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'. It should be parent with slug: '$expectedParentSlug'");
            return false;
        }

        // This is the translation record for parent's current language
        $translatedPageParentTranslation = $this->findTranslationByPageID($translatedPageParentID);
        if (empty($translatedPageParentTranslation)) {
            $this->setMessage("Parent with slug: '$expectedParentSlug' of page with slug: '$slug' has missing translation for '$languageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
            return false;
        }

        // Let's search for the trid - id of parent's translations group
        $translatedPageParentTrid = $translatedPageParentTranslation->trid;
        // Found parent's translation record in default 'en' language
        $translatedPageParentIDInDefaultLanguage = $this->findTranslationInDefaultLanguageByTrid($translatedPageParentTrid)->element_id;
        // Find parent page in default language
        $translatedPageParentInDefaultLanguage = $this->findPageByID($translatedPageParentIDInDefaultLanguage);
        if (empty($translatedPageParentInDefaultLanguage)) {
            $this->setMessage(
                "Parent with slug: '$expectedParentSlug' of page with slug: '$slug' has set wrong post ID in translation database for default '$defaultLanguageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'. " .
                "Page with this ID does not exists. Trid: $translatedPageParentTrid, set parent's page id for default language on translation record: $translatedPageParentIDInDefaultLanguage"
            );
            return false;
        }
        $parentTranslatedPageSlug = $translatedPageParentInDefaultLanguage->post_name;
        $isTranslatedParentNotCorrect = $parentTranslatedPageSlug !== $expectedParentSlug;
        if ($isTranslatedParentNotCorrect) {
            $this->setMessage("Page with slug: '$slug' has wrong parent: '$parentTranslatedPageSlug' instead of '$expectedParentSlug' for '$languageShortcode' language for whitelabel: '$this->currentWhitelabelsDomain'");
            return false;
        }

        return true;
    }

    private function addMissingTranslation(
        int $pageIDInDefaultLanguage,
        string $translationLanguageCode,
        string $translationsTrid,
    ): void {
        global $wpdb;
        $pagesDatabaseName = $wpdb->posts;

        $pagesTranslationsDatabaseName = str_replace('_posts', '_icl_translations', $pagesDatabaseName);

        $parentIDInTranslationLanguage = 0;

        $pageInDefaultLanguage = $wpdb->get_results("SELECT post_parent, post_name, post_content, post_title, post_excerpt, post_status FROM $pagesDatabaseName WHERE ID = $pageIDInDefaultLanguage")[0];
        $parentIDInDefaultLanguage = $pageInDefaultLanguage->post_parent;
        $parentInDefaultLanguage = $wpdb->get_results("SELECT post_name FROM $pagesDatabaseName WHERE ID = $parentIDInDefaultLanguage AND post_type = 'page'");
        $shouldSearchForParent = $parentIDInDefaultLanguage !== '0';
        if ($shouldSearchForParent) {
            $parentTranslation = $wpdb->get_results("SELECT trid FROM $pagesTranslationsDatabaseName WHERE element_id = $parentIDInDefaultLanguage AND element_type = 'post_page'");
            if (empty($parentTranslation)) {
                throw new Exception("Cannot auto-repair translations for page with slug $pageInDefaultLanguage->post_name. Parent page has no translation in default language. Parent slug: {$parentInDefaultLanguage[0]->post_name}");
            }

            $parentTranslationTrid = $parentTranslation[0]->trid;
            $parentInCurrentLanguage = $wpdb->get_results("SELECT element_id FROM $pagesTranslationsDatabaseName WHERE trid = $parentTranslationTrid AND language_code = '$translationLanguageCode' AND element_type = 'post_page'");
            if (empty($parentInCurrentLanguage)) {
                throw new Exception("Cannot auto-repair translations for page with slug $pageInDefaultLanguage->post_name. Parent page has no translation in '$translationLanguageCode' language. Parent slug: {$parentInDefaultLanguage[0]->post_name}");
            }

            $parentIDInTranslationLanguage = $parentInCurrentLanguage[0]->element_id;
        }

        $now = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
        $content = htmlspecialchars($pageInDefaultLanguage->post_content, ENT_COMPAT);
        $title = htmlspecialchars($pageInDefaultLanguage->post_title, ENT_COMPAT);
        $excerpt = htmlspecialchars($pageInDefaultLanguage->post_excerpt, ENT_COMPAT);
        $status = htmlspecialchars($pageInDefaultLanguage->post_status, ENT_COMPAT);
        $queryToAddPage = <<<QUERY
        INSERT INTO `$pagesDatabaseName` (
             `ID`, 
             `post_author`, 
             `post_date`, 
             `post_date_gmt`, 
             `post_content`, 
             `post_title`, 
             `post_excerpt`, 
             `post_status`, 
             `comment_status`, 
             `ping_status`, 
             `post_password`, 
             `post_name`, 
             `to_ping`, 
             `pinged`, 
             `post_modified`, 
             `post_modified_gmt`, 
             `post_content_filtered`, 
             `post_parent`, 
             `guid`, 
             `menu_order`, 
             `post_type`, 
             `post_mime_type`, 
             `comment_count`
        ) VALUES 
        (NULL,
         '1',
         '$now',
         '$now', 
         "$content",
         "$title",
         "$excerpt",
         "$status",
         'closed',
         'closed',
         '', 
         '$pageInDefaultLanguage->post_name',
         '',
         '',
         '$now',
         '$now',
         '',
         '$parentIDInTranslationLanguage', 
         'guid_to_update',
         '0',
         'page',
         '',
         '0')
QUERY;
        $insertingPageFailed = !$wpdb->query($queryToAddPage);
        if ($insertingPageFailed) {
            throw new Exception('Inserting missing translated page failed.');
        }

        $insertedID = $wpdb->get_results("SELECT ID FROM $pagesDatabaseName WHERE post_name = '$pageInDefaultLanguage->post_name' AND post_date = '$now' AND guid = 'guid_to_update'")[0]->ID;

        // Add missing translation
        $defaultLanguageShortcode = LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE;
        $queryToAddTranslation = <<<QUERY
INSERT INTO $pagesTranslationsDatabaseName (`translation_id`, `element_type`, `element_id`, `trid`, `language_code`, `source_language_code`) 
VALUES (NULL, 'post_page', $insertedID, $translationsTrid, '$translationLanguageCode', '$defaultLanguageShortcode')
QUERY;
        $insertingTranslationFailed = !$wpdb->query($queryToAddTranslation);
        if ($insertingTranslationFailed) {
            throw new Exception("Inserting missing translation failed. Page ID: $insertedID");
        }

        $currentDomain = Container::get('domain');
        $guid = "https://$currentDomain/?page_id=$insertedID/";

        // Updated guid
        $updatingGuidFailed = !$wpdb->query("UPDATE $pagesDatabaseName SET guid = '$guid' WHERE ID = $insertedID");
        if ($updatingGuidFailed) {
            throw new Exception("Updating guid field of translated page failed. Page ID: $insertedID");
        }
    }

    private function shouldAddMissingTranslation(): bool
    {
        return !empty(getenv('FIX_MISSING_TRANSLATIONS'));
    }
}
