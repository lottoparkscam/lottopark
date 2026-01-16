<?php

use Fuel\Core\Input;
use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;
use Helpers\UrlHelper;
use Services\AffService;
use Validators\ClickIdValidator;

/**
 * This class is responsible for processing clickID get input.
 */
class Forms_Wordpress_Click extends Forms_Main
{
    const CLICK_NAME = "clickID";

    /**
     *
     * @var array
     */
    private $whitelabel = [];
    private FileLoggerService $fileLoggerService;

    /**
     * @param array $whitelabel
     */
    public function __construct($whitelabel = [])
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

    /**
     * Build validation object for this form.
     * @return Validation object.
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge("clickID");

        $val->add("clickID", "")
                ->add_rule('trim')
                ->add_rule('stripslashes')
                ->add_rule("required")
                ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        return $val;
    }

    /**
     *  Process clickID get input.
     */
    public function process_form(string $pathname = ''): ?string
    {
        if (empty(Input::get(self::CLICK_NAME))) {
            return null;
        }
        /** @var ClickIdValidator $validator*/
        $validator = Container::get(ClickIdValidator::class);
        if ($validator->isValid()) {
            /** @var AffService */
            $affService = Container::get(AffService::class);
            try {
                $affService->savePropertyToCookieLegacy($this->get_whitelabel(), self::CLICK_NAME, $validator->getProperty(self::CLICK_NAME));
            } catch (Throwable $exception) {
                $this->fileLoggerService->error($exception->getMessage() . 'could not take value in property: ' . self::CLICK_NAME);
            }

            // redirect user to proper page, only if there is no more input to process. TODO: abstract this for future use.
            if (count(Input::get()) === 1) {
                return UrlHelper::getHomeUrlWithoutLanguage('/' . $pathname);
            }
        } else {
            $json_encoded_error = json_encode($validator->getErrors());

            $this->fileLoggerService->error(
                "Some errors occured: " . $json_encoded_error
            );
        }

        return null;
    }
}
