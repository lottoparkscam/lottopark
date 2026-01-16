<?php

namespace Forms\Wordpress;

use Container;
use Fuel\Core\Security;
use Fuel\Core\View;
use Helpers\UrlHelper;
use Helpers_General;
use LanguageHelper;
use Lotto_Helper;
use Lotto_Settings;
use Lotto_View;
use Model_Whitelabel;
use Model_Whitelabel_Mails_Custom_Template;
use Model_Whitelabel_Mails_Template;
use OptimizeQueryService;
use RaffleMailer;
use Services\Logs\FileLoggerService;

class Forms_Wordpress_Email
{
    /**
     * Colors for whitelabels
     *
     * @var array
     */
    private $colors = [
        'default' => [
            'header_bg' => '#1f93d5',
            'btn_bg' => '#1f93d5',
            'btn_text' => '#FFF',
            'normal_ball_bg' => 'royalblue',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => 'orange',
            'bonus_ball_text' => '#FFF'
        ],
        'lotoking' => [
            'header_bg' => '#046add',
            'btn_bg' => '#3cc68a',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#046add',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#ff3e58',
            'bonus_ball_text' => '#fff'
        ],
        'lotteo' => [
            'header_bg' => '#4b9dc8',
            'btn_bg' => '#4b9dc8',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#4b9dc8',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#ff5812',
            'bonus_ball_text' => '#FFF'
        ],
        'lottohoy' => [
            'header_bg' => '#9d37af',
            'btn_bg' => '#9d37af',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#9d37af',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#3aaf37',
            'bonus_ball_text' => '#FFF'
        ],
        'lottomat' => [
            'header_bg' => '#212f5f',
            'btn_bg' => '#212f5f',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#212f5f',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#ff6200',
            'bonus_ball_text' => '#FFF'
        ],
        'lottopark' => [
            'header_bg' => '#41934c',
            'btn_bg' => '#41934c',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#41934c',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#fe7104',
            'bonus_ball_text' => '#FFF'
        ],
        'redfoxlotto' => [
            'header_bg' => '#f9b339',
            'btn_bg' => '#f9b339',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#f9b339',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#f17524',
            'bonus_ball_text' => '#FFF'
        ],
        'tulotos' => [
            'header_bg' => '#581168',
            'btn_bg' => '#581168',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#581168',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#c53f93',
            'bonus_ball_text' => '#FFF'
        ],
        'lottolooting' => [
            'header_bg' => '#003399',
            'btn_bg' => '#003399',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#003399',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#73d433',
            'bonus_ball_text' => '#FFF'
        ],
        'lottolive24' => [
            'header_bg' => '#54abe5',
            'btn_bg' => '#67bd2e',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#40a4e5',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#74c554',
            'bonus_ball_text' => '#fff'
        ],
        'lotteryking' => [
            'header_bg' => '#3faad4',
            'btn_bg' => '#28a3d2',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#27a4d3',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#f67f1e',
            'bonus_ball_text' => '#FFF'
        ],
        'faireum' => [
            'header_bg' => '#35b9ec',
            'btn_bg' => '#67bd2e',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#1db4ed',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#67bd2e',
            'bonus_ball_text' => '#FFF'
        ],
        'doublejack' => [
            'header_bg' => '#eec666',
            'btn_bg' => '#af0007',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#1db4ed',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#011f55',
            'bonus_ball_text' => '#FFF'
        ],
        'lottomonster' => [
            'header_bg' => '#35a4f1',
            'btn_bg' => '#16d383',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#1e9cf3',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#16D383',
            'bonus_ball_text' => '#FFF'
        ],
        'lovcasino' => [
            'header_bg' => '#d4293a',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#d71024',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#23bb5c',
            'bonus_ball_text' => '#FFF'
        ],
        'luminariagames' => [
            'header_bg' => '#383870',
            'btn_bg' => '#00b0df',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#e92a6d',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#00b0df',
            'bonus_ball_text' => '#FFF'
        ],
        'lotto500' => [
            'header_bg' => '#96cf53',
            'btn_bg' => '#6db432',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#8bcc3f',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#456080',
            'bonus_ball_text' => '#FFF'
        ],
        'megajackpot' => [
            'header_bg' => '#fc6e41',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#23bb5c',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#ff5e2b',
            'bonus_ball_text' => '#FFF'
        ],
        'dushibet' => [
            'header_bg' => '#f6a236',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#f6a236',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#23bb5c',
            'bonus_ball_text' => '#FFF'
        ],
        'megajackpotph' => [
            'header_bg' => '#23b0df',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#FFF',
            'normal_ball_bg' => '#ed2d75',
            'normal_ball_text' => '#FFF',
            'bonus_ball_bg' => '#0baadf',
            'bonus_ball_text' => '#FFF'
        ],
        'lottomonks' => [
            'header_bg' => '#ef872a',
            'btn_bg' => '#f07a11',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#23bb5c',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#f07a11',
            'bonus_ball_text' => '#fff'
        ],
        'lottobazar' => [
            'header_bg' => '#82c311',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#82c311',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#f97413',
            'bonus_ball_text' => '#fff'
        ],
        'lottoxworld' => [
            'header_bg' => '#cc2a45',
            'btn_bg' => '#c8102e',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#a1a7ad',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#c8102e',
            'bonus_ball_text' => '#fff'
        ],
        'megamegapot' => [
            'header_bg' => '#34b9ec',
            'btn_bg' => '#67bd2e',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#67bd2e',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#67bd2e',
            'bonus_ball_text' => '#fff',
        ],
        'fatelotto' => [
            'header_bg' => '#25b1df',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#ed2d75',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#0baadf',
            'bonus_ball_text' => '#fff',
        ],
        'gglottodaeclub' => [
            'header_bg' => '#181818',
            'btn_bg' => '#23bb5c',
            'btn_text' => '#fff',
            'normal_ball_bg' => '#23bb5c',
            'normal_ball_text' => '#fff',
            'bonus_ball_bg' => '#da2e2d',
            'bonus_ball_text' => '#fff',
        ],
    ];

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    private ?string $lang = null;

    /**
     *
     * @var array
     */
    private $additional_translations = [];

    /**
     *
     * @var int
     */
    private $source = Helpers_General::SOURCE_WORDPRESS;
    private FileLoggerService $fileLoggerService;
    private OptimizeQueryService $optimizeQueryService;

