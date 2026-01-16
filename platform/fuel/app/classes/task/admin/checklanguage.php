<?php

namespace Task\Admin;

use DB;
use Exception;
use Input;
use Model_Whitelabel;
use View;
use WP_Query;

class CheckLanguage
{
    private $whitelabel;

    private $languages;

    private $chosen_language;

    private $found_pages;

    private $found_categories;

    private $pages_that_should_be_identical = [
        'none' => [
            'auth' => false,
        ]
    ];

    private $categories_that_should_be_identical = [];

    private $expected_pages = [
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
    ];

    private $expected_categories = [];

    private $pages = [];

    private $categories = [];

    private $translated_pages = [];

    private $translated_categories = [];

    private $are_pages_identical = [];

    private $are_categories_identical = [];

    public function __construct($domain)
    {
        $this->whitelabel = $this->getWhitelabelByDomain($domain);

        $this->switchToWhitelabelBlog();

        $this->languages = $this->getActiveLanguages();

        $this->readInput();

        $this->setExpectedPagesAndCategories(
            $this->getLotteries()
        );

        $this->run();
    }

    private function run()
    {
        $this->pages = $this->findPages($this->expected_pages);
        $this->categories = $this->findCategories($this->expected_categories);

        $this->translated_pages = [];
        $this->are_pages_identical = $this->pages_that_should_be_identical;
    }

    private function readInput()
    {
        $this->chosen_language = Input::post("language") ?? "en";
    }

    private function switchToWhitelabelBlog()
    {
        $site = $this->findWordpressSiteForWhiteLabel($this->whitelabel);

        $this->switchToBlog($site);
    }

    private function getWhitelabelByDomain($domain)
    {
        $whitelabel = Model_Whitelabel::get_by_domain($domain);
        if ($whitelabel === null) {
            throw new Exception("Wrong white-label");
        }

        return $whitelabel;
    }

    private function setExpectedPagesAndCategories($lotteries)
    {
        $this->expected_pages['play'] = $lotteries;
        $this->expected_pages['results'] = $lotteries;
        $this->expected_pages['lotteries'] = $lotteries;

        $this->expected_categories = $lotteries;
        $this->expected_categories[] = "uncategorized";

        $lottery_set = array_fill_keys($lotteries, false);

        $this->pages_that_should_be_identical['play'] = $lottery_set;
        $this->pages_that_should_be_identical['results'] = $lottery_set;
        $this->pages_that_should_be_identical['lotteries'] = $lottery_set;

        $this->categories_that_should_be_identical = $lottery_set;
    }

    private function getLotteries()
    {
        $lotteries = DB::select("lottery.slug")
                    ->from("whitelabel_lottery")
                    ->join("lottery")
                    ->on("lottery.id", "=", "whitelabel_lottery.lottery_id")
                    ->where("whitelabel_lottery.whitelabel_id", "=", $this->whitelabel['id'])
                    ->and_where("whitelabel_lottery.is_enabled", "=", 1)
                    ->and_where("lottery.is_enabled", "=", 1)->execute()->as_array();

        return array_column($lotteries, "slug");
    }

    public function display(): View
    {
        if ($this->chosen_language != "en") {
            $this->reviewLanguage($this->chosen_language);
        }

        return View::forge('admin/tasks/checkpages/index', [
            "languages" => $this->languages,
            "whitelabel" => $this->whitelabel,
            "chosen_language" => $this->chosen_language,
            "pages" => $this->preparePagesForView(),
            "categories" => $this->prepareCategoriesForView(),
        ]);
    }

    private function reviewLanguage($language)
    {
        global $sitepress;
        $sitepress->switch_lang($language);

        foreach ($this->expected_pages as $parent_page => $child_pages) {
            foreach ($child_pages as $child_page) {
                $this->checkTranslationsAndUniformity($child_page, $parent_page);
            }
        }

        foreach ($this->expected_categories as $category) {
            $this->checkCategoryTranslations($category);
        }

        $sitepress->switch_lang('en');
    }

    private function checkCategoryTranslations($category)
    {
        if (!isset($this->categories[$category])) {
            return;
        }
        $found_category = $this->categories[$category];

        $translated_category = $this->getTranslatedCategory($found_category);

        if ($translated_category === null) {
            return;
        }

        $this->translated_categories[$category] = $translated_category;

        if ($this->shouldCategoryBeIdentical($found_category)
            && $this->areCategoriesIdentical($translated_category, $found_category)) {
            $this->are_categories_identical[$category] = true;
        }
    }

    private function getTranslatedCategory($category)
    {
        $translated_category_id = $this->getTranslatedCategoryId($category);
        if ($translated_category_id === null) {
            return null;
        }
        return \get_category($translated_category_id);
    }

