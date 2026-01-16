<?php

namespace Fuel\Tasks\Seeders;

require_once(APPPATH . "/tasks/create_whitelabel.php");

/**
 * Whitelabel seeder.
 */
final class Whitelabel extends Seeder
{
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

    protected function create_whitelabel($options): void
    {
        $create_whitelabel_task = new \Fuel\Tasks\Create_Whitelabel();
        $create_whitelabel_task->set_options($options);

        if (!$this->should_install_wordpress()) {
            $create_whitelabel_task->disable_wordpress();
        }

        if (!$this->should_install_fuel()) {
            $create_whitelabel_task->disable_fuel();
        }

        $create_whitelabel_task->run();
    }

    protected function get_create_options($default_domain): array
    {
        $domain = getenv("WHITELABEL_DOMAINS");
        $name = getenv("WORDPRESS_MAINSITE_TITLE");
        $theme = getenv("WORDPRESS_MAINSITE_THEME");

        return [
            "domain" => $domain ? $domain : $default_domain,
            "name" => $name ? $name : "lottopark",
            "theme" => $theme ? $theme : "lottopark"
        ];
    }

    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsDevelopment(): array
    {
        $this->create_whitelabel(
            $this->get_create_options("lottopark.loc")
        );

        return [];
    }

    protected function rowsStaging(): array
    {
        $this->create_whitelabel(
            $this->get_create_options("lottopark.work")
        );

        return[];
    }

    protected function rowsProduction(): array
    {
        return [];
    }
}
