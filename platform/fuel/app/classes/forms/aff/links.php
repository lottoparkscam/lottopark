<?php

use Fuel\Core\Input;
use Fuel\Core\Validation;
use Helpers\UrlHelper;

/**
 * @deprecated
 */
final class Forms_Aff_Links extends Forms_Main
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
    private $banner_url = 'image/';

    /**
     *
     * @var string
     */
    private $widget_url = 'api/internal/widget/generate';

    /**
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel = [])
    {
        $this->whitelabel = $whitelabel;
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("medium", _("Medium"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("campaign", _("Campaign"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("content", _("Content"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("isCasinoCampaign", _("Is casino campaign"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }

    /**
     * @return Validation object
     */
    public function get_banner_validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("banner_size", _("Banner size"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 8)
            ->add_rule("valid_string", ["alpha", "numeric"]);

        $validation->add("language", _("Language"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 5)
            ->add_rule("valid_string", ["alpha", "dashes"]);

        $validation->add("lottery", _("Lottery"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("color_type", _("Color type"))
            ->add_rule("trim")
            ->add_rule("max_length", 20)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("medium", _("Medium"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("campaign", _("Campaign"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("content", _("Content"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);
        return $validation;
    }

    /**
     * @return Validation object
     */
    public function get_widgets_validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("widget_option", _("Widget option"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 2)
            ->add_rule("valid_string", ["numeric"]);

        $validation->add("widget_size", _("Widget width"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("valid_string", ["alpha"]);

        $validation->add("custom_width", _("Custom width"))
            ->add_rule("trim")
            ->add_rule("valid_string", ["numeric"]);

        $validation->add("language", _("Language"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 5)
            ->add_rule("valid_string", ["alpha", "dashes"]);

        $validation->add("lottery1", _("Lottery"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("lottery2", _("Lottery"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("lottery3", _("Lottery"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("max_length", 50)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("medium", _("Medium"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("campaign", _("Campaign"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("content", _("Content"))
            ->add_rule("trim")
            ->add_rule("max_length", 100)
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        return $validation;
    }

    /**
     *
     * @param View $inside
     * @param array $user
     * @return void
     */
    public function process_form(&$inside, array &$user = null): void
    {
        if (Input::post("submit") === null) {
            return;
        }

        if (empty($user) || is_null($user)) {
            return;
        }
        
        $whitelabel = $this->get_whitelabel();
        $link = 'https://' . $whitelabel['domain'] . '/?ref=' . strtoupper($user['token']);

        $validated_form = $this->validate_form();
        $isCasinoCampaign = false;
        if ($validated_form->run()) {
            if (!empty($validated_form->validated("medium"))) {
                $link .= '&medium=' . rawurlencode($validated_form->validated("medium"));
            }

            if (!empty($validated_form->validated("campaign"))) {
                $link .= '&campaign=' . rawurlencode($validated_form->validated("campaign"));
            }

            if (!empty($validated_form->validated("content"))) {
                $link .= '&content=' . rawurlencode($validated_form->validated("content"));
            }

            if (!empty($validated_form->validated("isCasinoCampaign"))) {
                $isCasinoCampaign = true;
            }
            
            $link = $isCasinoCampaign ? UrlHelper::changeAbsoluteUrlToCasinoUrl($link, true) : $link;
            $inside->set("link", $link);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $inside->set("errors", $errors);
        }

        return;
    }

    /**
     *
     * @param View $inside
     * @param array $user
     * @return void
     */
    public function process_banner_form(&$inside, ?array &$user = []): void
    {
        if (Input::post("submit") === null) {
            return;
        }

        $whitelabel = $this->get_whitelabel();

        $ref_link = 'https://' . $whitelabel['domain'] . '/';

        $banner_link = 'https://' . $whitelabel['domain'] . '/';

        $http_query = [];

        if (!empty($user) && !empty($user['token'])) {
            $http_query['ref'] = $user['token'];
        }

        $banner_validated_form = $this->get_banner_validate_form();

        if ($banner_validated_form->run()) {
            $lottery_validation = $this->check_lottery_exist($banner_validated_form->validated("lottery"));

            // Some validation
            if (!$lottery_validation) {
                $inside->set("errors", [_("Lottery is disabled or doesn't exist.")]);
                return ;
            }
            
            $color_validation = $this->check_color_type(
                $banner_validated_form->validated("lottery"),
                $banner_validated_form->validated("color_type")
            );
            
            if (!$color_validation) {
                $inside->set("errors", [_("Color type doesn't exist.")]);
                return ;
            }

            // Add post variables
            if (!empty($banner_validated_form->validated("medium"))) {
                $http_query['medium'] = rawurlencode($banner_validated_form->validated("medium"));
            }

            if (!empty($banner_validated_form->validated("campaign"))) {
                $http_query['campaign'] = rawurlencode($banner_validated_form->validated("campaign"));
            }

            if (!empty($banner_validated_form->validated("content"))) {
                $http_query['content'] = rawurlencode($banner_validated_form->validated("content"));
            }

            $language_explode = explode('_', rawurlencode($banner_validated_form->validated("language")));

            // Add language to url
            if ($language_explode[0] != "en") {
                $banner_link .= $language_explode[0]."/";
                $ref_link .= $language_explode[0]."/";
            }

            // Add banner url
            $banner_link .= $this->banner_url;

            // Add banner variables
            $banner_link .= '?size=' . rawurlencode($banner_validated_form->validated("banner_size"));

            $banner_link .= '&lottery=' . rawurlencode($banner_validated_form->validated("lottery"));

            if (!empty($banner_validated_form->validated("color_type"))) {
                $banner_link .= '&type=' . rawurlencode($banner_validated_form->validated("color_type"));
            }

            if (!empty($http_query)) {
                $ref_link .= '?' . http_build_query($http_query);
            }

            $code = '<a href="' . $ref_link . '"><img src="' . $banner_link . '"></a>';

            $inside->set("code", $code);
            $inside->set("link", $banner_link);
        } else {
            $errors = Lotto_Helper::generate_errors($banner_validated_form->error());
            $inside->set("errors", $errors);
        }

        return;
    }

    /**
     *
     * @param View $inside
     * @param array $user
     * @return void
     */
    public function process_widgets_form(&$inside, ?array &$user = []): void
    {
        if (Input::post("submit") === null) {
            return;
        }

        if (empty($user)) {
            return;
        }
        
        $whitelabel = $this->get_whitelabel();

        $types = Banners_Widgets::$allowed_methods;

        $widget_link = 'https://' . $whitelabel['domain'] . '/';

        $widgets_validated_form = $this->get_widgets_validate_form();

        if ($widgets_validated_form->run()) {
            $lottery_validation = $this->check_lottery_exist($widgets_validated_form->validated("lottery1"));

            // Some validation
            if (!$lottery_validation) {
                $inside->set("errors", [_("Lottery doesn't exist.")]);
                return;
            }

            // Add post variables
            if (!empty($widgets_validated_form->validated("medium"))) {
                $http_query['medium'] = rawurlencode($widgets_validated_form->validated("medium"));
            }

            if (!empty($widgets_validated_form->validated("campaign"))) {
                $http_query['campaign'] = rawurlencode($widgets_validated_form->validated("campaign"));
            }

            if (!empty($widgets_validated_form->validated("content"))) {
                $http_query['content'] = rawurlencode($widgets_validated_form->validated("content"));
            }

            $language_explode = explode('_', rawurlencode($widgets_validated_form->validated("language")));

            if ($language_explode[0] != "en") {
                $widget_link .= $language_explode[0] . '/';
            }

            // Add banner url
            $widget_link .= $this->widget_url;

            // Add banner variables
            $widget_link .= '?widget=' . rawurlencode(Input::post("widget_option_" . $widgets_validated_form->validated("widget_option")));

            $lotteries_count = $types[$widgets_validated_form->validated("widget_option")]['options']['lotteries'];

            for ($i = 1; $i <= $lotteries_count; $i++) {
                $widget_link .= '&lottery' . $i . '=' . rawurlencode($widgets_validated_form->validated("lottery" . $i));
            }

            if ($widgets_validated_form->validated("widget_size") == "custom") {
                $widget_link .= '&width=' . $widgets_validated_form->validated("custom_width");
            } else {
                $widget_link .= '&width=full';
            }

            if (!empty($user['token'])) {
                $widget_link .= '&ref='.strtoupper($user['token']);
            }

            $uniqid = 'widget-' . md5(uniqid(time(), true));

            $widget_link .= '&id=' . $uniqid;

            $code['script'] = '<script src="' . $widget_link . '"></script>';
            $code['div'] = '<div id="' . $uniqid . '"></div>';

            $inside->set("code", $code);
        } else {
            $errors = Lotto_Helper::generate_errors($widgets_validated_form->error());
            $inside->set("errors", $errors);
        }

        return;
    }

    /**
     * Checks if lottery exist
     *
     * @param string $slug - Lottery slug
     * @return bool
     */
    private function check_lottery_exist(string $slug): bool
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        return array_key_exists($slug, $lotteries['__by_slug']);
    }

    /**
     *  Checks if color type exist
     *
     * @param string $lottery_slug
     * @param string $type It was int, but based on validation it should be string
     * @return bool
     */
    private function check_color_type(string $lottery_slug, string $type): bool
    {
        if ($type != null) {
            // Checks if main color configuration exist
            return array_key_exists($type, Banners_Create::$allowed_colors);
        } else {
            $options = "";
            $path = realpath('../../wordpress/wp-content/themes/base/banners.php');

            $disable_main_configuration = true;
            require_once($path);

            // Checks if lottery color configuration exist
            //return array_key_exists($lottery, $options);
            return true;
        }
    }
}