    private function checkTranslationsAndUniformity($child_page, $parent_page)
    {
        $found_pages = $this->pages[$parent_page][$child_page] ?? null;
        if ($found_pages === null) {
            return;
        }
        
        foreach ($found_pages as $found_page) {
            $translated_page = $this->getTranslatedPage($found_page);

            if ($translated_page === null) {
                continue;
            }

            $this->translated_pages[$parent_page][$child_page][] = $translated_page;
            
            if ($this->shouldBeIdentical($child_page, $parent_page)
                && $this->areIdentical($translated_page, $found_page)) {
                $this->are_pages_identical[$parent_page][$child_page] = true;
            }
        }
    }

    private function getTranslatedPage($original_page)
    {
        $translated_page_id = $this->getTranslatedPageId($original_page);
        if ($translated_page_id === null) {
            return null;
        }
        return \get_post($translated_page_id);
    }

    private function areIdentical($first_page, $second_page)
    {
        if ($first_page->post_name === $second_page->post_name) {
            return true;
        }
        return false;
    }

    private function areCategoriesIdentical($first_category, $second_category)
    {
        if ($first_category->slug == $second_category->slug) {
            return  true;
        }
        return false;
    }

    private function shouldBeIdentical($child_page, $parent_page)
    {
        return isset($this->pages_that_should_be_identical[$parent_page][$child_page]);
    }

    private function shouldCategoryBeIdentical($category)
    {
        return isset($this->categories_that_should_be_identical[$category->slug]);
    }

    private function getTranslatedPageId($page)
    {
        return $this->getTranslatedId($page->ID);
    }

    private function getTranslatedCategoryId($category)
    {
        return $this->getTranslatedId($category->term_id, 'category');
    }

    private function getTranslatedId($item, $type = 'page')
    {
        return \apply_filters('wpml_object_id', $item, $type);
    }

    private function findCategories($expected_categories): array
    {
        $this->found_categories = [];

        if ($this->chosen_language === null) {
            return $this->found_categories;
        }

        $categories = \get_categories([
            "hide_empty" => false
        ]);
        
        foreach ($categories as $category) {
            if (in_array($category->slug, $expected_categories)) {
                $this->found_categories[$category->slug] = $category;
            }
        }

        return $this->found_categories;
    }

    private function findPages($pages): array
    {
        $this->found_pages = [];

        if ($this->chosen_language === null) {
            return $this->found_pages;
        }

        foreach ($pages as $parent_page => $child_pages) {
            $the_query = $this->runWordpressQuery($child_pages, $parent_page);
            if (!$the_query) {
                continue;
            }

            if (!$the_query->have_posts()) {
                $this->found_pages[$parent_page] = [];
                continue;
            }
            
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $slug = \get_post_field('post_name', \get_post());
                $this->found_pages[$parent_page][$slug][] = \get_post();
            }
        }

