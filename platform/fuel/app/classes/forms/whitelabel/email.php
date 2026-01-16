<?php

use Fuel\Core\Validation;
use Forms\Wordpress\Forms_Wordpress_Email;
use Services\Logs\FileLoggerService;

class Forms_Whitelabel_Email extends Forms_Main
{

    /**  @var array */
    private $whitelabel = [];
    private FileLoggerService $fileLoggerService;

    /**
     * Visible variables for admin control panel
     *
     * @var array
     */
    public $public_variables = [
        'template' => [
            '{content}' => 'Email content',
            '{footer_name}' => 'White-label name',
            '{footer_email}' => 'White-label email',
            '{main_url}' => 'URL of your white-label'
        ],
        'register' => [
            '{name}' => 'Whitelabel name',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'lost-password' => [
            '{name}' => 'Whitelabel name',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'email-change' => [
            '{name}' => 'Whitelabel name',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'ticket-buy' => [
            '{name}' => 'Whitelabel name',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{numbers}' => 'Picked numbers list',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'deposit-success' => [
            '{name}' => 'Whitelabel name',
            '{transaction_id}' => 'Transaction ID',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{payment_method_name}' => 'Payment method name',
            '{amount}' => 'Deposit amount with currency',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'deposit-failure' => [
            '{name}' => 'Whitelabel name',
            '{transaction_id}' => 'Transaction ID',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{amount}' => 'Deposit amount with currency',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'ticket-failure' => [
            '{name}' => 'Whitelabel name',
            '{transaction_id}' => 'Transaction ID',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
            '{amount}' => 'Transaction amount with currency',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'welcome-bonus' => [
            '{lottery_name}' => 'Lottery name',
            '{numbers}' => 'Numbers list',
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)'
        ],
        'welcome-mail' => [
            '{lotteries_widget}' => 'Widget with 3 lotteries (by highest jackpot)',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
        ],
        'draw-notification' => [
            '{numbers}' => 'Numbers list',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
        ],
        'multidraw-notification' => [
            '{multidraw}' => 'Multi-draw details',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
        ],
        'promo-code-bonus' => [
            '{name}' => 'Whitelabel name',
            '{lottery_name}' => 'Lottery name',
            '{button}' => 'Action button',
            '{link}' => 'Action button backup link',
        ]
    ];

    /**
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel = [])
    {
        $this->whitelabel = $whitelabel;
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    public function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add('title', _('Title'))
            ->add_rule('required')
            ->add_rule('trim')
            ->add_rule('max_length', 100);

        $validation->add('content', _('Content'))
            ->add_rule('required')
            ->add_rule('trim');

        $validation->add('text_content', _('Content of text version'))
            ->add_rule('required')
            ->add_rule('trim');

        $validation->add('additional_translates', _('Additional translates'));

        return $validation;
    }

    /**
     * Process email template change form
     *
     * @param int $custom_template_id
     * @param int $template_id
     * @param Fuel\Core\View $inside
     * @param string $mail_lang
     * @param string $slug
     * @param array $additional_translations
     * @return void
     */
    public function process_form(
        int $custom_template_id = null,
        int $template_id = null,
        Fuel\Core\View &$inside = null,
        string $mail_lang = null,
        string $slug = "",
        array $additional_translations = []
    ): void {
        if (Input::post("submit") === null) {
            return;
        }

        $whitelabel = $this->get_whitelabel();

        $validated_form = $this->validate_form();

        // Validate
        if ($validated_form->run()) {
            $new_additional_translations = $this->prepare_new_additional_translations(
                $additional_translations,
                $validated_form->validated("additional_translates")
            );

            // Update email template content
            $this->mail_update(
                $custom_template_id,
                $template_id,
                $whitelabel['id'],
                $validated_form->validated("title"),
                $validated_form->validated("content"),
                $validated_form->validated("text_content"),
                $mail_lang,
                $new_additional_translations
            );

            $mail = Model_Whitelabel_Mails_Template::get_whitelabel_templates($this->whitelabel['id'], $slug, $mail_lang);

            $wordpress_email = new Forms_Wordpress_Email($whitelabel);
            $mail['content'] = $wordpress_email->build_email($mail['content']);
            $mail['additional_translates'] = $wordpress_email->get_additional_translations($mail, $mail_lang);
            
            $variables = [];
            if (!empty($this->public_variables[$slug])) {
                $variables = $this->public_variables[$slug];
            }
            
            $inside->set("mail", $mail);
            $inside->set("mail_lang", $mail_lang);
            $inside->set("variables", $variables);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $inside->set("errors", $errors);
        }

        return;
    }

    /**
     * Update email template content
     *
     * @param int $custom_template_id
     * @param int $template_id
     * @param int $whitelabel_id
     * @param string $title
     * @param string $content
     * @param string $text_content
     * @param string $mail_lang
     * @param array $additional_translates
     * @return void
     */
    private function mail_update(
        int $custom_template_id = null,
        int $template_id = null,
        int $whitelabel_id = null,
        string $title = "",
        string $content = "",
        string $text_content = "",
        string $mail_lang = "",
        array $additional_translates = []
    ): void {
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
            $this->fileLoggerService->error(
                $error_message
            );
            
            $error_flash_message = _("Mail has not been saved. Please contact with us!");
            Session::set_flash("message", ["danger", $error_flash_message]);
            return;
        }
        
        Model_Whitelabel_Mails_Custom_Template::update_email_template(
            $custom_template_id,
            $template_id,
            $whitelabel_id,
            $title,
            $content,
            $text_content,
            $mail_lang,
            $additional_translates
        );

        Session::set_flash("message", ["success", _("Mail has been edited!")]);
    }

    /**
     * Restore default email template
     *
     * @param $whitelabel_id
     * @param $slug
     * @param $lang
     */
    public function restore_default(
        int $whitelabel_id,
        string $slug,
        string $lang
    ): void {
        Model_Whitelabel_Mails_Custom_Template::restore_default($whitelabel_id, $slug, $lang);

        Session::set_flash("message", ["success", _("Mail has been restored!")]);
    }

    /**
     * Prepare new additional translations for email template
     *
     * @param array $additional_translations
     * @param array $new_additional_translations
     * @return array
     */
    private function prepare_new_additional_translations(
        array $additional_translations = [],
        array $new_additional_translations = null
    ): array {
        $data = [];

        foreach ($additional_translations as $key => $row) {
            if (empty($new_additional_translations[$key])) {
                continue;
            }
            
            $data[$key] = [
                'label' => $additional_translations[$key]['label'],
                'translation' => $new_additional_translations[$key]
            ];
        }

        return $data;
    }
}
