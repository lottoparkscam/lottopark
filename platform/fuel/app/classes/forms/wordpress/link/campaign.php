<?php

use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

class Forms_Wordpress_Link_Campaign extends Forms_Main
{
    private FileLoggerService $fileLoggerService;

    public function __construct()
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    /**
     * @return Validation object
     */
    protected function get_prepared_form(): Validation
    {
        $val = Validation::forge("campaign");
        
        $val->add("campaign", "")
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule("min_length", 1)
            ->add_rule("max_length", 100)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);
        
        return $val;
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $val = $this->get_prepared_form();
        
        if ($val->run(Input::get())) {
            Session::set("campaign", Input::get("campaign"));
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $json_encoded_error = json_encode($errors);

            $this->fileLoggerService->warning(
                "Some errors occurred: " . $json_encoded_error
            );
        }
    }
}