    /**
     *
     * @param array $whitelabel
     * @param int   $source
     */
    public function __construct(
        array $whitelabel = [],
        int $source = Helpers_General::SOURCE_WORDPRESS
    )
    {
        $this->whitelabel = $whitelabel;
        $this->source = $source;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->optimizeQueryService = Container::get(OptimizeQueryService::class);
    }

    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     * Get email
     *
     * @param string      $slug
     * @param null|string $lang
     * @param array       $data
     */
    public function get_email(
        string $slug,
        string $lang = null,
        array $data = []
    ): array
    {
        // Get main html template + email content
        $templateSlug = $slug;
        if ($slug === RaffleMailer::RAFFLE_BUY_SLUG) { // Raffle uses standard lottery templates, with some tweaks
            $templateSlug = 'ticket-buy';
        }
        $this->lang = $lang;
        $main_template = Model_Whitelabel_Mails_Template::get_whitelabel_templates($this->whitelabel['id'], 'template', $lang);
        $content = Model_Whitelabel_Mails_Template::get_whitelabel_templates($this->whitelabel['id'], $templateSlug, $lang);

        if (empty($content)) {
            $this->fileLoggerService->error(
                '$content is empty when $slug = ' . $templateSlug . ' lang = ' . $lang
            );

            return [];
        }

        // Set additional translates
        $this->additional_translations = $this->get_additional_translations($content, $lang);

        /* HTML VERSION */
        // Replace variables to values
        $replaced_variables = $this->build_variables($content['content'], $slug, $data);

        // Build email
        $mail = $this->build_email($main_template['content'], $replaced_variables);

        /* TEXT VERSION */
        // Replace variables to values
        $replaced_variables_text = $this->build_variables($content['text_content'], $slug, $data, false);

        // Build email
        $mail_text = $this->build_email($main_template['text_content'], $replaced_variables_text);

        // Build title
        $title = $this->build_variables($content['title'], 'title', []);

        return [
            'title' => $title,
            'body_html' => $mail,
            'alt_body' => $mail_text
        ];
    }

    /**
     * Check additional mail translations
     * Check if existing whitelabel mail translations doesn't have any missing additional_translates keys
     *
     * @param array $mail
     */
    public function check_additional_translations(array $mail): bool
    {
        // If this template doesn't have whitelabel's translate - skip checking
        if (empty($mail['custom_template_id']) || empty($mail['additional_translates'])) {
            return false;
        }

        $additional_translation = unserialize($mail['additional_translates']);
        $custom_additional_translation = unserialize($mail['custom_additional_translates']);

        // Check if custom additional translation is an array
        if (!is_array($custom_additional_translation)) {
            $custom_additional_translation = [];
        }

        $to_update = false;

        foreach ($additional_translation as $key => $translation) {
            // Check if whitelabel's translate contains this additional_translate key
            if (!array_key_exists($key, $custom_additional_translation)) {
                $to_update = true;

                // If not, update it if main translation
                $custom_additional_translation[$key] = $additional_translation[$key];
            }
        }

        // If whitelabel's translate doesn't contain some of the main translation - update it
        if ($to_update) {
            Model_Whitelabel_Mails_Custom_Template::update_additional_translates($mail['custom_template_id'], $custom_additional_translation);
        }

        return true;
    }

    /**
     * Get additional mail translations
     *
     * @param array $mail
     */
    public function get_additional_translations(
        array $mail,
        string $lang = null
    ): array
    {
        $additional_translation = [];

        $additional_translations_update = $this->check_additional_translations($mail);

        if ($additional_translations_update) {
            $mail = Model_Whitelabel_Mails_Template::get_whitelabel_templates($this->whitelabel['id'], $mail['slug'], $lang);

            $additional_translation = unserialize($mail['custom_additional_translates']);
        } elseif (!empty($mail['custom_additional_translates'])) {
            $additional_translation = unserialize($mail['custom_additional_translates']);
        } elseif (!empty($mail['additional_translates'])) {
            $additional_translation = unserialize($mail['additional_translates']);
        }

        /**
         * Main template will never have additional translation.
         * Additional translation is used only in special template like register or lost-password.
         */
        $isNotMainTemplate = $mail['slug'] !== 'template';
        if (empty($additional_translation) && $isNotMainTemplate) {
            $error_message = 'Empty additional_translations data - some templates need it!';
            $error_message .= 'Mail data: ' . serialize($mail);
            $this->fileLoggerService->warning(
                $error_message
            );
        }

        return $additional_translation;
    }

    /**
     * Build Email
     *
     * @param string $main_template
     * @param string $ready_content
     */
    public function build_email(
        string $main_template = null,
        string $ready_content = null
    ): string
    {
        if (empty($main_template)) {
            return "";
        }

        $template = $main_template;

        $domain = $this->whitelabel['domain'];

        if (IS_CASINO) {
            $casinoPrefix = UrlHelper::getCasinoPrefixForWhitelabel($domain);
            $domain = "$casinoPrefix.$domain";
        }

        // Inject content
        if (!empty($ready_content)) {
            $main_template = strtr(
                $main_template,
                [
                    '{content}' => $ready_content,
                    '{footer_name}' => $this->whitelabel['name'],
                    '{footer_email}' => 'support@' . $this->whitelabel['domain'],
                    '{main_url}' => 'https://' . $domain . '/'
                ]
            );

            if ($main_template !== false) {
                $template = $main_template;
            }
        }

        $logoFileName = IS_CASINO ? 'casino-widget-logo.png' : 'logo-email.png';

        // Replace global variables
        $data = [
            'header_image_url' => Lotto_Helper::get_wordpress_file_url_path($this->whitelabel, 'images/mail-header-image.png'),
            'logo' => Lotto_Helper::get_wordpress_file_url_path($this->whitelabel, "images/$logoFileName")
        ];

        return $this->build_variables($template, 'main-template', $data);
    }

