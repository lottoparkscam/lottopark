<?php


namespace Fuel\Tasks;


use Task_Cli;

class Refresh_Translations extends Task_Cli
{
    public function __construct()
    {
        $this->disableOnProduction();
    }

    /**
     * Generate .mo files of each languages for theme
     */
    public function run(string $theme)
    {
        shell_exec("
            cd .. && 
            cd wordpress/wp-content/themes/{$theme}/languages/gettext/ &&
            find . -iname '*.po' | sed 's/\.po$//1' | xargs -I % msgfmt '%.po' -o '%.mo'
        ");
    }
}