        return $this->found_pages;
    }

    private function runWordpressQuery($page_names, $parent_page)
    {
        $args = [];
        $args['post_name__in'] = $page_names;
        $args['post_type'] = 'page';
        $args['posts_per_page'] = -1;

        if ($parent_page != "none") {
            if (!isset($this->found_pages["none"][$parent_page])) {
                return false;
            }
            $args['post_parent'] = get_post_field('ID', $this->found_pages["none"][$parent_page][0]);
        }

        return new WP_Query($args);
    }

    private function prepareCategoriesForView()
    {
        $categories = $this->categories;
        $expected_categories = $this->expected_categories;

        $final_categories = [];
        foreach ($expected_categories as $expected_category) {
            $item = [
                'slug' => $expected_category,
                'present' => false,
                'ids' => '-',
            ];

            if (isset($categories[$expected_category])) {
                $item['ids'] = '<a target="_blank" href="https://'.$this->whitelabel['domain'].'/wp-admin/term.php?taxonomy=category&tag_ID='.$categories[$expected_category]->term_id.'&post_type=post">'.$categories[$expected_category]->term_id.'</a>';
            }

            $satisfies = false;

            if (in_array($expected_category, array_keys($categories))) {
                $item['present'] = true;
                $satisfies = true;
            }

            if ($this->chosen_language !== 'en') {
                $satisfies = false;

                $item['present'] = isset($this->translated_categories[$expected_category]);

                $item['translated_ids'] = $this->translated_categories[$expected_category]->term_id ?? '';
                $item['translated_slug'] = $this->translated_categories[$expected_category]->slug ?? '';
                $item['must_be_identical'] = isset($this->are_categories_identical[$expected_category]);

                if ($item['must_be_identical'] && isset($this->are_categories_identical[$expected_category])
                && $this->are_categories_identical[$expected_category] === true) {
                    $satisfies = true;
                }
            }

            $item['present_text'] = $item['present'] ? 'Yes' : 'No';
            $item['present_class'] = $satisfies ? 'table-success' : 'table-danger';
            $final_categories[] = $item;
        }
        return $final_categories;
    }

    private function preparePagesForView()
    {
        $pages = $this->pages;
        $expected_pages = $this->expected_pages;

        $final_pages = [];
        foreach ($expected_pages as $parent_page => $child_pages) {
            foreach ($child_pages as $page) {
                $item = [
                    'slug' => $this->getPath($page, $parent_page),
                    'present' => false,
                    'ids' => '-',
                    'count' => 0,
                    'published_count' => 0
                ];

                if ($this->chosen_language !== 'en') {
                    $item['translated_slugs'] = $this->getTranslatedSlugs($page, $parent_page);
                    $item['must_be_identical'] = $this->shouldBeIdentical($page, $parent_page);
                    $item['translated_ids'] = $this->getTranslatedIds($page, $parent_page);
                }

                if (!empty($pages[$parent_page][$page])) {
                    sort($pages[$parent_page][$page]);

                    $item['ids'] = $this->getIds($pages[$parent_page][$page]);

                    $item['published_count'] = $this->getPublishedCount($pages[$parent_page][$page]);

                    $item['count'] = count($pages[$parent_page][$page]);
                }
                

                if (in_array($page, array_keys($pages[$parent_page]))) {
                    $item['present'] = true;
                }

                $satisfies = false;

                if ($this->chosen_language !== 'en') {
                    $item['count'] = 0;
                    if (!empty($this->translated_pages[$parent_page][$page])) {
                        $item['count'] = count($this->translated_pages[$parent_page][$page]);
                    }
                    $item['published_count'] = $this->getPublishedCount($this->translated_pages[$parent_page][$page] ?? []);

                    $satisfies = true;

                    if (empty($item['translated_slugs'])) {
                        $item['present'] = false;
                        $satisfies = false;
                    }

                    if ($item['must_be_identical'] && $this->are_pages_identical[$parent_page][$page] === false) {
                        $satisfies = false;
                    }

                    $item['must_be_identical'] = $item['must_be_identical'] ? 'Yes' : 'No';
                }

                $item['present_text'] = $item['present'] ? 'Yes' : 'No';

                if ($item['present'] && $item['count'] == 1 && $item['published_count'] == 1) {
                    $satisfies = true;
                }

                $item['present_class'] = $satisfies ? 'table-success' : 'table-danger';
                $final_pages[] = $item;
            }
        }
        return $final_pages;
    }

    private function getTranslatedIds($page, $parent_page)
    {
        if (!isset($this->translated_pages[$parent_page][$page])) {
            return false;
        }
        return $this->getIds($this->translated_pages[$parent_page][$page]);
    }

    private function getIds($pages)
    {
        return implode(', ', array_map(function ($item) {
            return '<a target="_blank" href="https://'. $this->whitelabel['domain'].'/wp-admin/post.php?post='.$item->ID.'&action=edit">'.$item->ID.'</a>';
        }, $pages));
    }

    private function getPublishedCount($pages)
    {
        $published_count = 0;
        foreach ($pages as $page) {
            if ($page->post_status == "publish") {
                $published_count++;
            }
        }
        return $published_count;
    }

    private function getTranslatedSlugs($page, $parent_page)
    {
        if (!isset($this->translated_pages[$parent_page][$page])) {
            return false;
        }
        return implode(', ', array_map(function ($item) use ($parent_page) {
            $parent_translated_slug = "none";
            if ($parent_page != "none") {
                $parent_translated_slug = $this->getTranslatedSlugs($parent_page, "none");
            }
            return $this->getPath($item->post_name, $parent_translated_slug);
        }, $this->translated_pages[$parent_page][$page]));
    }

    private function getPath($page, $parent_page)
    {
        $path = $parent_page != "none" ? $parent_page . "/" : "";
        $path .= $page;
        return $path;
    }

    private function findWordpressSiteForWhiteLabel($whitelabel)
    {
        $sites = \get_sites([
            "domain" => $whitelabel['domain']
        ]);

        if (empty($sites)) {
            throw new Exception("Can't find Wordpress website with specified domain!");
        }

        return $sites[0];
    }

    private function switchToBlog($site)
    {
        \switch_to_blog($site->blog_id);
    }

    private function getActiveLanguages()
    {
        return \apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    }
}