    /**
     * Replace variables to values
     *
     * @param string|null $content
     * @param string|null $slug
     * @param array       $data
     * @param bool        $html_version
     */
    private function build_variables(
        string $content = null,
        string $slug = null,
        array $data = [],
        bool $html_version = true
    ): string
    {
        $variables = [];
        $result = "";
        // Get variables for specific email slug
        switch ($slug) {
            case 'main-template':
                $variables = $this->get_main_template_variables($data);
                break;
            case 'title':
                $variables = $this->get_title_variables($data);
                break;
            case 'register':
                $variables = $this->get_register_variables($data);
                break;
            case 'lost-password':
                $variables = $this->get_lost_password_variables($data);
                break;
            case 'email-change':
                $variables = $this->get_email_change_variables($data);
                break;
            case 'deposit-success':
                $variables = $this->get_deposit_success_variables($data);
                break;
            case 'deposit-failure':
                $variables = $this->get_deposit_failure_variables($data);
                break;
            case 'ticket-buy':
                $variables = $this->get_ticket_buy_variables($data);
                break;
            case 'ticket-failure':
                $variables = $this->get_ticket_failure_variables($data);
                break;
            case 'refer-to-bonus':
            case 'refer-by-bonus':
            case 'welcome-bonus':
                $variables = $this->get_welcome_bonus_variables($data);
                break;
            case 'draw-notification':
                $variables = $this->get_draw_notification_variables($data);
                break;
            case 'welcome-mail':
                $variables = $this->get_welcome_mail_variables($data);
                break;
            case 'multidraw-notification':
                $variables = $this->get_multidraw_notification_variables($data);
                break;
            case 'promo-code-bonus':
                $variables = $this->get_promo_code_bonus_variables($data);
                break;
            case 'raffle-buy':
                $variables = $this->getRaffleBuyVariables($data);
                break;
            case 'support-ticket':
                $variables = $this->getSupportTicketVariables($data);
                break;
            case 'confirm-social-login':
                $variables = $this->getSocialLoginVariables($data);
                break;
        }

        $variables = $html_version ? $variables['html'] : $variables['text'];

        // Replace
        $content_body = strtr($content, $variables ? $variables : []);

        if ($content_body !== false) {
            $result = $content_body;
        }

        if ((string)$slug !== "main-template" && (string)$slug !== "register" && $slug !== 'raffle-buy') {
            // Replace variables available for all templates
            $variables = $this->get_all_available_variables($data, $slug);
            $content_body = strtr($content_body, $variables ? $variables : []);

            if ($content_body !== false) {
                $result = $content_body;
            }
        }

        return $result;
    }

    /**
     * Create button for email
     *
     * @param string $title
     * @param string $link
     * @param array  $colors
     */
    private function create_button(string $title, string $link, array $colors): string
    {
        $data = [
            'title' => $title,
            'link' => $link,
            'colors' => $colors
        ];

        $button = View::forge('whitelabel/mails/button', $data)->render();

        return $button;
    }

    /**
     * Create link for email
     *
     * @param string $link
     */
    private function create_button_backup_link(string $link): string
    {
        $data = [
            'link' => $link,
        ];

        $view = View::forge('whitelabel/mails/button-backup-link', $data)->render();

        return $view;
    }

    /**
     * Get colors for whitelabel
     */
    private function get_whitelabel_colors(): array
    {
        $color_list = $this->colors;

        // Check if whitelabel have custom colors, if not - get default
        if (array_key_exists($this->whitelabel['theme'], $color_list)) {
            return $color_list[$this->whitelabel['theme']];
        }

        return $color_list['default'];
    }

    /**
     * Global variables for main template
     *
     * @param array $data
     */
    private function get_main_template_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();

