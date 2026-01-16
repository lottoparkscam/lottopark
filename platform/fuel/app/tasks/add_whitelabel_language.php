<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Cli_Options;
use Task_Cli;

class Add_Whitelabel_Language extends Task_Cli
{
    use Cli_Options;

    private $disable_wordpress = false;
    private $disable_fuel = false;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->set_acceptable_options([
            'domain',
            'language'
        ]);
    }
    public function disable_wordpress(): void
    {
        $this->disable_wordpress = true;
    }

    public function disable_fuel(): void
    {
        $this->disable_fuel = true;
    }

    public function run(): void
    {
        if ($this->is_cli()) {
            $this->read_options();
        }

        $domain = $this->get_option("domain");
        $language = $this->get_option("language");

        if (empty($domain) || empty($language)) {
            $this->cli_error('Please specify domain and language arguments');
            return;
        }

        $whitelabel = \Model_Whitelabel::get_by_domain($domain);

        if (empty($whitelabel)) {
            $this->cli_error('Wrong domain (e.g. lottopark.loc)');
            return;
        }


        $language = \Model_Language::find_one_by_code($language);
        if (empty($language)) {
            $this->cli_error('Incorrect language');
            return;
        }

        try {
            $form_whitelabel_language = new \Forms_Whitelabel_Languages($whitelabel);
        } catch (Exception $e) {
            Cli::error($e->getMessage());
        }

        if ($this->disable_wordpress) {
            $form_whitelabel_language->disable_wordpress();
        }

        if ($this->disable_fuel) {
            $form_whitelabel_language->disable_fuel();
        }

        $logs = $form_whitelabel_language->add_whitelabel_language($language);

        foreach ($logs as $log) {
            Cli::write($log);
        }
    }
}
