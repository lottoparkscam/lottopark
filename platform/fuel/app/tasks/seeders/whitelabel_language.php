<?php

namespace Fuel\Tasks\Seeders;

require_once(APPPATH . "/tasks/add_whitelabel_language.php");

/**
* Whitelabel Language seeder.
*/
final class Whitelabel_Language extends Seeder
{
    protected function columnsStaging(): array
    {
        return [];
    }

    protected function should_install_wordpress(): bool
    {
        if ($this->is_docker() && getenv("INSTALL_WORDPRESS") == 1) {
            return true;
        }
        return false;
    }

    protected function should_install_fuel(): bool
    {
        if ($this->is_docker() && getenv("INSTALL_FUEL") === "0") {
            return false;
        }
        return true;
    }


    protected function get_create_options(): array
    {
        $domain = getenv("WHITELABEL_DOMAINS");
        $language = getenv("LANGUAGE");

        return [
            'domain' => $domain ? $domain : 'lottopark.loc',
            'language' => $language ? $language : 'pl_PL'
        ];
    }

    protected function add_language($options): void
    {
        $add_language_task = new \Fuel\Tasks\Add_Whitelabel_Language();
        $add_language_task->set_options($options);

        if (!$this->should_install_wordpress()) {
            $add_language_task->disable_wordpress();
        }

        if (!$this->should_install_fuel()) {
            $add_language_task->disable_fuel();
        }

        $add_language_task->run();
    }

    protected function rowsStaging(): array
    {
        $this->add_language(
            $this->get_create_options()
        );
        return [];
    }

    protected function rowsProduction(): array
    {
        return [];
    }

}