        return [
            'html' => [
                '{header_image}' => '<img class="adapt-img" style="display: block; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; border: 0;height:93px;" src="' . $data['header_image_url']['url'] . '" width="580" />',
                '{logo}' => '<img src="' . $data['logo']['url'] . '" alt="' . $this->whitelabel['name'] . '" height="53" style="max-width: 75%;"/>',
                '{header_background}' => $colors['header_bg']
            ],
            'text' => [
                '{header_image}' => '',
                '{logo}' => $this->whitelabel['name'],
                '{header_background}' => ''
            ]
        ];
    }

    /**
     *
     * @param array $data
     */
    private function get_all_available_variables(array $data, string $slug): array
    {
        $exception_list = ['draw-notification', 'multidraw-notification', 'title'];

        return [
            '{lotteries_widget}' => (!in_array($slug, $exception_list)) ? $this->build_lotteries_widget() : ''
        ];
    }

    /**
     * Variables for email title
     *
     * @param array $data
     */
    private function get_title_variables(array $data): array
    {
        return [
            'html' => [
                '{name}' => $this->whitelabel['name']
            ],
            'text' => [
                '{name}' => $this->whitelabel['name']
            ]
        ];
    }

    private function get_button_additional_translation(): string
    {
        $button_additional_transalation = '';

        if (!empty($this->additional_translations['button']['translation'])) {
            $button_additional_transalation = $this->additional_translations['button']['translation'];
        }

        return $button_additional_transalation;
    }

    /**
     * Variables for register email template
     *
     * @param array $data
     */
    private function get_register_variables(array $data): array
    {
        $button_additional_transalation = $this->get_button_additional_translation();

        return [
            'html' => [
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $data['link'],
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($data['link']),
            ],
            'text' => [
                '{button}' => $button_additional_transalation . ': ' . $data['link']
            ]
        ];
    }

    private function getSocialLoginVariables(array $data): array
    {
        $buttonAdditionalTranslation = $this->get_button_additional_translation();

        return [
            'html' => [
                '{button}' => $this->create_button(
                    $buttonAdditionalTranslation,
                    $data['link'],
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($data['link']),
                '{socialName}' => $data['socialName'],
            ],
            'text' => [
                '{button}' => $buttonAdditionalTranslation . ': ' . $data['link'],
                '{socialName}' => $data['socialName'],
            ]
        ];
    }

    /**
     * Variables for lost password email
     *
     * @param array $data
     */
    private function get_lost_password_variables(array $data): array
    {
        $button_additional_transalation = $this->get_button_additional_translation();
        return [
            'html' => [
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $data['link'],
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($data['link']),
            ],
            'text' => [
                '{button}' => $button_additional_transalation . ': ' . $data['link']
            ]
        ];
    }

    /**
     * Variables for email change email template
     *
     * @param array $data
     */
    private function get_email_change_variables(array $data): array
    {
        $button_additional_transalation = $this->get_button_additional_translation();

        return [
            'html' => [
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $data['link'],
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($data['link']),
            ],
            'text' => [
                '{button}' => $button_additional_transalation . ': ' . $data['link']
            ]
        ];
    }

    /**
     * Variables for deposit success email template
     *
     * @param array $data
     */
    private function get_deposit_success_variables(array $data): array
    {
        $amount_with_currency = Lotto_View::format_currency(
            $data['amount'],
            $data['currency'],
            true
        );

        $button_additional_transalation = $this->get_button_additional_translation();
        $playLink = $this->getPermalinkBySlug('play');

        return [
            'html' => [
                '{amount}' => $amount_with_currency,
                '{transaction_id}' => $data['transaction_id'],
                '{payment_method_name}' => $data['payment_method_name'],
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $playLink,
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($playLink),
            ],
            'text' => [
                '{amount}' => $amount_with_currency,
                '{transaction_id}' => $data['transaction_id'],
                '{payment_method_name}' => $data['payment_method_name'],
                '{button}' => $button_additional_transalation . ': ' . $playLink
            ]
        ];
    }

    /**
     * Variables for deposit failure email template
     *
     * @param array $data
     */
    private function get_deposit_failure_variables(array $data): array
    {
        $amount_with_currency = Lotto_View::format_currency(
            $data['amount'],
            $data['currency'],
            true
        );

        $button_additional_transalation = $this->get_button_additional_translation();
        $depositLink = $this->getPermalinkBySlug('deposit');

        return [
            'html' => [
                '{transaction_id}' => $data['transaction_id'],
                '{amount}' => $amount_with_currency,
                '{payment_method_name}' => $data['payment_method_name'],
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $depositLink,
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($depositLink),
            ],
            'text' => [
                '{transaction_id}' => $data['transaction_id'],
                '{amount}' => $amount_with_currency,
                '{payment_method_name}' => $data['payment_method_name'],
                '{button}' => $button_additional_transalation . ': ' . $depositLink
            ]
        ];
    }

    /**
     * Variables for ticket failure email template
     *
     * @param array $data
     */
    private function get_ticket_failure_variables(array $data): array
    {
        $amount_with_currency = Lotto_View::format_currency(
            $data['amount'],
            $data['currency'],
            true
        );

        $button_additional_transalation = $this->get_button_additional_translation();
        $orderLink = $this->getPermalinkBySlug('order');

        return [
            'html' => [
                '{transaction_id}' => $data['transaction_id'],
                '{amount}' => $amount_with_currency,
                '{payment_method_name}' => $data['payment_method_name'],
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $orderLink,
                    $this->get_whitelabel_colors()
                ),
                '{link}' => $this->create_button_backup_link($orderLink),
            ],
            'text' => [
                '{transaction_id}' => $data['transaction_id'],
                '{amount}' => $amount_with_currency,
                '{payment_method_name}' => $data['payment_method_name'],
                '{button}' => $button_additional_transalation . ': ' . $orderLink
            ]
        ];
    }

    /**
     * Variables for promo code bonus email template
     *
     * @param array $data
     */
    private function get_promo_code_bonus_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $button_additional_transalation = $this->get_button_additional_translation();
        $numbers_content_text = $this->numbers_content_data_text($data);
        $accountTicketsLink = $this->getPermalinkBySlug('account') . 'tickets/';

        return [
            'html' => [
                '{lottery_name}' => $data['lottery_name'],
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $accountTicketsLink,
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($accountTicketsLink),
            ],
            'text' => [
                '{lottery_name}' => $data['lottery_name'],
                '{button}' => $button_additional_transalation . ': ' . $accountTicketsLink
            ]
        ];
    }

    /**
     *
     * @param array $ticket
     * @param array $data
     */
    private function get_amount_formatted(
        array $ticket = null,
        array $data = null
    ): string
    {
        $amount_formatted = "";

        if (!empty($ticket['amount'])) {
            if (!empty($data['currency'])) {
                $amount = Lotto_View::format_currency(
                    (!empty($ticket['multi_draw_amount'])) ? $ticket['multi_draw_amount'] : $ticket['amount'],
                    $data['currency'],
                    true
                );
                $amount_formatted = Security::htmlentities($amount);
            } else {
                $amount_formatted = Security::htmlentities($ticket['amount']);
            }
        }

        return $amount_formatted;
    }

    /**
     *
     * @param array $data
     */
    private function numbers_content_data(array $data): string
    {
        // It is not used in the function and it made errors because of
        // lack of the currency set for some cases!!
//        $amount_with_currency = Lotto_View::format_currency(
//            $data['amount'],
//            $data['currency'],
//            true
//        );
//
//        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $colors = $this->get_whitelabel_colors();
        $numbers_content = '<table width="100%" cellpadding="0" cellspacing="0" align="left" style="border-top:1px solid #E8E8E8;">
            <tr>
                <td align="center" style="color: #5e5d60;" data-slot-container="1" style="">
                    <table cellpadding="0" cellspacing="0" class="table-content" width="100%" style="margin-top:7px;">';

        $numbers_content .= '';
        if (!empty($this->additional_translations['ticket_details']['translation'])) {
            $numbers_content .= '
                <tr>
                    <td style="padding-top:20px;padding-bottom:5px;font-weight:bold;font-size:18px;color:#5e5d60;">' .
                $this->additional_translations['ticket_details']['translation'] .
                ':</td>
                </tr>';
        }

        // Prepare tickets
        if (!empty($data['tickets'])) {
            foreach ($data['tickets'] as $id => $ticket) {

                if ($id == 0) {
                    $padding_top = '40';
                } else {
                    $padding_top = ' 25';
                }

                // TODO: MOVE IT TO PRESENTER/VIEW

                // Ticket lottery info
                $numbers_content .= '
                    <tr>
                        <td style="padding-top:15px;padding-bottom:3px;font-weight:bold;color:#727e8b;font-weight:bold;">' . $ticket['lottery_name'] . '</td>
                    </tr>';

                $draw_date_translation = '';
                if (!empty($this->additional_translations['draw_date']['translation'])) {
                    $draw_date_translation = $this->additional_translations['draw_date']['translation'] . ': ';
                }

                $purchase_date_translation = '';
                if (!empty($this->additional_translations['purchase_date']['translation'])) {
                    $purchase_date_translation = $this->additional_translations['purchase_date']['translation'] . ': ';
                }

                $purchase_price_translation = '';
                if (!empty($this->additional_translations['purchase_price']['translation'])) {
                    $purchase_price_translation = $this->additional_translations['purchase_price']['translation'] . ': ';
                }

                $amount_formatted = $this->get_amount_formatted($ticket, $data);

                // Draw date
                $this->setUserTimezone($data['user_timezone']);
                $ticket['draw_date'] = \Helpers_View_Date::format_date_for_user_timezone($ticket['draw_date'], $ticket['timezone']);
                $ticket['date'] = \Helpers_View_Date::format_date_for_user_timezone($ticket['date'], 'UTC');

                $purchase_price_content = ($amount_formatted != "0.00") ? '
                    <tr>
                        <td style="padding-right: 20px;">' . $purchase_price_translation . '<b>' . $amount_formatted . '</b></td>
                    </tr>
                    ' : '';

                // MULTI-DRAW
                $multi_draw = '';
                if (!empty($ticket['multi_draw_id']) && !empty($this->additional_translations['ticket_type'])) {
                    $ticket_type = $this->additional_translations['multi_draw_ticket_type']['translation'];
                } elseif (!empty($this->additional_translations['ticket_type'])) {
                    $ticket_type = $this->additional_translations['single_ticket_ticket_type']['translation'];
                }

                if (!empty($this->additional_translations['ticket_type'])) {
                    $multi_draw .= '<tr>
                        <td style="padding-right: 20px;">' . $this->additional_translations['ticket_type']['translation'] . ': <b>' . $ticket_type . '</b></td>
                    </tr>';
                }

                if (!empty($ticket['multi_draw_id'])) {
                    $multi_draw .= '<tr>
                        <td style="padding-right: 20px;">' . $this->additional_translations['draws']['translation'] . ': <b>' . $ticket['multi_draw_tickets'] . '</b></td>
                    </tr>';
                }

                $numbers_content .= (!empty($ticket['draw_date']) && !empty($ticket['date'])) ? '
                    <tr>
                        <td style="padding-bottom:10px;">
                            <table cellspacing="0" style="line-height:22px;font-size:14px; font-family: \'Roboto\', Arial;color:#7c8a94;">
                                <tr>
                                    <td style="padding-right: 90px;">' . $draw_date_translation . '<b>' . $ticket['draw_date'] . '</b></td>
                                </tr>
                                                                    <tr>
                                    <td style="padding-right: 90px;">' . $purchase_date_translation . '<b>' . $ticket['date'] . '</b></td>
                                </tr>
                                ' . $purchase_price_content . '
                                ' . $multi_draw . '
                            </table>
                        </td>
                    </tr>' : '';
            }
        }

        $numbers_content .= ' </table>
                </td>
            </tr>
        </table>';

        return $numbers_content;
    }

    /**
     *
     * @param array $data
     */
    private function numbers_content_data_text(array $data): string
    {
        // It is not used in the function and it made errors because of
        // lack of the currency set for some cases!!
//        $amount_with_currency = Lotto_View::format_currency(
//            $data['amount'],
//            $data['currency'],
//            true
//        );
//
//        $currencies = Lotto_Settings::getInstance()->get("currencies");

        $colors = $this->get_whitelabel_colors();
        $numbers_content = '';

        $numbers_content .= !empty($this->additional_translations['ticket_details']['translation']) ? $this->additional_translations['ticket_details']['translation'] . PHP_EOL : '';

        // Prepare tickets
        if (!empty($data['tickets'])) {
            foreach ($data['tickets'] as $id => $ticket) {

                // Ticket lottery info
                $numbers_content .= $ticket['lottery_name'] . PHP_EOL;

                $draw_date_translation = '';
                if (!empty($this->additional_translations['draw_date']['translation'])) {
                    $draw_date_translation = $this->additional_translations['draw_date']['translation'] . ': ';
                }

                $purchase_date_translation = '';
                if (!empty($this->additional_translations['purchase_date']['translation'])) {
                    $purchase_date_translation = $this->additional_translations['purchase_date']['translation'] . ': ';
                }

                $purchase_price_translation = '';
                if (!empty($this->additional_translations['purchase_price']['translation'])) {
                    $purchase_price_translation = $this->additional_translations['purchase_price']['translation'] . ': ';
                }

                $amount_formatted = $this->get_amount_formatted($ticket, $data);

                // Draw date
                $this->setUserTimezone($data['user_timezone']);
                $ticket['draw_date'] = \Helpers_View_Date::format_date_for_user_timezone($ticket['draw_date'], $ticket['timezone']);
                $ticket['date'] = \Helpers_View_Date::format_date_for_user_timezone($ticket['date'], 'UTC');

                $purchase_price_content = ($amount_formatted != "0.00") ? $purchase_price_translation . $amount_formatted . PHP_EOL : '';

                $numbers_content .= (!empty($ticket['draw_date']) && !empty($ticket['date'])) ?
                    $draw_date_translation . $ticket['draw_date'] . PHP_EOL .
                    $purchase_date_translation . $ticket['date'] . PHP_EOL .
                    $purchase_price_content : '';
            }
        }

        return $numbers_content;
    }

    /**
     * Variables for ticket buy email template
     *
     * @param array $data
     */
    private function get_ticket_buy_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $button_additional_transalation = $this->get_button_additional_translation();

        $amount_with_currency = Lotto_View::format_currency(
            $data['amount'],
            $data['currency'],
            true
        );

        $numbers_content = $this->numbers_content_data($data);
        $numbers_content_text = $this->numbers_content_data_text($data);
        $accountTicketsLink = $this->getPermalinkBySlug('account') . 'tickets/';

        return [
            'html' => [
                '{amount}' => $amount_with_currency,
                '{payment_method_name}' => $data['payment_method_name'],
                '{numbers}' => $numbers_content,
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $accountTicketsLink,
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($accountTicketsLink),
            ],
            'text' => [
                '{amount}' => $amount_with_currency,
                '{payment_method_name}' => $data['payment_method_name'],
                '{numbers}' => $numbers_content_text,
                '{button}' => $button_additional_transalation . ': ' . $accountTicketsLink
            ]
        ];
    }

    /**
     * Variables for welcome email template
     *
     * @param array $data
     */
    private function get_welcome_mail_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $button_additional_transalation = $this->get_button_additional_translation();
        $playLink = $this->getPermalinkBySlug('play');

        return [
            'html' => [
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $playLink,
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($playLink),
            ],
            'text' => [
                '{button}' => $button_additional_transalation . ': ' . $playLink
            ]
        ];
    }

    /**
     * Variables for ticket failure email template
     *
     * @param array $data
     */
    private function get_welcome_bonus_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $button_additional_transalation = $this->get_button_additional_translation();
        $numbers_content = $this->numbers_content_data($data);
        $numbers_content_text = $this->numbers_content_data_text($data);
        $accountTicketsLink = $this->getPermalinkBySlug('account') . 'tickets/';

        return [
            'html' => [
                '{lottery_name}' => $data['lottery_name'],
                '{numbers}' => $numbers_content,
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $accountTicketsLink,
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($accountTicketsLink),
            ],
            'text' => [
                '{lottery_name}' => $data['lottery_name'],
                '{numbers}' => $numbers_content_text,
                '{button}' => $button_additional_transalation . ': ' . $accountTicketsLink
            ]
        ];
    }

    private function build_lotteries_widget(): string
    {
        $lotteries = Model_Whitelabel::get_lotteries_by_highest_jackpot_for_whitelabel($this->whitelabel['id'], true);

        $colors = $this->get_whitelabel_colors();
        $widget_content = '<table width="100%" align="left" style="margin-top: 0px;">
            <tr>
                <td align="center" style="color: #5e5d60;" data-slot-container="1" style="">
                    <table width="100%">';

        foreach ($lotteries as $id => $lottery) {
            list(
                $towin,
                $formatted_thousands
                ) = Lotto_View::get_jackpot_formatted_to_text(
                $lottery['current_jackpot'],
                $lottery['currency'],
                $this->source,
                $lottery['force_currency']
            );

            $lottery_image = Lotto_View::get_lottery_image($lottery['id'], $this->whitelabel);

            if ($id != 0) {
                $border = 'border-top:1px solid #E8E8E8;';
            } else {
                $border = '';
            }

            $lottery_image = Lotto_View::get_lottery_image($lottery['id'], $this->whitelabel);
            $playLotteryLink = $this->getPermalinkBySlug('play/' . $lottery['slug']);

            $widget_content .= '<tr><td>
                    <table width="100%" style="' . $border . '">
                        <tr>
                            <td rowspan="2" width="20%" valign="middle" align="left" style="padding: 18px 5px 18px 0;"><img src="' . $lottery_image . '" width="80" /></td>
                            <td width="55%" style="font-size: 20px;font-weight: bold;color: #4c4e5c;padding-bottom:2px;padding-top:4px;" valign="bottom">' . Security::htmlentities(_($lottery['name'])) . '</td>
                            <td rowspan="2" width="25%" valign="middle" align="right">
                                <table cellpadding="0" cellspacing="0" width="100%" align="right" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px;">
                                    <tbody>
                                    <tr style="border-collapse: collapse;">
                                        <td align="right" class="esd-block-text" style="margin: 0;padding-top:18px;padding-bottom:18px;">
                                            <table>
                                                <tr>
                                                    <td style="background: ' . $colors['btn_bg'] . ';text-decoration: none;border-radius: 3px;"><a href="' . UrlHelper::esc_url(UrlHelper::removeCasinoPrefixFromAbsoluteUrl($playLotteryLink)) . '" style="color:' . $colors['btn_text'] . ';border-top:10px solid ' . $colors['btn_bg'] . ';border-bottom:10px solid ' . $colors['btn_bg'] . ';border-left:20px solid ' . $colors['btn_bg'] . ';border-right:20px solid ' . $colors['btn_bg'] . ';text-decoration:none;font-weight:bold;text-transform: lowercase;font-size: 15px;font-family: Arial;display:block;border-radius:3px;">' . strtolower(Security::htmlentities(_("Play now"))) . '</a></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="color:#d5271e;font-size:26px;font-weight:bold;padding-top:2px;" valign="top">' . $towin . '</td>
                        </tr>
                        </table>
                    </td></tr>';

            if ($id >= 2) {
                break;
            }
        }

        $widget_content .= '</table>
                        </td>
                    </tr>
                </table>';

        return $widget_content;
    }


    /**
     * Variables for draw notification
     *
     * @param $data
     */
    private function get_multidraw_notification_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $button_additional_translation = $this->get_button_additional_translation();

        $numbers_content = '<table width="100%" cellpadding="0" cellspacing="0" align="left" style="border-top:1px solid #E8E8E8;">
            <tr>
                <td align="center" style="color: #5e5d60;" data-slot-container="1" style="">
                    <table cellpadding="0" cellspacing="0" class="table-content" width="100%" style="margin-top:7px;">';

        $numbers_content .= '';
        if (!empty($this->additional_translations['ticket_details']['translation'])) {
            $numbers_content .= '
                <tr>
                    <td style="padding-top:20px;padding-bottom:5px;font-weight:bold;font-size:18px;color:#5e5d60;">' .
                $this->additional_translations['ticket_details']['translation'] .
                ':</td>
                </tr>';
        }

        // Balls list structure
        $multidraw_content = '
                <table class="table-content" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #E8E8E8;width:100% !important;margin-bottom:0px;">
                    <tr>
                    <td align="center" style="color: #5e5d60;" data-slot-container="1" style="">
                        <table cellpadding="0" cellspacing="0" class="table-content" width="100%" style="margin-top:7px;">
                        <tr>
                        <td style="padding-top:20px;padding-bottom:5px;font-weight:bold;font-size:18px;color:#5e5d60;">' .
            $this->additional_translations['ticket_details']['translation'] . ':
                        </td>
                        </tr>
                        </table>
                    </td>
                    <tr>
                        <td style="padding-top: 15px; padding-bottom: 3px; line-height: 150%; font-family: Roboto, Arial; color: #727e8b;"><strong>' . $data['lottery_name'] . '</strong></td>
                    </tr>
                    <tr>
                        <td style="line-height: 150%; font-size: 15px; font-family: Roboto, Arial; color: #727e8b;"><strong>' . $this->additional_translations['draws']['translation'] . ':</strong> ' . $data['draws'] . '</td>
                    </tr>
                    <tr>
                        <td style="line-height: 150%; font-size: 15px; font-family: Roboto, Arial; color: #727e8b;"><strong>' . $this->additional_translations['last_date']['translation'] . ':</strong> ' . $data['last_date'] . '</td>
                    </tr>
                </table>';

        $multidraw_content_text = "\r\n" . $data['lottery_name'] . "\r\n";
        $multidraw_content_text .= $this->additional_translations['draws']['translation'] . ': ' . $data['draws'] . "\r\n";
        $multidraw_content_text .= $this->additional_translations['last_date']['translation'] . ': ' . $data['last_date'] . "\r\n";

        return [
            'html' => [
                '{lottery_name}' => $data['lottery_name'],
                '{multidraw}' => $multidraw_content,
                '{button}' => $this->create_button(
                    $button_additional_translation,
                    $data['link'],
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($data['link']),
            ],
            'text' => [
                '{lottery_name}' => $data['lottery_name'],
                '{multidraw}' => $multidraw_content_text,
                '{button}' => $button_additional_translation . ': ' . $data['link']
            ]
        ];
    }

    /**
     * Variables for draw notification
     *
     * @param $data
     */
    private function get_draw_notification_variables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $button_additional_transalation = $this->get_button_additional_translation();
        $numbers_content = "";

        $lottery_numbers = explode(',', $data['numbers']);
        $lottery_bnumbers = explode(',', $data['bnumbers']);
        sort($lottery_numbers);
        sort($lottery_bnumbers);

        $lottery_numbers_text = $data['numbers'] . ' - ' . $data['bnumbers'];

        // Draw date
        $this->setUserTimezone($data['user_timezone']);
        $data['draw_date'] = \Helpers_View_Date::format_date_for_user_timezone($data['draw_date'], $data['lottery_timezone']);

        // Prepare balls content for email
        $numbers = [];

        // Normal balls
        foreach ($lottery_numbers as $number) {
            if (!empty($number)) {
                $numbers[] = '<td style="padding-left:0;padding-bottom:8px;padding-right:4px;"><table style="min-width:36px"><tr><td style="font-weight:bold;border-radius: 100%;width: 36px;min-width: 36px !important;height: 36px;min-height: 36px;font-size: 14px;text-align: center;background:' . $colors['normal_ball_bg'] . ';color:' . $colors['normal_ball_text'] . ';">' . $number . '</td></tr></table></td>';
            }
        }

        // Bonus balls
        foreach ($lottery_bnumbers as $bnumber) {
            if (!empty($bnumber)) {
                $numbers[] = '<td style="padding-left:0;padding-bottom:8px;padding-right:4px;"><table style="min-width:36px"><tr><td style="font-weight:bold;border-radius: 100%;width: 36px;min-width: 36px !important;height: 36px;min-height: 36px;font-size: 14px;text-align: center;background:' . $colors['bonus_ball_bg'] . ';color:' . $colors['bonus_ball_text'] . ';">' . $bnumber . '</td></tr></table></td>';
            }
        }

        $numbers_string = "";
        $numbers = array_chunk($numbers, 8);

        foreach ($numbers as $number_set_key => $number_set) {
            $numbers_string .= "<tr>" . join("", $number_set) . "</tr>";
        }

        // Balls list structure
        $numbers_content .= '
                <table class="table-content" width="100%" cellpadding="0" cellspacing="0" style="width:100% !important;margin-bottom:0px;">
                    <tr>
                        <td style="line-height: 150%; font-size: 19px; font-family: Roboto, Arial; color: #585a6b;padding-bottom:20px;"><strong>' . $this->additional_translations['draw_results']['translation'] . '</strong></td>
                    </tr>
                    <tr>
                        <td style="line-height: 150%; font-size: 15px; font-family: Roboto, Arial; color: #727e8b;"><strong>' . $data['lottery_name'] . '</strong></td>
                    </tr>
                    <tr>
                        <td style="line-height: 150%; font-size: 15px; font-family: Roboto, Arial; color: #727e8b;padding-bottom:25px;"><strong>' . $this->additional_translations['draw_date']['translation'] . ':</strong> ' . $data['draw_date'] . '</td>
                    </tr>
                    <tr>
                        <td style="padding-left:0;">
                            <table cellpadding="0" cellspacing="0" style="border-collapse: initial !important;">
                                ' . $numbers_string . '
                            </table>
                        </td>
                    </tr>
                </table>';

        return [
            'html' => [
                '{lottery_name}' => $data['lottery_name'],
                '{numbers}' => $numbers_content,
                '{button}' => $this->create_button(
                    $button_additional_transalation,
                    $data['link'],
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($data['link']),
            ],
            'text' => [
                '{lottery_name}' => $data['lottery_name'],
                '{numbers}' => $lottery_numbers_text,
                '{button}' => $button_additional_transalation . ': ' . $button_additional_transalation . ': ' . $data['link']
            ]
        ];
    }

    private function getRaffleBuyVariables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $buttonAdditionalTranslation = $this->get_button_additional_translation();

        $amountWithCurrency = Lotto_View::format_currency(
            $data['amount'],
            $data['currency'],
            true
        );

        $numbersContent = $this->raffleNumbersContentData($data);
        $numbersContentText = $this->raffleNumbersContentDataText($data);
        $accountTicketsLink = $this->getPermalinkBySlug('account') . 'tickets/';

        return [
            'html' => [
                '{amount}' => $amountWithCurrency,
                '{numbers}' => $numbersContent,
                '{button}' => $this->create_button(
                    $buttonAdditionalTranslation,
                    $accountTicketsLink,
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($accountTicketsLink),
            ],
            'text' => [
                '{amount}' => $amountWithCurrency,
                '{numbers}' => $numbersContentText,
                '{button}' => $buttonAdditionalTranslation . ': ' . $accountTicketsLink,
            ]
        ];
    }

    private function getSupportTicketVariables(array $data): array
    {
        $colors = $this->get_whitelabel_colors();
        $buttonAdditionalTranslation = $this->get_button_additional_translation();

        $mailToUrl = 'mailto:' . $data['email'];

        return [
            'html' => [
                '{button}' => $this->create_button(
                    $buttonAdditionalTranslation,
                    $mailToUrl,
                    $colors
                ),
                '{link}' => $this->create_button_backup_link($mailToUrl),
                '{user_message}' => $data['body'],
                '{user_name}' => $data['name'],
                '{user_email}' => $data['email'],
                '{user_phone}' => $data['phone']
            ],
            'text' => [
                '{button}' => $buttonAdditionalTranslation . ': ' . $mailToUrl,
                '{user_message}' => $data['body'],
                '{user_name}' => $data['name'],
                '{user_email}' => $data['email'],
                '{user_phone}' => $data['phone']
            ]
        ];
    }

    private function raffleNumbersContentData(array $data): string
    {
        $colors = $this->get_whitelabel_colors();
        $numbersContent = '<table width="100%" cellpadding="0" cellspacing="0" align="left" style="border-top:1px solid #E8E8E8;">
            <tr>
                <td align="center" style="color: #5e5d60;" data-slot-container="1" style="">
                    <table cellpadding="0" cellspacing="0" class="table-content" width="100%" style="margin-top:7px;">';

        $numbersContent .= '';
        if (!empty($this->additional_translations['ticket_details']['translation'])) {
            $numbersContent .= '
                <tr>
                    <td style="padding-top:20px;padding-bottom:5px;font-weight:bold;font-size:18px;color:#5e5d60;">' .
                $this->additional_translations['ticket_details']['translation'] .
                ':</td>
                </tr>';
        }

        // Prepare tickets
        if (!empty($data['tickets'])) {
            foreach ($data['tickets'] as $id => $ticket) {

                if ($id == 0) {
                    $padding_top = '40';
                } else {
                    $padding_top = ' 25';
                }

                // Ticket lottery info
                $numbersContent .= '
                    <tr>
                        <td style="padding-top:15px;padding-bottom:3px;font-weight:bold;color:#727e8b;font-weight:bold;">' . $data['raffleName'] . '</td>
                    </tr>';

                $drawDateTranslation = '';
                if (!empty($this->additional_translations['draw_date']['translation'])) {
                    $drawDateTranslation = $this->additional_translations['draw_date']['translation'] . ': ';
                }

                $purchaseDateTranslation = '';
                if (!empty($this->additional_translations['purchase_date']['translation'])) {
                    $purchaseDateTranslation = $this->additional_translations['purchase_date']['translation'] . ': ';
                }

                $purchasePriceTranslation = '';
                if (!empty($this->additional_translations['purchase_price']['translation'])) {
                    $purchasePriceTranslation = $this->additional_translations['purchase_price']['translation'] . ': ';
                }

                $amountFormatted = $this->get_amount_formatted($ticket, $data);

                $purchasePriceContent = ($amountFormatted != "0.00") ? '
                    <tr>
                        <td style="padding-right: 20px;">' . $purchasePriceTranslation . '<b>' . $amountFormatted . '</b></td>
                    </tr>
                    ' : '';
                 if (!empty($data['drawDate']) || !empty($data['purchaseDate'])) {
                     $numbersContent .= '<tr>
                        <td style="padding-bottom:10px;">
                            <table cellspacing="0" style="line-height:22px;font-size:14px; font-family: \'Roboto\', Arial;color:#7c8a94;">';
                     if (!empty($data['drawDate'])) {
                         $numbersContent .= '<tr>
                                                 <td style = "padding-right: 90px;" > ' . $drawDateTranslation . '<b > ' . $data['drawDate'] . ' </b ></td >
                                            </tr >';
                     }
                     if (!empty($data['purchaseDate'])) {
                         $numbersContent .= '<tr>
                                                <td style = "padding-right: 90px;" > ' . $purchaseDateTranslation . '<b > ' . $data['purchaseDate']->format('%a %b %d %H:%M:%S %G') . ' </b ></td >
                                            </tr >';
                     }
                     $numbersContent .= $purchasePriceContent . '
                                    </table>
                            </td>
                        </tr>';
                 }
            }
        }

        $numbersContent .= ' </table>
                </td>
            </tr>
        </table>';

        return $numbersContent;
    }

    private function raffleNumbersContentDataText(array $data): string
    {
        $colors = $this->get_whitelabel_colors();
        $numbersContent = '';

        $numbersContent .= !empty($this->additional_translations['ticket_details']['translation']) ? $this->additional_translations['ticket_details']['translation'] . PHP_EOL : '';

        // Prepare tickets
        if (!empty($data['tickets'])) {
            foreach ($data['tickets'] as $id => $ticket) {

                // Ticket lottery info
                $numbersContent .= $data['raffleName'] . PHP_EOL;

                $drawDateTranslation = '';
                if (!empty($this->additional_translations['draw_date']['translation'])) {
                    $drawDateTranslation = $this->additional_translations['draw_date']['translation'] . ': ';
                }

                $purchaseDateTranslation = '';
                if (!empty($this->additional_translations['purchase_date']['translation'])) {
                    $purchaseDateTranslation = $this->additional_translations['purchase_date']['translation'] . ': ';
                }

                $purchasePriceTranslation = '';
                if (!empty($this->additional_translations['purchase_price']['translation'])) {
                    $purchasePriceTranslation = $this->additional_translations['purchase_price']['translation'] . ': ';
                }

                $amountFormatted = $this->get_amount_formatted($ticket, $data);
            }
        }

        return $numbersContent;
    }

    private function setUserTimezone(?string $user_timezone): void
    {
        if (empty($user_timezone)) {
            $user_timezone = 'UTC';
        }
        Lotto_Settings::getInstance()->set('timezone', $user_timezone);
    }

    /**
     * Function 'lotto_platform_get_permalink_by_slug' does not exist when used via API or in CLI env.
     * Tries to get a link from the cache.
     * If the cache is not set, the default link for 'en' is returned.
     */
    private function getPermalinkBySlug(string $slug): string
    {
        if (defined('WPINC') && function_exists('lotto_platform_get_permalink_by_slug')) {
            // we need to check and change the domain in the URL if the whitelabel domain is different from the one
            // generated by lotto_platform_get_permalink_by_slug. This applies, for example, in ZEN payments
            // where all payments go through lottopark
            return UrlHelper::checkAndChangeWhitelabelDomainInUrl(
                $this->whitelabel['domain'],
                lotto_platform_get_permalink_by_slug($slug),
            );
        }

        $permalink = $this->optimizeQueryService->getPermalinkBySlugForDomain(
            $slug,
            $this->whitelabel['domain'],
            LanguageHelper::getLanguageCodeFromLocale($this->lang) // eg. en_US -> en
        );

        if ($permalink === null) {
            $permalink = "https://{$this->whitelabel['domain']}/$slug";
            $permalink = rtrim($permalink, '/');
            $permalink = "$permalink/";
        }

        return UrlHelper::changeAbsoluteUrlToCasinoUrl(strtolower($permalink));
    }
}
