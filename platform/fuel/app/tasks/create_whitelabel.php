<?php

namespace Fuel\Tasks;

use Fuel\Core\Cli;
use Cli_Options;
use Task_Cli;

class Create_Whitelabel extends Task_Cli
{
    use Cli_Options;

    private $disable_wordpress = false;
    private $disable_fuel = false;

    public function __construct()
    {
        $this->disableOnProduction();

        $this->set_options([
            'email' => 'admin@lottopark.work',
            'realname' => 'John Doe',
            'company_details' => "White Lotto B.V.\nFransche Bloemweg 4, Willemstad, Curacao",
            'margin' => 20,
            'type' => 1,
            'username' => 'lottopark',
            'prefix' => 'LP',
            'manager_site_currency_id' => 2,
            'prepaid' => 0,
            'prepaid_alert_limit' => 0,
            'site_currency_id' => 2,
            'us_state_active' => 0,
            'enabled_us_states' => null,
            'password' => 'lottoparkpassword',
        ]);

        $this->set_acceptable_options([
            'domain',
            'name',
            'theme',
            'password',
            'email',
            'realname',
            'company',
            'margin',
            'type',
            'username',
            'prefix',
            'currency_id',
            'prepaid',
            'prepaid_alert_limit',
            'site_currency_id'
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

        $form_whitelabel = new \Forms_Whitelabel_New();

        if ($this->disable_wordpress) {
            $form_whitelabel->disable_wordpress();
        }

        if ($this->disable_fuel) {
            $form_whitelabel->disable_fuel();
        }

        $domain = $this->options['domain'];
        $name = $this->options['name'];
        $theme = $this->options['theme'];

        if (empty($domain) || empty($name) || empty($theme)) {
            $this->cli_error('Please specify domain, name and theme arguments');
            return;
        }

        $password = $this->options['password'];

        $salt = \Lotto_Security::generate_salt();
        $hash = \Lotto_Security::generate_hash($password, $salt);

        $form_whitelabel->set(
            array_merge(
                $this->options,
                [
                    "hash" => $hash,
                    "salt" => $salt,
                ]
            )
        );

        $logs = $form_whitelabel->create_whitelabel();

        if ($this->is_cli()) {
            foreach ($logs as $log) {
                Cli::write($log);
            }
        }
    }
}
